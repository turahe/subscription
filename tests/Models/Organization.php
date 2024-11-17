<?php

namespace Turahe\Subscription\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Turahe\Ledger\Tests\Factories\OrganizationFactory;

class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    /**
     * Create a new factory instance for the model.
     *
     * @return OrganizationFactory
     */
    protected static function newFactory()
    {
        return new OrganizationFactory;
    }
}
