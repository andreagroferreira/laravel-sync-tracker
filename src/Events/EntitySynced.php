<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity;

class EntitySynced
{
    use Dispatchable, SerializesModels;

    /**
     * The model that was synced.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * The sync tracking information.
     *
     * @var \WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity
     */
    public $syncInfo;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \WizardingCode\FlowNetwork\SyncTracker\Models\SyncTrackedEntity  $syncInfo
     * @return void
     */
    public function __construct(Model $model, SyncTrackedEntity $syncInfo)
    {
        $this->model = $model;
        $this->syncInfo = $syncInfo;
    }
}