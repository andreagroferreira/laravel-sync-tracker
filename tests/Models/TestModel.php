<?php

namespace WizardingCode\FlowNetwork\SyncTracker\Tests\Models;

use WizardingCode\FlowNetwork\SyncTracker\Traits\HasSyncTracking;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasSyncTracking;
    
    protected $fillable = ['name'];
}