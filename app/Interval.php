<?php

declare(strict_types=1);

namespace Modules\Subscriptions;

enum Interval: string
{
    case YEAR = 'year';

    case MONTH = 'month';

    case DAY = 'day';
}
