<?php

use WizardingCode\FlowNetwork\SyncTracker\Facades\SyncTracker;
use WizardingCode\FlowNetwork\SyncTracker\Tests\Models\TestModel;

it('can mark a model as synced using facade', function () {
    $model = createTestModel();
    
    SyncTracker::markAsSynced($model, 'ext-123', 'api');
    
    expect(SyncTracker::isSynced($model))->toBeTrue();
    expect(SyncTracker::getSyncInfo($model)->external_id)->toBe('ext-123');
    expect(SyncTracker::getSyncInfo($model)->source)->toBe('api');
});

it('can mark a model as synced using trait', function () {
    $model = createTestModel('Test With Trait');
    
    $model->markAsSynced('ext-xyz', 'erp', ['foo' => 'bar']);
    
    expect($model->isSynced())->toBeTrue();
    expect($model->getExternalId())->toBe('ext-xyz');
    expect($model->getSyncSource())->toBe('erp');
    expect($model->getSyncMetadata())->toBe(['foo' => 'bar']);
});