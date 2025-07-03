# The Pain House

A powerful weightlifting tracking application built with Laravel and Livewire. Track your lifts, monitor progress, manage nutrition, and achieve your strength goals with our comprehensive fitness platform.

## 🏋️ Features

### Core Workout Management

-   **Workout Planning**: Create detailed workout plans with customizable schedules
-   **Exercise Library**: Comprehensive exercise database with categories and equipment types
-   **Workout Sessions**: Track individual workout sessions with real-time progress
-   **Exercise Sets**: Log sets, reps, weights, and rest periods
-   **Warm-up Sets**: Configure automatic warm-up sets with percentage-based weights
-   **Time-based Exercises**: Support for timed exercises (cardio, holds, etc.)

### Nutrition Tracking

-   **Food Search**: Search for food items by name using the Chomp API
-   **Barcode Scanning**: Scan product barcodes using your phone's camera
-   **Nutrition Tracking**: Track calories, protein, carbs, fat, and other nutrients
-   **Meal Organization**: Organize food items by meal type (breakfast, lunch, dinner, snack)
-   **Daily Summaries**: View daily nutrition totals and breakdowns
-   **Weekly Summaries**: View weekly nutrition totals (Monday-Sunday) with daily breakdown
-   **Food History**: Keep track of what you've eaten with timestamps
-   **View Modes**: Toggle between daily and weekly views

### Trainer-Client Management

-   **Trainer Accounts**: Dedicated trainer profiles with client management capabilities
-   **Client Assignment**: Trainers can manage multiple clients with individual workout plans
-   **Workout Copying**: Copy workouts from trainer's plan to client plans with one click
-   **Client Progress Tracking**: Monitor client progress, workout completion, and performance
-   **Client Communication**: Built-in messaging system for trainer-client communication
-   **Client Dashboard**: Comprehensive overview of client's fitness journey
-   **Workout Assignment**: Assign new workouts to clients with automatic notifications
-   **Trainer Request System**: Clients can request trainers by email with status tracking
-   **Messaging Center**: Three-tab interface for messages, incoming requests, and outgoing requests
-   **Real-time Status Updates**: Track trainer request status with visual badges (Pending/Approved/Rejected)

### Progress Tracking & Analytics

-   **Progress Monitoring**: Track performance over time with detailed analytics
-   **Exercise History**: View complete workout history and exercise performance
-   **Progress Charts**: Visual progress tracking for weights, reps, and volume
-   **Performance Metrics**: Monitor max weights, total volume, and rep counts
-   **Time-based Analysis**: Filter progress by week, month, or year
-   **Nutrition Analytics**: Track nutrition trends and daily/weekly summaries

### Advanced Workout Features

-   **Multi-week Plans**: Create workout plans spanning multiple weeks
-   **Flexible Scheduling**: Schedule exercises for specific days and weeks
-   **Exercise Categories**: Organize exercises by muscle groups (chest, back, legs, etc.)
-   **Equipment Types**: Filter exercises by equipment (barbell, dumbbell, machine, etc.)
-   **Custom Exercise Creation**: Add your own exercises to the library
-   **Workout Settings**: Configure default rest timers, warm-up sets, and work sets
-   **Workout Copying**: Copy workouts between days, weeks, or to clients

### Data Management & Sharing

-   **Backup & Restore**: Export and import your complete workout data
-   **Workout Plan Sharing**: Share workout plans via secure links
-   **Email Sharing**: Send workout plans directly to other users
-   **Data Export**: Download workout data in JSON format
-   **Selective Restore**: Choose what data to restore (plans, history, settings)

### User Experience

-   **Dark Mode**: Toggle between light and dark themes
-   **Mobile-Friendly**: Responsive design optimized for mobile devices
-   **Progressive Web App (PWA)**: Install as a native app on mobile devices
-   **Offline Support**: Basic offline functionality with PWA
-   **Real-time Updates**: Livewire-powered real-time interface updates
-   **Notification System**: Success/error notifications for user actions
-   **AI Workout Generation**: AI-powered personalized workout plans using Google's Gemini API

