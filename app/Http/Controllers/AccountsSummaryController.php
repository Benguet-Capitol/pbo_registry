<?php
namespace App\Http\Controllers;

use App\Exports\AccountsSummaryExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\RAOExport;
use App\Models\AccountCode;
use App\Models\Employee;
use App\Models\Obligation;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class AccountsSummaryController extends Controller
{
    public function index(Request $request)
{
    $selectedYear = request('year1', date('Y'));
    $asOfDate = request('as_of_filter', now()->toDateString());
    $selectedFund = request('fund_filter', 'all');

    // Get all employees for signatory filter
    $employees = Employee::where('office', '12')->orderBy('employee_id')->get();

    $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');
    
    // Get available funds
    $availableFunds = [
        'all' => 'All Funds',
        'General Fund' => 'General Fund',
        'Benguet General Hospital Economic Enterprise' => 'Benguet General Hospital Economic Enterprise',
        'Special Education Fund' => 'Special Education Fund'
    ];

    // Get current quarter based on as_of_date
    $currentMonth = Carbon::parse($asOfDate)->month;
    $currentQuarter = ceil($currentMonth / 3);

    // Fetch all account codes for description lookup
    $accountCodes = AccountCode::all()->keyBy('code');

    // Fetch allotment classes with their appropriations
    $query = OfficeAllotmentClass::with([
        'allotmentClass',
        'fundSourceRelation',
        'appropriations' => function($q) use ($asOfDate) {
            $q->with([
                'supplementals',
                'realignments',
                'obligationAmounts' => function($q2) use ($asOfDate) {
                    $q2->with(['obligation' => function($q3) use ($asOfDate) {
                        $q3->with('obligationAdjustments');
                    }]);
                }
            ]);
        }
    ])
        ->where('year', $selectedYear);
    
    // Apply allotment class category filter for 'Current'
    $query->whereHas('allotmentClass', function($q) {
        $q->where('category', 'Current');
    });
    
    // Apply fund_source filter for 'Current'
    $query->whereHas('fundSourceRelation', function($q) {
        $q->where('category', 'Current');
    });
    
    // Apply fund filter
    if ($selectedFund !== 'all') {
        if ($selectedFund === 'General Fund') {
            $query->whereIn('fund', ['General Fund', 'Provincial Development Fund']);
        } else {
            $query->where('fund', $selectedFund);
        }
    }
    
    $allotmentClasses = $query->orderBy('id')
        ->get()
        ->groupBy(function($item) {
            return $item->allotmentClass->description;
        });

    // Build grouped structure: class => [accounts with their appropriations]
    $allotmentClassTotals = $allotmentClasses->map(function($classes) use ($asOfDate, $currentQuarter, $accountCodes) {
        $classAccounts = [];
        $classSubtotals = [
            'appropriation' => 0,
            'sb_appropriation' => 0,
            'reversion' => 0,
            'realignment' => 0,
            'authorized_appropriation' => 0,
            'allotment' => 0,
            'for_later_release' => 0,
            'obligation' => 0,
            'appropriation_balance' => 0,
            'allotment_balance' => 0,
            'utilization_percent' => 0,
            'allotment_utilization_percent' => 0,
        ];

        // Get all appropriations for this allotment class group
        $classAppropriations = $classes->flatMap(fn($oac) => $oac->appropriations);

        // Group appropriations by base account code (without extension)
        $groupedByAccountCode = $classAppropriations->groupBy(function($app) {
            // Remove extension (everything after space)
            return trim(explode(' ', trim($app->account_code))[0]);
        });

        // Sort by account code (natural string sort)
        $groupedByAccountCode = $groupedByAccountCode->sortKeys();

        // Iterate through grouped appropriations (only those in this class)
        foreach ($groupedByAccountCode as $baseAccountCode => $appsByCode) {
            $matchingAppropriations = $appsByCode;
            
            if ($matchingAppropriations->isEmpty()) {
                continue;
            }

            // Sum all matching appropriations for this account
            $appropriation = $matchingAppropriations->sum('appropriation');

            $sb = $matchingAppropriations->flatMap(fn($app) => 
                $app->supplementals
                    ->where('type', 'Supplemental')
                    ->where('supplemental_date', '<=', $asOfDate)
            )->sum('amount');

            $rev = $matchingAppropriations->flatMap(fn($app) => 
                $app->supplementals
                    ->where('type', 'Reversion')
                    ->where('supplemental_date', '<=', $asOfDate)
            )->sum('amount') * -1;

            // --- Realignments ---
            $realignment = $matchingAppropriations->flatMap(fn($app) => 
                $app->realignments->where('realignment_date', '<=', $asOfDate)
            )->reduce(fn ($carry, $r) =>
                $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

            // --- Obligations ---
            $obligationBase = $matchingAppropriations->flatMap(fn($app) => 
                $app->obligationAmounts
                    ->filter(fn ($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
            )->sum('obr_amount');

            // --- Obligation Adjustments ---
            $obligationAdjustments = $matchingAppropriations->flatMap(fn($app) =>
                $app->obligationAmounts->flatMap(fn($oa) =>
                    $oa->obligation
                        ? $oa->obligation->obligationAdjustments
                            ->where('adjustment_date', '<=', $asOfDate)
                            ->where('obligation_amounts_id', $oa->id)
                        : collect()
                )
            )->sum('adjustment_amount');

            $obligation = $obligationBase + $obligationAdjustments;

            // --- Authorized Appropriation ---
            $authorized = $appropriation + $sb + $rev + $realignment;

            // --- Allotment & For Later Release ---
            $allotment = $matchingAppropriations->reduce(function($carry, $app) {
                return $carry + ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4);
            }, 0) + $sb + $rev + $realignment;

            $forLater = 0;
            if ($currentQuarter < 2) {
                $forLater += $matchingAppropriations->sum('quarter2');
            }
            if ($currentQuarter < 3) {
                $forLater += $matchingAppropriations->sum('quarter3');
            }
            if ($currentQuarter < 4) {
                $forLater += $matchingAppropriations->sum('quarter4');
            }

            $allotment -= $forLater;

            // --- Balances & Accomplishments ---
            $appropriation_balance = $authorized - $obligation;
            $appropriation_accomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
            $allotment_balance = $allotment - $obligation;
            $allotment_accomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

            // Get first appropriation's description and fpp for display
            $firstApp = $matchingAppropriations->first();

            // Get description from AccountCode model
                $accountCodeDescription = $accountCodes->get($baseAccountCode)?->description ?? $firstApp->description ?? '';
            
            // Store account data
            $classAccounts[] = [
                'id' => $firstApp->id ?? null,
                'description' => $accountCodeDescription,
                'account_code' => $baseAccountCode,
                'fpp' => $firstApp->fpp ?? null,
                'appropriation' => $appropriation,
                'sb_appropriation' => $sb,
                'reversion' => $rev,
                'realignment' => $realignment,
                'authorized_appropriation' => $authorized,
                'allotment' => $allotment,
                'for_later_release' => $forLater,
                'obligation' => $obligation,
                'appropriation_balance' => $appropriation_balance,
                'appropriation_accomplishment' => $appropriation_accomplishment,
                'allotment_balance' => $allotment_balance,
                'allotment_accomplishment' => $allotment_accomplishment,
            ];

            // Add to class subtotals
            $classSubtotals['appropriation'] += $appropriation;
            $classSubtotals['sb_appropriation'] += $sb;
            $classSubtotals['reversion'] += $rev;
            $classSubtotals['realignment'] += $realignment;
            $classSubtotals['authorized_appropriation'] += $authorized;
            $classSubtotals['allotment'] += $allotment;
            $classSubtotals['for_later_release'] += $forLater;
            $classSubtotals['obligation'] += $obligation;
            $classSubtotals['appropriation_balance'] += $appropriation_balance;
            $classSubtotals['allotment_balance'] += $allotment_balance;
        }

        // Calculate class utilization percentages
        $classSubtotals['utilization_percent'] = $classSubtotals['authorized_appropriation'] > 0 
            ? ($classSubtotals['obligation'] / $classSubtotals['authorized_appropriation']) * 100 
            : 0;
        
        $classSubtotals['allotment_utilization_percent'] = $classSubtotals['allotment'] > 0 
            ? ($classSubtotals['obligation'] / $classSubtotals['allotment']) * 100 
            : 0;

        return [
            'accounts' => $classAccounts,
            'subtotals' => $classSubtotals,
        ];
    });

    return view('summaryaccounts.index', compact(
        'availableYears', 
        'selectedYear',
        'asOfDate',
        'employees',
        'allotmentClassTotals',
        'availableFunds',
        'selectedFund'
    ))->with('status', session('status'));
}

    public function exportExcel(Request $request)
    {
        $year = $request->input('year1');
        $asOf = $request->input('as_of_filter');
        $signatoryName = $request->input('signatory_name');
        $signatoryDesignation = $request->input('signatory_designation');
        $selectedFund = $request->input('fund_filter', 'all');

        
        $fileName = 'SAAOB_Summary' .  '_' . $year . '.xlsx';

        return Excel::download(new AccountsSummaryExport(
            $year,
            $asOf,
            $signatoryName,
            $signatoryDesignation,
            $selectedFund
        ), $fileName);
    }
}
