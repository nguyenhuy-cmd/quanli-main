# 🚀 HƯỚNG DẪN KẾT NỐI XAMPP - NHANH

## Bước 1: Khởi động XAMPP

1. **Mở XAMPP Control Panel** (đã mở tự động)
2. **Start Apache**: Click nút "Start" bên cạnh Apache
3. **Start MySQL**: Click nút "Start" bên cạnh MySQL
4. Đợi cho đến khi cả hai có **màu xanh**

![XAMPP](https://i.imgur.com/XqZ8rKh.png)

## Bước 2: Tạo Database

### Cách 1: Tự động (Khuyến nghị)
```bash
# Chạy trong PowerShell:
cd C:\xampp\htdocs\quanli-main
C:\xampp\mysql\bin\mysql.exe -u root < backend\init.sql
```

### Cách 2: Qua phpMyAdmin
1. Mở: http://localhost/phpmyadmin
2. Click "New" (bên trái)
3. Tên database: `hrm_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Click tab "Import"
7. Chọn file: `C:\xampp\htdocs\quanli-main\backend\init.sql`
8. Click "Go"

## Bước 3: Kiểm tra kết nối

Mở trình duyệt và truy cập:
```
http://localhost/quanli-main/test-connection.php
```

Bạn sẽ thấy:
- ✅ PHP version
- ✅ PDO MySQL extension status
- ✅ Database configuration
- ✅ Connection status
- ✅ List of tables (nếu đã import)

## Bước 4: Chạy ứng dụng

Nếu test-connection.php hiển thị **KẾT NỐI THÀNH CÔNG**, mở:
```
http://localhost/quanli-main
```

Đăng nhập với:
- Email: `admin@hrm.com`
- Password: `password`

## ❌ Nếu gặp lỗi

### Lỗi: "Access denied for user 'root'@'localhost'"
**Nguyên nhân**: XAMPP MySQL có password

**Giải pháp**: Chỉnh file `backend/config/config.php`:
```php
define('DB_PASS', 'your_mysql_password'); // Thay bằng password của bạn
```

### Lỗi: "Unknown database 'hrm_system'"
**Nguyên nhân**: Chưa tạo database

**Giải pháp**: Làm theo Bước 2 ở trên

### Lỗi: "Can't connect to MySQL server"
**Nguyên nhân**: MySQL chưa start

**Giải pháp**: 
1. Mở XAMPP Control Panel
2. Click "Start" cho MySQL
3. Nếu không start được, click "Config" → "my.ini" và check port (mặc định: 3306)

## 📋 Checklist

- [ ] XAMPP Apache đang chạy (màu xanh)
- [ ] XAMPP MySQL đang chạy (màu xanh)
- [ ] Database `hrm_system` đã tạo
- [ ] File `backend/init.sql` đã import
- [ ] File `backend/config/config.php` đã cấu hình đúng
- [ ] http://localhost/quanli-main/test-connection.php hiển thị thành công
- [ ] http://localhost/quanli-main mở được trang chủ

## 🎯 Quick Commands

```powershell
# Tạo database tự động
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS hrm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Import dữ liệu
C:\xampp\mysql\bin\mysql.exe -u root hrm_system < C:\xampp\htdocs\quanli-main\backend\init.sql

# Check MySQL status
Get-Service MySQL94

# Start MySQL (cần Admin)
net start MySQL94
```

## ✅ Kết quả mong đợi

Sau khi hoàn thành, bạn sẽ có:
- ✅ XAMPP Apache + MySQL đang chạy
- ✅ Database `hrm_system` với 8 tables
- ✅ 50+ sample records
- ✅ Ứng dụng HRM chạy tại http://localhost/quanli-main
- ✅ Có thể đăng nhập và sử dụng

**Thời gian**: ~3-5 phút

---

Nếu vẫn gặp vấn đề, check file `C:\xampp\apache\logs\error.log` hoặc tạo issue trên GitHub.
