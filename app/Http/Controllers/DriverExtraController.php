<?php

namespace App\Http\Controllers;

use App\Http\Resources\DriverExtraResource;
use App\Models\DriverExtra;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;

class DriverExtraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $extras = DriverExtra::with(
                'vehicleDriverAssignment.vehicle',
                'vehicleDriverAssignment.driver',
                'vehicleDriverAssignment.policy',
                'vehicleDriverAssignment.policy.shipOrderData',
                )->get();

            return response()->json(DriverExtraResource::collection($extras), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve driver extras', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'vehicle_driver_assignment_id' => 'required|exists:vehicle_driver_assignments,id',
                'extras' => 'required|array|min:1',
                'extras.*.extra_amount' => 'required|numeric|min:0',
                'extras.*.extra_type' => 'required|string|max:255',
            ]);

            $createdExtras = [];

            foreach ($validatedData['extras'] as $item) {

                $item['vehicle_driver_assignment_id'] = $validatedData['vehicle_driver_assignment_id'];
                $extra = DriverExtra::create($item);

                $createdExtras[] = $extra->load('vehicleDriverAssignment');
            }

            return response()->json([
                'message' => 'Driver extras created successfully',
                'data' => DriverExtraResource::collection($createdExtras),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create driver extras',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $extra = DriverExtra::with(
                'vehicleDriverAssignment.vehicle',
                'vehicleDriverAssignment.driver',
                'vehicleDriverAssignment.policy',
                'vehicleDriverAssignment.policy.shipOrderData',
                )->findOrFail($id);

            return response()->json(new DriverExtraResource($extra), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve driver extra', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $extra = DriverExtra::findOrFail($id);
            $validatedData = $request->validate([
                'vehicle_driver_assignment_id' => 'sometimes|required|exists:vehicle_driver_assignments,id',
                'extra_amount' => 'sometimes|required|numeric|min:0',
                'extra_type' => 'sometimes|required|string|max:255',
            ]);
            $extra->update($validatedData);
            return response()->json([
                'message' => 'Driver extra updated successfully',
                'data' => new DriverExtraResource($extra->load('vehicleDriverAssignment')),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update driver extra', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $extra = DriverExtra::findOrFail($id);

            $extra->delete();

            return response()->json(['message' => 'Driver extra deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete driver extra', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get extras by vehicle driver assignment.
     */
    public function getByAssignment(string $assignmentId)
    {
        try {
            $extras = DriverExtra::where('vehicle_driver_assignment_id', $assignmentId)
                ->with('vehicleDriverAssignment')
                ->get();

            return response()->json(DriverExtraResource::collection($extras), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve extras for assignment', 'message' => $e->getMessage()], 500);
        }
    }
}
