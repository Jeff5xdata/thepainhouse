<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Add new unique constraint that includes set_number
            // Note: We're not dropping the old constraint because it's being used by foreign keys
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
            
            // Note: We don't restore the original constraint in down() because
            // it still exists and is being used by foreign keys
        });
    }
};
