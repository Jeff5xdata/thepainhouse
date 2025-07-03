# Food Tracker Feature

This feature adds comprehensive food tracking functionality to The Pain House workout application, integrating with the FatSecret Platform API for nutritional information and barcode scanning capabilities.

## Features

-   **Food Search**: Search for food items by name using the FatSecret Platform API
-   **Barcode Scanning**: Scan product barcodes using your phone's camera
-   **Nutrition Tracking**: Track calories, protein, carbs, fat, and other nutrients
-   **Meal Organization**: Organize food items by meal type (breakfast, lunch, dinner, snack)
-   **Daily Summaries**: View daily nutrition totals and breakdowns
-   **Weekly Summaries**: View weekly nutrition totals (Monday-Sunday) with daily breakdown
-   **Food History**: Keep track of what you've eaten with timestamps
-   **View Modes**: Toggle between daily and weekly views

## Setup Instructions

### 1. Environment Configuration

Add the FatSecret API credentials to your `.env` file:

```env
FATSECRET_CONSUMER_KEY=your_consumer_key_here
FATSECRET_CONSUMER_SECRET=your_consumer_secret_here
FATSECRET_ACCESS_TOKEN=your_access_token_here
```

You can get FatSecret API credentials by signing up at [FatSecret Platform API](https://platform.fatsecret.com/).

### 2. Database Migration

The feature creates two new database tables:

-   `food_items`: Stores food product information from FatSecret Platform API
-   `food_logs`: Stores user food consumption records

Run the migrations:

```bash
php artisan migrate
```

### 3. API Configuration

The feature includes API endpoints for food search and barcode scanning:

-   `POST /api/food/search` - Search food by name
-   `POST /api/food/barcode` - Search food by barcode
-   `POST /api/food/store` - Store new food item

These endpoints require authentication via Sanctum.

## Usage

### Accessing the Food Tracker

1. Navigate to the main menu (hamburger icon)
2. Click on "Food Tracker"
3. You'll be taken to the nutrition tracking page

### Adding Food Items

#### Method 1: Search by Name

1. Type a food item name in the search box
2. Select from the search results
3. Choose meal type and quantity
4. Add optional notes
5. Click "Add to Log"

#### Method 2: Scan Barcode

1. Click the barcode scanner icon
2. Allow camera access when prompted
3. Point your camera at a product barcode
4. The food information will be automatically retrieved
5. Adjust meal type and quantity as needed
6. Click "Add to Log"

#### Method 3: Manual Barcode Entry

1. Enter the barcode number manually
2. Click "Search"
3. Follow the same steps as above

### Viewing Your Food Log

#### Daily View

-   **Daily Summary**: See total calories, protein, carbs, and fat for the selected date
-   **Meal Breakdown**: View food items organized by meal type
-   **Date Navigation**: Use the date picker to view different days
-   **Delete Items**: Click the trash icon to remove food items from your log

#### Weekly View

-   **Weekly Summary**: See total calories, protein, carbs, and fat for the entire week (Monday-Sunday)
-   **Daily Breakdown Chart**: Visual representation of daily nutrition totals
-   **Week Navigation**: Navigate between different weeks using arrow buttons
-   **Detailed Weekly Log**: View all food items organized by date and meal type
-   **Data Indicators**: Days with food data are highlighted in green

### Switching Between Views

1. Use the "Daily View" and "Weekly Summary" toggle buttons at the top of the page
2. The interface will automatically adjust to show the appropriate view
3. In weekly view, you can navigate between weeks using the arrow buttons
4. The current week range is displayed (e.g., "Jun 23 - Jun 29, 2025")

### Nutrition Information

The system tracks the following nutritional values:

-   Calories
-   Protein (g)
-   Carbohydrates (g)
-   Fat (g)
-   Fiber (g)
-   Sugar (g)
-   Sodium (mg)
-   Cholesterol (mg)

## Technical Details

### Models

-   `FoodItem`: Stores food product information from Chomp API
-   `FoodLog`: Stores user food consumption records

### Services

-   `FatSecretApiService`: Handles communication with the FatSecret Platform API

### Components

-   `Nutrition` (Livewire): Main food tracking interface
-   `BarcodeScanner` (JavaScript): Handles barcode scanning functionality

### API Endpoints

All food-related API endpoints are protected by authentication and rate limiting:

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/food/search', [FoodController::class, 'searchByName']);
    Route::post('/food/barcode', [FoodController::class, 'searchByBarcode']);
    Route::post('/food/store', [FoodController::class, 'storeFoodItem']);
});
```

## Browser Compatibility

The barcode scanner requires:

-   HTTPS connection (for camera access)
-   Modern browser with WebRTC support
-   Camera permissions

Supported browsers:

-   Chrome 53+
-   Firefox 52+
-   Safari 11+
-   Edge 79+

## Troubleshooting

### Barcode Scanner Not Working

1. Ensure you're on HTTPS
2. Check camera permissions
3. Try refreshing the page
4. Use manual barcode entry as fallback

### API Errors

1. Verify your FatSecret API credentials are correct
2. Check API rate limits
3. Ensure internet connection is stable

### Food Not Found

1. Try different search terms
2. Check if the barcode is in the FatSecret database
3. Some generic or local products may not be available

## Future Enhancements

Potential improvements for the food tracker:

-   Meal planning and recipes
-   Nutrition goals and tracking
-   Food favorites and quick add
-   Export nutrition data
-   Integration with fitness trackers
-   Photo recognition for food items
