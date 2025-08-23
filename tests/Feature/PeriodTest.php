<?php

namespace Turahe\Subscription\Tests\Feature;

use Carbon\Carbon;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Tests\TestCase;

class PeriodTest extends TestCase
{
    public function test_period_class()
    {
        $period = new Period(Interval::Month, 1, $now = Carbon::now());
        $this->assertEquals($now->format('Y-m-d H:i:s'), $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals($now->copy()->addMonth()->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(Carbon::class, $period->getStartDate());
        $this->assertInstanceOf(Carbon::class, $period->getEndDate());
        $this->assertEquals('month', $period->getInterval());
        $this->assertEquals(1, $period->getIntervalCount());
    }

    #[DataProvider('providePeriodCases')]
    public function test_interval_period(Interval $interval, int $count): void
    {
        $period = new Period($interval, $count);

        $this->assertEquals($interval->value, $period->getInterval());
        $this->assertEquals($count, $period->getIntervalCount());
    }

    public static function providePeriodCases(): Generator
    {
        yield [Interval::Year, 1];
        yield [Interval::Year, 2];
        yield [Interval::Month, 1];
        yield [Interval::Month, 2];
        yield [Interval::Week, 1];
        yield [Interval::Week, 2];
        yield [Interval::Day, 1];
        yield [Interval::Day, 2];
    }

    #[DataProvider('provideStartAndEndDatePeriod')]
    public function test_start_date_and_end_date_period(int $count): void
    {
        $period = new Period(Interval::Month, $count);

        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(Carbon::now()->copy()->addMonths($count)->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    public static function provideStartAndEndDatePeriod(): Generator
    {
        yield [4];
        yield [7];
    }
}
