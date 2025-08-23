<?php

declare(strict_types=1);

namespace Turahe\Subscription\Services;

use Carbon\Carbon;
use Turahe\Subscription\Enums\Interval;

final class Period
{
    private static array $methodCache = [];

    public function __construct(
        public readonly Interval $interval,
        public readonly int $count,
        public readonly Carbon $start = new Carbon(),
    ) {
    }

    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    public function getEndDate(): Carbon
    {
        $end = clone $this->start;
        $method = $this->getCachedMethod();
        
        return $end->{$method}($this->count);
    }

    public function getInterval(): string
    {
        return $this->interval->value;
    }

    public function getIntervalCount(): int
    {
        return $this->count;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    private function getCachedMethod(): string
    {
        $cacheKey = $this->interval->value . '_' . $this->count;
        
        if (!isset(self::$methodCache[$cacheKey])) {
            self::$methodCache[$cacheKey] = match($this->interval) {
                Interval::Day => 'addDays',
                Interval::Week => 'addWeeks',
                Interval::Month => 'addMonths',
                Interval::Year => 'addYears',
            };
        }
        
        return self::$methodCache[$cacheKey];
    }

    public function __toString(): string
    {
        return $this->start->format('Y-m-d');
    }
}
