<?php

namespace Fayne\StoreLocator\Filament\Resources\StoreResource\Pages;

use Fayne\StoreLocator\Filament\Resources\StoreResource;
use Fayne\CMS\Filament\Operations\CreateRecord;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->storeUploadPaths($data);
        $data = $this->storeExceptions($data);

        return $data;
    }

    private function storeUploadPaths(array $data): array
    {
        $disk = config('store-locator.disk', 'public');
        if (filled($data['logo'] ?? null)) {
            $data['logo'] = ['disk' => $disk, 'path' => $data['logo']];
        }
        if (filled($data['banner'] ?? null)) {
            $data['banner'] = array_map(fn (string $path) => ['disk' => $disk, 'path' => $path], $data['banner']);
        }

        return $data;
    }

    private function storeExceptions(array $data): array
    {
        $openingHours = $data['opening_hours'] ?? [];
        $openingHours['exceptions'] = collect($data['opening_hour_exceptions'] ?? [])
            ->mapWithKeys(fn (array $item) => [$item['date'] => $item['hours'] ?? []])->all();
        unset($data['opening_hour_exceptions']);
        $data['opening_hours'] = $openingHours;

        return $data;
    }
}
