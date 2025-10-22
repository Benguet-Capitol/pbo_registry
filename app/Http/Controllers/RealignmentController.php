<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Realignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\OfficeAllotmentClass;
use Illuminate\Support\Facades\DB;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\ObligationAdjustment;
use App\Models\Supplemental;
use Illuminate\Support\Facades\Log;

class RealignmentController extends Controller
{
    public function index(Request $request): View
    {
        // --- Basic Filters ---
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

        $currentYear = date('Y');
        $selectedYear = $request->input('year1', $currentYear);

        // --- Base Query: Eager load all related data in one go ---
        $query = Realignment::with([
            'appropriation.officeAllotmentClass.offices',
            'appropriation.officeAllotmentClass.allotmentClass'
        ])->whereHas('officeAllotmentClass', function ($q) use ($selectedYear) {
            $q->where('year', $selectedYear);
        });

        // --- Apply Filters ---
        if ($request->filled('office_allotment_class_id')) {
            $query->where('office_allotment_classes_id', $request->office_allotment_class_id);
        }

        if ($request->filled('realignment_type_filter')) {
            $query->where('type', $request->realignment_type_filter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('realignment_no', 'like', "%{$search}%")
                    ->orWhere('realignment_date', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('basis', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        // --- Sorting & Pagination ---
        $query->orderBy($sortBy, $sortOrder);
        $realignments = $perPage === 'all'
            ? $query->get()
            : $query->paginate($perPage)->appends([
                'year1' => $selectedYear,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'office_allotment_class_id' => $request->office_allotment_class_id,
                'realignment_type' => $request->realignment_type,
                'per_page' => $perPage,
            ]);

        // --- Reference Data ---
        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        $officeAllotmentClasses = OfficeAllotmentClass::with(['offices', 'allotmentClass'])
            ->where('year', $selectedYear)
            ->orderBy('office', 'asc')
            ->get();

        $office_allotment_classes = DB::table('office_allotment_classes')
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->where('year', $currentYear)
            ->get();

        // --- Optimized Appropriations Computation ---
        $currentMonth = now()->month;
        $currentQuarter = ceil($currentMonth / 3);

        // Preload all related data in bulk
        $appropriations = Appropriation::with([
            'obligationAmounts.obligationAdjustments',
            'supplementals',
            'realignments',
        ])->get()->map(function ($appropriation) use ($currentQuarter) {

            // Compute total appropriation (up to current quarter)
            $totalAppropriation = collect([
                $appropriation->quarter1,
                $appropriation->quarter2,
                $appropriation->quarter3,
                $appropriation->quarter4
            ])->take($currentQuarter)->sum();

            // Sum OBRs and adjustments
            $totalObrAmount = $appropriation->obligationAmounts->sum(function ($oa) {
                $adj = $oa->obligationAdjustments->sum('adjustment_amount');
                return $oa->obr_amount + $adj;
            });

            // Supplemental & Reversion
            $supplementalSum = $appropriation->supplementals->where('type', 'Supplemental')->sum('amount');
            $reversionSum = $appropriation->supplementals->where('type', 'Reversion')->sum('amount');

            // Realignments (Recipient adds, Source subtracts)
            $realignmentSum = $appropriation->realignments->sum(function ($r) {
                return $r->type === 'Source' ? -$r->amount : $r->amount;
            });

            // Compute Balance
            $appropriation->balance = ($totalAppropriation - $totalObrAmount + $supplementalSum - $reversionSum) + $realignmentSum;

            // Adjust total OBR
            $appropriation->total_obr = $totalObrAmount + $supplementalSum - $reversionSum;

            return $appropriation;
        });

        // --- Prepare Data for JS (same variables preserved) ---
        $officeAllotmentClassesJs = collect($office_allotment_classes)->map(function ($oac) {
            return [
                'id' => $oac->id,
                'name' => ($oac->office_abbreviation ?? '') . ' - ' . ($oac->class ?? ''),
                'fund' => $oac->fund ?? 'General Fund',
            ];
        })->values();

        $appropriationsJs = collect($appropriations)->map(function ($app) {
            return [
                'id' => $app->id,
                'account_code' => $app->account_code,
                'program' => $app->programs,
                'description' => $app->description,
                'office_allotment_class_id' => $app->office_allotment_class_id,
                'balance' => $app->balance,
            ];
        })->values();

        // --- Breadcrumb ---
        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Realignments | Augmentations']
        ];

        // --- Return View ---
        return view('realignments.index', compact(
            'realignments',
            'perPage',
            'search',
            'sortBy',
            'sortOrder',
            'breadcrumb',
            'availableYears',
            'selectedYear',
            'officeAllotmentClasses',
            'office_allotment_classes',
            'appropriations',
            'officeAllotmentClassesJs',
            'appropriationsJs'
        ));
    }

    public function store(Request $request): RedirectResponse
    {

        try {
            // Normalize values to arrays
            $fields = [
                'source_office_allotment_class_id',
                'source_appropriations_id',
                'source_amount',
                'recipient_office_allotment_class_id',
                'recipient_appropriations_id',
                'recipient_amount',
            ];
            foreach ($fields as $field) {
                if ($request->has($field) && !is_array($request->input($field))) {
                    $request->merge([$field => [$request->input($field)]]);
                }
            }

            $section = $request->input('section_select', 'both');
            $rules = [
                'realignment_no' => 'required|string|max:255',
                'realignment_date' => 'required|date',
                'basis' => 'required|string|max:255',
            ];
            if ($section === 'source' || $section === 'both') {
                $rules = array_merge($rules, [
                    'source_office_allotment_class_id' => 'required|array',
                    'source_office_allotment_class_id.*' => 'required|exists:office_allotment_classes,id',
                    'source_appropriations_id' => 'required|array',
                    'source_appropriations_id.*' => 'required|exists:appropriations,id',
                    'source_amount' => 'required|array',
                    'source_amount.*' => 'required|numeric',
                ]);
            }
            if ($section === 'recipient' || $section === 'both') {
                $rules = array_merge($rules, [
                    'recipient_office_allotment_class_id' => 'required|array',
                    'recipient_office_allotment_class_id.*' => 'required|exists:office_allotment_classes,id',
                    'recipient_appropriations_id' => 'required|array',
                    'recipient_appropriations_id.*' => 'required|exists:appropriations,id',
                    'recipient_amount' => 'required|array',
                    'recipient_amount.*' => 'required|numeric',
                ]);
            }
            $request->validate($rules);

            $shared = [
                'realignment_no' => $request->realignment_no,
                'realignment_date' => $request->realignment_date,
                'basis' => $request->basis,
            ];


            // Only process source if present
            $source_oac = $request->input('source_office_allotment_class_id');
            $source_app = $request->input('source_appropriations_id');
            $source_amt = $request->input('source_amount');
            if (is_array($source_oac) && is_array($source_app) && is_array($source_amt)) {
                $maxSource = max(count($source_oac), count($source_app), count($source_amt));
                for ($i = 0; $i < $maxSource; $i++) {
                    Realignment::create(array_merge($shared, [
                        'office_allotment_classes_id' => $source_oac[$i] ?? $source_oac[0],
                        'appropriations_id' => $source_app[$i] ?? $source_app[0],
                        'amount' => $source_amt[$i] ?? $source_amt[0],
                        'type' => 'Source',
                    ]));
                }
            }

            // Only process recipient if present
            $recipient_oac = $request->input('recipient_office_allotment_class_id');
            $recipient_app = $request->input('recipient_appropriations_id');
            $recipient_amt = $request->input('recipient_amount');
            if (is_array($recipient_oac) && is_array($recipient_app) && is_array($recipient_amt)) {
                $maxRecipient = max(count($recipient_oac), count($recipient_app), count($recipient_amt));
                for ($i = 0; $i < $maxRecipient; $i++) {
                    Realignment::create(array_merge($shared, [
                        'office_allotment_classes_id' => $recipient_oac[$i] ?? $recipient_oac[0],
                        'appropriations_id' => $recipient_app[$i] ?? $recipient_app[0],
                        'amount' => $recipient_amt[$i] ?? $recipient_amt[0],
                        'type' => 'Recipient',
                    ]));
                }
            }

            return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
            ->with('status', 'Realignment No.: <strong>' . $request->realignment_no . '</strong> has been created successfully!');
        } catch (\Throwable $e) {
            Log::error('Realignment Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->withInput()->with('status', 'An error occurred while saving the realignment: ' . $e->getMessage());
        }
    }

    public function update(Request $request): RedirectResponse
    {
        try {
            // Use only realignment_id to fetch the row
            $realignment_id = $request->input('realignment_id');
            $realignment = Realignment::find($realignment_id);
            if (!$realignment) {
                Log::warning('No realignment found for update', [
                    'realignment_id' => $realignment_id,
                ]);
                return redirect()->back()->withInput()->with('status', 'No realignment found to update.');
            }
            $type = $realignment->type;
            // Always validate shared fields
            $rules = [
                'edit_realignment_no' => 'required|string|max:255',
                'edit_realignment_date' => 'required|date',
                'edit_basis' => 'required|string|max:255',
            ];
            // Set appropriations_id from hidden input if not present
            if ($type === 'Source') {
                if (!$request->has('edit_source_appropriations_id') && $request->has('appropriations_id')) {
                    $request->merge(['edit_source_appropriations_id' => $request->input('appropriations_id')]);
                }
                $rules = array_merge($rules, [
                    'edit_source_office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
                    'edit_source_appropriations_id' => 'required|exists:appropriations,id',
                    'edit_source_amount' => 'required',
                ]);
            } elseif ($type === 'Recipient') {
                if (!$request->has('edit_recipient_appropriations_id') && $request->has('appropriations_id')) {
                    $request->merge(['edit_recipient_appropriations_id' => $request->input('appropriations_id')]);
                }
                $rules = array_merge($rules, [
                    'edit_recipient_office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
                    'edit_recipient_appropriations_id' => 'required|exists:appropriations,id',
                    'edit_recipient_amount' => 'required|numeric',
                ]);
            }
            $request->validate($rules);
            $updateData = [
                'realignment_no' => $request->input('edit_realignment_no'),
                'realignment_date' => $request->input('edit_realignment_date'),
                'basis' => $request->input('edit_basis'),
            ];
            if ($type === 'Source') {
                $updateData = array_merge($updateData, [
                    'office_allotment_classes_id' => $request->input('edit_source_office_allotment_class_id'),
                    'appropriations_id' => $request->input('edit_source_appropriations_id'),
                    'amount' => $request->input('edit_source_amount'),
                ]);
            } elseif ($type === 'Recipient') {
                $updateData = array_merge($updateData, [
                    'office_allotment_classes_id' => $request->input('edit_recipient_office_allotment_class_id'),
                    'appropriations_id' => $request->input('edit_recipient_appropriations_id'),
                    'amount' => $request->input('edit_recipient_amount'),
                ]);
            }
            $realignment->update($updateData);
            Log::info('Realignment updated', [
                'id' => $realignment->id,
                'type' => $realignment->type,
                'updateData' => $updateData,
            ]);
            // Update Appropriation account_code if present in request
            if ($type === 'Source' && $request->has('edit_source_account_code')) {
                $appId = $request->input('edit_source_appropriations_id');
                $accountCode = $request->input('edit_source_account_code');
                if ($appId && $accountCode) {
                    $app = Appropriation::find($appId);
                    if ($app) {
                        $app->account_code = $accountCode;
                        $app->save();
                    }
                }
            } elseif ($type === 'Recipient' && $request->has('edit_recipient_account_code')) {
                $appId = $request->input('edit_recipient_appropriations_id');
                $accountCode = $request->input('edit_recipient_account_code');
                if ($appId && $accountCode) {
                    $app = Appropriation::find($appId);
                    if ($app) {
                        $app->account_code = $accountCode;
                        $app->save();
                    }
                }
            }
            return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
            ->with('status', 'Realignment No.: <strong>' . $request->input('edit_realignment_no') . '</strong> with Type: <strong>' . $realignment->type . '</strong> has been updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Realignment Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->withInput()->with('status', 'An error occurred while updating the realignment: ' . $e->getMessage());
        }
    }

    public function destroy(Realignment $realignment): RedirectResponse
    {
        try {
            // Get the related appropriation
            $appropriation = Appropriation::find($realignment->appropriations_id);
            $accountCode = $appropriation ? $appropriation->account_code : '';
            $description = $appropriation ? $appropriation->description : '';

            $realignment->delete();

            return redirect()->route('realignments.index', request()->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
                ->with('status',
                'Realignment No.: <strong>' . $realignment->realignment_no . '</strong> with Type: <strong>' . $realignment->type . '</strong>, Account Code: <strong>' . $accountCode . '</strong> - <strong>' . $description . '</strong> and Amount: <strong>' . number_format($realignment->amount, 2) . '</strong> has been deleted successfully!'
            );
        } catch (\Throwable $e) {
            Log::error('Realignment Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'realignment_id' => $realignment->id
            ]);
            return redirect()->back()->with('status', 'An error occurred while deleting the realignment: ' . $e->getMessage());
        }
    }
}
