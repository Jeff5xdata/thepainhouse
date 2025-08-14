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
        // Update day_of_week from 1-7 to 0-6 to match Carbon's dayOfWeek format
        // Carbon: 0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday
        // Current: 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday, 7=Sunday
        
        // Update workout_plan_schedule table
        DB::table('workout_plan_schedule')
            ->where('day_of_week', '7')
            ->update(['day_of_week' => '0']);

        // Update workout_sessions table
        DB::table('workout_sessions')
            ->where('day_of_week', 7)
            ->update(['day_of_week' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert day_of_week from 0-6 back to 1-7
        // Update workout_plan_schedule table
        DB::table('workout_plan_schedule')
            ->where('day_of_week', '0')
            ->update(['day_of_week' => '7']);

        // Update workout_sessions table
        DB::table('workout_sessions')
            ->where('day_of_week', 0)
            ->update(['day_of_week' => 7]);
    }
};
