<?php

namespace Fayne\StoreLocator\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class CacheableUpdatedEvent
{
    use Dispatchable;

    public function __construct(public readonly ?Model $model = null) {}
}
