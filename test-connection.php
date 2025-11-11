<?php
/**
 * Test Database Connection
 * Truy cập: http://localhost/quanli-main/test-connection.php
 */

echo "<h2>🔍 Kiểm tra kết nối XAMPP</h2>";

// 1. Kiểm tra PHP
echo "<h3>✅ PHP Version: " . phpversion() . "</h3>";

// 2. Kiểm tra PDO MySQL extension
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>✅ PDO MySQL extension: Loaded</p>";
} else {
    echo "<p style='color: red;'>❌ PDO MySQL extension: Not Loaded</p>";
}

// 3. Load config
require_once __DIR__ . '/backend/config/config.php';

echo "<h3>📋 Database Configuration:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>Database:</strong> " . DB_NAME . "</li>";
echo "<li><strong>User:</strong> " . DB_USER . "</li>";
echo "<li><strong>Password:</strong> " . (empty(DB_PASS) ? '(empty)' : '***') . "</li>";
echo "</ul>";

// 4. Test connection
echo "<h3>🔌 Testing Connection...</h3>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ KẾT NỐI THÀNH CÔNG!</p>";
    
    // Test query
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📊 Tables trong database:</h3>";
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ Chưa có tables nào. Bạn cần import file <code>backend/init.sql</code></p>";
        echo "<p>Cách import:</p>";
        echo "<ol>";
        echo "<li>Mở <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
        echo "<li>Chọn database <strong>" . DB_NAME . "</strong> (tạo mới nếu chưa có)</li>";
        echo "<li>Click tab <strong>Import</strong></li>";
        echo "<li>Chọn file <code>backend/init.sql</code></li>";
        echo "<li>Click <strong>Go</strong></li>";
        echo "</ol>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>✅ $table</li>";
        }
        echo "</ul>";
        
        // Count records
        echo "<h3>📈 Số lượng records:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch()['count'];
            echo "<li><strong>$table:</strong> $count records</li>";
        }
        echo "</ul>";
        
        echo "<p style='color: green; font-size: 16px;'>🎉 <strong>Database đã sẵn sàng!</strong></p>";
        echo "<p><a href='index.html' style='background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Mở ứng dụng HRM</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ KẾT NỐI THẤT BẠI!</p>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    
    echo "<h3>🔧 Giải pháp:</h3>";
    echo "<ol>";
    echo "<li>Mở <strong>XAMPP Control Panel</strong></li>";
    echo "<li>Click <strong>Start</strong> cho <strong>Apache</strong> và <strong>MySQL</strong></li>";
    echo "<li>Đợi cho đến khi cả hai service có màu xanh</li>";
    echo "<li>Tạo database <strong>" . DB_NAME . "</strong> trong phpMyAdmin</li>";
    echo "<li>Refresh trang này</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='test-connection.php'>🔄 Refresh</a> | ";
echo "<a href='http://localhost/phpmyadmin' target='_blank'>📊 phpMyAdmin</a> | ";
echo "<a href='index.html'>🏠 Home</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 { color: #0d6efd; }
    h3 { color: #333; margin-top: 20px; }
    code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    ul, ol {
        line-height: 1.8;
    }
</style>
