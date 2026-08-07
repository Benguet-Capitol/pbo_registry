<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function toggleRestriction(Role $role): RedirectResponse
    {
        if (in_array($role->name, Role::EXEMPT_FROM_RESTRICTION)) {
            return redirect()->route('users.index')
                ->with('error', 'The <strong>' . $role->name . '</strong> role cannot be restricted.');
        }

        $role->update(['is_login_restricted' => ! $role->is_login_restricted]);

        $status = $role->is_login_restricted ? 'restricted' : 'unrestricted';

        return redirect()->route('users.index')
            ->with('status', 'Login for role <strong>' . $role->name . '</strong> is now <strong>' . $status . '</strong>.');
    }
}