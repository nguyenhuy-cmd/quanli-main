# Script kiểm tra FTP connection
# Chạy script này để test FTP credentials

Write-Host "=== FTP Connection Test ===" -ForegroundColor Cyan

# Bạn cần nhập FTP credentials từ GitHub Secrets
$ftpServer = Read-Host "Nhập FTP Server (vd: ftpupload.net)"
$ftpUsername = Read-Host "Nhập FTP Username (vd: if0_xxxxx hoặc epiz_xxxxx)"
$ftpPassword = Read-Host "Nhập FTP Password" -AsSecureString

# Convert secure string to plain text
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($ftpPassword)
$plainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

Write-Host "`nĐang kết nối tới FTP server..." -ForegroundColor Yellow

try {
    # Create FTP request
    $ftpUrl = "ftp://$ftpServer/"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUrl)
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUsername, $plainPassword)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.UseBinary = $true
    $ftpRequest.KeepAlive = $false
    
    # Get response
    $response = $ftpRequest.GetResponse()
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $dirList = $reader.ReadToEnd()
    
    Write-Host "`n✅ Kết nối FTP thành công!" -ForegroundColor Green
    Write-Host "`nDanh sách thư mục:" -ForegroundColor Cyan
    Write-Host $dirList
    
    $reader.Close()
    $response.Close()
    
} catch {
    Write-Host "`n❌ Lỗi kết nối FTP:" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}
