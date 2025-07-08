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
        // Convert day names to numbers in workout_plan_schedule table
        $dayMapping = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        foreach ($dayMapping as $dayName => $dayNumber) {
            DB::table('workout_plan_schedule')
                ->where('day_of_week', $dayName)
                ->update(['day_of_week' => $dayNumber]);
        }

        // Convert day names to numbers in workout_sessions table
        foreach ($dayMapping as $dayName => $dayNumber) {
            DB::table('workout_sessions')
                ->where('day_of_week', $dayName)
                ->update(['day_of_week' => $dayNumber]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert day numbers back to names in workout_plan_schedule table
        $dayMapping = [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];

        foreach ($dayMapping as $dayNumber => $dayName) {
            DB::table('workout_plan_schedule')
                ->where('day_of_week', $dayNumber)
                ->update(['day_of_week' => $dayName]);
        }

        // Convert day numbers back to names in workout_sessions table
        foreach ($dayMapping as $dayNumber => $dayName) {
            DB::table('workout_sessions')
                ->where('day_of_week', $dayNumber)
                ->update(['day_of_week' => $dayName]);
        }
    }
};
