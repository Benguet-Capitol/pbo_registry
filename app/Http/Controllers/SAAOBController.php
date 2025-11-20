<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\SAAOBExport;
use App\Models\AccountCode;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use Maatwebsite\Excel\Facades\Excel;

class SAAOBController extends Controller
{
    public function index(Request $request)
        {
            $selectedYear = request('year1', date('Y'));
            $selectedOffice = request('office_filter');
            $selectedAccountCode = request('account_code');
            $asOfDate = request('as_of_filter', now()->toDateString());

            $allOffices = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Current');
                    });
            })->orderBy('id', 'asc')->get();

            // Get all employees for signatory filter
            $employees = Employee::where('office', 'PBO')->orderBy('employee_id')->get();

            $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

            // Get account codes
            $usedAccountCodes = Appropriation::whereHas('officeAllotmentClass', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Current');
                    });
            })
            ->distinct()
            ->pluck('account_code');

            // Get account codes from account_codes table that are actually used
            $accounts = AccountCode::whereIn('code', $usedAccountCodes)
                ->orderBy('code')
                ->pluck(DB::raw("CONCAT(code, ' - ', description)"), 'code');

            // Only include offices with OfficeAllotmentClasses for the selected year and "Current" category
            $officesQuery = Office::whereHas('officeAllotmentClasses', function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear)
                    ->whereHas('allotmentClass', function ($subQuery) {
                        $subQuery->where('category', 'Current');
                    });
            });

            // If a specific office is selected, filter it
            if (!empty($selectedOffice)) {
                $officesQuery->where('id', $selectedOffice);
            }
            
            // Get all offices with their OfficeAllotmentClasses and related data
            $offices = $officesQuery->with([
                'officeAllotmentClasses' => function ($query) use ($selectedYear) {
                    $query->where('year', $selectedYear)
                        ->whereHas('allotmentClass', function ($subQuery) {
                            $subQuery->where('category', 'Current');
                        });
                },
                'officeAllotmentClasses.allotmentClass',
                'officeAllotmentClasses.appropriations' => function ($query) use ($selectedAccountCode) {
                    // Add account code filter here
                    if (!empty($selectedAccountCode)) {
                        $query->where('account_code', 'LIKE', $selectedAccountCode . '%');
                    }
                    $query->orderByRaw("CASE WHEN programs IS NULL OR programs = '' THEN 0 ELSE 1 END ASC")
                        ->orderBy('account_code', 'asc');
                },
                'officeAllotmentClasses.appropriations.realignments',
                'officeAllotmentClasses.appropriations.supplementals',
                'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
            ])->get();

            // Filter out offices that have no appropriations after account_code filtering
            if (!empty($selectedAccountCode)) {
                $offices = $offices->filter(function($office) {
                    foreach ($office->officeAllotmentClasses as $oac) {
                        if ($oac->appropriations->isNotEmpty()) {
                            return true;
                        }
                    }
                    return false;
                })->values();
            }

            $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

            foreach ($offices as $office) {
                $office->officeAllotmentClasses = $office->officeAllotmentClasses
                    ->filter(function($oac) {
                        return $oac->appropriations->isNotEmpty();
                    })
                    ->sortBy(fn ($oac) => $oac->allotmentClass->id)
                    ->values();

                foreach ($office->officeAllotmentClasses as $oac) {
                    $grouped = $oac->appropriations
                        ->sortBy(fn ($a) => [$a->programs === null ? 0 : 1, $a->account_code])
                        ->groupBy(fn ($a) => $a->programs ?? '');

                    foreach ($grouped as $program => $appropriations) {
                        foreach ($appropriations as $app) {

                            // --- Supplementals ---
                            $sb = $app->supplementals
                                ->where('type', 'Supplemental')
                                ->where('supplemental_date', '<=', $asOfDate)
                                ->sum('amount');

                            $rev = $app->supplementals
                                ->where('type', 'Reversion')
                                ->where('supplemental_date', '<=', $asOfDate)
                                ->sum('amount') * -1;

                            // --- Realignments ---
                            $realignment = $app->realignments
                                ->where('realignment_date', '<=', $asOfDate)
                                ->reduce(fn ($carry, $r) =>
                                    $carry + ($r->type === 'Source' ? -$r->amount : $r->amount), 0);

                            // --- Obligations ---
                            $obligationBase = $app->obligationAmounts
                                ->filter(fn ($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                                ->sum('obr_amount');

                            // --- Obligation Adjustments ---
                            $obligationAdjustments = $app->obligationAmounts
                            ->flatMap(
                                fn ($oa) =>
                                $oa->obligation
                                    ? $oa->obligation->obligationAdjustments
                                    ->where('adjustment_date', '<=', $asOfDate)
                                    ->where('obligation_amounts_id', $oa->id)
                                    : collect()
                            )
                            ->sum('adjustment_amount');

                            $obligation = $obligationBase + $obligationAdjustments;

                            $authorized = $app->appropriation + $sb + $rev + $realignment;
                            $allotment = ($app->quarter1 + $app->quarter2 + $app->quarter3 + $app->quarter4) + $sb + $rev + $realignment;

                            $forLater = 0;
                            if ($currentQuarter < 2) $forLater += $app->quarter2;
                            if ($currentQuarter < 3) $forLater += $app->quarter3;
                            if ($currentQuarter < 4) $forLater += $app->quarter4;

                            $allotment -= $forLater;

                            $app->sb_appropriation = $sb;
                            $app->reversion = $rev;
                            $app->realignment = $realignment;
                            $app->obligation = $obligation;
                            $app->authorized_appropriation = $authorized;
                            $app->allotment = $allotment;
                            $app->for_later_release = $forLater;

                            $app->appropriation_balance = $authorized - $obligation;
                            $app->appropriation_accomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                            $app->allotment_balance = $allotment - $obligation;
                            $app->allotment_accomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;
                        }
                    }

                    // Subtotals per program
                    $subtotals = [];
                    foreach ($grouped as $program => $apps) {
                        if ($program === '') continue;

                        $subtotal = $this->computeTotals($apps);
                        $subtotal['appropriation_accomplishment'] = ($subtotal['authorized_appropriation'] > 0)
                            ? ($subtotal['obligation'] / $subtotal['authorized_appropriation']) * 100
                            : 0;
                        $subtotal['allotment_accomplishment'] = ($subtotal['allotment'] > 0)
                            ? ($subtotal['obligation'] / $subtotal['allotment']) * 100
                            : 0;
                        $subtotals[$program] = $subtotal;
                    }

                    // Grand Total
                    $total = $this->computeTotals($grouped->get('') ?? collect());
                    foreach ($subtotals as $sub) {
                        foreach ($sub as $key => $val) {
                            if ($key !== 'count') $total[$key] += $val;
                        }
                        $total['count'] += $sub['count'];
                    }

                    $total['appropriation_accomplishment'] = ($total['authorized_appropriation'] > 0)
                        ? ($total['obligation'] / $total['authorized_appropriation']) * 100
                        : 0;
                    $total['allotment_accomplishment'] = ($total['allotment'] > 0)
                        ? ($total['obligation'] / $total['allotment']) * 100
                        : 0;

                    $oac->groupedAppropriations = $grouped;
                    $oac->groupSubtotals = $subtotals;
                    $oac->groupTotal = $total;
                }

                // Grand Total for Office
                $gt = $this->computeOfficeTotal($office->officeAllotmentClasses);
                $gt['appropriation_accomplishment'] = ($gt['authorized_appropriation'] > 0)
                    ? ($gt['obligation'] / $gt['authorized_appropriation']) * 100
                    : 0;
                $gt['allotment_accomplishment'] = ($gt['allotment'] > 0)
                    ? ($gt['obligation'] / $gt['allotment']) * 100
                    : 0;
                $office->grandTotal = $gt;
            }

            // Calculate overall total if all offices are selected
            $overallTotal = null;
            if (empty($selectedOffice) && count($offices) > 0) {
                $allOacs = $offices->flatMap(function($office) {
                    return $office->officeAllotmentClasses;
                });
                $overallTotal = $this->computeOfficeTotal($allOacs);
                $overallTotal['appropriation_accomplishment'] = ($overallTotal['authorized_appropriation'] > 0)
                    ? ($overallTotal['obligation'] / $overallTotal['authorized_appropriation']) * 100
                    : 0;
                $overallTotal['allotment_accomplishment'] = ($overallTotal['allotment'] > 0)
                    ? ($overallTotal['obligation'] / $overallTotal['allotment']) * 100
                    : 0;
            }

            return view('saaob.index', compact('availableYears', 'offices', 'selectedYear', 'selectedOffice', 'selectedAccountCode', 'asOfDate', 'employees', 'officesQuery', 'allOffices', 'accounts', 'overallTotal'))
                ->with('status', session('status'));
        }

    private function computeTotals($appropriations)
        {
            $total = [
                'appropriation' => 0,
                'sb_appropriation' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'for_later_release' => 0,
                'obligation' => 0,
                'appropriation_balance' => 0,
                'appropriation_accomplishment' => 0,
                'allotment_balance' => 0,
                'allotment_accomplishment' => 0,
                'count' => 0,
            ];

            foreach ($appropriations as $app) {
                foreach ($total as $key => $val) {
                    if ($key !== 'count') $total[$key] += $app->$key ?? 0;
                }
                $total['count']++;
            }

            $total['appropriation_accomplishment'] = $total['count'] > 0
                ? $total['appropriation_accomplishment'] / $total['count']
                : 0;

            $total['allotment_accomplishment'] = $total['count'] > 0
                ? $total['allotment_accomplishment'] / $total['count']
                : 0;

            return $total;
        }

    private function computeOfficeTotal($oacs)
        {
            $officeTotal = [
                'appropriation' => 0,
                'sb_appropriation' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'for_later_release' => 0,
                'obligation' => 0,
                'appropriation_balance' => 0,
                'appropriation_accomplishment' => 0,
                'allotment_balance' => 0,
                'allotment_accomplishment' => 0,
                'count' => 0,
            ];

            foreach ($oacs as $oac) {
                foreach ($officeTotal as $key => $val) {
                    if ($key !== 'count') $officeTotal[$key] += $oac->groupTotal[$key] ?? 0;
                }
                $officeTotal['count'] += $oac->groupTotal['count'] ?? 0;
            }

            $officeTotal['appropriation_accomplishment'] = $officeTotal['count'] > 0
                ? $officeTotal['appropriation_accomplishment'] / $officeTotal['count']
                : 0;

            $officeTotal['allotment_accomplishment'] = $officeTotal['count'] > 0
                ? $officeTotal['allotment_accomplishment'] / $officeTotal['count']
                : 0;

            return $officeTotal;
        }

    public function exportExcel(Request $request)
    {
        $year = $request->input('year1');
        $officeId = $request->input('office_filter');
        $accountCode = $request->input('account_code');
        $asOf = $request->input('as_of_filter');
        $signatoryName = $request->input('signatory_name');
        $signatoryDesignation = $request->input('signatory_designation');

        // Get the office name by ID, or use "All_Offices" if none is selected
        $officeName = 'All_Offices';
        if (!empty($officeId)) {
            $office = Office::find($officeId);
            if ($office) {
                $officeName = preg_replace('/[^A-Za-z0-9_]/', '_', $office->office_abbreviation); // sanitize filename
            }
        }

        // Get the account code display (code only, without description)
        $accountCodeName = '';
        if (!empty($accountCode)) {
            $accountCodeName = '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $accountCode); // sanitize filename
        }

        $fileName = 'SAAOB_' . $officeName . $accountCodeName . '_' . $year . '.xlsx';

        return Excel::download(new SAAOBExport(
            $year,
            $officeId,
            $accountCode,
            $asOf,
            $signatoryName,
            $signatoryDesignation
        ), $fileName);
    }
}
