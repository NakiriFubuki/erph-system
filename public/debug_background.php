<?php
// debug_background.php - Debug background settings
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Try connecting to the database
try {
    require_once __DIR__ . '/../db.php';
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

// Get current background setting
$session_background = $_SESSION['login_background'] ?? 'Not set';
$database_background = 'Not queried';
$file_exists = false;
$file_path = '';

// Read background setting from database
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'system_settings'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'login_background' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result) {
                $database_background = $result['setting_value'];
                $file_path = $result['setting_value'];
            }
        } else {
            $database_background = 'system_settings table does not exist';
        }
    } catch (Exception $e) {
        $database_background = 'Database query error: ' . $e->getMessage();
    }
} else {
    $database_background = 'Database connection failed';
}

// Check whether file exists
if ($file_path && $file_path !== 'default') {
    $full_path = __DIR__ . '/' . $file_path;
    $file_exists = file_exists($full_path);
    $file_size = $file_exists ? filesize($full_path) : 0;
    $file_permissions = $file_exists ? substr(sprintf('%o', fileperms($full_path)), -4) : 'N/A';
}

// Check uploads directory
$uploads_dir = __DIR__ . '/uploads/backgrounds/';
$uploads_exists = is_dir($uploads_dir);
$uploads_writable = is_writable($uploads_dir);
$uploads_files = [];
if ($uploads_exists) {
    $files = scandir($uploads_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $uploads_files[] = $file;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Background settings debug - ERPH</title>
  <style>
    body {
      font-family: 'Microsoft YaHei', Arial, sans-serif;
      background: #f5f5f5;
      margin: 20px;
      line-height: 1.6;
    }
    .debug-section {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .debug-section h2 {
      color: #2563eb;
      border-bottom: 2px solid #2563eb;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }
    .debug-item {
      margin-bottom: 15px;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 5px;
      border-left: 4px solid #2563eb;
    }
    .debug-label {
      font-weight: bold;
      color: #333;
      margin-bottom: 5px;
    }
    .debug-value {
      color: #666;
      font-family: monospace;
      word-break: break-all;
    }
    .status-ok { border-left-color: #28a745; }
    .status-error { border-left-color: #dc3545; }
    .status-warning { border-left-color: #ffc107; }
    .btn {
      background: #2563eb;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin: 5px;
    }
    .btn:hover {
      background: #357abd;
    }
    .btn-secondary {
      background: #6c757d;
    }
    .btn-secondary:hover {
      background: #545b62;
    }
  </style>
</head>
<body>
  <div class="debug-section">
    <h2>Background settings debug info</h2>
    
    <div class="debug-item <?= $session_background !== 'Not set' ? 'status-ok' : 'status-warning' ?>">
      <div class="debug-label">Session background setting:</div>
      <div class="debug-value"><?= htmlspecialchars($session_background) ?></div>
    </div>
    
    <div class="debug-item <?= $database_background !== 'Not queried' && $database_background !== 'Database connection failed' && $database_background !== 'system_settings table does not exist' ? 'status-ok' : 'status-error' ?>">
      <div class="debug-label">Database background setting:</div>
      <div class="debug-value"><?= htmlspecialchars($database_background) ?></div>
    </div>
    
    <?php if ($file_path && $file_path !== 'default'): ?>
    <div class="debug-item <?= $file_exists ? 'status-ok' : 'status-error' ?>">
      <div class="debug-label">File path:</div>
      <div class="debug-value"><?= htmlspecialchars($file_path) ?></div>
    </div>
    
    <div class="debug-item <?= $file_exists ? 'status-ok' : 'status-error' ?>">
      <div class="debug-label">File exists:</div>
      <div class="debug-value"><?= $file_exists ? 'Yes' : 'No' ?></div>
    </div>
    
    <?php if ($file_exists): ?>
    <div class="debug-item status-ok">
      <div class="debug-label">File size:</div>
      <div class="debug-value"><?= number_format($file_size) ?> bytes</div>
    </div>
    
    <div class="debug-item status-ok">
      <div class="debug-label">File permissions:</div>
      <div class="debug-value"><?= $file_permissions ?></div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
  
  <div class="debug-section">
    <h2>Uploads directory status</h2>
    
    <div class="debug-item <?= $uploads_exists ? 'status-ok' : 'status-error' ?>">
      <div class="debug-label">uploads/backgrounds directory exists:</div>
      <div class="debug-value"><?= $uploads_exists ? 'Yes' : 'No' ?></div>
    </div>
    
    <?php if ($uploads_exists): ?>
    <div class="debug-item <?= $uploads_writable ? 'status-ok' : 'status-error' ?>">
      <div class="debug-label">Directory writable:</div>
      <div class="debug-value"><?= $uploads_writable ? 'Yes' : 'No' ?></div>
    </div>
    
    <div class="debug-item status-ok">
      <div class="debug-label">Image files in directory:</div>
      <div class="debug-value">
        <?php if (empty($uploads_files)): ?>
          No image files
        <?php else: ?>
          <?php foreach ($uploads_files as $file): ?>
            <div><?= htmlspecialchars($file) ?></div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <div class="debug-section">
    <h2>Actions</h2>
    
    <a href="login_background_manager.php" class="btn">Back to background manager</a>
    <a href="login_roles.php" class="btn btn-secondary">View login page</a>
    
    <?php if (isset($db_error)): ?>
    <div class="debug-item status-error">
      <div class="debug-label">Database connection error:</div>
      <div class="debug-value"><?= htmlspecialchars($db_error) ?></div>
    </div>
    <?php endif; ?>
  </div>
  
  <div class="debug-section">
    <h2>Solutions</h2>
    
    <div class="debug-item status-warning">
      <div class="debug-label">If the background does not display, check:</div>
      <div class="debug-value">
        1. Ensure the system_settings table has been created<br>
        2. Verify the file path is correct<br>
        3. Verify file permissions<br>
        4. Clear the browser cache<br>
        5. Check .htaccess configuration
      </div>
    </div>
    
    <div class="debug-item status-ok">
      <div class="debug-label">Create database table:</div>
      <div class="debug-value">
        Run the SQL in create_system_settings_table.sql
      </div>
    </div>
  </div>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

