<?php

namespace App\Http\Controllers\Api;

use App\Models\Umkm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UmkmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Umkm::with('products')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $umkm = Umkm::create($data);
        return response()->json([
            'message' => 'created',
            'data' => $umkm
        ], 201);
    }

    public function show($id)
    {
        $umkm = Umkm::with('products')->findOrFail($id);
        return response()->json([
            'message' => 'success',
            'data' => $umkm
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $umkm = Umkm::findOrFail($id);
        $umkm->update($request->all());
        return response()->json([
            'message' => 'updated',
            'data' => $umkm
        ], 200);
    }

    public function destroy($id)
    {
        $umkm = Umkm::findOrFail($id);
        $umkm->delete();
        return response()->json(null, 204);
    }
}
