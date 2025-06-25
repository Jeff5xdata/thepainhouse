# Chomp API Setup

## Current Status

The search functionality now uses **only live API data** from the Chomp API. No mock data is used.

## Requirements

**You MUST have a valid API key** for the search functionality to work. Without a valid API key, searches will return empty results.

## How to Get a Valid API Key

1. **Sign up for Chomp API**:

    - Go to https://chompthis.com/api/
    - Create an account
    - Choose a plan (they have free and paid options)

2. **Get your API key**:

    - After signing up, you'll receive an API key
    - Copy the API key

3. **Update your environment**:

    - Open your `.env` file
    - Update the `CHOMP_API_KEY` value:

    ```
    CHOMP_API_KEY=your_new_api_key_here
    ```

4. **Test the API**:
    - Clear your Laravel cache: `php artisan cache:clear`
    - Test the search functionality

## API Endpoints

The service uses these Chomp API v2 endpoints:

-   **Name Search**: `https://chompthis.com/api/v2/food/branded/name.php?api_key=API_KEY&name=NAME`
-   **Barcode Search**: `https://chompthis.com/api/v2/food/branded/barcode.php?api_key=API_KEY&code=CODE`

## Error Handling

If the API key is invalid or missing:

-   Search results will be empty
-   Errors will be logged to `storage/logs/laravel.log`
-   The application will continue to work, but no food data will be returned

## Testing

Once you have a valid API key:

1. Go to the nutrition page
2. Type any food name in the search field
3. Click the search button or wait for live search results
4. Real food data from the Chomp database will be displayed

## Troubleshooting

If searches return no results:

1. Check that your API key is valid
2. Verify the API key is correctly set in your `.env` file
3. Check the Laravel logs for any API errors
4. Test the API directly with curl to verify it's working
