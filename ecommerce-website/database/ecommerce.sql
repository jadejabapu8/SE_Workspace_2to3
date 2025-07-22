-- E-commerce Database Schema
CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

-- Users table for customer accounts
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin table for admin accounts
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    short_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    image VARCHAR(255),
    gallery TEXT, -- JSON array of image paths
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    weight DECIMAL(5,2),
    dimensions VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Shopping cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert default admin user (password: 'password')
INSERT INTO admin (username, email, password, first_name, last_name, role) VALUES 
('admin', 'admin@ecommerce.com', '$2y$10$9kWQFKPqQrBgYBpA3VQ7wOtQQQkm7qD7M1xF8sBjNz6DG3i1Kn0y6', 'Admin', 'User', 'super_admin');

-- Insert sample categories
INSERT INTO categories (name, description, image, status) VALUES 
('Electronics', 'Electronic devices and gadgets', 'electronics.jpg', 'active'),
('Clothing', 'Fashion and apparel', 'clothing.jpg', 'active'),
('Books', 'Books and literature', 'books.jpg', 'active'),
('Home & Garden', 'Home improvement and garden supplies', 'home-garden.jpg', 'active'),
('Sports', 'Sports equipment and accessories', 'sports.jpg', 'active');

-- Insert sample products
INSERT INTO products (name, description, short_description, price, sale_price, sku, stock_quantity, category_id, image, featured, weight, dimensions, status) VALUES 
('Smartphone Pro Max', 'Latest smartphone with advanced features and high-quality camera. Features include 6.1-inch display, triple camera system, A15 Bionic chip, and 5G capability.', 'Premium smartphone with excellent performance', 999.99, 899.99, 'PHONE001', 50, 1, 'smartphone.jpg', TRUE, 0.25, '6.1 x 3.0 x 0.3 inches', 'active'),
('Wireless Headphones', 'Noise-cancelling wireless headphones with superior sound quality. Up to 30 hours battery life and comfortable over-ear design.', 'Premium wireless headphones', 299.99, 249.99, 'HEAD001', 100, 1, 'headphones.jpg', TRUE, 0.75, '7.5 x 6.5 x 3.2 inches', 'active'),
('Cotton T-Shirt', 'Comfortable 100% cotton t-shirt available in multiple colors. Pre-shrunk and machine washable. Perfect for casual wear.', 'Soft cotton t-shirt', 29.99, NULL, 'SHIRT001', 200, 2, 'tshirt.jpg', FALSE, 0.2, 'Various sizes', 'active'),
('Programming Book', 'Complete guide to modern web development. Covers HTML, CSS, JavaScript, PHP, and database integration with practical examples.', 'Web development tutorial book', 49.99, 39.99, 'BOOK001', 75, 3, 'programming-book.jpg', FALSE, 1.5, '9 x 7 x 1.5 inches', 'active'),
('Gaming Chair', 'Ergonomic gaming chair with lumbar support. Adjustable height, tilt function, and premium leather upholstery for ultimate comfort.', 'Comfortable gaming chair', 299.99, 249.99, 'CHAIR001', 25, 4, 'gaming-chair.jpg', TRUE, 45.0, '26 x 26 x 48 inches', 'active'),
('Laptop Pro', 'High-performance laptop for professionals. Intel i7 processor, 16GB RAM, 512GB SSD, and 15.6-inch 4K display.', 'Professional laptop with powerful specs', 1299.99, 1199.99, 'LAPTOP001', 30, 1, 'laptop.jpg', TRUE, 4.2, '14.1 x 9.8 x 0.7 inches', 'active'),
('Bluetooth Speaker', 'Portable bluetooth speaker with 360-degree sound. Waterproof design with 12-hour battery life.', 'Portable wireless speaker', 79.99, NULL, 'SPEAKER001', 80, 1, 'speaker.jpg', FALSE, 1.5, '6 x 6 x 3 inches', 'active'),
('Running Shoes', 'Professional running shoes with advanced cushioning technology. Lightweight and breathable design for optimal performance.', 'High-performance running shoes', 129.99, 99.99, 'SHOES001', 150, 5, 'running-shoes.jpg', TRUE, 1.2, 'Various sizes', 'active');

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_cart_user ON cart(user_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_reviews_product ON reviews(product_id);
CREATE INDEX idx_reviews_user ON reviews(user_id);