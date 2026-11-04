<?php

namespace Fayne\StoreLocator\Models;

use Fayne\StoreLocator\Casts\MediaAsset;
use Fayne\StoreLocator\Casts\MediaAssetCollection;
use Fayne\StoreLocator\Database\Factories\StoreFactory;
use Fayne\StoreLocator\Enums\Publishable;
use Fayne\StoreLocator\Events\CacheableUpdatedEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use \Fayne\CMS\Support\Concerns\BelongsToTenant;
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'brands' => 'array',
            'subway_lines' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'opening_hours' => 'array',
            'logo' => MediaAsset::class,
            'banner' => MediaAssetCollection::class,
            'status' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return StoreFactory::new();
    }

    protected static function booted(): void
    {
        static::created(fn (Store $store) => CacheableUpdatedEvent::dispatch($store));
        static::updated(fn (Store $store) => CacheableUpdatedEvent::dispatch($store));
        static::deleted(fn (Store $store) => CacheableUpdatedEvent::dispatch($store));
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', Publishable::Published->value);
    }

    public function scopeOrderByDistance(Builder $query, ?float $latitude = null, ?float $longitude = null): Builder
    {
        if ($latitude === null || $longitude === null) {
            return $query;
        }

        return $query->orderByRaw(
            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) ASC',
            [$latitude, $longitude, $latitude],
        );
    }

    public function isPublished(): bool
    {
        return $this->status;
    }
}
