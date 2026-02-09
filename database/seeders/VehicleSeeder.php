<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'vehicle_number' => 'TRK-001',
                'trailer_number' => 'TRL-001',
                'badge_number' => 'BDG-001',
                'notes' => 'Primary transport vehicle',
            ],
            [
                'vehicle_number' => 'TRK-002',
                'trailer_number' => 'TRL-002',
                'badge_number' => 'BDG-002',
                'notes' => 'Heavy duty truck',
            ],
            [
                'vehicle_number' => 'TRK-003',
                'trailer_number' => 'TRL-003',
                'badge_number' => 'BDG-003',
                'notes' => 'Long haul vehicle',
            ],
            [
                'vehicle_number' => 'TRK-004',
                'trailer_number' => 'TRL-004',
                'badge_number' => 'BDG-004',
                'notes' => 'Local delivery truck',
            ],
            [
                'vehicle_number' => 'TRK-005',
                'trailer_number' => 'TRL-005',
                'badge_number' => 'BDG-005',
                'notes' => 'Backup vehicle',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
