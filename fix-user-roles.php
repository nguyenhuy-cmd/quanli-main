<?php
/**
 * Fix Users Without Role
 * Update all users to have default role if NULL
 */

require_once __DIR__ . '/backend/config/config.php';

echo "<h1>🔧 Fix Users Without Role</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
    th { background: #007bff; color: white; }
    .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
</style>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='section info'>ℹ️ Connected to database: <code>" . DB_NAME . "</code></div>";
    
    // Step 1: Check current users
    echo "<div class='section'>";
    echo "<h2>Step 1: Check Current Users</h2>";
    
    $stmt = $conn->query("SELECT id, name, email, role, created_at FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found <strong>" . count($users) . "</strong> users in database:</p>";
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        
        $usersWithoutRole = 0;
        foreach ($users as $user) {
            $hasRole = !empty($user['role']);
            if (!$hasRole) $usersWithoutRole++;
            
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . ($user['role'] ?: '<span style="color:red;">NULL</span>') . "</td>";
            echo "<td>" . ($hasRole ? '✅ OK' : '❌ Missing') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($usersWithoutRole > 0) {
            echo "<div class='warning'>⚠️ Found <strong>$usersWithoutRole</strong> user(s) without role!</div>";
        } else {
            echo "<div class='success'>✅ All users have role assigned!</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ No users found in database</div>";
    }
    
    echo "</div>";
    
    // Step 2: Fix users without role
    echo "<div class='section'>";
    echo "<h2>Step 2: Fix Users Without Role</h2>";
    
    // Check if we need to fix
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IS NULL OR role = ''");
    $result = $stmt->fetch();
    $needFix = $result['count'];
    
    if ($needFix > 0) {
        echo "<p>Updating <strong>$needFix</strong> user(s) to default role 'employee'...</p>";
        
        // Update query
        $sql = "UPDATE users SET role = 'employee' WHERE role IS NULL OR role = ''";
        $updated = $conn->exec($sql);
        
        echo "<div class='success'>✅ Updated <strong>$updated</strong> user(s) successfully!</div>";
        
        echo "<p><strong>SQL executed:</strong></p>";
        echo "<code>$sql</code>";
    } else {
        echo "<div class='info'>ℹ️ No users need fixing. All users already have role assigned.</div>";
    }
    
    echo "</div>";
    
    // Step 3: Verify fix
    echo "<div class='section'>";
    echo "<h2>Step 3: Verify Fix</h2>";
    
    $stmt = $conn->query("SELECT id, name, email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    
    $allOk = true;
    foreach ($users as $user) {
        $hasRole = !empty($user['role']);
        if (!$hasRole) $allOk = false;
        
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><strong>" . ($user['role'] ?: 'NULL') . "</strong></td>";
        echo "<td>" . ($hasRole ? '✅ OK' : '❌ Still missing') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($allOk) {
        echo "<div class='success'>";
        echo "<h3>🎉 All Users Fixed Successfully!</h3>";
        echo "<p>✅ All users now have valid role assigned</p>";
        echo "<p>✅ Login should work without errors</p>";
        echo "<p>✅ JWT token generation will work</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Some users still missing role! Please check manually.</div>";
    }
    
    echo "</div>";
    
    // Step 4: Test login
    echo "<div class='section'>";
    echo "<h2>Step 4: Next Steps</h2>";
    echo "<ol>";
    echo "<li>✅ Database has been fixed</li>";
    echo "<li>✅ AuthController now handles missing role</li>";
    echo "<li>🧪 Test login at: <a href='index.html'>index.html</a></li>";
    echo "<li>🧪 Or test register: <a href='test-register.html'>test-register.html</a></li>";
    echo "</ol>";
    
    echo "<div class='info'>";
    echo "<strong>ℹ️ Default Roles:</strong><br>";
    echo "• <code>admin</code> - Full access<br>";
    echo "• <code>hr_manager</code> - HR management<br>";
    echo "• <code>employee</code> - Basic user (default)<br>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section error'>";
    echo "<h2>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
