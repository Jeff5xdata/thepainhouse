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
        Schema::create('body_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('measurement_date');
            
            // Body measurements (in cm)
            $table->decimal('chest', 5, 1)->nullable();
            $table->decimal('waist', 5, 1)->nullable();
            $table->decimal('hips', 5, 1)->nullable();
            $table->decimal('biceps', 5, 1)->nullable();
            $table->decimal('forearms', 5, 1)->nullable();
            $table->decimal('thighs', 5, 1)->nullable();
            $table->decimal('calves', 5, 1)->nullable();
            $table->decimal('neck', 5, 1)->nullable();
            $table->decimal('shoulders', 5, 1)->nullable();
            $table->decimal('body_fat_percentage', 4, 1)->nullable(); // Body fat percentage
            $table->decimal('muscle_mass', 5, 1)->nullable(); // Muscle mass in kg
            $table->decimal('height', 5, 1)->nullable(); // Height in cm
            
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
        Schema::dropIfExists('body_measurements');
    }
};
