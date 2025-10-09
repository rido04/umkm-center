<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('umkm')->get();

        // Rangkai full URL untuk image_url (konsisten dengan UmkmController)
        $products->map(function ($product) {
            $product->image_url = $product->image_path
                ? asset('storage/' . $product->image_path)
                : null;
            return $product;
        });

        return response()->json($products);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'umkm_id' => 'required|exists:umkms,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Simpan file image ke storage/public/images (PATH RELATIF)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/products', 'public');
        }

        $product = Product::create([
            'umkm_id' => $validated['umkm_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image_path' => $imagePath,  // Simpan path relatif
        ]);

        // Load relasi & format FULL URL untuk response
        $product->load('umkm');
        $product->image_url = $product->image_path
            ? asset('storage/' . $product->image_path)
            : null;

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    /**
     * Show product detail.
     */
    public function show($id)
    {
        $product = Product::with('umkm')->findOrFail($id);

        // Format full URL untuk FE
        $product->image_url = $product->image_path
            ? asset('storage/' . $product->image_path)
            : null;

        return response()->json([
            'message' => 'success',
            'data' => $product
        ], 200);
    }

    /**
     * Update product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'umkm_id' => 'nullable|exists:umkms,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Filter data yang akan diupdate
        $dataToUpdate = [];

        foreach (['umkm_id', 'name', 'description', 'price'] as $field) {
            if ($request->has($field)) {
                $dataToUpdate[$field] = $validated[$field] ?? null;
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Hapus image lama
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            // Upload baru & simpan PATH RELATIF
            $dataToUpdate['image_path'] = $request->file('image')->store('images/products', 'public');
        }

        // Update product
        if (!empty($dataToUpdate)) {
            $product->update($dataToUpdate);
        }

        // Load relasi
        $product->load('umkm');

        // Format FULL URL untuk response
        $product->image_url = $product->image_path
            ? asset('storage/' . $product->image_path)
            : null;

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Hapus image dari storage
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
