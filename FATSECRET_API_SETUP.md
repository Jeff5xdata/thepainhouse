# FatSecret API Setup

## Current Status

The search functionality now uses **only live API data** from the FatSecret Platform API. No mock data is used.

## Requirements

**You MUST have valid OAuth credentials** for the search functionality to work. Without valid credentials, searches will return empty results.

## How to Get Valid API Credentials

1. **Sign up for FatSecret Platform API**:

    - Go to https://platform.fatsecret.com/
    - Create an account
    - Choose a plan (they have free and paid options)

2. **Get your API credentials**:

    - After signing up, you'll receive a Consumer Key and Consumer Secret
    - You may also need to generate an Access Token for OAuth 2.0 authentication
    - Copy all the credential values

3. **Update your environment**:

    - Open your `.env` file
    - Update the credential values:

    ```
    FATSECRET_CONSUMER_KEY=your_consumer_key_here
    FATSECRET_CONSUMER_SECRET=your_consumer_secret_here
    ```

    **Note**: The application will automatically obtain and manage OAuth 2.0 access tokens using your Consumer Key and Consumer Secret. You don't need to manually set the access token.

4. **Test the API**:
    - Clear your Laravel cache: `php artisan cache:clear`
    - Test the search functionality

## API Endpoints

The service uses these FatSecret Platform API endpoints:

-   **Name Search**: `https://platform.fatsecret.com/rest/server.api` (method=foods.search)
-   **Barcode Search**: `https://platform.fatsecret.com/rest/server.api` (method=food.find_id_for_barcode)
-   **Food Details**: `https://platform.fatsecret.com/rest/server.api` (method=food.get.v4)

## Authentication

The FatSecret API uses OAuth 2.0 Client Credentials flow:

-   **Automatic Token Management**: The service automatically obtains and refreshes access tokens
-   **Client Credentials Flow**: Uses Consumer Key and Consumer Secret to get access tokens
-   **Bearer Token**: Access tokens are used as Bearer tokens in API requests
-   **Token Expiration**: Tokens are automatically refreshed when they expire (24 hours)

The service handles all OAuth 2.0 token management automatically - you only need to provide your Consumer Key and Consumer Secret.

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
4. Real food data from the FatSecret database will be displayed

## Troubleshooting

If searches return no results:

1. Check that your Consumer Key and Consumer Secret are valid
2. Verify all credentials are correctly set in your `.env` file
3. Check the Laravel logs for any API errors
4. Test the API directly with curl to verify it's working
5. Ensure you have the correct permissions for the API endpoints

## API Rate Limiting

The FatSecret API has rate limits that vary by plan:

-   **Free Plan**: Limited requests per day
-   **Paid Plans**: Higher rate limits

The service includes built-in delays to prevent hitting rate limits too quickly.

## Migration from Chomp API

If you're migrating from the Chomp API:

1. **Update Environment Variables**: Replace Chomp credentials with FatSecret credentials
2. **Run Migration**: Execute the migration to rename the database column:
    ```bash
    php artisan migrate
    ```
3. **Clear Cache**: Clear Laravel cache to ensure new configuration is loaded:
    ```bash
    php artisan cache:clear
    ```
4. **Test Functionality**: Verify that food search and barcode scanning work correctly

## Environment Variables

Make sure these environment variables are configured:

-   `FATSECRET_CONSUMER_KEY`: Your Consumer Key from FatSecret
-   `FATSECRET_CONSUMER_SECRET`: Your Consumer Secret from FatSecret
-   `FATSECRET_LOOKUP_DELAY`: Delay between API calls in milliseconds (default: 500)

**Note**: Access tokens are automatically managed by the service and don't need to be manually configured.
