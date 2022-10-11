<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception;
use Laminas\Filter\UpperCaseWords as UpperCaseWordsFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

use function mb_internal_encoding;

/**
 * @covers \Laminas\Filter\UpperCaseWords
 */
class UpperCaseWordsTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /**
     * Laminas_Filter_UpperCaseWords object
     *
     * @var UpperCaseWordsFilter
     */
    protected $_filter;
    // @codingStandardsIgnoreEnd

    /**
     * Creates a new Laminas_Filter_UpperCaseWords object for each test method
     */
    public function setUp(): void
    {
        $this->_filter = new UpperCaseWordsFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            'string' => 'String',
            'aBc1@3' => 'Abc1@3',
            'A b C'  => 'A B C',
        ];

        foreach ($valuesExpected as $input => $output) {
            $this->assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior with
     * specified encoding
     */
    public function testWithEncoding(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
        ];

        try {
            $filter->setEncoding('UTF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    public function testFalseEncoding(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('is not supported');
        $this->_filter->setEncoding('aaaaa');
    }

    /**
     * @Laminas-8989
     */
    public function testInitiationWithEncoding(): void
    {
        $valuesExpected = [
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
        ];

        try {
            $filter = new UpperCaseWordsFilter([
                'encoding' => 'UTF-8',
            ]);
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @Laminas-9058
     */
    public function testCaseInsensitiveEncoding(): void
    {
        $filter         = $this->_filter;
        $valuesExpected = [
            '√º'      => '√º',
            '√±'      => '√±',
            '√º√±123' => '√º√±123',
        ];

        try {
            $filter->setEncoding('UTF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }

            $this->_filter->setEncoding('utf-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }

            $this->_filter->setEncoding('UtF-8');
            foreach ($valuesExpected as $input => $output) {
                $this->assertSame($output, $filter($input));
            }
        } catch (Exception\ExtensionNotLoadedException $e) {
            $this->assertContains('mbstring is required', $e->getMessage());
        }
    }

    /**
     * @group Laminas-9854
     */
    public function testDetectMbInternalEncoding(): void
    {
        $this->assertSame(mb_internal_encoding(), $this->_filter->getEncoding());
    }

    public function returnUnfilteredDataProvider()
    {
        return [
            [null],
            [new stdClass()],
            [123],
            [123.456],
            [
                [
                    'Upper CASE and lowerCase Words WRITTEN',
                    'This Should Stay The Same',
                ],
            ],
        ];
    }

    /**
     * @dataProvider returnUnfilteredDataProvider
     * @param mixed $input
     */
    public function testReturnUnfiltered($input): void
    {
        $this->assertSame($input, $this->_filter->filter($input));
    }
}
