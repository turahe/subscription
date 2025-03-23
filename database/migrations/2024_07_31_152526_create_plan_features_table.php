<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Turahe\Subscription\Models\Plan;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('subscription.tables.features', 'plan_features'), function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignIdFor(config('subscription.models.plan', Plan::class));
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->integer('value');
            $table->unsignedSmallInteger('resettable_period')->default(0);
            $table->string('resettable_interval')->default('month');
            $table->unsignedBigInteger('record_ordering')->nullable();

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

            if (config('core.table.use_timestamps')) {
                $table->timestamps();
                $table->softDeletes();
            } else {
                $table->integer('created_at')->index()->nullable();
                $table->integer('updated_at')->index()->nullable();
                $table->integer('deleted_at')->index()->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('subscription.tables.features', 'plan_features'));
    }
};
