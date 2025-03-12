<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Traits;

use WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSyncTracking
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    protected static function bootHasSyncTracking()
    {
        static::created(function ($model) {
            if (config('sync-tracker.default_tracking.track_created', true)) {
                $model->syncTracking()->updateOrCreate(
                    ['trackable_type' => get_class($model), 'trackable_id' => $model->getKey()],
                    ['created_at' => now()]
                );
            }
        });

        static::updated(function ($model) {
            if (config('sync-tracker.default_tracking.track_updated', true)) {
                $model->syncTracking()->updateOrCreate(
                    ['trackable_type' => get_class($model), 'trackable_id' => $model->getKey()],
                    ['updated_at' => now()]
                );
            }
        });

        static::deleted(function ($model) {
            if (config('sync-tracker.default_tracking.track_deleted', true)) {
                $model->syncTracking()->updateOrCreate(
                    ['trackable_type' => get_class($model), 'trackable_id' => $model->getKey()],
                    ['deleted_at' => now()]
                );
            }
        });
    }

    /**
     * Get the sync tracking information for this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function syncTracking(): MorphOne
    {
        return $this->morphOne(SyncTrackedEntity::class, 'trackable');
    }

    /**
     * Mark this model as synced.
     *
     * @param string|null $externalId
     * @param string|null $source
     * @param array $metadata
     * @return SyncTrackedEntity
     */
    public function markAsSynced(?string $externalId = null, ?string $source = null, array $metadata = []): SyncTrackedEntity
    {
        return $this->syncTracking()->updateOrCreate(
            ['trackable_type' => get_class($this), 'trackable_id' => $this->getKey()],
            [
                'external_id' => $externalId,
                'source' => $source,
                'metadata' => $metadata,
                'synced_at' => now(),
            ]
        );
    }

    /**
     * Check if this model has been synced.
     *
     * @return bool
     */
    public function isSynced(): bool
    {
        return $this->syncTracking && $this->syncTracking->synced_at !== null;
    }

    /**
     * Get the external ID for this model.
     *
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->syncTracking ? $this->syncTracking->external_id : null;
    }

    /**
     * Get the sync source for this model.
     *
     * @return string|null
     */
    public function getSyncSource(): ?string
    {
        return $this->syncTracking ? $this->syncTracking->source : null;
    }

    /**
     * Get the sync metadata for this model.
     *
     * @return array|null
     */
    public function getSyncMetadata(): ?array
    {
        return $this->syncTracking ? $this->syncTracking->metadata : null;
    }
}