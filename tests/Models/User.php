<?php

namespace Turahe\Subscription\Tests\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Turahe\Subscription\Traits\HasPlanSubscriptions;

class User extends Model
{
    use HasPlanSubscriptions;
    use HasUlids;

    protected $table = 'users';
}
