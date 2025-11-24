<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
{
    $request->validate([
        'name'      => 'required|string|max:255',
        'phone'     => 'required|string|unique:users',
        'password'  => 'required|string|min:6|confirmed',
        'role'      => 'nullable|in:admin,petani'
    ]);

    $user = User::create([
        'name'     => $request->name,
        'phone'    => $request->phone,
        'password' => Hash::make($request->password),
        'role'     => 'petani'
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message'      => 'User registered successfully',
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'role'         => $user->role,
        'user'         => [
            'id'    => $user->id,
            'name'  => $user->name,
            'phone' => $user->phone,
            'role'  => $user->role,
        ]
    ]);
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
            'name' => ['The provided credentials are incorrect.'],
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
        ]
    ]);
}


    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
