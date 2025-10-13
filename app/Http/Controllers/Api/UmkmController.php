<?php

namespace App\Http\Controllers\Api;

use App\Models\Umkm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UmkmController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Umkm::with('products');

        if ($user && $user->hasRole('owner')) {
            $query->where('user_id', $user->id);
        }

        $umkms = $query->paginate($request->input('per_page', 3));

        $umkms->getCollection()->transform(function ($umkm) {
            $umkm->image_url = $umkm->image_path
                ? asset('storage/' . $umkm->image_path)
                : null;
            return $umkm;
        });

        // Return pagination di root array
        return response()->json([
            'data' => $umkms->items(),
            'current_page' => $umkms->currentPage(),
            'last_page' => $umkms->lastPage(),
            'per_page' => $umkms->perPage(),
            'total' => $umkms->total(),
            'from' => $umkms->firstItem(),
            'to' => $umkms->lastItem(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'user_id' => 'required|exists:users,id',
            'region_id' => 'nullable|exists:regions,id'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/umkm', 'public');
        }

        $umkm = Umkm::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'image_path' => $imagePath,
            'user_id' => $validated['user_id'],
            'region_id' => $validated['region_id'] ?? null,
        ]);

        $umkm->load(['user', 'region', 'products']);
        $umkm->image_url = $umkm->image_path
            ? asset('storage/' . $umkm->image_path)
            : null;

        return response()->json([
            'message' => 'UMKM created successfully',
            'data' => $umkm
        ], 201);
    }

    public function show($id)
    {
        $umkm = Umkm::with('products')->findOrFail($id);

        $umkm->image_url = $umkm->image_path
            ? asset('storage/' . $umkm->image_path)
            : null;

        return response()->json([
            'message' => 'success',
            'data' => $umkm
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $umkm = Umkm::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'user_id' => 'sometimes|exists:users,id',
            'region_id' => 'nullable|exists:regions,id'
        ]);

        if ($request->hasFile('image')) {
            if ($umkm->image_path && Storage::disk('public')->exists($umkm->image_path)) {
                Storage::disk('public')->delete($umkm->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('images/umkm', 'public');
        }

        $umkm->update($validated);

        $umkm->image_url = $umkm->image_path
            ? asset('storage/' . $umkm->image_path)
            : null;

        return response()->json([
            'message' => 'UMKM updated successfully',
            'data' => $umkm
        ], 200);
    }

    public function destroy($id)
    {
        $umkm = Umkm::findOrFail($id);

        if ($umkm->image_path && Storage::disk('public')->exists($umkm->image_path)) {
            Storage::disk('public')->delete($umkm->image_path);
        }

        $umkm->delete();

        return response()->json([
            'message' => 'UMKM deleted successfully'
        ], 200);
    }

    public function dropdown(Request $request)
    {
        $user = $request->user();

        $query = Umkm::select('id', 'name', 'image_path');

        if ($user && $user->hasRole('owner')) {
            $query->where('user_id', $user->id);
        }

        $umkms = $query->get();

        $umkms->transform(function ($umkm) {
            $umkm->image_url = $umkm->image_path
                ? asset('storage/' . $umkm->image_path)
                : null;
            return $umkm;
        });

        return response()->json([
            'message' => 'success',
            'data' => $umkms
        ], 200);
    }
}
