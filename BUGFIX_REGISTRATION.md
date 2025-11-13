# 🔧 BUG FIX REPORT - 2025-11-13

## ❌ Lỗi Gặp Phải

### 1. Lỗi SQL: Unknown column 'username'
```
Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'username' in 'INSERT INTO'
```

**Nguyên nhân:**
- Code trong `AuthController.php` đang dùng `'username'` để map với database
- Nhưng table `users` chỉ có cột `'name'`, không có `'username'`

**Vị trí lỗi:**
```php
// backend/controllers/AuthController.php - Line 115
$userId = $this->userModel->register([
    'username' => $data['name'],  // ❌ SAI - column không tồn tại
    'email' => $data['email'],
    'password' => $data['password'],
    'role' => $data['role'] ?? 'employee'
]);
```

### 2. Lỗi aria-hidden (Warning)
```
Blocked aria-hidden on an element because its descendant retained focus
Element with focus: <a#showRegisterLink>
Ancestor with aria-hidden: <div.modal fade#loginModal>
```

**Nguyên nhân:**
- Bootstrap modal tự động thêm `aria-hidden="true"` khi modal đóng
- Link "Chưa có tài khoản?" vẫn giữ focus
- Chỉ là warning, không ảnh hưởng chức năng

## ✅ Các Sửa Đổi

### Fix #1: Sửa AuthController.php

**File:** `backend/controllers/AuthController.php`

**Thay đổi:**
```php
// BEFORE (Line 115-120)
$userId = $this->userModel->register([
    'username' => $data['name'],  // ❌ SAI
    'email' => $data['email'],
    'password' => $data['password'],
    'role' => $data['role'] ?? 'employee'
]);

// AFTER
$userId = $this->userModel->register([
    'name' => $data['name'],  // ✅ ĐÚNG - match với DB column
    'email' => $data['email'],
    'password' => $data['password'],
    'role' => $data['role'] ?? 'employee'
]);
```

**Giải thích:**
- Table `users` có cột `name` (không phải `username`)
- Frontend gửi field `name`
- Backend phải dùng `name` để match với DB schema

### Fix #2: aria-hidden Warning

**Trạng thái:** Không cần fix

**Lý do:**
- Đây chỉ là accessibility warning từ browser
- Bootstrap modal hoạt động bình thường
- Không ảnh hưởng đến chức năng đăng ký/đăng nhập
- User vẫn có thể sử dụng app tốt

**Nếu muốn fix (optional):**
```javascript
// Trong authmodule.js
showRegisterModal() {
    this.hideLoginModal();
    // Đợi modal đóng hoàn toàn
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('registerModal'));
        modal.show();
    }, 300);
}
```

## 🧪 Files Test Đã Tạo

### 1. check-database.php
- Kiểm tra cấu trúc database
- Xác nhận columns trong users table
- Verify query sẽ được execute
- URL: `http://localhost/quanli-main/check-database.php`

### 2. test-register.html
- Test registration API trực tiếp
- Không cần UI phức tạp
- Show request/response rõ ràng
- URL: `http://localhost/quanli-main/test-register.html`

## 📊 Database Schema

### Users Table Structure (Correct)
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,          -- ✅ Dùng 'name' không phải 'username'
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr_manager', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## ✅ Verification Checklist

Sau khi fix, verify các bước sau:

- [x] AuthController.php sử dụng `'name'` thay vì `'username'`
- [x] Database có cột `name` (không có `username`)
- [x] Model.php create() function hoạt động đúng
- [x] UserModel.php register() function OK
- [x] Test files đã được tạo
- [ ] Test register trên localhost thành công
- [ ] Test register trên production thành công
- [ ] Không còn lỗi SQL trong console

## 🚀 Testing Instructions

### Test Local (Localhost)

1. **Kiểm tra database:**
   ```
   http://localhost/quanli-main/check-database.php
   ```
   - Phải thấy: ✅ All checks passed
   - Verify: Column 'name' exists

2. **Test register API:**
   ```
   http://localhost/quanli-main/test-register.html
   ```
   - Nhập: Name, Email, Password
   - Click: Test Register
   - Kết quả: ✅ Registration successful!

3. **Test trong app:**
   ```
   http://localhost/quanli-main/
   ```
   - Click: "Chưa có tài khoản? Đăng ký"
   - Điền form đăng ký
   - Submit
   - Phải thấy: "Đăng ký thành công!"

### Test Production (Hosting)

1. **Upload fixed file:**
   - Upload `backend/controllers/AuthController.php` lên hosting

2. **Test qua test page:**
   ```
   https://your-domain.rf.gd/test-register.html
   ```

3. **Test trong app:**
   ```
   https://your-domain.rf.gd/
   ```

## 📝 Notes

### Về Lỗi "Email already registered"

Nếu thấy lỗi này:
```
Error: Email already registered
```

**Nguyên nhân:** Email đã tồn tại trong database

**Giải pháp:**
1. Dùng email khác để test
2. Hoặc xóa user cũ:
   ```sql
   DELETE FROM users WHERE email = 'test@example.com';
   ```

### Về Lỗi Anti-Bot (Production)

Nếu trên hosting vẫn thấy:
```
slowAES.decrypt(...)
```

**Giải pháp:** Đã được fix trong code
- Frontend tự động retry sau 2 giây
- Sử dụng `api-endpoint.php` wrapper
- Xem: `ANTI_BOT_FIX.md`

## 🎯 Summary

| Lỗi | Trạng thái | Impact | Fix |
|-----|-----------|--------|-----|
| SQL: Unknown column 'username' | ✅ Fixed | Critical | Changed to 'name' |
| aria-hidden warning | ⚠️ Warning | Low | No fix needed |
| Anti-bot protection | ✅ Fixed | High | Wrapper + retry |
| Email validation | ✅ Working | - | No change needed |

## 🆘 If Still Having Issues

1. Clear browser cache: `Ctrl + Shift + Delete`
2. Clear localStorage: Open `/clear-storage.html`
3. Check Console for errors (F12)
4. Verify database structure: `/check-database.php`
5. Test API directly: `/test-register.html`
6. Check error_log.txt on hosting

---

**Fixed by:** GitHub Copilot
**Date:** 2025-11-13
**Status:** ✅ Ready for Testing
**Files Modified:** 1 (`backend/controllers/AuthController.php`)
**Files Created:** 2 (`check-database.php`, `test-register.html`)
