<?php

namespace App\Http\Controllers;

use App\Models\AccountCode;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\Program;
use Illuminate\Http\Request;
use App\Models\OfficeAllotmentClass;
use App\Models\Realignment;
use App\Models\Supplemental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Spatie\SimpleExcel\SimpleExcelReader;

class AppropriationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');
        $sortBy = $request->query('sort_by', 'account_code');
        $sortOrder = $request->query('sort_order', 'asc');
        $officeAllotmentClassId = $request->query('office_allotment_class_id');
        
        $officeAllotmentClass = null;
        $allotmentClassDescription = null;
        $officeName = null;
        $totalAppropriation = 0;
        $totalAllotment = 0;

        if ($officeAllotmentClassId) {
            $officeAllotmentClass = OfficeAllotmentClass::with('allotmentClass', 'offices')->find($officeAllotmentClassId);

            $allotmentClassDescription = $officeAllotmentClass->allotmentClass->description ?? 'N/A';
            $officeName = $officeAllotmentClass->office->office_name ?? 'N/A';
            $totalAppropriation = Appropriation::where('office_allotment_class_id', $officeAllotmentClassId)
                ->sum('appropriation');
            $totalAllotment = Appropriation::where('office_allotment_class_id', $officeAllotmentClassId)
                ->sum(DB::raw('COALESCE(quarter1,0) + COALESCE(quarter2,0) + COALESCE(quarter3,0) + COALESCE(quarter4,0)'));
        }
        
        $query = Appropriation::query()->with(['officeAllotmentClass.allotmentClass']);
        
        if ($officeAllotmentClassId) {
            $query->where('office_allotment_class_id', $officeAllotmentClassId);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('programs', 'like', "%{$search}%")
                    ->orWhere('account_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('fpp_code', 'like', "%{$search}%")
                    ->orWhere('project_location', 'like', "%{$search}%")
                    ->orWhere('project_no', 'like', "%{$search}%")
                    ->orWhere('cco_year', 'like', "%{$search}%")
                    ->orWhere('appropriation', 'like', "%{$search}%")
                    ->orWhere('quarter1', 'like', "%{$search}%")
                    ->orWhere('quarter2', 'like', "%{$search}%")
                    ->orWhere('quarter3', 'like', "%{$search}%")
                    ->orWhere('quarter4', 'like', "%{$search}%")
                    ->orWhere('buffer_fund', 'like', "%{$search}%");
            });
        }
        
        $query->orderBy('id');

        $appropriations = ($perPage == 'all')
            ? $query->get()
            : $query->paginate($perPage)->appends($request->query());

        $account_codes = AccountCode::all()->sortBy('id');
        $programs = Program::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Allotment Classes', 'route' => route('office_allotment_classes.index')],
            ['label' => 'Accounts']
        ];

        return view('appropriations.index', compact('appropriations', 'perPage', 'search', 'sortBy', 'sortOrder', 'officeAllotmentClass', 'allotmentClassDescription', 'officeName', 'account_codes', 'officeAllotmentClassId', 'totalAppropriation', 'breadcrumb', 'programs', 'totalAllotment'))->with('status', session('status'));
    }

    /**
     * Get appropriations from last year for copying
     */
    public function getLastYearappropriations(Request $request)
    {
        try {
            $officeAllotmentClassId = $request->query('office_allotment_class_id');
            
            if (!$officeAllotmentClassId) {
                return response()->json(['error' => 'Office Allotment Class ID is required'], 400);
            }

            $currentOfficeAllotment = OfficeAllotmentClass::find($officeAllotmentClassId);
            
            if (!$currentOfficeAllotment) {
                return response()->json(['error' => 'Office Allotment Class not found'], 404);
            }

            // Column names from the actual database schema
            $year = $currentOfficeAllotment->year;
            $officeId = $currentOfficeAllotment->office;  // 'office' not 'office_id'
            $classId = $currentOfficeAllotment->class;    // 'class' not 'allotment_class_id'
            $allocationClass = $currentOfficeAllotment->class; // Get the class value

            if (!$year || !$officeId || !$classId) {
                return response()->json([
                    'error' => 'Missing required fields',
                    'received' => [
                        'year' => $year,
                        'office' => $officeId,
                        'class' => $classId
                    ]
                ], 400);
            }

            \Log::info('Searching for last year appropriations:', [
                'current_year' => $year,
                'office' => $officeId,
                'class' => $classId
            ]);

            $lastYear = $year - 1;

            // Query using the correct column names
            $lastYearOfficeAllotment = OfficeAllotmentClass::where('office', $officeId)
                ->where('class', $classId)
                ->where('year', $lastYear)
                ->first();

            \Log::info('Last Year Office Allotment:', [
                'found' => $lastYearOfficeAllotment !== null,
                'last_year' => $lastYear
            ]);

            if (!$lastYearOfficeAllotment) {
                return response()->json([
                    'data' => [], 
                    'message' => "No accounts found for last year ({$lastYear})",
                    'allocation_class' => $allocationClass
                ], 200);
            }

            $appropriations = Appropriation::where('office_allotment_class_id', $lastYearOfficeAllotment->id)
                ->select('programs', 'account_code', 'description', 'fpp_code', 'project_location', 'project_no', 'cco_year', 'appropriation', 'quarter1', 'quarter2', 'quarter3', 'quarter4', 'remarks')
                ->get();

            \Log::info('Appropriations fetched:', ['count' => $appropriations->count()]);

            return response()->json([
                'data' => $appropriations, 
                'count' => $appropriations->count(),
                'allocation_class' => $allocationClass
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in getLastYearappropriations:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store multiple appropriations from last year
     */
    public function storeFromLastYear(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_allotment_class_id' => 'required|integer|exists:office_allotment_classes,id',
            'appropriations' => 'required|array|min:1',
            'appropriations.*.programs' => 'nullable|string|max:255',
            'appropriations.*.account_code' => 'required|string|max:255',
            'appropriations.*.description' => 'required|string|max:255',
            'appropriations.*.fpp_code' => 'required|string|max:255',
            'appropriations.*.project_location' => 'nullable|string|max:255',
            'appropriations.*.project_no' => 'nullable|string|max:255',
            'appropriations.*.cco_year' => 'nullable|string|max:255',
            'appropriations.*.appropriation' => 'required|numeric',
            'appropriations.*.quarter1' => 'nullable|numeric',
            'appropriations.*.quarter2' => 'nullable|numeric',
            'appropriations.*.quarter3' => 'nullable|numeric',
            'appropriations.*.quarter4' => 'nullable|numeric',
            'appropriations.*.remarks' => 'nullable|string|max:255',
        ]);

        $officeAllotmentClassId = $validated['office_allotment_class_id'];
        $count = 0;

        foreach ($validated['appropriations'] as $appData) {
            Appropriation::create([
                'office_allotment_class_id' => $officeAllotmentClassId,
                'programs' => $appData['programs'],
                'account_code' => $appData['account_code'],
                'description' => $appData['description'],
                'fpp_code' => $appData['fpp_code'],
                'project_location' => $appData['project_location'],
                'project_no' => $appData['project_no'],
                'cco_year' => $appData['cco_year'],
                'appropriation' => $appData['appropriation'],
                'quarter1' => $appData['quarter1'] ?? 0,
                'quarter2' => $appData['quarter2'] ?? 0,
                'quarter3' => $appData['quarter3'] ?? 0,
                'quarter4' => $appData['quarter4'] ?? 0,
                'remarks' => $appData['remarks'],
            ]);
            $count++;
        }

        return redirect(route('appropriations.index', ['office_allotment_class_id' => $officeAllotmentClassId]))
            ->with('status', "<strong>{$count} account(s)</strong> from last year have been copied successfully!");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_allotment_class_id' => 'required|integer|exists:office_allotment_classes,id',
            'programs' => 'nullable|string|max:255',
            'account_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'fpp_code' => 'required|string|max:255',
            'project_location' => 'nullable|string|max:255',
            'project_no' => 'nullable|string|max:255',
            'cco_year' => 'nullable|string|max:255',
            'appropriation' => 'required|numeric',
            'quarter1' => 'nullable|numeric',
            'quarter2' => 'nullable|numeric',
            'quarter3' => 'nullable|numeric',
            'quarter4' => 'nullable|numeric',
            'remarks' => 'nullable|string|max:255',
        ]);

        Log::info('Validated data:', $validated);

        $appropriations = Appropriation::create([
            'office_allotment_class_id' => $validated['office_allotment_class_id'],
            'programs' => $validated['programs'],
            'account_code' => $validated['account_code'],
            'description' => $validated['description'],
            'fpp_code' => $validated['fpp_code'],
            'project_location' => $validated['project_location'],
            'project_no' => $validated['project_no'],
            'cco_year' => $validated['cco_year'],
            'appropriation' => $validated['appropriation'],
            'quarter1' => $validated['quarter1'],
            'quarter2' => $validated['quarter2'],
            'quarter3' => $validated['quarter3'],
            'quarter4' => $validated['quarter4'],
            'remarks' => $validated['remarks'],
        ]);

        return redirect(route('appropriations.index', ['office_allotment_class_id' => $appropriations->office_allotment_class_id]))
            ->with('status', 'Appropriations of <strong>' . number_format($appropriations->appropriation, 2) . '</strong> under <strong>Account Code: ' . $appropriations->account_code . ' - ' . $appropriations->description . '</strong> has been created successfully!');
    }

    public function edit(Request $request, Appropriation $appropriation): View
    {
        $officeAllotmentClassId = $request->query('office_allotment_class_id');
        $account_codes = AccountCode::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Office Allotment Classes', 'route' => route('office_allotment_classes.index')],
            ['label' => 'Appropriations and Allotments', 'route' => route('appropriations.index', ['office_allotment_class_id' => $appropriation->office_allotment_class_id])],
            ['label' => 'Edit Appropriation and Allotment']
        ];

        return view('appropriations.edit', compact('appropriation', 'officeAllotmentClassId', 'account_codes', 'breadcrumb'))
            ->with('status', session('status'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appropriation $appropriation): RedirectResponse
    {
        $validated = $request->validate([
            'edit_programs' => 'nullable|string|max:255',
            'edit_account_code' => 'required|string|max:255',
            'edit_description' => 'required|string|max:255',
            'edit_fpp_code' => 'required|string|max:255',
            'edit_project_location' => 'nullable|string|max:255',
            'edit_project_no' => 'nullable|string|max:255',
            'edit_cco_year' => 'nullable|string|max:255',
            'edit_appropriation' => 'required|numeric',
            'edit_quarter1' => 'nullable|numeric',
            'edit_quarter2' => 'nullable|numeric',
            'edit_quarter3' => 'nullable|numeric',
            'edit_quarter4' => 'nullable|numeric',
            'edit_remarks' => 'nullable|string|max:255',
        ]);

        Log::info('Incoming request data:', $request->all());

        $appropriation->update([
            'programs' => $validated['edit_programs'],
            'account_code' => $validated['edit_account_code'],
            'description' => $validated['edit_description'],
            'fpp_code' => $validated['edit_fpp_code'],
            'project_location' => $validated['edit_project_location'],
            'project_no' => $validated['edit_project_no'],
            'cco_year' => $validated['edit_cco_year'],
            'appropriation' => $validated['edit_appropriation'],
            'quarter1' => $validated['edit_quarter1'],
            'quarter2' => $validated['edit_quarter2'],
            'quarter3' => $validated['edit_quarter3'],
            'quarter4' => $validated['edit_quarter4'],
            'remarks' => $validated['edit_remarks'],
        ]);

        return redirect(route('appropriations.index', ['office_allotment_class_id' => $appropriation->office_allotment_class_id]))
            ->with('status', 'Appropriations of <strong>' . number_format($appropriation->appropriation, 2) . '</strong> under <strong>Account Code: ' . $appropriation->account_code . ' - ' . $appropriation->description . '</strong> has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appropriation $appropriation): RedirectResponse
    {
        try {
            $officeAllotmentClassId = $appropriation->office_allotment_class_id;
            $accountCode = $appropriation->account_code;
            $description = $appropriation->description;
            $appropriationAmount = $appropriation->appropriation;

            $obligationsCount = ObligationAmount::where('appropriation_id', $appropriation->id)
                ->distinct('obligation_id')
                ->count('obligation_id');

            if ($obligationsCount > 0) {
                return redirect()->route('appropriations.index', ['office_allotment_class_id' => $officeAllotmentClassId])
                    ->with('error', 
                        "Cannot delete Account Code: <strong>{$accountCode} - {$description}</strong>. " .
                        "This Account has <strong>{$obligationsCount}</strong> obligation(s) associated with it. " .
                        "Please delete the related obligations first before removing this Account."
                    );
            }

            $realignmentsCount = Realignment::where('appropriations_id', $appropriation->id)->count();

            if ($realignmentsCount > 0) {
                return redirect()->route('appropriations.index', ['office_allotment_class_id' => $officeAllotmentClassId])
                    ->with('error', 
                        "Cannot delete Account Code: <strong>{$accountCode} - {$description}</strong>. " .
                        "This Account has <strong>{$realignmentsCount}</strong> realignment(s) associated with it. " .
                        "Please delete the related realignments first before removing this Account."
                    );
            }

            $supplementalsCount = Supplemental::where('appropriations_id', $appropriation->id)->count();

            if ($supplementalsCount > 0) {
                return redirect()->route('appropriations.index', ['office_allotment_class_id' => $officeAllotmentClassId])
                    ->with('error', 
                        "Cannot delete Account Code: <strong>{$accountCode} - {$description}</strong>. " .
                        "This Account has <strong>{$supplementalsCount}</strong> supplemental/reversion(s) associated with it. " .
                        "Please delete the related supplementals/reversions first before removing this Account."
                    );
            }

            $appropriation->delete();

            return redirect()->route('appropriations.index', ['office_allotment_class_id' => $officeAllotmentClassId])
                ->with('status', 
                    'Appropriations of <strong>' . number_format($appropriationAmount, 2) . '</strong> under <strong>Account Code: ' . $accountCode . ' - ' . $description . '</strong> has been deleted successfully!'
                );

        } catch (\Exception $e) {
            Log::error('Error deleting appropriation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'appropriation_id' => $appropriation->id ?? null
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the appropriation. Please try again.');
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
            'office_allotment_class_id' => 'required|integer'
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $type = $extension === 'csv' ? 'csv' : 'xlsx';

        $reader = SimpleExcelReader::create($file->getRealPath(), $type);

        $headerMap = [
            'Account Code' => 'account_code',
            'Description' => 'description',
            'FPP Code' => 'fpp_code',
            'Appropriation' => 'appropriation',
            'Programs' => 'programs',
            'Project Number' => 'project_no',
            'ID2' => 'id2',
            'Project Location' => 'project_location',
            'CCO Year' => 'cco_year',
            '1st Quarter Allotment' => 'quarter1',
            '2nd Quarter Allotment' => 'quarter2',
            '3rd Quarter Allotment' => 'quarter3',
            '4th Quarter Allotment' => 'quarter4',
            'Remarks' => 'remarks',
        ];

        $headers = $reader->getHeaders();
        $requiredHeaders = ['Account Code', 'Description', 'FPP Code', 'Appropriation'];

        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (count($missingHeaders)) {
            return redirect()->back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missingHeaders)]);
        }

        $importedCount = 0;

        $reader->getRows()->each(function (array $rowProperties) use ($request, $headerMap, &$importedCount) {
            $data = [];

            foreach ($headerMap as $friendlyHeader => $dbField) {
                $value = $rowProperties[$friendlyHeader] ?? null;

                if (in_array($dbField, ['quarter1', 'quarter2', 'quarter3', 'quarter4'])) {
                    $data[$dbField] = $value !== null && $value !== '' ? $value : 0.00;
                } else {
                    $data[$dbField] = $value;
                }
            }

            $data['office_allotment_class_id'] = $request->office_allotment_class_id;

            Appropriation::create($data);
            $importedCount++;
        });

        return redirect()->back()->with('status', "<strong>{$importedCount} account(s)</strong> imported successfully!");
    }

    public function getAccountCodes(Request $request)
    {
        try {
            $search = $request->query('search', '');
            $class = $request->query('class', null);
            
            Log::info('getAccountCodes called', ['search' => $search, 'class' => $class]);
            
            $accountCodes = AccountCode::query();
            
            if ($search) {
                $accountCodes->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($class) {
                $accountCodes->where('class', $class);
            }
            
            $data = $accountCodes->select('code', 'description', 'class')
                                 ->limit(10)
                                 ->get();
            
            Log::info('Account codes found', ['count' => count($data), 'data' => $data]);
            
            $response = response()->json(['data' => $data]);
            Log::info('Response status', ['status' => $response->status()]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('getAccountCodes error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getAllotmentClassInfo(Request $request)
    {
        try {
            $officeAllotmentClassId = $request->query('office_allotment_class_id');
            
            if (!$officeAllotmentClassId) {
                return response()->json(['error' => 'office_allotment_class_id is required'], 400);
            }
            
            $officeAllotmentClass = OfficeAllotmentClass::with('allotmentClass')
                ->find($officeAllotmentClassId);
            
            if (!$officeAllotmentClass) {
                return response()->json(['error' => 'Office Allotment Class not found'], 404);
            }
            
            if (!$officeAllotmentClass->allotmentClass) {
                return response()->json(['error' => 'Allotment Class relationship not found'], 404);
            }
            
            return response()->json([
                'class' => $officeAllotmentClass->allotmentClass->class ?? 'UNKNOWN',
                'description' => $officeAllotmentClass->allotmentClass->description ?? ''
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}