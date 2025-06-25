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
        // Add indexes to workout_sessions table
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'date']);
            $table->index(['workout_plan_id', 'date']);
            $table->index(['user_id', 'status']);
        });

        // Add indexes to workout_plans table
        Schema::table('workout_plans', function (Blueprint $table) {
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'created_at']);
        });

        // Add indexes to exercise_sets table
        Schema::table('exercise_sets', function (Blueprint $table) {
            $table->index(['exercise_id', 'created_at']);
            $table->index(['workout_session_id', 'exercise_id']);
        });

        // Add indexes to workout_plan_schedule table
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            $table->index(['workout_plan_id', 'week_number', 'day_of_week']);
            $table->index(['exercise_id', 'week_number']);
        });

        // Add indexes to share_links table - commented out due to migration order issue
        // Schema::table('share_links', function (Blueprint $table) {
        //     $table->index(['token']);
        //     $table->index(['workout_plan_id', 'user_id']);
        //     $table->index(['expires_at']);
        // });

        // Add indexes to exercises table
        Schema::table('exercises', function (Blueprint $table) {
            $table->index(['category']);
            $table->index(['equipment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from workout_sessions table
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['workout_plan_id', 'date']);
            $table->dropIndex(['user_id', 'status']);
        });

        // Remove indexes from workout_plans table
        Schema::table('workout_plans', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        // Remove indexes from exercise_sets table
        Schema::table('exercise_sets', function (Blueprint $table) {
            $table->dropIndex(['exercise_id', 'created_at']);
            $table->dropIndex(['workout_session_id', 'exercise_id']);
        });

        // Remove indexes from workout_plan_schedule table
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            $table->dropIndex(['workout_plan_id', 'week_number', 'day_of_week']);
            $table->dropIndex(['exercise_id', 'week_number']);
        });

        // Remove indexes from share_links table - commented out due to migration order issue
        // Schema::table('share_links', function (Blueprint $table) {
        //     $table->dropIndex(['token']);
        //     $table->dropIndex(['workout_plan_id', 'user_id']);
        //     $table->dropIndex(['expires_at']);
        // });

        // Remove indexes from exercises table
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['equipment']);
        });
    }
};
