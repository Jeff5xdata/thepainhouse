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
        // Check if chomp_id column exists before trying to rename it
        if (Schema::hasColumn('food_items', 'chomp_id')) {
            Schema::table('food_items', function (Blueprint $table) {
                $table->renameColumn('chomp_id', 'fatsecret_id');
            });
        }
        
        // Ensure fatsecret_id column exists
        if (!Schema::hasColumn('food_items', 'fatsecret_id')) {
            Schema::table('food_items', function (Blueprint $table) {
                $table->string('fatsecret_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if fatsecret_id column exists before trying to rename it
        if (Schema::hasColumn('food_items', 'fatsecret_id')) {
            Schema::table('food_items', function (Blueprint $table) {
                $table->renameColumn('fatsecret_id', 'chomp_id');
            });
        }
    }
};
