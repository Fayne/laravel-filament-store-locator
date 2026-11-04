<?php

namespace Fayne\StoreLocator\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Fayne\StoreLocator\Filament\Resources\StoreResource;

class StoreLocatorPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'store-locator';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([StoreResource::class]);
    }

    public function boot(Panel $panel): void {}
}
