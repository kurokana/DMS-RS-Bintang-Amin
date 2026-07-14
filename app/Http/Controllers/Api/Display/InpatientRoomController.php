<?php

namespace App\Http\Controllers\Api\Display;

use App\Http\Controllers\Controller;
use App\Models\InpatientRoom;
use App\Events\InpatientRoomAvailabilityChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InpatientRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $rooms = InpatientRoom::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_code' => 'required|string|unique:inpatient_rooms,room_code',
            'name' => 'required|string',
            'floor' => 'required|string',
            'building' => 'required|string',
            'bed_total' => 'required|integer|min:0',
            'bed_occupied' => 'required|integer|min:0',
            'bed_available' => 'required|integer|min:0',
        ]);

        $room = InpatientRoom::create($validated);

        return response()->json([
            'success' => true,
            'data' => $room
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $room = InpatientRoom::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $room = InpatientRoom::findOrFail($id);

        $validated = $request->validate([
            'room_code' => 'sometimes|required|string|unique:inpatient_rooms,room_code,' . $room->id,
            'name' => 'sometimes|required|string',
            'floor' => 'sometimes|required|string',
            'building' => 'sometimes|required|string',
            'bed_total' => 'sometimes|required|integer|min:0',
            'bed_occupied' => 'sometimes|required|integer|min:0',
            'bed_available' => 'sometimes|required|integer|min:0',
        ]);

        $room->update($validated);

        // Broadcast changes
        event(new InpatientRoomAvailabilityChanged($room->id, [
            'room_code' => $room->room_code,
            'name' => $room->name,
            'floor' => $room->floor,
            'building' => $room->building,
            'bed_total' => $room->bed_total,
            'bed_occupied' => $room->bed_occupied,
            'bed_available' => $room->bed_available,
        ]));

        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $room = InpatientRoom::findOrFail($id);
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruangan rawat inap berhasil dihapus.'
        ]);
    }
}
