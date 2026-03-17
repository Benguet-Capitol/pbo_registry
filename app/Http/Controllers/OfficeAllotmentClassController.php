<?php

namespace App\Http\Controllers;

use App\Models\AllotmentClass;
use App\Models\Appropriation;
use App\Models\FundSource;
use App\Models\Office;
use App\Models\OfficeAllotmentClass;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class OfficeAllotmentClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');

        // Get the selected year or default to the current year
        $currentYear = date('Y');
        $selectedYear = $request->input('year1', $currentYear);

        // Get sorting parameters
        $sortBy = $request->query('sort_by', 'office'); // Default to 'id'
        $sortOrder = $request->query('sort_order', 'asc'); // Default to 'desc'

        // Query office_allotment_classes based on the selected year
        $query = OfficeAllotmentClass::where('year', $selectedYear)->with('allotmentClass');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('year', 'like', "%{$search}%")
                    ->orWhere('office', 'like', "%{$search}%")
                    ->orWhere('fund', 'like', "%{$search}%")
                    ->orWhere('fund_source', 'like', "%{$search}%")
                    ->orWhere('class', 'like', "%{$search}%")
                    ->orWhere('fpp_code', 'like', "%{$search}%")
                    ->orWhere('responsibility_code', 'like', "%{$search}%");
            });
        }

        // Apply filters for office, allotment class, and fund source
        $officeFilter = $request->input('office_filter');
        $allotmentClassFilter = $request->input('allotment_class_filter');
        $fundSourceFilter = $request->input('fund_source_filter');

        if ($officeFilter) {
            $query->where('office', $officeFilter);
        }
        if ($allotmentClassFilter) {
            $query->where('class', $allotmentClassFilter);
        }
        if ($fundSourceFilter) {
            $query->where('fund_source', $fundSourceFilter);
        }

        // Apply sorting
        if ($sortBy === 'total_appropriation') {
            // Fetch the data first to calculate total appropriation
            $office_allotment_classes = $query->get();

            // Calculate total appropriation for each office_allotment_class
            foreach ($office_allotment_classes as $class) {
                $class->total_appropriation = DB::table('appropriations')
                    ->where('office_allotment_class_id', $class->id)
                    ->sum('appropriation');
            }

            // Sort the collection by total_appropriation
            $office_allotment_classes = $office_allotment_classes->sortBy(function ($class) {
                return $class->total_appropriation;
            }, SORT_REGULAR, $sortOrder === 'desc');

            // Paginate the sorted collection
            $currentPage = $request->input('page', 1);
            $office_allotment_classes = $office_allotment_classes->forPage($currentPage, $perPage == 'all' ? $office_allotment_classes->count() : $perPage);
            $office_allotment_classes = new \Illuminate\Pagination\LengthAwarePaginator(
                $office_allotment_classes,
                $office_allotment_classes->count(),
                $perPage == 'all' ? $office_allotment_classes->count() : $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // Apply database-level sorting for other fields, including the default 'id'
            // First sort by office, then by allotment_classes.id
            $query->orderBy('office', 'asc')
                  ->join('allotment_classes', 'office_allotment_classes.class', '=', 'allotment_classes.class')
                  ->orderBy('allotment_classes.id', 'asc')
                  ->select('office_allotment_classes.*');

            // Use Laravel's paginate method
            $office_allotment_classes = $perPage == 'all' ? $query->get() : $query->paginate($perPage)
                ->appends([
                    'search' => $search,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                    'year1' => $selectedYear, // Retain the selected year
                ]);
        }

        // Calculate total appropriation for each office_allotment_class (if not already calculated)
        if ($sortBy !== 'total_appropriation') {
            foreach ($office_allotment_classes as $class) {
                $class->total_appropriation = DB::table('appropriations')
                    ->where('office_allotment_class_id', $class->id)
                    ->sum('appropriation');
            }
        }

        // Get total count of office_allotment_classes
        $totalRecords = OfficeAllotmentClass::where('year', $selectedYear)->with('allotmentClass')
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($subQ) use ($search) {
                    $subQ->where('year', 'like', "%{$search}%")
                        ->orWhere('office', 'like', "%{$search}%")
                        ->orWhere('fund', 'like', "%{$search}%")
                        ->orWhere('fund_source', 'like', "%{$search}%")
                        ->orWhere('class', 'like', "%{$search}%")
                        ->orWhere('fpp_code', 'like', "%{$search}%")
                        ->orWhere('responsibility_code', 'like', "%{$search}%");
                });
            })
            ->when($officeFilter, function ($q) use ($officeFilter) {
                return $q->where('office', $officeFilter);
            })
            ->when($allotmentClassFilter, function ($q) use ($allotmentClassFilter) {
                return $q->where('class', $allotmentClassFilter);
            })
            ->when($fundSourceFilter, function ($q) use ($fundSourceFilter) {
                return $q->where('fund_source', $fundSourceFilter);
            })
            ->count();

        // Fetch distinct years from the database
        $availableYears = OfficeAllotmentClass::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // Get fund_source, office, and allotment class
        $fund_sources = FundSource::all()->sortBy('source');
        $offices = Office::all()->sortBy('id');
        $allotment_classes = AllotmentClass::where('category', 'Current')->orderBy('id')->get();
        $allotmentClasses = AllotmentClass::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Allotment Classes']
        ];
        return view('office_allotment_classes.index', compact(
            'office_allotment_classes',
            'perPage',
            'search',
            'sortBy',
            'sortOrder',
            'fund_sources',
            'offices',
            'allotment_classes',
            'allotmentClasses',
            'availableYears',
            'selectedYear',
            'totalRecords',
            'breadcrumb'
        ))->with('status', session('status'));
    }

    public function getContinuingAllotmentClasses()
    {
        $continuingAllotmentClasses = AllotmentClass::where('category', 'Continuing')->get();
        return response()->json($continuingAllotmentClasses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $fund_sources = FundSource::all()->sortBy('source');
        $offices = Office::all()->sortBy('office_name');

        return view('office_allotment_classes.create', compact('fund_sources', 'offices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:1900',
                'office' => 'required|string|max:255',
                'office_abbreviation' => 'required|string|max:255',
                'sub_office' => 'nullable|string|max:255',
                'fund' => 'required|string|max:255',
                'fund_source' => 'required|string|max:255',
                'allotment_class' => 'required|string|max:255',
                'fpp_code' => 'nullable|string|max:255',
                'responsibility_code' => 'nullable|string|max:255',
                /* 'mfo_services' => 'nullable|string|max:1000', */
            ]);

            Log::info('Validated data:', $validated);

            $office_allotment_class = OfficeAllotmentClass::create([
                'year' => $validated['year'],
                'office' => $validated['office'],
                'office_abbreviation' => $validated['office_abbreviation'],
                'sub_office' => $validated['sub_office'],
                'fund' => $validated['fund'],
                'fund_source' => $validated['fund_source'],
                'class' => $validated['allotment_class'],
                'fpp_code' => $validated['fpp_code'],
                'responsibility_code' => $validated['responsibility_code'],
                /* 'mfo_services' => $validated['mfo_services'], */
            ]);

            return redirect()
                ->route('appropriations.index', ['office_allotment_class_id' => $office_allotment_class->id])
                ->with('status', 'Allotment class: <strong>' . e($office_allotment_class->class) . '</strong> under <strong>' . e($office_allotment_class->office_abbreviation) . '</strong> has been created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Laravel handle validation exceptions automatically
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error creating Office Allotment Class:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred while creating the Office Allotment Class. Please try again.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OfficeAllotmentClass $office_allotment_class): View
    {
        // Get fund_source, office and allotment class
        $fund_sources = FundSource::all()->sortBy('source');
        $offices = Office::all()->sortBy('id');
        $allotment_classes = AllotmentClass::where('category', 'Current')->orderBy('id')->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Office Allotment Classes', 'route' => route('office_allotment_classes.index')],
            ['label' => 'Edit Office Allotment Classes']
        ];

        return view('office_allotment_classes.edit', compact('office_allotment_class', 'fund_sources', 'offices', 'allotment_classes', 'breadcrumb'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OfficeAllotmentClass $office_allotment_class): RedirectResponse
    {
        $validated = $request->validate([
            'edit_year' => 'required|integer|min:1900',
            'edit_office' => 'required|string|max:255',
            'edit_office_abbreviation' => 'required|string|max:255',
            'edit_sub_office' => 'nullable|string|max:255',
            'edit_fund' => 'required|string|max:255',
            'edit_fund_source' => 'required|string|max:255',
            'edit_allotment_class' => 'required|string|max:255',
            'edit_fpp_code' => 'nullable|string|max:255',
            'edit_responsibility_code' => 'nullable|string|max:255',
            /* 'mfo_services' => 'nullable|string|max:1000', */
        ]);

        try {
        $office_allotment_class->update([
            'year' => $validated['edit_year'],
            'office' => $validated['edit_office'],
            'office_abbreviation' => $validated['edit_office_abbreviation'],
            'sub_office' => $validated['edit_sub_office'],
            'fund' => $validated['edit_fund'],
            'fund_source' => $validated['edit_fund_source'],
            'class' => $validated['edit_allotment_class'],
            'fpp_code' => $validated['edit_fpp_code'],
            'responsibility_code' => $validated['edit_responsibility_code'],
        ]);

        return redirect()->route('office_allotment_classes.index')
                ->with('status', 'Allotment class: <strong>' . $office_allotment_class->class . '</strong> under <strong>' . $office_allotment_class->office_abbreviation . '</strong> has been updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('status', '⚠️ <strong>Error:</strong> Update failed — ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OfficeAllotmentClass $office_allotment_class): RedirectResponse
    {
        try {
            // Store details before deletion
            $class = $office_allotment_class->class;
            $officeAbbreviation = $office_allotment_class->office_abbreviation;

            // Check if there are any appropriations linked to this office allotment class
            $appropriationsCount = Appropriation::where('office_allotment_class_id', $office_allotment_class->id)->count();

            if ($appropriationsCount > 0) {
                return redirect()->route('office_allotment_classes.index')
                    ->with('error', 
                        "Cannot delete Allotment Class: <strong>{$class}</strong> under <strong>{$officeAbbreviation}</strong>. " .
                        "This allotment class has <strong>{$appropriationsCount}</strong> account(s) associated with it. " .
                        "Please delete the related accounts first before removing this allotment class."
                    );
            }

            // Proceed with deletion if no related records exist
            $office_allotment_class->delete();

            return redirect()->route('office_allotment_classes.index')
                ->with('status', 
                    'Allotment class: <strong>' . $class . '</strong> under <strong>' . $officeAbbreviation . '</strong> has been deleted successfully!'
                );

        } catch (\Exception $e) {
            Log::error('Error deleting office allotment class: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'office_allotment_class_id' => $office_allotment_class->id ?? null
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the office allotment class. Please try again.');
        }
    }
}
