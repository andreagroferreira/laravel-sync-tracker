<?php

namespace WizardingCode\FlowNetwork\SyncTracker;

use Illuminate\Database\Eloquent\Model;
use WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity;

class SyncTracker
{
    /**
     * Track the sync status for a model.
     */
    public function track(Model $model, array $attributes = []): SyncTrackedEntity
    {
        return SyncTrackedEntity::updateOrCreate(
            [
                'trackable_type' => get_class($model),
                'trackable_id' => $model->getKey(),
            ],
            array_merge([
                'synced_at' => now(),
            ], $attributes)
        );
    }

    /**
     * Mark a model as synced.
     */
    public function markAsSynced(
        Model $model,
        ?string $externalId = null,
        ?string $source = null,
        array $metadata = []
    ): SyncTrackedEntity {
        $syncInfo = $this->track($model, [
            'external_id' => $externalId,
            'source' => $source,
            'metadata' => $metadata,
            'synced_at' => now(),
        ]);

        // Dispatch an event when a model is synced
        event(new \WizardingCode\FlowNetwork\SyncTracker\Events\EntitySynced($model, $syncInfo));

        return $syncInfo;
    }

    /**
     * Get the sync tracking information for a model.
     */
    public function getSyncInfo(Model $model): ?SyncTrackedEntity
    {
        return SyncTrackedEntity::where([
            'trackable_type' => get_class($model),
            'trackable_id' => $model->getKey(),
        ])->first();
    }

    /**
     * Check if a model has been synced.
     */
    public function isSynced(Model $model): bool
    {
        $tracking = $this->getSyncInfo($model);

        return $tracking && $tracking->synced_at !== null;
    }

    /**
     * Find a model by its external ID and source.
     */
    public function findByExternalId(string $externalId, string $source, string $modelClass): ?Model
    {
        $tracking = SyncTrackedEntity::where([
            'external_id' => $externalId,
            'source' => $source,
            'trackable_type' => $modelClass,
        ])->first();

        if (! $tracking) {
            return null;
        }

        return $tracking->trackable;
    }
}
