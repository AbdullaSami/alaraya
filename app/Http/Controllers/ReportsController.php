<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipOrderData;

class ReportsController extends Controller
{
    public function vehicleReport($number)
    {
        try {
            $searchValue = $number;

            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', 'LIKE', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::query()
                ->where('order_number', 'LIKE', "%{$searchValue}%")
                ->with('policies.vehicleDriverAssignments.vehicle')
                ->with('policies.vehicleDriverAssignments.driver')
                ->with('policies.vehicleDriverAssignments.shipContainers.torrentContainers')
                ->with('shipLineClients.client')
                ->get();
            return response()->json(
                [
                    "ship_orders_data" => $report,
                    "ship_order_type" => $shipOrder->getShipOrderType(),
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function torrentsReports($number)
    {
        try {
            $searchValue = $number;
            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', 'LIKE', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::query()
                ->where('order_number', 'LIKE', "%{$searchValue}%")
                ->with('policies.vehicleDriverAssignments.shipContainers.torrentContainers')
                ->with('operatingOrder')
                ->with('operatingOrder.torrentContainers.container')
                ->with('shipLineClients.client')
                ->get();
            return response()->json(
                [
                    "ship_orders_data" => $report,
                    "ship_order_type" => $shipOrder->getShipOrderType(),
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function LoadingWithdrawalReport($number)
    {
        try {
            $searchValue = $number;

            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', 'LIKE', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::query()
                ->where('order_number', 'LIKE', "%{$searchValue}%")
                ->with('operatingOrder.vehicles.vehicle')
                ->with('operatingOrder.drivers.driver')
                ->with('operatingOrder.torrentContainers')
                ->with('operatingOrder.torrentContainers.container')
                ->with('shipLineClients.shipLineClientFactories.factory')
                ->with('shipLineClients.client')
                ->get();
            return response()->json(
                [
                    "ship_orders_data" => $report,
                    "ship_order_type" => $shipOrder->getShipOrderType(),
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function clientAccountStatements(Request $request){
        try {
            // Search ship orders by order number and/or client name
            $query = ShipOrderData::with([
                'shipLineClients.client',
                'operatingOrder',
                'policies.vehicleDriverAssignments.vehicle',
                'policies.vehicleDriverAssignments.driver',
                'policies.transportReceipts',
                'policies',
                'transportReceipt']);

            $number = $request->number;
            $clientName = $request->clientName;

            // Apply filters if parameters are provided
            if (!empty($number)) {
                $query->where('order_number', 'LIKE', "%{$number}%");
            }

            if (!empty($clientName)) {
                $query->whereHas('shipLineClients.client', function($query) use ($clientName) {
                    $query->where('client_name', 'LIKE', "%{$clientName}%");
                });
            }

            $shipOrders = $query->get();

            if ($shipOrders->isEmpty()) {
                return response()->json([
                    'error' => 'No ship orders found for the given order number and client name'
                ], 404);
            }

            // Initialize totals
            $totalShipOrdersCount = $shipOrders->count();
            $totalOperatingOrders = 0;
            $totalOrdersNoloans = 0;
            $totalTransportReceiptsSum = 0;
            $totalNoloanSum = 0;

            $shipOrdersDetails = [];

            foreach ($shipOrders as $shipOrder) {
                // Count operating orders
                $operatingOrdersCount = $shipOrder->operatingOrder ? 1 : 0;
                $totalOperatingOrders += $operatingOrdersCount;

                // Add noloans
                $noloans = $shipOrder->noloans ?? 0;
                $totalOrdersNoloans += $noloans;

                // Calculate transport receipts sum for this ship order
                $transportReceiptsSum = 0;
                if ($shipOrder->transportReceipt) {
                    foreach ($shipOrder->transportReceipt as $receipt) {
                        $transportReceiptsSum +=
                            ($receipt->army_scales ?? 0) +
                            ($receipt->roads_and_bridges ?? 0) +
                            ($receipt->road_cards ?? 0) +
                            ($receipt->governorate_voucher ?? 0) +
                            ($receipt->tips ?? 0) +
                            ($receipt->official_receipts ?? 0) +
                            ($receipt->overnight_leave ?? 0) +
                            ($receipt->tarif_receipts ?? 0) +
                            ($receipt->third_party_car_rental ?? 0) +
                            ($receipt->customs_clearance ?? 0) +
                            ($receipt->bill_of_lading_amendment ?? 0) +
                            ($receipt->third_party_vehicle_leave ?? 0) +
                            ($receipt->brokers ?? 0);
                    }
                }
                $totalTransportReceiptsSum += $transportReceiptsSum;

                // Add to total noloan sum
                $totalNoloanSum += $noloans;

                // Prepare ship order details
                $shipOrdersDetails[] = [
                    'order_number' => $shipOrder->order_number,
                    'order_type' => $shipOrder->order_type,
                    'noloans' => $noloans,
                    'shipping_date' => $shipOrder->shipping_date,
                    'aging_date' => $shipOrder->aging_date,
                    'containers_type' => $shipOrder->containers_type,
                    'containers_number' => $shipOrder->containers_number,
                    'loading_way' => $shipOrder->loading_way,
                    'transfers_count' => $shipOrder->transfers_count,
                    'has_operating_order' => $operatingOrdersCount > 0,
                    'transport_receipts_sum' => $transportReceiptsSum,
                    'transportReceipt' => $shipOrder->transportReceipt->policies,
                    'vehicle_driver_assignments' => $shipOrder->policies->flatMap(function($policy) {
                        return $policy->vehicleDriverAssignments->map(function($assignment) {
                            return [
                                'id' => $assignment->id,
                                'vehicle_info' => $assignment->vehicle ?? null,
                                'driver_info' => $assignment->driver ?? null,
                            ];
                        });
                    })->unique('id')->values(),
                    'clients' => $shipOrder->shipLineClients->map(function($shipLineClient) {
                        return [
                            'client_name' => $shipLineClient->client->client_name ?? null,
                            'contact_number' => $shipLineClient->client->contact_number ?? null,
                        ];
                    })
                ];
            }

            // Calculate net amount
            $netAmount = $totalTransportReceiptsSum + $totalNoloanSum;

            return response()->json([
                'success' => true,
                'data' => [
                    'ship_orders_count' => $totalShipOrdersCount,
                    'operating_orders' => $totalOperatingOrders,
                    'total_orders_noloans' => $totalOrdersNoloans,
                    'ship_orders_details' => $shipOrdersDetails,
                    'total_sum_transport_receipts' => $totalTransportReceiptsSum,
                    'total_sum_noloan' => $totalNoloanSum,
                    'net_amount' => $netAmount
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to generate client account statements',
                'message' => $th->getMessage()
            ], 500);
        }
    }


}
