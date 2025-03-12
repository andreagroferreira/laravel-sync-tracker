<?php

use WizardingCode\FlowNetwork\SyncTracker\Facades\SyncTracker;
use WizardingCode\FlowNetwork\SyncTracker\Tests\Models\TestModel;

// Make sure to use the TestCase to have Laravel set up
uses(WizardingCode\FlowNetwork\SyncTracker\Tests\TestCase::class);

it('can find a model by external id', function () {
    $model = TestModel::create(['name' => 'Test Model']);
    SyncTracker::markAsSynced($model, 'ext-abc', 'crm');

    $found = SyncTracker::findByExternalId('ext-abc', 'crm', TestModel::class);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($model->id);
});

it('returns null when model not found by external id', function () {
    $model = TestModel::create(['name' => 'Test Model']);
    SyncTracker::markAsSynced($model, 'ext-abc', 'crm');

    $found = SyncTracker::findByExternalId('non-existent', 'crm', TestModel::class);

    expect($found)->toBeNull();
});
