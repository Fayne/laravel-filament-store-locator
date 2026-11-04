<?php

namespace Fayne\StoreLocator\Filament\Resources;

use Fayne\StoreLocator\Events\CacheableUpdatedEvent;
use Fayne\StoreLocator\Filament\Resources\StoreResource\Pages;
use Fayne\StoreLocator\Models\Store;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoreResource extends Resource
{
    public static function getNavigationLabel(): string
    {
        return __('filament-store-locator::navigation.stores');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('filament-cms::navigation.cms');
    }

    protected static bool $isScopedToTenant = false;
    protected static ?string $model = Store::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Schema $schema): Schema
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        return $schema->components([
            Section::make('Basic information')->schema([
                TextInput::make('name')->required()->maxLength(255)->unique(ignoreRecord: true),
                Textarea::make('description'),
                FileUpload::make('logo')->image()->disk(config('store-locator.disk', 'public')),
                FileUpload::make('banner')->image()->multiple()->disk(config('store-locator.disk', 'public')),
                TextInput::make('internal_serial_number'),
                TagsInput::make('brands'),
                Toggle::make('status')->default(true),
            ])->columns(2),
            Section::make('Location')->schema([
                TextInput::make('continent'),
                TextInput::make('country'),
                TextInput::make('province'),
                TextInput::make('city'),
                Textarea::make('address'),
                TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90)->step('any'),
                TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180)->step('any'),
                TextInput::make('postal_code'),
                TagsInput::make('subway_lines'),
            ])->columns(2),
            Section::make('Contact and hours')->schema([
                TextInput::make('website')->url(),
                TextInput::make('phone'),
                TextInput::make('mobile'),
                TextInput::make('email')->email(),
                ...array_map(fn (string $day) => TagsInput::make("opening_hours.{$day}")->label(ucfirst($day)), $days),
                Repeater::make('opening_hour_exceptions')->defaultItems(0)->schema([
                    DatePicker::make('date')->required(),
                    TagsInput::make('hours')->required(),
                ])->columns(2),
                Textarea::make('opening_hours_text'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('weight')->reorderable('weight')
            ->authorizeReorder(fn () => auth('admin')->user()?->can('update', Store::class) ?? false)
            ->afterReordering(fn () => CacheableUpdatedEvent::dispatch())
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('city')->searchable(),
                TextColumn::make('weight')->sortable(),
                IconColumn::make('status')->boolean(),
            ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
