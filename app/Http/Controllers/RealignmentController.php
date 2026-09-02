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
        // Whitelist sortable columns; 'office_allotment_class' has no matching column, so it sorts by the FK instead.
        $sortColumnMap = [
            'office_allotment_class' => 'office_allotment_classes_id',
            'realignment_no' => 'realignment_no',
            'realignment_date' => 'realignment_date',
            'type' => 'type',
            'basis' => 'basis',
        ];
        $query->orderBy($sortColumnMap[$sortBy] ?? 'id', $sortOrder);
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

        // Used by the CREATE modal 
        $office_allotment_classes_for_create = DB::table('office_allotment_classes')
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->where('year', $selectedYear)
            ->get();

        // Used by the EDIT modal 
        $office_allotment_classes_for_edit = DB::table('office_allotment_classes')
            ->select('id', 'office_abbreviation', 'class', 'fund')
            ->get();

        $officeAllotmentClassesJs = collect($office_allotment_classes_for_create)->map(function ($oac) {
            return [
                'id' => $oac->id,
                'name' => ($oac->office_abbreviation ?? '') . ' - ' . ($oac->class ?? ''),
                'fund' => $oac->fund ?? 'General Fund',
            ];
        })->values();

        $officeAllotmentClassesAllJs = collect($office_allotment_classes_for_edit)->map(function ($oac) {
            return [
                'id' => $oac->id,
                'name' => ($oac->office_abbreviation ?? '') . ' - ' . ($oac->class ?? ''),
                'fund' => $oac->fund ?? 'General Fund',
            ];
        })->values();

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

        $realignmentsBulkDelete = Realignment::with('appropriation')
            ->when($request->filled('year1'), function ($q) use ($request) {
                $q->whereHas('officeAllotmentClass', function ($q2) use ($request) {
                    $q2->where('year', $request->year1);
                });
            })
            ->get();

        // Get total count of realignments based on filters (grouped by realignment_no)
        $totalRecords = Realignment::with('appropriation.officeAllotmentClass')
            ->whereHas('officeAllotmentClass', function ($q) use ($selectedYear) {
                $q->where('year', $selectedYear);
            })
            ->when($request->filled('office_allotment_class_id'), function ($q) use ($request) {
                return $q->where('office_allotment_classes_id', $request->office_allotment_class_id);
            })
            ->when($request->filled('realignment_type_filter'), function ($q) use ($request) {
                return $q->where('type', $request->realignment_type_filter);
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($subQ) use ($search) {
                    $subQ->where('realignment_no', 'like', "%{$search}%")
                        ->orWhere('realignment_date', 'like', "%{$search}%")
                        ->orWhere('amount', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('basis', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->distinct('realignment_no')
            ->count('realignment_no');


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
            'officeAllotmentClassesAllJs',
            'appropriations',
            'officeAllotmentClassesJs',
            'appropriationsJs',
            'realignmentsBulkDelete',
            'totalRecords'
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
            $realignment_id = $request->input('realignment_id');
            $realignment = Realignment::find($realignment_id);

            if (!$realignment) {
                Log::warning('No realignment found for update', ['realignment_id' => $realignment_id]);
                return redirect()->back()->withInput()->with('status', 'No realignment found to update.');
            }

            $type = $realignment->type;
            
            // CRITICAL STEP 1: Store the original Realignment Number before any updates.
            // This is the common key for all related rows.
            $originalRealignmentNo = $realignment->realignment_no;

            // 2. Validate shared and type-specific fields
            $rules = [
                'edit_realignment_no' => 'required|string|max:255',
                'edit_realignment_date' => 'required|date',
                'edit_basis' => 'required|string|max:255',
            ];
            // ... (rest of type-specific rule setting logic)
            if ($type === 'Source') {
                if (!$request->has('edit_source_appropriations_id') && $request->has('appropriations_id')) {
                    $request->merge(['edit_source_appropriations_id' => $request->input('appropriations_id')]);
                }
                $rules = array_merge($rules, [
                    'edit_source_office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
                    'edit_source_appropriations_id' => 'required|exists:appropriations,id',
                    'edit_source_amount' => 'required|numeric|min:0',
                ]);
            } elseif ($type === 'Recipient') {
                if (!$request->has('edit_recipient_appropriations_id') && $request->has('appropriations_id')) {
                    $request->merge(['edit_recipient_appropriations_id' => $request->input('appropriations_id')]);
                }
                $rules = array_merge($rules, [
                    'edit_recipient_office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
                    'edit_recipient_appropriations_id' => 'required|exists:appropriations,id',
                    'edit_recipient_amount' => 'required|numeric|min:0',
                ]);
            }
            $request->validate($rules);

            // 3. Prepare data for updates
            $sharedUpdateData = [
                'realignment_no' => $request->input('edit_realignment_no'),
                'realignment_date' => $request->input('edit_realignment_date'),
                'basis' => $request->input('edit_basis'),
            ];

            $lineItemUpdateData = $sharedUpdateData;
            
            // ... (rest of type-specific data merging logic)
            if ($type === 'Source') {
                $lineItemUpdateData = array_merge($lineItemUpdateData, [
                    'office_allotment_classes_id' => $request->input('edit_source_office_allotment_class_id'),
                    'appropriations_id' => $request->input('edit_source_appropriations_id'),
                    'amount' => $request->input('edit_source_amount'),
                ]);
            } elseif ($type === 'Recipient') {
                $lineItemUpdateData = array_merge($lineItemUpdateData, [
                    'office_allotment_classes_id' => $request->input('edit_recipient_office_allotment_class_id'),
                    'appropriations_id' => $request->input('edit_recipient_appropriations_id'),
                    'amount' => $request->input('edit_recipient_amount'),
                ]);
            }

            // 4. Update the specific line item (Realignment row)
            $realignment->update($lineItemUpdateData);
            
            // 5. CRITICAL STEP 2: Update ALL related line items (peers) with the same administrative data.
            // Use the original number to find all related entries.
            // We exclude the current row because it was already updated, but we update the other rows
            // that share the transaction ID (originalRealignmentNo).
            
            if ($originalRealignmentNo !== $request->input('edit_realignment_no')) {
                // Case A: The Realignment No. was changed. 
                // We need to update all rows that previously had this number.
                Realignment::where('realignment_no', $originalRealignmentNo)
                    ->where('id', '!=', $realignment_id)
                    ->update($sharedUpdateData);
            } else {
                // Case B: Only Date/Basis changed, but Realignment No. stayed the same.
                // We update all peer rows with the new shared data.
                Realignment::where('realignment_no', $originalRealignmentNo)
                    ->where('id', '!=', $realignment_id)
                    ->update($sharedUpdateData);
            }
            
            Log::info('Realignment updated. Shared administrative data synchronized across related entries.', [
                'id' => $realignment->id,
                'original_no' => $originalRealignmentNo,
                'new_no' => $request->input('edit_realignment_no'),
            ]);

            // 6. Redirect with status
            return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
            ->with('status', 'Realignment No.: <strong>' . $request->input('edit_realignment_no') . '</strong> with Type: <strong>' . $realignment->type . '</strong> has been updated successfully! Shared administrative data was also synchronized.');

        } catch (\Throwable $e) {
            Log::error('Realignment Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return redirect()->back()->withInput()->with('status', 'An error occurred while updating the realignment: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Realignment $realignment): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store details before deletion
            $realignmentNo = $realignment->realignment_no;
            $type = $realignment->type;
            $amount = $realignment->amount;
            $appropriation = Appropriation::find($realignment->appropriations_id);
            $accountCode = $appropriation ? $appropriation->account_code : 'N/A';
            $description = $appropriation ? $appropriation->description : 'N/A';

            // Check if this is a bulk delete request
            $isBulkDelete = $request->has('bulk_delete') && $request->bulk_delete == '1';

            if ($isBulkDelete) {
                // Delete all realignments with the same realignment_no
                $relatedRealignments = Realignment::where('realignment_no', $realignmentNo)->get();
                $deletedCount = $relatedRealignments->count();
                
                // Check if any related realignment has obligations created after the realignment
                foreach ($relatedRealignments as $related) {
                    $appropriationCheck = Appropriation::find($related->appropriations_id);
                    if ($appropriationCheck) {
                        // Get obligations and check their dates
                        $obligationAmounts = ObligationAmount::where('appropriation_id', $appropriationCheck->id)
                            ->with('obligation')
                            ->get();
                        
                        // Check if any obligation was created after (or on same date as) the realignment
                        $realignmentDate = \Carbon\Carbon::parse($related->realignment_date);
                        $blockedObligations = 0;
                        
                        foreach ($obligationAmounts as $oa) {
                            if ($oa->obligation) {
                                $obligationDate = \Carbon\Carbon::parse($oa->obligation->obr_date);
                                if (!$obligationDate->lt($realignmentDate)) {
                                    $blockedObligations++;
                                }
                            }
                        }
                        
                        if ($blockedObligations > 0) {
                            DB::rollBack();
                            return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
                                ->with('error', 
                                    "Cannot delete Realignment No: <strong>{$realignmentNo}</strong>. " .
                                    "One or more accounts in this realignment transaction have <strong>{$blockedObligations}</strong> obligation(s) created after the realignment date. " .
                                    "Please delete the related obligations first before removing this realignment."
                                );
                        }
                    }
                }

                // Delete all related realignments
                Realignment::where('realignment_no', $realignmentNo)->delete();

                DB::commit();

                return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
                    ->with('status',
                        "All <strong>{$deletedCount}</strong> realignment(s) with Realignment No: <strong>{$realignmentNo}</strong> have been deleted successfully!"
                    );
            } else {
                // Single delete - check for linked records
                $relatedCount = Realignment::where('realignment_no', $realignmentNo)
                    ->where('id', '!=', $realignment->id)
                    ->count();

                // Check if appropriation has obligations created after the realignment
                if ($appropriation) {
                    $obligationAmounts = ObligationAmount::where('appropriation_id', $appropriation->id)
                        ->with('obligation')
                        ->get();
                    
                    // Check if any obligation was created after (or on same date as) the realignment
                    $realignmentDate = \Carbon\Carbon::parse($realignment->realignment_date);
                    $blockedObligations = 0;
                    
                    foreach ($obligationAmounts as $oa) {
                        if ($oa->obligation) {
                            $obligationDate = \Carbon\Carbon::parse($oa->obligation->obr_date);
                            if (!$obligationDate->lt($realignmentDate)) {
                                $blockedObligations++;
                            }
                        }
                    }
                    
                    if ($blockedObligations > 0) {
                        DB::rollBack();
                        return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
                            ->with('error', 
                                "Cannot delete Realignment No: <strong>{$realignmentNo}</strong> with Type: <strong>{$type}</strong>, Account Code: <strong>{$accountCode}</strong>. " .
                                "This realignment has <strong>{$blockedObligations}</strong> obligation(s) created after the realignment date. " .
                                "Please delete the related obligations first before removing this realignment."
                            );
                    }
                }

                // Single delete
                $realignment->delete();

                DB::commit();

                $warningMessage = '';
                if ($relatedCount > 0) {
                    $warningMessage = " <strong>Note:</strong> There are still <strong>{$relatedCount}</strong> related realignment(s) with the same Realignment No.";
                }

                return redirect()->route('realignments.index', $request->only(['year1', 'office_allotment_class_id', 'realignment_type_filter', 'per_page', 'search']))
                    ->with('status',
                        'Realignment No: <strong>' . $realignmentNo . '</strong> with Type: <strong>' . $type . '</strong>, Account Code: <strong>' . $accountCode . '</strong> - <strong>' . $description . '</strong> and Amount: <strong>' . number_format($amount, 2) . '</strong> has been deleted successfully! </br>' . $warningMessage
                    );
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Realignment Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'realignment_id' => $realignment->id ?? null
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the realignment: ' . $e->getMessage());
        }
    }

    /**
     * Check if realignment can be deleted based on obligation date
     */
    public function checkRealignmentDeletionDate(Request $request)
    {
        $validated = $request->validate([
            'realignment_id' => 'required|integer',
        ]);

        $realignment = Realignment::find($validated['realignment_id']);
        if (!$realignment) {
            return response()->json([
                'canDelete' => false,
                'message' => 'Realignment not found'
            ], 404);
        }

        // Get the appropriation and related obligations
        $appropriation = Appropriation::find($realignment->appropriations_id);
        if (!$appropriation) {
            return response()->json([
                'canDelete' => false,
                'message' => 'Appropriation not found'
            ], 404);
        }

        // Get the earliest obligation date for this appropriation
        $earliestObligation = ObligationAmount::where('appropriation_id', $appropriation->id)
            ->with('obligation')
            ->get()
            ->map(function ($oam) {
                return $oam->obligation;
            })
            ->filter()
            ->sortBy('obr_date')
            ->first();

        // If no obligations exist, allow deletion
        if (!$earliestObligation) {
            return response()->json([
                'canDelete' => true,
                'message' => 'No obligations associated with this realignment'
            ]);
        }

        // Convert realignment_date to comparable format
        $realignmentDate = \Carbon\Carbon::parse($realignment->realignment_date);
        $obligationDate = \Carbon\Carbon::parse($earliestObligation->obr_date);

        // Realignment can be deleted if obligation was created BEFORE realignment
        $canDelete = $obligationDate->lt($realignmentDate);

        return response()->json([
            'canDelete' => $canDelete,
            'message' => $canDelete 
                ? 'Realignment can be deleted' 
                : 'Cannot delete: Obligation was created after or on the same date as this realignment',
            'realignment_date' => $realignmentDate->format('Y-m-d'),
            'earliest_obligation_date' => $obligationDate->format('Y-m-d')
        ]);
    }
}
