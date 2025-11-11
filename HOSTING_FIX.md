# Hướng dẫn Fix lỗi 500 trên Hosting

## Vấn đề đã sửa:

### 1. ✅ Tạo file `.htaccess`
- Tắt `display_errors` trên production
- Bật ghi log lỗi vào file
- Cấu hình CORS headers
- Bảo vệ các file nhạy cảm
- Tối ưu hóa với Gzip và Cache

### 2. ✅ Cập nhật `config.php`
- Tự động phát hiện môi trường production
- Tắt hiển thị lỗi trên production, ghi vào log file
- Giữ nguyên hiển thị lỗi khi dev local

### 3. ✅ Sửa `api.php`
- Xóa bỏ `display_errors` cứng
- Sử dụng cấu hình từ `config.php`

## Các bước kiểm tra sau khi deploy:

1. **Chờ GitHub Actions hoàn thành** (xem tab Actions trên GitHub)

2. **Kiểm tra file đã upload đầy đủ**:
   - `.htaccess` phải có mặt trên hosting
   - `error_log.txt` sẽ được tạo tự động khi có lỗi

3. **Nếu vẫn bị lỗi 500**:
   - Kiểm tra file `error_log.txt` trên hosting để xem lỗi cụ thể
   - Đảm bảo database đã được tạo và cấu hình đúng
   - Kiểm tra PHP version (cần >= 7.4)

4. **Lỗi thường gặp trên InfinityFree**:
   ```
   - Không hỗ trợ một số php_flag trong .htaccess
     → Xóa các dòng php_flag nếu bị lỗi
   
   - Database host không phải localhost
     → Sửa trong config.php: DB_HOST = 'sqlXXX.infinityfreeapp.com'
   
   - Giới hạn kích thước file
     → Upload từng file nhỏ thay vì toàn bộ
   ```

## Cách xem log lỗi:

1. **Trên hosting**: Tải file `error_log.txt` về máy và mở
2. **Hoặc**: Tạm thời bật `display_errors = 1` để debug, nhớ tắt sau khi fix

## Debug nếu cần:

Tạm thời bật hiển thị lỗi bằng cách thêm vào đầu file `api.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**⚠️ Nhớ xóa sau khi debug xong!**
