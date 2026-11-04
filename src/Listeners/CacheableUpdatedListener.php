<?php

namespace Fayne\StoreLocator\Listeners;

use Fayne\StoreLocator\Events\CacheableUpdatedEvent;

class CacheableUpdatedListener
{
    public const FLUSH_TAG = 'modules.store-locator';

    public function handle(CacheableUpdatedEvent $event): void
    {
        cache()->tags(static::FLUSH_TAG)->flush();
    }
}
