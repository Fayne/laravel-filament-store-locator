# Laravel Filament Store Locator

Manage store records and expose the store listing API in Laravel 13 and Filament 5.

## Requirements

- PHP 8.4+
- Laravel 13
- Filament 5
- `fayne/laravel-filament-cms-core`

## Installation

```bash
composer require fayne/laravel-filament-store-locator
php artisan migrate
```

Register the plugin in your panel:

```php
use Fayne\StoreLocator\Filament\StoreLocatorPlugin;

$panel->plugins([StoreLocatorPlugin::make()]);
```

The service provider and migrations are auto-discovered. Store logo and banner fields use Filament's native `FileUpload`; the model stores disk/path values for public API resolution. The FileManager package remains available for media administration but is not required as a selection widget.

## Tests and quality checks

Run from this package directory:

```bash
composer install
composer test
composer format
composer analyse
```

## Scope and roadmap

Current Filament scope includes store CRUD, configured API routes, response field control, cache invalidation, and disk/path media serialization. Historical Nova field management is not part of this Filament package.

## License

See [LICENSE.md](LICENSE.md).


## CMS permission registration

This package owns `resources/permissions.php`. When CMS Core is present, the service provider contributes these definitions to its `PermissionRegistry`; run `php artisan cms:install` to initialize the combined catalog and initial roles. Core no longer hardcodes this module's permissions. Existing role grants are preserved, so new module permissions must be explicitly assigned to existing roles.

See [permission registration](../laravel-filament-cms-core/docs/permission-registration.en.md).


Permission labels and groups are declared by this package in `resources/permissions.php`, with English and Simplified Chinese translations in `resources/lang`. CMS Core resolves the registered metadata without business-name matching.
