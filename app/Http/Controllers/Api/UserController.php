<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->except('index', 'show');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // if (!Auth::user()->hasRole('admin')) {
        //     return response()->json(['message' => 'Forbidden'], 403);
        // }

        $users = User::role('owner')->with('roles')->get();
        return response()->json($users);
    }

    /**
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'image_path' => 'nullable|string',
            'role'     => 'required|string|in:owner,admin',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'image_path' => $validated['image_path'],
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user->load('roles'),
        ], 201);
    }

    /**
     * Show user detail.
     */
    public function show(User $user)
    {
        if (!$user->hasRole('owner')) {
        return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($user->load('roles'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'image_path' => 'sometimes|string',
            'role'     => 'sometimes|string|in:owner,admin',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->load('roles'),
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
