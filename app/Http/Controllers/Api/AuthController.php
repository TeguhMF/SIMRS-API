<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login User & Return Sanctum Token + User Role
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email atau password yang Anda masukkan salah.'
            ], 401);
        }

        // Hapus token lama & buat token Sanctum baru
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Ambil nama role user (Spatie / Kolom DB)
        $roleName = method_exists($user, 'getRoleNames') && $user->getRoleNames()->first() 
            ? $user->getRoleNames()->first() 
            : ($user->role ?? 'admin');

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $roleName,
                ],
                'token' => $token
            ]
        ]);
    }

    /**
     * Get Current Authenticated User Info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $roleName = method_exists($user, 'getRoleNames') && $user->getRoleNames()->first() 
            ? $user->getRoleNames()->first() 
            : ($user->role ?? 'admin');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $roleName,
            ]
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil.'
        ]);
    }
}