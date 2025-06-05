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
        Schema::create('workout_plan_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->integer('default_sets')->default(3);
            $table->integer('default_reps')->default(10);
            $table->decimal('default_weight', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('has_warmup')->default(false);
            $table->integer('warmup_sets')->nullable();
            $table->integer('warmup_reps')->nullable();
            $table->integer('warmup_weight_percentage')->nullable();
            $table->timestamps();

            $table->unique(['workout_plan_id', 'exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plan_exercises');
    }
};
