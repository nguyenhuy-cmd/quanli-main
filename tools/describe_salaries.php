<?php
require_once __DIR__ . '/../backend/config.php';
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'salaries'");
$stmt->execute([':db'=>DB_NAME]);
$cols = $stmt->fetchAll();
foreach($cols as $c) echo $c['COLUMN_NAME']." - ".$c['DATA_TYPE']." - ".$c['COLUMN_TYPE']."\n";
?>