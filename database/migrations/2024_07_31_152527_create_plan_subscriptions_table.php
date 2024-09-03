<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('subscription.tables.subscriptions'), function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->ulidMorphs('subscriber');
            $table->foreignIdFor(config('subscription.models.plan'));
            $table->json('name');
            $table->string('slug')->index()->unique();
            $table->json('description')->nullable();
            $table->string('timezone')->nullable();

            $table->integer('trial_ends_at')->index()->nullable();
            $table->integer('starts_at')->index()->nullable();
            $table->integer('ends_at')->index()->nullable();
            $table->integer('cancels_at')->index()->nullable();
            $table->integer('canceled_at')->index()->nullable();

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
        Schema::dropIfExists(config('subscription.tables.subscription'));
    }
};
