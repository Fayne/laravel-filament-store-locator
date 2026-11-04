<?php

use Fayne\StoreLocator\Enums\Publishable;
use Fayne\StoreLocator\Models\Store;
use Tests\Support\PackageDatabase;

beforeEach(function () {
    config(['logging.default' => 'single', 'store-locator.routes.cached' => false]);
    PackageDatabase::prepare();
});

it('lists published stores and hides unpublished records and internal fields', function () {
    $published = Store::query()->create(['name' => 'Published Store', 'status' => Publishable::Published->value, 'weight' => 1]);
    Store::query()->create(['name' => 'Unpublished Store', 'status' => Publishable::Unpublished->value]);

    $this->getJson('/modules/store-locator/stores?page_size=10')->assertOk()
        ->assertJsonPath('data.0.id', $published->id)
        ->assertJsonMissing(['name' => 'Unpublished Store'])
        ->assertJsonMissingPath('data.0.status')
        ->assertJsonMissingPath('data.0.weight');
    $this->getJson('/modules/store-locator/stores/' . $published->id)->assertOk();
    $this->getJson('/modules/store-locator/stores/' . Store::query()->where('name', 'Unpublished Store')->value('id'))
        ->assertNotFound();
});

it('validates coordinate bounds and orders distance requests from zero coordinates', function () {
    $nearest = Store::query()->create(['name' => 'At Origin', 'weight' => 1, 'latitude' => 0, 'longitude' => 0]);
    Store::query()->create(['name' => 'Far Away', 'weight' => 1, 'latitude' => 0, 'longitude' => 10]);

    $this->getJson('/modules/store-locator/stores?order_by=distance&latitude=0&longitude=0')
        ->assertOk()->assertJsonPath('data.0.id', $nearest->id);
    $this->getJson('/modules/store-locator/stores?order_by=distance&latitude=91&longitude=0')
        ->assertUnprocessable();
    $this->getJson('/modules/store-locator/stores?order_by=distance&latitude=0')
        ->assertUnprocessable();
});
