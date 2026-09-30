<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Destination;
use App\Models\Drivers;
use App\Models\OperatingOrder;
use App\Models\OperatingOrderDriver;
use App\Models\OperatingOrderVehicle;
use App\Models\Policy;
use App\Models\ShipLineClient;
use App\Models\ShipOrderData;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriverAssignment;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListingSearchPaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Keep this feature test isolated from the application's configured database.
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        Artisan::call('migrate', ['--force' => true]);

        $user = User::create([
            'full_name' => 'Test Admin',
            'user_name' => 'listing-test-admin',
            'email' => 'listing-test@example.test',
            'password' => 'password',
            'phone_number' => '01000000000',
        ]);
        Role::findOrCreate('admin', 'web');
        $user->assignRole('admin');
        $this->actingAs($user);
    }

    public function test_search_and_pagination_preserve_response_shapes(): void
    {
        $client = Client::create(['client_name' => 'Acme Shipping']);
        $shippingLine = ShippingLine::create(['shipping_line_name' => 'Test Line']);
        $destination = Destination::create(['destination_name' => 'Test Port']);
        $driver = Drivers::create([
            'driver_name' => 'Ahmed Driver',
            'phone_number' => '01000000001',
            'identification_number' => '12345678901234',
            'license_number' => 'LICENSE-1',
        ]);
        $vehicle = Vehicle::create(['vehicle_number' => 'TRUCK-42']);

        foreach (['ORD-ALPHA', 'ORD-BETA'] as $number) {
            $order = ShipOrderData::create([
                'order_number' => $number,
                'order_type' => 'import',
                'noloans' => 100,
            ]);
            if ($number === 'ORD-ALPHA') {
                ShipLineClient::create([
                    'ship_order_data_id' => $order->id,
                    'client_id' => $client->id,
                    'shipping_line_id' => $shippingLine->id,
                    'destination_id' => $destination->id,
                ]);
            }

            $operatingOrder = OperatingOrder::create(['ship_order_data_id' => $order->id]);
            $policy = Policy::create([
                'ship_order_data_id' => $order->id,
                'operating_order_id' => $operatingOrder->id,
            ]);

            if ($number === 'ORD-ALPHA') {
                OperatingOrderDriver::create([
                    'operating_order_id' => $operatingOrder->id,
                    'driver_id' => $driver->id,
                ]);
                OperatingOrderVehicle::create([
                    'operating_order_id' => $operatingOrder->id,
                    'vehicle_id' => $vehicle->id,
                ]);
                VehicleDriverAssignment::create([
                    'policy_id' => $policy->id,
                    'driver_id' => $driver->id,
                    'vehicle_id' => $vehicle->id,
                ]);
            }
        }

        $this->getJson('/api/operating-orders?search=TRUCK-42&per_page=1')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/policies?search=Ahmed%20Driver&per_page=1')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/ship-order-data?search=Acme%20Shipping')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonCount(1, 'data.data');

        $response = $this->withHeaders(['Origin' => 'https://example.test'])
            ->getJson('/api/reports/vehicle-statements?search=ORD&per_page=1&page=2')
            ->assertOk()
            ->assertHeader('X-Current-Page', '2')
            ->assertHeader('X-Total-Count', '2')
            ->assertHeader('Access-Control-Expose-Headers', 'X-Current-Page, X-Last-Page, X-Per-Page, X-Total-Count')
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.per_page', 1)
            ->assertJsonPath('pagination.total', 2)
            ->assertJsonPath('pagination.from', 2)
            ->assertJsonPath('pagination.to', 2)
            ->assertJsonPath('totals.total_noloan', 200)
            ->assertJsonCount(1, 'data');

        $this->assertSame(['success', 'data', 'pagination', 'totals'], array_keys($response->json()));

        $this->getJson('/api/reports/vehicle-statements?search=TRUCK-42')
            ->assertOk()
            ->assertHeader('X-Total-Count', '1')
            ->assertJsonCount(1, 'data');
    }

    public function test_vehicle_statement_totals_are_policy_based_and_partition_by_clearance_state(): void
    {
        $mixedOrder = ShipOrderData::create([
            'order_number' => 'ORD-MIXED',
            'order_type' => 'import',
            'noloans' => 100,
        ]);
        $mixedOperatingOrder = OperatingOrder::create([
            'ship_order_data_id' => $mixedOrder->id,
        ]);

        Policy::create([
            'ship_order_data_id' => $mixedOrder->id,
            'operating_order_id' => $mixedOperatingOrder->id,
            'policy_number' => 'BL-CLEARED',
            'covenant_amount' => 30,
            'clearance_date' => '2026-09-01',
        ]);
        Policy::create([
            'ship_order_data_id' => $mixedOrder->id,
            'operating_order_id' => $mixedOperatingOrder->id,
            'policy_number' => 'BL-UNCLEARED-MIXED',
            'covenant_amount' => 70,
        ]);

        $unclearedOrder = ShipOrderData::create([
            'order_number' => 'ORD-UNCLEARED',
            'order_type' => 'import',
            'noloans' => 200,
        ]);
        $unclearedOperatingOrder = OperatingOrder::create([
            'ship_order_data_id' => $unclearedOrder->id,
        ]);
        Policy::create([
            'ship_order_data_id' => $unclearedOrder->id,
            'operating_order_id' => $unclearedOperatingOrder->id,
            'policy_number' => 'BL-UNCLEARED',
            'covenant_amount' => 50,
        ]);

        // A ship order without a policy is not part of a vehicle statement.
        ShipOrderData::create([
            'order_number' => 'ORD-WITHOUT-POLICY',
            'order_type' => 'import',
            'noloans' => 999,
        ]);

        $all = $this->getJson('/api/reports/vehicle-statements')
            ->assertOk()
            ->assertJsonPath('pagination.total', 2)
            ->assertJsonPath('totals.total_noloan', 400)
            ->assertJsonPath('totals.total_covenant_amount', 150)
            ->assertJsonPath('totals.total_driver_extras', 0)
            ->assertJsonPath('totals.net_amount', 250);

        $cleared = $this->getJson('/api/reports/vehicle-statements?is_cleared=true')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('totals.total_noloan', 100)
            ->assertJsonPath('totals.total_covenant_amount', 30)
            ->assertJsonPath('totals.total_driver_extras', 0)
            ->assertJsonPath('totals.net_amount', 70);

        $uncleared = $this->getJson('/api/reports/vehicle-statements?is_cleared=false')
            ->assertOk()
            ->assertJsonPath('pagination.total', 2)
            ->assertJsonPath('totals.total_noloan', 300)
            ->assertJsonPath('totals.total_covenant_amount', 120)
            ->assertJsonPath('totals.total_driver_extras', 0)
            ->assertJsonPath('totals.net_amount', 180);

        foreach (['total_noloan', 'total_covenant_amount', 'total_driver_extras', 'net_amount'] as $total) {
            $this->assertSame(
                $all->json("totals.{$total}"),
                $cleared->json("totals.{$total}") + $uncleared->json("totals.{$total}"),
                "The {$total} total should reconcile across clearance states."
            );
        }
    }
}
