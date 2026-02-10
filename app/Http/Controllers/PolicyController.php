<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\ShipOrderData;
use App\Models\VehicleDriverAssignment;
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
            'vehicle_driver_assignments' => 'required|array|min:1',
            'vehicle_driver_assignments.*.vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_driver_assignments.*.driver_id' => 'required|exists:drivers,id',
            'vehicle_driver_assignments.*.ship_container_id' => 'required|exists:ship_containers_details,id',
        ]);

        $shipOrderData = ShipOrderData::findOrFail($validated['ship_order_data_id']);

        if (!$shipOrderData->canCreateMorePolicies()) {
            return response()->json([
                'message' => 'Cannot create more policies. Maximum limit of ' . $shipOrderData->transfers_count . ' policies reached.',
                'remaining_slots' => $shipOrderData->remainingPolicySlots()
            ], 422);
        }

        // Create policy
        $policyData = $request->only([
            'ship_order_data_id',
            'operating_order_id',
            'covenant_amount',
            'policy_type',
            'policy_aging_date',
            'policy_loading_date'
        ]);

        $policy = Policy::create($policyData);

        // Create vehicle driver assignments
        $assignments = [];
        foreach ($validated['vehicle_driver_assignments'] as $assignmentData) {
            $assignment = VehicleDriverAssignment::create([
                'vehicle_id' => $assignmentData['vehicle_id'],
                'driver_id' => $assignmentData['driver_id'],
                'ship_container_id' => $assignmentData['ship_container_id'],
                'policy_id' => $policy->id,
            ]);
            $assignments[] = $assignment->load(['vehicle', 'driver', 'shipContainer']);
        }

        return response()->json([
            'message' => 'Policy created successfully with vehicle assignments',
            'policy' => $policy->load(['shipOrderData', 'operatingOrder', 'vehicleDriverAssignments.vehicle', 'vehicleDriverAssignments.driver', 'vehicleDriverAssignments.shipContainer'])
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
            'vehicle_driver_assignments' => 'sometimes|array|min:1',
            'vehicle_driver_assignments.*.vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_driver_assignments.*.driver_id' => 'required|exists:drivers,id',
            'vehicle_driver_assignments.*.ship_container_id' => 'required|exists:ship_containers_details,id',
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

        // Update policy data
        $policyData = $request->only([
            'ship_order_data_id',
            'operating_order_id',
            'covenant_amount',
            'policy_type',
            'policy_aging_date',
            'policy_loading_date'
        ]);

        $policy->update($policyData);

        // Update vehicle driver assignments if provided
        if (isset($validated['vehicle_driver_assignments'])) {
            // Delete existing assignments
            $policy->vehicleDriverAssignments()->delete();

            // Create new assignments
            $assignments = [];
            foreach ($validated['vehicle_driver_assignments'] as $assignmentData) {
                $assignment = VehicleDriverAssignment::create([
                    'vehicle_id' => $assignmentData['vehicle_id'],
                    'driver_id' => $assignmentData['driver_id'],
                    'ship_container_id' => $assignmentData['ship_container_id'],
                    'policy_id' => $policy->id,
                ]);
                $assignments[] = $assignment->load(['vehicle', 'driver', 'shipContainer']);
            }
        }

        return response()->json([
            'message' => 'Policy updated successfully',
            'policy' => $policy->load(['shipOrderData', 'operatingOrder', 'vehicleDriverAssignments.vehicle', 'vehicleDriverAssignments.driver', 'vehicleDriverAssignments.shipContainer'])
        ]);
    }

    public function destroy(string $id)
    {
        $policy = Policy::findOrFail($id);

        // Delete related vehicle driver assignments first
        $policy->vehicleDriverAssignments()->delete();

        // Delete the policy
        $policy->delete();

        return response()->json([
            'message' => 'Policy and related vehicle assignments deleted successfully'
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
            $query->with(['operatingOrder', 'vehicleDriverAssignments.vehicle', 'vehicleDriverAssignments.driver', 'vehicleDriverAssignments.shipContainer']);
        }])->findOrFail($shipOrderDataId);

        return response()->json([
            'ship_order_data' => $shipOrderData,
            'policies' => $shipOrderData->policies,
            'remaining_slots' => $shipOrderData->remainingPolicySlots(),
            'can_create_more' => $shipOrderData->canCreateMorePolicies()
        ]);
    }

    public function addVehicleAssignment(Request $request, $policyId)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'ship_container_id' => 'required|exists:ship_containers_details,id',
        ]);

        $policy = Policy::findOrFail($policyId);

        $assignment = VehicleDriverAssignment::create([
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => $validated['driver_id'],
            'ship_container_id' => $validated['ship_container_id'],
            'policy_id' => $policy->id,
        ]);

        return response()->json([
            'message' => 'Vehicle assignment added successfully',
            'assignment' => $assignment->load(['vehicle', 'driver', 'shipContainer'])
        ], 201);
    }

    public function removeVehicleAssignment($assignmentId)
    {
        $assignment = VehicleDriverAssignment::findOrFail($assignmentId);
        $assignment->delete();

        return response()->json([
            'message' => 'Vehicle assignment removed successfully'
        ]);
    }

    public function updateVehicleAssignment(Request $request, $assignmentId)
    {
        $validated = $request->validate([
            'vehicle_id' => 'sometimes|required|exists:vehicles,id',
            'driver_id' => 'sometimes|required|exists:drivers,id',
            'ship_container_id' => 'sometimes|required|exists:ship_containers_details,id',
        ]);

        $assignment = VehicleDriverAssignment::findOrFail($assignmentId);
        $assignment->update($validated);

        return response()->json([
            'message' => 'Vehicle assignment updated successfully',
            'assignment' => $assignment->load(['vehicle', 'driver', 'shipContainer'])
        ]);
    }
}
