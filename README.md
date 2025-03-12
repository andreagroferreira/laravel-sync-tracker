# Laravel Sync Tracker

<p align="center">
  <img src="https://raw.githubusercontent.com/andreagroferreira/laravel-sync-tracker/main/art/banner.png" alt="Laravel Sync Tracker Banner" width="100%">
</p>

<p align="center">
  <a href="https://github.com/andreagroferreira/laravel-sync-tracker/actions"><img src="https://github.com/andreagroferreira/laravel-sync-tracker/workflows/run-tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/andreagroferreira/laravel-sync-tracker"><img src="https://img.shields.io/packagist/v/andreagroferreira/laravel-sync-tracker.svg?style=flat-square" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/andreagroferreira/laravel-sync-tracker"><img src="https://img.shields.io/packagist/dt/andreagroferreira/laravel-sync-tracker.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/andreagroferreira/laravel-sync-tracker"><img src="https://img.shields.io/packagist/l/andreagroferreira/laravel-sync-tracker.svg?style=flat-square" alt="License"></a>
</p>

A powerful Laravel package for tracking entity synchronization status between systems. Easily manage data synchronization between your Laravel application and external services like CRMs, ERPs, or any third-party API.

## Features

- 🔄 **Track sync status** of any Eloquent model with external systems
- 🔍 **Find models by external ID** from various sources
- 🕒 **Track timestamps** for creation, update, and deletion events
- 🧩 **Support for multiple sync sources** within the same application
- 📊 **Store metadata** about sync operations for audit trails
- 🛠️ **Highly configurable** to suit your specific needs
- 🔌 **Easy integration** with existing Laravel applications

<p align="center">
  <img src="https://raw.githubusercontent.com/andreagroferreira/laravel-sync-tracker/main/art/flow-diagram.png" alt="Sync Flow Diagram" width="80%">
