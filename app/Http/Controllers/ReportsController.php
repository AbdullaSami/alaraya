<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipOrderData;
class ReportsController extends Controller
{
    public function vehicleReport($number){
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
                                    ->get();
                return response()->json(
                    [
                        "ship_orders_data" => $report,
                        "ship_order_type" => $shipOrder->getShipOrderType(),
                    ],
                 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function torrentsReports($number){
        try {
            $searchValue = $number;
            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', 'LIKE', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::query()
                                    ->where('order_number', 'LIKE', "%{$searchValue}%")
                                    ->with('operatingOrder')
                                    ->with('operatingOrder.torrentContainers')
                                    ->with('operatingOrder.torrentContainers.container')
                                    ->get();
            return response()->json(
                                    [
                        "ship_orders_data" => $report,
                        "ship_order_type" => $shipOrder->getShipOrderType(),
                    ],
                 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function LoadingWithdrawalReport($number){
        try {
            $searchValue = $number;

            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', 'LIKE', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::query()
                                    ->where('order_number', 'LIKE', "%{$searchValue}%")
                                    ->with('operatingOrder.drivers')
                                    ->with('operatingOrder.vehicles')
                                    ->with('operatingOrder.torrentContainers')
                                    ->with('operatingOrder.torrentContainers.container')
                                    ->with('operatingOrder.torrentContainers.container.shipContainersDetail')
                                    ->with('shipLineClients.factory')
                                    ->with('shipLineClients')
                                    ->get();
            return response()->json($report, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    // public function alrayaVehicleReports($number){
    //     try {
    //         $searchValue = $number;
    //         $report = Vehicle::where('number', $searchValue)->get();
    //         return response()->json($report, 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(['error' => $th->getMessage()], 500);
    //     }
    // }
}
