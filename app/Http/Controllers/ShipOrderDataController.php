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

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                // Ship Order Data
                'order_type' => 'required|in:import,export',
                'client_requirements' => 'nullable|string',
                'noloans' => 'nullable|integer',
                'shipping_date' => 'nullable|date',
                'aging_date' => 'nullable|date',
                'notes' => 'nullable|string',

                // Ship Line Client Data
                'client_id' => 'required|exists:clients,id',
                'shipping_line_id' => 'required|exists:shipping_lines,id',
                'destination_id' => 'required|exists:destinations,id',

                // Factories (array)
                'factories' => 'required|array|min:1',
                'factories.*.factory_id' => 'required|exists:factories,id',

                // Ship Policies (array)
                'policies' => 'required_without:bookings|array|min:1',
                'policies.*.policy_number' => 'required|string|unique:ship_policies,policy_number',
                'policies.*.containers' => 'required|array|min:1',
                'policies.*.containers.*.container_number' => 'required|string',

                // Ship Bookings (array)
                'bookings' => 'required_without:policies|array|min:1',
                'bookings.*.booking_number' => 'required|string|unique:ship_bookings,booking_number',
                'bookings.*.containers' => 'required|array|min:1',
                'bookings.*.containers.*.container_number' => 'required|string',

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
                $orderNumber = $this->generateOrderNumber();

                // Create Ship Order Data
                $shipOrderData = ShipOrderData::create([
                    'order_number' => $orderNumber,
                    'order_type' => $validatedData['order_type'],
                    'client_requirements' => $validatedData['client_requirements'] ?? null,
                    'noloans' => $validatedData['noloans'] ?? 0,
                    'shipping_date' => $validatedData['shipping_date'] ?? null,
                    'aging_date' => $validatedData['aging_date'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
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

                // Create Ship Policies and their containers
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

                // Create Ship Bookings and their containers
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
}
