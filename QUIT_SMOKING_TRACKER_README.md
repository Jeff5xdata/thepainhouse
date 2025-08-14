# 🚭 Quit Smoking Tracker

A comprehensive web application to help users gradually reduce and quit smoking over a 30-day period. Built with Laravel, Livewire, and Tailwind CSS.

## ✨ Features

### 📊 Gradual Reduction Plan
- **30-day reduction schedule**: Starts at 20 cigarettes per day and gradually reduces to 0
- **Smart scheduling**: Automatically calculates optimal smoking times throughout the day
- **Progress tracking**: Visual indicators showing daily targets vs. actual consumption
- **Flexible dates**: Customizable start and quit dates

### 💰 Cost Tracking
- **Real-time cost calculation**: Based on $8.59 per pack of 20 cigarettes
- **Total spending**: Track cumulative cost of smoking
- **Savings projection**: See potential savings from quitting

### 📝 Smoking Logs
- **Detailed logging**: Record time and number of cigarettes smoked
- **Easy management**: Add, edit, and delete smoking entries
- **Historical data**: View complete smoking history with pagination
- **Data persistence**: Session-based storage (can be upgraded to database)

### 🔔 Smart Notifications
- **Browser notifications**: Desktop notifications when it's time to smoke
- **In-page alerts**: Floating notifications with sound alerts
- **Automatic scheduling**: Calculates next smoke time based on daily targets
- **Permission management**: Requests notification permissions automatically

### 📱 Modern UI
- **Responsive design**: Works on desktop, tablet, and mobile
- **Beautiful interface**: Modern gradient backgrounds and card-based layout
- **Dark mode support**: Integrates with existing theme system
- **Interactive elements**: Hover effects and smooth transitions

## 🚀 Getting Started

### Prerequisites
- Laravel 10+ application
- Livewire 3.x
- Tailwind CSS
- Alpine.js

### Installation

1. **Add the route** (already added to `routes/web.php`):
```php
Route::get('/quit-smoking', \App\Livewire\QuitSmokingTracker::class)->name('quit.smoking');
```

2. **Access the page**: Navigate to `/quit-smoking` in your browser

3. **Grant permissions**: Allow browser notifications when prompted

## 📋 Usage Guide

### Setting Up Your Quit Plan

1. **Configure Settings**:
   - Set your start date
   - Choose your target quit date (default: 30 days)
   - Adjust initial cigarettes per day (default: 20)
   - Set pack price (default: $8.59)

2. **Review Your Plan**:
   - View the 30-day reduction schedule
   - See daily targets and progress
   - Monitor completion status

### Daily Usage

1. **Add Smoking Logs**:
   - Click "Add Log" button
   - Enter time and number of cigarettes
   - Submit to track your consumption

2. **Monitor Progress**:
   - Check daily targets vs. actual consumption
   - View cost accumulation
   - Track days smoke-free

3. **Follow Notifications**:
   - Receive alerts when it's time to smoke
   - Stay on track with your reduction plan
   - Use the floating notification button for reminders

### Tracking Your Progress

- **Overview Cards**: See totals, costs, and next smoke time at a glance
- **Reduction Table**: Detailed day-by-day progress tracking
- **Smoking Logs**: Complete history of all smoking entries
- **Cost Analysis**: Real-time spending calculations

## 🔧 Technical Details

### Architecture
- **Backend**: Laravel Livewire component with reactive state management
- **Frontend**: Blade templates with Tailwind CSS styling
- **JavaScript**: Custom notification system with browser API integration
- **State Management**: Session-based storage with Livewire reactivity

### Key Components

#### QuitSmokingTracker.php
- Main Livewire component
- Handles all business logic
- Manages smoking logs and calculations
- Generates reduction plans

#### quit-smoking-tracker.blade.php
- Main view template
- Responsive UI components
- Interactive elements and modals
- Progress visualization

#### smoking-notifications.js
- Browser notification system
- Automatic reminder scheduling
- Sound alerts and in-page notifications
- Permission management

### Data Flow
1. User interacts with UI (adds logs, changes settings)
2. Livewire processes requests and updates state
3. Component recalculates reduction plan and totals
4. UI updates reactively with new data
5. JavaScript handles notifications and reminders

## 🎯 Customization Options

### Reduction Schedule
- Modify the reduction algorithm in `calculateReductionPlan()`
- Adjust the 30-day default period
- Change the reduction curve (linear, exponential, etc.)

### Notification Settings
- Adjust notification frequency in `startNotificationChecks()`
- Modify notification content and styling
- Add custom sound effects or vibration

### Cost Calculations
- Update default pack price and size
- Add tax calculations
- Include additional smoking-related costs

## 🔒 Security & Privacy

- **Session-based storage**: Data stored locally in user session
- **No external APIs**: All calculations done server-side
- **User isolation**: Each user sees only their own data
- **CSRF protection**: Built-in Laravel security features

## 🚧 Future Enhancements

### Database Integration
- Persistent storage for smoking logs
- User account linking
- Data backup and export

### Advanced Analytics
- Smoking pattern analysis
- Trigger identification
- Success rate tracking

### Social Features
- Support groups
- Progress sharing
- Achievement badges

### Mobile App
- PWA optimization
- Offline functionality
- Push notifications

## 🐛 Troubleshooting

### Common Issues

1. **Notifications not working**:
   - Check browser notification permissions
   - Ensure HTTPS connection (required for notifications)
   - Verify JavaScript is enabled

2. **Data not persisting**:
   - Check session configuration
   - Verify Livewire is working properly
   - Check browser console for errors

3. **UI not updating**:
   - Refresh the page
   - Check Livewire dev tools
   - Verify Alpine.js is loaded

### Debug Mode
Enable Livewire dev tools in your `.env`:
```
LIVEWIRE_DEVTOOLS=true
```

## 📞 Support

For technical support or feature requests:
- Check the Laravel and Livewire documentation
- Review browser console for JavaScript errors
- Verify all dependencies are properly installed

## 📄 License

This component is part of The Pain House application and follows the same licensing terms.

---

**Good luck on your journey to a smoke-free life! 🚭➡️💪**
