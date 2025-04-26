/*
  # Initial Database Setup for Amaris Heavy Machinery

  1. New Tables
    - `users` - Store user information with role-based access control
    - `categories` - Product categories
    - `products` - Product information and specifications
    - `quotes` - Customer quote requests
    - `notifications` - System notifications for users
    - `company_settings` - Company information and settings

  2. Security
    - Enable RLS on all tables
    - Policies for different user roles (admin, sales, executive)
*/

-- Users table for authentication and authorization
CREATE TABLE IF NOT EXISTS users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  email TEXT UNIQUE NOT NULL,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('admin', 'sales', 'executive')),
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- Categories table for product categorization
CREATE TABLE IF NOT EXISTS categories (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  description TEXT,
  image_url TEXT,
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE categories ENABLE ROW LEVEL SECURITY;

-- Products table for machinery and equipment
CREATE TABLE IF NOT EXISTS products (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  category_id UUID REFERENCES categories(id) NOT NULL,
  name TEXT NOT NULL,
  slug TEXT NOT NULL UNIQUE,
  short_description TEXT NOT NULL,
  full_description TEXT NOT NULL,
  specifications JSONB NOT NULL DEFAULT '{}',
  features JSONB NOT NULL DEFAULT '[]',
  price NUMERIC(12, 2),
  stock_status TEXT NOT NULL CHECK (stock_status IN ('in_stock', 'out_of_stock', 'pre_order')),
  main_image_url TEXT,
  gallery_images JSONB NOT NULL DEFAULT '[]',
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE INDEX IF NOT EXISTS products_category_id_idx ON products(category_id);

ALTER TABLE products ENABLE ROW LEVEL SECURITY;

-- Quotes table for customer inquiries
CREATE TABLE IF NOT EXISTS quotes (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  customer_name TEXT NOT NULL,
  customer_email TEXT NOT NULL,
  customer_phone TEXT,
  company_name TEXT,
  product_id UUID REFERENCES products(id),
  message TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'new' CHECK (status IN ('new', 'reviewed', 'contacted', 'closed')),
  assigned_to UUID REFERENCES users(id),
  created_at TIMESTAMPTZ DEFAULT now(),
  updated_at TIMESTAMPTZ DEFAULT now()
);

CREATE INDEX IF NOT EXISTS quotes_product_id_idx ON quotes(product_id);
CREATE INDEX IF NOT EXISTS quotes_status_idx ON quotes(status);
CREATE INDEX IF NOT EXISTS quotes_assigned_to_idx ON quotes(assigned_to);

ALTER TABLE quotes ENABLE ROW LEVEL SECURITY;

-- Notifications table for system notifications
CREATE TABLE IF NOT EXISTS notifications (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID REFERENCES users(id),
  title TEXT NOT NULL,
  message TEXT NOT NULL,
  type TEXT NOT NULL CHECK (type IN ('info', 'warning', 'success', 'error')),
  read BOOLEAN NOT NULL DEFAULT FALSE,
  link TEXT,
  created_at TIMESTAMPTZ DEFAULT now()
);

CREATE INDEX IF NOT EXISTS notifications_user_id_idx ON notifications(user_id);
CREATE INDEX IF NOT EXISTS notifications_read_idx ON notifications(read);

ALTER TABLE notifications ENABLE ROW LEVEL SECURITY;

-- Company settings table
CREATE TABLE IF NOT EXISTS company_settings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  logo_url TEXT,
  contact_email TEXT NOT NULL,
  contact_phone TEXT,
  address TEXT,
  social_media JSONB DEFAULT '{}',
  homepage_content JSONB DEFAULT '{}',
  about_content JSONB DEFAULT '{}',
  updated_at TIMESTAMPTZ DEFAULT now()
);

ALTER TABLE company_settings ENABLE ROW LEVEL SECURITY;

-- Row Level Security Policies

-- Users Policies
CREATE POLICY "Users can view their own profile"
  ON users
  FOR SELECT
  USING (auth.uid() = id);

CREATE POLICY "Admins can view all users"
  ON users
  FOR SELECT
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role = 'admin'
    )
  );

CREATE POLICY "Admins can insert users"
  ON users
  FOR INSERT
  WITH CHECK (
    auth.uid() IN (
      SELECT id FROM users WHERE role = 'admin'
    )
  );

CREATE POLICY "Admins can update users"
  ON users
  FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role = 'admin'
    )
  );

-- Categories Policies
CREATE POLICY "Anyone can view categories"
  ON categories
  FOR SELECT
  USING (true);

CREATE POLICY "Admins and sales can insert categories"
  ON categories
  FOR INSERT
  WITH CHECK (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'sales')
    )
  );

CREATE POLICY "Admins and sales can update categories"
  ON categories
  FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'sales')
    )
  );

CREATE POLICY "Admins can delete categories"
  ON categories
  FOR DELETE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role = 'admin'
    )
  );

-- Products Policies
CREATE POLICY "Anyone can view products"
  ON products
  FOR SELECT
  USING (true);

CREATE POLICY "Admins and sales can insert products"
  ON products
  FOR INSERT
  WITH CHECK (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'sales')
    )
  );

CREATE POLICY "Admins and sales can update products"
  ON products
  FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'sales')
    )
  );

CREATE POLICY "Admins can delete products"
  ON products
  FOR DELETE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role = 'admin'
    )
  );

-- Quotes Policies
CREATE POLICY "Anyone can insert quotes"
  ON quotes
  FOR INSERT
  WITH CHECK (true);

CREATE POLICY "Authenticated users with roles can view quotes"
  ON quotes
  FOR SELECT
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'sales', 'executive')
    )
  );

CREATE POLICY "Admins and sales can update quotes"
  ON quotes
  FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'sales')
    )
  );

-- Notifications Policies
CREATE POLICY "Users can view their own notifications"
  ON notifications
  FOR SELECT
  USING (auth.uid() = user_id);

CREATE POLICY "System can insert notifications"
  ON notifications
  FOR INSERT
  WITH CHECK (true);

CREATE POLICY "Users can update their own notifications"
  ON notifications
  FOR UPDATE
  USING (auth.uid() = user_id);

-- Company Settings Policies
CREATE POLICY "Anyone can view company settings"
  ON company_settings
  FOR SELECT
  USING (true);

CREATE POLICY "Admins and executives can update company settings"
  ON company_settings
  FOR UPDATE
  USING (
    auth.uid() IN (
      SELECT id FROM users WHERE role IN ('admin', 'executive')
    )
  );

-- Initial company settings
INSERT INTO company_settings (name, contact_email)
VALUES ('Amaris Heavy Machinery', 'contact@amaris-machinery.com');

-- Function to create a notification when a quote is created
CREATE OR REPLACE FUNCTION create_quote_notification()
RETURNS TRIGGER AS $$
BEGIN
  -- Create notification for all sales users
  INSERT INTO notifications (user_id, title, message, type, link)
  SELECT 
    id,
    'New Quote Request',
    'A new quote has been submitted by ' || NEW.customer_name,
    'info',
    '/admin/quotes/' || NEW.id
  FROM users
  WHERE role IN ('admin', 'sales');
  
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to create notifications for new quotes
CREATE TRIGGER quote_notification_trigger
AFTER INSERT ON quotes
FOR EACH ROW
EXECUTE FUNCTION create_quote_notification();