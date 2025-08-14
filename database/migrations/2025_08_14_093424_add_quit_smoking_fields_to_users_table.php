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
        Schema::table('users', function (Blueprint $table) {
            $table->date('quit_date')->nullable()->after('weight_unit_preference');
            $table->decimal('pack_price', 8, 2)->default(10.00)->after('quit_date');
            $table->integer('cigarettes_per_pack')->default(20)->after('pack_price');
            $table->integer('max_cigarettes_per_day')->default(0)->after('cigarettes_per_pack');
            $table->boolean('enable_reduction_plan')->default(false)->after('max_cigarettes_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'quit_date',
                'pack_price',
                'cigarettes_per_pack',
                'max_cigarettes_per_day',
                'enable_reduction_plan'
            ]);
        });
    }
};
