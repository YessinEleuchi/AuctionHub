# 🏷️ AuctionHub API



![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white) ![JWT](https://img.shields.io/badge/JWT-000000?style=for-the-badge&logo=JSON%20web%20tokens&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)


## 📝 Description

AuctionHub is a robust RESTful API built with Laravel that powers an online auction platform. This backend service provides all the necessary endpoints for managing auctions, users, bids, and real-time notifications.

## ✨ Features

- 🔐 JWT Authentication
- 👥 User Management
- 🏷️ Auction Management
- 💰 Bidding System
- 🔔 Real-time Notifications
- 📊 Admin Dashboard
- 🔍 Search & Filtering
- 📱 RESTful API Architecture

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- MySQL
- Node.js & NPM

### Installation

1. Clone the repository
```bash
git clone https://github.com/Yessine-ELEUCHI/AuctionHub.git
cd AuctionHub
```

2. Install PHP dependencies
```bash
composer install
```

3. Create environment file
```bash
cp .env.example .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Configure your database in `.env` file
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auction_hub
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations and seeders
```bash
php artisan migrate --seed
```

7. Start the development server
```bash
php artisan serve
```

## 📚 API Documentation

The API documentation is available at `/api/documentation` when running the server.

### Main Endpoints

- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/auctions` - List all auctions
- `POST /api/auctions` - Create new auction
- `GET /api/auctions/{id}` - Get auction details
- `POST /api/auctions/{id}/bid` - Place a bid

## 🧪 Testing

Run the test suite using PHPUnit:

```bash
php artisan test
```

## 🔧 Configuration

The main configuration files are located in the `config` directory. Key configurations include:

- `config/auth.php` - Authentication settings
- `config/jwt.php` - JWT configuration
- `config/database.php` - Database settings

## 📦 Dependencies

- Laravel Framework 10.x
- JWT Auth for authentication
- Laravel Sanctum for API tokens
- PHPUnit for testing

## 👨‍💻 Author

**Yessine ELEUCHI**
- GitHub: [@Yessine-ELEUCHI](https://github.com/Yessine-ELEUCHI)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


## ⭐ Show your support

Give a ⭐️ if this project helped you!
