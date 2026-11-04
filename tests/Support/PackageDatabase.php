<?php

namespace Tests\Support;

use Spatie\Permission\PermissionRegistrar;

final class PackageDatabase
{
    public static function prepare(): void
    {
        $package = dirname(__DIR__, 2);
        $core = dirname((new \ReflectionClass(\Fayne\CMS\CMSServiceProvider::class))->getFileName(), 2);

        foreach (glob($core . '/database/migrations/*.php') ?: [] as $migration) {
            (require $migration)->up();
        }
        foreach (glob($package . '/database/migrations/*.php') ?: [] as $migration) {
            (require $migration)->up();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
