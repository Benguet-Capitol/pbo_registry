<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

        // Query employees
        $query = Employee::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhere('office', 'like', "%{$search}%")
                ->orWhere('employee_id', 'like', "%{$search}%")
                ->orWhere('created_at', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $employees = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $employees = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Employees']
        ];

        return view('employees.index', compact('employees', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'office' => 'required|string|max:255',
        ]);

        $employee = Employee::create([
            'employee_id' => $validated['employee_id'],
            'name' => $validated['name'],
            'designation' => $validated['designation'],
            'office' => $validated['office'],
        ]);

        return redirect(route('employees.index'))->with('status', 'Employee <strong>' . $employee->name . '</strong> from <strong>' . $employee->office . '</strong> has been created successfully!');
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
            'edit_office' => 'required|string|max:255',
            'edit_designation' => 'required|string|max:255'
        ]);

        $employee->update([
            'employee_id' => $validated['edit_employee_id'],
            'name' => $validated['edit_name'],
            'office' => $validated['edit_office'],
            'designation' => $validated['edit_designation']
        ]);

        return redirect()->route('employees.index')->with('status', 'Employee <strong>' . $employee->name . '</strong> from <strong>' . $employee->office . '</strong> has been updated successfully!');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('status', 'Employee <strong>' . $employee->name . '</strong> from <strong>' . $employee->office . '</strong>has been deleted successfully!');
    }
}
