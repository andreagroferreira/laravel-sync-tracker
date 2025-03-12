<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity track(\Illuminate\Database\Eloquent\Model $model, array $attributes = [])
 * @method static \WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity markAsSynced(\Illuminate\Database\Eloquent\Model $model, ?string $externalId = null, ?string $source = null, array $metadata = [])
 * @method static \WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity|null getSyncInfo(\Illuminate\Database\Eloquent\Model $model)
 * @method static bool isSynced(\Illuminate\Database\Eloquent\Model $model)
 * @method static \Illuminate\Database\Eloquent\Model|null findByExternalId(string $externalId, string $source, string $modelClass)
 *
 * @see \WizardingCode\FlowNetwork\SyncTracker\SyncTracker
 */
class SyncTracker extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sync-tracker';
    }
}
