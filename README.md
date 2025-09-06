# Laravel Messaging App

A robust Laravel application for sending SMS messages to multiple recipients with advanced rate limiting, comprehensive logging, and campaign management features.

## Features

- **Campaign Management**: Create and manage SMS campaigns with multiple recipients
- **Rate Limiting**: Built-in rate limiting (2 messages per 5 seconds) to prevent API abuse
- **Queue Processing**: Asynchronous message processing with Laravel queues
- **Comprehensive Logging**: Detailed logging with timing analysis and performance metrics
- **Status Tracking**: Track message status (Pending, Sent, Failed) with detailed failure reasons
- **REST API**: Complete API endpoints for message management and statistics
- **Retry Logic**: Automatic retry mechanism with configurable attempts (25 retries)
- **Caching**: Redis-based caching for message IDs and performance optimization
- **Testing**: Comprehensive test suite with Pest PHP

![Running messages:send-pending command to process campaigns and dispatch batch jobs for message sending](https://github.com/Biostate/laravel-messaging-app/blob/main/art/figure-1.png?raw=true)

![Rate limiting implementation showing 2 messages processed every 5 seconds with job middleware](https://github.com/Biostate/laravel-messaging-app/blob/main/art/figure-2.png?raw=true)

## Requirements

- PHP 8.3+
- Laravel 12.x
- Redis (for queues and caching)
- SQLite/MySQL/PostgreSQL
- Composer
- Node.js & NPM (for frontend assets)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Biostate/laravel-messaging-app
cd laravel-messaging-app
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Update the following values in your `.env` file:

```env
# Application
APP_NAME="Laravel Messaging App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite by default)
DB_CONNECTION=sqlite

# Queue & Cache (Redis recommended)
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Webhook Site Configuration
WEBHOOK_SITE_UNIQUE_ID=your-webhook-site-id
WEBHOOK_SITE_BASE_URL=https://webhook.site
WEBHOOK_SITE_AUTH_KEY=your-auth-key
```

### 4. Application Setup

```bash
# Generate application key
php artisan key:generate

# Install application (runs migrations and optionally seeds data)
php artisan app:install-application

# Or install with sample data
php artisan app:install-application --seed-dummy-data
```

### 5. Start the Application

```bash
# Start the development server
php artisan serve

# In another terminal, start the queue worker
php artisan queue:work
```

Your application will be available at: [http://localhost:8000](http://localhost:8000)

## Usage

### Sending Messages

1. **Create a Campaign**:
   ```bash
   php artisan tinker
   ```
   ```php
   $campaign = \App\Models\Campaign::create([
       'name' => 'Welcome Campaign',
       'message' => 'Welcome to our service!',
       'status' => 'draft'
   ]);
   ```

2. **Add Recipients**:
   ```php
   $recipient = \App\Models\Recipient::create([
       'phone_number' => '+1234567890',
       'name' => 'John Doe'
   ]);
   ```

3. **Create Campaign Recipients**:
   ```php
   $campaignRecipient = \App\Models\CampaignRecipient::create([
       'campaign_id' => $campaign->id,
       'recipient_id' => $recipient->id,
       'status' => 'pending'
   ]);
   ```

4. **Send Pending Messages**:
   ```bash
   php artisan messages:send-pending
   ```

### API Endpoints

#### Get Messages
```bash
GET /api/messages
GET /api/messages?status=sent&limit=50&page=1
```

#### Get Message Details
```bash
GET /api/messages/{id}
```

#### Get Statistics
```bash
GET /api/messages/stats/overview
```

### API Documentation

The API is fully documented with Swagger/OpenAPI 3.0 specifications:

- **Swagger UI**: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
- **Generate Docs**: `php artisan swagger:generate`
- **JSON Spec**: `storage/api-docs/api-docs.json`

The documentation includes:
- Complete endpoint descriptions
- Request/response schemas
- Parameter validation rules
- Example requests and responses
- Error handling documentation

### Queue Management

```bash
# Process pending messages
php artisan messages:send-pending --limit=100
```

## Configuration

### Rate Limiting

The application uses Laravel's built-in rate limiting:

```php
// In AppServiceProvider.php
RateLimiter::for('send-message', function () {
    return Limit::perSecond(2, 5); // 2 jobs per 5 seconds
});
```

### Job Configuration

```php
// In SendMessageJob.php
public $timeout = 60;    // 60 seconds timeout
public $tries = 25;      // 25 retry attempts
```

### Logging

Custom logging channel for detailed message tracking:

```php
// Logs stored in: storage/logs/send-message-job.log
Log::channel('send_message_job')->info('SendMessageJob: message_sent_successfully', [
    'campaign_recipient_id' => 123,
    'message_id' => 'msg_456',
    'waiting_time_seconds' => 5,
    'job_duration_seconds' => 2,
    'webhook_response_time_seconds' => 1.5
]);
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
