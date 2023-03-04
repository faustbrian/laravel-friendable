<?php

declare(strict_types=1);

namespace PreemStudio\Friendships;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-friendships')
            ->hasMigration('create_friendships_table');
    }
}