</p>

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
- [Real-world Examples](#real-world-examples)
- [Events](#events)
- [Custom Implementations](#custom-implementations)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require andreagroferreira/laravel-sync-tracker
```

## Configuration

Publish the configuration and migrations:

```bash
php artisan vendor:publish --provider="WizardingCode\FlowNetwork\SyncTracker\SyncTrackerServiceProvider" --tag="config"
php artisan vendor:publish --provider="WizardingCode\FlowNetwork\SyncTracker\SyncTrackerServiceProvider" --tag="migrations"
```

Then run the migrations:

```bash
php artisan migrate
```

The published configuration file (`config/sync-tracker.php`) allows you to customize how sync tracking works:

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
        // Example of model-specific configuration
        App\Models\User::class => [
            'track_created' => true,
            'track_updated' => false,
            'track_deleted' => true,
        ],
    ],
];
```

## Basic Usage

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

## Advanced Usage

### Sync Multiple Source Systems

Track entities that exist in multiple external systems:

```php
// Track the same user in different systems
$user = User::find(1);

// Mark as synced with Salesforce
$user->markAsSynced('SF-123456', 'salesforce', [
    'last_sync' => now(),
    'account_type' => 'customer'
]);

// In another part of your app, sync with HubSpot
SyncTracker::markAsSynced($user, 'HS-789012', 'hubspot', [
    'contact_owner' => 'jane.doe@example.com',
    'lead_score' => 85
]);

// Get all sync trackers for this user
$syncTrackers = $user->syncTrackers()->get();

// Check if synced with specific system
$salesforceId = $user->getExternalIdFromSource('salesforce');
$hubspotId = $user->getExternalIdFromSource('hubspot');
```

### Batch Synchronization with Progress Tracking

When syncing multiple entities in a batch job:

```php
// In a command or job
public function handle()
{
    $users = User::where('needs_sync', true)->get();
    $totalUsers = $users->count();
    $processed = 0;
    
    foreach ($users as $user) {
        // Sync with external API (pseudo code)
        $externalData = $this->apiClient->syncUser($user);
        
        // Mark as synced with metadata for tracking
        SyncTracker::markAsSynced($user, $externalData['id'], 'api', [
            'batch_id' => $this->batchId,
            'sync_attempt' => now(),
            'sync_status' => 'success',
            'progress' => ++$processed / $totalUsers
        ]);
        
        // Update user status
        $user->update(['needs_sync' => false]);
    }
}
```

### Handling Failed Syncs

Track failed synchronization attempts:

```php
try {
    // Attempt to sync with external system
    $response = $this->apiClient->createOrUpdate($product);
    
    // If successful, mark as synced
    SyncTracker::markAsSynced($product, $response['id'], 'erp', [
        'sync_status' => 'success',
        'last_successful_sync' => now()
    ]);
    
} catch (ApiException $e) {
    // If failed, track the failure but don't update synced_at
    $product->syncTracking()->update([
        'metadata->sync_status' => 'failed',
        'metadata->error_message' => $e->getMessage(),
        'metadata->error_code' => $e->getCode(),
        'metadata->retry_count' => DB::raw('COALESCE(metadata->\'retry_count\', 0) + 1'),
        'metadata->last_attempt' => now()->toIso8601String()
    ]);
    
    // Maybe schedule a retry
    if (($product->getSyncMetadata()['retry_count'] ?? 0) < 5) {
        SyncRetryJob::dispatch($product)->delay(now()->addMinutes(30));
    }
}
```

### Tracking Bi-directional Syncs

Track changes from both your system and external systems:

```php
// When a local change is made
$product = Product::find(1);
$product->update(['price' => 29.99]);

// Mark that this entity needs to be synced
$product->syncTracking()->update([
    'metadata->needs_upstream_sync' => true,
    'metadata->local_changes' => ['price' => 29.99],
    'updated_at' => now() // This triggers the trait's auto-tracking 
]);

// When receiving webhooks from an external system
public function handleExternalUpdate(Request $request)
{
    $externalId = $request->input('id');
    $source = 'erp';
    
    $product = SyncTracker::findByExternalId($externalId, $source, Product::class);
    
    if ($product) {
        // Update local record with data from external system
        $product->update([
            'name' => $request->input('name'),
            'sku' => $request->input('sku')
        ]);
        
        // Mark as synced from downstream with metadata
        SyncTracker::markAsSynced($product, $externalId, $source, [
            'sync_type' => 'downstream',
            'webhook_id' => $request->input('webhook_id'),
            'external_updated_at' => $request->input('updated_at')
        ]);
    }
}
```

<p align="center">
  <img src="https://raw.githubusercontent.com/andreagroferreira/laravel-sync-tracker/main/art/bidirectional-sync.png" alt="Bidirectional Sync" width="70%">
</p>

### Query Scopes for Sync Status

Define query scopes on your models for easy filtering:

```php
use WizardingCode\FlowNetwork\SyncTracker\Traits\HasSyncTracking;

class Product extends Model
{
    use HasSyncTracking;
    
    // Scope for products that need syncing to the ERP
    public function scopeNeedsErpSync($query)
    {
        return $query->whereHas('syncTracking', function ($q) {
            $q->where('source', 'erp')
              ->where(function ($q) {
                  $q->whereNull('synced_at')
                    ->orWhere('updated_at', '>', 'synced_at');
              });
        });
    }
    
    // Scope for products that have been synced with Shopify
    public function scopeSyncedWithShopify($query)
    {
        return $query->whereHas('syncTracking', function ($q) {
            $q->where('source', 'shopify')
              ->whereNotNull('synced_at');
        });
    }
    
    // Scope for products that failed to sync
    public function scopeFailedSync($query, $source = null)
    {
        return $query->whereHas('syncTracking', function ($q) use ($source) {
            $q->when($source, function ($q) use ($source) {
                $q->where('source', $source);
              })
              ->whereJsonContains('metadata->sync_status', 'failed');
        });
    }
}

// Then use in your application
$needsSyncProducts = Product::needsErpSync()->get();
$shopifyProducts = Product::syncedWithShopify()->get();
$failedProducts = Product::failedSync('erp')->get();
```

## Real-world Examples

### E-commerce Platform Integration

```php
class ProductSyncService
{
    public function syncToShopify(Product $product)
    {
        // If product exists in Shopify, update it, otherwise create it
        if ($product->getExternalIdFromSource('shopify')) {
            $shopifyId = $product->getExternalIdFromSource('shopify');
            $response = $this->shopifyClient->updateProduct($shopifyId, [
                'title' => $product->name,
                'price' => $product->price,
                'inventory_quantity' => $product->stock
            ]);
        } else {
            $response = $this->shopifyClient->createProduct([
                'title' => $product->name,
                'price' => $product->price,
                'inventory_quantity' => $product->stock
            ]);
            
            $shopifyId = $response['id'];
        }
        
        // Track the sync with detailed metadata
        $product->markAsSynced($shopifyId, 'shopify', [
            'shopify_handle' => $response['handle'],
            'variants_synced' => count($response['variants']),
            'images_synced' => count($response['images']),
            'shopify_updated_at' => $response['updated_at'],
            'inventory_tracked' => true
        ]);
        
        return $response;
    }
    
    public function syncFromShopify(array $shopifyData)
    {
        $shopifyId = $shopifyData['id'];
        
        // Try to find existing product
        $product = SyncTracker::findByExternalId($shopifyId, 'shopify', Product::class);
        
        if (!$product) {
            // Create new local product from Shopify data
            $product = Product::create([
                'name' => $shopifyData['title'],
                'price' => $shopifyData['price'],
                'stock' => $shopifyData['inventory_quantity'],
                'description' => $shopifyData['body_html']
            ]);
        } else {
            // Update existing product
            $product->update([
                'name' => $shopifyData['title'],
                'price' => $shopifyData['price'],
                'stock' => $shopifyData['inventory_quantity'],
                'description' => $shopifyData['body_html']
            ]);
        }
        
        // Track the sync
        SyncTracker::markAsSynced($product, $shopifyId, 'shopify', [
            'shopify_handle' => $shopifyData['handle'],
            'shopify_updated_at' => $shopifyData['updated_at'],
            'sync_direction' => 'from_shopify',
            'webhook_id' => request()->header('X-Shopify-Webhook-Id')
        ]);
        
        return $product;
    }
}
```

### CRM Integration with Conflict Resolution

```php
class ContactSyncService
{
    public function syncWithCrm(User $user)
    {
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address
        ];
        
        $syncInfo = $user->syncTracking;
        $externalId = $user->getExternalId();
        
        // Check if this is an update or a new record
        if ($externalId) {
            // Get the latest data from CRM first
            $crmData = $this->crmClient->getContact($externalId);
            
            // Compare timestamps to detect conflicts
            $crmUpdatedAt = Carbon::parse($crmData['updated_at']);
            $localUpdatedAt = $syncInfo->updated_at;
            
            if ($crmUpdatedAt->gt($localUpdatedAt)) {
                // Remote has newer data, handle conflict
                if (config('sync.conflict_strategy') === 'remote_wins') {
                    // Update local with remote data
                    $user->update([
                        'name' => $crmData['name'],
                        'email' => $crmData['email'],
                        'phone' => $crmData['phone'],
                        'address' => $crmData['address']
                    ]);
                    
                    $result = $crmData;
                    $conflictResolution = 'remote_won';
                } else {
                    // Push local changes to CRM anyway
                    $result = $this->crmClient->updateContact($externalId, $userData);
                    $conflictResolution = 'local_force_push';
                }
            } else {
                // Local has newer data, update CRM
                $result = $this->crmClient->updateContact($externalId, $userData);
                $conflictResolution = 'local_newer';
            }
        } else {
            // Create new CRM contact
            $result = $this->crmClient->createContact($userData);
            $externalId = $result['id'];
            $conflictResolution = 'new_record';
        }
        
        // Track the sync with detailed metadata
        $user->markAsSynced($externalId, 'crm', [
            'sync_result' => 'success',
            'conflict_detected' => $conflictResolution !== 'new_record',
            'conflict_resolution' => $conflictResolution,
            'fields_synced' => array_keys($userData),
            'crm_updated_at' => $result['updated_at']
        ]);
        
        return $result;
    }
}
```

<p align="center">
  <img src="https://raw.githubusercontent.com/andreagroferreira/laravel-sync-tracker/main/art/conflict-resolution.png" alt="Conflict Resolution Workflow" width="75%">
</p>

### Syncing Data with Legacy Systems through ETL Processes

```php
class LegacySystemSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $batchId;
    protected $models;
    protected $source = 'legacy_erp';
    
    public function __construct(array $modelIds, string $batchId)
    {
        $this->models = $modelIds;
        $this->batchId = $batchId;
    }
    
    public function handle()
    {
        // Connect to legacy system via ODBC or similar
        $connection = $this->getLegacyConnection();
        
        foreach ($this->models as $modelType => $ids) {
            $modelClass = $this->getModelClass($modelType);
            
            foreach ($ids as $id) {
                $model = $modelClass::find($id);
                
                if (!$model) {
                    continue;
                }
                
                try {
                    // Prepare data for legacy system format
                    $legacyData = $this->transformToLegacyFormat($model);
                    
                    // Check if record exists in legacy system
                    $externalId = $model->getExternalIdFromSource($this->source);
                    
                    if ($externalId) {
                        // Update existing record
                        $result = $connection->update(
                            $this->getLegacyTableName($modelType),
                            $legacyData,
                            "ID = '$externalId'"
                        );
                    } else {
                        // Insert new record
                        $externalId = $this->generateLegacyId($model);
                        $legacyData['ID'] = $externalId;
                        
                        $result = $connection->insert(
                            $this->getLegacyTableName($modelType),
                            $legacyData
                        );
                    }
                    
                    // Track successful sync
                    SyncTracker::markAsSynced($model, $externalId, $this->source, [
                        'batch_id' => $this->batchId,
                        'sync_timestamp' => now()->timestamp,
                        'tables_affected' => [$this->getLegacyTableName($modelType)],
                        'sync_mode' => $externalId ? 'update' : 'insert',
                        'legacy_fields' => array_keys($legacyData)
                    ]);
                    
                } catch (\Exception $e) {
                    // Track failed sync but don't update synced_at
                    if ($model->syncTracking) {
                        $model->syncTracking->update([
                            'metadata->sync_status' => 'failed',
                            'metadata->error' => $e->getMessage(),
                            'metadata->batch_id' => $this->batchId,
                            'metadata->attempt_timestamp' => now()->timestamp
                        ]);
                    }
                    
                    // Log error for admin review
                    Log::error("Legacy sync failed for {$modelType} #{$id}: " . $e->getMessage());
                }
            }
        }
    }
}
```

## Events

This package dispatches Laravel events that you can listen for in your application:

```php
// In your EventServiceProvider
protected $listen = [
    'WizardingCode\FlowNetwork\SyncTracker\Events\EntitySynced' => [
        'App\Listeners\HandleEntitySynced',
    ],
    'WizardingCode\FlowNetwork\SyncTracker\Events\SyncFailed' => [
        'App\Listeners\HandleSyncFailed',
    ],
];
```

Then create listeners to handle these events:

```php
namespace App\Listeners;

use WizardingCode\FlowNetwork\SyncTracker\Events\EntitySynced;

class HandleEntitySynced
{
    public function handle(EntitySynced $event)
    {
        $model = $event->model;
        $syncInfo = $event->syncInfo;
        
        // Notify admins of successful sync
        if ($model instanceof \App\Models\CriticalEntity) {
            \Notification::route('slack', config('services.slack.webhook_url'))
                ->notify(new \App\Notifications\EntitySynced($model, $syncInfo));
        }
        
        // Invalidate any cache related to this model
        \Cache::tags([$model->getTable()])->flush();
    }
}
```

## Custom Implementations

### Creating a Custom Synchronization Manager

```php
namespace App\Services;

use WizardingCode\FlowNetwork\SyncTracker\Facades\SyncTracker;
use Illuminate\Database\Eloquent\Model;

class SalesforceSync
{
    protected $client;
    
    public function __construct(SalesforceClient $client)
    {
        $this->client = $client;
    }
    
    public function syncAccount(Model $company)
    {
        // Check if already synced
        $sfAccountId = $company->getExternalIdFromSource('salesforce');
        
        $companyData = [
            'Name' => $company->name,
            'BillingStreet' => $company->address,
            'BillingCity' => $company->city,
            'BillingState' => $company->state,
            'BillingPostalCode' => $company->zip,
            'BillingCountry' => $company->country,
            'Phone' => $company->phone,
            'Website' => $company->website
        ];
        
        try {
            if ($sfAccountId) {
                // Update existing
                $result = $this->client->update('Account', $sfAccountId, $companyData);
            } else {
                // Create new
                $result = $this->client->create('Account', $companyData);
                $sfAccountId = $result['id'];
            }
            
            // Now sync all contacts for this company
            $this->syncContacts($company, $sfAccountId);
            
            // Track successful sync with metadata
            SyncTracker::markAsSynced($company, $sfAccountId, 'salesforce', [
                'object_type' => 'Account',
                'sf_last_modified' => $result['LastModifiedDate'] ?? now(),
                'child_objects_synced' => [
                    'contacts' => $company->users()->count()
                ]
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            // Handle failure - track but don't update synced_at
            if ($company->syncTracking) {
                $company->syncTracking->update([
                    'metadata->sync_status' => 'failed',
                    'metadata->error' => $e->getMessage(),
                    'metadata->last_attempt' => now()
                ]);
            }
            
            throw $e;
        }
    }
    
    protected function syncContacts(Model $company, string $sfAccountId)
    {
        // Implementation for syncing associated contacts...
    }
}
```

### Implement a REST API for External Systems to Check Sync Status

```php
// In a controller
public function getSyncStatus(Request $request)
{
    $request->validate([
        'model_type' => 'required|string',
        'model_id' => 'required',
        'source' => 'required|string'
    ]);
    
    $modelClass = $this->getModelClassFromType($request->model_type);
    $model = $modelClass::find($request->model_id);
    
    if (!$model) {
        return response()->json([
            'error' => 'Model not found'
        ], 404);
    }
    
    $syncInfo = $model->syncTracking()->where('source', $request->source)->first();
    
    if (!$syncInfo) {
        return response()->json([
            'sync_status' => 'not_synced',
            'model_type' => $request->model_type,
            'model_id' => $request->model_id,
            'source' => $request->source
        ]);
    }
    
    return response()->json([
        'sync_status' => $syncInfo->synced_at ? 'synced' : 'pending',
        'external_id' => $syncInfo->external_id,
        'last_synced_at' => $syncInfo->synced_at,
        'last_updated_at' => $syncInfo->updated_at,
        'metadata' => $syncInfo->metadata
    ]);
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email andre.ferreira@wizardingcode.io instead of using the issue tracker.

## Credits

- [Andre Agro Ferreira](https://github.com/andreagroferreira)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.