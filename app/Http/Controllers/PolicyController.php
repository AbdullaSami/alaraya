<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\ShipOrderData;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::with(['shipOrderData', 'operatingOrder', 'vehicleDriverAssignments'])->get();
        return response()->json($policies);
    }

    public function create()
    {
        $shipOrderData = ShipOrderData::with(['policies'])->get();
        return response()->json($shipOrderData);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ship_order_data_id' => 'required|exists:ship_order_data,id',
            'operating_order_id' => 'required|exists:operating_orders,id',
            'covenant_amount' => 'nullable|numeric|min:0',
            'policy_type' => 'boolean',
            'policy_aging_date' => 'required_if:policy_type,true|nullable|date',
            'policy_loading_date' => 'required_if:policy_type,true|nullable|date|after_or_equal:policy_aging_date',
        ]);

        $shipOrderData = ShipOrderData::findOrFail($validated['ship_order_data_id']);

        if (!$shipOrderData->canCreateMorePolicies()) {
            return response()->json([
                'message' => 'Cannot create more policies. Maximum limit of ' . $shipOrderData->transfers_count . ' policies reached.',
                'remaining_slots' => $shipOrderData->remainingPolicySlots()
            ], 422);
        }

        $policy = Policy::create($validated);

        return response()->json([
            'message' => 'Policy created successfully',
            'policy' => $policy->load(['shipOrderData', 'operatingOrder'])
        ], 201);
    }

    public function show(string $id)
    {
        $policy = Policy::with(['shipOrderData', 'operatingOrder', 'vehicleDriverAssignments.vehicle', 'vehicleDriverAssignments.driver', 'vehicleDriverAssignments.shipContainer'])
            ->findOrFail($id);

        return response()->json($policy);
    }

    public function edit(string $id)
    {
        $policy = Policy::with(['shipOrderData', 'operatingOrder'])->findOrFail($id);
        return response()->json($policy);
    }

    public function update(Request $request, string $id)
    {
        $policy = Policy::findOrFail($id);

        $validated = $request->validate([
            'ship_order_data_id' => 'sometimes|required|exists:ship_order_data,id',
            'operating_order_id' => 'sometimes|required|exists:operating_orders,id',
            'covenant_amount' => 'nullable|numeric|min:0',
            'policy_type' => 'sometimes|boolean',
            'policy_aging_date' => 'required_if:policy_type,true|nullable|date',
            'policy_loading_date' => 'required_if:policy_type,true|nullable|date|after_or_equal:policy_aging_date',
        ]);

        if (isset($validated['ship_order_data_id'])) {
            $newShipOrderData = ShipOrderData::findOrFail($validated['ship_order_data_id']);

            if ($newShipOrderData->id !== $policy->ship_order_data_id) {
                $currentPoliciesCount = $newShipOrderData->policies()->count();
                if ($currentPoliciesCount >= $newShipOrderData->transfers_count) {
                    return response()->json([
                        'message' => 'Cannot move policy to this ship order. Maximum limit reached.',
                        'remaining_slots' => $newShipOrderData->remainingPolicySlots()
                    ], 422);
                }
            }
        }

        $policy->update($validated);

        return response()->json([
            'message' => 'Policy updated successfully',
            'policy' => $policy->load(['shipOrderData', 'operatingOrder'])
        ]);
    }

    public function destroy(string $id)
    {
        $policy = Policy::findOrFail($id);
        $policy->delete();

        return response()->json([
            'message' => 'Policy deleted successfully'
        ]);
    }

    public function generatePolicyNumber()
    {
        $policyNumber = Policy::generatePolicyNumber();
        return response()->json([
            'policy_number' => $policyNumber
        ]);
    }

    public function getByShipOrderData($shipOrderDataId)
    {
        $shipOrderData = ShipOrderData::with(['policies' => function($query) {
            $query->with(['operatingOrder', 'vehicleDriverAssignments']);
        }])->findOrFail($shipOrderDataId);

        return response()->json([
            'ship_order_data' => $shipOrderData,
            'policies' => $shipOrderData->policies,
            'remaining_slots' => $shipOrderData->remainingPolicySlots(),
            'can_create_more' => $shipOrderData->canCreateMorePolicies()
        ]);
    }
}
