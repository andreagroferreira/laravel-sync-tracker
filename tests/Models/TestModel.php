<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use WizardingCode\FlowNetwork\SyncTracker\Traits\HasSyncTracking;

class TestModel extends Model
{
    use HasSyncTracking;

    protected $fillable = ['name'];
}
