<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to check if an index exists on a table
     */
    private function indexExists($table, $index)
    {
        // For SQLite, use PRAGMA to check indexes
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$table})");
            return collect($indexes)->contains('name', $index);
        }
        
        // For MySQL/PostgreSQL, use information_schema
        $dbName = DB::getDatabaseName();
        $result = DB::select("SELECT COUNT(1) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?", [$dbName, $table, $index]);
        return $result[0]->count > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to workout_sessions table
        Schema::table('workout_sessions', function (Blueprint $table) {
            if (!(new self)->indexExists('workout_sessions', 'ws_uid_date_idx')) {
                $table->index(['user_id', 'date'], 'ws_uid_date_idx');
            }
            if (!(new self)->indexExists('workout_sessions', 'ws_wpid_date_idx')) {
                $table->index(['workout_plan_id', 'date'], 'ws_wpid_date_idx');
            }
            if (!(new self)->indexExists('workout_sessions', 'ws_uid_status_idx')) {
                $table->index(['user_id', 'status'], 'ws_uid_status_idx');
            }
        });

        // Add indexes to workout_plans table
        Schema::table('workout_plans', function (Blueprint $table) {
            if (!(new self)->indexExists('workout_plans', 'wp_uid_active_idx')) {
                $table->index(['user_id', 'is_active'], 'wp_uid_active_idx');
            }
            if (!(new self)->indexExists('workout_plans', 'wp_uid_created_idx')) {
                $table->index(['user_id', 'created_at'], 'wp_uid_created_idx');
            }
        });

        // Add indexes to exercise_sets table
        Schema::table('exercise_sets', function (Blueprint $table) {
            if (!(new self)->indexExists('exercise_sets', 'es_eid_created_idx')) {
                $table->index(['exercise_id', 'created_at'], 'es_eid_created_idx');
            }
            if (!(new self)->indexExists('exercise_sets', 'es_wsid_eid_idx')) {
                $table->index(['workout_session_id', 'exercise_id'], 'es_wsid_eid_idx');
            }
        });

        // Add indexes to workout_plan_schedule table
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            if (!(new self)->indexExists('workout_plan_schedule', 'wps_wpid_wn_dow_idx')) {
                $table->index(['workout_plan_id', 'week_number', 'day_of_week'], 'wps_wpid_wn_dow_idx');
            }
            if (!(new self)->indexExists('workout_plan_schedule', 'wps_eid_wn_idx')) {
                $table->index(['exercise_id', 'week_number'], 'wps_eid_wn_idx');
            }
        });

        // Add indexes to exercises table
        Schema::table('exercises', function (Blueprint $table) {
            if (!(new self)->indexExists('exercises', 'ex_category_idx')) {
                $table->index(['category'], 'ex_category_idx');
            }
            if (!(new self)->indexExists('exercises', 'ex_equipment_idx')) {
                $table->index(['equipment'], 'ex_equipment_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from workout_sessions table
        Schema::table('workout_sessions', function (Blueprint $table) {
            if ((new self)->indexExists('workout_sessions', 'ws_uid_date_idx')) {
                $table->dropIndex('ws_uid_date_idx');
            }
            if ((new self)->indexExists('workout_sessions', 'ws_wpid_date_idx')) {
                $table->dropIndex('ws_wpid_date_idx');
            }
            if ((new self)->indexExists('workout_sessions', 'ws_uid_status_idx')) {
                $table->dropIndex('ws_uid_status_idx');
            }
        });

        // Remove indexes from workout_plans table
        Schema::table('workout_plans', function (Blueprint $table) {
            if ((new self)->indexExists('workout_plans', 'wp_uid_active_idx')) {
                $table->dropIndex('wp_uid_active_idx');
            }
            if ((new self)->indexExists('workout_plans', 'wp_uid_created_idx')) {
                $table->dropIndex('wp_uid_created_idx');
            }
        });

        // Remove indexes from exercise_sets table
        Schema::table('exercise_sets', function (Blueprint $table) {
            if ((new self)->indexExists('exercise_sets', 'es_eid_created_idx')) {
                $table->dropIndex('es_eid_created_idx');
            }
            if ((new self)->indexExists('exercise_sets', 'es_wsid_eid_idx')) {
                $table->dropIndex('es_wsid_eid_idx');
            }
        });

        // Remove indexes from workout_plan_schedule table
        Schema::table('workout_plan_schedule', function (Blueprint $table) {
            if ((new self)->indexExists('workout_plan_schedule', 'wps_wpid_wn_dow_idx')) {
                $table->dropIndex('wps_wpid_wn_dow_idx');
            }
            if ((new self)->indexExists('workout_plan_schedule', 'wps_eid_wn_idx')) {
                $table->dropIndex('wps_eid_wn_idx');
            }
        });

        // Remove indexes from exercises table
        Schema::table('exercises', function (Blueprint $table) {
            if ((new self)->indexExists('exercises', 'ex_category_idx')) {
                $table->dropIndex('ex_category_idx');
            }
            if ((new self)->indexExists('exercises', 'ex_equipment_idx')) {
                $table->dropIndex('ex_equipment_idx');
            }
        });
    }
};
