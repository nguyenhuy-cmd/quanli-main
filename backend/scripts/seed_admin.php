<?php
// Simple seed script to create an admin user with hashed password
require_once __DIR__ . '/../config.php';
$pdo = getPDO();
$email = $argv[1] ?? 'admin@example.com';
$pass = $argv[2] ?? 'password';
$name = $argv[3] ?? 'Admin';
$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('SELECT id FROM users WHERE email=:email');
$stmt->execute([':email'=>$email]);
if($stmt->fetch()){
    echo "User $email already exists\n";
    exit;
}
$stmt = $pdo->prepare('INSERT INTO users (email,password,name) VALUES (:e,:p,:n)');
$stmt->execute([':e'=>$email,':p'=>$hash,':n'=>$name]);
echo "Created user $email with password (plaintext): $pass\n";
