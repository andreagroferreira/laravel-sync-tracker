<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSyncTrackedEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('sync-tracker.table_name', 'sync_tracked_entities'), function (Blueprint $table) {
            $table->id();
            $table->morphs('trackable');
            $table->string('external_id')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->json('metadata')->nullable();
            
            $table->unique(['trackable_type', 'trackable_id']);
            $table->index(['external_id', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('sync-tracker.table_name', 'sync_tracked_entities'));
    }
}