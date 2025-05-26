# LaraPool - Pool Management System

LaraPool is a comprehensive pool management system designed to streamline the operations of billiard and pool halls. It offers robust functionality for table reservations, F&B management, transaction processing, and business analytics.

## Features

- **Table Reservation**: Easy scheduling and management of pool tables
- **F&B Order Management**: Process food and beverage orders efficiently
- **POS System**: Complete point-of-sale functionality
- **Inventory Management**: Track F&B and equipment inventory
- **User Management**: Role-based access control for staff and administrators
- **Reports & Analytics**: Comprehensive business intelligence dashboard
- **Notifications**: Real-time alerts for reservations and orders

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx web server

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/andimohsoreang/LaraPool.git
   cd LaraPool
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Install JavaScript dependencies:
   ```
   npm install
   ```

4. Create and configure your environment file:
   ```
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=larapool
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Run migrations and seed the database:
   ```
   php artisan migrate --seed
   ```

7. Build frontend assets:
   ```
   npm run dev
   ```

## Configuration

### Environment Variables

Review and update these important environment variables in your `.env` file:

- `APP_URL`: Your application URL
- `DB_*`: Database connection details
- `MAIL_*`: Email configuration for notifications
- `QUEUE_CONNECTION`: For background processing (recommend 'database' or 'redis')

### Cache and Optimization

For production environments, run:
```
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Usage

1. Start the development server:
   ```
   php artisan serve
   ```

2. Access the application at `http://localhost:8000`

3. Default admin login:
   - Email: admin@larapool.com
   - Password: password

## Development

### Code Style

This project follows the PSR-12 coding standard and Laravel's coding style. To check your code style:
```
./vendor/bin/phpcs
```

### Running Tests

```
php artisan test
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

For support or inquiries, please contact [andimohamaddd@gmail.com](mailto:andimohamaddd@gmail.com).
