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
        Schema::table('exercise_sets', function (Blueprint $table) {
            if (!Schema::hasColumn('exercise_sets', 'is_warmup')) {
                $table->boolean('is_warmup')->default(false)->after('set_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_sets', function (Blueprint $table) {
            if (Schema::hasColumn('exercise_sets', 'is_warmup')) {
                $table->dropColumn('is_warmup');
            }
        });
    }
}; 