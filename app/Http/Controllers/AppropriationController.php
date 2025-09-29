<?php

namespace App\Http\Controllers;

use App\Models\AccountCode;
use App\Models\Appropriation;
use App\Models\Program;
use Illuminate\Http\Request;
use App\Models\OfficeAllotmentClass;
use Illuminate\Http\RedirectResponse;
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
        // Get the per page value from the request, default to "all"
        $perPage = $request->input('per_page', 'all');
        $search = $request->input('search');
        // Get the sort by and sort order values from the request
        $sortBy = $request->query('sort_by', 'account_code');
        $sortOrder = $request->query('sort_order', 'asc');
        // Get the office allotment class id from the request
        $officeAllotmentClassId = $request->query('office_allotment_class_id');
        // Retrieve the OfficeAllotmentClass along with its related AllotmentClass
        $officeAllotmentClass = null;
        $allotmentClassDescription = null;
        $officeName = null;
        $totalAppropriation = 0;
        $totalAllotment = 0;

        if ($officeAllotmentClassId) {
            $officeAllotmentClass = OfficeAllotmentClass::with('allotmentClass', 'offices')->find($officeAllotmentClassId);

            // Retrieve the allotment class description
            $allotmentClassDescription = $officeAllotmentClass->allotmentClass->description ?? 'N/A';
            // Retrieve the office name
            $officeName = $officeAllotmentClass->office->office_name ?? 'N/A';
            // Calculate the total appropriation for the given office_allotment_class_id
            $totalAppropriation = Appropriation::where('office_allotment_class_id', $officeAllotmentClassId)
                ->sum('appropriation');
            // Calculate the total allotment (sum of all quarters)
            $totalAllotment = Appropriation::where('office_allotment_class_id', $officeAllotmentClassId)
                ->sum(\DB::raw('COALESCE(quarter1,0) + COALESCE(quarter2,0) + COALESCE(quarter3,0) + COALESCE(quarter4,0)'));
        }
        // Query the appropriations
        $query = Appropriation::query()->with(['officeAllotmentClass.allotmentClass']);
        // Filter the appropriations based on the office allotment class id
        if ($officeAllotmentClassId) {
            $query->where('office_allotment_class_id', $officeAllotmentClassId);
        }
        // Filter the appropriations based on the search value
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
        // Sort appropriations: prioritize those without programs, then alphabetically sort those with programs
       $query->orderByRaw("
            CASE WHEN programs IS NULL OR programs = '' THEN 0 ELSE 1 END ASC,
            programs ASC,
            CAST(SUBSTRING_INDEX(account_code, '-', 1) AS UNSIGNED) ASC,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(account_code, '-', 2), '-', -1) AS UNSIGNED) ASC,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(account_code, '-', 3), '-', -1) AS UNSIGNED) ASC,
            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(account_code, ' ', 1), '-', -1), ' ', 1) AS UNSIGNED) ASC,
            SUBSTRING_INDEX(account_code, ' ', -1) ASC
        ");

        // Get the appropriations
        $appropriations = ($perPage == 'all')
            ? $query->get()
            : $query->paginate($perPage)
            ->appends($request->query());

        // Get account code class
        $account_codes = AccountCode::all()->sortBy('id');

        // Get all programs for the Programs dropdown in create blade
        $programs = Program::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Allotment Classes', 'route' => route('office_allotment_classes.index')],
            ['label' => 'Accounts']
        ];

        // Return the view with the appropriations
        return view('appropriations.index', compact('appropriations', 'perPage', 'search', 'sortBy', 'sortOrder', 'officeAllotmentClass', 'allotmentClassDescription', 'officeName', 'account_codes', 'officeAllotmentClassId', 'totalAppropriation', 'breadcrumb', 'programs', 'totalAllotment'))->with('status', session('status'));
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
        // Get account code class
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
        // Store the office_allotment_class_id before deleting
        $officeAllotmentClassId = $appropriation->office_allotment_class_id;

        // Delete the record
        $appropriation->delete();

        // Redirect to index with the correct parameter
        return redirect()->route('appropriations.index', ['office_allotment_class_id' => $officeAllotmentClassId])
            ->with('status', 'Appropriations of <strong>' . number_format($appropriation->appropriation, 2) . '</strong> under <strong>Account Code: ' . $appropriation->account_code . ' - ' . $appropriation->description . '</strong> has been deleted successfully!');
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

        // Map of friendly headers => DB columns
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

        // Validate headers based on Excel columns
        $headers = $reader->getHeaders();
        $requiredHeaders = ['Account Code', 'Description', 'FPP Code', 'Appropriation'];

        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (count($missingHeaders)) {
            return redirect()->back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missingHeaders)]);
        }

        // Process rows
        $reader->getRows()->each(function (array $rowProperties) use ($request, $headerMap) {
            $data = [];

            foreach ($headerMap as $friendlyHeader => $dbField) {
                $value = $rowProperties[$friendlyHeader] ?? null;

                // Set default value for quarter fields
                if (in_array($dbField, ['quarter1', 'quarter2', 'quarter3', 'quarter4'])) {
                    $data[$dbField] = $value !== null && $value !== '' ? $value : 0.00;
                } else {
                    $data[$dbField] = $value;
                }
            }

            $data['office_allotment_class_id'] = $request->office_allotment_class_id;

            Appropriation::create($data);
        });

        return redirect()->back()->with('status', '<strong>Appropriations</strong> and <strong>Allotments</strong> Imported successfully!');
    }
}
