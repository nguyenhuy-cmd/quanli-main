<?php
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = getPDO();
    
    echo "Đang xóa bảng leaves cũ...\n";
    $pdo->exec("DROP TABLE IF EXISTS leaves");
    echo "✓ Đã xóa\n\n";
    
    echo "Đang tạo lại bảng leaves với schema đúng...\n";
    $pdo->exec("
        CREATE TABLE leaves (
          id INT AUTO_INCREMENT PRIMARY KEY,
          employee_id INT,
          start_date DATE,
          end_date DATE,
          status VARCHAR(32) DEFAULT 'pending',
          FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Đã tạo lại bảng\n\n";
    
    // Insert sample data
    echo "Đang thêm dữ liệu mẫu...\n";
    $stmt = $pdo->query("SELECT id FROM employees LIMIT 1");
    $emp = $stmt->fetch();
    
    if($emp) {
        $pdo->prepare("INSERT INTO leaves (employee_id, start_date, end_date, status) VALUES (?, ?, ?, ?)")
            ->execute([$emp['id'], '2025-11-04', '2025-11-08', 'pending']);
        echo "✓ Đã thêm dữ liệu mẫu\n\n";
    }
    
    echo "===== HOÀN THÀNH =====\n";
    echo "Bảng leaves đã được tạo lại với schema đúng!\n";
    echo "Vui lòng quay lại trang web và refresh.\n";
    
} catch(Exception $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
}
