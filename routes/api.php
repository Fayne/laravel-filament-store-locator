<?php

use Fayne\StoreLocator\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('modules/store-locator')
    ->middleware(['api', \Fayne\CMS\Http\Middleware\ResolvePublicTenant::class, 'cache.headers:public;max_age=' . config('store-locator.routes.max_age', 3600) . ';etag'])
    ->name('modules.store-locator.')
    ->group(function (): void {
        Route::apiResource('stores', StoreController::class)->only(['index', 'show']);
    });
