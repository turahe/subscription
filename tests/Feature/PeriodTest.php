<?php

namespace Turahe\Subscription\Tests\Feature;

use Carbon\Carbon;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Tests\TestCase;

class PeriodTest extends TestCase
{
    public function testPeriodClass()
    {
        $period = new Period;
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(Carbon::now()->addMonth()->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(Carbon::class, $period->getStartDate());
        $this->assertInstanceOf(Carbon::class, $period->getEndDate());
        $this->assertEquals('month', $period->getInterval());
        $this->assertEquals(1, $period->getIntervalCount());
    }

    #[DataProvider('providePeriodCases')]
    public function testIntervalPeriod(string $interval, int $count): void
    {
        $period = new Period($interval, $count);

        $this->assertEquals($interval, $period->getInterval());
        $this->assertEquals($count, $period->getIntervalCount());
    }

    public static function providePeriodCases(): Generator
    {
        yield ['year', 1];
        yield ['year', 2];
        yield ['month', 1];
        yield ['month', 2];
        yield ['week', 1];
        yield ['week', 2];
        yield ['day', 1];
        yield ['day', 2];
    }

    #[DataProvider('provideStartAndEndDatePeriod')]
    public function testStartDateAndEndDatePeriod(int $count): void
    {
        $period = new Period('month', $count);

        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(Carbon::now()->addMonths($count)->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    public static function provideStartAndEndDatePeriod(): Generator
    {
        yield [4];
        yield [7];
    }
}
