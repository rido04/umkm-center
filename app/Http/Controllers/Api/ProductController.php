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

        // Rangkai full URL untuk image_path
        $products->map(function ($product) {
            $product->image_path = $product->image_path
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
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Simpan file image ke storage/public/images
        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('images', 'public');
        }

        $product = Product::create([
            'umkm_id' => $validated['umkm_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image_path' => $imagePath,
        ]);

        // Lengkapi URL-nya biar bisa langsung diakses
        $product->image_path = $product->image_path
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
        $product->image_path = $product->image_path
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
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Hapus image lama & simpan baru kalau ada file baru
        if ($request->hasFile('image_path')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image_path')->store('images', 'public');
        }

        $product->update($validated);

        // Format URL biar FE bisa langsung pakai
        $product->image_path = $product->image_path
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

        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
