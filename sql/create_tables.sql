-- Create database and users table for dhsbord
CREATE DATABASE IF NOT EXISTS dhsbord CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE dhsbord;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  fullname VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert demo user: admin / secret
INSERT IGNORE INTO users (username, password, fullname) VALUES (
  'admin',
  '$2y$10$e0NR6Y9bHkVnA8a3QkqQcuZ1HqTgG6Q5b5hQJqQf8xwFvLU0XyK1G',
  'Administrator'
);