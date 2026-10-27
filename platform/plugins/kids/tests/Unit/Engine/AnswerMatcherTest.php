<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\AnswerMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AnswerMatcherTest extends TestCase
{
    #[DataProvider('matchingCasesProvider')]
    public function test_matches(Answer $expected, string $input, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, AnswerMatcher::matches($expected, $input));
    }

    public static function matchingCasesProvider(): array
    {
        $int1000 = new Answer('integer', '1000');
        $dec25 = new Answer('decimal', '2.5');
        $frac12 = new Answer('fraction', '1/2');
        $frac12Simplest = new Answer('fraction', '1/2', requireSimplest: true);
        $frac32 = new Answer('fraction', '3/2');
        $choiceGt = new Answer('choice', '>');

        return [
            // integer
            'int exact' => [$int1000, '1000', true],
            'int thousand dots' => [$int1000, ' 1.000 ', true],
            'int thousand spaces' => [$int1000, '1 000', true],
            'int trailing decimal zero rejected' => [$int1000, '1000.0', false],
            'int text rejected' => [$int1000, 'abc', false],

            // decimal
            'decimal comma' => [$dec25, '2,5', true],
            'decimal dot' => [$dec25, '2.5', true],
            'decimal trailing zero' => [$dec25, '2,50', true],
            'decimal different value' => [$dec25, '2.05', false],

            // fraction
            'fraction equivalent' => [$frac12, '2/4', true],
            'fraction exact' => [$frac12, '1/2', true],
            'fraction different' => [$frac12, '3/4', false],
            'fraction decimal rejected' => [$frac12, '0.5', false],

            // fraction with requireSimplest: true
            'fraction unsimplified rejected when simplest required' => [$frac12Simplest, '2/4', false],
            'fraction simplified accepted when simplest required' => [$frac12Simplest, '1/2', true],

            // mixed fraction
            'fraction mixed number' => [$frac32, '1 1/2', true],

            // choice
            'choice exact' => [$choiceGt, '>', true],
            'choice wrong' => [$choiceGt, '<', false],
        ];
    }

    public function test_display_uses_vietnamese_decimal_comma(): void
    {
        $decimalAnswer = new Answer('decimal', '2.5');
        $this->assertSame('2,5', $decimalAnswer->display());

        $intAnswer = new Answer('integer', '1000');
        $this->assertSame('1000', $intAnswer->display());
    }
}
