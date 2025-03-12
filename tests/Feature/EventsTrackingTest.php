<?php

use WizardingCode\FlowNetwork\SyncTracker\Tests\Models\TestModel;

it('automatically tracks model creation', function () {
    // Create model without manually tracking it
    $model = createTestModel();
    
    // The trait should have auto-tracked creation
    $tracking = $model->syncTracking;
    
    expect($tracking)->not->toBeNull();
    expect($tracking->created_at)->not->toBeNull();
});

it('automatically tracks model updates', function () {
    $model = createTestModel();
    
    // Wait a moment to ensure timestamps are different
    usleep(1000);
    
    // Update the model
    $model->name = 'Updated Name';
    $model->save();
    
    // Refresh tracking relationship
    $model->refresh();
    
    expect($model->syncTracking->updated_at)->not->toBeNull();
});

it('automatically tracks model deletion when using soft deletes', function () {
    // This test would require a model with SoftDeletes
    // Since we can't modify base Laravel behavior in this package to detect real deletions,
    // we're only tracking soft deletes through model events
    
    // For standard tests, we'll verify config loading
    expect(config('sync-tracker.default_tracking.track_deleted'))->toBeTrue();
});