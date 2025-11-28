<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Parse the roles parameter (e.g., "admin,police")
        $allowedRoles = explode(',', $roles);
        
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // Check if user has the required role
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: insufficient permissions'
            ], 403);
        }

        return $next($request);
    }
}
