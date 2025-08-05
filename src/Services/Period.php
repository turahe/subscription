<?php

declare(strict_types=1);

namespace Turahe\Subscription\Services;

use Carbon\Carbon;
use Turahe\Subscription\Enums\Interval;

final class Period
{
    public function __construct(
        private readonly Interval $interval = Interval::Month,
        private readonly int $count = 1,
        private readonly Carbon $start = new Carbon(),
    ) {
    }

    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    public function getEndDate(): Carbon
    {
        $end = clone $this->start;
        $method = 'add' . ucfirst(strtolower($this->interval->value)) . 's';
        
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
}
