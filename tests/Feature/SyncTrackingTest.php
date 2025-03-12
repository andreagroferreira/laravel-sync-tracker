<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Tests\Feature;

use WizardingCode\FlowNetwork\SyncTracker\Facades\SyncTracker;
use WizardingCode\FlowNetwork\SyncTracker\Tests\Models\TestModel;
use WizardingCode\FlowNetwork\SyncTracker\Tests\TestCase;

class SyncTrackingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_mark_a_model_as_synced()
    {
        $model = TestModel::create(['name' => 'Test Model']);

        SyncTracker::markAsSynced($model, 'ext-123', 'api');

        $this->assertTrue(SyncTracker::isSynced($model));
        $this->assertEquals('ext-123', SyncTracker::getSyncInfo($model)->external_id);
        $this->assertEquals('api', SyncTracker::getSyncInfo($model)->source);
    }

    /** @test */
    public function it_can_find_a_model_by_external_id()
    {
        $model = TestModel::create(['name' => 'Test Model']);
        SyncTracker::markAsSynced($model, 'ext-abc', 'crm');

        $found = SyncTracker::findByExternalId('ext-abc', 'crm', TestModel::class);

        $this->assertNotNull($found);
        $this->assertEquals($model->id, $found->id);
    }

    /** @test */
    public function it_can_use_trait_methods()
    {
        $model = TestModel::create(['name' => 'Test With Trait']);

        $model->markAsSynced('ext-xyz', 'erp', ['foo' => 'bar']);

        $this->assertTrue($model->isSynced());
        $this->assertEquals('ext-xyz', $model->getExternalId());
        $this->assertEquals('erp', $model->getSyncSource());
        $this->assertEquals(['foo' => 'bar'], $model->getSyncMetadata());
    }
}
