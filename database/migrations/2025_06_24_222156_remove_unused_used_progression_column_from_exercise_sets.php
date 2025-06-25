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
            $table->dropColumn('used_progression');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_sets', function (Blueprint $table) {
            $table->boolean('used_progression')->default(false)->after('notes');
        });
    }
};
