<?php
// test_db_update.php - Test database update
require_once __DIR__ . '/inc/session_config.php';

// Try connecting to the database
try {
    require_once __DIR__ . '/../db.php';
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Check whether table exists
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'system_settings'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ system_settings table exists<br>";
    } else {
        echo "❌ system_settings table does not exist<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Table check failed: " . $e->getMessage() . "<br>";
    exit;
}

// Get current settings
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'login_background' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result) {
        echo "📋 Current background setting: " . htmlspecialchars($result['setting_value']) . "<br>";
    } else {
        echo "📋 Current background setting: Not set<br>";
    }
} catch (Exception $e) {
    echo "❌ Query failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test update
if (isset($_GET['test'])) {
    $test_value = 'uploads/backgrounds/login_bg_1756011872_screenshot 2025-08-17 160652.png';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_at) 
                               VALUES ('login_background', :value, NOW()) 
                               ON DUPLICATE KEY UPDATE setting_value = :value, updated_at = NOW()");
        $stmt->execute(['value' => $test_value]);
        
        echo "✅ Test update succeeded!<br>";
        echo "📋 New setting value: " . htmlspecialchars($test_value) . "<br>";
        
        // Verify update
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'login_background' LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        if ($result) {
            echo "✅ Verification succeeded, current setting: " . htmlspecialchars($result['setting_value']) . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Test update failed: " . $e->getMessage() . "<br>";
        
        // Try a simpler SQL statement
        try {
            echo "🔄 Trying a simple update statement...<br>";
            $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = :value, updated_at = NOW() WHERE setting_key = 'login_background'");
            $stmt->execute(['value' => $test_value]);
            
            if ($stmt->rowCount() > 0) {
                echo "✅ Simple update succeeded!<br>";
            } else {
                // If no rows were updated, try insert
                echo "🔄 Trying to insert a new record...<br>";
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_at) VALUES ('login_background', :value, NOW())");
                $stmt->execute(['value' => $test_value]);
                echo "✅ Insert succeeded!<br>";
            }
            
            // Verify again
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'login_background' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result) {
                echo "✅ Final verification succeeded, current setting: " . htmlspecialchars($result['setting_value']) . "<br>";
            }
            
        } catch (Exception $e2) {
            echo "❌ Fallback also failed: " . $e2->getMessage() . "<br>";
        }
    }
}

// Show all settings
try {
    echo "<br><h3>All system settings:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM system_settings");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    if (empty($results)) {
        echo "No settings yet";
    } else {
        foreach ($results as $row) {
            echo "🔑 " . htmlspecialchars($row['setting_key']) . ": " . htmlspecialchars($row['setting_value']) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Failed to query all settings: " . $e->getMessage() . "<br>";
}
?>

<br>
<a href="?test=1" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Test update background setting</a>
<br><br>
<a href="login_background_manager.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Back to background manager</a>
<a href="test_background_display.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Test background display</a>

