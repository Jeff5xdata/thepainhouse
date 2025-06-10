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
        Schema::table('workout_settings', function (Blueprint $table) {
            $table->integer('default_warmup_weight_percentage')->default(50)->after('default_warmup_reps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_settings', function (Blueprint $table) {
            $table->dropColumn('default_warmup_weight_percentage');
        });
    }
}; 