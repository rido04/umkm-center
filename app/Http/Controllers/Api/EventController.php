<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $events = Event::paginate($request->input('per_page', 15));

        $events->getCollection()->transform(function ($event) {
            $event->image_url = $event->image_path
                ? asset('storage/' . $event->image_path)
                : null;
            return $event;
        });

        return response()->json([
            'data' => $events->items(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
            'per_page' => $events->perPage(),
            'total' => $events->total(),
            'from' => $events->firstItem(),
            'to' => $events->lastItem(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'places' => 'nullable|string',
            'event_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // kalau ada file image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/events', 'public');
            $data['image_path'] = $path;
        }

        $event = Event::create($data);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'places' => $event->places,
                'event_date' => $event->event_date,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'image_url' => $event->image_path ? asset('storage/' . $event->image_path) : null,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        $event->image_url = $event->image_path ? asset('storage/' . $event->image_path) : null;

        return response()->json([
            'message' => 'success',
            'data' => $event
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'places' => 'nullable|string',
            'event_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // hapus gambar lama kalau ada
            if ($event->image_path && Storage::disk('public')->exists($event->image_path)) {
                Storage::disk('public')->delete($event->image_path);
            }

            $path = $request->file('image')->store('images/events', 'public');
            $data['image_path'] = $path;
        }

        $event->update($data);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'places' => $event->places,
                'event_date' => $event->event_date,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'image_url' => $event->image_path ? asset('storage/' . $event->image_path) : null,
                'updated_at' => $event->updated_at,
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);

        if ($event->image_path && Storage::disk('public')->exists($event->image_path)) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], 200);
    }
}
