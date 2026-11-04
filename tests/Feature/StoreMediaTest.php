<?php

use Fayne\StoreLocator\Models\Store;
use Tests\Support\PackageDatabase;

beforeEach(function () {
    config(['logging.default' => 'single', 'store-locator.disk' => 'public']);
    PackageDatabase::prepare();
});

it('persists FileUpload paths using the legacy disk and path shape', function () {
    $store = Store::query()->create([
        'name' => 'Media Store',
        'logo' => 'stores/logo.png',
        'banner' => ['stores/banner-1.png', 'stores/banner-2.png'],
    ])->fresh();

    expect($store->logo)->toBe(['disk' => 'public', 'path' => 'stores/logo.png'])
        ->and($store->banner)->toBe([
            ['disk' => 'public', 'path' => 'stores/banner-1.png'],
            ['disk' => 'public', 'path' => 'stores/banner-2.png'],
        ]);
});

it('uploads an image through the form and retains it when the store is edited', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    $admin = \Fayne\CMS\Models\Admin::create(['name' => 'Uploader', 'email' => 'upload@example.test', 'password' => 'password', 'is_active' => true]);
    foreach (['viewAnyStore', 'viewStore', 'createStore', 'updateStore'] as $name) {
        $admin->givePermissionTo(\Spatie\Permission\Models\Permission::create(['name' => $name, 'guard_name' => 'admin']));
    }
    $this->actingAs($admin, 'admin');

    \Livewire\Livewire::test(\Fayne\StoreLocator\Filament\Resources\StoreResource\Pages\CreateStore::class)
        ->assertOk()
        ->fillForm(['name' => 'Uploaded Store', 'logo' => \Illuminate\Http\UploadedFile::fake()->image('logo.png'), 'status' => true])
        ->call('create')->assertHasNoFormErrors();

    $store = Store::where('name', 'Uploaded Store')->firstOrFail();
    $logo = $store->logo;
    expect($logo['disk'])->toBe('public');
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($logo['path']);

    \Livewire\Livewire::test(\Fayne\StoreLocator\Filament\Resources\StoreResource\Pages\EditStore::class, ['record' => $store->id])
        ->assertOk()->fillForm(['name' => 'Edited Uploaded Store'])->call('save')->assertHasNoFormErrors();

    expect($store->fresh()->logo)->toBe($logo);
    $this->getJson('/modules/store-locator/stores/' . $store->id)
        ->assertOk()->assertJsonPath('data.logo.path', $logo['path']);
});
