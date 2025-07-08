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
            $indexes = \DB::select("SHOW INDEX FROM food_items WHERE Key_name = 'food_items_barcode_unique'");
            $constraintExists = count($indexes) > 0;
            
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
