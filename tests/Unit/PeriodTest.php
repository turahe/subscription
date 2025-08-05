<?php

namespace Turahe\Subscription\Tests\Unit;

use Carbon\Carbon;
use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Tests\TestCase;

class PeriodTest extends TestCase
{
    public function test_can_create_period_with_day_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Day, 7, $startDate);
        
        $this->assertEquals(Interval::Day, $period->interval);
        $this->assertEquals(7, $period->count);
        $this->assertEquals($startDate, $period->start);
    }

    public function test_can_create_period_with_week_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Week, 2, $startDate);
        
        $this->assertEquals(Interval::Week, $period->interval);
        $this->assertEquals(2, $period->count);
        $this->assertEquals($startDate, $period->start);
    }

    public function test_can_create_period_with_month_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Month, 1, $startDate);
        
        $this->assertEquals(Interval::Month, $period->interval);
        $this->assertEquals(1, $period->count);
        $this->assertEquals($startDate, $period->start);
    }

    public function test_can_create_period_with_year_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Year, 1, $startDate);
        
        $this->assertEquals(Interval::Year, $period->interval);
        $this->assertEquals(1, $period->count);
        $this->assertEquals($startDate, $period->start);
    }

    public function test_get_start_date()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Day, 7, $startDate);
        
        $this->assertEquals($startDate, $period->getStartDate());
    }

    public function test_get_end_date_with_day_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Day, 7, $startDate);
        
        $expectedEndDate = $startDate->copy()->addDays(7);
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_get_end_date_with_week_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Week, 2, $startDate);
        
        $expectedEndDate = $startDate->copy()->addWeeks(2);
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_get_end_date_with_month_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Month, 1, $startDate);
        
        $expectedEndDate = $startDate->copy()->addMonth();
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_get_end_date_with_year_interval()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Year, 1, $startDate);
        
        $expectedEndDate = $startDate->copy()->addYear();
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_get_end_date_with_multiple_months()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Month, 3, $startDate);
        
        $expectedEndDate = $startDate->copy()->addMonths(3);
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_get_end_date_with_multiple_years()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Year, 2, $startDate);
        
        $expectedEndDate = $startDate->copy()->addYears(2);
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_period_with_zero_count()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Day, 0, $startDate);
        
        $this->assertEquals($startDate, $period->getEndDate());
    }

    public function test_period_with_negative_count()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Day, -5, $startDate);
        
        $expectedEndDate = $startDate->copy()->subDays(5);
        $this->assertEquals($expectedEndDate->format('Y-m-d'), $period->getEndDate()->format('Y-m-d'));
    }

    public function test_period_with_specific_date()
    {
        $startDate = Carbon::create(2024, 1, 1, 12, 0, 0);
        $period = new Period(Interval::Month, 1, $startDate);
        
        $expectedEndDate = Carbon::create(2024, 2, 1, 12, 0, 0);
        $this->assertEquals($expectedEndDate->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function test_period_with_leap_year()
    {
        $startDate = Carbon::create(2024, 2, 29, 12, 0, 0);
        $period = new Period(Interval::Year, 1, $startDate);
        
        $expectedEndDate = Carbon::create(2025, 2, 28, 12, 0, 0);
        $this->assertEquals($expectedEndDate->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function test_period_with_month_end_dates()
    {
        $startDate = Carbon::create(2024, 1, 31, 12, 0, 0);
        $period = new Period(Interval::Month, 1, $startDate);
        
        $expectedEndDate = Carbon::create(2024, 2, 29, 12, 0, 0); // Leap year
        $this->assertEquals($expectedEndDate->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function test_period_with_different_timezones()
    {
        $startDate = Carbon::now()->setTimezone('UTC');
        $period = new Period(Interval::Day, 1, $startDate);
        
        $endDate = $period->getEndDate();
        $this->assertEquals('UTC', $endDate->timezone->getName());
    }

    public function test_period_immutability()
    {
        $startDate = Carbon::now();
        $originalStartDate = $startDate->copy();
        $period = new Period(Interval::Day, 7, $startDate);
        
        // Modify the start date after creating period
        $startDate->addDay();
        
        // Period should still use the original start date
        $this->assertEquals($originalStartDate->format('Y-m-d'), $period->getStartDate()->format('Y-m-d'));
    }

    public function test_period_to_string()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Month, 3, $startDate);
        
        $this->assertIsString((string) $period);
    }

    public function test_period_json_serialization()
    {
        $startDate = Carbon::now();
        $period = new Period(Interval::Day, 7, $startDate);
        
        $json = json_encode($period);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
    }
} 