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
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Drop only the columns that still exist in the database
            $table->dropColumn([
                'time_in_seconds',
                'has_warmup'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            // Re-add the columns in case we need to rollback
            $table->integer('sets')->default(3)->after('is_time_based');
            $table->integer('reps')->default(10)->after('sets');
            $table->decimal('weight', 8, 2)->nullable()->after('reps');
            $table->integer('time_in_seconds')->nullable()->after('weight');
            $table->boolean('has_warmup')->default(false)->after('is_time_based');
            $table->boolean('has_warmup')->default(false)->after('notes');
            $table->integer('warmup_sets')->nullable()->after('has_warmup');
            $table->integer('warmup_reps')->nullable()->after('warmup_sets');
            $table->integer('warmup_time_in_seconds')->nullable()->after('warmup_reps');
            $table->integer('warmup_weight_percentage')->nullable()->after('warmup_time_in_seconds');
        });
    }
};
