# ✅ DEPLOY CHECKLIST

## Trước Khi Deploy

### 1. Kiểm Tra Local
- [ ] Test app trên localhost hoạt động bình thường
- [ ] Đăng nhập/đăng ký OK
- [ ] Tất cả modules load được
- [ ] Không có lỗi trong Console

### 2. Chuẩn Bị Files
- [ ] `backend/api-endpoint.php` - Wrapper để bypass anti-bot
- [ ] `backend/.htaccess` - Config Apache
- [ ] `js/services/api.js` - Auto-detect production
- [ ] `test-api-hosting.html` - Test file
- [ ] `ANTI_BOT_FIX.md` - Documentation

## Upload Lên Hosting

### 3. Upload Structure
```
hosting-root/
├── index.html
├── clear-storage.html
├── test-api-hosting.html
├── style.css
├── backend/
│   ├── api.php
│   ├── api-endpoint.php     ← QUAN TRỌNG!
│   ├── .htaccess            ← QUAN TRỌNG!
│   ├── init.sql
│   ├── config/
│   │   └── config.php       ← Check DB credentials
│   ├── controllers/
│   └── models/
├── js/
│   ├── app.js
│   ├── services/
│   │   └── api.js           ← Auto-detect production
│   ├── modules/
│   └── utils/
└── [other files]
```

### 4. Kiểm Tra Config
Mở `backend/config/config.php`:

```php
// ✅ Check these values match your hosting
if ($isProduction) {
    define('DB_HOST', 'sql209.infinityfree.com');
    define('DB_NAME', 'if0_40315513_hrm_db');
    define('DB_USER', 'if0_40315513');
    define('DB_PASS', 'Huy140923');
}
```

### 5. Import Database
- [ ] Login vào phpMyAdmin trên hosting
- [ ] Tạo database (nếu chưa có)
- [ ] Import `backend/init.sql`
- [ ] Verify tables: users, employees, departments, positions, etc.

## Testing Trên Hosting

### 6. Test API Endpoint
Truy cập: `https://your-domain.rf.gd/test-api-hosting.html`

**Tests cần chạy:**
- [ ] Test 1: Environment Info - Check API Base URL
- [ ] Test 2: Test api-endpoint.php - Phải thấy JSON response
- [ ] Test 3: Test Auth Me - Phải thấy "Unauthorized" (JSON)
- [ ] Test 4: Anti-Bot Detection - Không có slowAES

**Kết quả mong đợi:**
```
✅ Valid JSON Response
✅ No anti-bot code
✅ Content-Type: application/json
```

### 7. Test Application
Truy cập: `https://your-domain.rf.gd/`

**Checks:**
- [ ] Trang load được (không blank)
- [ ] Modal đăng nhập hiện ra
- [ ] Console không có lỗi về JSON
- [ ] Console log: `API Base URL: /backend/api-endpoint.php`

### 8. Test Login
- [ ] Thử register tài khoản mới
- [ ] Thử login với tài khoản test
- [ ] Check token được lưu vào localStorage
- [ ] Dashboard load sau khi login

## Troubleshooting

### Lỗi: "Non-JSON response"
**Solution:**
1. Check file `api-endpoint.php` đã upload chưa
2. Check file `.htaccess` đã upload vào folder `backend/` chưa
3. Thử clear browser cache: Ctrl + Shift + Delete
4. Mở `/clear-storage.html` để xóa localStorage

### Lỗi: "slowAES.decrypt" trong console
**Solution:**
1. Wrapper chưa hoạt động
2. Check URL có đúng là `api-endpoint.php` không
3. Check console log xem API_BASE_URL
4. Thử thêm timestamp: `?resource=auth&action=me&_t=123456`

### Lỗi: "Database connection failed"
**Solution:**
1. Sai credentials trong `config.php`
2. Database chưa được tạo
3. User không có quyền truy cập DB
4. Check phpMyAdmin xem DB có tồn tại không

### Lỗi: "CORS policy"
**Solution:**
1. Check `api-endpoint.php` có CORS headers chưa
2. Check `.htaccess` config
3. Thử access trực tiếp: `https://your-domain.rf.gd/backend/api-endpoint.php?resource=auth&action=me`

### Lỗi: Blank page
**Solution:**
1. Check Console errors (F12)
2. Check Network tab xem files nào 404
3. Check paths trong `index.html` có đúng không
4. Clear browser cache

## Post-Deploy

### 9. Security
- [ ] Đổi `JWT_SECRET` trong `config.php`
- [ ] Đổi mật khẩu admin mặc định
- [ ] Check `.htaccess` disable directory listing
- [ ] Check error_log không public

### 10. Performance
- [ ] Test tốc độ load trang
- [ ] Check API response time
- [ ] Monitor hosting bandwidth
- [ ] Check database queries optimize chưa

### 11. Monitoring
- [ ] Bookmark URL để check thường xuyên
- [ ] Setup uptime monitor (optional)
- [ ] Check hosting logs định kỳ
- [ ] Monitor error_log.txt

## Alternative Hosting

Nếu InfinityFree không work, thử:

### Option 1: 000webhost
- URL: https://www.000webhost.com
- ✅ No anti-bot
- ✅ Better support
- ❌ Có ads

### Option 2: ByetHost
- URL: https://byet.host
- ✅ No anti-bot
- ✅ Better performance
- ✅ Less ads

### Option 3: FreeHosting.com
- URL: https://www.freehosting.com
- ✅ No bandwidth limit
- ✅ PHP 7.4+
- ❌ Có ads

## Files Created for This Fix

1. `backend/api-endpoint.php` - Main wrapper
2. `backend/.htaccess` - Apache config
3. `test-api-hosting.html` - Test tool
4. `ANTI_BOT_FIX.md` - Quick fix guide
5. `HOSTING_DEPLOY_GUIDE.md` - Full guide
6. `DEPLOY_CHECKLIST.md` - This file

## Support

Nếu vẫn gặp vấn đề:
1. Check console logs carefully
2. Test với `test-api-hosting.html`
3. Read `ANTI_BOT_FIX.md`
4. Consider alternative hosting

---

**Last Updated:** 2025-11-13
**Version:** 1.0
**Status:** ✅ Ready for Production
