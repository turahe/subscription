<?php

declare(strict_types=1);

namespace Turahe\Subscription\Services;

use Carbon\Carbon;
use Turahe\Subscription\Enums\Interval;

final class Period
{
    private Carbon|string $start;

    private Carbon|string $end;

    private $interval;

    private int $period;

    /**
     * Create a new Period instance.
     *
     *
     * @return void
     */
    public function __construct(Interval $interval = Interval::Month, int $count = 1, ?Carbon $start = null)
    {
        $this->interval = $interval;

        if (empty($start)) {
            $this->start = Carbon::now();
        } elseif (! $start instanceof Carbon) {
            $this->start = new Carbon($start);
        } else {
            $this->start = $start;
        }

        $this->period = $count;
        $start = clone $this->start;
        $method = 'add'.ucfirst($this->interval->value).'s';
        $this->end = $start->{$method}($this->period);
    }

    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    public function getEndDate(): Carbon
    {
        return $this->end;
    }

    public function getInterval(): string
    {
        return $this->interval->value;
    }

    public function getIntervalCount(): int
    {
        return $this->period;
    }
}
