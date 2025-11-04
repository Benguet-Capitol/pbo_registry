<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AllotmentClass;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\OfficeAllotmentClass;
use App\Models\Realignment;
use App\Models\Supplemental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;


class AllotmentClassController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        // Query allotment classes
        $query = AllotmentClass::query();

        if ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $allotment_classes = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $allotment_classes = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
            
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Allotment Classes']
        ];

        return view('allotment_classes.index', compact('allotment_classes', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))
            ->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class' => 'required|string|max:255|unique:allotment_classes,class',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:255',
        ]);

        AllotmentClass::create($validated);

        return redirect()->route('allotment_classes.index')->with('status', 'Allotment Class <strong>' . $validated['description'] . '</strong> with category <strong>' . $validated['category'] . '</strong> has been created successfully!');
    }

    public function update(Request $request, AllotmentClass $allotment_class): RedirectResponse
    {
        $validated = $request->validate([
            'edit_class' => 'required|string|max:255|unique:allotment_classes,class,' . $allotment_class->id,
            'edit_description' => 'required|string|max:255',
            'edit_category' => 'required|string|max:255',
        ]);

        $allotment_class->update([
            'class' => $validated['edit_class'],
            'description' => $validated['edit_description'],
            'category' => $validated['edit_category']
        ]);

        return redirect()->route('allotment_classes.index')->with('status', 'Allotment Class <strong>' . $validated['edit_description'] . '</strong> with category <strong>' . $validated['edit_category'] . '</strong> has been updated successfully!');
    }

    public function destroy(Request $request, AllotmentClass $allotment_class): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store details before deletion
            $description = $allotment_class->description;
            $category = $allotment_class->category;

            // System Validation: Check if allotment class is in use

            // 1. Check if allotment class is used in Office Allotment Classes
            $officeAllotmentClassesCount = OfficeAllotmentClass::where('class', $allotment_class->class)->count();
            
            if ($officeAllotmentClassesCount > 0) {
                DB::rollBack();
                return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Allotment Class <strong>{$description}</strong>. " .
                        "This allotment class is currently assigned to <strong>{$officeAllotmentClassesCount}</strong> Office Allotment Class(es). " .
                        "Please remove or reassign the office allotment classes first before deleting this allotment class."
                    );
            }

            // 2. Check if allotment class is referenced through Office Allotment Classes in Appropriations
            $appropriationsCount = Appropriation::whereHas('officeAllotmentClass', function($query) use ($allotment_class) {
                $query->where('class', $allotment_class->class);
            })->count();
            
            if ($appropriationsCount > 0) {
                DB::rollBack();
                return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Allotment Class <strong>{$description}</strong>. " .
                        "This allotment class has <strong>{$appropriationsCount}</strong> appropriation(s) associated with it through Office Allotment Classes. " .
                        "Please reassign or remove the appropriations first before deleting this allotment class."
                    );
            }

            // 3. Check if allotment class is referenced in Obligations through Appropriations
            $obligationsCount = ObligationAmount::whereHas('appropriation.officeAllotmentClass', function($query) use ($allotment_class) {
                $query->where('class', $allotment_class->class);
            })->distinct('obligation_id')->count('obligation_id');
            
            if ($obligationsCount > 0) {
                DB::rollBack();
                return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Allotment Class <strong>{$description}</strong>. " .
                        "This allotment class has <strong>{$obligationsCount}</strong> obligation(s) associated with it through appropriations. " .
                        "Please delete the related obligations first before removing this allotment class."
                    );
            }

            // 4. Check if allotment class is referenced in Realignments through Appropriations
            $realignmentsCount = Realignment::whereHas('appropriation.officeAllotmentClass', function($query) use ($allotment_class) {
                $query->where('class', $allotment_class->class);
            })->count();
            
            if ($realignmentsCount > 0) {
                DB::rollBack();
                return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Allotment Class <strong>{$description}</strong>. " .
                        "This allotment class has <strong>{$realignmentsCount}</strong> realignment/augmentation(s) associated with it. " .
                        "Please delete the related realignments first before removing this allotment class."
                    );
            }

            // 5. Check if allotment class is referenced in Supplementals through Appropriations
            $supplementalsCount = Supplemental::whereHas('appropriation.officeAllotmentClass', function($query) use ($allotment_class) {
                $query->where('class', $allotment_class->class);
            })->count();
            
            if ($supplementalsCount > 0) {
                DB::rollBack();
                return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Allotment Class <strong>{$description}</strong>. " .
                        "This allotment class has <strong>{$supplementalsCount}</strong> supplemental/reversion(s) associated with it. " .
                        "Please delete the related supplementals first before removing this allotment class."
                    );
            }

            // All validations passed - proceed with deletion
            $allotment_class->delete();

            DB::commit();

            return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                ->with('status', 
                    'Allotment Class <strong>' . $description . '</strong> with category <strong>' . $category . '</strong> has been deleted successfully!'
                );

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Allotment Class Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'allotment_class_id' => $allotment_class->id ?? null,
                'description' => $allotment_class->description ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->route('allotment_classes.index', array_filter($request->only(['per_page', 'search'])))
                ->with('error', 'An error occurred while deleting the allotment class: ' . $e->getMessage());
        }
    }
}
