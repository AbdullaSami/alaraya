<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Drivers;

class DriversSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'driver_name' => 'Ahmed Mohammed',
                'phone_number' => '+966501234567',
                'identification_number' => '1001234567',
                'license_number' => 'SA-001234',
            ],
            [
                'driver_name' => 'Khalid Abdullah',
                'phone_number' => '+966502345678',
                'identification_number' => '1002345678',
                'license_number' => 'SA-002345',
            ],
            [
                'driver_name' => 'Mohammed Ali',
                'phone_number' => '+966503456789',
                'identification_number' => '1003456789',
                'license_number' => 'SA-003456',
            ],
            [
                'driver_name' => 'Saeed Hassan',
                'phone_number' => '+966504567890',
                'identification_number' => '1004567890',
                'license_number' => 'SA-004567',
            ],
            [
                'driver_name' => 'Omar Khalid',
                'phone_number' => '+966505678901',
                'identification_number' => '1005678901',
                'license_number' => 'SA-005678',
            ],
        ];

        foreach ($drivers as $driver) {
            Drivers::create($driver);
        }
    }
}
