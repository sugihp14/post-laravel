<?php
namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user->only(['id', 'name', 'email']),
        ], 201);
    }

    public function destroy($id)
    {
        // Mulai transaksi
        DB::beginTransaction();

        try {
            // Cari pengguna berdasarkan ID
            $user = User::find($id);
            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Hapus semua postingan terkait pengguna
            $user->posts()->delete();

            // Hapus pengguna
            $user->delete();

            // Commit transaksi
            DB::commit();

            return response()->json(['message' => 'User and associated posts deleted successfully']);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();

            // Kembalikan response error
            return response()->json([
                'message' => 'Failed to delete user and associated posts',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! \Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Buat token JWT
        $payload = [
            'iss' => "complex-app",    // Issuer
            'sub' => $user->id,        // Subject
            'iat' => time(),           // Waktu pembuatan
            'exp' => time() + 60 * 60, // Kedaluwarsa dalam 1 jam
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user->only(['id', 'name', 'email']),
        ]);
    }

    public function logout(Request $request)
    {

        return response()->json(['message' => 'Logged out successfully']);
    }

}
