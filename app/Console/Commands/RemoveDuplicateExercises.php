<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateExercises extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exercises:remove-duplicates {--dry-run : Show what would be deleted without actually deleting} {--force : Force deletion even if references exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate exercises from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for duplicate exercises...');

        // Get all duplicate names
        $duplicates = Exercise::selectRaw('name, COUNT(*) as duplicate_count')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate exercises found.');
            return 0;
        }

        $this->info("Found {$duplicates->count()} duplicate exercise names:");
        $duplicates->each(function ($dup) {
            $this->line("- {$dup->name} (appears {$dup->duplicate_count} times)");
        });

        if ($this->option('dry-run')) {
            $this->info("\nDRY RUN - No changes will be made.");
        } else {
            if (!$this->confirm('Do you want to proceed with removing duplicates?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $deletedCount = 0;
        $keptCount = 0;
        $updatedReferencesCount = 0;

        foreach ($duplicates as $duplicate) {
            $exercises = Exercise::where('name', $duplicate->name)->get();
            
            $this->info("\nProcessing: {$duplicate->name}");
            
            // Determine which exercise to keep based on priority
            $exerciseToKeep = $this->selectExerciseToKeep($exercises);
            $exercisesToDelete = $exercises->where('id', '!=', $exerciseToKeep->id);
            
            $this->line("Keeping: ID {$exerciseToKeep->id} (Category: {$exerciseToKeep->category}, Equipment: {$exerciseToKeep->equipment})");
            
            foreach ($exercisesToDelete as $exercise) {
                $this->line("Deleting: ID {$exercise->id} (Category: {$exercise->category}, Equipment: {$exercise->equipment})");
                
                if (!$this->option('dry-run')) {
                    // Check if this exercise is referenced in workout plans or sessions
                    $hasReferences = $this->checkExerciseReferences($exercise->id);
                    
                    if ($hasReferences) {
                        if ($this->option('force')) {
                            $this->warn("  Exercise ID {$exercise->id} has references. Updating references to point to ID {$exerciseToKeep->id}...");
                            $updated = $this->updateExerciseReferences($exercise->id, $exerciseToKeep->id);
                            $updatedReferencesCount += $updated;
                            $this->line("  Updated {$updated} references.");
                        } else {
                            $this->warn("  Warning: Exercise ID {$exercise->id} has references in workout data. Skipping deletion.");
                            $this->line("  Use --force option to update references and delete anyway.");
                            continue;
                        }
                    }
                    
                    $exercise->delete();
                }
                $deletedCount++;
            }
            $keptCount++;
        }

        if ($this->option('dry-run')) {
            $this->info("\nDRY RUN SUMMARY:");
            $this->info("- Would keep: {$keptCount} exercises");
            $this->info("- Would delete: {$deletedCount} exercises");
        } else {
            $this->info("\nSUMMARY:");
            $this->info("- Kept: {$keptCount} exercises");
            $this->info("- Deleted: {$deletedCount} exercises");
            if ($updatedReferencesCount > 0) {
                $this->info("- Updated references: {$updatedReferencesCount}");
            }
        }

        return 0;
    }

    /**
     * Select which exercise to keep based on priority rules
     */
    private function selectExerciseToKeep($exercises)
    {
        // Priority order for categories (more specific categories first)
        $categoryPriority = [
            'chest' => 1,
            'back' => 2,
            'legs' => 3,
            'shoulders' => 4,
            'arms' => 5,
            'core' => 6,
            'full_body' => 7,
            'strength' => 8,
            'other' => 9,
        ];

        // Priority order for equipment (more specific equipment first)
        $equipmentPriority = [
            'barbell' => 1,
            'dumbbells' => 2,
            'dumbbell' => 2,
            'machine' => 3,
            'cable_pulley' => 4,
            'cables' => 4,
            'kettlebell' => 5,
            'bodyweight' => 6,
            'other' => 7,
        ];

        $bestExercise = $exercises->first();
        $bestScore = PHP_INT_MAX;

        foreach ($exercises as $exercise) {
            $categoryScore = $categoryPriority[$exercise->category] ?? 999;
            $equipmentScore = $equipmentPriority[$exercise->equipment] ?? 999;
            $totalScore = $categoryScore + $equipmentScore;

            // Prefer exercises with created_at timestamp (more recent data)
            if ($exercise->created_at) {
                $totalScore -= 100; // Bonus for having timestamp
            }

            if ($totalScore < $bestScore) {
                $bestScore = $totalScore;
                $bestExercise = $exercise;
            }
        }

        return $bestExercise;
    }

    /**
     * Check if an exercise has references in workout data
     */
    private function checkExerciseReferences($exerciseId)
    {
        // Check workout_plan_schedule table
        $scheduleCount = DB::table('workout_plan_schedule')
            ->where('exercise_id', $exerciseId)
            ->count();

        // Check exercise_sets table
        $setsCount = DB::table('exercise_sets')
            ->where('exercise_id', $exerciseId)
            ->count();

        // Check workout_plan_exercises table
        $planExercisesCount = DB::table('workout_plan_exercises')
            ->where('exercise_id', $exerciseId)
            ->count();

        return $scheduleCount > 0 || $setsCount > 0 || $planExercisesCount > 0;
    }

    /**
     * Update all references from old exercise ID to new exercise ID
     */
    private function updateExerciseReferences($oldExerciseId, $newExerciseId)
    {
        $updatedCount = 0;

        // Update workout_plan_schedule table
        $scheduleUpdated = DB::table('workout_plan_schedule')
            ->where('exercise_id', $oldExerciseId)
            ->update(['exercise_id' => $newExerciseId]);
        $updatedCount += $scheduleUpdated;

        // Update exercise_sets table
        $setsUpdated = DB::table('exercise_sets')
            ->where('exercise_id', $oldExerciseId)
            ->update(['exercise_id' => $newExerciseId]);
        $updatedCount += $setsUpdated;

        // Update workout_plan_exercises table
        $planExercisesUpdated = DB::table('workout_plan_exercises')
            ->where('exercise_id', $oldExerciseId)
            ->update(['exercise_id' => $newExerciseId]);
        $updatedCount += $planExercisesUpdated;

        return $updatedCount;
    }
} 