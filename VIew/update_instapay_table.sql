USE ecommerce_db;


-- =========================
-- Instapay Transfer Proof
-- =========================
CREATE TABLE IF NOT EXISTS Instapay_Transfer (
    instapay_transfer_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL UNIQUE,
    order_id INT NOT NULL,
    sender_phone VARCHAR(30) NOT NULL,
    proof_image VARCHAR(255) NOT NULL,
    admin_status VARCHAR(50) DEFAULT 'Pending',
    admin_note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    FOREIGN KEY (payment_id) REFERENCES Payment(payment_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES Admin(admin_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_instapay_order (order_id),
    INDEX idx_instapay_status (admin_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- =========================
-- Visa Payment Details
-- Full card number and CVV are not stored.
-- =========================
CREATE TABLE IF NOT EXISTS Visa_Payment_Details (
    visa_payment_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL UNIQUE,
    order_id INT NOT NULL,
    cardholder_name VARCHAR(120) NOT NULL,
    card_last4 VARCHAR(4) NOT NULL,
    expiry_month TINYINT NOT NULL,
    expiry_year SMALLINT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES Payment(payment_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_visa_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
