CREATE TABLE orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(6) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    order_details JSON, -- Use TEXT if MySQL version is older than 5.7.8
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('not_paid', 'half_paid', 'full_paid', 'wire_transfer') DEFAULT 'not_paid',
    delivery_status ENUM('pending', 'processing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);