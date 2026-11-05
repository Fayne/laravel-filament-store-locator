<?php

namespace Fayne\StoreLocator;

use Illuminate\Support\ServiceProvider;
use Fayne\StoreLocator\Models\Store;
use Fayne\StoreLocator\Policies\StorePolicy;
use Illuminate\Support\Facades\Gate;
use Fayne\StoreLocator\Events\CacheableUpdatedEvent;
use Fayne\StoreLocator\Listeners\CacheableUpdatedListener;
use Illuminate\Support\Facades\Event;
use Fayne\CMS\Support\Authorization\PermissionRegistry;
use Fayne\CMS\Support\Modules\ModuleRegistry;
use Fayne\StoreLocator\Filament\StoreLocatorPlugin;

class StoreLocatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->callAfterResolving(ModuleRegistry::class, function ($registry): void {
            $registry->register('store-locator', 'Store Locator', StoreLocatorPlugin::class);
        });
        $this->callAfterResolving(PermissionRegistry::class, function ($registry): void {
            $registry->register('store-locator', require __DIR__ . '/../resources/permissions.php');
        });
    }

    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], ['store-locator-migrations', 'cms-module-assets']);
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-store-locator');
        $this->mergeConfigFrom(__DIR__ . '/../config/store-locator.php', 'store-locator');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        if (config('store-locator.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
        Gate::policy(Store::class, StorePolicy::class);
        Event::listen(CacheableUpdatedEvent::class, CacheableUpdatedListener::class);
        $this->publishes([
            __DIR__ . '/../config/store-locator.php' => config_path('store-locator.php'),
        ], ['store-locator-config', 'cms-module-assets']);
    }
}
