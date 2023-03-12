<?php

declare(strict_types=1);

namespace Tests\Analysis;

use GrahamCampbell\Analyzer\AnalysisTrait;
use Tests\TestCase;

final class AnalysisTest extends TestCase
{
    use AnalysisTrait;

    public static function getPaths(): array
    {
        return [
            __DIR__.'/../src',
        ];
    }
}
