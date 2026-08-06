<?php
// classes.php - Class management (admin)
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

require_once __DIR__ . '/../db.php';
$msg = '';
$error = '';
$navSection = 'classes';

// Handle class deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Add debug info
    error_log("Attempting to delete class ID: " . $delete_id);
    
    try {
        // Check whether class is in use
        $check_stmt = $pdo->prepare('SELECT COUNT(*) FROM lesson_plans WHERE class_id = ?');
        $check_stmt->execute([$delete_id]);
        $usage_count = $check_stmt->fetchColumn();
        
        error_log("Class usage count: " . $usage_count);
        
        if ($usage_count > 0) {
            $error = str_replace('{count}', $usage_count, t('classes.cannot_delete_used'));
            error_log("Delete failed: " . $error);
        } else {
            $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ?');
            $stmt->execute([$delete_id]);
            
            if ($stmt->rowCount() > 0) {
                $msg = t('classes.delete_success');
                error_log("Class deleted successfully: " . $delete_id);
            } else {
                $error = t('classes.delete_not_found');
                error_log("Delete failed: class does not exist");
            }
        }
    } catch (Throwable $e) {
        $error = t('classes.delete_failed') . '：' . $e->getMessage();
        error_log("Delete exception: " . $e->getMessage());
    }
}

// Handle add class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $name = trim($_POST['name'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $error = t('classes.enter_name');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO classes (name, is_active) VALUES (?, ?)');
            $stmt->execute([$name, $is_active]);
            $msg = t('classes.create_success');
        } catch (Throwable $e) {
            $error = t('classes.create_failed') . '：' . $e->getMessage();
        }
    }
}

