<?php

namespace App\Http\Controllers;

use App\Models\CosList;
use App\Models\OfficeAllotmentClass;
use App\Models\Appropriation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CosListController extends Controller
{
    /**
     * Appropriation account codes eligible for Contract of Service records.
     * Matching is prefix-based (LIKE 'code%') so entries with extensions
     * (e.g. "5-02-12-990 se") are still included alongside exact matches.
     */
    private const COS_ELIGIBLE_APPROPRIATION_CODES = [
        '5-02-12-990',
        '5-02-11-990',
        '5-02-12-020',
        '5-02-11-010',
        '5-02-11-020',
        '5-02-11-030',
        '5-02-12-010',
        '5-02-12-030',
    ];

    /**
     * Display a listing of COS records
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
    $search = $request->input('search');
    $searchColumn = $request->input('search_column', '');
    $sortBy = $request->query('sort_by', 'id');
    $sortOrder = $request->query('sort_order', 'asc');
    $currentYear = date('Y');
    $selectedYear = $request->input('year1', $currentYear);

    // Base query for COS records
    $query = CosList::with([
        'officeAllotmentClass.offices',
        'officeAllotmentClass.allotmentClass',
        'appropriation'
    ])->whereHas('officeAllotmentClass', function ($q) use ($selectedYear) {
        $q->where('year', $selectedYear);
    });

    // Year filter
    if ($request->filled('year1')) {
        $selectedYear = $request->year1;
    }

    // Office/Allotment Class filter
    if ($request->filled('office_allotment_class_filter')) {
        $query->where('office_allotment_class_id', $request->office_allotment_class_filter);
    }

    // Appropriation filter
    if ($request->filled('appropriation_filter')) {
        $query->where('appropriation_id', $request->appropriation_filter);
    }

    // Build a separate query for totals — mirrors the year/office-class/appropriation
    // filters above, but deliberately excludes search, so typing in the search box
    // doesn't change the Total Amount / Balance figures in the tfoot.
    $totalsQuery = CosList::whereHas('officeAllotmentClass', function ($q) use ($selectedYear) {
        $q->where('year', $selectedYear);
    });

    if ($request->filled('office_allotment_class_filter')) {
        $totalsQuery->where('office_allotment_class_id', $request->office_allotment_class_filter);
    }

    if ($request->filled('appropriation_filter')) {
        $totalsQuery->where('appropriation_id', $request->appropriation_filter);
    }

    // Search — only affects $query (the paginated/listed records), not $totalsQuery
    if ($search) {
        // "Period" isn't a stored column — it's from_date/to_date rendered the same
        // way the table displays it (e.g. "Jan 01, 2024 - Dec 31, 2024").
        $periodClause = function ($q) use ($search) {
            $q->whereRaw(
                "CONCAT(DATE_FORMAT(from_date, '%b %d, %Y'), ' - ', DATE_FORMAT(to_date, '%b %d, %Y')) LIKE ?",
                ["%{$search}%"]
            );
        };

        // Monthly Rate / Total Amount aren't searched as raw numbers — they're matched
        // against the same "50,000.00" formatted string number_format() produces in the
        // view, via MySQL's FORMAT(), so typing "50,000" or "0.00" behaves as expected.
        $monthlyRateClause = function ($q) use ($search) {
            $q->whereRaw("FORMAT(monthly_rate, 2) LIKE ?", ["%{$search}%"]);
        };

        $totalAmountClause = function ($q) use ($search) {
            $q->whereRaw("FORMAT(annual_rate, 2) LIKE ?", ["%{$search}%"]);
        };

        switch ($searchColumn) {
            case 'employee_name':
                $query->where('employee_name', 'like', "%{$search}%");
                break;
            case 'position_title':
                $query->where('position_title', 'like', "%{$search}%");
                break;
            case 'salary_grade':
                $query->where('salary_grade', 'like', "%{$search}%");
                break;
            case 'period':
                $query->where($periodClause);
                break;
            case 'monthly_rate':
                $query->where($monthlyRateClause);
                break;
            case 'total_amount':
                $query->where($totalAmountClause);
                break;
            default:
                // All Columns — search every searchable field
                $query->where(function ($q) use ($search, $periodClause, $monthlyRateClause, $totalAmountClause) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('position_title', 'like', "%{$search}%")
                        ->orWhere('salary_grade', 'like', "%{$search}%")
                        ->orWhere('basis', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhere($periodClause)
                        ->orWhere($monthlyRateClause)
                        ->orWhere($totalAmountClause);
                });
        }
    }

    // Sorting
    $query->orderBy($sortBy, $sortOrder);

    // Paginate
    $cosList = $perPage == 'all'
        ? $query->get()
        : $query->paginate($perPage)->appends([
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
            'year1' => $selectedYear,
            'office_allotment_class_filter' => $request->office_allotment_class_filter,
            'appropriation_filter' => $request->appropriation_filter,
        ]);

        // Get all available years from existing records
        $availableYears = OfficeAllotmentClass::query()
        ->distinct()
        ->pluck('year')
        ->push($currentYear)
        ->unique()
        ->sort()
        ->reverse()
        ->values();

        $officeAllotmentClasses = $this->eligibleOfficeAllotmentClasses($selectedYear);
        $currentYearOfficeAllotmentClasses = $this->eligibleOfficeAllotmentClasses($currentYear);

        // Appropriations for the Accounts filter dropdown
        $appropriationsForFilter = collect();
        if ($request->filled('office_allotment_class_filter')) {
            $selectedClass = OfficeAllotmentClass::find($request->office_allotment_class_filter);
            if ($selectedClass) {
                $appropriationsForFilter = $selectedClass->appropriations()
                    ->where(function ($q) {
                        foreach (self::COS_ELIGIBLE_APPROPRIATION_CODES as $code) {
                            $q->orWhere('account_code', 'like', $code . '%');
                        }
                    })
                    ->select('id', 'account_code', 'description')
                    ->get();
            }
        }

        // Total Appropriation for the selected Accounts filter — base appropriation
        $totalAppropriation = null;
        if ($request->filled('appropriation_filter')) {
            $appropriation = Appropriation::find($request->appropriation_filter);
            if ($appropriation) {
                $base = (float) $appropriation->appropriation;

                $supplementalAdd = (float) $appropriation->supplementals()
                    ->where('type', 'Supplemental')
                    ->sum('amount');

                $supplementalDeduct = (float) $appropriation->supplementals()
                    ->where('type', 'Reversion')
                    ->sum('amount');

                $realignmentAdd = (float) $appropriation->realignments()
                    ->where('type', 'Recipient')
                    ->sum('amount');

                $realignmentDeduct = (float) $appropriation->realignments()
                    ->where('type', 'Source')
                    ->sum('amount');

                $totalAppropriation = $base + $supplementalAdd - $supplementalDeduct + $realignmentAdd - $realignmentDeduct;
            }
        }

        $totalAnnualRate = $totalsQuery->sum('annual_rate');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'COS List']
        ];

        return view('cos_lists.index', compact(
            'cosList',
            'officeAllotmentClasses',
            'currentYearOfficeAllotmentClasses',
            'appropriationsForFilter',
            'availableYears',
            'selectedYear',
            'perPage',
            'search',
            'sortBy',
            'sortOrder',
            'breadcrumb',
            'totalAnnualRate',
            'totalAppropriation'
        ));
    }

    /**
     * Office/Allotment Classes for a given year, restricted to those with at
     * least one appropriation matching the COS-eligible account codes
     * (prefix match, so extensions like "5-02-12-990 se" still qualify).
     */
    private function eligibleOfficeAllotmentClasses($year)
    {
        return OfficeAllotmentClass::where('year', $year)
            ->with('offices', 'allotmentClass')
            ->whereHas('appropriations', function ($q) {
                $q->where(function ($sub) {
                    foreach (self::COS_ELIGIBLE_APPROPRIATION_CODES as $code) {
                        $sub->orWhere('account_code', 'like', $code . '%');
                    }
                });
            })
            ->orderBy('office', 'asc')
            ->get();
    }

    /**
     * Show the form for creating a new COS record
     */
    public function create()
    {
        return redirect()->route('cos_lists.index');
    }

    /**
     * Store a newly created COS record in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
            'appropriation_id' => 'required|exists:appropriations,id',
            'employee_id' => 'required|string',
            'employee_name' => 'required|string',
            'position_title' => 'required|string',
            'salary_grade' => 'nullable|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'monthly_rate' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'basis' => 'nullable|string',
        ]);

        $redirectParams = $request->only([
            'year1', 'office_allotment_class_filter', 'appropriation_filter',
            'per_page', 'search', 'search_column', 'sort_by', 'sort_order', 'page',
        ]);

        try {
            CosList::create($validated);

            return redirect()->route('cos_lists.index', $redirectParams)
                ->with('status', [
                    'type' => 'create',
                    'message' => 'Contract of Service: <strong>' . $validated['employee_name'] . '</strong> has been created successfully.',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('cos_lists.index', $redirectParams)
                ->with('error', 'Failed to create Contract of Service: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified Contract of Service
     */
    public function show(CosList $cosList)
    {
        return redirect()->route('cos_lists.index');
    }

    /**
     * Show the form for editing the specified Contract of Service
     */
    public function edit(CosList $cosList)
    {
        return redirect()->route('cos_lists.index');
    }

    /**
     * Update the specified Contract of Service in database
     */
    public function update(Request $request, CosList $cosList)
    {
        $validated = $request->validate([
            'office_allotment_class_id' => 'required|exists:office_allotment_classes,id',
            'appropriation_id' => 'required|exists:appropriations,id',
            'employee_id' => 'required|string',
            'employee_name' => 'required|string',
            'position_title' => 'required|string',
            'salary_grade' => 'nullable|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'monthly_rate' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'basis' => 'nullable|string',
        ]);

        $redirectParams = $request->only([
            'year1', 'office_allotment_class_filter', 'appropriation_filter',
            'per_page', 'search', 'search_column', 'sort_by', 'sort_order', 'page',
        ]);

        try {
            $cosList->update($validated);

            return redirect()->route('cos_lists.index', $redirectParams)
                ->with('status', [
                    'type' => 'update',
                    'message' => 'Contract of Service: <strong>' . $validated['employee_name'] . '</strong> has been updated successfully.',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('cos_lists.index', $redirectParams)
                ->with('error', 'Failed to update Contract of Service: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified Contract of Service from database
     */
    public function destroy(CosList $cosList)
    {
        $redirectParams = request()->only([
            'year1', 'office_allotment_class_filter', 'appropriation_filter',
            'per_page', 'search', 'search_column', 'sort_by', 'sort_order', 'page',
        ]);

        try {
            $cosList->delete();

            return redirect()->route('cos_lists.index', $redirectParams)
                ->with('status', [
                    'type' => 'delete',
                    'message' => 'Contract of Service: <strong>' . $cosList->employee_name . '</strong> has been deleted successfully.',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('cos_lists.index', $redirectParams)
                ->with('error', 'Failed to delete Contract of Service: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint: Get appropriations by office allotment class
     */
    public function getAppropriationsByClass($classId)
    {
        $appropriations = OfficeAllotmentClass::findOrFail($classId)
            ->appropriations()
            ->where(function ($query) {
                foreach (self::COS_ELIGIBLE_APPROPRIATION_CODES as $code) {
                    $query->orWhere('account_code', 'like', $code . '%');
                }
            })
            ->select('id', 'account_code', 'description', 'programs')
            ->get();

        return response()->json($appropriations);
    }

    /**
     * API endpoint: Get all employees from external API
     */
    public function getEmployees()
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => '2idqUEqD16WlkMwoWohuluNqFIm9ZqKmsw4GuSsM15E',
                'Accept' => 'application/json',
            ])->get('http://192.168.2.26/api/v1/getEmployees');

            if ($response->successful()) {
                $data = $response->json();
                // Return the data as-is to see the structure
                return response()->json($data);
            }

            return response()->json(['error' => 'Failed to fetch employees from external API', 'status' => $response->status()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching employees: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API endpoint: Get employee info (integrate with existing employee API)
     */
    public function getEmployeeInfo($employeeId)
    {
        // This integrates with the existing employee API/service
        // For now, return basic structure - integrate with actual employee service
        try {
            $employee = Employee::find($employeeId);
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            return response()->json([
                'id' => $employee->id,
                'name' => $employee->name,
                'position_title' => $employee->position,
                'salary_grade' => $employee->salary_grade ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching employee'], 500);
        }
    }
}