### Security & Performance

-   **User Authentication**: Secure login and registration system
-   **Rate Limiting**: Protection against abuse with request throttling
-   **Data Validation**: Comprehensive input validation and sanitization
-   **Database Constraints**: Proper database relationships and constraints
-   **Optimized Queries**: Efficient database queries with proper indexing

## 🚀 Installation

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd thepainhouse
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Configure environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Set up database**

    ```bash
    php artisan migrate
    ```

5. **Install and build frontend assets**

    ```bash
    npm install
    npm run dev
    ```

6. **Configure FatSecret API (for nutrition tracking)**

    ```bash
    # Add to your .env file:
    FATSECRET_CONSUMER_KEY=your_consumer_key_here
    FATSECRET_CONSUMER_SECRET=your_consumer_secret_here
    ```

    Get your API credentials from [FatSecret Platform API](https://platform.fatsecret.com/)

7. **Configure Gemini API (for AI workout generation)**

    ```bash
    # Add to your .env file:
    GEMINI_API_KEY=your_gemini_api_key_here
    ```

    Get your API key from [Google AI Studio](https://makersuite.google.com/app/apikey)

8. **Start the development server**
    ```bash
    php artisan serve
    ```

## 🛠️ Technology Stack

-   **Backend**: Laravel 12.x with PHP 8.2+
-   **Frontend**: Livewire 3.x, Alpine.js, Tailwind CSS
-   **Database**: MySQL/PostgreSQL/SQLite
-   **PWA**: Laravel PWA package
-   **Charts**: Custom progress tracking with Alpine.js
-   **File Handling**: PhpSpreadsheet for data export/import
-   **Nutrition API**: FatSecret Platform API for food data and barcode scanning
-   **Barcode Scanning**: WebRTC camera API for mobile barcode scanning
-   **AI Integration**: Google Gemini API for AI-powered workout generation

## 👥 Trainer-Client Workflow

### For Trainers

1. **Create Trainer Account**: Register and set up your trainer profile
2. **Accept Client Requests**: Clients can send trainer requests via email or messaging center
3. **Manage Clients**: View all your clients in the trainer dashboard
4. **Create Workout Plans**: Design comprehensive workout plans for clients
5. **Copy Workouts**: Copy workouts from your plan to client plans
6. **Monitor Progress**: Track client progress, workout completion, and nutrition data
7. **Communicate**: Send messages and notifications to clients through the messaging center

### For Clients

1. **Request Trainer**: Send a trainer request with a message via email or messaging center
2. **Track Request Status**: Monitor your trainer request status with visual badges
3. **Accept Trainer**: Approve trainer requests to establish relationship
4. **Receive Workouts**: Get assigned workouts from your trainer
5. **Track Progress**: Complete workouts and monitor your progress
6. **Log Nutrition**: Track your daily food intake and nutrition goals
7. **Communicate**: Message your trainer for guidance and support

### Copy to Client Feature

Trainers can easily copy workouts to their clients:

1. **View Your Plan**: Navigate to your workout plan view
2. **Select Workout**: Click the copy button on any day's workout
3. **Choose Target**: Select whether to copy to your plan or to a client
4. **Select Client**: Choose from your list of clients
5. **Set Schedule**: Specify target week and day for the client
6. **Copy & Notify**: The workout is copied and the client is automatically notified

## 🍎 Nutrition Tracking Features

### Food Search & Barcode Scanning

-   **Name Search**: Search for food items by name using the Chomp API
-   **Barcode Scanning**: Use your phone's camera to scan product barcodes
-   **Manual Barcode Entry**: Enter barcode numbers manually if scanning isn't available
-   **Real-time Results**: Live search results as you type

### Meal Organization

-   **Meal Types**: Organize food by breakfast, lunch, dinner, and snacks
-   **Quantity Tracking**: Log specific quantities and portion sizes
-   **Notes**: Add optional notes to food entries
-   **Quick Add**: Streamlined food logging process

### Nutrition Analytics

-   **Daily Summaries**: Total calories, protein, carbs, and fat for each day
-   **Weekly Summaries**: Weekly totals with daily breakdown charts
-   **Nutrition History**: Complete food log with timestamps
-   **View Modes**: Toggle between daily and weekly views
-   **Data Indicators**: Visual indicators for days with logged food

## 📱 Progressive Web App Features

-   **Installable**: Add to home screen on mobile devices
-   **Offline Support**: Basic offline functionality
-   **App-like Experience**: Native app feel on mobile
-   **Push Notifications**: Ready for future notification features
-   **Camera Access**: Barcode scanning functionality on mobile devices

## 🔧 Configuration

### Workout Settings

Configure your default workout preferences:

-   Rest timer duration
-   Warm-up sets and reps
-   Warm-up weight percentage
-   Default work sets and reps

### Exercise Management

-   Create custom exercises
-   Categorize by muscle groups
-   Specify equipment requirements
-   Add detailed descriptions

### Nutrition Settings

-   Configure Chomp API credentials
-   Set up barcode scanning permissions
-   Customize meal type preferences
-   Configure nutrition tracking goals

### Backup & Restore

-   Export complete workout data
-   Import from backup files
-   Selective data restoration
-   Conflict resolution options

## 📊 Data Structure

The application manages several key data models:

-   **Users**: Authentication and profile management with trainer/client relationships
-   **Exercises**: Exercise library with categories and equipment
-   **Workout Plans**: Multi-week workout schedules
-   **Workout Sessions**: Individual workout tracking
-   **Exercise Sets**: Detailed set-by-set logging
-   **Workout Settings**: User preferences and defaults
-   **Share Links**: Secure workout plan sharing
-   **Trainer Requests**: Client-trainer relationship management
-   **Messages**: Communication system between trainers and clients
-   **Food Items**: Food product information from Chomp API
-   **Food Logs**: User food consumption records

## 🔒 Security Features

-   **Authentication**: Laravel Breeze authentication
-   **Rate Limiting**: Request throttling on sensitive operations
-   **Data Validation**: Comprehensive input validation
-   **SQL Injection Protection**: Eloquent ORM with parameter binding
-   **XSS Protection**: Blade templating with automatic escaping
-   **Trainer-Client Isolation**: Secure separation of trainer and client data
-   **API Security**: Chomp API authentication with user-specific credentials

## 📈 Performance Optimizations

-   **Database Indexing**: Optimized queries with proper indexes
-   **Eager Loading**: Efficient relationship loading
-   **Caching**: Ready for Redis/Memcached integration
-   **Asset Optimization**: Vite-based asset compilation
-   **Lazy Loading**: Progressive loading of components
-   **API Caching**: Efficient Chomp API request handling

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

The Pain House application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For support and questions:

-   Check the application logs for detailed error messages
-   Review the backup/restore documentation in `BACKUP_RESTORE.md`
-   Review the food tracker documentation in `FOOD_TRACKER_README.md`
-   Review the trainer system documentation in `TRAINER_SYSTEM_README.md`
-   Review the FatSecret API setup in `FATSECRET_API_SETUP.md`
-   Ensure you have the latest version of the application

## 📚 Additional Documentation

-   **Backup & Restore**: See `BACKUP_RESTORE.md` for detailed backup/restore instructions
-   **Food Tracker**: See `FOOD_TRACKER_README.md` for nutrition tracking features
-   **Trainer System**: See `TRAINER_SYSTEM_README.md` for trainer-client management
-   **FatSecret API Setup**: See `FATSECRET_API_SETUP.md` for nutrition API configuration
-   **AI Workout Generator**: See `AI_WORKOUT_GENERATOR_README.md` for AI-powered workout generation

---

**The Pain House** - Transform your strength training journey with comprehensive workout tracking, nutrition monitoring, progress analytics, and trainer-client management.
