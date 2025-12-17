<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Office;
use App\Models\AllotmentClass;
use Carbon\Carbon;
use App\Exports\SAAOBFundSectorExport;
use App\Models\Employee;
use App\Models\ObligationAdjustment;
use App\Models\Fund;
use App\Models\Sector;
use Maatwebsite\Excel\Facades\Excel;

class SAAOBFundSectorController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = request('year1', date('Y'));
        $selectedFund = request('fund_filter');
        $asOfDate = request('as_of_filter', now()->toDateString());

        $employees = Employee::where('office', '12')->orderBy('employee_id')->get();
        $sectors = Sector::all();
        $allFunds = Fund::select('fund_type')->distinct()->orderBy('id')->pluck('fund_type');
        $fundsQuery = Fund::query();
        $allotmentClasses = AllotmentClass::all()->keyBy('class');

        $month = Carbon::parse($asOfDate)->month;
            $currentQuarter = match(true) {
                $month <= 3 => 1,
                $month <= 6 => 2,
                $month <= 9 => 3,
                default => 4,
            };

        if ($selectedFund === 'others') {
            $fundsQuery->whereIn('fund_type', [
                'Benguet General Hospital Economic Enterprise',
                'Special Education Fund'
            ]);
        } elseif ($selectedFund) {
            $fundsQuery->where('fund_type', $selectedFund);
        }

        $funds = $fundsQuery->with([
            'officeAllotmentClasses' => fn($query) => $query->where('year', $selectedYear),
            'officeAllotmentClasses.fundSourceRelation',
            'officeAllotmentClasses.appropriations',
            'officeAllotmentClasses.appropriations.supplementals',
            'officeAllotmentClasses.appropriations.realignments',
            'officeAllotmentClasses.appropriations.obligationAmounts.obligation.obligationAdjustments',
        ])->get();

        $groupedFunds = $funds->groupBy('fund_type')->map(function ($fundGroup, $type) use ($sectors, $allotmentClasses, $currentQuarter, $asOfDate) {
            $mergedOACs = $fundGroup->flatMap->officeAllotmentClasses;
            $baseFund = $fundGroup->first()->replicate();
            $baseFund->fund_type = $type;
            $baseFund->setRelation('officeAllotmentClasses', $mergedOACs);

            $groupedByCategory = $mergedOACs->groupBy(fn($oac) =>
                optional($oac->fundSourceRelation)->category ?? 'Uncategorized'
            );

            $baseFund->setRelation('groupedOACs', $groupedByCategory);

            $categoryClassStats = collect();

            foreach ($groupedByCategory as $category => $oacs) {
                $classMap = collect();

                foreach ($oacs as $oac) {
                    $oacClass = $allotmentClasses->get($oac->class);
                    if (!$oacClass) continue;

                    foreach ($oac->appropriations as $appropriation) {
                        $classKey = $oac->class;

                        $approved = $appropriation->appropriation;
                        // Supplemental & Reversion (filter by supplemental_date)
                        $supplemental = $appropriation->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $reversion = $appropriation->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        // Realignments (filter by realignment_date)
                        $realignment = $appropriation->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

                        // Authorized Appropriation
                        $authorized = $approved + $supplemental + $realignment + $reversion;

                        // Allotment (respect date-filtered adjustments too)
                        $allotment = ($appropriation->quarter1 + $appropriation->quarter2 + $appropriation->quarter3 + $appropriation->quarter4)
                            + $supplemental + $reversion + $realignment;

                        // For Later Release (unchanged, but still dynamic by quarter)
                        $forLaterRelease = $appropriation->for_later_release ?? (
                            ($currentQuarter < 2 ? ($appropriation->quarter2 ?? 0) : 0) +
                            ($currentQuarter < 3 ? ($appropriation->quarter3 ?? 0) : 0) +
                            ($currentQuarter < 4 ? ($appropriation->quarter4 ?? 0) : 0)
                        );

                        $allotment -= $forLaterRelease;

                        // Obligations (filter by obr_date)
                        $obligationBase = $appropriation->obligationAmounts
                            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        // --- Obligation Adjustments ---
                        $obligationAdjustments = $appropriation->obligationAmounts
                        ->flatMap(fn ($oa) =>
                            $oa->obligation
                                ? $oa->obligation->obligationAdjustments
                                    ->where('adjustment_date', '<=', $asOfDate)
                                    ->where('obligation_amounts_id', $oa->id) // restrict per obligation_amount of this appropriation
                                : collect()
                        )
                        ->sum('adjustment_amount');
                            
                        $obligation = $obligationBase + $obligationAdjustments;

                        $appropriationBalance = $authorized - $obligation;
                        $appropriationAccomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                        $allotmentBalance = $allotment - $obligation;
                        $allotmentAccomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

                        if (!$classMap->has($classKey)) {
                            $classMap[$classKey] = (object) [
                                'description' => $oacClass->description,
                                'approved_appropriation' => 0,
                                'supplemental' => 0,
                                'reversion' => 0,
                                'realignment' => 0,
                                'authorized_appropriation' => 0,
                                'allotment' => 0,
                                'for_later_release' => 0,
                                'obligations' => 0,
                                'appropriation_balance' => 0,
                                'appropriation_accomplishment' => 0,
                                'allotment_balance' => 0,
                                'allotment_accomplishment' => 0,
                                'count' => 0,
                            ];
                        }

                        $row = $classMap[$classKey];
                        $row->approved_appropriation += $approved;
                        $row->supplemental += $supplemental;
                        $row->reversion += $reversion;
                        $row->realignment += $realignment;
                        $row->authorized_appropriation += $authorized;
                        $row->allotment += $allotment;
                        $row->for_later_release += $forLaterRelease;
                        $row->obligations += $obligation;
                        $row->appropriation_balance += $appropriationBalance;
                        $row->allotment_balance += $allotmentBalance;
                        $row->appropriation_accomplishment += $appropriationAccomplishment;
                        $row->allotment_accomplishment += $allotmentAccomplishment;
                        $row->count += 1;
                    }
                }

                // Average percentages
                foreach ($classMap as $classKey => $row) {
                    if ($row->count > 0) {
                        $row->appropriation_accomplishment /= $row->count;
                        $row->allotment_accomplishment /= $row->count;
                    }
                }

                $categoryClassStats[$category] = $classMap
                    ->sortBy(fn($row, $classCode) => $allotmentClasses[$classCode]->id ?? PHP_INT_MAX);
            }

            $baseFund->setAttribute('categoryClassStats', $categoryClassStats);

            $matched = [];

            foreach ($groupedByCategory as $category => $oacs) {
                $sectorsMap = collect();

                foreach ($oacs as $oac) {
                    $oacClass = $allotmentClasses->get($oac->class);

                    foreach ($oac->appropriations as $appropriation) {
                        $sector = $sectors->first(fn($s) =>
                            Str::startsWith($appropriation->fpp_code, $s->sector_code)
                        );

                        // If no sector is matched and the fund is "Special Education Fund", group by class only
                        if (!$sector) {
                            if ($type === 'Special Education Fund') {
                                $sectorCode = '';
                                $sector = (object) [
                                    'sector_code' => $sectorCode,
                                    'sector' => '',
                                    'present_allotment_classes' => collect(),
                                ];
                            } else {
                                continue; // skip if it's not SEF and sector not found
                            }
                        }

                        // Still skip if class is not found
                        if (!$oacClass) continue;

                        $sectorCode = $sector->sector_code;

                        if (!$sectorsMap->has($sectorCode)) {
                            $sectorClone = clone $sector;
                            $sectorClone->present_allotment_classes = collect();
                            $sectorsMap->put($sectorCode, $sectorClone);
                        }

                        $allotmentKey = $oacClass->class;

                        // Compute fields
                        $approved = $appropriation->appropriation;
                        // Supplemental & Reversion (filter by supplemental_date)
                        $supplemental = $appropriation->supplementals
                            ->where('type', 'Supplemental')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount');

                        $reversion = $appropriation->supplementals
                            ->where('type', 'Reversion')
                            ->where('supplemental_date', '<=', $asOfDate)
                            ->sum('amount') * -1;

                        // Realignments (filter by realignment_date)
                        $realignment = $appropriation->realignments
                            ->where('realignment_date', '<=', $asOfDate)
                            ->sum(fn($r) => $r->type === 'Source' ? -$r->amount : $r->amount);

                        // Authorized Appropriation
                        $authorized = $approved + $supplemental + $realignment + $reversion;

                        // Allotment (respect date-filtered adjustments too)
                        $allotment = ($appropriation->quarter1 + $appropriation->quarter2 + $appropriation->quarter3 + $appropriation->quarter4)
                            + $supplemental + $reversion + $realignment;

                        // For Later Release (unchanged, but still dynamic by quarter)
                        $forLaterRelease = $appropriation->for_later_release ?? (
                            ($currentQuarter < 2 ? ($appropriation->quarter2 ?? 0) : 0) +
                            ($currentQuarter < 3 ? ($appropriation->quarter3 ?? 0) : 0) +
                            ($currentQuarter < 4 ? ($appropriation->quarter4 ?? 0) : 0)
                        );

                        $allotment -= $forLaterRelease;

                        // Obligations (filter by obr_date)
                        $obligationBase = $appropriation->obligationAmounts
                            ->filter(fn($oa) => $oa->obligation && $oa->obligation->obr_date <= $asOfDate)
                            ->sum('obr_amount');

                        // --- Obligation Adjustments ---
                        $obligationAdjustments = $appropriation->obligationAmounts
                        ->flatMap(fn ($oa) =>
                            $oa->obligation
                                ? $oa->obligation->obligationAdjustments
                                    ->where('adjustment_date', '<=', $asOfDate)
                                    ->where('obligation_amounts_id', $oa->id) // restrict per obligation_amount of this appropriation
                                : collect()
                        )
                        ->sum('adjustment_amount');

                        $obligation = $obligationBase + $obligationAdjustments;

                        $appropriationBalance = $authorized - $obligation;
                        $appropriationAccomplishment = $authorized > 0 ? ($obligation / $authorized) * 100 : 0;
                        $allotmentBalance = $allotment - $obligation;
                        $allotmentAccomplishment = $allotment > 0 ? ($obligation / $allotment) * 100 : 0;

                        // Add or update
                        $existingClass = $sectorsMap[$sectorCode]->present_allotment_classes->get($allotmentKey);

                        if ($existingClass) {
                            $existingClass->approved_appropriation += $approved;
                            $existingClass->supplemental += $supplemental;
                            $existingClass->reversion += $reversion;
                            $existingClass->realignment += $realignment;
                            $existingClass->authorized_appropriation += $authorized;
                            $existingClass->allotment += $allotment;
                            $existingClass->for_later_release += $forLaterRelease;
                            $existingClass->obligations += $obligation;
                            $existingClass->appropriation_balance += $appropriationBalance;
                            $existingClass->appropriation_accomplishment = $existingClass->authorized_appropriation > 0
                                ? ($existingClass->obligations / $existingClass->authorized_appropriation) * 100 : 0;
                            $existingClass->allotment_balance += $allotmentBalance;
                            $existingClass->allotment_accomplishment = $existingClass->allotment > 0
                                ? ($existingClass->obligations / $existingClass->allotment) * 100 : 0;
                        } else {
                            $classClone = clone $oacClass;
                            $classClone->approved_appropriation = $approved;
                            $classClone->supplemental = $supplemental;
                            $classClone->reversion = $reversion;
                            $classClone->realignment = $realignment;
                            $classClone->authorized_appropriation = $authorized;
                            $classClone->allotment = $allotment;
                            $classClone->for_later_release = $forLaterRelease;
                            $classClone->obligations = $obligation;
                            $classClone->appropriation_balance = $appropriationBalance;
                            $classClone->appropriation_accomplishment = $appropriationAccomplishment;
                            $classClone->allotment_balance = $allotmentBalance;
                            $classClone->allotment_accomplishment = $allotmentAccomplishment;

                            $sectorsMap[$sectorCode]->present_allotment_classes->put($allotmentKey, $classClone);
                        }
                    }
                }

                $matched[$category] = $sectorsMap->sortBy('sector_code')->map(function ($sector) {
                    $sector->present_allotment_classes = $sector->present_allotment_classes
                        ->sortBy('id')
                        ->values();

                        // Compute totals per sector
                        $totals = [
                            'approved_appropriation' => 0,
                            'supplemental' => 0,
                            'reversion' => 0,
                            'realignment' => 0,
                            'authorized_appropriation' => 0,
                            'allotment' => 0,
                            'for_later_release' => 0,
                            'obligations' => 0,
                            'appropriation_balance' => 0,
                            'appropriation_accomplishment' => 0,
                            'allotment_balance' => 0,
                            'allotment_accomplishment' => 0,
                            'count' => 0,
                        ];

                        foreach ($sector->present_allotment_classes as $ac) {
                            $totals['approved_appropriation'] += $ac->approved_appropriation ?? 0;
                            $totals['supplemental'] += $ac->supplemental ?? 0;
                            $totals['reversion'] += $ac->reversion ?? 0;
                            $totals['realignment'] += $ac->realignment ?? 0;
                            $totals['authorized_appropriation'] += $ac->authorized_appropriation ?? 0;
                            $totals['allotment'] += $ac->allotment ?? 0;
                            $totals['for_later_release'] += $ac->for_later_release ?? 0;
                            $totals['obligations'] += $ac->obligations ?? 0;
                            $totals['appropriation_balance'] += $ac->appropriation_balance ?? 0;
                            $totals['appropriation_accomplishment'] = $totals['authorized_appropriation'] > 0
                                ? ($totals['obligations'] / $totals['authorized_appropriation']) * 100
                                : 0;
                            $totals['allotment_balance'] += $ac->allotment_balance ?? 0;
                            $totals['allotment_accomplishment'] = $totals['allotment'] > 0
                                ? ($totals['obligations'] / $totals['allotment']) * 100
                                : 0;
                            $totals['count']++;
                        }

                        $sector->totals = (object) $totals;
                    return $sector;
                })->values();
            }

            $baseFund->setAttribute('matchedSectorsByCategory', collect($matched));

            // Compute category-level totals
            $categoryTotals = collect();

            foreach ($matched as $category => $sectors) {
                $totals = [
                    'approved_appropriation' => 0,
                    'supplemental' => 0,
                    'reversion' => 0,
                    'realignment' => 0,
                    'authorized_appropriation' => 0,
                    'allotment' => 0,
                    'for_later_release' => 0,
                    'obligations' => 0,
                    'appropriation_balance' => 0,
                    'appropriation_accomplishment' => 0,
                    'allotment_balance' => 0,
                    'allotment_accomplishment' => 0,
                    'count' => 0,
                ];

                foreach ($sectors as $sector) {
                    foreach ($totals as $key => $val) {
                        if ($key !== 'count') $totals[$key] += $sector->totals->$key ?? 0;
                    }
                    $totals['count']++;
                }

                $totals['appropriation_accomplishment'] = $totals['authorized_appropriation'] > 0
                                ? ($totals['obligations'] / $totals['authorized_appropriation']) * 100
                                : 0;

                $totals['allotment_accomplishment'] = $totals['allotment'] > 0
                                ? ($totals['obligations'] / $totals['allotment']) * 100
                                : 0;

                $categoryTotals[$category] = (object) $totals;
            }

            $baseFund->setAttribute('categoryTotals', $categoryTotals);

            // Compute grand total across all categories
            $grandTotal = [
                'approved_appropriation' => 0,
                'supplemental' => 0,
                'reversion' => 0,
                'realignment' => 0,
                'authorized_appropriation' => 0,
                'allotment' => 0,
                'for_later_release' => 0,
                'obligations' => 0,
                'appropriation_balance' => 0,
                'appropriation_accomplishment' => 0,
                'allotment_balance' => 0,
                'allotment_accomplishment' => 0,
                'count' => 0,
            ];

            foreach ($categoryTotals as $totals) {
                foreach ($grandTotal as $key => $val) {
                    if ($key !== 'count') $grandTotal[$key] += $totals->$key ?? 0;
                }
                $grandTotal['count']++;
            }


            $grandTotal['appropriation_accomplishment'] = $grandTotal['authorized_appropriation'] > 0
                                ? ($grandTotal['obligations'] / $grandTotal['authorized_appropriation']) * 100
                                : 0;

            $grandTotal['allotment_accomplishment'] = $grandTotal['allotment'] > 0
                                ? ($grandTotal['obligations'] / $grandTotal['allotment']) * 100
                                : 0;

            $baseFund->setAttribute('grandTotal', (object) $grandTotal);

            return $baseFund;
        })->values();

        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('saaobfundsector.index', compact(
            'availableYears',
            'allFunds',
            'selectedYear',
            'selectedFund',
            'asOfDate',
            'employees',
            'funds',
            'groupedFunds',
            'sectors'
        ))->with('status', session('status'));
    }

    


    public function exportExcel(Request $request)
        {
            $year = $request->input('year1');
            $fund = $request->input('fund_filter');
            $asOf = $request->input('as_of_filter');
            $signatoryName = $request->input('signatory_name');
            $signatoryDesignation = $request->input('signatory_designation');

           // Sanitize fund name for filename
            $fundName = 'All_Funds';

            if (!empty($fund)) {
                if ($fund === 'others') {
                    $fundName = 'BEGHEE_SEF';
                } else {
                    $fundName = preg_replace('/[^A-Za-z0-9_]/', '_', $fund);
                }
            }

            $fileName = 'SAAOB_' . $fundName . '_' . $year . '.xlsx';

            return Excel::download(new SAAOBFundSectorExport(
                $year,
                $fund,
                $asOf,
                $signatoryName,
                $signatoryDesignation
            ), $fileName);
        }
}
