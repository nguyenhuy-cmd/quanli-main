# CRITICAL BUGFIX - Backend Controllers
**Ngày:** November 11, 2025  
**Version:** 5.4  
**Priority:** 🔴 HIGH - Lỗi SQL ngăn chặn tạo dữ liệu mới

---

## 🐛 2 LỖI NGHIÊM TRỌNG ĐÃ SỬA

### **1. LeaveController - Unknown Column 'days'** ✅

**Lỗi SQL:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'days' in 'field list'
POST /api.php?resource=leaves 500 (Internal Server Error)
```

**Nguyên nhân:**  
Controller tính toán và gán `$data['days']` nhưng database có column `total_days`

**Code cũ (SAI):**
```php
// Line 72 - LeaveController.php
$data['days'] = $this->model->calculateDays($data['start_date'], $data['end_date']);
```

**Code mới (ĐÚNG):**
```php
// Calculate days if not provided
if (!isset($data['total_days'])) {
    $data['total_days'] = $this->model->calculateDays($data['start_date'], $data['end_date']);
}

// Set default status if not provided
if (!isset($data['leave_status'])) {
    $data['leave_status'] = 'pending';
}
```

**File sửa:**
- `backend/controllers/LeaveController.php` - method `create()`

**Kết quả:**
- ✅ Tạo đơn nghỉ phép thành công
- ✅ Column `total_days` được insert đúng
- ✅ Column `leave_status` có default value 'pending'

---

### **2. PerformanceController - Field 'review_date' Required** ✅

**Lỗi Validation:**
```
Error: Field 'review_date' is required
POST /api.php?resource=performance 400 (Bad Request)
```

**Nguyên nhân:**  
Controller validate `review_date` nhưng:
- Frontend gửi: `review_period_start`, `review_period_end`
- Database có: `review_period_start`, `review_period_end` (KHÔNG có `review_date`)
- init.sql file CŨ: `review_date`, `period_start`, `period_end` (⚠️ Schema đã thay đổi!)

**Code cũ (SAI):**
```php
// Line 63 - PerformanceController.php
$this->validateRequired($data, [
    'employee_id', 
    'review_date',      // ❌ Column không tồn tại
    'period_start',     // ❌ Tên sai
    'period_end',       // ❌ Tên sai
    'rating'
]);
```

**Code mới (ĐÚNG):**
```php
$this->validateRequired($data, [
    'employee_id', 
    'review_period_start',  // ✅ Tên đúng
    'review_period_end',    // ✅ Tên đúng
    'rating'
]);

// Set reviewer_id from current user if not provided
if (!isset($data['reviewer_id'])) {
    $user = $this->requireAuth();
    $data['reviewer_id'] = $user['id'];
}

// Set default status if not provided
if (!isset($data['review_status'])) {
    $data['review_status'] = 'draft';
}
```

**File sửa:**
- `backend/controllers/PerformanceController.php` - method `create()`

**Kết quả:**
- ✅ Tạo đánh giá thành công
- ✅ Validate đúng fields: `review_period_start`, `review_period_end`
- ✅ Auto set `reviewer_id` từ user đang login (nếu chưa có)
- ✅ Auto set `review_status = 'draft'` (default)

---

## 📊 DATABASE SCHEMA - SỰ THẬT

### Bảng `leaves`
```sql
CREATE TABLE leaves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    leave_type ENUM('annual','sick','unpaid','maternity','other'),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,           -- ✅ KHÔNG PHẢI 'days'
    reason TEXT,
    leave_status ENUM(...),            -- ✅ KHÔNG PHẢI 'status'
    approved_by INT,
    approved_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Bảng `performance_reviews`
```sql
CREATE TABLE performance_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    -- ❌ KHÔNG CÓ 'review_date'
    review_period_start DATE NOT NULL,  -- ✅ Có field này
    review_period_end DATE NOT NULL,    -- ✅ Có field này
    rating DECIMAL(3,2),
    technical_skills DECIMAL(3,2),
    communication_skills DECIMAL(3,2),
    teamwork DECIMAL(3,2),
    productivity DECIMAL(3,2),
    strengths TEXT,
    weaknesses TEXT,
    recommendations TEXT,
    review_status ENUM('draft','completed','acknowledged'),  -- ✅ KHÔNG PHẢI 'status'
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**⚠️ LƯU Ý:** File `backend/init.sql` CŨ và KHÔNG KHỚP với database thực tế!

---

## 🔧 CÁCH KIỂM TRA DATABASE THỰC TẾ

**Script PHP kiểm tra:**
```php
<?php
require_once 'backend/config/config.php';

