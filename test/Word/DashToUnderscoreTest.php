<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\DashToUnderscore as DashToUnderscoreFilter;
use PHPUnit\Framework\TestCase;

class DashToUnderscoreTest extends TestCase
{
    public function testFilterSeparatesCamelCasedWordsWithDashes(): void
    {
        $string   = 'dash-separated-words';
        $filter   = new DashToUnderscoreFilter();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('dash_separated_words', $filtered);
    }
}
