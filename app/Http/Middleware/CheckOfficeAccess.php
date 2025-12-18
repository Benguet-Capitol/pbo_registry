<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOfficeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // If user is not a guest, allow access
        if (!$user->hasRole('Guest')) {
            return $next($request);
        }
        
        // For guests, check if they're trying to access data from their office
        $officeFilter = $request->input('office_filter');
        $userOfficeId = $user->office;
        
        // If guest tries to filter by a different office, reset to their office
        if ($officeFilter && $officeFilter != $userOfficeId) {
            return redirect()->route(request()->route()->getName(), 
                array_merge($request->except('office_filter'), ['office_filter' => $userOfficeId])
            );
        }
        
        return $next($request);
    }
}