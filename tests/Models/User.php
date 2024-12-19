<?php

namespace Turahe\Subscription\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Turahe\Subscription\Traits\HasPlanSubscriptions;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasPlanSubscriptions;
    use HasUlids;

    protected $table = 'users';
}
