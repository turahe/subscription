<?php

namespace Turahe\Subscription\Tests\Feature;

use Carbon\Carbon;
use Turahe\Subscription\Services\Period;
use Turahe\Subscription\Tests\TestCase;

class PeriodTest extends TestCase
{
    public function testPeriod()
    {
        $period = new Period;
        $this->assertEquals(Carbon::now()->format('Y-m-d H:i:s'), $period->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(Carbon::now()->addMonth()->format('Y-m-d H:i:s'), $period->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(Carbon::class, $period->getStartDate());
        $this->assertInstanceOf(Carbon::class, $period->getEndDate());
        $this->assertEquals('month', $period->getInterval());
        $this->assertEquals(1, $period->getIntervalCount());
    }
}
