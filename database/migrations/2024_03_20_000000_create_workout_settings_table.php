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
        Schema::create('workout_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('default_rest_timer')->default(60);
            $table->integer('default_warmup_sets')->default(2);
            $table->integer('default_warmup_reps')->default(10);
            $table->integer('default_work_sets')->default(3);
            $table->integer('default_work_reps')->default(10);
            $table->timestamps();

            // Add unique constraint to ensure one settings record per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_settings');
    }
}; 