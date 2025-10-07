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

        // Rangkai URL gambar tiap umkm
        $umkms->transform(function ($umkm) {
            if ($umkm->image_path) {
                $umkm->image_path = asset('storage/' . $umkm->image_path);
            }
            return $umkm;
        });

        return response()->json($umkms);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'user_id' => 'required|exists:users,id',
            'region_id' => 'nullable|exists:regions,id'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/umkm', 'public');
            $data['image_path'] = asset('storage/' . $path);
        }

        $umkm = Umkm::create($data);

        // kalau kamu mau include relasi, tinggal load:
        $umkm->load(['user', 'region', 'products']); // opsional, tergantung relasi di model

        return response()->json([
            'message' => 'UMKM created successfully',
            'data' => [
                'id' => $umkm->id,
                'name' => $umkm->name,
                'description' => $umkm->description,
                'address' => $umkm->address,
                'phone' => $umkm->phone,
            'image_url' => $umkm->image_path ?? null,
            'user' => $umkm->user ?? null,
            'region' => $umkm->region ?? null,
            'products' => $umkm->products ?? [],
            'created_at' => $umkm->created_at,
            'updated_at' => $umkm->updated_at,
        ]
    ], 201);
}



    public function show($id)
    {
        $umkm = Umkm::with('products')->findOrFail($id);

        if ($umkm->image_path) {
            $umkm->image_path = asset('storage/' . $umkm->image_path);
        }

        return response()->json([
            'message' => 'success',
            'data' => $umkm
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $umkm = Umkm::findOrFail($id);
        $umkm->update($request->all());

        if ($umkm->image_path) {
            $umkm->image_path = asset('storage/' . $umkm->image_path);
        }

        return response()->json([
            'message' => 'updated',
            'data' => $umkm
        ], 200);
    }

    public function destroy($id)
    {
        $umkm = Umkm::findOrFail($id);
        $umkm->delete();

        return response()->json([
            'message' => 'deleted',
            'data' => $umkm
        ], 204);
    }
}
