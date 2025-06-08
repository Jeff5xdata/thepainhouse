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
            // Drop foreign keys first
            $table->dropForeign(['workout_plan_id']);
            $table->dropForeign(['exercise_id']);
            
            // Now we can drop the unique index
            $table->dropUnique('unique_exercise_order');
            
            // Re-add the foreign keys
            $table->foreign('workout_plan_id')->references('id')->on('workout_plans')->onDelete('cascade');
            $table->foreign('exercise_id')->references('id')->on('exercises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['workout_plan_id']);
            $table->dropForeign(['exercise_id']);
            
            // Add the unique constraint back
            $table->unique(['workout_plan_id', 'week_number', 'day_of_week', 'order_in_day'], 'unique_exercise_order');
            
            // Re-add the foreign keys
            $table->foreign('workout_plan_id')->references('id')->on('workout_plans')->onDelete('cascade');
            $table->foreign('exercise_id')->references('id')->on('exercises')->onDelete('cascade');
        });
    }
};
