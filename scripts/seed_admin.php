<?php
// scripts/seed_admin.php
// Usage (from project root):
//   php scripts/seed_admin.php [email] [password] [name]
// Example:
//   php scripts/seed_admin.php admin@example.com admin123 "Administrator"
// The script uses backend/config.php -> getPDO() to connect.

require_once __DIR__ . '/../backend/config.php';
$pdo = getPDO();

// CLI args or defaults
$email = isset($argv[1]) && $argv[1] ? $argv[1] : 'admin@example.com';
$password = isset($argv[2]) && $argv[2] ? $argv[2] : 'admin123';
$name = isset($argv[3]) && $argv[3] ? $argv[3] : 'Administrator';

$hash = password_hash($password, PASSWORD_DEFAULT);

try{
    // check existing
    $chk = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $chk->execute([':email' => $email]);
    $row = $chk->fetch();
    if($row){
        echo "User already exists: $email\n";
        exit(0);
    }

    $stmt = $pdo->prepare('INSERT INTO users (email, password, name) VALUES (:email, :password, :name)');
    $stmt->execute([':email' => $email, ':password' => $hash, ':name' => $name]);

    echo "Created user $email with password $password\n";
    exit(0);
}catch(PDOException $e){
    fwrite(STDERR, "Error creating admin user: " . $e->getMessage() . "\n");
    exit(1);
}
