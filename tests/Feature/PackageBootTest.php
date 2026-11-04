<?php

use Fayne\StoreLocator\StoreLocatorServiceProvider;

it('boots Store Locator independently with its Filament plugin', function () {
    expect(app()->getLoadedProviders())->toHaveKey(StoreLocatorServiceProvider::class)
        ->and(Filament\Facades\Filament::getPanel('admin')->getPlugins())->toHaveKey('store-locator');
});
