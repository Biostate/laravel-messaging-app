# Laravel Messaging App

This application allows you to send SMS messages to multiple phone numbers with **rate limiting** and keeps a record of all previously sent messages.

## Installation

1. **Clone the repository**
```bash
git clone https://github.com/Biostate/laravel-messaging-app
cd laravel-messaging-app
```

2. **Install dependencies**

```bash
composer install
```

3. **Copy and configure environment file**

```bash
cp .env.example .env
```

Update the following values in your `.env` file:

```env
WEBHOOK_SITE_UNIQUE_ID=your-webhook-site-id
```

4. **Generate application key**

```bash
php artisan key:generate
```

5. **Run migrations**

```bash
php artisan migrate
```

Your app will be available at: [http://localhost:8000](http://localhost:8000)
