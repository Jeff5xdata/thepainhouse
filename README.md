# The Pain House

A powerful weightlifting tracking application built with Laravel and Livewire. Track your lifts, monitor progress, and achieve your strength goals with our comprehensive weightlifting platform.

## 🏋️ Features

### Core Workout Management

-   **Workout Planning**: Create detailed workout plans with customizable schedules
-   **Exercise Library**: Comprehensive exercise database with categories and equipment types
-   **Workout Sessions**: Track individual workout sessions with real-time progress
-   **Exercise Sets**: Log sets, reps, weights, and rest periods
-   **Warm-up Sets**: Configure automatic warm-up sets with percentage-based weights
-   **Time-based Exercises**: Support for timed exercises (cardio, holds, etc.)

### Progress Tracking & Analytics

-   **Progress Monitoring**: Track performance over time with detailed analytics
-   **Exercise History**: View complete workout history and exercise performance
-   **Progress Charts**: Visual progress tracking for weights, reps, and volume
-   **Performance Metrics**: Monitor max weights, total volume, and rep counts
-   **Time-based Analysis**: Filter progress by week, month, or year

### Advanced Workout Features

-   **Multi-week Plans**: Create workout plans spanning multiple weeks
-   **Flexible Scheduling**: Schedule exercises for specific days and weeks
-   **Exercise Categories**: Organize exercises by muscle groups (chest, back, legs, etc.)
-   **Equipment Types**: Filter exercises by equipment (barbell, dumbbell, machine, etc.)
-   **Custom Exercise Creation**: Add your own exercises to the library
-   **Workout Settings**: Configure default rest timers, warm-up sets, and work sets

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

6. **Start the development server**
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

## 📱 Progressive Web App Features

-   **Installable**: Add to home screen on mobile devices
-   **Offline Support**: Basic offline functionality
-   **App-like Experience**: Native app feel on mobile
-   **Push Notifications**: Ready for future notification features

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

### Backup & Restore

-   Export complete workout data
-   Import from backup files
-   Selective data restoration
-   Conflict resolution options

## 📊 Data Structure

The application manages several key data models:

-   **Users**: Authentication and profile management
-   **Exercises**: Exercise library with categories and equipment
-   **Workout Plans**: Multi-week workout schedules
-   **Workout Sessions**: Individual workout tracking
-   **Exercise Sets**: Detailed set-by-set logging
-   **Workout Settings**: User preferences and defaults
-   **Share Links**: Secure workout plan sharing

## 🔒 Security Features

-   **Authentication**: Laravel Breeze authentication
-   **Rate Limiting**: Request throttling on sensitive operations
-   **Data Validation**: Comprehensive input validation
-   **SQL Injection Protection**: Eloquent ORM with parameter binding
-   **XSS Protection**: Blade templating with automatic escaping

## 📈 Performance Optimizations

-   **Database Indexing**: Optimized queries with proper indexes
-   **Eager Loading**: Efficient relationship loading
-   **Caching**: Ready for Redis/Memcached integration
-   **Asset Optimization**: Vite-based asset compilation
-   **Lazy Loading**: Progressive loading of components

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
-   Ensure you have the latest version of the application

---

**The Pain House** - Transform your strength training journey with comprehensive workout tracking and progress monitoring.
