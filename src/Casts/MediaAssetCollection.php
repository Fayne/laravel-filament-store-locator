<?php

namespace Fayne\StoreLocator\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;

class MediaAssetCollection implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $assets = is_string($value) ? json_decode($value, true) : $value;

        if ($assets instanceof Collection) {
            $assets = $assets->all();
        } elseif ($assets instanceof Arrayable) {
            $assets = $assets->toArray();
        }

        if (! is_array($assets)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($asset) {
            if ($asset instanceof Arrayable) {
                $asset = $asset->toArray();
            } elseif ($asset instanceof JsonSerializable) {
                $asset = $asset->jsonSerialize();
            }

            if (is_string($asset)) {
                $asset = ['disk' => config('store-locator.disk', 'public'), 'path' => $asset];
            }

            if (! is_array($asset) || ! isset($asset['disk'], $asset['path'])) {
                return null;
            }

            return ['disk' => (string) $asset['disk'], 'path' => (string) $asset['path']];
        }, $assets)));
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        } elseif ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            return null;
        }

        return json_encode((new self)->get(null, $key, $value, []), JSON_THROW_ON_ERROR);
    }
}
