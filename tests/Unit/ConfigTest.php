<?php

use WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity;

// Make sure to use the TestCase to have Laravel set up
uses(WizardingCode\FlowNetwork\SyncTracker\Tests\TestCase::class);

it('loads configuration correctly', function () {
    // Test default config values
    expect(config('sync-tracker.table_name'))->toBe('sync_tracked_entities');
    expect(config('sync-tracker.default_tracking.track_created'))->toBeTrue();
    expect(config('sync-tracker.default_tracking.track_updated'))->toBeTrue();
    expect(config('sync-tracker.default_tracking.track_deleted'))->toBeTrue();
});

it('allows overriding table name', function () {
    // Change config for this test
    config(['sync-tracker.table_name' => 'custom_sync_table']);
    
    expect(config('sync-tracker.table_name'))->toBe('custom_sync_table');
    
    // Instantiate model to verify it uses the config
    $model = new SyncTrackedEntity();
    expect($model->getTable())->toBe('custom_sync_table');
});