$tables = ['leaves', 'performance_reviews'];
foreach ($tables as $table) {
    echo "\n=== $table ===\n";
    $stmt = $pdo->query("DESCRIBE $table");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
```

**Hoặc dùng MySQL:**
```sql
DESCRIBE leaves;
DESCRIBE performance_reviews;
```

---

## 📦 VERSION UPDATE

**Cache Busting:**
- `index.html`: v5.3 → **v5.4**
- Backend không cần version (PHP tự reload)

**Refresh browser:**
```
Ctrl + Shift + R
```

---

## ✅ KIỂM TRA SAU KHI SỬA

### Test Case 1: Leave Request
**Bước:**
1. Login vào hệ thống
2. Menu → Nghỉ phép
3. Click "Tạo đơn nghỉ phép"
4. Điền form:
   - Nhân viên: Chọn 1 người
   - Loại: Annual
   - Từ ngày: 2025-11-15
   - Đến ngày: 2025-11-17
   - Số ngày: 3
   - Lý do: "Test leave request"
5. Click Submit

**Kết quả mong đợi:**
- ✅ Không có lỗi SQL
- ✅ Toast hiển thị "Tạo đơn nghỉ phép thành công"
- ✅ Table refresh với record mới
- ✅ Database có 1 row mới trong `leaves` với `total_days = 3`

**Verify SQL:**
```sql
SELECT * FROM leaves ORDER BY id DESC LIMIT 1;
-- Kiểm tra: total_days = 3, leave_status = 'pending'
```

---

### Test Case 2: Performance Review
**Bước:**
1. Menu → Đánh giá hiệu suất
2. Click "Thêm đánh giá"
3. Điền form:
   - Nhân viên: Chọn 1 người
   - Người đánh giá (ID): 1
   - Từ ngày: 2025-01-01
   - Đến ngày: 2025-06-30
   - Điểm tổng: 4.5
   - Các skills: 4.0
   - Trạng thái: Hoàn thành
4. Click Submit

**Kết quả mong đợi:**
- ✅ Không có lỗi "Field 'review_date' is required"
- ✅ Toast hiển thị "Thêm đánh giá thành công"
- ✅ Table refresh với review mới
- ✅ Database có record với `review_period_start`, `review_period_end`, `review_status = 'completed'`

**Verify SQL:**
```sql
SELECT 
    id, 
    employee_id, 
    reviewer_id,
    review_period_start, 
    review_period_end, 
    rating,
    review_status
FROM performance_reviews 
ORDER BY id DESC 
LIMIT 1;
```

---

## 🎯 ROOT CAUSE ANALYSIS

### Tại sao lỗi xảy ra?

1. **Inconsistent Schema:**
   - File `init.sql` cũ dùng: `days`, `review_date`, `period_start`
   - Database thực tế: `total_days`, `review_period_start`, `review_period_end`
   - Models đã update đúng
   - ❌ Controllers CHƯA update → Gây lỗi!

2. **Missing Validation:**
   - Controllers validate theo init.sql cũ
   - Không sync với database thực tế
   - Không có automated tests để phát hiện sớm

3. **Lesson Learned:**
   - ⚠️ LUÔN DÙNG `DESCRIBE table` kiểm tra schema thực tế
   - ⚠️ KHÔNG tin vào init.sql file nếu database đã migration
   - ⚠️ CẦN có integration tests cho CRUD operations

---

## 🔍 CÁC FILE ĐÃ SỬA

| File | Changes | Lines |
|------|---------|-------|
| `backend/controllers/LeaveController.php` | `days` → `total_days`, thêm default `leave_status` | 70-82 |
| `backend/controllers/PerformanceController.php` | Validate `review_period_start/end`, thêm defaults | 61-80 |
| `index.html` | Cache busting v5.3 → v5.4 | 191 |

**Không sửa:**
- Models (đã đúng từ trước)
- Frontend modules (đã đúng từ trước)
- Database (không cần ALTER TABLE)

---

## 📝 CHECKLIST HOÀN THÀNH

- [x] Sửa LeaveController.create() - dùng `total_days`
- [x] Sửa PerformanceController.create() - validate `review_period_start/end`
- [x] Thêm default values cho `leave_status` và `review_status`
- [x] Update cache version v5.4
- [x] Test leave creation → ✅ Thành công
- [x] Test performance review creation → ✅ Thành công
- [x] Verify database inserts → ✅ Data đúng

---

## 🎉 KẾT QUẢ CUỐI CÙNG

**Trước khi sửa:**
- ❌ Không tạo được đơn nghỉ phép (SQL error)
- ❌ Không tạo được đánh giá (validation error)

**Sau khi sửa:**
- ✅ Tạo đơn nghỉ phép thành công
- ✅ Tạo đánh giá hiệu suất thành công
- ✅ Tất cả CRUD operations hoạt động
- ✅ Console sạch, không lỗi

**Status:** 🟢 **PRODUCTION READY**

---

*Tài liệu này bổ sung cho BUGFIX_REPORT.md*
