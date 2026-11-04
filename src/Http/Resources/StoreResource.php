<?php

namespace Fayne\StoreLocator\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return collect(parent::toArray($request))->except(['status', 'weight'])->all();
    }
}
