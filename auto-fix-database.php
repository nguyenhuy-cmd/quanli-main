<?php
/**
 * AUTO FIX DATABASE STRUCTURE
 * Upload file này lên hosting root và chạy: https://huyhrm.rf.gd/auto-fix-database.php
 */

// Database config
$host = 'sql209.infinityfree.com';
$dbname = 'if0_40315513_hrm_db';
$username = 'if0_40315513';
$password = 'y2EL3L0jjpY'; // Thay bằng password thật của bạn

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Auto Fix Database</title>
    <style>
        body { font-family: Arial; max-width: 1200px; margin: 20px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #e8f5e9; margin: 5px 0; }
        .error { color: red; padding: 10px; background: #ffebee; margin: 5px 0; }
        .info { color: blue; padding: 10px; background: #e3f2fd; margin: 5px 0; }
        .warning { color: orange; padding: 10px; background: #fff3e0; margin: 5px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; margin: 10px 5px; }
        .btn-danger { background: #f44336; color: white; border: none; }
        .btn-primary { background: #2196F3; color: white; border: none; }
    </style>
</head>
<body>
    <h1>🔧 Auto Fix Database Structure</h1>
    
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<div class='success'>✅ Connected to database: $dbname</div>";
        
        if ($_POST['action'] === 'fix') {
            echo "<h2>🔨 Fixing Database Structure...</h2>";
            
            // Drop all tables in correct order
            echo "<div class='info'>📋 Step 1: Dropping existing tables...</div>";
            $dropTables = [
                'performance_reviews',
                'leaves',
                'attendance',
                'salaries',
                'employees',
                'positions',
                'departments',
                'users'
            ];
            
            foreach ($dropTables as $table) {
                try {
                    $pdo->exec("DROP TABLE IF EXISTS `$table`");
                    echo "<div class='success'>✓ Dropped: $table</div>";
                } catch (Exception $e) {
                    echo "<div class='warning'>⚠ Skip: $table - " . $e->getMessage() . "</div>";
                }
            }
            
            // Create all tables
            echo "<div class='info'>📋 Step 2: Creating tables with correct structure...</div>";
            
            $sql = file_get_contents(__DIR__ . '/init-hosting.sql');
            
            if (!$sql) {
                throw new Exception("Cannot read init-hosting.sql file. Please upload it to the same folder.");
            }
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $success = 0;
            $errors = 0;
            
            foreach ($statements as $statement) {
                if (empty($statement)) continue;
                
                // Skip comments
                if (strpos($statement, '--') === 0) continue;
                if (strpos($statement, '/*') === 0) continue;
                
                try {
                    $pdo->exec($statement);
                    $success++;
                } catch (Exception $e) {
                    $errors++;
                    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
                }
            }
            
            echo "<div class='success'>✅ Executed $success statements successfully</div>";
            if ($errors > 0) {
                echo "<div class='error'>❌ $errors statements failed</div>";
            }
            
            echo "<div class='info'>📋 Step 3: Verifying structure...</div>";
            
            // Check tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<div class='success'>✅ Found " . count($tables) . " tables:</div>";
            echo "<pre>" . implode("\n", $tables) . "</pre>";
            
            // Check employees structure
            $stmt = $pdo->query("DESCRIBE employees");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $requiredColumns = ['id', 'user_id', 'employee_code', 'full_name', 'email', 'phone', 
                              'date_of_birth', 'gender', 'address', 'hire_date', 'employment_status', 
                              'department_id', 'position_id', 'salary'];
            
            $existingColumns = array_column($columns, 'Field');
            $missing = array_diff($requiredColumns, $existingColumns);
            
            if (empty($missing)) {
                echo "<div class='success'>✅ All required columns exist in employees table!</div>";
            } else {
                echo "<div class='error'>❌ Missing columns: " . implode(', ', $missing) . "</div>";
            }
            
            echo "<h2>🎉 Database fix completed!</h2>";
            echo "<div class='success'>";
            echo "<p>✅ Test your app now: <a href='https://huyhrm.rf.gd/' target='_blank'>https://huyhrm.rf.gd/</a></p>";
            echo "<p>✅ Verify structure: <a href='fix-database-structure.php' target='_blank'>fix-database-structure.php</a></p>";
            echo "</div>";
            
        } elseif ($_POST['action'] === 'check') {
            echo "<h2>🔍 Checking Current Structure...</h2>";
            
            // Check tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<div class='info'>📊 Found " . count($tables) . " tables:</div>";
            echo "<pre>" . implode("\n", $tables) . "</pre>";
            
            // Check employees columns
            if (in_array('employees', $tables)) {
                $stmt = $pdo->query("DESCRIBE employees");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<div class='info'>📊 Employees table columns:</div>";
                echo "<pre>";
                foreach ($columns as $col) {
                    echo $col['Field'] . " - " . $col['Type'] . "\n";
                }
                echo "</pre>";
                
                $requiredColumns = ['employee_code', 'full_name', 'employment_status'];
                $existingColumns = array_column($columns, 'Field');
                $missing = array_diff($requiredColumns, $existingColumns);
                
                if (empty($missing)) {
                    echo "<div class='success'>✅ All key columns exist!</div>";
                } else {
                    echo "<div class='error'>❌ Missing columns: " . implode(', ', $missing) . "</div>";
                    echo "<div class='warning'>⚠️ You need to click FIX button below!</div>";
                }
            }
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Database Error: " . $e->getMessage() . "</div>";
        echo "<div class='warning'>⚠️ Please check your database credentials in this file.</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
} else {
    // Show form
    ?>
    <div class="info">
        <h3>⚠️ IMPORTANT</h3>
        <ul>
            <li>Make sure you uploaded <strong>init-hosting.sql</strong> to the same folder as this file</li>
            <li>Update the database password in this file (line 10)</li>
            <li>This will DROP all existing tables and recreate them</li>
            <li><strong>ALL EXISTING DATA WILL BE LOST!</strong></li>
        </ul>
    </div>
    
    <form method="POST">
        <h3>Choose an action:</h3>
        <button type="submit" name="action" value="check" class="btn-primary">
            🔍 Check Current Structure
        </button>
        
        <button type="submit" name="action" value="fix" class="btn-danger" 
                onclick="return confirm('⚠️ WARNING: This will DROP all tables and recreate them. All data will be lost! Continue?')">
            🔨 FIX DATABASE (Drop & Recreate)
        </button>
    </form>
    <?php
}
?>

</body>
</html>
