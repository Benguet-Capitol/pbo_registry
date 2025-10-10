<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\ActivityLogger;
use Spatie\Permission\Traits\HasRoles;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $roleInfo = '';
        
        // Get roles directly from the user model
        try {
            if ($user && $user->roles) {
                $roles = collect($user->roles)->pluck('name')->implode(', ');
                $roleInfo = $roles ? " as {$roles} Role" : '';
            }
        } catch (\Exception $e) {
            // Silently handle any role retrieval errors
        }
        
        ActivityLogger::log("User {$user->name} logged in{$roleInfo}", 'auth', [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            $roleInfo = '';
            
            // Get roles directly from the user model
            try {
                if ($user && $user->roles) {
                    $roles = collect($user->roles)->pluck('name')->implode(', ');
                    $roleInfo = $roles ? " as {$roles} Role" : '';
                }
            } catch (\Exception $e) {
                // Silently handle any role retrieval errors
            }
            
            ActivityLogger::log("User {$user->name} logged out{$roleInfo}", 'auth', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
