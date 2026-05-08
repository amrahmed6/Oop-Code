-- BonnaVerse E-commerce Database
-- Import this file in phpMyAdmin.
-- Default test login after import:
--   Admin:    admin@bonnaverse.com / 123456
--   Customer: customer@bonnaverse.com / 123456

CREATE DATABASE IF NOT EXISTS ecommerce_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ecommerce_db;

SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- Users
-- =========================
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    registration_date DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Admin
-- =========================
CREATE TABLE IF NOT EXISTS Admin (
    admin_id INT PRIMARY KEY,
    FOREIGN KEY (admin_id) REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Customer
-- =========================
CREATE TABLE IF NOT EXISTS Customer (
    customer_id INT PRIMARY KEY,
    name VARCHAR(100),
    delivery_address VARCHAR(255),
    account_status BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (customer_id) REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Product
-- =========================
CREATE TABLE IF NOT EXISTS Product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    brand VARCHAR(100),
    category VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    stock_count INT DEFAULT 0,
    image VARCHAR(255),
    average_rating FLOAT DEFAULT 0,
    review_count INT DEFAULT 0,
    admin_id INT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_product_brand (brand),
    INDEX idx_product_category (category),
    INDEX idx_product_price (price),
    INDEX idx_product_rating (average_rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Coupon
-- =========================
CREATE TABLE IF NOT EXISTS Coupon (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount FLOAT NOT NULL,
    expiry_date DATE,
    admin_id INT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Cart
-- =========================
CREATE TABLE IF NOT EXISTS Cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_cart_user (user_id),
    INDEX idx_cart_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Cart_Item
-- =========================
CREATE TABLE IF NOT EXISTS Cart_Item (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES Cart(cart_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE KEY uq_cart_product (cart_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Wishlist
-- =========================
CREATE TABLE IF NOT EXISTS Wishlist (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE KEY uq_wishlist_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Review
-- =========================
CREATE TABLE IF NOT EXISTS Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating FLOAT NOT NULL,
    comment TEXT,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CHECK (rating >= 1 AND rating <= 5),
    INDEX idx_review_product (product_id),
    INDEX idx_review_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Orders
-- =========================
CREATE TABLE IF NOT EXISTS Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Processing',
    discount DECIMAL(10,2) DEFAULT 0,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    tracking_number VARCHAR(100),
    coupon_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES Coupon(coupon_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Order_Item
-- =========================
CREATE TABLE IF NOT EXISTS Order_Item (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Payment
-- =========================
CREATE TABLE IF NOT EXISTS Payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status VARCHAR(50),
    payment_date DATE,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- Safe test data
-- =========================
INSERT INTO Users (email, password, first_name, last_name, phone, registration_date)
VALUES
('admin@bonnaverse.com', '$2y$12$rVv5DWYbMAOyhS7ReY.bXO3EUhdWIc6bmRrtLjznlHjXtSyFLHu3.', 'Admin', 'User', '01000000000', CURDATE())
ON DUPLICATE KEY UPDATE email = email;

SET @admin_id := (SELECT user_id FROM Users WHERE email = 'admin@bonnaverse.com' LIMIT 1);
INSERT INTO Admin (admin_id)
VALUES (@admin_id)
ON DUPLICATE KEY UPDATE admin_id = admin_id;

INSERT INTO Users (email, password, first_name, last_name, phone, registration_date)
VALUES
('customer@bonnaverse.com', '$2y$12$rVv5DWYbMAOyhS7ReY.bXO3EUhdWIc6bmRrtLjznlHjXtSyFLHu3.', 'Test', 'Customer', '01111111111', CURDATE())
ON DUPLICATE KEY UPDATE email = email;

SET @customer_id := (SELECT user_id FROM Users WHERE email = 'customer@bonnaverse.com' LIMIT 1);
INSERT INTO Customer (customer_id, name, delivery_address, account_status)
VALUES (@customer_id, 'Test Customer', 'Cairo, Egypt', TRUE)
ON DUPLICATE KEY UPDATE customer_id = customer_id;

INSERT INTO Coupon (code, discount, expiry_date, admin_id)
SELECT 'SAVE10', 10, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Coupon WHERE code = 'SAVE10');

INSERT INTO Product (name, description, brand, category, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Nike Air Max 270', 'Comfortable lifestyle sneakers for daily wear.', 'Nike', 'Sneakers', 120.00, 12,
'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop', 4.5, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Nike Air Max 270');

INSERT INTO Product (name, description, brand, category, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Adidas Street Hoodie', 'Soft streetwear hoodie with simple modern design.', 'Adidas', 'Hoodies', 65.00, 18,
'https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=900&auto=format&fit=crop', 4.2, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Adidas Street Hoodie');

INSERT INTO Product (name, description, brand, category, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Jordan Retro High', 'Premium basketball-inspired sneakers.', 'Jordan', 'Sneakers', 180.00, 6,
'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=900&auto=format&fit=crop', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Jordan Retro High');

INSERT INTO Product (name, description, brand, category, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Supreme Logo T-Shirt', 'Simple cotton t-shirt for casual outfits.', 'Supreme', 'T-Shirts', 40.00, 25,
'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=900&auto=format&fit=crop', 4.1, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Supreme Logo T-Shirt');

INSERT INTO Product (name, description, brand, category, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'New Balance 550', 'Classic sneakers with clean retro style.', 'New Balance', 'Sneakers', 135.00, 4,
'https://images.unsplash.com/photo-1605348532760-6753d2c43329?q=80&w=900&auto=format&fit=crop', 4.6, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'New Balance 550');
