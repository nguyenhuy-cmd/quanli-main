-- =====================================================
-- HRM System Database - For Hosting
-- Fixed version for InfinityFree hosting
-- =====================================================

-- Drop existing tables in correct order (respect foreign keys)
DROP TABLE IF EXISTS performance_reviews;
DROP TABLE IF EXISTS leaves;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS salaries;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS positions;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS users;

-- =====================================================
-- CREATE TABLES
-- =====================================================

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr_manager', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    manager_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: positions
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    department_id INT DEFAULT NULL,
    base_salary DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employees
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    hire_date DATE,
    employment_status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    department_id INT DEFAULT NULL,
    position_id INT DEFAULT NULL,
    salary DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
    INDEX idx_employee_code (employee_code),
    INDEX idx_email (email),
    INDEX idx_department (department_id),
    INDEX idx_position (position_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: salaries
CREATE TABLE salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    base_salary DECIMAL(15, 2) DEFAULT 0,
    allowance DECIMAL(15, 2) DEFAULT 0,
    bonus DECIMAL(15, 2) DEFAULT 0,
    deduction DECIMAL(15, 2) DEFAULT 0,
    total_salary DECIMAL(15, 2) GENERATED ALWAYS AS (base_salary + allowance + bonus - deduction) STORED,
    payment_date DATE NOT NULL,
    payment_month VARCHAR(7) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_payment_month (payment_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: attendance
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME DEFAULT NULL,
    check_out TIME DEFAULT NULL,
    work_hours DECIMAL(5, 2) DEFAULT 0,
    overtime_hours DECIMAL(5, 2) DEFAULT 0,
    status ENUM('present', 'absent', 'late', 'early_leave', 'on_leave') DEFAULT 'present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (employee_id, date),
    INDEX idx_date (date),
    INDEX idx_employee_date (employee_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: leaves
CREATE TABLE leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('annual', 'sick', 'maternity', 'paternity', 'unpaid', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT DEFAULT NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: performance_reviews
CREATE TABLE performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    reviewer_id INT DEFAULT NULL,
    review_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    strengths TEXT,
    weaknesses TEXT,
    goals TEXT,
    comments TEXT,
    status ENUM('draft', 'completed', 'acknowledged') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_review_date (review_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT SAMPLE DATA
-- =====================================================

-- Users (password: password)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('HR Manager', 'hr@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr_manager'),
('Nguyen Van A', 'nva@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Tran Thi B', 'ttb@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Le Van C', 'lvc@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee');

-- Departments
INSERT INTO departments (name, description, manager_id) VALUES
('Information Technology', 'Software development and system management', NULL),
('Human Resources', 'HR management and recruitment', NULL),
('Accounting', 'Financial management and accounting', NULL),
('Marketing', 'Marketing and communications', NULL),
('Sales', 'Sales and business development', NULL);

-- Positions
INSERT INTO positions (title, description, department_id, base_salary) VALUES
('IT Director', 'Manage IT department', 1, 50000000),
('Senior Developer', 'Software development', 1, 30000000),
('Junior Developer', 'Software development', 1, 15000000),
('HR Manager', 'Manage HR department', 2, 35000000),
('HR Staff', 'Recruitment and HR management', 2, 18000000),
('Chief Accountant', 'Manage accounting', 3, 40000000),
('Accountant', 'Process accounting books', 3, 20000000),
('Marketing Manager', 'Manage Marketing', 4, 35000000),
('Marketing Staff', 'Execute marketing campaigns', 4, 18000000),
('Sales Director', 'Manage sales', 5, 45000000);

-- Employees
INSERT INTO employees (user_id, employee_code, full_name, email, phone, date_of_birth, gender, address, department_id, position_id, hire_date, employment_status, salary) VALUES
(3, 'EMP001', 'Nguyen Van A', 'nva@hrm.com', '0901234567', '1990-01-15', 'male', '123 Nguyen Hue, Q.1, HCMC', 1, 2, '2020-01-15', 'active', 30000000),
(4, 'EMP002', 'Tran Thi B', 'ttb@hrm.com', '0901234568', '1992-03-20', 'female', '456 Le Loi, Q.1, HCMC', 2, 5, '2020-03-01', 'active', 18000000),
(5, 'EMP003', 'Le Van C', 'lvc@hrm.com', '0901234569', '1988-07-10', 'male', '789 Tran Hung Dao, Q.5, HCMC', 3, 7, '2019-06-15', 'active', 20000000);

-- Salaries
INSERT INTO salaries (employee_id, base_salary, allowance, bonus, deduction, payment_date, payment_month, status) VALUES
(1, 30000000, 3000000, 5000000, 0, '2024-11-30', '2024-11', 'paid'),
(2, 18000000, 2000000, 2000000, 0, '2024-11-30', '2024-11', 'paid'),
(3, 20000000, 2500000, 3000000, 0, '2024-11-30', '2024-11', 'paid');

-- Attendance
INSERT INTO attendance (employee_id, date, check_in, check_out, work_hours, status) VALUES
(1, '2024-11-13', '08:00:00', '17:00:00', 8.0, 'present'),
(2, '2024-11-13', '08:15:00', '17:00:00', 7.75, 'late'),
(3, '2024-11-13', '08:00:00', '17:00:00', 8.0, 'present');

-- Leaves
INSERT INTO leaves (employee_id, leave_type, start_date, end_date, days, reason, status, approved_by) VALUES
(1, 'annual', '2024-12-20', '2024-12-24', 5, 'Year-end vacation', 'approved', 2),
(2, 'sick', '2024-11-05', '2024-11-05', 1, 'Sick', 'approved', 2);

-- Performance Reviews
INSERT INTO performance_reviews (employee_id, reviewer_id, review_date, period_start, period_end, rating, strengths, weaknesses, goals, comments, status) VALUES
(1, 2, '2024-06-30', '2024-01-01', '2024-06-30', 5, 'Good programming skills, proactive', 'Need to improve communication', 'Learn new technologies', 'Excellent employee', 'completed'),
(2, 2, '2024-06-30', '2024-01-01', '2024-06-30', 4, 'Enthusiastic, careful', 'Not proactive enough', 'Increase initiative', 'Good employee', 'completed');

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_leaves_dates ON leaves(start_date, end_date);
CREATE INDEX idx_reviews_date ON performance_reviews(review_date);

-- =====================================================
-- COMPLETED
-- =====================================================

SELECT 'Database setup completed successfully!' AS message;
