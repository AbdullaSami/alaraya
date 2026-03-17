<?php

namespace App\Http\Controllers;

use App\Models\TransportReceipt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TransportReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $transportReceipts = TransportReceipt::with('shipOrder')->get();

        return response()->json([
            'success' => true,
            'data' => $transportReceipts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ship_order_id' => 'required|exists:ship_order_data,id',
                'army_scales' => 'nullable|numeric|min:0',
                'roads_and_bridges' => 'nullable|numeric|min:0',
                'road_cards' => 'nullable|numeric|min:0',
                'governorate_voucher' => 'nullable|numeric|min:0',
                'tips' => 'nullable|numeric|min:0',
                'official_receipts' => 'nullable|numeric|min:0',
                'overnight_leave' => 'nullable|numeric|min:0',
                'tarif_receipts' => 'nullable|numeric|min:0',
                'third_party_car_rental' => 'nullable|numeric|min:0',
                'customs_clearance' => 'nullable|numeric|min:0',
                'bill_of_lading_amendment' => 'nullable|numeric|min:0',
                'third_party_vehicle_leave' => 'nullable|numeric|min:0',
                'brokers' => 'nullable|numeric|min:0',
            ]);

            $total = collect($validated)->only([
                'army_scales',
                'roads_and_bridges',
                'road_cards',
                'governorate_voucher',
                'tips',
                'official_receipts',
                'overnight_leave',
                'tarif_receipts',
                'third_party_car_rental',
                'customs_clearance',
                'bill_of_lading_amendment',
                'third_party_vehicle_leave',
                'brokers',
            ])->sum();
            $transportReceipt = TransportReceipt::create($validated);

            $shipOrder = $transportReceipt->shipOrder;
            $treasury = $shipOrder->treasuries()->first();
            if ($treasury) {
                $treasury->balance -= $total;
                $treasury->save();
            }
            return response()->json([
                'success' => true,
                'message' => 'Transport receipt created successfully',
                'data' => $transportReceipt->load('shipOrder')
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transport receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transportReceipt = TransportReceipt::with('shipOrder')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $transportReceipt
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transport receipt not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transport receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $transportReceipt = TransportReceipt::findOrFail($id);

            $validated = $request->validate([
                'ship_order_id' => 'sometimes|required|exists:ship_order_data,id',
                'army_scales' => 'nullable|numeric|min:0',
                'roads_and_bridges' => 'nullable|numeric|min:0',
                'road_cards' => 'nullable|numeric|min:0',
                'governorate_voucher' => 'nullable|numeric|min:0',
                'tips' => 'nullable|numeric|min:0',
                'official_receipts' => 'nullable|numeric|min:0',
                'overnight_leave' => 'nullable|numeric|min:0',
                'tarif_receipts' => 'nullable|numeric|min:0',
                'third_party_car_rental' => 'nullable|numeric|min:0',
                'customs_clearance' => 'nullable|numeric|min:0',
                'bill_of_lading_amendment' => 'nullable|numeric|min:0',
                'third_party_vehicle_leave' => 'nullable|numeric|min:0',
                'brokers' => 'nullable|numeric|min:0',
            ]);

            $transportReceipt->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transport receipt updated successfully',
                'data' => $transportReceipt->load('shipOrder')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transport receipt not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transport receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $transportReceipt = TransportReceipt::findOrFail($id);
            $transportReceipt->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transport receipt deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transport receipt not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transport receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
