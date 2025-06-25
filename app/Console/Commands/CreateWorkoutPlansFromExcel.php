<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Models\WorkoutPlanSchedule;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CreateWorkoutPlansFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workout:create-from-excel {file : Path to the Excel file or folder} {--user1= : Email for user 1} {--user2= : Email for user 2} {--jeff=jeff.cook@example.com : Email for Jeff Cook} {--nat=nat@example.com : Email for Nat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create workout plans from Excel file(s) for specified users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $user1Email = $this->option('user1');
        $user2Email = $this->option('user2');
        $jeffEmail = $this->option('jeff');
        $natEmail = $this->option('nat');

        if (!file_exists($filePath)) {
            $this->error("File or folder not found: {$filePath}");
            return 1;
        }

        // Get or create users
        $user1 = $this->getOrCreateUser($user1Email, 'User One');
        $user2 = $this->getOrCreateUser($user2Email, 'User Two');
        $jeff = $this->getOrCreateUser($jeffEmail, 'Jeff Cook');
        $nat = $this->getOrCreateUser($natEmail, 'Nat');

        // Check if it's a directory
        if (is_dir($filePath)) {
            $this->processDirectory($filePath, $user1, $user2, $jeff, $nat);
        } else {
            // Process single file
            $this->processFile($filePath, $user1, $user2, $jeff, $nat);
        }

        $this->info("Workout plans created successfully!");
        return 0;
    }

    private function getOrCreateUser($email, $defaultName)
    {
        if (!$email) {
            $email = strtolower(str_replace(' ', '', $defaultName)) . '@example.com';
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $user = User::create([
                'name' => $defaultName,
                'email' => $email,
                'password' => bcrypt('password123'),
            ]);
            $this->info("Created user: {$user->name} ({$user->email})");
        } else {
            $this->info("Found existing user: {$user->name} ({$user->email})");
        }

        return $user;
    }

    private function processDirectory($directory, $user1, $user2, $jeff, $nat)
    {
        $files = glob($directory . '/*.xlsx');
        
        if (empty($files)) {
            $this->error("No Excel files found in directory: {$directory}");
            return;
        }

        $this->info("Found " . count($files) . " Excel files in directory");

        foreach ($files as $file) {
            $this->info("Processing file: " . basename($file));
            $this->processFile($file, $user1, $user2, $jeff, $nat);
        }
    }

    private function processFile($filePath, $user1, $user2, $jeff, $nat)
    {
        // Load the Excel file
        try {
            $spreadsheet = IOFactory::load($filePath);
            $this->info("Successfully loaded Excel file: {$filePath}");
        } catch (\Exception $e) {
            $this->error("Error loading Excel file: " . $e->getMessage());
            return;
        }

        // Get filename for user assignment
        $filename = basename($filePath, '.xlsx');

        // Process each worksheet
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $this->info("Processing worksheet: " . $worksheet->getTitle());
            $this->processWorksheet($worksheet, $user1, $user2, $jeff, $nat, $filename);
        }
    }

    private function processWorksheet($worksheet, $user1, $user2, $jeff, $nat, $filename = null)
    {
        $worksheetName = $worksheet->getTitle();
        
        // Determine which user this worksheet is for based on the worksheet name and content
        $targetUser = $this->determineTargetUser($worksheetName, $worksheet, $user1, $user2, $jeff, $nat, $filename);

        // Create workout plan
        $workoutPlan = WorkoutPlan::create([
            'name' => "Plan from {$worksheetName} ({$filename})",
            'description' => "Workout plan created from Excel worksheet: {$worksheetName} from file: {$filename}",
            'weeks_duration' => 4, // Default to 4 weeks
            'user_id' => $targetUser->id,
            'is_active' => true,
        ]);

        $this->info("Created workout plan: {$workoutPlan->name} for {$targetUser->name}");

        // Parse the worksheet data
        $this->parseWorksheetData($worksheet, $workoutPlan);
    }

    private function determineTargetUser($worksheetName, $worksheet, $user1, $user2, $jeff, $nat, $filename = null)
    {
        // Check worksheet name for user indicators
        if (stripos($worksheetName, 'user 1') !== false || stripos($worksheetName, 'user1') !== false) {
            return $user1;
        } elseif (stripos($worksheetName, 'user 2') !== false || stripos($worksheetName, 'user2') !== false) {
            return $user2;
        } elseif (stripos($worksheetName, 'jeff') !== false || stripos($worksheetName, 'cook') !== false) {
            return $jeff;
        } elseif (stripos($worksheetName, 'nat') !== false) {
            return $nat;
        }

        // Check filename for week indicators
        if ($filename && preg_match('/week\s*(\d+)/i', $filename, $matches)) {
            $weekNumber = (int)$matches[1];
            return ($weekNumber % 2 == 1) ? $jeff : $nat; // Odd weeks to Jeff, even weeks to Nat
        }

        // Check content for user indicators
        $content = $this->getWorksheetContent($worksheet);
        
        if (stripos($content, 'jeff') !== false || stripos($content, 'cook') !== false) {
            return $jeff;
        } elseif (stripos($content, 'nat') !== false) {
            return $nat;
        }

        // Default distribution: alternate between Jeff and Nat for week files
        if (preg_match('/week\s*(\d+)/i', $worksheetName, $matches)) {
            $weekNumber = (int)$matches[1];
            return ($weekNumber % 2 == 1) ? $jeff : $nat; // Odd weeks to Jeff, even weeks to Nat
        }

        // Default to Jeff if no clear indication
        return $jeff;
    }

    private function getWorksheetContent($worksheet)
    {
        $content = '';
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        // Get first few rows to check for user indicators
        for ($row = 1; $row <= min(10, $highestRow); $row++) {
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . $row)->getValue();
                if ($cellValue) {
                    $content .= ' ' . $cellValue;
                }
            }
        }
        
        return strtolower($content);
    }

    private function parseWorksheetData($worksheet, $workoutPlan)
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        $this->info("Worksheet has {$highestRow} rows and {$highestColumn} columns");

        // Get all exercises for reference
        $exercises = Exercise::all()->keyBy('name');

        $currentWeek = 1;
        $currentDay = 'monday';
        $orderInDay = 0;

        // Process each row
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            
            // Get all cell values in the row
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cellValue = $worksheet->getCell($col . $row)->getValue();
                $rowData[$col] = $cellValue;
            }

            // Skip empty rows
            if (empty(array_filter($rowData))) {
                continue;
            }

            // Try to identify the structure based on cell content
            $this->processRow($rowData, $workoutPlan, $exercises, $currentWeek, $currentDay, $orderInDay);
        }
    }

    private function processRow($rowData, $workoutPlan, $exercises, &$currentWeek, &$currentDay, &$orderInDay)
    {
        // Look for exercise names in the row
        $exerciseName = null;
        $sets = 3; // Default
        $reps = 10; // Default
        $weight = null;
        $timeInSeconds = null;
        $isTimeBased = false;
        $notes = null;

        foreach ($rowData as $col => $value) {
            if (empty($value)) continue;

            $value = trim($value);
            
            // Check if this looks like an exercise name
            if ($this->isExerciseName($value)) {
                $exerciseName = $value;
            }
            
            // Check for sets (e.g., "3 sets", "3x", etc.)
            if (preg_match('/(\d+)\s*sets?/i', $value, $matches)) {
                $sets = (int)$matches[1];
            }
            
            // Check for reps (e.g., "10 reps", "10x", etc.)
            if (preg_match('/(\d+)\s*reps?/i', $value, $matches)) {
                $reps = (int)$matches[1];
            }
            
            // Check for weight (e.g., "100 lbs", "100kg", etc.)
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:lbs?|kg)/i', $value, $matches)) {
                $weight = (float)$matches[1];
            }
            
            // Check for time (e.g., "30 seconds", "30s", etc.)
            if (preg_match('/(\d+)\s*(?:seconds?|s)/i', $value, $matches)) {
                $timeInSeconds = (int)$matches[1];
                $isTimeBased = true;
            }
            
            // Check for week indicators
            if (preg_match('/week\s*(\d+)/i', $value, $matches)) {
                $currentWeek = (int)$matches[1];
                $orderInDay = 0;
            }
            
            // Check for day indicators
            $dayPatterns = [
                'monday' => '/monday/i',
                'tuesday' => '/tuesday/i',
                'wednesday' => '/wednesday/i',
                'thursday' => '/thursday/i',
                'friday' => '/friday/i',
                'saturday' => '/saturday/i',
                'sunday' => '/sunday/i',
            ];
            
            foreach ($dayPatterns as $day => $pattern) {
                if (preg_match($pattern, $value)) {
                    $currentDay = $day;
                    $orderInDay = 0;
                    break;
                }
            }
        }

        // If we found an exercise, create the schedule entry
        if ($exerciseName) {
            // Try to find the exercise in our database
            $exercise = $exercises->get($exerciseName);
            
            if (!$exercise) {
                // Create the exercise if it doesn't exist
                $exercise = Exercise::create([
                    'name' => $exerciseName,
                    'description' => "Exercise from Excel import",
                    'category' => 'strength',
                    'equipment' => 'bodyweight',
                ]);
                $this->info("Created new exercise: {$exerciseName}");
            }

            // Create the workout plan schedule entry
            WorkoutPlanSchedule::create([
                'workout_plan_id' => $workoutPlan->id,
                'exercise_id' => $exercise->id,
                'week_number' => $currentWeek,
                'day_of_week' => $currentDay,
                'order_in_day' => $orderInDay++,
                'is_time_based' => $isTimeBased,
                'sets' => $sets,
                'reps' => $isTimeBased ? 1 : $reps,
                'weight' => $weight,
                'time_in_seconds' => $timeInSeconds,
                'notes' => $notes,
            ]);

            $this->info("Added: {$exerciseName} - Week {$currentWeek}, {$currentDay} - {$sets} sets x {$reps} reps");
        }
    }

    private function isExerciseName($value)
    {
        // Common exercise patterns
        $exercisePatterns = [
            '/push.?up/i',
            '/pull.?up/i',
            '/squat/i',
            '/deadlift/i',
            '/bench.?press/i',
            '/plank/i',
            '/lunge/i',
            '/burpee/i',
            '/mountain.?climber/i',
            '/jumping.?jack/i',
            '/sit.?up/i',
            '/crunch/i',
            '/press/i',
            '/curl/i',
            '/row/i',
            '/fly/i',
            '/extension/i',
            '/flexion/i',
            '/raise/i',
            '/dip/i',
        ];

        foreach ($exercisePatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        // Check if it's a reasonable length for an exercise name (2-50 characters)
        if (strlen($value) >= 2 && strlen($value) <= 50) {
            // Check if it contains mostly letters and spaces
            if (preg_match('/^[a-zA-Z\s\-]+$/', $value)) {
                return true;
            }
        }

        return false;
    }
} 