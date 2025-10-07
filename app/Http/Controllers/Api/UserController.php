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

        // tambahkan image_url biar FE tinggal pakai langsung
        $users->map(function ($user) {
            $user->image_url = $user->image_path
                ? (str_starts_with($user->image_path, 'http')
                    ? $user->image_path
                    : asset('storage/' . $user->image_path))
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
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image_path')) {
            $file = $request->file('image_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images', $fileName);
            $imagePath = 'storage/images/' . $fileName;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'image_path' => $imagePath,
        ]);

        // assign role (owner / admin / dll)
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles')
        ]);
    }


    /**
     * Show user detail (public hanya boleh lihat owner)
     */
    public function show(User $user)
    {
        if (!$user->hasRole('owner')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($this->formatUser($user));
    }

    /**
     * Update user (hanya admin)
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
            'image_path' => 'sometimes|string',
            'role'       => 'sometimes|string|in:owner,admin',
        ]);

        // kalau bukan admin, owner gak boleh ubah role
        if (isset($validated['role']) && !$authUser->hasRole('admin')) {
            unset($validated['role']);
        }

        // update basic data
        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['email'])) $user->email = $validated['email'];
        if (isset($validated['password'])) $user->password = Hash::make($validated['password']);
        if (isset($validated['image_path'])) $user->image_path = $validated['image_path'];
        $user->save();

        // cuma admin yang bisa ubah role
        if (isset($validated['role']) && $authUser->hasRole('admin')) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->load('roles'),
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

        // hapus image jika ada
        if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
            Storage::disk('public')->delete($user->image_path);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Helper: format user with image_url
     */
    private function formatUser(User $user)
    {
        $user->load('roles');
        $user->image_url = $user->image_path
            ? (str_starts_with($user->image_path, 'http')
                ? $user->image_path
                : asset('storage/' . $user->image_path))
            : null;
        return $user;
    }
}
