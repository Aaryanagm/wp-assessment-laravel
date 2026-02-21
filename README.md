# WP Assessment - Laravel Backend

## Tech Stack
- Laravel 10
- JWT Authentication (tymon/jwt-auth)
- WordPress Database Integration

## Features Implemented

1. JWT Login using wp_users
2. Role extraction from wp_usermeta (customer, silver, gold)
3. Shop API with:
   - Public / Protected category filtering
   - Role-based pricing
4. Single Product API
   - Categories
   - Variants
   - Stock
   - Tier pricing
5. Orders API (user-specific)
6. Blade frontend adapting based on role

## API Endpoints

POST   /api/login  
GET    /api/shop  
GET    /api/shop/{id}  
GET    /api/orders  

## Setup

1. Clone repository
2. Configure .env
3. Run:
   php artisan migrate
   php artisan serve
