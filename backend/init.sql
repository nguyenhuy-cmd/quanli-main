-- Human Resource Management System Database
-- Khởi tạo database và các bảng

-- Tạo database nếu chưa tồn tại
CREATE DATABASE IF NOT EXISTS hrm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hrm_system;

-- Bảng Users (Người dùng hệ thống)
CREATE TABLE IF NOT EXISTS users (
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

-- Bảng Departments (Phòng ban)
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    manager_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Positions (Vị trí công việc)
CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    department_id INT,
    base_salary DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_department (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Employees (Nhân viên)
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other') DEFAULT 'other',
    address TEXT,
    department_id INT,
    position_id INT,
    hire_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
    INDEX idx_employee_code (employee_code),
    INDEX idx_full_name (full_name),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cập nhật foreign key cho departments (manager_id)
ALTER TABLE departments ADD FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL;

-- Bảng Salaries (Lương)
CREATE TABLE IF NOT EXISTS salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    base_salary DECIMAL(15, 2) NOT NULL,
    allowance DECIMAL(15, 2) DEFAULT 0,
    bonus DECIMAL(15, 2) DEFAULT 0,
    deduction DECIMAL(15, 2) DEFAULT 0,
    total_salary DECIMAL(15, 2) GENERATED ALWAYS AS (base_salary + allowance + bonus - deduction) STORED,
    payment_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Attendance (Chấm công)
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    work_hours DECIMAL(4, 2) DEFAULT 0,
    status ENUM('present', 'absent', 'late', 'half_day', 'on_leave') DEFAULT 'present',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, date),
    INDEX idx_employee (employee_id),
    INDEX idx_date (date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Leaves (Nghỉ phép)
CREATE TABLE IF NOT EXISTS leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('annual', 'sick', 'unpaid', 'maternity', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_employee (employee_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng Performance Reviews (Đánh giá hiệu suất)
CREATE TABLE IF NOT EXISTS performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    review_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    strengths TEXT,
    weaknesses TEXT,
    goals TEXT,
    comments TEXT,
    status ENUM('draft', 'completed', 'approved') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_reviewer (reviewer_id),
    INDEX idx_review_date (review_date),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert dữ liệu mẫu

-- Users
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('HR Manager', 'hr@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr_manager'),
('Nguyễn Văn A', 'nva@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Trần Thị B', 'ttb@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Lê Văn C', 'lvc@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('Phạm Thị D', 'ptd@hrm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee');

-- Departments
INSERT INTO departments (name, description) VALUES
('Công nghệ thông tin', 'Phòng ban phát triển phần mềm và quản lý hệ thống'),
('Nhân sự', 'Phòng ban quản lý nhân sự và tuyển dụng'),
('Kế toán', 'Phòng ban quản lý tài chính và kế toán'),
('Marketing', 'Phòng ban marketing và truyền thông'),
('Kinh doanh', 'Phòng ban kinh doanh và phát triển thị trường');

-- Positions
INSERT INTO positions (title, description, department_id, base_salary) VALUES
('Giám đốc IT', 'Quản lý phòng IT', 1, 50000000),
('Lập trình viên Senior', 'Phát triển phần mềm', 1, 30000000),
('Lập trình viên Junior', 'Phát triển phần mềm', 1, 15000000),
('Trưởng phòng Nhân sự', 'Quản lý phòng Nhân sự', 2, 35000000),
('Nhân viên Nhân sự', 'Tuyển dụng và quản lý nhân sự', 2, 18000000),
('Kế toán trưởng', 'Quản lý kế toán', 3, 40000000),
('Kế toán viên', 'Xử lý sổ sách kế toán', 3, 20000000),
('Trưởng phòng Marketing', 'Quản lý Marketing', 4, 35000000),
('Nhân viên Marketing', 'Thực hiện chiến dịch marketing', 4, 18000000),
('Giám đốc Kinh doanh', 'Quản lý kinh doanh', 5, 45000000);

-- Employees
INSERT INTO employees (user_id, employee_code, full_name, email, phone, date_of_birth, gender, address, department_id, position_id, hire_date, status) VALUES
(3, 'EMP001', 'Nguyễn Văn A', 'nva@hrm.com', '0901234567', '1990-01-15', 'male', '123 Nguyễn Huệ, Q.1, TP.HCM', 1, 2, '2020-01-15', 'active'),
(4, 'EMP002', 'Trần Thị B', 'ttb@hrm.com', '0901234568', '1992-03-20', 'female', '456 Lê Lợi, Q.1, TP.HCM', 2, 5, '2020-03-01', 'active'),
(5, 'EMP003', 'Lê Văn C', 'lvc@hrm.com', '0901234569', '1988-07-10', 'male', '789 Trần Hưng Đạo, Q.5, TP.HCM', 3, 7, '2019-06-15', 'active'),
(6, 'EMP004', 'Phạm Thị D', 'ptd@hrm.com', '0901234570', '1995-11-25', 'female', '321 Võ Văn Tần, Q.3, TP.HCM', 4, 9, '2021-02-01', 'active');

-- Salaries
INSERT INTO salaries (employee_id, base_salary, allowance, bonus, deduction, payment_date, status) VALUES
(1, 30000000, 3000000, 5000000, 0, '2024-01-31', 'paid'),
(2, 18000000, 2000000, 2000000, 0, '2024-01-31', 'paid'),
(3, 20000000, 2500000, 3000000, 0, '2024-01-31', 'paid'),
(4, 18000000, 2000000, 1500000, 0, '2024-01-31', 'paid'),
(1, 30000000, 3000000, 6000000, 0, '2024-02-29', 'paid'),
(2, 18000000, 2000000, 2500000, 0, '2024-02-29', 'paid'),
(3, 20000000, 2500000, 3500000, 0, '2024-02-29', 'paid'),
(4, 18000000, 2000000, 2000000, 0, '2024-02-29', 'paid');

-- Attendance
INSERT INTO attendance (employee_id, date, check_in, check_out, work_hours, status) VALUES
(1, '2024-11-01', '08:00:00', '17:00:00', 8.0, 'present'),
(2, '2024-11-01', '08:15:00', '17:00:00', 7.75, 'late'),
(3, '2024-11-01', '08:00:00', '17:00:00', 8.0, 'present'),
(4, '2024-11-01', '08:00:00', '17:00:00', 8.0, 'present'),
(1, '2024-11-04', '08:00:00', '17:00:00', 8.0, 'present'),
(2, '2024-11-04', '08:00:00', '17:00:00', 8.0, 'present'),
(3, '2024-11-04', NULL, NULL, 0, 'absent'),
(4, '2024-11-04', '08:00:00', '17:00:00', 8.0, 'present');

-- Leaves
INSERT INTO leaves (employee_id, leave_type, start_date, end_date, days, reason, status, approved_by) VALUES
(1, 'annual', '2024-12-20', '2024-12-24', 5, 'Nghỉ lễ cuối năm', 'approved', 2),
(2, 'sick', '2024-11-05', '2024-11-05', 1, 'Ốm', 'approved', 2),
(3, 'annual', '2024-12-15', '2024-12-17', 3, 'Du lịch gia đình', 'pending', NULL),
(4, 'unpaid', '2024-11-10', '2024-11-12', 3, 'Việc gia đình', 'rejected', 2);

-- Performance Reviews
INSERT INTO performance_reviews (employee_id, reviewer_id, review_date, period_start, period_end, rating, strengths, weaknesses, goals, comments, status) VALUES
(1, 2, '2024-06-30', '2024-01-01', '2024-06-30', 5, 'Kỹ năng lập trình tốt, chủ động trong công việc', 'Cần cải thiện kỹ năng giao tiếp', 'Học thêm công nghệ mới, mentoring junior', 'Nhân viên xuất sắc', 'completed'),
(2, 2, '2024-06-30', '2024-01-01', '2024-06-30', 4, 'Nhiệt tình, cẩn thận', 'Chưa chủ động trong công việc', 'Tăng sự chủ động, học hỏi thêm', 'Nhân viên tốt', 'completed'),
(3, 2, '2024-06-30', '2024-01-01', '2024-06-30', 4, 'Kỹ năng kế toán vững vàng', 'Cần cải thiện tốc độ làm việc', 'Tối ưu quy trình làm việc', 'Hoàn thành tốt nhiệm vụ', 'completed'),
(4, 2, '2024-06-30', '2024-01-01', '2024-06-30', 5, 'Sáng tạo, năng động', 'Đôi khi thiếu tập trung', 'Phát triển chiến lược marketing mới', 'Nhân viên xuất sắc', 'completed');

-- Views để báo cáo

-- View: Thông tin nhân viên đầy đủ
CREATE OR REPLACE VIEW vw_employee_details AS
SELECT 
    e.id,
    e.employee_code,
    e.full_name,
    e.email,
    e.phone,
    e.date_of_birth,
    e.gender,
    e.address,
    d.name AS department_name,
    p.title AS position_title,
    p.base_salary,
    e.hire_date,
    e.status,
    TIMESTAMPDIFF(YEAR, e.hire_date, CURDATE()) AS years_of_service
FROM employees e
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN positions p ON e.position_id = p.id;

-- View: Báo cáo lương theo tháng
CREATE OR REPLACE VIEW vw_monthly_salary_report AS
SELECT 
    YEAR(s.payment_date) AS year,
    MONTH(s.payment_date) AS month,
    e.employee_code,
    e.full_name,
    d.name AS department_name,
    s.base_salary,
    s.allowance,
    s.bonus,
    s.deduction,
    s.total_salary,
    s.status
FROM salaries s
JOIN employees e ON s.employee_id = e.id
LEFT JOIN departments d ON e.department_id = d.id;

-- View: Báo cáo chấm công
CREATE OR REPLACE VIEW vw_attendance_report AS
SELECT 
    e.employee_code,
    e.full_name,
    d.name AS department_name,
    a.date,
    a.check_in,
    a.check_out,
    a.work_hours,
    a.status
FROM attendance a
JOIN employees e ON a.employee_id = e.id
LEFT JOIN departments d ON e.department_id = d.id;

-- View: Thống kê hiệu suất
CREATE OR REPLACE VIEW vw_performance_stats AS
SELECT 
    e.employee_code,
    e.full_name,
    d.name AS department_name,
    AVG(pr.rating) AS avg_rating,
    COUNT(pr.id) AS review_count,
    MAX(pr.review_date) AS last_review_date
FROM employees e
LEFT JOIN performance_reviews pr ON e.id = pr.employee_id
LEFT JOIN departments d ON e.department_id = d.id
GROUP BY e.id, e.employee_code, e.full_name, d.name;

-- Indexes để tối ưu performance
CREATE INDEX idx_salaries_date ON salaries(payment_date);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_leaves_dates ON leaves(start_date, end_date);
CREATE INDEX idx_reviews_date ON performance_reviews(review_date);

-- Completed!
SELECT 'Database initialized successfully!' AS message;
