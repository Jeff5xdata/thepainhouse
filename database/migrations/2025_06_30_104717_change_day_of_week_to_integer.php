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
        // Change day_of_week column type in workout_plan_schedule table
        DB::statement('ALTER TABLE workout_plan_schedule MODIFY COLUMN day_of_week INT');

        // Change day_of_week column type in workout_sessions table
        DB::statement('ALTER TABLE workout_sessions MODIFY COLUMN day_of_week INT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change day_of_week column type back to string in workout_plan_schedule table
        DB::statement('ALTER TABLE workout_plan_schedule MODIFY COLUMN day_of_week VARCHAR(255)');

        // Change day_of_week column type back to string in workout_sessions table
        DB::statement('ALTER TABLE workout_sessions MODIFY COLUMN day_of_week VARCHAR(255)');
    }
};
