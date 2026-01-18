<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $vehicles = Vehicle::all();
            return response()->json($vehicles, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve vehicles', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'vehicle_number' => 'required|string|unique:vehicles,vehicle_number',
                'trailer_number' => 'nullable|string',
                'badge_number'   => 'required|string',
                'notes'          => 'nullable|string',
            ]);
            $vehicle = Vehicle::create($validatedData);
            return response()->json(
                [
                    'message' => 'Vehicle created successfully',
                    'data'    => $vehicle
                ],
                201
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create vehicle', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);

            $validatedData = $request->validate([
                'vehicle_number' => 'sometimes|required|string|unique:vehicles,vehicle_number,' . $vehicle->id,
                'trailer_number' => 'sometimes|nullable|string',
                'badge_number'   => 'sometimes|required|string',
                'notes'          => 'sometimes|nullable|string',
            ]);

            $vehicle->update($validatedData);

            return response()->json(
                [
                    'message' => 'Vehicle updated successfully',
                    'data'    => $vehicle
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update vehicle', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
            return response()->json(['message' => 'Vehicle deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete vehicle', 'message' => $e->getMessage()], 500);
        }
    }
}
