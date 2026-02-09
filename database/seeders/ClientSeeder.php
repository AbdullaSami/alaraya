<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'client_name' => 'Al-Rajhi Industrial Group',
                'contact_number' => '+966138012345',
                'notes' => 'Major industrial client with multiple factories',
            ],
            [
                'client_name' => 'Saudi Manufacturing Co.',
                'contact_number' => '+966138023456',
                'notes' => 'Manufacturing specialist client',
            ],
            [
                'client_name' => 'Gulf Trading Corporation',
                'contact_number' => '+966138034567',
                'notes' => 'Trading and distribution client',
            ],
            [
                'client_name' => 'National Logistics Ltd.',
                'contact_number' => '+966138045678',
                'notes' => 'Logistics and transportation client',
            ],
            [
                'client_name' => 'Middle East Exporters',
                'contact_number' => '+966138056789',
                'notes' => 'Export and import business client',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
