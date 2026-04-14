<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipOrderData;
use App\Models\ShareLink;

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

    public function clientAccountStatements(Request $request)
    {
        try {
            // Search ship orders by order number and/or client name
            $query = ShipOrderData::with([
                'shipPolicies',
                'shipBookings',
                'shipLineClients.client',
                'operatingOrder',
                'policies.vehicleDriverAssignments.vehicle',
                'policies.vehicleDriverAssignments.driver',
                'policies.vehicleDriverAssignments.driverExtras',
                'policies.transportReceipts',
                'policies',
                'transportReceipt'
            ]);

            $number = $request->number;
            $clientName = $request->clientName;

            // Apply filters if parameters are provided
            if (!empty($number)) {
                $query->where('order_number', 'LIKE', "%{$number}%");
            }

            if (!empty($clientName)) {
                $query->whereHas('shipLineClients.client', function ($query) use ($clientName) {
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
            $totalDriverExtrasSum = 0;
            $totalCovenantAmountSum = 0;

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

                // Calculate driver extras sum for this ship order
                $driverExtrasSum = 0;
                foreach ($shipOrder->policies as $policy) {
                    foreach ($policy->vehicleDriverAssignments as $assignment) {
                        foreach ($assignment->driverExtras as $extra) {
                            $driverExtrasSum += ($extra->extra_amount ?? 0);
                        }
                    }
                }
                $totalDriverExtrasSum += $driverExtrasSum;

                // Calculate covenant amount sum for this ship order
                $covenantAmountSum = 0;
                foreach ($shipOrder->policies as $policy) {
                    $covenantAmountSum += ($policy->covenant_amount ?? 0);
                }
                $totalCovenantAmountSum += $covenantAmountSum;

                // Add to total noloan sum
                $totalNoloanSum += $noloans;

                // Prepare ship order details
                $shipOrdersDetails[] = [
                    'order_number' => $shipOrder->order_number,
                    'order_type' => $shipOrder->order_type,
                    'orderBooking' => $shipOrder->shipBookings->map(function ($booking) {
                        return [
                            'booking_number' => $booking->booking_number,
                            'booking_date' => $booking->booking_date,
                        ] ?? null;
                    }),
                    'orderPolicy' => $shipOrder->shipPolicies->map(function ($policy) {
                        return [
                            'policy_number' => $policy->policy_number,
                            'policy_date' => $policy->policy_date,
                        ] ?? null;
                    }),
                    'noloans' => $noloans,
                    'shipping_date' => $shipOrder->shipping_date,
                    'aging_date' => $shipOrder->aging_date,
                    'containers_type' => $shipOrder->containers_type,
                    'containers_number' => $shipOrder->containers_number,
                    'loading_way' => $shipOrder->loading_way,
                    'transfers_count' => $shipOrder->transfers_count,
                    'has_operating_order' => $operatingOrdersCount > 0,
                    'transport_receipts_sum' => $transportReceiptsSum,
                    'driver_extras_total' => $driverExtrasSum,
                    'covenant_amount_total' => $covenantAmountSum,
                    'transportReceipt' => $shipOrder->transportReceipt->map(function ($transportReceipt) {
                        $policy = $transportReceipt->policy;
                        return [
                            'policy_number' => $policy->policy_number,
                            'covenant_amount' => $policy->covenant_amount,
                            'transport_receipt_details' => [
                                'army_scales' => $transportReceipt->army_scales,
                                'roads_and_bridges' => $transportReceipt->roads_and_bridges,
                                'road_cards' => $transportReceipt->road_cards,
                                'governorate_voucher' => $transportReceipt->governorate_voucher,
                                'tips' => $transportReceipt->tips,
                                'official_receipts' => $transportReceipt->official_receipts,
                                'overnight_leave' => $transportReceipt->overnight_leave,
                                'tarif_receipts' => $transportReceipt->tarif_receipts,
                                'third_party_car_rental' => $transportReceipt->third_party_car_rental,
                                'customs_clearance' => $transportReceipt->customs_clearance,
                                'bill_of_lading_amendment' => $transportReceipt->bill_of_lading_amendment,
                                'third_party_vehicle_leave' => $transportReceipt->third_party_vehicle_leave,
                                'brokers' => $transportReceipt->brokers,
                            ]
                        ];
                    }),
                    'vehicle_driver_assignments' => $shipOrder->policies->flatMap(function ($policy) {
                        return $policy->vehicleDriverAssignments->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'vehicle_info' => $assignment->vehicle ?? null,
                                'driver_info' => $assignment->driver ?? null,
                                'driver_extras' => $assignment->driverExtras ?? null,
                            ];
                        });
                    })->unique('id')->values(),
                    'clients' => $shipOrder->shipLineClients->map(function ($shipLineClient) {
                        return [
                            'client_name' => $shipLineClient->client->client_name ?? null,
                            'contact_number' => $shipLineClient->client->contact_number ?? null,
                        ];
                    })
                ];
            }

            // Calculate net amount
            $netAmount = ($totalNoloanSum - $totalCovenantAmountSum) + $totalDriverExtrasSum;

            return response()->json([
                'success' => true,
                'data' => [
                    'ship_orders_count' => $totalShipOrdersCount,
                    'operating_orders' => $totalOperatingOrders,
                    'total_orders_noloans' => $totalOrdersNoloans,
                    'ship_orders_details' => $shipOrdersDetails,
                    'total_sum_transport_receipts' => $totalTransportReceiptsSum,
                    'total_sum_noloan' => $totalNoloanSum,
                    'total_driver_extras' => $totalDriverExtrasSum,
                    'total_covenant_amount' => $totalCovenantAmountSum,
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

    public function vehicleStatement(Request $request)
    {
        try {
            $vehicles = ShipOrderData::with([
                'policies' => function ($query) use ($request) {

                    // فلترة بتاريخ الإنشاء
                    if ($request->filled('from_date') && $request->filled('to_date')) {
                        $query->whereBetween('created_at', [
                            $request->from_date,
                            $request->to_date
                        ]);
                    }

                    // فلترة برقم العربية (داخل العلاقة)
                    if ($request->filled('vehicle_number')) {
                        $query->whereHas('vehicleDriverAssignments.vehicle', function ($q) use ($request) {
                            $q->where('vehicle_number', $request->vehicle_number);
                        });
                    }

                    $query->with([
                        'user',
                        'transportReceipts',
                        'vehicleDriverAssignments',
                        'vehicleDriverAssignments.vehicle',
                        'vehicleDriverAssignments.driver',
                        'vehicleDriverAssignments.driverExtras',
                    ]);
                },
                'shipLineClients.client',
                'shipLineClients.shippingLine',
                'shipLineClients.destination'
            ])->get();

            // Calculate driver extras total for each vehicle
            $totalNoloanSum = 0;
            $totalCovenantAmountSum = 0;
            $totalDriverExtrasSum = 0;

            $vehiclesWithExtras = $vehicles->map(function ($shipOrder) use (&$totalNoloanSum, &$totalCovenantAmountSum, &$totalDriverExtrasSum) {
                $driverExtrasSum = 0;
                $covenantAmountSum = 0;
                $noloans = $shipOrder->noloans ?? 0;

                foreach ($shipOrder->policies as $policy) {
                    $covenantAmountSum += ($policy->covenant_amount ?? 0);
                    foreach ($policy->vehicleDriverAssignments as $assignment) {
                        foreach ($assignment->driverExtras as $extra) {
                            $driverExtrasSum += ($extra->extra_amount ?? 0);
                        }
                    }
                }

                // Add driver_extras_total, covenant_amount_total, and noloans to the ship order
                $shipOrder->driver_extras_total = $driverExtrasSum;
                $shipOrder->covenant_amount_total = $covenantAmountSum;
                $shipOrder->noloans = $noloans;

                // Accumulate totals
                $totalNoloanSum += $noloans;
                $totalCovenantAmountSum += $covenantAmountSum;
                $totalDriverExtrasSum += $driverExtrasSum;

                return $shipOrder;
            });

            // Calculate net amount
            $netAmount = ($totalNoloanSum - $totalCovenantAmountSum) + $totalDriverExtrasSum;

            return response()->json([
                'success' => true,
                'data' => $vehiclesWithExtras,
                'totals' => [
                    'total_noloan' => $totalNoloanSum,
                    'total_covenant_amount' => $totalCovenantAmountSum,
                    'total_driver_extras' => $totalDriverExtrasSum,
                    'net_amount' => $netAmount
                ]
            ], 200);
        } catch (\Throwable $th) {
            \Log::error('Vehicle Statement Error:', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to generate vehicle statement',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function GenerateShareLink(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string',
                'body' => 'required|array',
            ]);

            $serialNumber = ShareLink::generateSerialNumber();
            $link = ShareLink::create([
                'serial_number' => $serialNumber,
                'user_id'       => auth()->id(),
                'type'          => $request->type,
                'body'          => json_encode($request->body),
            ]);

            return response()->json([
                'serial' => $link->serial_number,
                'url'    => url("/report/share/{$link->serial_number}"),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to generate share link',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getSharedReport(Request $request, $serial)
    {
        try {
            $link = ShareLink::where('serial_number', $serial)->firstOrFail();

            dd($link->body->number); // Debugging line to inspect the retrieved link
            $request->merge($link->body); // now $request has all body fields
            switch ($link->type) {
                case 'vehicle_report':
                    $report = $this->vehicleReport($link->body['number'] ?? '');
                    break;
                case 'torrents_report':
                    $report = $this->torrentsReports($link->body['number'] ?? '');
                    break;
                case 'loading_withdrawal_report':
                    $report = $this->LoadingWithdrawalReport($link->body['number'] ?? '');
                    break;
                case 'client_account_statements':
                    $report = $this->clientAccountStatements($request);
                    break;
                case 'vehicle_statement':
                    $report = $this->vehicleStatement($request);
                    break;
                default:
                    return response()->json(['error' => 'Invalid report type'], 400);
            }

            return $report;
        } catch (\Exception $th) {
            return response()->json([
                'error' => 'Failed to retrieve shared report',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),      // add this
                'file' => $th->getFile(),      // add this
            ], 500);
        }
    }
}
