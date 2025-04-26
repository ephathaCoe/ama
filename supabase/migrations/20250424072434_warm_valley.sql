-- MySQL Database Schema for Amaris Heavy Machinery

-- Create database
CREATE DATABASE IF NOT EXISTS amaris_heavy_machinery;
USE amaris_heavy_machinery;

-- Users table for authentication and authorization
CREATE TABLE IF NOT EXISTS users (
  id VARCHAR(36) PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  role ENUM('admin', 'sales', 'executive') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table for product categorization
CREATE TABLE IF NOT EXISTS categories (
  id VARCHAR(36) PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  description TEXT,
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table for machinery and equipment
CREATE TABLE IF NOT EXISTS products (
  id VARCHAR(36) PRIMARY KEY,
  category_id VARCHAR(36) NOT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  short_description VARCHAR(255) NOT NULL,
  full_description TEXT NOT NULL,
  specifications JSON NOT NULL,
  features JSON NOT NULL,
  price DECIMAL(12, 2),
  stock_status ENUM('in_stock', 'out_of_stock', 'pre_order') NOT NULL,
  main_image_url VARCHAR(255),
  gallery_images JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create index on category_id for faster joins
CREATE INDEX products_category_id_idx ON products(category_id);

-- Quotes table for customer inquiries
CREATE TABLE IF NOT EXISTS quotes (
  id VARCHAR(36) PRIMARY KEY,
  customer_name VARCHAR(150) NOT NULL,
  customer_email VARCHAR(200) NOT NULL,
  customer_phone VARCHAR(50),
  company_name VARCHAR(200),
  product_id VARCHAR(36),
  message TEXT NOT NULL,
  status ENUM('new', 'reviewed', 'contacted', 'closed') NOT NULL DEFAULT 'new',
  assigned_to VARCHAR(36),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for frequently queried columns
CREATE INDEX quotes_product_id_idx ON quotes(product_id);
CREATE INDEX quotes_status_idx ON quotes(status);
CREATE INDEX quotes_assigned_to_idx ON quotes(assigned_to);

-- Notifications table for system notifications
CREATE TABLE IF NOT EXISTS notifications (
  id VARCHAR(36) PRIMARY KEY,
  user_id VARCHAR(36),
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('info', 'warning', 'success', 'error') NOT NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  link VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create index for faster notification filtering
CREATE INDEX notifications_user_id_idx ON notifications(user_id);
CREATE INDEX notifications_is_read_idx ON notifications(is_read);

-- Company settings table
CREATE TABLE IF NOT EXISTS company_settings (
  id VARCHAR(36) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  logo_url VARCHAR(255),
  contact_email VARCHAR(150) NOT NULL,
  contact_phone VARCHAR(50),
  address TEXT,
  social_media JSON DEFAULT ('{}'),
  homepage_content JSON DEFAULT ('{}'),
  about_content JSON DEFAULT ('{}'),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create trigger for quote notifications
DELIMITER //

CREATE TRIGGER quote_notification_trigger
AFTER INSERT ON quotes
FOR EACH ROW
BEGIN
  INSERT INTO notifications (id, user_id, title, message, type, link)
  SELECT 
    UUID(),
    id,
    'New Quote Request',
    CONCAT('A new quote has been submitted by ', NEW.customer_name),
    'info',
    CONCAT('/admin/quotes/', NEW.id)
  FROM users
  WHERE role IN ('admin', 'sales');
END //

DELIMITER ;

-- Insert initial company settings
INSERT INTO company_settings (id, name, contact_email)
VALUES (UUID(), 'Amaris Heavy Machinery', 'contact@amaris-machinery.com');