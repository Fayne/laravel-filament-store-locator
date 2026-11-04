<?php

use Fayne\StoreLocator\Listeners\CacheableUpdatedListener;
use Fayne\StoreLocator\Models\Store;
use Illuminate\Support\Facades\Cache;
use Tests\Support\PackageDatabase;

beforeEach(function () {
    config(['logging.default' => 'single']);
    PackageDatabase::prepare();
});

it('invalidates tagged public store cache after create, update, and delete', function () {
    Cache::tags(CacheableUpdatedListener::FLUSH_TAG)->put('store-cache-probe', 'cached', 600);
    $store = Store::query()->create(['name' => 'Cache Store']);
    expect(Cache::tags(CacheableUpdatedListener::FLUSH_TAG)->get('store-cache-probe'))->toBeNull();

    Cache::tags(CacheableUpdatedListener::FLUSH_TAG)->put('store-cache-probe', 'cached', 600);
    $store->update(['name' => 'Updated Cache Store']);
    expect(Cache::tags(CacheableUpdatedListener::FLUSH_TAG)->get('store-cache-probe'))->toBeNull();

    Cache::tags(CacheableUpdatedListener::FLUSH_TAG)->put('store-cache-probe', 'cached', 600);
    $store->delete();
    expect(Cache::tags(CacheableUpdatedListener::FLUSH_TAG)->get('store-cache-probe'))->toBeNull();
});
