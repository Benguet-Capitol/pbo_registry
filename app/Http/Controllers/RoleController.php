<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function toggleRestriction(Role $role): RedirectResponse
    {
        // Safety guard: never let the currently logged-in user's own role get restricted
        if (auth()->user()->usertype === $role->name) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot restrict your own role (<strong>' . $role->name . '</strong>).');
        }

        $role->update(['is_login_restricted' => ! $role->is_login_restricted]);

        $status = $role->is_login_restricted ? 'restricted' : 'active';

        return redirect()->route('users.index')
            ->with('status', 'Login for <strong>' . $role->name . '</strong> Role is now <strong>' . $status . '</strong>.');
    }
}