<?php

namespace Fayne\StoreLocator\Filament\Resources\StoreResource\Pages;

use Fayne\StoreLocator\Filament\Resources\StoreResource;
use Fayne\CMS\Filament\Operations\EditRecord;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['logo'] = $data['logo']['path'] ?? null;
        $data['banner'] = collect($data['banner'] ?? [])->pluck('path')->all();
        $data['opening_hour_exceptions'] = collect($data['opening_hours']['exceptions'] ?? [])
            ->map(fn (array $hours, string $date) => ['date' => $date, 'hours' => $hours])->values()->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $disk = config('store-locator.disk', 'public');
        $data['logo'] = filled($data['logo'] ?? null) ? ['disk' => $disk, 'path' => $data['logo']] : null;
        $data['banner'] = array_map(fn (string $path) => ['disk' => $disk, 'path' => $path], $data['banner'] ?? []);
        $openingHours = $data['opening_hours'] ?? [];
        $openingHours['exceptions'] = collect($data['opening_hour_exceptions'] ?? [])
            ->mapWithKeys(fn (array $item) => [$item['date'] => $item['hours'] ?? []])->all();
        unset($data['opening_hour_exceptions']);
        $data['opening_hours'] = $openingHours;

        return $data;
    }
}
