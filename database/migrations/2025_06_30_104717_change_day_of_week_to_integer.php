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
        // Use Laravel's schema builder for database-agnostic column modification
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            $table->integer('day_of_week')->change();
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->integer('day_of_week')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change day_of_week column type back to string
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            $table->string('day_of_week')->change();
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->string('day_of_week')->change();
        });
    }
};
