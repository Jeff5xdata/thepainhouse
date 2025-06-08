<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing table if it exists
        Schema::dropIfExists('workout_plan_schedule');

        Schema::create('workout_plan_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->integer('week_number')->default(1);
            $table->string('day_of_week');
            $table->integer('order_in_day')->default(0);
            $table->boolean('is_time_based')->default(false);
            $table->integer('sets')->default(3);
            $table->integer('reps')->default(10);
            $table->integer('time_in_seconds')->nullable();
            $table->boolean('has_warmup')->default(false);
            $table->integer('warmup_sets')->nullable();
            $table->integer('warmup_reps')->nullable();
            $table->integer('warmup_time_in_seconds')->nullable();
            $table->integer('warmup_weight_percentage')->nullable();
            $table->timestamps();

            // Add unique constraint for order within a day
            $table->unique(['workout_plan_id', 'week_number', 'day_of_week', 'order_in_day'], 'unique_exercise_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plan_schedule');
    }
};
