<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * $roles berisi daftar role yang diizinkan (misal: 'admin', 'dosen')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // 1. Cek apakah user ada (valid)?
        if (!$user) {
            // Jika request dari Flutter / API
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Akses ditolak. Silakan login terlebih dahulu!'], 401);
            }
            // Jika request dari Web Browser
            return redirect('/login');
        }

        // 2. Cek apakah role user tersebut ada di dalam daftar yang diizinkan?
        if (!in_array($user->role, $roles)) {
            // Jika request dari Flutter / API
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden: Anda tidak memiliki akses ke halaman ini!'], 403);
            }
            // Jika request dari Web Browser, tendang kembali ke Dashboard
            return redirect('/dashboard');
        }

        // Jika aman, persilakan masuk
        return $next($request);
    }
}