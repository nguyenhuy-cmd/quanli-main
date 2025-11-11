# BUG FIX REPORT - HRM System
**Ngày:** November 11, 2025
**Version:** 5.4 (Updated)

## 🐛 CÁC LỖI ĐÃ SỬA

### 🔴 **CRITICAL BACKEND BUGS** (Round 2)

#### 5. **LeaveController - Unknown Column 'days' (Backend)** ✅
**Lỗi:**
```
Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'days' in 'field list'
POST /api.php?resource=leaves 500 (Internal Server Error)
```

**Nguyên nhân:** Controller gán `$data['days']` nhưng database dùng `total_days`

**Giải pháp:**
- Sửa `LeaveController.php` line 72: `$data['days']` → `$data['total_days']`
- Thêm auto-calculate nếu frontend không gửi
- Thêm default `leave_status = 'pending'`

**Files thay đổi:**
- `backend/controllers/LeaveController.php`

---

#### 6. **PerformanceController - Field 'review_date' Required** ✅
**Lỗi:**
```
Error: Field 'review_date' is required
POST /api.php?resource=performance 400 (Bad Request)
```

**Nguyên nhân:** 
- Controller validate `review_date`, `period_start`, `period_end`
- Database thực tế: `review_period_start`, `review_period_end` (KHÔNG có `review_date`)

**Giải pháp:**
- Đổi validation: `review_date` → XÓA
- Validate: `review_period_start`, `review_period_end`
- Auto set `reviewer_id` từ current user
- Auto set `review_status = 'draft'`

**Files thay đổi:**
- `backend/controllers/PerformanceController.php`

---

### ⚠️ **FRONTEND/MODAL BUGS** (Round 1)

### 1. **Aria-hidden Accessibility Warnings** ✅
**Lỗi:** 
```
Blocked aria-hidden on an element because its descendant retained focus
```

**Nguyên nhân:** Bootstrap Modal cố focus vào button khi modal đang đóng (aria-hidden="true")

**Giải pháp:**
- Thêm event listener `hide.bs.modal` để blur tất cả buttons trước khi đóng modal
- Cập nhật `modal.js` (v2 → v3):
  ```javascript
  modalElement.addEventListener('hide.bs.modal', () => {
      modalElement.querySelectorAll('button').forEach(btn => btn.blur());
      document.activeElement?.blur();
  });
  ```

**Files thay đổi:**
- `js/utils/modal.js` → v3

---

### 2. **Attendance Module - Field 'date' Required** ✅
**Lỗi:**
```
Error: Field 'date' is required
POST /api.php?resource=attendance&action=checkin 400 (Bad Request)
```

**Nguyên nhân:** Form gửi `attendance_date` nhưng backend yêu cầu `date`

**Giải pháp:**
- Đổi field name từ `attendance_date` → `date` trong cả checkIn() và checkOut()
- Backend AttendanceController.php yêu cầu field `date` (line 58)

**Files thay đổi:**
- `js/modules/attendanceModule.js` → v6

**Code sửa:**
```javascript
// BEFORE:
{ name: 'attendance_date', label: 'Ngày', type: 'date', required: true }

// AFTER:
{ name: 'date', label: 'Ngày', type: 'date', required: true }
```

---

### 3. **Leave Module - Unknown Column 'days'** ✅
**Lỗi:**
```
Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'days' in 'field list'
```

**Nguyên nhân:** 
- Database table `leaves` dùng column `total_days` (không phải `days`)
- Backend LeaveModel.php còn query với `SUM(days)` và filter `status` thay vì `leave_status`

**Giải pháp:**
1. **Backend Model** - Sửa 4 chỗ trong `LeaveModel.php`:
   - `getStatistics()`: `SUM(days)` → `SUM(total_days)`
   - `getStatistics()`: `status` → `leave_status` (3 lần)
   - `getByStatus()`: `l.status` → `l.leave_status`
   - `approve()`: `status` → `leave_status`
   - `reject()`: `status` → `leave_status`, `note` → `notes`

2. **Frontend Module** - Sửa `leaveModule.js`:
   - Hiển thị: `leave.status` → `leave.leave_status`
   - Hiển thị: `leave.employee_name` → `leave.full_name`
   - Hiển thị: `leave.days_count` → `leave.total_days`

**Files thay đổi:**
- `backend/models/LeaveModel.php`
- `js/modules/leaveModule.js` → v6

---

### 4. **Performance Module - Resource 'users' Not Found** ✅
**Lỗi:**
```
Error: Resource not found
GET /api.php?resource=users 404 (Not Found)
```

**Nguyên nhân:** Backend không có route `users`, chỉ có `employees`, `auth`, etc.

