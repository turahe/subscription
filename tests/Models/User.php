<?php

namespace Turahe\Subscription\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Turahe\Subscription\Tests\Factories\UserFactory;
use Turahe\Subscription\Traits\HasPlanSubscriptions;

class User extends Model
{
    use HasFactory;
    use HasPlanSubscriptions;

    protected $table = 'users';

    protected static function newFactory()
    {
        return new UserFactory;
    }
}
