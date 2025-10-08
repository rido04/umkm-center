<?php

namespace App\Http\Controllers\Api;

use App\Models\Umkm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UmkmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $umkms = Umkm::with('products')->get();

        // Rangkai full URL untuk image_path biar FE tinggal pakai
        $umkms->map(function ($umkm) {
            $umkm->image_url = $umkm->image_path
                ? asset('storage/' . $umkm->image_path)
                : null;
            return $umkm;
        });

        return response()->json($umkms);
    }

    /**
     * Store a newly created UMKM.
     */
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

        // Simpan image PATH RELATIF ke database (bukan full URL)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/umkm', 'public');
        }

        $umkm = Umkm::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'image_path' => $imagePath,  // Simpan path relatif
            'user_id' => $validated['user_id'],
            'region_id' => $validated['region_id'] ?? null,
        ]);

        // Load relasi & format FULL URL untuk response (FE pakai ini)
        $umkm->load(['user', 'region', 'products']);
        $umkm->image_url = $umkm->image_path
            ? asset('storage/' . $umkm->image_path)
            : null;

        return response()->json([
            'message' => 'UMKM created successfully',
            'data' => $umkm
        ], 201);
    }

    /**
     * Show UMKM detail.
     */
    public function show($id)
    {
        $umkm = Umkm::with('products')->findOrFail($id);

        // Format full URL biar FE tinggal pakai
        $umkm->image_url = $umkm->image_path
            ? asset('storage/' . $umkm->image_path)
            : null;

        return response()->json([
            'message' => 'success',
            'data' => $umkm
        ], 200);
    }

    /**
     * Update UMKM.
     */
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

        // Handle image upload (hapus lama, upload baru)
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($umkm->image_path && Storage::disk('public')->exists($umkm->image_path)) {
                Storage::disk('public')->delete($umkm->image_path);
            }

            // Upload gambar baru & simpan PATH RELATIF
            $validated['image_path'] = $request->file('image')->store('images/umkm', 'public');
        }

        // Update data
        $umkm->update($validated);

        // Format FULL URL untuk response ke FE
        $umkm->image_url = $umkm->image_path
            ? asset('storage/' . $umkm->image_path)
            : null;

        return response()->json([
            'message' => 'UMKM updated successfully',
            'data' => $umkm
        ], 200);
    }

    /**
     * Delete UMKM.
     */
    public function destroy($id)
    {
        $umkm = Umkm::findOrFail($id);

        // Hapus image dari storage jika ada (sekarang bisa jalan karena path relatif!)
        if ($umkm->image_path && Storage::disk('public')->exists($umkm->image_path)) {
            Storage::disk('public')->delete($umkm->image_path);
        }

        $umkm->delete();

        return response()->json([
            'message' => 'UMKM deleted successfully'
        ], 200);
    }
}
