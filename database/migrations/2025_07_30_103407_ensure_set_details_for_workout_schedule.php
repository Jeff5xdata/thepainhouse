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
        // Get all schedule items that don't have set_details or have empty set_details
        $scheduleItems = DB::table('workout_plan_schedule')
            ->whereNull('set_details')
            ->orWhereRaw("JSON_LENGTH(set_details) = 0")
            ->orWhereRaw("set_details = ''")
            ->orWhereRaw("set_details = 'null'")
            ->get();

        foreach ($scheduleItems as $item) {
            $setDetails = [];
            $setNumber = 1;

            // Generate warmup sets if they exist
            if ($item->has_warmup && $item->warmup_sets > 0) {
                for ($i = 1; $i <= $item->warmup_sets; $i++) {
                    $setDetails[] = [
                        'set_number' => $setNumber++,
                        'reps' => $item->warmup_reps ?? 10,
                        'weight' => null,
                        'notes' => "Warmup Set {$i}",
                        'time_in_seconds' => $item->warmup_time_in_seconds ?? null,
                        'is_warmup' => true,
                        'weight_percentage' => $item->warmup_weight_percentage ?? null,
                    ];
                }
            }

            // Generate working sets
            $workingSets = $item->sets ?? 3;
            for ($i = 1; $i <= $workingSets; $i++) {
                $setDetails[] = [
                    'set_number' => $setNumber++,
                    'reps' => $item->reps ?? 10,
                    'weight' => $item->weight ?? null,
                    'notes' => "Work Set {$i}",
                    'time_in_seconds' => $item->time_in_seconds ?? null,
                    'is_warmup' => false,
                ];
            }

            // Update the schedule item with the generated set_details
            DB::table('workout_plan_schedule')
                ->where('id', $item->id)
                ->update([
                    'set_details' => json_encode($setDetails),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it's ensuring data integrity
        // We don't want to remove set_details once they're properly set
    }
};
