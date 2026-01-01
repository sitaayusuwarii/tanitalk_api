<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. REGISTER PUBLIK (Hanya untuk Petani)
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'petani' // <--- PAKSA JADI PETANI
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Registrasi berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'role'         => $user->role,
            'user'         => $user
        ], 201);
    }

    // 2. CREATE USER (Khusus Super Admin untuk buat Admin/Petani)
    public function createUserByAdmin(Request $request)
    {
        // Cek manual apakah yang request benar-benar Super Admin
        $currentUser = $request->user();

        if (!$currentUser || $currentUser->role !== 'super_admin') {
            return response()->json(['message' => 'Unauthorized. Hanya Super Admin yang boleh akses.'], 403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|unique:users', // Pastikan HP belum terdaftar
            'password' => 'required|string|min:6', 
            'role'     => 'required|in:admin,petani,super_admin', // Admin bisa pilih role
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => $request->role // Sesuai pilihan Super Admin
            ]);

            return response()->json([
                'message' => 'User berhasil dibuat oleh Super Admin',
                'user'    => $user
            ], 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['Kredensial salah (Username atau Password tidak cocok).'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'role'         => $user->role,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone,
                'role'  => $user->role,
                'profile_picture' => $user->profile_picture, // Tambahan jika perlu
            ]
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return response()->json(['message' => 'Logged out']);
    }
}