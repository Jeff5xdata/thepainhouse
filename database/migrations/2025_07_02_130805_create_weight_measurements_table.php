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
        Schema::create('weight_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 5, 2); // Weight in kg/lbs (supports up to 999.99)
            $table->enum('unit', ['kg', 'lbs'])->default('kg');
            $table->date('measurement_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index for efficient queries
            $table->index(['user_id', 'measurement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_measurements');
    }
};
