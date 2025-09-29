<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
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
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

        // Query users
        $query = User::query();

        // Apply search filtering
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('usertype', 'like', "%{$search}%")
                ->orWhere('office', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $users = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $users = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        // Get employees and sort by name (locally)
        $employees = Employee::all()->sortBy('name');

        //Get roles
        $roles = Role::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Users']
        ];

        return view('users.index', compact('users', 'perPage', 'search', 'sortBy', 'sortOrder', 'employees', 'roles', 'breadcrumb'))->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'usertype' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed', // Add password validation
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'usertype' => $validated['usertype'],
            'office' => $validated['office'],
            'password' => Hash::make($validated['password']), // Hash the password before storing
        ]);

        return redirect(route('users.index'))->with('status', 'User <strong>' . $user->name . '</strong> from <strong>' . $user->office . '</strong> has been created successfully!');
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
            'edit_office' => 'required|string|max:255'
        ]);

        $user->update([
            'name' => $validated['edit_name'],
            'username' => $validated['edit_username'],
            'usertype' => $validated['edit_usertype'],
            'office' => $validated['edit_office']
        ]);

        $user->syncRoles([$user->usertype]);

        return redirect()->route('users.index')->with('status', 'User <strong>' . $user->name . '</strong> from <strong>' . $user->office . '</strong> has been updated successfully!');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User <strong>' . $user->name . '</strong> from <strong>' . $user->office . '</strong> has been deleted successfully!');
    }
}
