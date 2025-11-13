# Hướng Dẫn Deploy Lên InfinityFree Hosting

## ⚠️ Vấn Đề Anti-Bot Protection

InfinityFree hosting tự động thêm JavaScript anti-bot vào tất cả response, khiến API JSON không hoạt động đúng.

## ✅ Giải Pháp Đã Áp Dụng

### 1. **Sử dụng API Endpoint Wrapper**
   - File `backend/api-endpoint.php` đã được tạo
   - File này set headers trước khi InfinityFree inject code
   - Frontend tự động dùng endpoint này khi ở production

### 2. **Retry Logic Thông Minh**
   - Tự động phát hiện anti-bot HTML
   - Retry sau 2 giây nếu gặp anti-bot
   - Parse JSON an toàn với error handling

### 3. **Cấu Hình .htaccess**
   - File `backend/.htaccess` đã được tạo
   - Disable bot check cho API endpoints
   - Set proper content-type headers

## 📤 Các Bước Deploy

### Bước 1: Upload Files
```bash
# Upload toàn bộ project lên hosting
# Đảm bảo structure như sau:
/
├── index.html
├── backend/
│   ├── api.php
│   ├── api-endpoint.php  ← File mới (quan trọng!)
│   ├── .htaccess          ← File mới (quan trọng!)
│   ├── config/
│   ├── controllers/
│   └── models/
├── js/
└── css/
```

### Bước 2: Kiểm Tra Config
Mở `backend/config/config.php` và kiểm tra:

```php
// Phải có đúng thông tin hosting
if ($isProduction) {
    define('DB_HOST', 'sql209.infinityfree.com');
    define('DB_NAME', 'if0_40315513_hrm_db');
    define('DB_USER', 'if0_40315513');
    define('DB_PASS', 'Huy140923');
}
```

### Bước 3: Import Database
1. Truy cập phpMyAdmin trên hosting
2. Tạo database (nếu chưa có)
3. Import file `backend/init.sql`
4. Kiểm tra tất cả tables đã được tạo

### Bước 4: Test API Trực Tiếp
Truy cập URL sau để test:
```
https://your-domain.rf.gd/backend/api-endpoint.php?resource=auth&action=me
```

Kết quả mong đợi:
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

Nếu thấy HTML anti-bot → .htaccess chưa hoạt động

### Bước 5: Test Frontend
1. Truy cập `https://your-domain.rf.gd`
2. Mở Developer Console (F12)
3. Kiểm tra console log: `API Base URL: /backend/api-endpoint.php`
4. Thử đăng nhập

## 🔧 Troubleshooting

### Lỗi: "Server returned non-JSON response"

**Nguyên nhân:** InfinityFree vẫn inject anti-bot code

**Giải pháp:**
1. Kiểm tra file `.htaccess` đã upload đúng vị trí chưa
2. Thử đổi tên file từ `api.php` sang `api-endpoint.php`
3. Thử thêm query parameter: `?nocache=timestamp`

### Lỗi: "slowAES.decrypt" trong response

**Nguyên nhân:** Anti-bot đang active

**Giải pháp:**
1. Code đã có retry logic, đợi 2-3 giây sẽ tự retry
2. Nếu vẫn lỗi, contact InfinityFree support để whitelist domain
3. Hoặc xem xét chuyển sang hosting khác (000webhost, ByetHost)

### Lỗi: "Database connection failed"

**Nguyên nhân:** Sai thông tin database

**Giải pháp:**
1. Kiểm tra lại DB credentials trong `config.php`
2. Đảm bảo DB đã được tạo trong cPanel
3. Check user có quyền truy cập DB không

### Lỗi: "CORS policy blocked"

**Nguyên nhân:** Headers không đúng

**Giải pháp:**
1. Đảm bảo `api-endpoint.php` có CORS headers
2. Thêm domain vào whitelist nếu cần
3. Check .htaccess có config CORS chưa

## 🎯 Alternative: Hosting Khác

Nếu InfinityFree không work, thử các hosting này:

### 1. **000webhost**
- ✅ Không có anti-bot protection
- ✅ Hỗ trợ PHP & MySQL tốt
- URL: https://www.000webhost.com

### 2. **ByetHost**
- ✅ Ít ads hơn
- ✅ Performance tốt hơn
- URL: https://byet.host

### 3. **FreeHosting.com**
- ✅ Không giới hạn bandwidth
- ✅ Support PHP 7.4+
- URL: https://www.freehosting.com

## 📝 Notes

- InfinityFree anti-bot thường chỉ block request đầu tiên
- Sau 1-2 request sẽ tự bypass
- Code đã có retry logic để tự động xử lý
- Nếu vẫn lỗi → Consider upgrade to paid hosting

## 🆘 Support

Nếu vẫn gặp vấn đề, liên hệ:
- Email: support@yourdomain.com
- GitHub Issues: [link]
