<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Turahe\Subscription\Models\PlanFeature;
use Turahe\Subscription\Models\PlanSubscription;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('subscription.tables.subscription_usage', 'plan_subscription_usage'), function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignIdFor(config('subscription.models.subscription', PLanSubscription::class));
            $table->foreignIdFor(config('subscription.models.feature', PlanFeature::class));
            $table->unsignedSmallInteger('used');
            $table->string('timezone')->nullable();

            $table->integer('valid_until')->nullable();

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
        Schema::dropIfExists(config('subscription.tables.subscription_usage', 'plan_subscription_usage'));
    }
};
