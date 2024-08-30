<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\subscription\Interval;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('subscription.tables.plans'), function (Blueprint $table): void {
            $table->ulid('id')->primary();

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
            $table->string('trial_interval')->default(Interval::DAY->value);
            $table->unsignedSmallInteger('invoice_period')->default(0);
            $table->string('invoice_interval')->default(Interval::MONTH->value);
            $table->unsignedSmallInteger('grace_period')->default(0);
            $table->string('grace_interval')->default(Interval::DAY->value);
            $table->unsignedTinyInteger('prorate_day')->nullable();
            $table->unsignedTinyInteger('prorate_period')->nullable();
            $table->unsignedTinyInteger('prorate_extend_due')->nullable();
            $table->unsignedSmallInteger('active_subscribers_limit')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->foreignUlid('created_by')
                ->index()
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUlid('updated_by')
                ->index()
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUlid('deleted_by')
                ->index()
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->integer('deleted_at')->index()->nullable();
            $table->integer('created_at')->index()->nullable();
            $table->integer('updated_at')->index()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('subscription.tables.plans'));
    }
};
