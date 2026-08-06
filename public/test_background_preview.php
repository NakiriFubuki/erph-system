<?php
// test_background_preview.php - Test background preview modal
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Simulate admin login state
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Test Admin',
    'role' => 'admin'
];

$current_page = 'background_manager';

// Simulate current background setting
$_SESSION['login_background'] = 'default';
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
  <meta charset="utf-8">
  <title>Background preview modal test - ERPH</title>
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="assets/css/background-manager.css">
</head>
<body>
  <header class="header">
    <h1>ERPH System - Background Preview Modal Test</h1>
    <div>
      <a href="admin_dashboard.php">Back to dashboard</a>
      <a href="logout.php">Log out</a>
      <button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle theme">
        🌙
      </button>
    </div>
  </header>

  <div class="admin-layout">
    <?php include 'inc/admin_sidebar.php'; ?>

    <main>
      <div class="page-header">
        <h2>Background preview modal test</h2>
        <div>
          <button class="btn btn-primary" onclick="openPreviewModal()">Test preview modal</button>
        </div>
      </div>

      <div class="upload-section">
        <h3>Test notes</h3>
        <p>This is a test page to verify the background preview modal works correctly.</p>
        <ul>
          <li>Click "Test preview modal" to open the modal</li>
          <li>The modal shows a preview of the login page</li>
          <li>Supports closing via outside click or ESC</li>
          <li>Supports dark/light theme switching</li>
        </ul>
      </div>
    </main>
  </div>

  <!-- Preview modal -->
  <div id="previewModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Login page preview</h3>
        <span class="close" onclick="closePreviewModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div class="preview-container">
          <div class="login-preview" id="loginPreview">
            <!-- Login page preview will appear here -->
            <div class="preview-login-card">
              <div class="preview-language-switch">
                <span class="preview-lang-label">Language:</span>
                <span class="preview-lang-btn active">Chinese</span>
                <span class="preview-lang-btn">English</span>
              </div>
              <h2 class="preview-title">ERPH System Login</h2>
              <div class="preview-form">
                <div class="preview-role-selector">
                  <label>Select role:</label>
                  <div class="preview-role-btns">
                    <span class="preview-role-btn active">Admin</span>
                    <span class="preview-role-btn">Teacher</span>
                  </div>
                </div>
                <div class="preview-input-group">
                  <label>Email</label>
                  <input type="email" placeholder="Enter email address" disabled>
                </div>
                <div class="preview-input-group">
                  <label>Password</label>
                  <input type="password" placeholder="Enter password" disabled>
                </div>
                <button class="preview-login-btn" disabled>Login</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closePreviewModal()">Close</button>
      </div>
    </div>
  </div>

  <script>
    // Theme toggle
    function toggleTheme() {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      
      // Set theme
      document.documentElement.setAttribute('data-theme', newTheme);
      
      // Update button icon
      const themeBtn = document.querySelector('.theme-toggle-btn');
      themeBtn.innerHTML = newTheme === 'light' ? '🌙' : '☀️';
      themeBtn.title = newTheme === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
      
      // Save to sessionStorage
      sessionStorage.setItem('theme', newTheme);
    }
    
    // Modal preview
    function openPreviewModal() {
      const modal = document.getElementById('previewModal');
      modal.style.display = 'block';
      
      // Apply current background to preview area
      applyBackgroundToPreview();
      
      // Prevent background scroll
      document.body.style.overflow = 'hidden';
    }
    
    function closePreviewModal() {
      const modal = document.getElementById('previewModal');
      modal.style.display = 'none';
      
      // Restore background scroll
      document.body.style.overflow = 'auto';
    }
    
    function applyBackgroundToPreview() {
      const previewContainer = document.getElementById('loginPreview');
      const currentBackground = '<?= $_SESSION['login_background'] ?? 'default' ?>';
      
      if (currentBackground !== 'default') {
        previewContainer.style.backgroundImage = `url('${currentBackground}')`;
        previewContainer.style.backgroundSize = 'cover';
        previewContainer.style.backgroundPosition = 'center';
        previewContainer.style.backgroundRepeat = 'no-repeat';
      } else {
        previewContainer.style.background = 'linear-gradient(135deg, #fff 10%, #ffecec 60%)';
        previewContainer.style.backgroundImage = 'none';
      }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('previewModal');
      if (event.target === modal) {
        closePreviewModal();
      }
    }
    
    // Close modal with ESC
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closePreviewModal();
      }
    });
    
    // Restore theme on page load
    function initTheme() {
      const savedTheme = sessionStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
      
      // Update button icon
      const themeBtn = document.querySelector('.theme-toggle-btn');
      themeBtn.innerHTML = savedTheme === 'light' ? '🌙' : '☀️';
      themeBtn.title = savedTheme === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
    }
    
    // Initialize theme after page load
    document.addEventListener('DOMContentLoaded', function() {
      initTheme();
    });
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

