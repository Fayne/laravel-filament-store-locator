<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Store Locator

[简体中文](../README.md) | English

Store management and public lookup for **PHP 8.4+, Laravel 13 and Filament 5**, depending on CMS Core and Qrcode.

## Features and boundaries

Manage stores, publication status and sorting; addresses, coordinates, contact details, weekly hours and date exceptions; logos and banners via native Filament FileUpload. Public endpoints provide published listings, details, pagination and distance sorting. The package integrates with CMS module registration, permissions and tenant scoping.

No map provider, geocoding, automatic “open now” calculation or former Nova field-management UI is supplied. Some admin form headings remain English; bundled translation resources do not imply complete UI localization.

## 1. Installation

Configure authorized Composer sources for this private package and its required fayne dependencies first:

```bash
composer require fayne/laravel-filament-store-locator
php artisan vendor:publish --tag=store-locator-config
php artisan cms:install
```

Laravel discovers the provider and loads migrations. Core installation runs registered migrations, initializes permissions and publishes Filament assets. Production automation uses `--force --no-interaction`. Existing Core projects may run `php artisan migrate`, then rerun Core installation to add permission definitions. Never use migrate:fresh on business data.

The default public disk must expose accessible URLs; create storage links according to host configuration. An additional file-picker package is not required.

## 2. Panel integration and permissions

The provider registers `store-locator` with Core ModuleRegistry. Core-derived AdminPanelProvider / TenantPanelProvider automatically connect it to the ordinary single-site panel or tenant workspace.

In a fully custom panel, retain CMS, middleware, guard, tenancy and explicit plugins, then return:

```php
return app(\Fayne\CMS\Support\Modules\ModuleRegistry::class)->applyTo($panel);
```

Manual integration may use `Fayne\StoreLocator\Filament\StoreLocatorPlugin::make()`. Explicit same-ID plugins are preserved; do not register them again after applying the registry.

Permissions live in resources/permissions.php with group/name translations in resources/lang/{zh_CN,en}. Installation does not grant new permissions to existing roles. Grant store capabilities in the proper scope; tenant membership and ownership remain mandatory. Platform administrators do not automatically bypass tenant boundaries.

## 3. Configuration

Publish tag `store-locator-config` creates **config/store-locator.php**. Options:

| Environment variable | Default | Purpose |
| --- | --- | --- |
| STORE_LOCATOR_DISK | public | Upload disk |
| STORE_LOCATOR_ROUTE_ENABLED | true | Enable public routes |
| STORE_LOCATOR_ROUTE_CACHED | true | Cache listings on the server |
| STORE_LOCATOR_ROUTE_MAX_AGE | 3600 | HTTP cache max-age |

Listing caching requires a tag-capable cache driver; disable it for unsupported drivers. Server caching uses rememberForever and does not expire at HTTP max_age. Model creation/update/deletion and admin reordering trigger invalidation. Multi-tenant proxies/CDNs must distinguish request hosts and URLs. Refresh configuration/routes and reload long-lived processes after deployment changes.

## 4. Public API

| Method | Path | Purpose |
| --- | --- | --- |
| GET | /modules/store-locator/stores | Published paginated listings |
| GET | /modules/store-locator/stores/{store} | Published details; unpublished returns 404 |

Default page size is 15; page_size accepts 1–100. Example:

```text
GET /modules/store-locator/stores?order_by=distance&latitude=31.23&longitude=121.47&page_size=20
```

Distance ordering requires both coordinates: latitude -90–90, longitude -180–180. Verify database trigonometric support on the target database. Laravel Resources resolve media disk/path values; internal upload data is not itself a public URL.

Single-site data uses scope 0. Multi-tenant public routes use ResolvePublicTenant and require a valid configured tenant domain. Admin interface language does not create translated store content; content translation versions are not provided.

## 5. Programmatic usage

```php
use Fayne\StoreLocator\Models\Store;

$stores = Store::query()->published()->orderBy('weight')->get();
```

Queries remain site/tenant scoped. Set Core TenantContext explicitly in multi-tenant jobs and commands. Media casts manage logos/banners; structured attributes hold hours and brands. Custom write entrypoints remain responsible for authorization and validation.

## 6. Tests and deployment

From the package directory:

```bash
composer install
composer test
composer format
composer analyse
```

Accept migrations, role grants, CRUD/reordering, media URLs, publication filtering, cache invalidation and database distance queries. In multi-tenant mode, verify host resolution and cross-tenant isolation. Automated tests do not replace real storage, CDN and browser checks.

## Cooperation and contributions

Open an issue with versions, reproduction steps and expected behavior. Include relevant tests, both language documents and unverified environments in pull requests. Do not commit credentials, business data or composer.lock. Contact [Fayne Guo](mailto:fayne.guo@gmail.com) for commercial cooperation and licensing.

## License

[Proprietary license](../LICENSE.md). Use and distribution require authorization. Dependencies retain their respective licenses.
