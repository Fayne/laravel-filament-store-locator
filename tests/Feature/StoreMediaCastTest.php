<?php

use Fayne\StoreLocator\Casts\MediaAsset;
use Fayne\StoreLocator\Casts\MediaAssetCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['store-locator.disk' => 'public']);
    Schema::create('store_media_cast_test', function ($table) {
        $table->id();
        $table->text('logo')->nullable();
        $table->text('banner')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('store_media_cast_test');
});

it('reads legacy logo and banner JSON without Nova cast objects', function () {
    $record = new class extends Model
    {
        protected $table = 'store_media_cast_test';

        protected $guarded = [];

        protected $casts = [
            'logo' => MediaAsset::class,
            'banner' => MediaAssetCollection::class,
        ];
    };

    $record->forceFill([
        'logo' => ['disk' => 'public', 'path' => 'stores/logo.png'],
        'banner' => [
            ['disk' => 'public', 'path' => 'stores/banner-1.png'],
            ['disk' => 's3', 'path' => 'stores/banner-2.png'],
        ],
    ])->save();

    $loaded = $record->newQuery()->findOrFail($record->getKey());

    expect($loaded->logo)->toBe(['disk' => 'public', 'path' => 'stores/logo.png'])
        ->and($loaded->banner)->toBe([
            ['disk' => 'public', 'path' => 'stores/banner-1.png'],
            ['disk' => 's3', 'path' => 'stores/banner-2.png'],
        ])
        ->and($loaded->toArray()['logo'])->toBe(['disk' => 'public', 'path' => 'stores/logo.png'])
        ->and($loaded->toArray()['banner'])->toHaveCount(2);
});

it('accepts null and an empty legacy banner collection', function () {
    $record = new class extends Model
    {
        protected $table = 'store_media_cast_test';

        protected $guarded = [];

        protected $casts = [
            'logo' => MediaAsset::class,
            'banner' => MediaAssetCollection::class,
        ];
    };

    $record->forceFill(['logo' => null, 'banner' => []])->save();
    $loaded = $record->newQuery()->findOrFail($record->getKey());

    expect($loaded->logo)->toBeNull()->and($loaded->banner)->toBe([]);
});

it('converts Filament upload paths to the legacy disk and path JSON shape', function () {
    $record = new class extends Model
    {
        protected $table = 'store_media_cast_test';

        protected $guarded = [];

        protected $casts = [
            'logo' => MediaAsset::class,
            'banner' => MediaAssetCollection::class,
        ];
    };

    $record->forceFill([
        'logo' => 'stores/logo.png',
        'banner' => ['stores/banner-1.png', 'stores/banner-2.png'],
    ])->save();
    $loaded = $record->newQuery()->findOrFail($record->getKey());

    expect($loaded->logo)->toBe(['disk' => 'public', 'path' => 'stores/logo.png'])
        ->and($loaded->banner)->toBe([
            ['disk' => 'public', 'path' => 'stores/banner-1.png'],
            ['disk' => 'public', 'path' => 'stores/banner-2.png'],
        ]);
});
