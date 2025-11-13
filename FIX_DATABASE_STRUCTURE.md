# 🚨 LỖI DATABASE STRUCTURE - HƯỚNG DẪN FIX

## ❌ VẤN ĐỀ

Database trên hosting **CHƯA ĐƯỢC IMPORT ĐÚNG**. Nhiều columns và tables bị thiếu:

### Missing Columns:
- `employees.full_name` → lỗi departments query
- `employees.employee_code` → lỗi salaries, attendance, leaves
- `employees.employment_status` → lỗi positions query
- `positions.title` → lỗi employees query
- `performance_reviews` table → không tồn tại

### Lỗi cụ thể:
```
Column not found: 1054 Unknown column 'e.full_name' in 'SELECT'
Column not found: 1054 Unknown column 'e.employee_code' in 'SELECT'
Column not found: 1054 Unknown column 'e.employment_status' in 'ON'
Column not found: 1054 Unknown column 'p.title' in 'SELECT'
Table doesn't exist: performance_reviews
```

## ✅ GIẢI PHÁP

### Option 1: Dùng Fix Script (RECOMMENDED)

1. **Upload file fix:**
   Upload `fix-database-structure.php` lên hosting root

2. **Truy cập:**
   ```
   https://your-domain.rf.gd/fix-database-structure.php
   ```

3. **Chọn một trong hai:**
   - **Download init.sql**: Tải về và import thủ công qua phpMyAdmin
   - **Auto Fix**: Click nút đỏ để tự động drop & recreate tables (⚠️ MẤT DATA)

### Option 2: Import Thủ Công qua phpMyAdmin

1. **Login phpMyAdmin trên hosting:**
   - Vào cPanel → phpMyAdmin
   - Chọn database: `if0_40315513_hrm_db`

2. **Backup data hiện tại (nếu có):**
   - Tab "Export" → Go
   - Download file .sql

3. **Drop các tables cũ:**
   ```sql
   DROP TABLE IF EXISTS performance_reviews;
   DROP TABLE IF EXISTS leaves;
   DROP TABLE IF EXISTS attendance;
   DROP TABLE IF EXISTS salaries;
   DROP TABLE IF EXISTS employees;
   DROP TABLE IF EXISTS positions;
   DROP TABLE IF EXISTS departments;
   DROP TABLE IF EXISTS users;
   ```

4. **Import init.sql:**
   - Tab "Import"
   - Choose File → chọn `backend/init.sql`
   - Click "Go"

5. **Verify:**
   - Check tất cả tables đã được tạo
   - Check columns trong mỗi table

### Option 3: Run SQL Directly

Copy SQL từ `backend/init.sql` và run trong phpMyAdmin SQL tab.

**Lưu ý:** 
- Bỏ dòng `CREATE DATABASE` và `USE hrm_system`
- Database đã tồn tại rồi, chỉ cần CREATE TABLES

## 📋 Kiểm Tra Database Structure Đúng

### Users Table:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr_manager', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Employees Table:
```sql
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    employee_code VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    hire_date DATE,
    employment_status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    department_id INT,
    position_id INT,
    salary DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL
);
```

### Departments Table:
```sql
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    manager_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Positions Table:
```sql
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    department_id INT,
    base_salary DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);
```

### Performance Reviews Table:
```sql
CREATE TABLE performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    reviewer_id INT,
    review_date DATE NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    strengths TEXT,
    areas_for_improvement TEXT,
    goals TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES employees(id) ON DELETE SET NULL
);
```

## 🧪 Test Sau Khi Fix

1. **Test API:**
   ```
   https://your-domain.rf.gd/fix-database-structure.php
   ```
   → Phải thấy: ✅ All required columns exist!

2. **Test App:**
   ```
   https://your-domain.rf.gd/
   ```
   → Không còn lỗi "Column not found"
   → Các modules load được

3. **Test từng module:**
   - Dashboard → OK
   - Employees → OK
   - Departments → OK
   - Positions → OK
   - Salaries → OK
   - Attendance → OK
   - Leaves → OK
   - Performance → OK

## 💡 Lưu Ý Quan Trọng

### Tại sao lỗi này xảy ra?

1. **Chưa import database:**
   - Database được tạo nhưng chưa có tables
   - Hoặc import không đầy đủ

2. **Import sai file:**
   - Import file SQL cũ
   - Thiếu các columns mới

3. **Version mismatch:**
   - Code mới nhưng dùng database cũ
   - Cần migrate schema

### Cách tránh lỗi trong tương lai:

1. **Luôn backup trước khi update:**
   ```bash
   # Export database trước
   mysqldump -u user -p database > backup.sql
   ```

2. **Check database version:**
   - Thêm table `schema_version`
   - Track migrations

3. **Test local trước:**
   - Test đầy đủ trên localhost
   - Đảm bảo database structure match

4. **Document changes:**
   - Ghi lại mọi thay đổi database
   - Tạo migration files

## 📤 Files Upload Checklist

- [x] `fix-database-structure.php` → root folder
- [x] `backend/init.sql` → backend folder  
- [x] All other backend files

## 🆘 Nếu Vẫn Lỗi

1. **Check trong phpMyAdmin:**
   - Verify all tables exist
   - Check columns trong employees table
   - Run: `SHOW TABLES;`
   - Run: `DESCRIBE employees;`

2. **Check error log:**
   - `error_log.txt` trên hosting
   - PHP errors trong cPanel

3. **Try debug:**
   ```
   https://your-domain.rf.gd/debug-api-error.html
   ```

4. **Contact support:**
   - InfinityFree support nếu không import được
   - Họ có thể help import database

---

**Tóm lại: Database structure chưa được setup đúng. Cần import lại `init.sql` đầy đủ!** 🎯
