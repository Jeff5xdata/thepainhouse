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
            $table->integer('set_number')->nullable()->after('order_in_day');
            $table->decimal('weight', 8, 2)->nullable()->after('reps');
            $table->text('notes')->nullable()->after('weight');
            $table->decimal('warmup_weight', 8, 2)->nullable()->after('warmup_weight_percentage');
            $table->text('warmup_notes')->nullable()->after('warmup_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            $table->dropColumn(['set_number', 'weight', 'notes', 'warmup_weight', 'warmup_notes']);
        });
    }
};
