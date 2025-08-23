<?php

declare(strict_types=1);

namespace Turahe\Subscription\Enums;

enum Interval: string
{
    case Day = 'DAY';
    case Week = 'WEEK';
    case Month = 'MONTH';
    case Year = 'YEAR';

    public function label(): string
    {
        return match($this) {
            self::Day => 'Daily',
            self::Week => 'Weekly',
            self::Month => 'Monthly',
            self::Year => 'Yearly',
        };
    }

    public function isRecurring(): bool
    {
        return in_array($this, [self::Month, self::Year]);
    }

    public function getDays(): int
    {
        return match($this) {
            self::Day => 1,
            self::Week => 7,
            self::Month => 30,
            self::Year => 365,
        };
    }

    public function getOrder(): int
    {
        return match($this) {
            self::Day => 1,
            self::Week => 2,
            self::Month => 3,
            self::Year => 4,
        };
    }


}
