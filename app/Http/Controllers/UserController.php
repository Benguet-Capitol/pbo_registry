<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 'all'); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

        // Query users with office relationship
        $query = User::with('officeRelation'); // Eager load the office relationship

        // Apply search filtering
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('usertype', 'like', "%{$search}%");
            })
            ->orWhereHas('officeRelation', function($q) use ($search) {
                $q->where('office_abbreviation', 'like', "%{$search}%")
                ->orWhere('office_name', 'like', "%{$search}%");
            });
        }

        // Handle sorting for office
        if ($sortBy === 'office') {
            // Join with offices table for sorting by office_abbreviation
            $query->leftJoin('offices', 'users.office', '=', 'offices.id')
                ->select('users.*')
                ->orderBy('offices.office_abbreviation', $sortOrder);
        } else {
            // Apply regular sorting
            $query->orderBy($sortBy, $sortOrder);
        }

        // Apply pagination
        if ($perPage == 'all') {
            $users = $query->get();
        } else {
            $users = $query->paginate($perPage);
        }

        // Get employees and sort by name (locally)
        $employees = Employee::all()->sortBy('name');

        //Get roles
        $roles = Role::all()->sortBy('id');

        // Get all offices for any dropdowns/filters you might need
        $offices = Office::orderBy('id')->get();

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Users']
        ];

        return view('users.index', compact('users', 'perPage', 'search', 'sortBy', 'sortOrder', 'employees', 'roles', 'offices', 'breadcrumb'))
            ->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'usertype' => 'required|string|max:255',
            'office' => 'required|integer|exists:offices,id',
            'password' => 'required|string|min:6|confirmed', // Add password validation
        ], [
            'username.unique' => 'The username has already been taken. Please choose a different username.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'usertype' => $validated['usertype'],
            'office' => $validated['office'],
            'password' => Hash::make($validated['password']), // Hash the password before storing
        ]);

        // Load the office relationship to get the office abbreviation
        $user->load('officeRelation');
        $officeAbbr = $user->office_abbreviation;

        return redirect(route('users.index'))->with('status', 'User <strong>' . $user->name . '</strong> from <strong>' . $officeAbbr . '</strong> has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $employees = Employee::all()->sortBy('name');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Users', 'route' => route('users.index')],
            ['label' => 'Edit Users']
        ];

        return view('users.edit', compact('user', 'employees', 'breadcrumb'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'edit_name' => 'required|string|max:255',
            'edit_username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'edit_usertype' => 'required|string|max:255',
            'edit_office' => 'required|integer|exists:offices,id',
        ]);

        // Prepare update data
        $updateData = [
            'name' => $validated['edit_name'],
            'username' => $validated['edit_username'],
            'usertype' => $validated['edit_usertype'],
            'office' => $validated['edit_office']
        ];

        $user->update($updateData);

        $user->syncRoles([$user->usertype]);

        // Load the office relationship to get the office abbreviation
        $user->load('officeRelation');
        $officeAbbr = $user->office_abbreviation;

        return redirect()->route('users.index')->with('status', 'User <strong>' . $user->name . '</strong> from <strong>' . $officeAbbr . '</strong> has been updated successfully!');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Load the office relationship before deleting
        $user->load('officeRelation');
        $userName = $user->name;
        $officeAbbr = $user->office_abbreviation;

        // Check if user has activity logs
        if ($user->activityLogs()->exists()) {
            return redirect()->route('users.index')->with('error', 'Cannot delete user <strong>' . $userName . '</strong> from <strong>' . $officeAbbr . '</strong> because they have associated activity logs. Please archive or reassign the logs first.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User <strong>' . $userName . '</strong> from <strong>' . $officeAbbr . '</strong> has been deleted successfully!');
    }
}
