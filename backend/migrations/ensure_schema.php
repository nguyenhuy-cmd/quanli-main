<?php
/**
 * Idempotent migration script to ensure required schema changes.
 * Run via CLI: php backend/migrations/ensure_schema.php
 */
require_once __DIR__ . '/../../backend/config.php';

try{
    $pdo = getPDO();
    $db = defined('DB_NAME') ? DB_NAME : 'hrm_db';
    echo "Connected to DB: $db\n";

    // Ensure position_id column exists on employees
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME='employees' AND COLUMN_NAME='position_id'");
    $stmt->execute([':db'=>$db]);
    $row = $stmt->fetch();
    if($row && $row['cnt'] == 0){
        echo "Adding column position_id to employees...\n";
        $pdo->exec("ALTER TABLE employees ADD COLUMN position_id INT NULL AFTER department_id");
    }else{
        echo "Column position_id already exists.\n";
    }

    // Ensure FK constraint exists
    $stmt = $pdo->prepare("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = :db AND TABLE_NAME='employees' AND COLUMN_NAME='position_id' AND REFERENCED_TABLE_NAME='positions'");
    $stmt->execute([':db'=>$db]);
    $fk = $stmt->fetchColumn();
    if(!$fk){
        echo "Adding foreign key fk_employees_position...\n";
        $pdo->exec("ALTER TABLE employees ADD CONSTRAINT fk_employees_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL");
    }else{
        echo "Foreign key for position_id already exists: $fk\n";
    }

    // Ensure tokens table exists for simple token storage
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'tokens'");
    $stmt->execute([':db'=>$db]);
    $row = $stmt->fetch();
    if($row && $row['cnt'] == 0){
        echo "Creating tokens table...\n";
        $pdo->exec(<<<SQL
CREATE TABLE tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(128) NOT NULL,
    expires_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (token),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
SQL
        );
    }else{
        echo "Tokens table already exists.\n";
    }

        // ensure tokens.expires_at column exists (idempotent)
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME='tokens' AND COLUMN_NAME='expires_at'");
        $stmt->execute([':db'=>$db]);
        $row = $stmt->fetch();
        if($row && $row['cnt'] == 0){
                echo "Adding expires_at to tokens...\n";
                $pdo->exec("ALTER TABLE tokens ADD COLUMN expires_at DATETIME NULL AFTER token");
        }else{
                echo "tokens.expires_at already present.\n";
        }

    echo "Migration finished.\n";
}catch(PDOException $e){
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
