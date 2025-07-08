<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorkoutPlanSchedule;

class FixWorkoutPlanOrder extends Command
{
    protected $signature = 'workout:fix-order';
    protected $description = 'Fix order_in_day and set_number for each exercise per day, week, and plan (two-pass to avoid unique constraint collisions)';

    public function handle()
    {
        $schedules = WorkoutPlanSchedule::orderBy('workout_plan_id')
            ->orderBy('week_number')
            ->orderBy('day_of_week')
            ->orderBy('exercise_id')
            ->orderBy('id')
            ->get();

        $grouped = $schedules->groupBy([
            'workout_plan_id',
            'week_number',
            'day_of_week',
            'exercise_id'
        ]);

        $count = 0;
        // First pass: assign temporary high values
        foreach ($grouped as $planId => $weeks) {
            foreach ($weeks as $weekNum => $days) {
                foreach ($days as $day => $exercises) {
                    foreach ($exercises as $exerciseId => $items) {
                        $tempOrder = 1000;
                        foreach ($items as $setIndex => $item) {
                            $item->order_in_day = $tempOrder + $setIndex;
                            $item->set_number = $tempOrder + $setIndex;
                            $item->save();
                        }
                    }
                }
            }
        }
        // Second pass: assign correct sequential values
        foreach ($grouped as $planId => $weeks) {
            foreach ($weeks as $weekNum => $days) {
                foreach ($days as $day => $exercises) {
                    foreach ($exercises as $exerciseId => $items) {
                        $order = 0;
                        foreach ($items as $setIndex => $item) {
                            $item->order_in_day = $order;
                            $item->set_number = $setIndex + 1; // set_number starts from 1
                            $item->save();
                            $order++;
                            $count++;
                        }
                    }
                }
            }
        }

        $this->info("Fixed order_in_day and set_number for {$count} schedule items (two-pass update).\n");
        return 0;
    }
} 