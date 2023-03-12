<?php

declare(strict_types=1);

namespace PreemStudio\Friendships;

use PreemStudio\Jetpack\Package\AbstractServiceProvider;
use PreemStudio\Jetpack\Package\Package;

final class ServiceProvider extends AbstractServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-friendships')
            ->hasMigration('create_friendships_table');
    }
}
