-- backend/init.sql: create database, tables and sample data for HRM app
--  CREATE DATABASE IF NOT EXISTS hrm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--USE hrm_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL
);

-- tokens table: used to store bearer tokens issued at login
CREATE TABLE IF NOT EXISTS tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(128) NOT NULL,
  expires_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY (token)
);

CREATE TABLE IF NOT EXISTS employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  department_id INT,
  position_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS salaries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  amount DECIMAL(12,2),
  month YEAR(4),
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  date DATE,
  status VARCHAR(32),
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS leaves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  start_date DATE,
  end_date DATE,
  status VARCHAR(32) DEFAULT 'pending',
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT,
  score INT,
  note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- sample data
INSERT IGNORE INTO departments (id,name) VALUES (1,'Hành chính'),(2,'Kinh doanh'),(3,'Phát triển');
INSERT IGNORE INTO positions (id,name) VALUES (1,'Nhân viên'),(2,'Trưởng nhóm'),(3,'Quản lý');

INSERT IGNORE INTO employees (id,name,email,department_id,position_id) VALUES
(1,'Nguyen Van A','a@example.com',1,1),
(2,'Tran Thi B','b@example.com',2,2),
(3,'Le Van C','c@example.com',3,3);

-- Note: create a user manually after import, e.g. via PHP script using password_hash
