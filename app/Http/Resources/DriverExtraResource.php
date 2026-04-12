<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverExtraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_driver_assignment_id' => $this->vehicle_driver_assignment_id,
            'extra_amount' => $this->extra_amount,
            'extra_type' => $this->extra_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationship
            'vehicle_driver_assignment' => $this->when($this->relationLoaded('vehicleDriverAssignment'), function () {
                return [
                    'id' => $this->vehicleDriverAssignment->id,
                    'vehicle_id' => $this->vehicleDriverAssignment->vehicle_id,
                    'driver_id' => $this->vehicleDriverAssignment->driver_id,
                    'policy_id' => $this->vehicleDriverAssignment->policy_id,
                    'created_at' => $this->vehicleDriverAssignment->created_at,
                    'updated_at' => $this->vehicleDriverAssignment->updated_at,
                    'driver' => $this->when($this->vehicleDriverAssignment->relationLoaded('driver'), function () {
                        return [
                            'id' => $this->vehicleDriverAssignment->driver->id,
                            'name' => $this->vehicleDriverAssignment->driver->driver_name ?? null,
                            'phone' => $this->vehicleDriverAssignment->driver->phone_number ?? null,
                        ];
                    }),
                    'vehicle' => $this->when($this->vehicleDriverAssignment->relationLoaded('vehicle'), function () {
                        return [
                            'id' => $this->vehicleDriverAssignment->vehicle->id,
                            'vehicle_number' => $this->vehicleDriverAssignment->vehicle->vehicle_number,
                            'trailer_number' => $this->vehicleDriverAssignment->vehicle->trailer_number,
                            'badge_number' => $this->vehicleDriverAssignment->vehicle->badge_number,
                            'type' => $this->vehicleDriverAssignment->vehicle->type,
                            'office_name' => $this->vehicleDriverAssignment->vehicle->office_name,
                        ];
                    }),
                    'policy' => $this->when($this->vehicleDriverAssignment->relationLoaded('policy'), function () {
                        return [
                            'id' => $this->vehicleDriverAssignment->policy->id,
                            'policy_number' => $this->vehicleDriverAssignment->policy->policy_number
                        ];
                    }),
                    'ship_order_data' => $this->when($this->vehicleDriverAssignment->relationLoaded('policy') && $this->vehicleDriverAssignment->policy->relationLoaded('shipOrderData'), function () {
                        return [
                            'id' => $this->vehicleDriverAssignment->policy->shipOrderData->id,
                            'ship_order_number' => $this->vehicleDriverAssignment->policy->shipOrderData->ship_order_number
                        ];
                    }),
                ];
            }),
        ];
    }
}
