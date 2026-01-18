<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
class DestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $destinations = Destination::all();
            return response()->json($destinations, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve destinations'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'destination_name' => 'required|string|max:255',
                'noloan_code' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
            ]);

            $destination = Destination::create($validatedData);
            return response()->json([
                'message' => 'Destination created successfully',
                'destination' => $destination
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create destination'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validatedData = $request->validate([
                'destination_name' => 'sometimes|required|string|max:255',
                'noloan_code' => 'sometimes|nullable|string|max:100',
                'notes' => 'sometimes|nullable|string',
            ]);

            $destination = Destination::findOrFail($id);
            $destination->update($validatedData);

            return response()->json([
                'message' => 'Destination updated successfully',
                'destination' => $destination
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update destination'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $destination = Destination::findOrFail($id);
            $destination->delete();

            return response()->json(['message' => 'Destination deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete destination'], 500);
        }
    }
}
