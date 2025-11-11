# 🔍 HƯỚNG DẪN TROUBLESHOOT LỖI 404

## Vấn đề hiện tại
Website hiển thị trang 404 của InfinityFree hosting, nghĩa là file CHƯA được deploy.

## ✅ CHECKLIST - Kiểm tra từng bước:

### 1. Kiểm tra GitHub Secrets đã được set chưa?

Vào: https://github.com/nguyenhuy-cmd/quanli-main/settings/secrets/actions

Cần có 3 secrets:
- `FTP_SERVER` - Ví dụ: `ftpupload.net`
- `FTP_USERNAME` - Ví dụ: `if0_67810668` hoặc `epiz_xxxxx`
- `FTP_PASSWORD` - Mật khẩu FTP từ InfinityFree

**Nếu chưa có → Thêm ngay!**

### 2. Lấy FTP credentials từ InfinityFree:

1. Đăng nhập vào https://dash.infinityfree.com
2. Vào **Control Panel** của domain `huy12345.click`
3. Tìm phần **FTP Details** hoặc **FTP Accounts**
4. Lấy thông tin:
   - FTP Hostname: `ftpupload.net` (hoặc `ftp.huy12345.click`)
   - FTP Username: `if0_xxxxx` hoặc `epiz_xxxxx`
   - FTP Password: (tạo mới nếu quên)
   - FTP Port: `21`

### 3. Kiểm tra server directory:

Với InfinityFree, thư mục upload thường là:
- `/htdocs/` - Đây là thư mục public root
- **KHÔNG PHẢI** `/public_html/`
- **KHÔNG PHẢI** `/www/`

### 4. Kiểm tra GitHub Actions log:

1. Vào: https://github.com/nguyenhuy-cmd/quanli-main/actions
2. Click vào workflow run mới nhất
3. Click vào job "deploy"
4. Xem log để tìm lỗi:
   - ❌ "Authentication failed" → Sai username/password
   - ❌ "530 Login incorrect" → Sai credentials
   - ❌ "Directory not found" → Sai server-dir
   - ✅ "X files uploaded" → Thành công!

### 5. Test FTP thủ công:

**Cách 1: Dùng FileZilla**
1. Tải FileZilla: https://filezilla-project.org/
2. Kết nối với:
   - Host: `ftpupload.net`
   - Username: từ InfinityFree
   - Password: từ InfinityFree
   - Port: `21`
3. Vào thư mục `/htdocs/`
4. Upload file `test-deploy.html` thủ công
5. Truy cập: `https://huy12345.click/test-deploy.html`

**Cách 2: Dùng PowerShell script**
```powershell
cd c:\xampp\htdocs\quanli-main
.\tools\test-ftp.ps1
```

### 6. Nếu vẫn lỗi - Thử thay đổi server-dir:

Thử các giá trị sau trong `deploy.yml`:
- `server-dir: /htdocs/`
- `server-dir: /`
- `server-dir: /public_html/` (ít khi dùng cho IF)

## 🎯 Next Steps:

1. **NGAY BÂY GIỜ**: Kiểm tra GitHub Secrets
2. Xem log của GitHub Actions workflow
3. Nếu có lỗi auth → Update FTP credentials
4. Nếu deploy thành công nhưng vẫn 404 → Thử truy cập `https://huy12345.click/test-deploy.html`

## 📌 Ghi chú quan trọng:

- InfinityFree hosting miễn phí có thể mất **5-10 phút** để file có hiệu lực
- Xóa cache trình duyệt hoặc dùng Incognito mode
- Thử cả `huy12345.click` và `www.huy12345.click`
