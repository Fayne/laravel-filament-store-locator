<?php

namespace Fayne\StoreLocator\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class MediaAsset implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $asset = is_string($value) ? json_decode($value, true) : $value;

        if ($asset instanceof Arrayable) {
            $asset = $asset->toArray();
        } elseif ($asset instanceof JsonSerializable) {
            $asset = $asset->jsonSerialize();
        }

        if (! is_array($asset) || ! isset($asset['disk'], $asset['path'])) {
            return null;
        }

        return ['disk' => (string) $asset['disk'], 'path' => (string) $asset['path']];
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_string($value)) {
            $value = ['disk' => config('store-locator.disk', 'public'), 'path' => $value];
        }

        if (! is_array($value) || ! isset($value['disk'], $value['path'])) {
            return null;
        }

        return json_encode(['disk' => (string) $value['disk'], 'path' => (string) $value['path']], JSON_THROW_ON_ERROR);
    }
}
