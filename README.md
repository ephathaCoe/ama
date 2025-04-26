# Amaris Heavy Machinery

A comprehensive website for Amaris, a company that sells heavy machinery and generators used in building, mining, and manufacturing industries. The website includes a product catalog, quote request system, and an admin panel for managing products, orders, and company information.

## Features

### Public Website
- Responsive design for all device sizes
- Product catalog with filtering by category
- Detailed product pages with specifications and features
- Quote request system for customers to inquire about products
- Company information and contact pages

### Admin Panel
- Secure login system with role-based access control
- Dashboard with key metrics and notifications
- Category management (add, edit, delete categories)
- Product management (add, edit, delete products)
- Order management system
- User management for admin, sales, and executive roles
- Company settings management

## Technology Stack
- Frontend: React + Vite with Tailwind CSS and shadcn/ui components
- Backend: PHP with MySQL database
- Local hosting: XAMPP

## Setup Instructions

### Prerequisites
1. XAMPP installed with PHP 7.4+ and MySQL
2. Node.js and npm installed

### Installation Steps

1. Clone the repository to your XAMPP htdocs folder:
```
git clone https://github.com/yourusername/amaris-heavy-machinery.git
```

2. Navigate to the project folder and install frontend dependencies:
```
cd amaris-heavy-machinery
npm install
```

3. Set up the database:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `amaris_heavy_machinery`
   - Import the SQL file from `database.sql`

4. Configure backend:
   - Navigate to the `api` folder
   - Install PHP dependencies using Composer:
     ```
     composer install
     ```
   - Update the database connection in `api/config/Database.php` if necessary

5. Start the development server:
```
npm run dev
```

6. Build for production:
```
npm run build
```

## Admin Access
The system comes without admin credentials. The admin must register at first use and then can create other users.

## User Roles and Permissions

### Admin
- Full access to all features
- Can manage users, quotes, orders and company settings

### Sales
- Can view and manage quotes and orders
- Can view and manage products
- Cannot access user management

### Executive
- Can view all data
- Can manage company settings
- Cannot manage users

## Project Structure

- `/src`: Frontend React code
  - `/components`: Reusable UI components
  - `/hooks`: Custom React hooks
  - `/layouts`: Page layouts
  - `/lib`: Utility functions and API clients
  - `/pages`: Application pages
  - `/providers`: Context providers
  - `/types`: TypeScript type definitions

- `/api`: Backend PHP code
  - `/config`: Database and JWT configuration
  - `/models`: Data models
  - `/auth`: Authentication endpoints
  - Various API endpoints for categories, products, quotes, etc.

- `/public`: Static assets

## License
This project is proprietary and confidential.