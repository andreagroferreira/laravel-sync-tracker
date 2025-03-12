# Laravel Sync Tracker

A Laravel package for tracking entity synchronization status between systems.

## Installation

You can install the package via composer:

```bash
composer require wizardingcode/laravel-sync-tracker
```

## Publishing the configuration and migrations

```bash
php artisan vendor:publish --provider="WizardingCode\FlowNetwork\SyncTracker\SyncTrackerServiceProvider" --tag="config"
php artisan vendor:publish --provider="WizardingCode\FlowNetwork\SyncTracker\SyncTrackerServiceProvider" --tag="migrations"
```

Then run the migrations:

```bash
php artisan migrate
```

## Usage

### Using the Trait

Add the `HasSyncTracking` trait to your model:

```php
use WizardingCode\FlowNetwork\SyncTracker\Traits\HasSyncTracking;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSyncTracking;
    
    // ...
}
```

Then you can use the methods provided by the trait:

```php
$user = User::find(1);

// Mark the model as synced
$user->markAsSynced('external-123', 'salesforce', ['meta' => 'data']);

// Check if model is synced
if ($user->isSynced()) {
    // Do something
}

// Get sync information
$externalId = $user->getExternalId();
$source = $user->getSyncSource();
$metadata = $user->getSyncMetadata();
```

### Using the Facade

```php
use WizardingCode\FlowNetwork\SyncTracker\Facades\SyncTracker;

// Mark a model as synced
SyncTracker::markAsSynced($model, 'external-123', 'salesforce', ['meta' => 'data']);

// Check if model is synced
if (SyncTracker::isSynced($model)) {
    // Do something
}

// Find a model by external ID and source
$user = SyncTracker::findByExternalId('external-123', 'salesforce', User::class);
```

## Configuration

You can configure the package by editing the `config/sync-tracker.php` file:

```php
return [
    // The table name used to store sync tracking information
    'table_name' => 'sync_tracked_entities',

    // Default tracking options
    'default_tracking' => [
        // Whether to track creation timestamps by default
        'track_created' => true,
        
        // Whether to track update timestamps by default
        'track_updated' => true,
        
        // Whether to track deletion timestamps by default
        'track_deleted' => true,
    ],

    // Custom tracking models configuration
    'models' => [
        // Example:
        // App\Models\User::class => [
        //     'track_created' => true,
        //     'track_updated' => false,
        //     'track_deleted' => true,
        // ],
    ],
];
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.