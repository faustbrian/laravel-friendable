<?php

declare(strict_types=1);

namespace Tests\Analysis;

use PreemStudio\Jetpack\Tests\AbstractAnalysisTestCase;

final class AnalysisTest extends AbstractAnalysisTestCase
{
    public static function getPaths(): array
    {
        return [
            __DIR__.'/../src',
        ];
    }
}
