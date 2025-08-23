<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Turahe\Subscription\Enums\Interval;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('subscription.tables.plans', 'plans'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
            }

            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price')->default('0.00');
            $table->decimal('signup_fee')->default('0.00');
            $table->string('currency')->default('IDR')->index();
            $table->foreign('currency')
                ->references('iso_code')
                ->on('tm_currencies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedSmallInteger('trial_period')->default(0);
            $table->string('trial_interval')->default(Interval::Day->value);
            $table->unsignedSmallInteger('invoice_period')->default(0);
            $table->string('invoice_interval')->default(Interval::Month->value);
            $table->unsignedSmallInteger('grace_period')->default(0);
            $table->string('grace_interval')->default(Interval::Day->value);
            $table->unsignedTinyInteger('prorate_day')->nullable();
            $table->unsignedTinyInteger('prorate_period')->nullable();
            $table->unsignedTinyInteger('prorate_extend_due')->nullable();
            $table->unsignedSmallInteger('active_subscribers_limit')->nullable();
            $table->unsignedSmallInteger('record_ordering')->default(0);

            // Create userstamp columns with correct data types
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->unsignedBigInteger('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('created_by')->nullable()->index();
                $table->ulid('updated_by')->nullable()->index();
                $table->ulid('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('created_by')->nullable()->index();
                $table->uuid('updated_by')->nullable()->index();
                $table->uuid('deleted_by')->nullable()->index();
            }

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraints for userstamps
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('subscription.tables.plans', 'plans'));
    }
};
