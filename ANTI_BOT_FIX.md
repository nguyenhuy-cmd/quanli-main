# 🚨 QUICK FIX: InfinityFree Anti-Bot Protection

## Vấn Đề
Khi deploy lên hosting InfinityFree (.rf.gd), API trả về HTML anti-bot thay vì JSON:
```html
<script type="text/javascript">
function toNumbers(d){...}
slowAES.decrypt(...)
</script>
```

## ✅ Giải Pháp Đã Áp Dụng

### 1. Tạo File `backend/api-endpoint.php`
File wrapper này set headers TRƯỚC khi InfinityFree inject code:

```php
<?php
ob_start();
header_remove();
header('Content-Type: application/json; charset=utf-8', true);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (ob_get_level()) {
    ob_clean();
}

require_once __DIR__ . '/api.php';
ob_end_flush();
```

### 2. Frontend Tự Động Chuyển Đổi
File `js/services/api.js` tự động dùng endpoint đúng:

```javascript
const isProduction = window.location.hostname !== 'localhost' && 
                     window.location.hostname !== '127.0.0.1';

const API_BASE_URL = isProduction 
    ? '/backend/api-endpoint.php'  // Production
    : '/backend/api.php';          // Local
```

### 3. Retry Logic Thông Minh
Tự động phát hiện và retry khi gặp anti-bot:

```javascript
// Lấy text response trước
const text = await response.text();

// Nếu response chứa HTML anti-bot
if (text.includes('slowAES.decrypt') || text.includes('toNumbers')) {
    console.warn('InfinityFree anti-bot detected, retrying...');
    await new Promise(resolve => setTimeout(resolve, 2000));
    return this.request(endpoint, method, data);
}
```

### 4. File .htaccess
Thêm file `backend/.htaccess` để config server:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} ^/backend/api\.php [NC]
    RewriteRule .* - [E=noconntect:1]
</IfModule>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    
    <FilesMatch "\.php$">
        Header set Content-Type "application/json; charset=utf-8"
    </FilesMatch>
</IfModule>
```

## 📤 Checklist Deploy

- [ ] Upload file `backend/api-endpoint.php`
- [ ] Upload file `backend/.htaccess`
- [ ] Update `js/services/api.js` (đã có trong code)
- [ ] Test bằng `test-api-hosting.html`
- [ ] Check console không có lỗi "Non-JSON response"

## 🧪 Test Trên Hosting

1. Upload file `test-api-hosting.html` lên hosting
2. Truy cập: `https://your-domain.rf.gd/test-api-hosting.html`
3. Click "Test api-endpoint.php (Wrapper)"
4. Kết quả mong đợi: ✅ Valid JSON Response

## 🔧 Nếu Vẫn Lỗi

### Option 1: Thử nghiệm với timestamp
Thêm timestamp vào URL để bypass cache:
```javascript
const timestamp = Date.now();
fetch(`${API_BASE_URL}?resource=auth&action=me&_t=${timestamp}`)
```

### Option 2: Đổi tên file
Đổi `api-endpoint.php` thành tên khác như:
- `api-v2.php`
- `json-api.php`
- `service.php`

### Option 3: Sử dụng subdomain
Tạo subdomain `api.your-domain.rf.gd` và point đến folder backend:
```javascript
const API_BASE_URL = isProduction 
    ? 'https://api.your-domain.rf.gd/api-endpoint.php'
    : '/backend/api.php';
```

### Option 4: Chuyển hosting
Nếu tất cả fail, xem xét hosting khác:
- **000webhost.com** - Không có anti-bot
- **byet.host** - Better performance
- **awardspace.com** - Free PHP hosting

## 📊 Monitoring

Sau khi deploy, check Console log:
```javascript
// Phải thấy:
✅ API Base URL: /backend/api-endpoint.php
✅ Auth check successful
✅ User loaded

// KHÔNG được thấy:
❌ Non-JSON response
❌ slowAES.decrypt
❌ Unexpected end of JSON input
```

## 💡 Pro Tips

1. **Luôn test local trước**: Đảm bảo code chạy trên localhost
2. **Upload từng file một**: Dễ debug hơn
3. **Check console logs**: Mọi lỗi đều log ra console
4. **Clear browser cache**: Sau mỗi lần deploy
5. **Test API trực tiếp**: Dùng Postman hoặc curl

## 🆘 Support URLs

- Test API: `/test-api-hosting.html`
- Clear Storage: `/clear-storage.html`
- Full Guide: `/HOSTING_DEPLOY_GUIDE.md`
