<?php

declare(strict_types=1);

namespace Tests;

use PreemStudio\Jetpack\TestBench\AbstractPackageTestCase;

/**
 * @internal
 */
abstract class TestCase extends AbstractPackageTestCase
{
    protected function getServiceProviderClass(): string
    {
        return \PreemStudio\Friendable\ServiceProvider::class;
    }
}
