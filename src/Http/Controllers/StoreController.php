<?php

namespace Fayne\StoreLocator\Http\Controllers;

use Fayne\StoreLocator\Http\Resources\StoreResource;
use Fayne\StoreLocator\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Fayne\StoreLocator\Listeners\CacheableUpdatedListener;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:0'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90', 'required_if:order_by,distance'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180', 'required_if:order_by,distance'],
            'order_by' => ['nullable', 'in:distance'],
        ]);
        $query = Store::query()->published();

        if (($validated['order_by'] ?? null) === 'distance') {
            $query->orderByDistance((float) $validated['latitude'], (float) $validated['longitude']);
        }

        $query->orderBy('weight');
        $pageSize = $validated['page_size'] ?? 15;
        $cacheKey = 'stores:' . md5($request->fullUrl());
        $stores = config('store-locator.routes.cached')
            ? cache()->tags(CacheableUpdatedListener::FLUSH_TAG)->rememberForever($cacheKey, fn () => $query->paginate($pageSize))
            : $query->paginate($pageSize);

        return StoreResource::collection($stores);
    }

    public function show(Store $store): StoreResource
    {
        abort_unless($store->isPublished(), 404);

        return new StoreResource($store);
    }
}
