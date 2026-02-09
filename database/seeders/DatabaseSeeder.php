<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            ClientSeeder::class,
            FactorySeeder::class,
            VehicleSeeder::class,
            DriversSeeder::class,
            DestinationSeeder::class,
            ShippingLineSeeder::class,
        ]);
    }
}
