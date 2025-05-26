# PoolApp - Comprehensive Billiard Management System

PoolApp is a powerful and intuitive management system designed specifically for billiard halls and pool establishments. The application streamlines all aspects of day-to-day operations including table reservations, food and beverage ordering, inventory management, staff coordination, and detailed business analytics.

Built with Laravel, this system provides a robust foundation for managing the complete lifecycle of pool hall operations, from customer check-in to financial reporting.

## Key Features

- **Advanced Table Reservation**: Intuitive scheduling system with conflict prevention, time-based pricing, and calendar visualization
- **F&B Order Management**: Complete food and beverage ordering system with kitchen notifications and status tracking
- **Modern POS System**: Comprehensive point-of-sale functionality with multiple payment methods support
- **Inventory Control**: Track F&B and equipment inventory with automatic alerts for low stock
- **User Management**: Role-based access control with custom permissions for owners, managers, staff, and customers
- **Business Intelligence**: Detailed reports and analytics with customizable dashboards for data-driven decisions
- **Real-time Notifications**: Instant alerts for new reservations, order status changes, and important system events
- **Customer Management**: Profile creation, reservation history, and loyalty program integration

## Requirements

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL 5.7+ or MariaDB 10.3+
- Apache or Nginx web server

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/andimohsoreang/PoolApp.git
   cd PoolApp
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
   DB_DATABASE=poolapp
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
   - Email: admin@poolapp.com
   - Password: password

## Development

### Code Style & Standards

This project follows the PSR-12 coding standard and Laravel's coding style guidelines. To check your code style:
```
./vendor/bin/phpcs
```

### Testing

The application includes comprehensive testing:
```
php artisan test
```

### Project Architecture

PoolApp follows Laravel best practices with:
- MVC architecture for clean separation of concerns
- Repository pattern for database operations
- Service layer for business logic
- Event-driven design for real-time features

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Security

If you discover any security vulnerabilities, please email [andimohamaddd@gmail.com](mailto:andimohamaddd@gmail.com) instead of using the issue tracker.

## Contact & Support

For general support, feature requests, or inquiries, please contact:
- Email: [andimohamaddd@gmail.com](mailto:andimohamaddd@gmail.com)
- GitHub: [github.com/andimohsoreang](https://github.com/andimohsoreang)
