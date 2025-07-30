-- Travel Agency Management System Database Schema
-- Created for Diploma Capstone Project

CREATE DATABASE IF NOT EXISTS travel_agency;
USE travel_agency;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    address VARCHAR(200),
    user_type ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    destination VARCHAR(50) NOT NULL,
    transport_type ENUM('bus', 'train', 'flight') NOT NULL,
    departure_date DATE NOT NULL,
    return_date DATE,
    passengers INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    special_requests TEXT,
    booking_status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'net_banking', 'upi') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(100) UNIQUE,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user
INSERT INTO users (username, email, password, full_name, phone, user_type) VALUES 
('admin', 'admin@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '9876543210', 'admin');

-- Insert sample customer user
INSERT INTO users (username, email, password, full_name, phone, user_type) VALUES 
('demo', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo Customer', '8765432109', 'customer');

-- Insert sample bookings
INSERT INTO bookings (user_id, destination, transport_type, departure_date, passengers, total_price, booking_status) VALUES 
(2, 'goa', 'flight', '2021-12-25', 2, 12000.00, 'confirmed'),
(2, 'amritsar', 'train', '2021-11-15', 1, 6000.00, 'completed');

-- Insert sample payments
INSERT INTO payments (booking_id, payment_method, amount, transaction_id, payment_status) VALUES 
(1, 'credit_card', 12000.00, 'TXN12345678901', 'completed'),
(2, 'upi', 6000.00, 'TXN12345678902', 'completed');
