<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        
        // --- TAMBAHAN KODE FIX ---
        // Jika $roles terdeteksi sebagai array dengan 1 item yang mengandung koma (contoh: ["admin,super_admin"])
        // Kita pecah manual menjadi ["admin", "super_admin"]
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = explode(',', $roles[0]);
        }
        // -------------------------

        // Cek apakah role user ada di dalam daftar
        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
        }

        return $next($request);
    }
}