# Chomp API Setup

## Current Status

The search functionality now uses **only live API data** from the Chomp API. No mock data is used.

## Requirements

**You MUST have a valid API key and user ID** for the search functionality to work. Without valid credentials, searches will return empty results.

## How to Get Valid API Credentials

1. **Sign up for Chomp API**:

    - Go to https://chompthis.com/api/
    - Create an account
    - Choose a plan (they have free and paid options)

2. **Get your API credentials**:

    - After signing up, you'll receive an API key and user ID
    - Copy both the API key and user ID

3. **Update your environment**:

    - Open your `.env` file
    - Update both credential values:

    ```
    CHOMP_API_KEY=your_new_api_key_here
    CHOMP_API_USER=your_user_id_here
    ```

4. **Test the API**:
    - Clear your Laravel cache: `php artisan cache:clear`
    - Test the search functionality

## API Endpoints

The service uses these Chomp API v2 endpoints with premium server authentication:

-   **Name Search**: `https://chompthis.com/api/v2/food/branded/name.php?api_key=API_KEY&name=NAME&user_id=USER_ID`
-   **Barcode Search**: `https://chompthis.com/api/v2/food/branded/barcode.php?api_key=API_KEY&code=CODE&user_id=USER_ID`

## Error Handling

If the API credentials are invalid or missing:

-   Search results will be empty
-   Errors will be logged to `storage/logs/laravel.log`
-   The application will continue to work, but no food data will be returned

## Testing

Once you have valid API credentials:

1. Go to the nutrition page
2. Type any food name in the search field
3. Click the search button or wait for live search results
4. Real food data from the Chomp database will be displayed

## Troubleshooting

If searches return no results:

1. Check that your API key and user ID are valid
2. Verify both credentials are correctly set in your `.env` file
3. Check the Laravel logs for any API errors
4. Test the API directly with curl to verify it's working

## Premium Server Authentication

The Chomp API requires both an API key and user ID for premium server access. This ensures proper authentication and access to the full food database. Make sure both environment variables are configured:

-   `CHOMP_API_KEY`: Your API key from Chomp
-   `CHOMP_API_USER`: Your user ID from Chomp
