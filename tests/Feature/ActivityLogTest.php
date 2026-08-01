<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Destination;
use App\Models\Drivers;
use App\Models\Treasury;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Activity Log Integration Test
 *
 * PURPOSE:
 *   Verifies that spatie/laravel-activitylog correctly persists created / updated /
 *   deleted events to the `activity_log` table in the REAL database, and that
 *   each log entry correctly records the authenticated user as the causer.
 *
 * DATABASE:
 *   Uses the real application database (no RefreshDatabase / transaction rollback).
 *   A single test user is created once for the whole class (setUpBeforeClass) and
 *   force-deleted after all tests complete (tearDownAfterClass).
 *   All other test records and their activity_log rows are deleted in each test's
 *   tearDown.
 *
 * RUN:
 *   php artisan test --filter=ActivityLogTest --stop-on-failure
 */
class ActivityLogTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Shared test user (created once, reused across all test methods)
    // -------------------------------------------------------------------------

    private static int $sharedUserId;

    /** Resolve the User model for this test run */
    private function causer(): User
    {
        return User::find(static::$sharedUserId);
    }

    // -------------------------------------------------------------------------
    // Per-test cleanup tracking
    // -------------------------------------------------------------------------

    /** @var array<string, array<int>> Model class => IDs to delete in tearDown */
    private array $createdIds = [];

    /** @var array<int> activity_log IDs to delete in tearDown */
    private array $activityIds = [];

    // -------------------------------------------------------------------------
    // Class-level setup/teardown — runs ONCE for the whole test class
    // -------------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Boot the Laravel application so models are available
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $uid = uniqid('altst_', true);

        $user = User::create([
            'full_name'    => 'Activity Log Tester',
            'user_name'    => substr($uid, 0, 30),
            'email'        => substr($uid, 0, 20) . '@test.local',
            'password'     => bcrypt('TestPassword123!'),
            'phone_number' => substr(preg_replace('/[^0-9]/', '', $uid), 0, 11),
        ]);

        static::$sharedUserId = $user->id;

        // Clean up the "User created" activity log produced for this test user
        Activity::where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->delete();
    }

    public static function tearDownAfterClass(): void
    {
        // Force-delete the shared test user (SoftDeletes — must use forceDelete)
        User::withTrashed()->where('id', static::$sharedUserId)->forceDelete();

        parent::tearDownAfterClass();
    }

    // -------------------------------------------------------------------------
    // Per-test tearDown: removes only the records created in THIS test
    // -------------------------------------------------------------------------

    protected function tearDown(): void
    {
        // 1. Remove activity_log rows
        if (!empty($this->activityIds)) {
            Activity::whereIn('id', $this->activityIds)->delete();
        }

        // 2. Remove model records
        foreach ($this->createdIds as $modelClass => $ids) {
            if (!empty($ids)) {
                $modelClass::whereIn('id', array_values($ids))->delete();
            }
        }

        $this->createdIds = [];
        $this->activityIds = [];

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function track(string $modelClass, int $id): void
    {
        $this->createdIds[$modelClass][] = $id;
    }

    private function snapshotLogs(): void
    {
        $ids = Activity::latest('id')->limit(30)->pluck('id')->toArray();
        $this->activityIds = array_unique(array_merge($this->activityIds, $ids));
    }

    private function latestLog(string $modelClass, int $subjectId, string $event): ?Activity
    {
        return Activity::where('subject_type', $modelClass)
            ->where('subject_id', $subjectId)
            ->where('event', $event)
            ->latest('id')
            ->first();
    }

    private function printLog(string $label, Activity $log): void
    {
        $props   = $log->properties->toArray();
        $newVals = $props['attributes'] ?? [];
        $oldVals = $props['old'] ?? [];

        echo PHP_EOL;
        echo "  {$label}" . PHP_EOL;
        echo "  ├── ID:          #{$log->id}" . PHP_EOL;
        echo "  ├── Log Name:    {$log->log_name}" . PHP_EOL;
        echo "  ├── Event:       {$log->event}" . PHP_EOL;
        echo "  ├── Description: {$log->description}" . PHP_EOL;
        echo "  ├── Subject:     " . class_basename($log->subject_type) . " #{$log->subject_id}" . PHP_EOL;
        echo "  ├── Causer:      " . ($log->causer_id ? "User #{$log->causer_id}" : 'NULL') . PHP_EOL;

        if (!empty($oldVals)) {
            echo "  ├── Old:  " . json_encode($oldVals, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
        if (!empty($newVals)) {
            echo "  └── New:  " . json_encode($newVals, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
        echo PHP_EOL;
    }

    // =========================================================================
    // TEST 1 — Client: CREATE
    // =========================================================================

    /** @test */
    public function it_logs_client_created_with_causer(): void
    {
        $this->actingAs($this->causer());

        $client = Client::create([
            'client_name'    => 'Test Client — Activity Log',
            'contact_number' => '0501234567',
            'notes'          => 'Created by activity log test',
        ]);
        $this->track(Client::class, $client->id);
        $this->snapshotLogs();

        $log = $this->latestLog(Client::class, $client->id, 'created');

        $this->assertNotNull($log, 'Expected a [created] activity log entry for Client.');
        $this->assertEquals('Client created', $log->description);
        $this->assertEquals('clients', $log->log_name);
        $this->assertEquals(User::class, $log->causer_type);
        $this->assertEquals(static::$sharedUserId, $log->causer_id);

        $attrs = $log->properties['attributes'];
        $this->assertEquals('Test Client — Activity Log', $attrs['client_name']);

        $this->printLog('✅ Client CREATED', $log);
    }

    // =========================================================================
    // TEST 2 — Client: UPDATE
    // =========================================================================

    /** @test */
    public function it_logs_client_updated_with_causer(): void
    {
        $this->actingAs($this->causer());

        $client = Client::create([
            'client_name'    => 'Update Test Client',
            'contact_number' => '0501111111',
            'notes'          => 'Before update',
        ]);
        $this->track(Client::class, $client->id);
        $this->snapshotLogs();

        $client->update([
            'client_name' => 'Updated Client Name',
            'notes'       => 'After update',
        ]);
        $this->snapshotLogs();

        $log = $this->latestLog(Client::class, $client->id, 'updated');

        $this->assertNotNull($log, 'Expected an [updated] activity log entry for Client.');
        $this->assertEquals('Client updated', $log->description);
        $this->assertEquals('clients', $log->log_name);
        $this->assertEquals(static::$sharedUserId, $log->causer_id);

        $props = $log->properties->toArray();
        $this->assertArrayHasKey('old', $props);
        $this->assertArrayHasKey('attributes', $props);
        $this->assertEquals('Update Test Client', $props['old']['client_name']);
        $this->assertEquals('Updated Client Name', $props['attributes']['client_name']);

        $this->printLog('✅ Client UPDATED', $log);
    }

    // =========================================================================
    // TEST 3 — Client: DELETE
    // =========================================================================

    /** @test */
    public function it_logs_client_deleted_with_causer(): void
    {
        $this->actingAs($this->causer());

        $client = Client::create([
            'client_name'    => 'Delete Test Client',
            'contact_number' => '0509999999',
            'notes'          => 'Will be deleted',
        ]);
        $clientId = $client->id;
        // Don't track — we will delete it right now
        $this->snapshotLogs();

        $client->delete();
        $this->snapshotLogs();

        $log = $this->latestLog(Client::class, $clientId, 'deleted');

        $this->assertNotNull($log, 'Expected a [deleted] activity log entry for Client.');
        $this->assertEquals('Client deleted', $log->description);
        $this->assertEquals('clients', $log->log_name);
        $this->assertEquals(static::$sharedUserId, $log->causer_id);

        $this->printLog('✅ Client DELETED', $log);
    }

    // =========================================================================
    // TEST 4 — Drivers: full lifecycle (CREATE → UPDATE → DELETE)
    // =========================================================================

    /** @test */
    public function it_logs_driver_full_lifecycle_with_causer(): void
    {
        $this->actingAs($this->causer());

        // CREATE
        $driver = Drivers::create([
            'driver_name'           => 'Test Driver — Log',
            'phone_number'          => '0501234560',
            'identification_number' => 'ID-TEST-001',
            'license_number'        => 'LIC-TEST-001',
        ]);
        $driverId = $driver->id;
        $this->track(Drivers::class, $driverId);
        $this->snapshotLogs();

        $createLog = $this->latestLog(Drivers::class, $driverId, 'created');
        $this->assertNotNull($createLog, 'Expected [created] log for Drivers.');
        $this->assertEquals('Drivers created', $createLog->description);
        $this->assertEquals('drivers', $createLog->log_name);
        $this->assertEquals(static::$sharedUserId, $createLog->causer_id);
        $this->printLog('✅ Drivers CREATED', $createLog);

        // UPDATE
        $driver->update(['driver_name' => 'Updated Driver Name']);
        $this->snapshotLogs();

        $updateLog = $this->latestLog(Drivers::class, $driverId, 'updated');
        $this->assertNotNull($updateLog, 'Expected [updated] log for Drivers.');
        $this->assertEquals('Drivers updated', $updateLog->description);
        $this->assertEquals(static::$sharedUserId, $updateLog->causer_id);

        $props = $updateLog->properties->toArray();
        $this->assertEquals('Test Driver — Log', $props['old']['driver_name']);
        $this->assertEquals('Updated Driver Name', $props['attributes']['driver_name']);
        $this->printLog('✅ Drivers UPDATED', $updateLog);

        // DELETE
        $driver->delete();
        $this->snapshotLogs();
        // Remove from cleanup — already deleted
        $this->createdIds[Drivers::class] = array_filter(
            $this->createdIds[Drivers::class] ?? [],
            fn($id) => $id !== $driverId
        );

        $deleteLog = $this->latestLog(Drivers::class, $driverId, 'deleted');
        $this->assertNotNull($deleteLog, 'Expected [deleted] log for Drivers.');
        $this->assertEquals('Drivers deleted', $deleteLog->description);
        $this->assertEquals(static::$sharedUserId, $deleteLog->causer_id);
        $this->printLog('✅ Drivers DELETED', $deleteLog);
    }

    // =========================================================================
    // TEST 5 — Vehicle: full lifecycle
    // =========================================================================

    /** @test */
    public function it_logs_vehicle_full_lifecycle_with_causer(): void
    {
        $this->actingAs($this->causer());

        $vehicle = Vehicle::create([
            'vehicle_number' => 'VEH-TEST-001',
            'trailer_number' => 'TRL-TEST-001',
            'badge_number'   => 'BADGE-001',
            'notes'          => 'Test vehicle',
            'type'           => 'truck',
            'office_name'    => 'Test Office',
        ]);
        $vehicleId = $vehicle->id;
        $this->track(Vehicle::class, $vehicleId);
        $this->snapshotLogs();

        // CREATE
        $createLog = $this->latestLog(Vehicle::class, $vehicleId, 'created');
        $this->assertNotNull($createLog);
        $this->assertEquals('Vehicle created', $createLog->description);
        $this->assertEquals('vehicles', $createLog->log_name);
        $this->assertEquals(static::$sharedUserId, $createLog->causer_id);
        $this->printLog('✅ Vehicle CREATED', $createLog);

        // UPDATE
        $vehicle->update(['office_name' => 'Updated Office']);
        $this->snapshotLogs();

        $updateLog = $this->latestLog(Vehicle::class, $vehicleId, 'updated');
        $this->assertNotNull($updateLog);
        $this->assertEquals('Vehicle updated', $updateLog->description);
        $this->assertEquals(static::$sharedUserId, $updateLog->causer_id);
        $this->printLog('✅ Vehicle UPDATED', $updateLog);

        // DELETE
        $vehicle->delete();
        $this->snapshotLogs();
        $this->createdIds[Vehicle::class] = array_filter(
            $this->createdIds[Vehicle::class] ?? [],
            fn($id) => $id !== $vehicleId
        );

        $deleteLog = $this->latestLog(Vehicle::class, $vehicleId, 'deleted');
        $this->assertNotNull($deleteLog);
        $this->assertEquals('Vehicle deleted', $deleteLog->description);
        $this->assertEquals(static::$sharedUserId, $deleteLog->causer_id);
        $this->printLog('✅ Vehicle DELETED', $deleteLog);
    }

    // =========================================================================
    // TEST 6 — Treasury: CREATE + UPDATE
    // =========================================================================

    /** @test */
    public function it_logs_treasury_create_and_update_with_causer(): void
    {
        $this->actingAs($this->causer());

        $treasury = Treasury::create([
            'name'    => 'Test Treasury — Log',
            'is_main' => false,
            'balance' => 0.00,
        ]);
        $this->track(Treasury::class, $treasury->id);
        $this->snapshotLogs();

        $createLog = $this->latestLog(Treasury::class, $treasury->id, 'created');
        $this->assertNotNull($createLog);
        $this->assertEquals('Treasury created', $createLog->description);
        $this->assertEquals('treasury', $createLog->log_name);
        $this->assertEquals(static::$sharedUserId, $createLog->causer_id);
        $this->printLog('✅ Treasury CREATED', $createLog);

        $treasury->update(['balance' => 5000.00]);
        $this->snapshotLogs();

        $updateLog = $this->latestLog(Treasury::class, $treasury->id, 'updated');
        $this->assertNotNull($updateLog);
        $this->assertEquals('Treasury updated', $updateLog->description);
        $this->assertEquals(static::$sharedUserId, $updateLog->causer_id);

        $props = $updateLog->properties->toArray();
        $this->assertEquals(0.00, (float) $props['old']['balance']);
        $this->assertEquals(5000.00, (float) $props['attributes']['balance']);
        $this->printLog('✅ Treasury UPDATED', $updateLog);
    }

    // =========================================================================
    // TEST 7 — Destination: full lifecycle
    // =========================================================================

    /** @test */
    public function it_logs_destination_full_lifecycle_with_causer(): void
    {
        $this->actingAs($this->causer());

        $destination = Destination::create([
            'destination_name' => 'Test Destination — Log',
            'noloan_code'      => 'NL-TEST-001',
            'notes'            => 'Test destination',
        ]);
        $destId = $destination->id;
        $this->track(Destination::class, $destId);
        $this->snapshotLogs();

        $createLog = $this->latestLog(Destination::class, $destId, 'created');
        $this->assertNotNull($createLog);
        $this->assertEquals('Destination created', $createLog->description);
        $this->assertEquals('destinations', $createLog->log_name);
        $this->assertEquals(static::$sharedUserId, $createLog->causer_id);
        $this->printLog('✅ Destination CREATED', $createLog);

        $destination->update(['destination_name' => 'Updated Destination']);
        $this->snapshotLogs();

        $updateLog = $this->latestLog(Destination::class, $destId, 'updated');
        $this->assertNotNull($updateLog);
        $this->assertEquals('Destination updated', $updateLog->description);
        $this->printLog('✅ Destination UPDATED', $updateLog);

        $destination->delete();
        $this->snapshotLogs();
        $this->createdIds[Destination::class] = array_filter(
            $this->createdIds[Destination::class] ?? [],
            fn($id) => $id !== $destId
        );

        $deleteLog = $this->latestLog(Destination::class, $destId, 'deleted');
        $this->assertNotNull($deleteLog);
        $this->assertEquals('Destination deleted', $deleteLog->description);
        $this->assertEquals(static::$sharedUserId, $deleteLog->causer_id);
        $this->printLog('✅ Destination DELETED', $deleteLog);
    }

    // =========================================================================
    // TEST 8 — No log when nothing changed (dontSubmitEmptyLogs)
    // =========================================================================

    /** @test */
    public function it_does_not_log_when_nothing_changed(): void
    {
        $this->actingAs($this->causer());

        $client = Client::create([
            'client_name'    => 'No Change Client',
            'contact_number' => '0500000011',
        ]);
        $this->track(Client::class, $client->id);
        $this->snapshotLogs();

        $countBefore = Activity::where('subject_type', Client::class)
            ->where('subject_id', $client->id)
            ->count();

        // No-op save — should produce zero new log entries
        $client->save();

        $countAfter = Activity::where('subject_type', Client::class)
            ->where('subject_id', $client->id)
            ->count();

        $this->assertEquals(
            $countBefore,
            $countAfter,
            'dontSubmitEmptyLogs() failed: a log was created for a no-op save().'
        );

        echo PHP_EOL . '  ✅ No-op save() → ZERO new log entries (dontSubmitEmptyLogs works).' . PHP_EOL;
    }

    // =========================================================================
    // TEST 9 — User: sensitive fields NOT logged
    // =========================================================================

    /** @test */
    public function it_does_not_log_sensitive_user_fields(): void
    {
        $this->actingAs($this->causer());

        $uid = uniqid('usrtst_', true);
        $sensitiveUser = User::create([
            'full_name'    => 'Sensitive User',
            'user_name'    => substr($uid, 0, 30),
            'email'        => substr($uid, 0, 20) . '@test.local',
            'password'     => bcrypt('SuperSecret123!'),
            'phone_number' => substr(preg_replace('/[^0-9]/', '', $uid), 0, 11),
        ]);
        $this->snapshotLogs();

        $log = $this->latestLog(User::class, $sensitiveUser->id, 'created');
        $this->assertNotNull($log);

        $attrs = $log->properties['attributes'] ?? [];

        $this->assertArrayNotHasKey('password', $attrs, 'SECURITY: password was logged!');
        $this->assertArrayNotHasKey('remember_token', $attrs, 'SECURITY: remember_token was logged!');
        $this->assertArrayHasKey('full_name', $attrs);
        $this->assertArrayHasKey('email', $attrs);

        // Cleanup: force-delete this temp user
        User::withTrashed()->where('id', $sensitiveUser->id)->forceDelete();

        $this->printLog('✅ User CREATED (password & remember_token excluded)', $log);
    }

    // =========================================================================
    // TEST 10 — Causer is NULL when not authenticated
    // =========================================================================

    /** @test */
    public function it_logs_with_null_causer_when_not_authenticated(): void
    {
        // Intentionally NO actingAs() call
        $client = Client::create([
            'client_name'    => 'Unauthenticated Client',
            'contact_number' => '0509876000',
        ]);
        $this->track(Client::class, $client->id);
        $this->snapshotLogs();

        $log = $this->latestLog(Client::class, $client->id, 'created');

        $this->assertNotNull($log);
        $this->assertNull($log->causer_id, 'causer_id must be NULL when no user is authenticated.');

        echo PHP_EOL . '  ✅ Unauthenticated operation → causer_id is NULL.' . PHP_EOL;
    }

    // =========================================================================
    // TEST 11 — Print: dump all logs stored in this run (DB inspection)
    // =========================================================================

    /** @test */
    public function it_prints_activity_log_summary_from_database(): void
    {
        $this->actingAs($this->causer());

        // Produce a variety of log entries
        $client = Client::create(['client_name' => 'Print Test Client', 'contact_number' => '0500000099']);
        $this->track(Client::class, $client->id);

        $destination = Destination::create(['destination_name' => 'Print Test Dest', 'noloan_code' => 'NL-PRT-999']);
        $this->track(Destination::class, $destination->id);

        $treasury = Treasury::create(['name' => 'Print Test Treasury', 'is_main' => false, 'balance' => 0]);
        $this->track(Treasury::class, $treasury->id);

        $client->update(['notes' => 'Updated in print test']);
        $treasury->update(['balance' => 9999.99]);

        $this->snapshotLogs();

        // Read rows directly from the DB table
        $logs = DB::table('activity_log')
            ->whereIn('id', $this->activityIds)
            ->orderBy('id')
            ->get();

        echo PHP_EOL;
        echo '  ┌─────────────────────────────────────────────────────────────────────────────┐' . PHP_EOL;
        echo '  │           ACTIVITY LOG — ROWS STORED IN DATABASE (this test run)            │' . PHP_EOL;
        echo '  └─────────────────────────────────────────────────────────────────────────────┘' . PHP_EOL;
        echo PHP_EOL;
        echo sprintf(
            "  %-6s %-12s %-35s %-12s %s\n",
            'ID', 'EVENT', 'SUBJECT', 'CAUSER', 'LOG_NAME'
        );
        echo '  ' . str_repeat('─', 82) . PHP_EOL;

        foreach ($logs as $log) {
            echo sprintf(
                "  %-6d %-12s %-35s %-12s %s\n",
                $log->id,
                $log->event ?? 'n/a',
                class_basename($log->subject_type ?? '') . ' #' . $log->subject_id,
                $log->causer_id ?? 'NULL',
                $log->log_name
            );
        }

        echo '  ' . str_repeat('─', 82) . PHP_EOL;
        echo "  Total rows: {$logs->count()}" . PHP_EOL;
        echo PHP_EOL;

        $this->assertGreaterThan(0, $logs->count(), 'Expected at least one activity_log row.');
    }
}
