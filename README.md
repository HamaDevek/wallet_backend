# Digital Wallet Backend API

A comprehensive Laravel-based digital wallet system that provides secure user authentication, wallet management, and transaction processing capabilities.

## 🚀 Features

-   **User Authentication** - Secure registration and login with Laravel Sanctum
-   **Wallet Management** - Digital wallet with balance tracking
-   **Money Transfers** - Send money between users via email
-   **Wallet Recharge** - Add funds to user wallets
-   **Transaction History** - Complete transaction tracking and retrieval
-   **Rate Limiting** - Protection against brute force attacks
-   **Event System** - Automated notifications for transactions
-   **API Response Helper** - Consistent JSON responses

## 🛠️ Tech Stack

-   **Framework**: Laravel 12.x
-   **Authentication**: Laravel Sanctum
-   **Database**: MySQL (configurable)
-   **Queue System**: Database-driven queues
-   **Caching**: Database/Redis
-   **Testing**: PHPUnit
-   **Code Style**: Laravel Pint

## 📋 Requirements

-   PHP 8.2 or higher
-   Composer
-   MySQL 5.7+ or equivalent database
-   Node.js & NPM (for asset compilation)

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd wallet-backend
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_backend
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan db:seed
```

### 6. Start Development Server

```bash
# Start all services (server, queue, and vite)
composer run dev

# Or start individually:
php artisan serve          # API server
php artisan queue:work      # Queue worker
npm run dev                # Asset compilation
```

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication Endpoints

#### Health Check

```http
GET /health
```

#### Register User

```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login

```http
POST /auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Get Current User

```http
GET /auth/me
Authorization: Bearer {token}
```

#### Logout

```http
DELETE /auth/logout
Authorization: Bearer {token}
```

### Transaction Endpoints

#### Recharge Wallet

```http
POST /transactions/recharge
Authorization: Bearer {token}
Content-Type: application/json

{
    "amount": 5000,
    "description": "Wallet top-up"
}
```

#### Send Money

```http
POST /transactions/send-money
Authorization: Bearer {token}
Content-Type: application/json

{
    "recipient_email": "recipient@example.com",
    "amount": 1000,
    "description": "Payment for services"
}
```

#### Get Transaction History

```http
# All transactions
GET /transactions/all-transactions
Authorization: Bearer {token}

# Sent transactions only
GET /transactions/sent-transactions
Authorization: Bearer {token}

# Received transactions only
GET /transactions/received-transactions
Authorization: Bearer {token}
```

## 📊 Response Format

All API responses follow a consistent format:

### Success Response

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    },
    "server_time": "2024-06-05T12:00:00.000Z"
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        // Validation errors (if applicable)
    },
    "server_time": "2024-06-05T12:00:00.000Z"
}
```

## 🔒 Security Features

### Rate Limiting

-   **Authentication endpoints**: 5 attempts per minute per IP/email combination
-   **Failed attempts**: Automatically tracked and blocked

### Validation Rules

-   **Recharge amount**: 1,000 - 5,000,000 (currency units)
-   **Transfer amount**: 1,000 - 5,000,000 (currency units)
-   **Email validation**: RFC compliant email addresses
-   **Password**: Minimum 8 characters

### Database Security

-   **Foreign key constraints**: Maintain data integrity
-   **Decimal precision**: 15 digits with 2 decimal places for amounts
-   **Transaction status tracking**: Prevent double-spending

## 🎯 Business Logic

### Wallet Operations

1. **Recharge**: Adds funds to user's wallet balance
2. **Transfer**: Moves funds between users with validation
3. **Balance Check**: Prevents overdrafts

### Transaction Types

-   `recharge`: Wallet top-up
-   `transfer`: Money transfer between users

### Event System

Automatic email notifications (simulated) for:

-   Successful wallet recharge
-   Money sent confirmation
-   Money received notification

## 🗃️ Database Schema

### Users Table

-   `id` - Primary key
-   `name` - User's full name
-   `email` - Unique email address
-   `wallet_balance` - Current balance (decimal 15,2)
-   `email_verified_at` - Email verification timestamp
-   `password` - Hashed password
-   `created_at`, `updated_at` - Timestamps

### Transactions Table

-   `id` - Primary key
-   `amount` - Transaction amount (decimal 15,2)
-   `sender_id` - Foreign key to users (nullable for recharge)
-   `receiver_id` - Foreign key to users
-   `type` - Transaction type (recharge/transfer)
-   `status` - Transaction status (pending/completed/failed)
-   `description` - Transaction description
-   `created_at`, `updated_at` - Timestamps

## 🧪 Testing

```bash
# Run all tests
composer test

# Run specific test types
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 📁 Project Structure

```
app/
├── Events/
│   └── TransactionProcessed.php      # Transaction events
├── Helpers/
│   └── ApiResponseHelper.php         # Consistent API responses
├── Http/
│   ├── Controllers/API/
│   │   ├── ApiController.php         # Health check
│   │   ├── AuthController.php        # Authentication
│   │   └── TransactionController.php # Transaction management
│   ├── Middleware/RateLimit/
│   │   └── AuthRateLimitMiddleware.php # Rate limiting
│   └── Resources/
│       ├── TransactionResource.php   # Transaction API resource
│       └── UserResource.php          # User API resource
├── Listeners/
│   └── SendTransactionNotification.php # Email notifications
├── Models/
│   ├── Transactions.php              # Transaction model
│   └── User.php                      # User model with wallet methods
└── Providers/
    └── EventServiceProvider.php      # Event binding
```

## 🔧 Configuration

### Queue Configuration

The application uses database queues for processing notifications:

```env
QUEUE_CONNECTION=database
```

### Cache Configuration

```env
CACHE_STORE=database
```

### Mail Configuration

Currently set to log emails (development):

```env
MAIL_MAILER=log
```

## 🚀 Deployment

### Production Checklist

-   [ ] Set `APP_ENV=production`
-   [ ] Set `APP_DEBUG=false`
-   [ ] Configure proper database credentials
-   [ ] Set up mail service (SMTP/SendGrid/etc.)
-   [ ] Configure queue worker service
-   [ ] Set up proper cache driver (Redis recommended)
-   [ ] Configure SSL certificates
-   [ ] Set up monitoring and logging

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

###  Code Style

This project uses Laravel Pint for code formatting:

```bash
composer run format
```

## 🔄 Changelog

### v1.0.0 (Initial Release)

-   User authentication with Sanctum
-   Wallet management system
-   Money transfer functionality
-   Transaction history tracking
-   Rate limiting implementation
-   Event-driven notifications

---

Built with ❤️ using Laravel 
