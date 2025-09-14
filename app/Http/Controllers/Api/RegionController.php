<?php

namespace App\Http\Controllers\Api;

use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Region::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string'
        ]);

        $region = Region::create($data);
        return response()->json($region, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $region = Region::findOrFail($id);
        return response()->json($region);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $region = Region::findOrFail($id);
        $region = Region::update($request->all());
        return response()->json($region);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $region = Region::findOrFail($id);
        $region->delete();
        return response()->json(null, 204);
    }
}
