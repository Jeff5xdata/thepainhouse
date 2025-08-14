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
            $table->boolean('enable_smoke_reminders')->default(true)->after('push_subscription');
            $table->boolean('enable_daily_progress')->default(true)->after('enable_smoke_reminders');
            $table->boolean('enable_milestone_celebrations')->default(true)->after('enable_daily_progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'enable_smoke_reminders',
                'enable_daily_progress',
                'enable_milestone_celebrations'
            ]);
        });
    }
};