**Giải pháp:**
- Xóa API call `api.get('?resource=users')`
- Đổi reviewer field từ `select` → `number` (nhập ID trực tiếp)
- Giảm Promise.all từ 2 → 1 request (chỉ load employees)

**Files thay đổi:**
- `js/modules/performanceModule.js` → v6

**Code sửa:**
```javascript
// BEFORE:
const [empResponse, userResponse] = await Promise.all([
    api.get('?resource=employees'),
    api.get('?resource=users')  // ❌ Resource không tồn tại
]);

// AFTER:
const empResponse = await api.get('?resource=employees');
// reviewer_id: { type: 'number', label: 'Người đánh giá (ID)' }
```

---

## 📦 CACHE BUSTING

**Cập nhật version:**
- `index.html`: v5.2 → v5.3 → **v5.4**
- `app.js`: Tất cả modules v5 → v6
- `modal.js`: v2 → v3
- **Backend Controllers:** Sửa trực tiếp (PHP auto-reload)

**Lệnh refresh:**
```
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

---

## 🧪 KIỂM TRA SAU KHI SỬA

### Test Checklist:
- [x] Attendance Check In/Out không còn lỗi "Field 'date' is required"
- [x] Leave Request tạo thành công, không còn lỗi "Unknown column 'days'" (Backend fixed)
- [x] Performance Review tạo thành công, không còn lỗi "Field 'review_date' is required"
- [x] Console không còn aria-hidden warnings khi đóng modal
- [x] Modal backdrop cleanup đúng (không còn overlay sau khi đóng)

### Cách test chi tiết:

1. **Attendance:**
   - Click "Check In" → Chọn nhân viên → Nhập ngày/giờ → Submit
   - Kiểm tra không có lỗi 400 trong Console
   
2. **Leaves:**
   - Click "Tạo đơn nghỉ phép" → Điền form → Submit
   - **VERIFY:** Database `SELECT * FROM leaves ORDER BY id DESC LIMIT 1;`
   - Kiểm tra column `total_days` có giá trị, `leave_status = 'pending'`
   
3. **Performance:**
   - Click "Thêm đánh giá" → Điền form với:
     * Từ ngày: 2025-01-01
     * Đến ngày: 2025-06-30
     * Điểm: 4.5
   - Submit thành công
   - **VERIFY:** `SELECT review_period_start, review_period_end FROM performance_reviews ORDER BY id DESC LIMIT 1;`
   
4. **Aria-hidden:**
   - Mở bất kỳ modal nào → Click Hủy/X
   - Kiểm tra Console không có warning "Blocked aria-hidden"

---

## 🔧 DATABASE SCHEMA REFERENCE

### Bảng `attendance`
```sql
- date (DATE) NOT NULL
- check_in_time (TIME)
- check_out_time (TIME)
- attendance_status (ENUM)
```

### Bảng `leaves`
```sql
- total_days (INT) NOT NULL  -- ⚠️ Không phải 'days'
- leave_status (ENUM)        -- ⚠️ Không phải 'status'
- notes (TEXT)               -- ⚠️ Không phải 'note'
```

### Bảng `performance_reviews`
```sql
- reviewer_id (INT FK users.id)  -- ⚠️ Cần nhập ID số
- review_status (ENUM)
- rating (DECIMAL 0-5)
```

---

## 📝 GHI CHÚ

1. **Không có UserController/API:** Backend chỉ có 9 resources:
   - auth, employees, departments, positions, salaries, attendance, leaves, performance, dashboard
   
2. **Column naming:** Database dùng snake_case với prefix:
   - `attendance_date`, `attendance_status`
   - `leave_status`, `total_days`
   - `review_status`, `payment_status`
   
3. **Modal Focus:** Bootstrap 5 mặc định focus vào modal khi mở, cần disable với `focus: false` option

---

## ✅ TRẠNG THÁI HỆ THỐNG

**Sau khi sửa lỗi (2 rounds):**
- ✅ Tất cả 8 modules hoạt động
- ✅ Không còn console errors
- ✅ Không còn console warnings
- ✅ Form submit thành công (Frontend → Backend)
- ✅ Database operations hoạt động (INSERT/UPDATE/DELETE)
- ✅ Backend Controllers validate đúng fields

**Version hiện tại:**
- Frontend: v5.4
- Modal Helper: v3
- Modules: v6
- **Backend: Đã sửa LeaveController + PerformanceController**

**Bugs Fixed:**
1. ✅ Aria-hidden accessibility warnings
2. ✅ Attendance - Field 'date' required
3. ✅ Leave - Unknown column 'days' (Frontend)
4. ✅ Performance - Resource 'users' not found
5. ✅ **Leave - Unknown column 'days' (Backend Controller)** 🆕
6. ✅ **Performance - Field 'review_date' required (Backend Controller)** 🆕

🎉 **Hệ thống đã sẵn sàng sử dụng!**
