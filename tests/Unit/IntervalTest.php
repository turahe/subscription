<?php

namespace Turahe\Subscription\Tests\Unit;

use Turahe\Subscription\Enums\Interval;
use Turahe\Subscription\Tests\TestCase;

class IntervalTest extends TestCase
{
    public function test_interval_enum_values()
    {
        $this->assertEquals('DAY', Interval::Day->value);
        $this->assertEquals('WEEK', Interval::Week->value);
        $this->assertEquals('MONTH', Interval::Month->value);
        $this->assertEquals('YEAR', Interval::Year->value);
    }

    public function test_interval_enum_names()
    {
        $this->assertEquals('Day', Interval::Day->name);
        $this->assertEquals('Week', Interval::Week->name);
        $this->assertEquals('Month', Interval::Month->name);
        $this->assertEquals('Year', Interval::Year->name);
    }

    public function test_interval_enum_cases()
    {
        $cases = Interval::cases();
        
        $this->assertCount(4, $cases);
        $this->assertContains(Interval::Day, $cases);
        $this->assertContains(Interval::Week, $cases);
        $this->assertContains(Interval::Month, $cases);
        $this->assertContains(Interval::Year, $cases);
    }

    public function test_interval_enum_from_value()
    {
        $this->assertEquals(Interval::Day, Interval::from('DAY'));
        $this->assertEquals(Interval::Week, Interval::from('WEEK'));
        $this->assertEquals(Interval::Month, Interval::from('MONTH'));
        $this->assertEquals(Interval::Year, Interval::from('YEAR'));
    }

    public function test_interval_enum_try_from()
    {
        $this->assertEquals(Interval::Day, Interval::tryFrom('DAY'));
        $this->assertEquals(Interval::Week, Interval::tryFrom('WEEK'));
        $this->assertEquals(Interval::Month, Interval::tryFrom('MONTH'));
        $this->assertEquals(Interval::Year, Interval::tryFrom('YEAR'));
        $this->assertNull(Interval::tryFrom('invalid'));
    }

    public function test_interval_enum_from_invalid_value()
    {
        $this->expectException(\ValueError::class);
        Interval::from('invalid');
    }

    public function test_interval_enum_equality()
    {
        $day1 = Interval::Day;
        $day2 = Interval::Day;
        $week = Interval::Week;
        
        $this->assertTrue($day1 === $day2);
        $this->assertFalse($day1 === $week);
    }

    public function test_interval_enum_comparison()
    {
        $this->assertTrue(Interval::Day < Interval::Week);
        $this->assertTrue(Interval::Week < Interval::Month);
        $this->assertTrue(Interval::Month < Interval::Year);
    }

    public function test_interval_enum_to_string()
    {
        $this->assertEquals('DAY', (string) Interval::Day);
        $this->assertEquals('WEEK', (string) Interval::Week);
        $this->assertEquals('MONTH', (string) Interval::Month);
        $this->assertEquals('YEAR', (string) Interval::Year);
    }

    public function test_interval_enum_json_serialization()
    {
        $this->assertEquals('"DAY"', json_encode(Interval::Day));
        $this->assertEquals('"WEEK"', json_encode(Interval::Week));
        $this->assertEquals('"MONTH"', json_encode(Interval::Month));
        $this->assertEquals('"YEAR"', json_encode(Interval::Year));
    }

    public function test_interval_enum_serialization()
    {
        $day = Interval::Day;
        $serialized = serialize($day);
        $unserialized = unserialize($serialized);
        
        $this->assertEquals($day, $unserialized);
    }

    public function test_interval_enum_values_array()
    {
        $values = array_column(Interval::cases(), 'value');
        
        $this->assertContains('DAY', $values);
        $this->assertContains('WEEK', $values);
        $this->assertContains('MONTH', $values);
        $this->assertContains('YEAR', $values);
    }

    public function test_interval_enum_names_array()
    {
        $names = array_column(Interval::cases(), 'name');
        
        $this->assertContains('Day', $names);
        $this->assertContains('Week', $names);
        $this->assertContains('Month', $names);
        $this->assertContains('Year', $names);
    }

    public function test_interval_enum_case_sensitive()
    {
        $this->assertNull(Interval::tryFrom('day'));
        $this->assertNull(Interval::tryFrom('Week'));
        $this->assertNull(Interval::tryFrom('month'));
        $this->assertNull(Interval::tryFrom('Year'));
    }

    public function test_interval_enum_whitespace_handling()
    {
        $this->assertNull(Interval::tryFrom(' DAY'));
        $this->assertNull(Interval::tryFrom('DAY '));
        $this->assertNull(Interval::tryFrom(' DAY '));
    }

    public function test_interval_enum_empty_string()
    {
        $this->assertNull(Interval::tryFrom(''));
        $this->expectException(\ValueError::class);
        Interval::from('');
    }

    public function test_interval_enum_null_handling()
    {
        $this->assertNull(Interval::tryFrom(null));
        $this->expectException(\TypeError::class);
        Interval::from(null);
    }

    public function test_interval_enum_array_access()
    {
        $cases = Interval::cases();
        
        $this->assertEquals(Interval::Day, $cases[0]);
        $this->assertEquals(Interval::Week, $cases[1]);
        $this->assertEquals(Interval::Month, $cases[2]);
        $this->assertEquals(Interval::Year, $cases[3]);
    }

    public function test_interval_enum_iteration()
    {
        $values = [];
        foreach (Interval::cases() as $case) {
            $values[] = $case->value;
        }
        
        $this->assertContains('DAY', $values);
        $this->assertContains('WEEK', $values);
        $this->assertContains('MONTH', $values);
        $this->assertContains('YEAR', $values);
    }
} 