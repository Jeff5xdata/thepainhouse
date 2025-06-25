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
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique('unique_exercise_order');
            
            // Add new unique constraint that includes set_number
            $table->unique(['workout_plan_id', 'week_number', 'day_of_week', 'order_in_day', 'set_number'], 'unique_exercise_set_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('unique_exercise_set_order');
            
            // Restore the original unique constraint
            $table->unique(['workout_plan_id', 'week_number', 'day_of_week', 'order_in_day'], 'unique_exercise_order');
        });
    }
};
