# Workout Backup & Restore Feature

This feature allows users to backup and restore their workout data, including workout plans, sessions, exercise history, settings, food tracking, body measurements, and trainer-client relationships.

## Features

### Backup Functionality

-   **Complete Data Export**: Exports all user workout data including:
    -   Workout plans with exercises and schedules
    -   Workout sessions and exercise sets
    -   Workout settings and preferences
    -   **Food tracker data** (food logs and food items)
    -   **Body tracking data** (weight and body measurements)
    -   **Trainer information** (if user has a trainer)
    -   **Client data** (if user is a trainer)
-   **JSON Format**: Data is exported in a readable JSON format
-   **Timestamped Files**: Backup files include timestamps for easy identification
-   **Secure**: Only exports the current user's data
-   **Version 1.1**: Enhanced backup format with comprehensive data coverage

### Restore Functionality

-   **Flexible Restore Options**: Choose what to restore:
    -   Workout settings
    -   Workout plans
    -   Workout history (sessions and exercise sets)
    -   **Food tracker data** (nutrition logs and food items)
    -   **Body tracking data** (weight and measurement history)
    -   **Client data** (for trainers only)
-   **Conflict Resolution**: Option to overwrite existing plans or skip them
-   **Data Validation**: Validates backup file format before restoration
-   **Transaction Safety**: Uses database transactions to ensure data integrity
-   **Smart Data Mapping**: Handles relationships between different data types

## How to Use

### Creating a Backup

1. Navigate to **Workout Settings** in the application
2. Click on the **"Backup & Restore"** tab
3. Click the **"Download Backup"** button
4. The backup file will be automatically downloaded to your device

### Restoring a Backup

1. Navigate to **Workout Settings** in the application
2. Click on the **"Backup & Restore"** tab
3. Click **"Choose File"** and select your backup file
4. Click **"Preview & Restore"** to see backup details
5. Configure restore options:
    - **Overwrite existing workout plans**: Replace existing plans with the same name
    - **Include workout settings**: Restore your workout preferences
    - **Include workout history**: Restore past workout sessions and exercise data
    - **Include food tracker data**: Restore nutrition logs and food items
    - **Include body tracking data**: Restore weight and body measurements
    - **Include client data**: Restore client information (trainers only)
6. Click **"Restore Backup"** to complete the restoration

## Backup File Format

The backup file is a JSON file with the following structure:

```json
{
  "version": "1.1",
  "created_at": "2024-01-01T12:00:00.000000Z",
  "user": {
    "name": "User Name",
    "email": "user@example.com",
    "is_trainer": false,
    "has_trainer": true
  },
  "workout_plans": [...],
  "workout_sessions": [...],
  "exercise_sets": [...],
  "workout_settings": {...},
  "food_tracker": {
    "food_logs": [...],
    "food_items": [...]
  },
  "body_tracking": {
    "weight_measurements": [...],
    "body_measurements": [...]
  },
  "trainer_data": {
    "trainer_info": {
      "id": 123,
      "name": "Trainer Name",
      "email": "trainer@example.com"
    }
  },
  "client_data": [
    {
      "client_info": {
        "id": 456,
        "name": "Client Name",
        "email": "client@example.com"
      },
      "workout_plans": [...],
      "food_tracker": {...},
      "body_tracking": {...}
    }
  ]
}
```

## Data Types Included

### Core Workout Data
- **Workout Plans**: Complete workout plans with exercises and schedules
- **Workout Sessions**: Individual workout session records
- **Exercise Sets**: Detailed set information (reps, weight, time, notes)
- **Workout Settings**: User preferences and defaults

### Food Tracking Data
- **Food Items**: Nutritional information for food products
- **Food Logs**: Daily food consumption records with meal types
- **Nutrition Data**: Calories, protein, carbs, fat, and other nutrients

### Body Tracking Data
- **Weight Measurements**: Weight tracking with units (kg/lbs) and notes
- **Body Measurements**: Comprehensive body measurements including:
  - Chest, waist, hips, biceps, forearms, thighs, calves
  - Neck, shoulders, height, body fat percentage, muscle mass
- **BMI Calculations**: Automatic BMI calculations based on height and weight

### Trainer-Client Data
- **Trainer Information**: Details about the user's trainer (if applicable)
- **Client Data**: Complete client information for trainers including:
  - Client profiles and workout plans
  - Client food tracking and body measurements
  - Client workout history and progress

## Security Considerations

-   Backup files contain sensitive workout and health data
-   Store backup files securely and privately
-   Only restore backup files from trusted sources
-   Backup files are automatically deleted from the server after download
-   Client data is only included when the user is a trainer

## Technical Details

### Files Created/Modified

**New Files:**

-   `app/Livewire/WorkoutBackup.php` - Main backup/restore component
-   `app/Http/Controllers/WorkoutBackupController.php` - Handles backup downloads
-   `resources/views/livewire/workout-backup.blade.php` - Backup UI
-   `BACKUP_RESTORE.md` - This documentation

**Modified Files:**

-   `resources/views/livewire/workout-settings.blade.php` - Added backup tab
-   `routes/web.php` - Added backup routes
-   `resources/js/app.js` - Added notification system

### Database Tables Involved

-   `workout_plans` - Workout plan data
-   `workout_plan_schedule` - Exercise schedules
-   `workout_sessions` - Workout session data
-   `exercise_sets` - Individual exercise set data
-   `workout_settings` - User preferences
-   `food_items` - Food product information
-   `food_logs` - User food consumption records
-   `weight_measurements` - Weight tracking data
-   `body_measurements` - Body measurement data
-   `users` - User profiles and trainer relationships

### Error Handling

The system includes comprehensive error handling:

-   File format validation
-   Database transaction rollback on errors
-   User-friendly error messages
-   Notification system for success/error feedback
-   Graceful handling of missing or corrupted data
-   Logging for debugging and troubleshooting

## Troubleshooting

### Common Issues

1. **"Invalid backup file format"**

    - Ensure the file is a valid JSON backup file
    - Check that the file wasn't corrupted during download
    - Verify the backup file version is compatible (1.0 or 1.1)

2. **"Failed to restore backup"**

    - Check that all required exercises exist in the system
    - Ensure you have sufficient database permissions
    - Verify the backup file is complete and not corrupted
    - Check the application logs for detailed error information

3. **"Exercise not found"**
    - Some exercises in the backup may not exist in the current system
    - The system will skip missing exercises and continue with restoration
    - Consider updating your exercise database before restoration

4. **"Client data restoration failed"**
    - Ensure you have trainer permissions to restore client data
    - Check that client email addresses are valid and unique
    - Verify that all required client data is present in the backup

### Performance Considerations

-   Large backup files may take longer to process
-   Client data restoration can be resource-intensive for trainers with many clients
-   Consider breaking large backups into smaller, focused backups
-   Monitor database performance during large restorations

## Version History

### Version 1.1 (Current)
- Added food tracker data backup and restore
- Added body tracking data backup and restore
- Added trainer-client relationship support
- Enhanced backup preview with data type information
- Improved restore options and user interface
- Better error handling and data validation

### Version 1.0 (Legacy)
- Basic workout plan and session backup
- Exercise set and settings restoration
- Simple backup and restore functionality

## Future Enhancements

-   **Incremental Backups**: Only backup changed data since last backup
-   **Cloud Storage**: Automatic backup to cloud storage services
-   **Scheduled Backups**: Automatic backup creation on schedule
-   **Backup Encryption**: Enhanced security for sensitive data
-   **Cross-Platform Sync**: Backup sharing between different devices
-   **Backup Analytics**: Insights into backup usage and data patterns
