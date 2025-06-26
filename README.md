# Tracking System (PHP/MySQL)

## 📌 Overview
- A lightweight order tracking system built with PHP and MySQL that allows:

- Admin to add/update orders with multiple items

- Customers to track order status using a 6-character order ID

- Real-time updates on payment and delivery status

# Screenshots
---
- Admin Updating Page
![image](https://github.com/user-attachments/assets/dafb0ccc-66d7-47a1-a135-c5b3e7a86b82)

---
- Customer Tracking Page
![image](https://github.com/user-attachments/assets/b5289cea-c9a9-4367-9264-9450b555e23e)

---


## 🌟 Features
- Admin Panel

  - Add new orders with multiple items

  - Update existing orders

  - Set payment status (Not Paid/Half Paid/Full Paid/Wire Transfer)

  - Set delivery status (Pending/Processing/Out for Delivery/Delivered/Cancelled)

  - Add admin notes

- Customer Tracking

  - Simple order lookup by ID

  - Visual status indicators with color coding

  - View order items and quantities

  - See payment and delivery status

## Technical Features

- Secure MySQL database

- JSON storage for order items

- Responsive design

- Form validation

## 🛠️ Installation
  ### Prerequisites
- PHP 7.0+

- MySQL 5.7.8+ (for JSON support)

- Web server (Apache/Nginx)

## Setup Steps
  ### 1. Database Setup

```
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
```

  ### 2. File Structure

/tracking-system-php-mysql
- ├── add_order.php       # Admin order management
- ├── track_order.php     # Customer order tracking
- ├── db_connect.php      # Database connection
- ├── database/           # SQL exports
- │   └── tracking_system.sql
- └── README.md

### 3. Configure Database
  - Create db_connect.php with your credentials:

```
<?php
// db_connect.php
$servername = "localhost";
$username = "root"; // Your database username
$password = "";     // Your database password
$dbname = "tracking_system"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```


## 📥 Installation Steps
  ### - 1. Download and Install XAMPP
- Download XAMPP from the official website

- Run the installer with default settings

- Make sure to select these components during installation:

  - Apache

  - MySQL

  - PHP

  - phpMyAdmin

  ### 2. Setup the Project
- Download or clone the tracking-system-php-mysql project

```
https://github.com/khdxsohee/tracking-system-php-mysql.git
```


- Copy the project folder to:

- Windows: C:\xampp\htdocs\tracking-system-php-mysql

- macOS: /Applications/XAMPP/htdocs/tracking-system-php-mysql

- Linux: /opt/lampp/htdocs/tracking-system-php-mysql

  ### 3. Start XAMPP Services
- Open XAMPP Control Panel

- Start these services:

  - Apache

  - MySQL

  ### 4. Create the Database
- Open phpMyAdmin in your browser: http://localhost/phpmyadmin

- Create a new database named tracking_system

- Import the SQL file from your project's database folder

  ### 5. Configure Database Connection
- Edit db_connect.php in your project folder: (optional)

```
<?php
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password (blank)
$dbname = "tracking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

## 🚀 Running the Application
  ### - Access the Admin Panel
- Open your browser

```
http://localhost/tracking-system-php-mysql/add_order.php
```


- You can now:

  - Add new orders

  - Update existing orders

  - Manage order statuses

  ## Customer Tracking Page
- Customers can track orders at:

```
http://localhost/tracking-system/track_order.php
```


## 🚀 Usage
- Admin Access
  - Access add_order.php in your browser

  - Enter new order details or load existing order

  - Add multiple items with quantities and prices

  - Set payment and delivery status

  - Save order

- Customer Tracking
  - Customers visit track_order.php

  - Enter their 6-character order ID

  - View current order status with color indicators

## 🎨 Status Indicators
- Status Type	Options	Colors
- Payment	Not Paid, Half Paid, Full Paid, Wire Transfer	Red, Yellow, Green, Purple
- Delivery	Pending, Processing, Out for Delivery, Delivered, Cancelled	Yellow, Light Blue, Blue, Green, Red
## 📝 Notes
- Order IDs must be exactly 6 alphanumeric characters

- System automatically calculates order totals

- All timestamps are recorded automatically

## 🤝 Contributing
- Contributions welcome! Please fork the repository and submit pull requests.

## 📜 License
- MIT License - Free for personal and commercial use

### 📌 Tip: For production use, remember to:

- Disable PHP error display (display_errors = Off in php.ini)

- Use prepared statements throughout (already implemented)

- Implement proper authentication for admin access

- 🚀 Enjoy your tracking system! 🚀

