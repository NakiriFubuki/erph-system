<?php
// test_background_display.php - Test background display
require_once __DIR__ . '/inc/session_config.php';

// Try connecting to the database
try {
    require_once __DIR__ . '/../db.php';
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

// Get current background setting
$current_background = 'default';
$debug_info = [];

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
                $current_background = $result['setting_value'];
                $debug_info[] = "Database read succeeded: " . $current_background;
            } else {
                $debug_info[] = "No background setting found in database";
            }
        } else {
            $debug_info[] = "system_settings table does not exist";
        }
    } catch (Exception $e) {
        $debug_info[] = "Database query error: " . $e->getMessage();
    }
} else {
    $debug_info[] = "Database connection failed";
}

// Read from session
if (isset($_SESSION['login_background'])) {
    $debug_info[] = "Session has background setting: " . $_SESSION['login_background'];
    if ($current_background === 'default') {
        $current_background = $_SESSION['login_background'];
    }
}

// Check file
$file_path = '';
$file_exists = false;
if ($current_background !== 'default') {
    $file_path = __DIR__ . '/' . $current_background;
    $file_exists = file_exists($file_path);
    $debug_info[] = "File path: " . $file_path;
    $debug_info[] = "File exists: " . ($file_exists ? 'Yes' : 'No');
    
    if ($file_exists) {
        $file_size = filesize($file_path);
        $file_permissions = substr(sprintf('%o', fileperms($file_path)), -4);
        $debug_info[] = "File size: " . number_format($file_size) . " bytes";
        $debug_info[] = "File permissions: " . $file_permissions;
    }
}

$debug_info[] = "Final background used: " . $current_background;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Background display test - ERPH</title>
  <style>
    body {
      font-family: 'Microsoft YaHei', Arial, sans-serif;
      margin: 0;
      padding: 20px;
      <?php if ($current_background !== 'default' && $file_exists): ?>
        background: url('<?= htmlspecialchars($current_background) ?>') no-repeat center center fixed;
        background-size: cover;
      <?php else: ?>
        background: linear-gradient(135deg, #fff 10%, #ffecec 60%);
      <?php endif; ?>
      min-height: 100vh;
    }
    
    .test-container {
      background: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 16px;
      max-width: 800px;
      margin: 50px auto;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
    }
    
    .test-title {
      color: #2563eb;
      text-align: center;
      margin-bottom: 30px;
      font-size: 24px;
    }
    
    .debug-section {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #2563eb;
    }
    
    .debug-title {
      font-weight: bold;
      color: #333;
      margin-bottom: 15px;
    }
    
    .debug-item {
      margin-bottom: 8px;
      padding: 5px;
      background: white;
      border-radius: 4px;
      font-family: monospace;
      font-size: 14px;
    }
    
    .btn {
      background: #2563eb;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin: 10px;
      font-size: 16px;
    }
    
    .btn:hover {
      background: #357abd;
    }
    
    .status-ok { color: #28a745; }
    .status-error { color: #dc3545; }
    .status-warning { color: #ffc107; }
    
    .background-preview {
      width: 100%;
      height: 200px;
      border: 2px dashed #ddd;
      border-radius: 8px;
      margin: 20px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: <?php if ($current_background !== 'default' && $file_exists): ?>url('<?= htmlspecialchars($current_background) ?>') center center no-repeat; background-size: cover;<?php else: ?>linear-gradient(135deg, #fff 10%, #ffecec 60%);<?php endif; ?>
    }
    
    .preview-text {
      background: rgba(0, 0, 0, 0.7);
      color: white;
      padding: 10px 20px;
      border-radius: 20px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="test-container">
    <h1 class="test-title">Background display test page</h1>
    
    <div class="background-preview">
      <?php if ($current_background !== 'default' && $file_exists): ?>
        <div class="preview-text">✅ Background image loaded</div>
      <?php else: ?>
        <div class="preview-text">❌ Using default background</div>
      <?php endif; ?>
    </div>
    
    <div class="debug-section">
      <div class="debug-title">Debug info</div>
      <?php foreach ($debug_info as $info): ?>
        <div class="debug-item"><?= htmlspecialchars($info) ?></div>
      <?php endforeach; ?>
    </div>
    
    <div class="debug-section">
      <div class="debug-title">Current status</div>
      <div class="debug-item">
        Background setting: <span class="<?= $current_background !== 'default' ? 'status-ok' : 'status-warning' ?>"><?= htmlspecialchars($current_background) ?></span>
      </div>
      <div class="debug-item">
        File exists: <span class="<?= $file_exists ? 'status-ok' : 'status-error' ?>"><?= $file_exists ? 'Yes' : 'No' ?></span>
      </div>
      <?php if ($file_path): ?>
      <div class="debug-item">
        File path: <span class="<?= $file_exists ? 'status-ok' : 'status-error' ?>"><?= htmlspecialchars($file_path) ?></span>
      </div>
      <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
      <a href="login_background_manager.php" class="btn">Back to background manager</a>
      <a href="login_roles.php" class="btn">View login page</a>
      <a href="debug_background.php" class="btn">Detailed debug</a>
    </div>
  </div>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

