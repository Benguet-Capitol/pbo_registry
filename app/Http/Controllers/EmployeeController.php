<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 'all'); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

        // Query employees
        $query = Employee::with('officeRelation'); // Eager load the office relationship

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhere('employee_id', 'like', "%{$search}%")
                ->orWhere('created_at', 'like', "%{$search}%");
            })
            ->orWhereHas('officeRelation', function($q) use ($search) {
                $q->where('office_abbreviation', 'like', "%{$search}%")
                ->orWhere('office_name', 'like', "%{$search}%");
            });
        }

        // Handle sorting for office
        if ($sortBy === 'office') {
            // Join with offices table for sorting by office_abbreviation
            $query->leftJoin('offices', 'employees.office', '=', 'offices.id')
                ->select('employees.*')
                ->orderBy('offices.office_abbreviation', $sortOrder);
        } else {
            // Apply regular sorting
            $query->orderBy($sortBy, $sortOrder);
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $employees = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $employees = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        $offices = Office::orderBy('id')->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Employees']
        ];

        return view('employees.index', compact('employees', 'offices', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'office' => 'required|integer|exists:offices,id',
        ]);

        $employee = Employee::create([
            'employee_id' => $validated['employee_id'],
            'name' => $validated['name'],
            'designation' => $validated['designation'],
            'office' => $validated['office'],
        ]);

        // Load the office relationship to get the office abbreviation
        $employee->load('officeRelation');
        $officeAbbr = $employee->office_abbreviation;

        return redirect(route('employees.index'))->with('status', 'Employee <strong>' . $employee->name . '</strong> from <strong>' . $officeAbbr . '</strong> has been created successfully!');
    }

    public function edit(Employee $employee): View
    {

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Employees', 'route' => route('employees.index')],
            ['label' => 'Edit Employees']
        ];

        return view('employees.edit', [
            'employee' => $employee,
            'breadcrumb' => $breadcrumb
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'edit_employee_id' => 'required|string|max:255',
            'edit_name' => 'required|string|max:255',
            'edit_office' => 'required|integer|exists:offices,id',
            'edit_designation' => 'required|string|max:255'
        ]);

        $updateData = [
            'employee_id' => $validated['edit_employee_id'],
            'name' => $validated['edit_name'],
            'office' => $validated['edit_office'],
            'designation' => $validated['edit_designation']
        ];

        $employee->update($updateData);

        // Load the office relationship to get the office abbreviation
        $employee->load('officeRelation');
        $officeAbbr = $employee->office_abbreviation;

        return redirect()->route('employees.index')->with('status', 'Employee <strong>' . $employee->name . '</strong> from <strong>' . $officeAbbr . '</strong> has been updated successfully!');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store details before deletion
            $employeeName = $employee->name;
            $employeeOffice = $employee->office;

            // System Validation: Check if employee has a related user account
            $relatedUser = User::where('name', $employee->name)->first();
            
            if ($relatedUser) {
                DB::rollBack();
                return redirect()->route('employees.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Employee <strong>{$employeeName}</strong>. " .
                        "This employee has a user account (<strong>{$relatedUser->username}</strong>) associated with them. " .
                        "Please delete or reassign the user account first before removing this employee."
                    );
            }

            // All validations passed - proceed with deletion
            $employee->delete();

            // Load the office relationship to get the office abbreviation
            $employee->load('officeRelation');
            $officeAbbr = $employee->office_abbreviation;

            DB::commit();

            return redirect()->route('employees.index', array_filter($request->only(['per_page', 'search'])))
                ->with('status', 
                    'Employee <strong>' . $employeeName . '</strong> from <strong>' . $officeAbbr . '</strong> has been deleted successfully!'
                );

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Employee Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'employee_id' => $employee->id ?? null,
                'employee_name' => $employee->name ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->route('employees.index', array_filter($request->only(['per_page', 'search'])))
                ->with('error', 'An error occurred while deleting the employee: ' . $e->getMessage());
        }
    }

    public function checkUnique(Request $request): JsonResponse
    {
        $query = Employee::where('employee_id', $request->employee_id);

        // When editing, exclude the current record from the check
        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        return response()->json([
            'is_unique' => !$query->exists(),
        ]);
    }
}
