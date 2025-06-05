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
        Schema::create('exercise_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->integer('set_number');
            $table->integer('reps')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('is_warmup')->default(false);
            $table->integer('time_in_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Add index for common queries
            $table->index(['workout_session_id', 'exercise_id', 'set_number']);
            $table->index(['workout_session_id', 'is_warmup']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_sets');
    }
};
