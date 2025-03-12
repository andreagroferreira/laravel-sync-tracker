<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Exception;

class SyncFailed
{
    use Dispatchable, SerializesModels;

    /**
     * The model that failed to sync.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * The source system identifier.
     *
     * @var string
     */
    public $source;

    /**
     * The exception that caused the failure.
     *
     * @var \Exception|null
     */
    public $exception;

    /**
     * Additional metadata about the failure.
     *
     * @var array
     */
    public $metadata;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $source
     * @param  \Exception|null  $exception
     * @param  array  $metadata
     * @return void
     */
    public function __construct(Model $model, string $source, ?Exception $exception = null, array $metadata = [])
    {
        $this->model = $model;
        $this->source = $source;
        $this->exception = $exception;
        $this->metadata = $metadata;
    }
}