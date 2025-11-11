# HƯỚNG DẪN CÀI ĐẶT NHANH - HRM SYSTEM

## ⚡ Cài đặt trong 5 phút

### Bước 1: Chuẩn bị
- Tải và cài đặt XAMPP: https://www.apachefriends.org/download.html
- Khởi động Apache và MySQL trong XAMPP Control Panel

### Bước 2: Cài đặt project

```bash
# Clone project (hoặc download ZIP)
git clone https://github.com/nguyenhuy-cmd/quanli-main.git

# Copy vào thư mục htdocs
# Windows:
xcopy quanli-main C:\xampp\htdocs\quanli-main /E /I

# Linux/Mac:
cp -r quanli-main /opt/lampp/htdocs/
```

### Bước 3: Tạo database

**Option 1: Sử dụng phpMyAdmin**
1. Mở http://localhost/phpmyadmin
2. Click "New" để tạo database mới
3. Tên database: `hrm_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Import" tab
6. Chọn file `backend/init.sql`
7. Click "Go"

**Option 2: Sử dụng MySQL command line**
```bash
# Windows
C:\xampp\mysql\bin\mysql.exe -u root -p

# Linux/Mac
mysql -u root -p

# Sau đó chạy:
CREATE DATABASE hrm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hrm_system;
SOURCE /path/to/quanli-main/backend/init.sql;
```

### Bước 4: Cấu hình (Optional)

Nếu MySQL của bạn có password, chỉnh sửa `backend/config/config.php`:

```php
define('DB_PASS', 'your_mysql_password'); // Thay 'your_mysql_password'
```

### Bước 5: Chạy ứng dụng

1. Mở browser
2. Truy cập: http://localhost/quanli-main
3. Đăng nhập với tài khoản mặc định:
   - Email: `admin@hrm.com`
   - Password: `password`

## 🎉 Xong!

Bây giờ bạn có thể:
- ✅ Xem Dashboard
- ✅ Quản lý Nhân viên
- ✅ Xem các module khác

---

## 🔧 Troubleshooting

### Lỗi: "Database connection failed"
**Nguyên nhân**: Không kết nối được MySQL  
**Giải pháp**:
1. Kiểm tra MySQL đang chạy trong XAMPP
2. Kiểm tra `DB_USER` và `DB_PASS` trong `backend/config/config.php`
3. Kiểm tra database `hrm_system` đã tạo chưa

### Lỗi: "404 Not Found"
**Nguyên nhân**: URL không đúng hoặc Apache chưa chạy  
**Giải pháp**:
1. Kiểm tra Apache đang chạy
2. URL phải là: `http://localhost/quanli-main` (không có s trong https nếu chưa config SSL)
3. Kiểm tra thư mục project có trong `C:\xampp\htdocs\`

### Lỗi: "CORS policy"
**Nguyên nhân**: Browser block request  
**Giải pháp**: CORS headers đã được thêm trong `backend/config/config.php`, thử:
1. Clear browser cache
2. Thử trình duyệt khác
3. Kiểm tra file `config.php` có chạy không

### Lỗi: Module không load
**Nguyên nhân**: JavaScript module error  
**Giải pháp**:
1. Mở DevTools (F12) → Console tab
2. Xem error message
3. Đảm bảo browser hỗ trợ ES6 modules (Chrome, Firefox, Edge modern versions)

### API trả về error
**Nguyên nhân**: Backend error  
**Giải pháp**:
1. Mở DevTools → Network tab
2. Click vào failed request
3. Xem Response để biết lỗi cụ thể
4. Check PHP error log: `C:\xampp\apache\logs\error.log`

---

## 📋 Checklist sau khi cài đặt

- [ ] XAMPP Apache đang chạy (port 80)
- [ ] XAMPP MySQL đang chạy (port 3306)
- [ ] Database `hrm_system` đã tạo
- [ ] Đã import `backend/init.sql`
- [ ] Có thể truy cập http://localhost/quanli-main
- [ ] Đăng nhập thành công
- [ ] Xem được Dashboard
- [ ] Xem được danh sách Nhân viên

---

## 🚀 Triển khai lên Hosting

### 1. Chuẩn bị
- Hosting hỗ trợ PHP 8+ và MySQL
- FTP credentials

### 2. Upload files
Sử dụng FTP client (FileZilla, WinSCP) upload tất cả files (trừ `.git/`, `node_modules/`)

### 3. Tạo database trên hosting
- Vào cPanel/Plesk
- Tạo MySQL database
- Import `backend/init.sql`

### 4. Cấu hình
Chỉnh `backend/config/config.php`:
```php
define('DB_HOST', 'your_db_host');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 5. Kiểm tra
- Truy cập domain của bạn
- Test các chức năng

---

## 🔗 Links hữu ích

- **Repository**: https://github.com/nguyenhuy-cmd/quanli-main
- **XAMPP Download**: https://www.apachefriends.org/
- **PHP Documentation**: https://www.php.net/docs.php
- **MySQL Tutorial**: https://www.mysqltutorial.org/
- **Bootstrap 5**: https://getbootstrap.com/docs/5.3/

---

## 💬 Hỗ trợ

Nếu gặp vấn đề, tạo issue trên GitHub:
https://github.com/nguyenhuy-cmd/quanli-main/issues

Hoặc liên hệ qua email: your-email@example.com

---

**Chúc bạn cài đặt thành công! 🎊**
