<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipOrderData;
use App\Models\ShipLineClient;
use App\Models\ShipLineClientFactory;
use App\Models\ShipPolicy;
use App\Models\ShipBooking;
use App\Models\ShipContactData;
use App\Models\ShipContainersDetail;
use App\Models\ClearanceData;
use Illuminate\Support\Facades\DB;

class ShipOrderDataController extends Controller
{

    /*
    * Display a listing of the Ship Order Data along with related data.
    */
    public function index(Request $request)
    {
        $shipOrders = ShipOrderData::with([
            'shipLineClients.client',
            'shipLineClients.shippingLine',
            'shipLineClients.destination',
            'shipLineClients.shipLineClientFactories.factory',
            'shipPolicies.shipContainersDetails',
            'shipBookings.shipContainersDetails',
            'shipBookings.clearanceData',
            'shipContactData'
        ])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $shipOrders
        ]);
    }

    /*
    * Generate a unique order number in the format ORD-{YEAR}-{SEQUENCE}
    */
    public function generateOrderNumber()
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {

            // Lock latest row for this year (prevents duplicates)
            $lastOrder = ShipOrderData::whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextNumber = $lastOrder
                ? ((int) substr($lastOrder->order_number, -4)) + 1
                : 1;

            return sprintf(
                'ORD-%d-%04d',
                $year,
                $nextNumber
            );
        });
    }

    /**
     * Store a newly created Ship Order Data along with related data.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                // Ship Order Data
                'order_number' => 'required|string|unique:ship_order_data,order_number',
                'order_type' => 'required|in:import,export',
                'client_requirements' => 'nullable|string',
                'noloans' => 'nullable|integer',
                'shipping_date' => 'nullable|date',
                'aging_date' => 'nullable|date',
                'notes' => 'nullable|string',
                'containers_type' => 'nullable|string',
                'containers_number' => 'nullable|string',
                'loading_way' => 'nullable|string',
                'transfers_count' => 'nullable|integer',

                // Ship Line Client Data
                'client_id' => 'required|exists:clients,id',
                'shipping_line_id' => 'required|exists:shipping_lines,id',
                'destination_id' => 'required|exists:destinations,id',

                // Factories (array)
                'factories' => 'required|array|min:1',
                'factories.*.factory_id' => 'required|exists:factories,id',

                // Ship Policies (array) - mutually exclusive with bookings
                'policies' => 'required_without:bookings|array|min:1',
                'policies.*.policy_number' => 'required|string|unique:ship_policies,policy_number',
                'policies.*.containers' => 'sometimes|array|min:1',
                'policies.*.containers.*.container_number' => 'sometimes|string',

                // Ship Bookings (array) - mutually exclusive with policies
                'bookings' => 'required_without:policies|array|min:1',
                'bookings.*.booking_number' => 'required|string|unique:ship_bookings,booking_number',
                'bookings.*.containers' => 'sometimes|array|min:1',
                'bookings.*.containers.*.container_number' => 'sometimes|string',

                // Ship Contact Data
                'contact_loading_name' => 'required|string',
                'contact_loading_number' => 'required|string',
                'contact_customs_officer_name' => 'required|string',
                'contact_customs_officer_number' => 'required|string',

                // Clearance Data (optional)
                'clearance_data' => 'nullable|array',
                'clearance_data.clearance_type' => 'nullable|string',
                'clearance_data.customs_location' => 'nullable|string',
                'clearance_data.redirect_location' => 'nullable|string',
            ]);

            return DB::transaction(function () use ($validatedData) {
                // Generate order number
                // $orderNumber = $this->generateOrderNumber();

                // Create Ship Order Data
                $shipOrderData = ShipOrderData::create([
                    'order_number' => $validatedData['order_number'],
                    'order_type' => $validatedData['order_type'],
                    'client_requirements' => $validatedData['client_requirements'] ?? null,
                    'noloans' => $validatedData['noloans'] ?? 0,
                    'shipping_date' => $validatedData['shipping_date'] ?? null,
                    'aging_date' => $validatedData['aging_date'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
                    'containers_type' => $validatedData['containers_type'] ?? null,
                    'containers_number' => $validatedData['containers_number'] ?? null,
                    'loading_way' => $validatedData['loading_way'] ?? null,
                    'transfers_count' => $validatedData['transfers_count'] ?? 1,
                ]);

                // Create Ship Line Client
                $shipLineClient = ShipLineClient::create([
                    'ship_order_data_id' => $shipOrderData->id,
                    'client_id' => $validatedData['client_id'],
                    'shipping_line_id' => $validatedData['shipping_line_id'],
                    'destination_id' => $validatedData['destination_id'],
                ]);

                // Create Ship Line Client Factories
                foreach ($validatedData['factories'] as $factory) {
                    ShipLineClientFactory::create([
                        'ship_line_client_id' => $shipLineClient->id,
                        'factory_id' => $factory['factory_id'],
                    ]);
                }

                // Create Ship Policies and their containers (if provided)
                if (isset($validatedData['policies'])) {
                    foreach ($validatedData['policies'] as $policyData) {
                        $policy = ShipPolicy::create([
                            'ship_order_data_id' => $shipOrderData->id,
                            'policy_number' => $policyData['policy_number'],
                        ]);

                        // Create containers for this policy
                        foreach ($policyData['containers'] as $containerData) {
                            ShipContainersDetail::create([
                                'policy_id' => $policy->id,
                                'container_number' => $containerData['container_number'],
                            ]);
                        }
                    }
                }

                // Create Ship Bookings and their containers (if provided)
                if (isset($validatedData['bookings'])) {
                    foreach ($validatedData['bookings'] as $bookingData) {
                        $booking = ShipBooking::create([
                            'ship_order_data_id' => $shipOrderData->id,
                            'booking_number' => $bookingData['booking_number'],
                        ]);

                        // Create containers for this booking
                        foreach ($bookingData['containers'] as $containerData) {
                            ShipContainersDetail::create([
                                'booking_id' => $booking->id,
                                'container_number' => $containerData['container_number'],
                            ]);
                        }

                        // Create Clearance Data if provided
                        if (!empty($validatedData['clearance_data'])) {
                            ClearanceData::create([
                                'booking_id' => $booking->id,
                                'clearance_type' => $validatedData['clearance_data']['clearance_type'] ?? null,
                                'customs_location' => $validatedData['clearance_data']['customs_location'] ?? null,
                                'redirect_location' => $validatedData['clearance_data']['redirect_location'] ?? null,
                            ]);
                        }
                    }
                }

                // Create Ship Contact Data
                ShipContactData::create([
                    'ship_order_data_id' => $shipOrderData->id,
                    'contact_loading_name' => $validatedData['contact_loading_name'],
                    'contact_loading_number' => $validatedData['contact_loading_number'],
                    'contact_customs_officer_name' => $validatedData['contact_customs_officer_name'],
                    'contact_customs_officer_number' => $validatedData['contact_customs_officer_number'],
                ]);

                return response()->json([
                    'message' => 'Ship order data created successfully',
                    'data' => $shipOrderData->load([
                        'shipLineClients.client',
                        'shipLineClients.shippingLine',
                        'shipLineClients.destination',
                        'shipLineClients.shipLineClientFactories.factory',
                        'shipPolicies.shipContainersDetails',
                        'shipBookings.shipContainersDetails',
                        'shipBookings.clearanceData',
                        'shipContactData'
                    ])
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create ship order data', 'message' => $e->getMessage()], 500);
        }
    }

    /*
    * Display the specified Ship Order Data along with related data.
    */
    public function show($id)
    {
        $shipOrderData = ShipOrderData::with([
            'shipLineClients.client',
            'shipLineClients.shippingLine',
            'shipLineClients.destination',
            'shipLineClients.shipLineClientFactories.factory',
            'shipPolicies.shipContainersDetails',
            'shipBookings.shipContainersDetails',
            'shipBookings.clearanceData',
            'shipContactData'
        ])->findOrFail($id);

        return response()->json([
            'data' => $shipOrderData
        ]);
    }

    /*
     * Update the specified Ship Order Data along with related data.
     */
    public function update(Request $request, $id)
    {
        try {

            $shipOrderData = ShipOrderData::findOrFail($id);

            $validatedData = $request->validate([

                'order_number' => 'required|string|unique:ship_order_data,order_number,' . $shipOrderData->id,
                'order_type' => 'required|in:import,export',
                'client_requirements' => 'nullable|string',
                'noloans' => 'nullable|integer',
                'shipping_date' => 'nullable|date',
                'aging_date' => 'nullable|date',
                'notes' => 'nullable|string',

                'client_id' => 'required|exists:clients,id',
                'shipping_line_id' => 'required|exists:shipping_lines,id',
                'destination_id' => 'required|exists:destinations,id',

                'factories' => 'required|array|min:1',
                'factories.*.factory_id' => 'required|exists:factories,id',

                // 🔥 mutually exclusive
                'policies' => 'required_without:bookings|prohibited_with:bookings|array|min:1',
                'policies.*.policy_number' => 'required|string',
                'policies.*.containers' => 'sometimes|array|min:1',
                'policies.*.containers.*.container_number' => 'sometimes|string',

                'bookings' => 'required_without:policies|prohibited_with:policies|array|min:1',
                'bookings.*.booking_number' => 'required|string',
                'bookings.*.containers' => 'sometimes|array|min:1',
                'bookings.*.containers.*.container_number' => 'sometimes|string',

                'contact_loading_name' => 'required|string',
                'contact_loading_number' => 'required|string',
                'contact_customs_officer_name' => 'required|string',
                'contact_customs_officer_number' => 'required|string',

                'clearance_data' => 'nullable|array',
                'clearance_data.clearance_type' => 'nullable|string',
                'clearance_data.customs_location' => 'nullable|string',
                'clearance_data.redirect_location' => 'nullable|string',
            ]);

            return DB::transaction(function () use ($validatedData, $shipOrderData) {

                // =========================
                // Update Ship Order
                // =========================
                $shipOrderData->update([
                    'order_number' => $validatedData['order_number'],
                    'order_type' => $validatedData['order_type'],
                    'client_requirements' => $validatedData['client_requirements'] ?? null,
                    'noloans' => $validatedData['noloans'] ?? 0,
                    'shipping_date' => $validatedData['shipping_date'] ?? null,
                    'aging_date' => $validatedData['aging_date'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
                ]);

                // =========================
                // Update Ship Line Client
                // =========================
                $shipLineClient = ShipLineClient::where('ship_order_data_id', $shipOrderData->id)->first();

                $shipLineClient->update([
                    'client_id' => $validatedData['client_id'],
                    'shipping_line_id' => $validatedData['shipping_line_id'],
                    'destination_id' => $validatedData['destination_id'],
                ]);

                // reset factories
                ShipLineClientFactory::where('ship_line_client_id', $shipLineClient->id)->delete();

                foreach ($validatedData['factories'] as $factory) {
                    ShipLineClientFactory::create([
                        'ship_line_client_id' => $shipLineClient->id,
                        'factory_id' => $factory['factory_id'],
                    ]);
                }

                // =========================
                // Reset Policies & Bookings
                // =========================
                ShipPolicy::where('ship_order_data_id', $shipOrderData->id)->delete();
                ShipBooking::where('ship_order_data_id', $shipOrderData->id)->delete();

                // =========================
                // Policies
                // =========================
                if (!empty($validatedData['policies'])) {

                    foreach ($validatedData['policies'] as $policyData) {

                        $policy = ShipPolicy::create([
                            'ship_order_data_id' => $shipOrderData->id,
                            'policy_number' => $policyData['policy_number'],
                        ]);

                        foreach ($policyData['containers'] as $containerData) {
                            ShipContainersDetail::create([
                                'policy_id' => $policy->id,
                                'container_number' => $containerData['container_number'],
                            ]);
                        }
                    }
                }

                // =========================
                // Bookings
                // =========================
                if (!empty($validatedData['bookings'])) {

                    foreach ($validatedData['bookings'] as $bookingData) {

                        $booking = ShipBooking::create([
                            'ship_order_data_id' => $shipOrderData->id,
                            'booking_number' => $bookingData['booking_number'],
                        ]);

                        foreach ($bookingData['containers'] as $containerData) {
                            ShipContainersDetail::create([
                                'booking_id' => $booking->id,
                                'container_number' => $containerData['container_number'],
                            ]);
                        }

                        if (!empty($validatedData['clearance_data'])) {
                            ClearanceData::create([
                                'booking_id' => $booking->id,
                                'clearance_type' => $validatedData['clearance_data']['clearance_type'] ?? null,
                                'customs_location' => $validatedData['clearance_data']['customs_location'] ?? null,
                                'redirect_location' => $validatedData['clearance_data']['redirect_location'] ?? null,
                            ]);
                        }
                    }
                }

                // =========================
                // Update Contact
                // =========================
                ShipContactData::updateOrCreate(
                    ['ship_order_data_id' => $shipOrderData->id],
                    [
                        'contact_loading_name' => $validatedData['contact_loading_name'],
                        'contact_loading_number' => $validatedData['contact_loading_number'],
                        'contact_customs_officer_name' => $validatedData['contact_customs_officer_name'],
                        'contact_customs_officer_number' => $validatedData['contact_customs_officer_number'],
                    ]
                );

                return response()->json([
                    'message' => 'Ship order updated successfully',
                    'data' => $shipOrderData->load([
                        'shipLineClients.client',
                        'shipLineClients.shippingLine',
                        'shipLineClients.destination',
                        'shipLineClients.shipLineClientFactories.factory',
                        'shipPolicies.shipContainersDetails',
                        'shipBookings.shipContainersDetails',
                        'shipBookings.clearanceData',
                        'shipContactData'
                    ])
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update ship order data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified Ship Order Data along with all related data.
     */
    public function destroy($id)
    {
        try {

            $shipOrderData = ShipOrderData::findOrFail($id);

            DB::transaction(function () use ($shipOrderData) {

                // Delete related policies containers
                ShipContainersDetail::whereIn(
                    'policy_id',
                    ShipPolicy::where('ship_order_data_id', $shipOrderData->id)->pluck('id')
                )->delete();

                // Delete related booking containers
                ShipContainersDetail::whereIn(
                    'booking_id',
                    ShipBooking::where('ship_order_data_id', $shipOrderData->id)->pluck('id')
                )->delete();

                // Delete clearance data
                ClearanceData::whereIn(
                    'booking_id',
                    ShipBooking::where('ship_order_data_id', $shipOrderData->id)->pluck('id')
                )->delete();

                // Delete policies & bookings
                ShipPolicy::where('ship_order_data_id', $shipOrderData->id)->delete();
                ShipBooking::where('ship_order_data_id', $shipOrderData->id)->delete();

                // Delete factories
                ShipLineClientFactory::whereIn(
                    'ship_line_client_id',
                    ShipLineClient::where('ship_order_data_id', $shipOrderData->id)->pluck('id')
                )->delete();

                // Delete ship line client
                ShipLineClient::where('ship_order_data_id', $shipOrderData->id)->delete();

                // Delete contact data
                ShipContactData::where('ship_order_data_id', $shipOrderData->id)->delete();

                // Finally delete order
                $shipOrderData->delete();
            });

            return response()->json([
                'message' => 'Ship order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete ship order',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
