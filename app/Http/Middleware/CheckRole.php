<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // Administrator has full access
        if ($user->role === 'administrator') {
            return $next($request);
        }

        // User role has read-only access with restricted endpoints
        if ($user->role === 'user') {
            $path = $request->path();
            $method = $request->method();

            // 1. Block shipment routes completely
            if (
                preg_match('/^(shipment-planner|shipment-history|shipments|api\/shipments)/i', $path)
            ) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aksi ini tidak diperbolehkan untuk peran Anda.'
                    ], 403);
                }
                abort(403, 'Aksi ini tidak diperbolehkan untuk peran Anda.');
            }

            // 2. Block import and export routes completely
            if (
                preg_match('/(import|export)/i', $path)
            ) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aksi ini tidak diperbolehkan untuk peran Anda.'
                    ], 403);
                }
                abort(403, 'Aksi ini tidak diperbolehkan untuk peran Anda.');
            }

            // 3. Block creation/edit pages (e.g. countries/create, countries/1/edit)
            if (
                preg_match('/\/(create|edit)$/i', $path) || 
                preg_match('/(create|edit)\//i', $path)
            ) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aksi ini tidak diperbolehkan untuk peran Anda.'
                    ], 403);
                }
                abort(403, 'Aksi ini tidak diperbolehkan untuk peran Anda.');
            }

            // 4. Block all non-GET requests except allowed user actions
            if ($method !== 'GET') {
                $isAllowed = false;

                // Path spesifik yang diperbolehkan
                $allowedPaths = [
                    'api/profile',
                    'api/profile/photo',
                    'api/profile/password',
                    'compare-countries',
                    'logout'
                ];

                if (in_array($path, $allowedPaths)) {
                    $isAllowed = true;
                }

                // Izinkan pengguna mengelola Watchlist mereka sendiri (POST/PUT/DELETE /watchlist...)
                if (preg_match('/^watchlist(\/.*)?$/i', $path)) {
                    $isAllowed = true;
                }

                if (!$isAllowed) {
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Aksi ini tidak diperbolehkan untuk peran Anda.'
                        ], 403);
                    }
                    abort(403, 'Aksi ini tidak diperbolehkan untuk peran Anda.');
                }
            }
        }

        return $next($request);
    }
}
