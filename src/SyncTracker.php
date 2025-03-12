<?php

namespace WizardingCode\FlowNetwork\SyncTracker;

use Illuminate\Database\Eloquent\Model;
use WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity;

class SyncTracker
{
    /**
     * Track the sync status for a model.
     *
     * @param Model $model
     * @param array $attributes
     * @return SyncTrackedEntity
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
     *
     * @param Model $model
     * @param string|null $externalId
     * @param string|null $source
     * @param array $metadata
     * @return SyncTrackedEntity
     */
    public function markAsSynced(
        Model $model,
        ?string $externalId = null,
        ?string $source = null,
        array $metadata = []
    ): SyncTrackedEntity {
        return $this->track($model, [
            'external_id' => $externalId,
            'source' => $source,
            'metadata' => $metadata,
            'synced_at' => now(),
        ]);
    }

    /**
     * Get the sync tracking information for a model.
     *
     * @param Model $model
     * @return SyncTrackedEntity|null
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
     *
     * @param Model $model
     * @return bool
     */
    public function isSynced(Model $model): bool
    {
        $tracking = $this->getSyncInfo($model);
        
        return $tracking && $tracking->synced_at !== null;
    }

    /**
     * Find a model by its external ID and source.
     *
     * @param string $externalId
     * @param string $source
     * @param string $modelClass
     * @return Model|null
     */
    public function findByExternalId(string $externalId, string $source, string $modelClass): ?Model
    {
        $tracking = SyncTrackedEntity::where([
            'external_id' => $externalId,
            'source' => $source,
            'trackable_type' => $modelClass,
        ])->first();

        if (!$tracking) {
            return null;
        }

        return $tracking->trackable;
    }
}