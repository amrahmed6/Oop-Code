CREATE DATABASE ecommerce_db;
USE ecommerce_db;

-- =========================
-- User
-- =========================
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    registration_date DATE
);

-- =========================
-- Admin
-- =========================
CREATE TABLE Admin (
    admin_id INT PRIMARY KEY,
    FOREIGN KEY (admin_id) REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================
-- Customer
-- =========================
CREATE TABLE Customer (
    customer_id INT PRIMARY KEY,
    name VARCHAR(100),
    delivery_address VARCHAR(255),
    account_status BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (customer_id) REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================
-- Product
-- =========================
CREATE TABLE Product (
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
    admin_id INT,

    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================
-- Coupon
-- =========================
CREATE TABLE Coupon (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount FLOAT NOT NULL,
    expiry_date DATE,
    admin_id INT,

    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================
-- Cart
-- =========================
CREATE TABLE Cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(100),

    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =========================
-- Cart_Item
-- =========================
CREATE TABLE Cart_Item (
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

    UNIQUE (cart_id, product_id)
);

-- =========================
-- Wishlist
-- =========================
CREATE TABLE Wishlist (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,

    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    UNIQUE (user_id, product_id)
);

-- =========================
-- Review
-- =========================
CREATE TABLE Review (
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

    CHECK (rating >= 0 AND rating <= 5)
);

-- =========================
-- Orders
-- =========================
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50),
    discount FLOAT DEFAULT 0,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    tracking_number VARCHAR(100),
    coupon_id INT,

    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (coupon_id) REFERENCES Coupon(coupon_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- =========================
-- Order_Item
-- =========================
CREATE TABLE Order_Item (
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
);

-- =========================
-- Payment
-- =========================
CREATE TABLE Payment (
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
);