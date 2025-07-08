<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the table exists and has data
        $hasData = false;
        if (Schema::hasTable('workout_plan_schedule')) {
            $count = DB::table('workout_plan_schedule')->count();
            $hasData = $count > 0;
        }

        if ($hasData) {
            // Create a backup of the current table
            Schema::create('workout_plan_schedule_backup', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workout_plan_id')->constrained()->onDelete('cascade');
                $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
                $table->integer('week_number')->default(1);
                $table->string('day_of_week');
                $table->integer('order_in_day')->default(0);
                $table->text('notes')->nullable();
                $table->boolean('complete')->default(false);
                $table->timestamps();
            });

            // Copy existing data to backup
            DB::statement('INSERT INTO workout_plan_schedule_backup SELECT * FROM workout_plan_schedule');
        }

        // Drop the existing table
        Schema::dropIfExists('workout_plan_schedule');

        // Create the new simplified structure
        Schema::create('workout_plan_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('cascade');
            $table->integer('week_number')->default(1);
            $table->string('day_of_week');
            $table->integer('order_in_day')->default(0);
            $table->json('set_details')->nullable(); // Store individual set details as JSON
            $table->timestamps();

            // Add unique constraint for exercise order within a day
            $table->unique(['workout_plan_id', 'week_number', 'day_of_week', 'order_in_day'], 'unique_exercise_order');
            
            // Add indexes for performance
            $table->index(['workout_plan_id', 'week_number', 'day_of_week'], 'wps_wpid_wn_dow_idx');
            $table->index(['exercise_id'], 'wps_exercise_id_idx');
        });

        // Migrate data from backup to new structure if we had data
        if ($hasData) {
            $this->migrateDataFromBackup();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new table
        Schema::dropIfExists('workout_plan_schedule');

        // Restore the backup table if it exists
        if (Schema::hasTable('workout_plan_schedule_backup')) {
            Schema::rename('workout_plan_schedule_backup', 'workout_plan_schedule');
        }
    }

    /**
     * Migrate data from the backup table to the new structure
     */
    private function migrateDataFromBackup(): void
    {
        // Get all unique exercise configurations from backup
        $uniqueExercises = DB::table('workout_plan_schedule_backup')
            ->select([
                'workout_plan_id',
                'exercise_id',
                'week_number',
                'day_of_week',
                'order_in_day',
            ])
            ->groupBy([
                'workout_plan_id',
                'exercise_id',
                'week_number',
                'day_of_week',
                'order_in_day'
            ])
            ->get();

        foreach ($uniqueExercises as $exercise) {
            // Get all set details for this exercise
            $setDetails = DB::table('workout_plan_schedule_backup')
                ->where('workout_plan_id', $exercise->workout_plan_id)
                ->where('exercise_id', $exercise->exercise_id)
                ->where('week_number', $exercise->week_number)
                ->where('day_of_week', $exercise->day_of_week)
                ->where('order_in_day', $exercise->order_in_day)
                ->orderBy('set_number')
                ->get()
                ->map(function ($set) {
                    return [
                        'set_number' => $set->set_number,
                        'reps' => $set->reps,
                        'weight' => $set->weight,
                        'notes' => $set->notes,
                        'time_in_seconds' => $set->time_in_seconds,
                        'is_warmup' => $set->has_warmup && $set->set_number <= $set->warmup_sets,
                    ];
                })
                ->toArray();

            // Insert into new table
            DB::table('workout_plan_schedule')->insert([
                'workout_plan_id' => $exercise->workout_plan_id,
                'exercise_id' => $exercise->exercise_id,
                'week_number' => $exercise->week_number,
                'day_of_week' => $exercise->day_of_week,
                'order_in_day' => $exercise->order_in_day,
                'set_details' => !empty($setDetails) ? json_encode($setDetails) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}; 