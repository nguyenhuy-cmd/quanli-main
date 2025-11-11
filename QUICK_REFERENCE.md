# 🔧 QUICK FIX REFERENCE - HRM System

## ⚡ LỆNH KIỂM TRA NHANH

### 1. Kiểm tra Database Schema
```bash
cd c:\xampp\htdocs\quanli-main
c:\xampp\php\php.exe check_schema.php
```

### 2. Kiểm tra Logs Console
```
F12 → Console tab → Refresh page (Ctrl+Shift+R)
```

### 3. Test CRUD Operations
```sql
-- Test Leave Creation
SELECT * FROM leaves ORDER BY id DESC LIMIT 1;

-- Test Performance Creation  
SELECT employee_id, review_period_start, review_period_end, rating 
FROM performance_reviews 
ORDER BY id DESC LIMIT 1;

-- Test Attendance
SELECT * FROM attendance WHERE attendance_date = CURDATE();
```

---

## 📋 DATABASE COLUMN MAPPING

| Feature | Frontend Field | Backend Column | Type |
|---------|---------------|----------------|------|
| **Attendance** |
| Date | `date` | `attendance_date` | DATE |
| Check In | `check_in_time` | `check_in_time` | TIME |
| Check Out | `check_out_time` | `check_out_time` | TIME |
| Status | - | `attendance_status` | ENUM |
| **Leaves** |
| Days | `total_days` | `total_days` | INT |
| Status | - | `leave_status` | ENUM |
| **Performance** |
| Period Start | `review_period_start` | `review_period_start` | DATE |
| Period End | `review_period_end` | `review_period_end` | DATE |
| Status | `review_status` | `review_status` | ENUM |
| Rating | `rating` | `rating` | DECIMAL |
| **Salaries** |
| Allowances | `allowances` | `allowances` | DECIMAL |
| Deductions | `deductions` | `deductions` | DECIMAL |
| Status | `payment_status` | `payment_status` | ENUM |

---

## 🚨 COMMON ERRORS & SOLUTIONS

### Error 1: "Column not found: 1054 Unknown column 'X'"
**Cause:** Field name mismatch  
**Fix:** Check database with `DESCRIBE table_name;`  
**Example:** `days` → `total_days`

### Error 2: "Field 'X' is required"
**Cause:** Controller validation mismatch  
**Fix:** Update `validateRequired()` in Controller  
**Example:** `review_date` không tồn tại, dùng `review_period_start/end`

### Error 3: "Resource not found 404"
**Cause:** API route không tồn tại  
**Fix:** Kiểm tra `backend/api.php` switch cases  
**Example:** `/users` không có → Dùng `/employees`

### Error 4: "Blocked aria-hidden on element"
**Cause:** Bootstrap focus vào button khi modal đóng  
**Fix:** Thêm `focus: false` option và blur buttons  
**File:** `js/utils/modal.js`

---

## 🎯 VALIDATION RULES

### LeaveController::create()
```php
Required: ['employee_id', 'leave_type', 'start_date', 'end_date', 'reason']
Auto-set: total_days (calculated), leave_status = 'pending'
```

### PerformanceController::create()
```php
Required: ['employee_id', 'review_period_start', 'review_period_end', 'rating']
Auto-set: reviewer_id (from auth), review_status = 'draft'
```

### AttendanceController::create()
```php
Required: ['employee_id', 'date']
Optional: check_in_time, check_out_time, notes
```

---

## 🔄 CACHE BUSTING WORKFLOW

1. Sửa code JavaScript → Update version in `app.js`
2. Sửa code PHP → Không cần version (auto-reload)
3. Update `index.html` version number
4. Browser: `Ctrl + Shift + R`

**Current versions:**
- index.html: v5.4
- Modules: v6
- modal.js: v3

---

## 📞 TROUBLESHOOTING STEPS

1. **Check Console** (F12)
   - Errors → Xem API response
   - Network → Xem request payload

2. **Check Database**
   ```sql
   DESCRIBE table_name;  -- Xem columns
   SELECT * FROM table_name ORDER BY id DESC LIMIT 5;  -- Xem data
   ```

3. **Check Backend Logs**
   - `c:\xampp\apache\logs\error.log`
   - Hoặc enable `display_errors` in PHP

4. **Test API Directly**
   - Postman/Insomnia
   - Test endpoint: `http://localhost/quanli-main/backend/api.php?resource=X`

---

## 🎓 LESSONS LEARNED

1. ⚠️ **Luôn verify database schema** với `DESCRIBE` trước khi code
2. ⚠️ **Không tin init.sql** nếu database đã migration/alter
3. ⚠️ **Sync naming** giữa Frontend → Backend → Database
4. ⚠️ **Test CRUD** trước khi deploy
5. ⚠️ **Cache busting** sau mỗi JS update

---

## 📚 FILES THAM KHẢO

- **Full Report:** `BUGFIX_REPORT.md`
- **Critical Bugs:** `CRITICAL_BUGFIX.md`
- **Schema Check:** `check_schema.php`
- **API Router:** `backend/api.php`

---

*Last updated: 2025-11-11 | Version 5.4*
