<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',

        ]);
    
       $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        
        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user
        ], 201);
    }



    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Email atau password salah.'
        ], 401);
    }

    // Delete token lama
    $user->tokens()->delete();

    // Buat token baru
    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'message' => 'Login berhasil',
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
}



public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logout Berhasil'
    ]);
}

public function profile(Request $request)
{
    return response()->json($request->user());
}

public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|string|min:6|confirmed',
    ], [
        'new_password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
    ]);

    $user = $request->user();

    // Cek password lama
    if (! Hash::check($request->current_password, $user->password)) {
        return response()->json(['message' => 'Password lama salah'], 403);
    }

    // Cek password baru sama dengan lama
    if (Hash::check($request->new_password, $user->password)) {
        return response()->json(['message' => 'Password baru tidak boleh sama dengan password lama'], 422);
    }

    
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json(['message' => 'Password berhasil diubah']);
}

public function updateProfile(Request $request)
{
    $user = $request->user();

    $request->validate([
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:6|confirmed',
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    // Update data dasar
    if ($request->name) $user->name = $request->name;
    if ($request->email) $user->email = $request->email;

    // Update password jika diisi
    if ($request->password) {
        $user->password = Hash::make($request->password);
    }

    // Upload foto jika ada file baru
    if ($request->hasFile('profile_photo')) {

        // Hapus foto lama
        if ($user->profile_photo && file_exists(public_path($user->profile_photo))) {
            unlink(public_path($user->profile_photo));
        }

        // Upload baru
        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        $user->profile_photo = '/storage/' . $path;
    }

    // Simpan perubahan
    $user->save();

    return response()->json([
        'message' => 'Profil berhasil diperbarui',
        'user' => $user
    ]);
}



public function deleteProfile(Request $request)
{
    $user = $request->user();
    $user->delete();
    $user->tokens()->delete();
    return response()->json(['message' => 'Profilmu hilang']);
}

public function allUsers()
{
    $user = Auth::user();

    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $users = User::all();
    return response()->json($users);
}


}



