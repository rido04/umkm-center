<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        // hanya admin yang bisa CRUD, public boleh index & show
        $this->middleware(['auth:sanctum', 'role:admin'])->except(['index', 'show', 'update']);
    }

    /**
     * Display list user (public lihat owner)
     */
    public function index()
    {
        $users = User::role('owner')->with('roles')->get();

        // Tambahkan image_url biar FE tinggal pakai langsung
        $users->map(function ($user) {
            $user->image_url = $user->image_path
                ? asset('storage/' . $user->image_path)
                : null;
            return $user;
        });

        return response()->json($users);
    }

    /**
     * Create user (hanya admin)
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'required|string|in:owner,admin',
        ]);

        // Simpan image PATH RELATIF jika ada
        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('images', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'image_path' => $imagePath,  // Simpan path relatif
        ]);

        $user->assignRole($validated['role']);

        // Load relasi & format FULL URL untuk response
        $user->load('roles');
        $user->image_url = $user->image_path
            ? asset('storage/' . $user->image_path)
            : null;

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Show user detail (public hanya boleh lihat owner)
     */
    public function show(User $user)
    {
        if (!$user->hasRole('owner')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Load relasi & format full URL
        $user->load('roles');
        $user->image_url = $user->image_path
            ? asset('storage/' . $user->image_path)
            : null;

        return response()->json($user);
    }

    /**
     * Update user (admin atau diri sendiri)
     */
    public function update(Request $request, User $user)
    {
        $authUser = Auth::user();

        // Cek: kalau bukan admin dan bukan dirinya sendiri -> tolak
        if (!($authUser->hasRole('admin') || $authUser->id === $user->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => 'sometimes|email|unique:users,email,' . $user->id,
            'password'   => 'sometimes|string|min:6',
            'image_path' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'role'       => 'sometimes|string|in:owner,admin',
        ]);

        // Kalau bukan admin, owner gak boleh ubah role
        if (isset($validated['role']) && !$authUser->hasRole('admin')) {
            unset($validated['role']);
        }

        // Update basic data
        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['password'])) $user->password = Hash::make($validated['password']);

        // Handle image upload (hapus lama, upload baru)
        if ($request->hasFile('image_path')) {
            // Hapus image lama jika ada
            if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                Storage::disk('public')->delete($user->image_path);
            }

            // Upload image baru & simpan PATH RELATIF
            $user->image_path = $request->file('image_path')->store('images', 'public');
        }

        $user->save();

        // Cuma admin yang bisa ubah role
        if (isset($validated['role']) && $authUser->hasRole('admin')) {
            $user->syncRoles([$validated['role']]);
        }

        // Load relasi & format FULL URL untuk response
        $user->load('roles');
        $user->image_url = $user->image_path
            ? asset('storage/' . $user->image_path)
            : null;

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user,
        ]);
    }

    /**
     * Delete user (hanya admin)
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Hapus image jika ada (sekarang bisa jalan karena path relatif!)
        if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
            Storage::disk('public')->delete($user->image_path);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
