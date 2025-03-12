<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SyncTrackedEntity extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'external_id',
        'source',
        'synced_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Create a new model instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('sync-tracker.table_name', 'sync_tracked_entities'));
    }

    /**
     * Get the trackable model.
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }
}
