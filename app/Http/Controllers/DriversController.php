<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Drivers;
class DriversController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $drivers = Drivers::all();
            return response()->json($drivers, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve drivers'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'driver_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'identification_number' => 'required|string|max:50',
                'license_number' => 'required|string|max:50',
            ]);

            $driver = Drivers::create($validatedData);
            return response()->json($driver, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create driver'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $driver = Drivers::findOrFail($id);

            $validatedData = $request->validate([
                'driver_name' => 'sometimes|required|string|max:255',
                'phone_number' => 'sometimes|required|string|max:20',
                'identification_number' => 'sometimes|required|string|max:50',
                'license_number' => 'sometimes|required|string|max:50',
            ]);

            $driver->update($validatedData);
            return response()->json($driver, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update driver'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $driver = Drivers::findOrFail($id);
            $driver->delete();
            return response()->json(['message' => 'Driver deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete driver'], 500);
        }
    }
}
