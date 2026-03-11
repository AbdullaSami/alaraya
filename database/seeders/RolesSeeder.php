<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles safely
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $operationsRole = Role::firstOrCreate(['name' => 'operations']);
        $dataEntryRole = Role::firstOrCreate(['name' => 'data_entry']);

        // System models
        $models = [
            'users',
            'vehicles',
            'drivers',
            'clients',
            'factories',
            'destinations',
            'shipping_lines',
            'ship_order_data',
            'operation_orders',
            'policies',
            'reports',
            'transport_receipts',
            'treasury'
        ];

        // CRUD + extra system actions
        $actions = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'export',
            'print',
            'approve'
        ];

        $permissions = [];

        foreach ($models as $model) {
            foreach ($actions as $action) {

                $permission = Permission::firstOrCreate([
                    'name' => "{$action} {$model}"
                ]);

                $permissions[] = $permission;
            }
        }

        // Admin gets all permissions
        $adminRole->syncPermissions($permissions);

        // Create default admin user safely
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@alraya.com'],
            [
                'full_name' => 'Admin User',
                'user_name' => 'admin',
                'phone_number' => '1234567890',
                'password' => Hash::make('password123'),
            ]
        );

        // Assign admin role
        $adminUser->syncRoles([$adminRole]);
    }
}
