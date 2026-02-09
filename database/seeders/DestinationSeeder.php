<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Destination;

class DestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = [
            [
                'destination_name' => 'Jeddah Islamic Port',
                'noloan_code' => 'JED001',
                'notes' => 'Main port for western region exports',
            ],
            [
                'destination_name' => 'King Abdulaziz Port Dammam',
                'noloan_code' => 'DAM001',
                'notes' => 'Eastern region main port',
            ],
            [
                'destination_name' => 'King Fahd Industrial Port',
                'noloan_code' => 'KFA001',
                'notes' => 'Industrial port in Yanbu',
            ],
            [
                'destination_name' => 'Riyadh Dry Port',
                'noloan_code' => 'RUH001',
                'notes' => 'Inland container terminal',
            ],
            [
                'destination_name' => 'Jubail Commercial Port',
                'noloan_code' => 'JUB001',
                'notes' => 'Commercial port in Jubail',
            ],
        ];

        foreach ($destinations as $destination) {
            Destination::create($destination);
        }
    }
}
