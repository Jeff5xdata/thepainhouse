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
        Schema::table('food_items', function (Blueprint $table) {
            // Check if the unique constraint exists before trying to drop it
            $indexes = \DB::select("PRAGMA index_list(food_items)");
            $constraintExists = collect($indexes)->contains('name', 'food_items_barcode_unique');
            
            if ($constraintExists) {
                $table->dropUnique(['barcode']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_items', function (Blueprint $table) {
            $table->unique('barcode');
        });
    }
};
