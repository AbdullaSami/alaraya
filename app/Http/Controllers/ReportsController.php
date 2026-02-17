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
            $shipOrder = ShipOrderData::where('order_number', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::query()
                                        ->where('order_number', "%{$searchValue}%")
                                        ->orWhereHas('shipPolicies', function ($query) use ($searchValue) {
                                            $query->where('policy_number', "%{$searchValue}%");
                                        })
                                        ->orWhereHas('shipBookings', function ($query) use ($searchValue) {
                                            $query->where('booking_number', "%{$searchValue}%");
                                        })
                                        ->with('operatingOrder.drivers')
                                        ->with('operatingOrder.vehicles')
                                        ->with('shipPolicies.operatingOrder')
                                        ->with('shipPolicies.vehicleDriverAssignments.vehicle')
                                        ->with('shipPolicies.vehicleDriverAssignments.driver')
                                        ->with('shipPolicies.vehicleDriverAssignments.shipContainers')
                                        ->with('shipBookings')
                                        ->get();
                return response()->json($report, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function torrentsReports($number){
        try {
            $searchValue = $number;

            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::where('order_number', "%{$searchValue}%")
                                    ->with('operatingOrder')
                                    ->with('operatingOrder.torrentContainers')
                                    ->with('operatingOrder.torrentContainers.container')
                                    ->with('operatingOrder.torrentContainers.container.shipContainersDetail')
                                    ->with('shipPolicies.vehicleDriverAssignments.shipContainers')
                                    ->with('shipBookings')
                                    ->get();
            return response()->json($report, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function LoadingWithdrawalReport($number){
        try {
            $searchValue = $number;

            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', "%{$searchValue}%")->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $report = ShipOrderData::where('order_number', "%{$searchValue}%")
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
