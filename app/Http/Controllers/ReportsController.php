<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShipOrderData;
class ReportsController extends Controller
{
    private function getBaseReport($number, $relations = [])
    {
        try {
            $searchValue = $number;

            // Check if ship order exists
            $shipOrder = ShipOrderData::where('order_number', $searchValue)->first();
            if (!$shipOrder) {
                return response()->json(['error' => 'Ship order not found'], 404);
            }

            $query = ShipOrderData::query()
                ->where('order_number', $searchValue)
                ->orWhereHas('shipPolicies', function ($query) use ($searchValue) {
                    $query->where('policy_number', $searchValue);
                })
                ->orWhereHas('shipBookings', function ($query) use ($searchValue) {
                    $query->where('booking_number', $searchValue);
                });

            foreach ($relations as $relation) {
                $query->with($relation);
            }

            $report = $query->get();
            return response()->json($report, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function vehicleReport($number){
        $relations = [
            'operatingOrder.drivers',
            'operatingOrder.vehicles',
            'shipPolicies.vehicleDriverAssignments.vehicle',
            'shipPolicies.vehicleDriverAssignments.driver',
            'shipPolicies.vehicleDriverAssignments.shipContainers',
            'shipBookings'
        ];

        return $this->getBaseReport($number, $relations);
    }

    public function torrentsReports($number){
        $relations = [
            'operatingOrder',
            'operatingOrder.torrentContainers',
            'operatingOrder.torrentContainers.container',
            'operatingOrder.torrentContainers.container.shipContainersDetail',
            'shipPolicies.vehicleDriverAssignments.shipContainers',
            'shipBookings'
        ];

        return $this->getBaseReport($number, $relations);
    }

    public function LoadingWithdrawalReport($number){
        $relations = [
            'operatingOrder.drivers',
            'operatingOrder.vehicles',
            'operatingOrder.torrentContainers',
            'operatingOrder.torrentContainers.container',
            'operatingOrder.torrentContainers.container.shipContainersDetail',
            'shipLineClients.factory',
            'shipLineClients'
        ];

        return $this->getBaseReport($number, $relations);
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
