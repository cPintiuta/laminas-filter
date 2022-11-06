<?php

declare(strict_types=1);

namespace LaminasTest\Filter\Word;

use Laminas\Filter\Word\UnderscoreToStudlyCase;
use PHPUnit\Framework\TestCase;

class UnderscoreToStudlyCaseTest extends TestCase
{
    public function testFilterSeparatesStudlyCasedWordsWithDashes(): void
    {
        $string   = 'studly_cased_words';
        $filter   = new UnderscoreToStudlyCase();
        $filtered = $filter($string);

        self::assertNotEquals($string, $filtered);
        self::assertSame('studlyCasedWords', $filtered);
    }

    public function testSomeFilterValues(): void
    {
        $filter = new UnderscoreToStudlyCase();

        $string   = 'laminas_project';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('laminasProject', $filtered);

        $string   = 'laminas_Project';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('laminasProject', $filtered);

        $string   = 'laminasProject';
        $filtered = $filter($string);
        self::assertSame('laminasProject', $filtered);

        $string   = 'laminas';
        $filtered = $filter($string);
        self::assertSame('laminas', $filtered);

        $string   = '_laminas';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('laminas', $filtered);

        $string   = '_laminas_project';
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame('laminasProject', $filtered);
    }

    public function testFiltersArray(): void
    {
        $filter = new UnderscoreToStudlyCase();

        $string   = ['laminas_project', '_laminas_project'];
        $filtered = $filter($string);
        self::assertNotEquals($string, $filtered);
        self::assertSame(['laminasProject', 'laminasProject'], $filtered);
    }

    public function testWithEmpties(): void
    {
        $filter = new UnderscoreToStudlyCase();

        $string   = '';
        $filtered = $filter($string);
        self::assertSame('', $filtered);

        $string   = ['', 'Laminas_Project'];
        $filtered = $filter($string);
        self::assertSame(['', 'laminasProject'], $filtered);
    }
}
