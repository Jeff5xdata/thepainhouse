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
        // Add constraints to workout_sessions table
        Schema::table('workout_sessions', function (Blueprint $table) {
            // Ensure week_number is positive
            $table->unsignedInteger('week_number')->change();
        });

        // Add constraints to exercise_sets table
        Schema::table('exercise_sets', function (Blueprint $table) {
            // Ensure set_number is positive
            $table->unsignedInteger('set_number')->change();
            
            // Ensure reps is non-negative
            $table->unsignedInteger('reps')->default(0)->change();
            
            // Ensure time_in_seconds is positive
            $table->unsignedInteger('time_in_seconds')->nullable()->change();
        });

        // Add constraints to workout_plans table
        Schema::table('workout_plans', function (Blueprint $table) {
            // Ensure weeks_duration is positive
            $table->unsignedInteger('weeks_duration')->change();
        });

        // Add constraints to workout_plan_schedule table
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Ensure week_number is positive
            $table->unsignedInteger('week_number')->change();
            
            // Ensure sets is positive
            $table->unsignedInteger('sets')->change();
            
            // Ensure reps is non-negative
            $table->unsignedInteger('reps')->nullable()->change();
            
            // Ensure time_in_seconds is positive
            $table->unsignedInteger('time_in_seconds')->nullable()->change();
            
            // Ensure warmup_sets is non-negative
            $table->unsignedInteger('warmup_sets')->nullable()->change();
            
            // Ensure warmup_reps is non-negative
            $table->unsignedInteger('warmup_reps')->nullable()->change();
            
            // Ensure warmup_weight_percentage is between 0 and 100
            $table->unsignedInteger('warmup_weight_percentage')->nullable()->change();
        });

        // Add unique constraint to prevent duplicate sessions per user per day
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'unique_user_session_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove unique constraint
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropUnique('unique_user_session_per_day');
        });

        // Revert constraints for workout_sessions table
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->integer('week_number')->change();
        });

        // Revert constraints for exercise_sets table
        Schema::table('exercise_sets', function (Blueprint $table) {
            $table->integer('set_number')->change();
            $table->integer('reps')->change();
            $table->integer('time_in_seconds')->nullable()->change();
        });

        // Revert constraints for workout_plans table
        Schema::table('workout_plans', function (Blueprint $table) {
            $table->integer('weeks_duration')->change();
        });

        // Revert constraints for workout_plan_schedule table
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            $table->integer('week_number')->change();
            $table->integer('sets')->change();
            $table->integer('reps')->nullable()->change();
            $table->integer('time_in_seconds')->nullable()->change();
            $table->integer('warmup_sets')->nullable()->change();
            $table->integer('warmup_reps')->nullable()->change();
            $table->integer('warmup_weight_percentage')->nullable()->change();
        });
    }
};
