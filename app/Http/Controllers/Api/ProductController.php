<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Product::with('umkm');

        if ($user && $user->hasRole('owner')) {
            $query->whereHas('umkm', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $products = $query->paginate($request->input('per_page', 15));

        $products->getCollection()->transform(function ($product) {
            $product->image_url = $product->image_path
                ? asset('storage/' . $product->image_path)
                : null;
            return $product;
        });

        return response()->json([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        // ✅ DEBUG: Log request untuk cek dari React (hapus setelah selesai debug)
        Log::info('Store Product Request', [
            'all_data' => $request->all(),
            'has_file' => $request->hasFile('image'),
            'file_info' => $request->file('image') ? [
                'name' => $request->file('image')->getClientOriginalName(),
                'size' => $request->file('image')->getSize(),
                'mime' => $request->file('image')->getMimeType()
            ] : null
        ]);

        $validated = $request->validate([
            'umkm_id' => 'required|exists:umkms,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Simpan PATH RELATIF ke database
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $product = Product::create([
            'umkm_id' => $validated['umkm_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'image_path' => $imagePath,
        ]);

        // Load relasi & tambahkan image_url untuk response
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

        // Tambahkan image_url untuk FE
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

        // ✅ DEBUG: Log request untuk cek dari React (hapus setelah selesai debug)
        \Log::info('Update Product Request', [
            'product_id' => $id,
            'all_data' => $request->all(),
            'has_file' => $request->hasFile('image'),
        ]);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            // Upload baru & simpan PATH RELATIF ke validated
            $validated['image_path'] = $request->file('image')->store('images', 'public');
        }

        // Update data
        $product->update($validated);

        // Refresh untuk dapetin data terbaru
        $product->refresh();
        $product->load('umkm');

        // Tambahkan image_url untuk response FE
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
