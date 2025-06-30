# Workout Backup & Restore Feature

This feature allows users to backup and restore their workout data, including workout plans, sessions, exercise history, and settings.

## Features

### Backup Functionality

-   **Complete Data Export**: Exports all user workout data including:
    -   Workout plans with exercises and schedules
    -   Workout sessions and exercise sets
    -   Workout settings and preferences
-   **JSON Format**: Data is exported in a readable JSON format
-   **Timestamped Files**: Backup files include timestamps for easy identification
-   **Secure**: Only exports the current user's data

### Restore Functionality

-   **Flexible Restore Options**: Choose what to restore:
    -   Workout settings
    -   Workout plans
    -   Workout history (sessions and exercise sets)
-   **Conflict Resolution**: Option to overwrite existing plans or skip them
-   **Data Validation**: Validates backup file format before restoration
-   **Transaction Safety**: Uses database transactions to ensure data integrity

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
6. Click **"Restore Backup"** to complete the restoration

## Backup File Format

The backup file is a JSON file with the following structure:

```json
{
  "version": "1.0",
  "created_at": "2024-01-01T12:00:00.000000Z",
  "user": {
    "name": "User Name",
    "email": "user@example.com"
  },
  "workout_plans": [...],
  "workout_sessions": [...],
  "exercise_sets": [...],
  "workout_settings": {...}
}
```

## Security Considerations

-   Backup files contain sensitive workout data
-   Store backup files securely
-   Only restore backup files from trusted sources
-   Backup files are automatically deleted from the server after download

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

### Error Handling

The system includes comprehensive error handling:

-   File format validation
-   Database transaction rollback on errors
-   User-friendly error messages
-   Notification system for success/error feedback

## Troubleshooting

### Common Issues

1. **"Invalid backup file format"**

    - Ensure the file is a valid JSON backup file
    - Check that the file wasn't corrupted during download

2. **"Failed to restore backup"**

    - Check that all required exercises exist in the system
    - Ensure you have sufficient database permissions
    - Verify the backup file is complete and not corrupted

3. **"Exercise not found"**
    - Some exercises in the backup may not exist in the current system
    - The system will skip these exercises and continue with the restoration

### Support

If you encounter issues with backup/restore functionality, please:

1. Check the application logs for detailed error messages
2. Verify your backup file format
3. Ensure you have the latest version of the application
