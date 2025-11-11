# ⚠️ CẤU HÌNH PRODUCTION - QUAN TRỌNG!

## Trước khi push lên GitHub, BẠN PHẢI:

### 1. Cập nhật Database Password

Mở file `backend/config/config.php` và tìm dòng:

```php
define('DB_PASS', '');  // ⚠️ THAY ĐỔI PASSWORD CỦA BẠN Ở ĐÂY
```

**Thay bằng password vPanel của bạn!**

### 2. Cập nhật URL Production

Tìm dòng:

```php
define('APP_URL', 'https://huy12345.click');  // ⚠️ THAY ĐỔI URL CỦA BẠN
```

**Thay bằng domain thực của bạn!**

---

## Thông tin Database của bạn:

Từ ảnh screenshot cPanel:

```
MySQL DB Name:     if0_40315513_hrm_db
MySQL User Name:   if0_40315513
MySQL Host Name:   sql209.infinityfree.com
MySQL Password:    (Your vPanel Password) ← LẤY TỪ EMAIL ĐĂNG KÝ
```

---

## Các bước setup:

### Bước 1: Tạo Database trên InfinityFree
✅ ĐÃ XONG - Bạn đã tạo: `if0_40315513_hrm_db`

### Bước 2: Import Database
1. Click vào **Admin** bên cạnh database
2. Vào **Import** tab
3. Upload file `backend/init.sql`
4. Click **Go**

### Bước 3: Cập nhật config.php
1. Mở `backend/config/config.php`
2. Thay password ở dòng `DB_PASS`
3. Thay domain ở dòng `APP_URL`

### Bước 4: Push lên GitHub
```bash
git add .
git commit -m "Update production config"
git push
```

### Bước 5: Kiểm tra
- Đợi GitHub Actions deploy xong
- Truy cập: https://huy12345.click (hoặc domain của bạn)
- Nếu lỗi, xem file `error_log.txt` trên hosting

---

## ⚠️ BẢO MẬT:

**KHÔNG BAO GIỜ** commit password thật lên GitHub!

Nên dùng biến môi trường hoặc file `.env` (không push lên Git)

Hiện tại để đơn giản, code tự động detect production và dùng config phù hợp.
