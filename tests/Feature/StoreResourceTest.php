<?php

use Fayne\CMS\Models\Admin;
use Spatie\Permission\Models\Permission;
use Tests\Support\PackageDatabase;

beforeEach(function () {
    config(['logging.default' => 'single']);
    PackageDatabase::prepare();
});

it('blocks a Store resource URL without its permission', function () {
    $admin = Admin::query()->create(['name' => 'Store Admin', 'email' => 'store-admin@example.test', 'password' => 'password']);
    $this->actingAs($admin, 'admin')->get('/admin/stores')->assertForbidden();
});

it('allows an administrator with the Store listing permission into the resource', function () {
    $admin = Admin::query()->create(['name' => 'Store Admin', 'email' => 'store-admin@example.test', 'password' => 'password']);
    $permission = Permission::query()->create(['name' => 'viewAnyStore', 'guard_name' => 'admin']);
    $admin->givePermissionTo($permission);
    expect($admin->fresh()->can('viewAny', \Fayne\StoreLocator\Models\Store::class))->toBeTrue();
    $this->actingAs($admin->fresh(), 'admin')->get('/admin/stores')->assertOk();
});
