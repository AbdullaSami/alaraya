<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'admin']);
        $operationsRole = Role::create(['name' => 'operations']);
        $dataEntryRole = Role::create(['name' => 'data_entry']);

        // create a default admin user
        $adminUser = User::create([
            'full_name' => 'Admin User',
            'user_name' => 'admin',
            'phone_number' => '1234567890',
            'email' => 'admin@alraya.com',
            'password' => Hash::make('password123'), // make sure to hash the password
        ]);
        $adminUser->assignRole($adminRole);
    }
}
