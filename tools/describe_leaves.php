<?php
require_once __DIR__ . '/../backend/config.php';
$pdo = getPDO();
$stmt = $pdo->query("SHOW COLUMNS FROM leaves");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