// Load class list
try {
    $classes = $pdo->query('SELECT id, name, is_active, created_at FROM classes ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $e) {
    $classes = [];
    $error = $error ?: (t('classes.query_failed') . '：' . $e->getMessage());
}
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('classes.title') ?> - ERPH</title>
  <link rel="stylesheet" href="assets/css/admin.css?v=<?= filemtime(__DIR__ . '/assets/css/admin.css') ?>">
  <link rel="stylesheet" href="assets/css/mobile-optimization.css">
  <style>
    /* Dark mode CSS variables */
    :root {
      --bg-primary: #f5f5f5;
      --bg-secondary: #ffffff;
      --text-primary: #333333;
      --text-secondary: #666666;
      --text-muted: #999999;
      --border-color: #e1e5e9;
      --shadow-color: rgba(0, 0, 0, 0.08);
      --accent-color: #2563eb;
      --accent-hover: #3b82f6;
      --header-bg: linear-gradient(90deg, #2563eb, #3b82f6);
      --card-border: #2563eb;
      --success-bg: #d4edda;
      --success-text: #155724;
      --success-border: #c3e6cb;
      --error-bg: #f8d7da;
      --error-text: #721c24;
      --error-border: #f5c6cb;
      --warning-bg: #fff3cd;
      --warning-text: #856404;
      --warning-border: #ffeaa7;
    }
    
    /* Dark mode styles */
    [data-theme="dark"] {
      --bg-primary: #0f1419 !important;
      --bg-secondary: #1e2328 !important;
      --text-primary: #ffffff !important;
      --text-secondary: #b0b7c3 !important;
      --text-muted: #7a8288 !important;
      --border-color: #2d3748 !important;
      --shadow-color: rgba(0, 0, 0, 0.4) !important;
      --accent-color: #60a5fa !important;
      --accent-hover: #93c5fd !important;
      --header-bg: linear-gradient(90deg, #2563eb, #3b82f6) !important;
      --card-border: #60a5fa !important;
      --success-bg: #065f46 !important;
      --success-text: #d1fae5 !important;
      --success-border: #047857 !important;
      --error-bg: #7f1d1d !important;
      --error-text: #fecaca !important;
      --error-border: #dc2626 !important;
      --warning-bg: #92400e !important;
      --warning-text: #fed7aa !important;
      --warning-border: #ea580c !important;
      --card-hover-bg: #2a2f35 !important;
      --card-hover-shadow: rgba(0, 0, 0, 0.5) !important;
    }
    
    body {
      font-family: 'Microsoft YaHei', Arial, sans-serif;
      background: var(--bg-primary);
      color: var(--text-primary);
      transition: background-color 0.3s ease, color 0.3s ease;
      margin: 0;
      padding: 0;
    }
    
    /* Header fully protected - highest priority, unaffected by dark mode */
    .header {
      background: var(--header-bg);
      color: white;
      padding: 14px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px var(--shadow-color);
      transition: background 0.3s ease;
    }
    
    .header h1 {
      font-size: 20px;
      font-weight: 600;
      margin: 0;
      text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .header a {
      color: white;
      text-decoration: none;
      background: rgba(255,255,255,0.2);
      padding: 8px 12px;
      border-radius: 6px;
      margin-left: 8px;
      transition: all 0.2s ease;
    }
    
    .header a:hover {
      background: rgba(255,255,255,0.3);
      transform: translateY(-1px);
    }
    
    /* Theme toggle button */
    .theme-toggle-btn {
      background: rgba(255,255,255,0.2);
      color: white;
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 6px;
      padding: 8px 12px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.2s ease;
      margin-left: 8px;
    }
    
    .theme-toggle-btn:hover {
      background: rgba(255,255,255,0.3);
      border-color: rgba(255,255,255,0.5);
      transform: translateY(-1px);
    }
    
    /* Dark mode page polish */
    [data-theme="dark"] body {
      background: linear-gradient(135deg, var(--bg-primary), #1a1f24) !important;
      background-attachment: fixed !important;
    }
    
    [data-theme="dark"] .admin-layout {
      background: transparent !important;
    }
    
    [data-theme="dark"] main {
      background: transparent !important;
    }
    
    /* Dark mode card polish */
    [data-theme="dark"] .page-header,
    [data-theme="dark"] .classes-form,
    [data-theme="dark"] .classes-list {
      background: linear-gradient(135deg, var(--bg-secondary), var(--card-hover-bg)) !important;
      border: 1px solid var(--border-color) !important;
      box-shadow: 0 8px 25px var(--shadow-color) !important;
      backdrop-filter: blur(10px) !important;
    }
    
    [data-theme="dark"] .page-header:hover,
    [data-theme="dark"] .classes-form:hover,
    [data-theme="dark"] .classes-list:hover {
      background: var(--card-hover-bg) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px var(--card-hover-shadow) !important;
      border-color: var(--accent-color) !important;
    }
    
    /* Dark mode sidebar polish */
    [data-theme="dark"] .admin-sidebar {
      background: var(--bg-secondary) !important;
      border: 1px solid var(--border-color) !important;
      box-shadow: 0 6px 18px var(--shadow-color) !important;
    }
    
    [data-theme="dark"] .admin-sidebar .brand {
      color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .admin-sidebar .menu-title {
      color: var(--text-secondary) !important;
    }
    
    [data-theme="dark"] .admin-sidebar .menu a {
      color: var(--text-secondary) !important;
      background: transparent !important;
      border: none !important;
      box-shadow: none !important;
    }
    
    [data-theme="dark"] .admin-sidebar .menu a:hover {
      background: var(--card-hover-bg) !important;
      color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .admin-sidebar .menu a.active {
      background: var(--card-hover-bg) !important;
      color: var(--accent-color) !important;
      box-shadow: none !important;
      border-left: 3px solid var(--accent-color) !important;
    }
    
    [data-theme="dark"] .admin-sidebar .menu a .icon {
      color: var(--text-secondary) !important;
    }
    
    [data-theme="dark"] .admin-sidebar .menu a.active .icon {
      color: var(--accent-color) !important;
    }
    
    /* Light mode sidebar polish */
    .admin-sidebar {
      background: white !important;
      border: 1px solid #e1e5e9 !important;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    
    .admin-sidebar .brand {
      color: #333333 !important;
    }
    
    .admin-sidebar .menu-title {
      color: #666666 !important;
    }
    
    .admin-sidebar .menu a {
      color: #666666 !important;
      background: transparent !important;
      border: none !important;
      box-shadow: none !important;
    }
    
    .admin-sidebar .menu a:hover {
      background: #f8f9fa !important;
      color: #333333 !important;
    }
    
    .admin-sidebar .menu a.active {
      background: #f8f9fa !important;
      color: #2563eb !important;
      box-shadow: none !important;
      border-left: 3px solid #2563eb !important;
    }
    
    .admin-sidebar .menu a .icon {
      color: #666666 !important;
    }
    
    .admin-sidebar .menu a.active .icon {
      color: #2563eb !important;
    }
    
    /* Page header styles */
    .page-header {
      background: var(--bg-secondary);
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 4px 15px var(--shadow-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .page-header h2 {
      color: var(--accent-color);
      margin: 0;
    }
    
    /* Form styles */
    .classes-form {
      background: var(--bg-secondary);
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 4px 15px var(--shadow-color);
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: var(--text-primary);
      font-weight: 500;
    }
    
    .form-group input[type="text"],
    .form-group input[type="checkbox"] {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      font-size: 14px;
      background: var(--bg-secondary);
      color: var(--text-primary);
    }
    
    .form-group input[type="checkbox"] {
      width: auto;
    }
    
    .submit-btn {
      background: var(--accent-color);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .submit-btn:hover {
      background: var(--accent-hover);
      transform: translateY(-1px);
    }
    
    /* Class list styles */
    .classes-list {
      background: var(--bg-secondary);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px var(--shadow-color);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 14px;
      border-bottom: 1px solid var(--border-color);
      text-align: left;
    }
    
    th {
      background: #f8f9fa;
      color: var(--accent-color);
      font-weight: 600;
    }
    
    [data-theme="dark"] th {
      background: var(--bg-primary) !important;
      color: var(--accent-color) !important;
    }
    
    .status-active {
      background: var(--success-bg);
      color: var(--success-text);
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
    }
    
    .status-inactive {
      background: var(--error-bg);
      color: var(--error-text);
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
    }
    
    .action-buttons {
      display: flex;
      gap: 8px;
    }
    
    .edit-btn, .delete-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      font-size: 12px;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
    }
    
    .edit-btn {
      background: var(--accent-color);
      color: white;
    }
    
    .delete-btn {
      background: #f44336;
      color: white;
    }
    
    .delete-btn:hover {
      background: #d32f2f;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
    }
    
    /* Message styles */
    .message {
      padding: 12px 16px;
      border-radius: 6px;
      margin-bottom: 20px;
    }
    
    .message.success {
      background: var(--success-bg);
      color: var(--success-text);
      border: 1px solid var(--success-border);
    }
    
    .message.error {
      background: var(--error-bg);
      color: var(--error-text);
      border: 1px solid var(--error-border);
    }
    
    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
    }
    
    .modal-content {
      background: var(--bg-secondary);
      margin: 5% auto;
      padding: 0;
      border-radius: 12px;
      width: 90%;
      max-width: 600px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease-out;
    }
    
    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .modal-header {
      background: var(--accent-color);
      color: white;
      padding: 20px;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header h3 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
    }
    
    .close {
      color: white;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      line-height: 1;
      transition: all 0.2s ease;
    }
    
    .close:hover {
      opacity: 0.7;
      transform: scale(1.1);
    }
    
    .modal-body {
      padding: 30px;
    }
    
    .form-actions {
      display: flex;
      gap: 15px;
      margin-top: 25px;
      justify-content: flex-end;
    }
    
    .cancel-btn {
      background: var(--text-secondary);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .cancel-btn:hover {
      background: var(--text-muted);
      transform: translateY(-1px);
    }
    
    /* Dark mode modal styles */
    [data-theme="dark"] .modal-content {
      background: var(--bg-secondary) !important;
      border: 1px solid var(--border-color) !important;
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>ERPH System - <?= t('classes.title') ?></h1>
    <div>
      <a href="admin_dashboard.php"><?= t('common.back') ?><?= t('common.dashboard') ?></a>
      <a href="logout.php"><?= t('common.logout') ?></a>
    </div>
  </header>

  <div class="admin-layout">
    <?php include 'inc/admin_sidebar.php'; ?>

    <main>
      <div class="page-header">
        <h2><?= t('classes.title') ?></h2>
      </div>

      <?php if ($msg || isset($_SESSION['msg'])): ?>
        <div class="message success"><?= htmlspecialchars($msg ?: $_SESSION['msg']) ?></div>
        <?php unset($_SESSION['msg']); ?>
      <?php endif; ?>

      <?php if ($error || isset($_SESSION['error'])): ?>
        <div class="message error"><?= htmlspecialchars($error ?: $_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <div class="classes-form">
        <form method="POST">
          <div class="form-group">
            <label for="name"><?= t('classes.class_name') ?>:</label>
            <input type="text" id="name" name="name" required>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" name="is_active" checked> <?= t('common.enable') ?>
            </label>
          </div>
          <button type="submit" name="create" class="submit-btn"><?= t('classes.create_class') ?></button>
        </form>
      </div>

      <div class="classes-list">
        <?php if (empty($classes)): ?>
          <div style="text-align:center;padding:40px;color:#666;"><?= t('classes.no_data') ?></div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th><?= t('classes.class_name') ?></th>
                <th><?= t('common.status') ?></th>
                <th><?= t('common.created_at') ?></th>
                <th><?= t('common.action') ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($classes as $class): ?>
              <tr>
                <td><?= htmlspecialchars($class['id']) ?></td>
                <td><?= htmlspecialchars($class['name']) ?></td>
                <td>
                  <span class="status-<?= $class['is_active'] ? 'active' : 'inactive' ?>">
                    <?= $class['is_active'] ? t('common.enabled') : t('common.disabled') ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($class['created_at']) ?></td>
                <td>
                  <div class="action-buttons">
                    <button class="edit-btn" onclick="editClass(<?= $class['id'] ?>, '<?= htmlspecialchars($class['name']) ?>', <?= $class['is_active'] ?>)"><?= t('common.edit') ?></button>
                    <button class="delete-btn" onclick="deleteClass(<?= $class['id'] ?>)"><?= t('common.delete') ?></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Edit modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3><?= t('common.edit') ?> <?= t('classes.title') ?></h3>
        <span class="close" onclick="closeEditModal()">&times;</span>
      </div>
      <div class="modal-body">
        <form method="POST" id="editForm" action="edit_class.php">
          <input type="hidden" id="edit_id" name="edit_id">
          <div class="form-group">
            <label for="edit_name"><?= t('classes.class_name') ?>:</label>
            <input type="text" id="edit_name" name="name" required>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" id="edit_is_active" name="is_active"> <?= t('common.enable') ?>
            </label>
          </div>
          <div class="form-actions">
            <button type="submit" class="submit-btn"><?= t('common.save') ?></button>
            <button type="button" class="cancel-btn" onclick="closeEditModal()"><?= t('common.cancel') ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Theme toggle feature
    function toggleTheme() {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      
      // Set theme
      document.documentElement.setAttribute('data-theme', newTheme);
      
      // Update button icon
      const themeBtn = document.querySelector('.theme-toggle-btn');
      themeBtn.innerHTML = newTheme === 'light' ? '🌙' : '☀️';
      themeBtn.title = newTheme === 'light' ? '<?= t('common.switch_to_dark') ?>' : '<?= t('common.switch_to_light') ?>';
      
      // Save to sessionStorage
      sessionStorage.setItem('theme', newTheme);
      
      // Send to server to save
      fetch('change_theme.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'theme=' + newTheme
      }).then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Theme switched successfully:', newTheme);
        } else {
          console.error('Theme switch failed:', data.error);
        }
      }).catch(error => {
        console.error('Theme switch request failed:', error);
      });
    }
    
    // Restore theme on page load
    function initTheme() {
      const savedTheme = sessionStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
      
      // Update button icon
      const themeBtn = document.querySelector('.theme-toggle-btn');
      themeBtn.innerHTML = savedTheme === 'light' ? '🌙' : '☀️';
      themeBtn.title = savedTheme === 'light' ? '<?= t('common.switch_to_dark') ?>' : '<?= t('common.switch_to_light') ?>';
    }
    
    // Initialize theme after page load
    document.addEventListener('DOMContentLoaded', function() {
      initTheme();
    });
    
    // Click outside modal to close
    window.onclick = function(event) {
      const modal = document.getElementById('editModal');
      if (event.target === modal) {
        closeEditModal();
      }
    }
    
    function editClass(classId, name, isActive) {
      console.log('Edit class:', { classId, name, isActive });
      
      document.getElementById('edit_id').value = classId;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_is_active').checked = isActive === 1;
      
      document.getElementById('editModal').style.display = 'block';
    }
    
    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    function deleteClass(classId) {
      if (confirm('<?= t('classes.delete_confirm') ?>')) {
        // Create form to submit delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="delete_id" value="' + classId + '">';
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

