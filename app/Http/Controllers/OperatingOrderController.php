<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperatingOrder;

class OperatingOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = OperatingOrder::with([
            'shipOrderData',
            'drivers.driver',
            'vehicles.vehicle',
        ])->latest()->get();

        return response()->json($orders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                // Operating Order Data
                'ship_order_data_id' => 'required|exists:ship_order_data,id',
                'is_operating_order' => 'required|boolean',
                'requirement_notes' => 'nullable|string',
                // Vehicles and Drivers Data
                'driver_ids' => 'nullable|array',
                'driver_ids.*' => 'exists:drivers,id',
                'vehicle_ids' => 'nullable|array',
                'vehicle_ids.*' => 'exists:vehicles,id',
            ]);
            // Create Operating Order
            $operatingOrder = OperatingOrder::create([
                'ship_order_data_id' => $validatedData['ship_order_data_id'],
                'is_operating_order' => $validatedData['is_operating_order'],
                'requirement_notes' => $validatedData['requirement_notes'] ?? null,
            ]);


            if ((count($validatedData['driver_ids']) <= $operatingOrder->shipOrderData()->transfers_count)
                ||
                (count($validatedData['vehicle_ids']) <= $operatingOrder->shipOrderData()->transfers_count)
            ) {
                return response()->json(['error' => 'At least one driver or vehicle must be assigned to the operating order.'], 422);
            }

            // Attach Vehicles and Drivers
            if (!empty($validatedData['driver_ids'])) {
                foreach ($validatedData['driver_ids'] as $driverId) {
                    $operatingOrder->drivers()->create(['driver_id' => $driverId]);
                }
            }

            if (!empty($validatedData['vehicle_ids'])) {
                foreach ($validatedData['vehicle_ids'] as $vehicleId) {
                    $operatingOrder->vehicles()->create(['vehicle_id' => $vehicleId]);
                }
            }

            return response()->json($operatingOrder, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create operating order', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($operating_order)
    {
        $order = OperatingOrder::with([
            'shipOrderData',
            'drivers.driver',
            'vehicles.vehicle',
        ])->findOrFail($operating_order);

        return response()->json($order);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $operating_order)
    {
        try {
            $order = OperatingOrder::findOrFail($operating_order);

            $validatedData = $request->validate([
                'is_operating_order' => 'sometimes|boolean',
                'requirement_notes' => 'nullable|string',

                'driver_ids' => 'nullable|array',
                'driver_ids.*' => 'exists:drivers,id',

                'vehicle_ids' => 'nullable|array',
                'vehicle_ids.*' => 'exists:vehicles,id',
            ]);

            $order->update([
                'is_operating_order' => $validatedData['is_operating_order'] ?? $order->is_operating_order,
                'requirement_notes' => $validatedData['requirement_notes'] ?? $order->requirement_notes,
            ]);

            $transfersCount = $order->shipOrderData->transfers_count;

            if (
                (isset($validatedData['driver_ids']) && count($validatedData['driver_ids']) < $transfersCount) ||
                (isset($validatedData['vehicle_ids']) && count($validatedData['vehicle_ids']) < $transfersCount)
            ) {
                return response()->json([
                    'error' => 'Drivers and vehicles count must match transfers count'
                ], 422);
            }

            // Sync Drivers
            if (isset($validatedData['driver_ids'])) {
                $order->drivers()->delete();
                foreach ($validatedData['driver_ids'] as $driverId) {
                    $order->drivers()->create(['driver_id' => $driverId]);
                }
            }

            // Sync Vehicles
            if (isset($validatedData['vehicle_ids'])) {
                $order->vehicles()->delete();
                foreach ($validatedData['vehicle_ids'] as $vehicleId) {
                    $order->vehicles()->create(['vehicle_id' => $vehicleId]);
                }
            }

            return response()->json($order->load([
                'shipOrderData',
                'drivers.driver',
                'vehicles.vehicle',
            ]));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update operating order',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($operating_order)
    {
        $order = OperatingOrder::findOrFail($operating_order);

        $order->drivers()->delete();
        $order->vehicles()->delete();
        $order->delete();

        return response()->json([
            'message' => 'Operating order deleted successfully'
        ]);
    }
}
