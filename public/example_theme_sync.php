<?php
// example_theme_sync.php - Theme sync feature example page
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Check if logged in
if (!isset($_SESSION['user'])) {
    header('Location: login_roles.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="light">
<head>
  <meta charset="utf-8">
  <title>Theme sync example page</title>
  <link rel="stylesheet" href="assets/css/admin.css">
  <!-- Include theme sync script -->
  <script src="assets/js/theme-sync.js"></script>
  <style>
    /* Dark mode styles */
    [data-theme="dark"] body {
      background: #1a1a1a !important;
      color: #ffffff !important;
    }
    
    [data-theme="dark"] .content-card {
      background: #2d2d2d !important;
      border: 1px solid #404040 !important;
      color: #ffffff !important;
    }
    
    [data-theme="dark"] .header {
      background: linear-gradient(90deg, #2563eb, #3b82f6) !important;
      color: white !important;
    }
    
    /* Light mode styles */
    [data-theme="light"] body {
      background: #f5f5f5 !important;
      color: #333333 !important;
    }
    
    [data-theme="light"] .content-card {
      background: white !important;
      border: 1px solid #e1e5e9 !important;
      color: #333333 !important;
    }
    
    [data-theme="light"] .header {
      background: linear-gradient(90deg, #2563eb, #3b82f6) !important;
      color: white !important;
    }
    
    /* Common styles */
    body {
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    .header {
      padding: 20px;
      text-align: center;
      margin-bottom: 30px;
    }
    
    .header h1 {
      margin: 0;
      font-size: 28px;
      font-weight: 600;
    }
    
    .content-card {
      max-width: 800px;
      margin: 0 auto 20px;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .theme-toggle-btn {
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.2s ease;
      margin: 10px;
    }
    
    .theme-toggle-btn:hover {
      background: rgba(255, 255, 255, 0.25);
      border-color: rgba(255, 255, 255, 0.3);
    }
    
    .info-section {
      margin-bottom: 30px;
    }
    
    .info-section h2 {
      color: #2563eb;
      margin-bottom: 15px;
    }
    
    .info-section p {
      line-height: 1.6;
      margin-bottom: 10px;
    }
    
    .code-block {
      background: #f8f9fa;
      border: 1px solid #e1e5e9;
      border-radius: 6px;
      padding: 15px;
      font-family: 'Courier New', monospace;
      font-size: 14px;
      overflow-x: auto;
      margin: 15px 0;
    }
    
    [data-theme="dark"] .code-block {
      background: #2a2a2a !important;
      border-color: #404040 !important;
      color: #e0e0e0 !important;
    }
    
    .feature-list {
      list-style: none;
      padding: 0;
    }
    
    .feature-list li {
      padding: 8px 0;
      border-bottom: 1px solid #e1e5e9;
    }
    
    [data-theme="dark"] .feature-list li {
      border-bottom-color: #404040 !important;
    }
    
    .feature-list li:before {
      content: "✅ ";
      margin-right: 10px;
    }
    
    .back-link {
      display: inline-block;
      background: #2563eb;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 6px;
      margin-top: 20px;
      transition: background-color 0.2s ease;
    }
    
    .back-link:hover {
      background: #3b82f6;
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>🌙 Theme sync feature example page</h1>
    <button class="theme-toggle-btn" onclick="ThemeManager.toggleTheme()" title="Toggle theme">
      🌙
    </button>
    <a href="admin_dashboard.php" class="back-link">Back to dashboard</a>
  </header>

  <div class="content-card">
    <div class="info-section">
      <h2>🎯 Feature overview</h2>
      <p>This page shows how theme sync works. When you change theme on the dashboard or another page, this page syncs automatically.</p>
      
      <div class="code-block">
// Include the theme sync script in the HTML page
&lt;script src="assets/js/theme-sync.js"&gt;&lt;/script&gt;

// Add a theme toggle button
&lt;button class="theme-toggle-btn" onclick="ThemeManager.toggleTheme()"&gt;🌙&lt;/button&gt;
      </div>
    </div>

    <div class="info-section">
      <h2>✨ Key features</h2>
      <ul class="feature-list">
        <li><strong>Cross-page sync</strong> - Changing theme on any page syncs other pages automatically</li>
        <li><strong>Real-time updates</strong> - Theme changes apply immediately without refresh</li>
        <li><strong>Multi-tab support</strong> - Supports theme sync across browser tabs</li>
        <li><strong>Local storage</strong> - Saves theme preference in localStorage</li>
        <li><strong>Server sync</strong> - Automatically saves theme setting to the server</li>
        <li><strong>Good compatibility</strong> - Works with modern and older browsers</li>
      </ul>
    </div>

    <div class="info-section">
      <h2>🔧 Technical implementation</h2>
      <p>Theme sync is implemented with:</p>
      
      <div class="code-block">
// 1. localStorage stores theme state
localStorage.setItem('theme', 'dark');

// 2. CustomEvent for in-page communication
window.dispatchEvent(new CustomEvent('themeChanged', {
  detail: { theme: 'dark' }
}));

// 3. BroadcastChannel for cross-tab communication
const channel = new BroadcastChannel('theme-sync');
channel.postMessage({ theme: 'dark' });
      </div>
    </div>

    <div class="info-section">
      <h2>📱 How to use</h2>
      <p>To use theme sync on your page, follow these steps:</p>
      
      <ol>
        <li>Include the theme sync script in the HTML &lt;head&gt;</li>
        <li>Add a data-theme attribute on the HTML root element</li>
        <li>Add a theme toggle button with an onclick handler</li>
        <li>Add dark mode CSS rules</li>
        <li>Test theme switching</li>
      </ol>
    </div>

    <div class="info-section">
      <h2>🎨 Style customization</h2>
      <p>Customize dark mode styles with CSS variables or attribute selectors:</p>
      
      <div class="code-block">
/* Using attribute selectors */
[data-theme="dark"] body {
  background: #1a1a1a;
  color: #ffffff;
}

/* Using CSS variables */
:root {
  --bg-primary: #f5f5f5;
  --text-primary: #333333;
}

[data-theme="dark"] {
  --bg-primary: #1a1a1a;
  --text-primary: #ffffff;
}
      </div>
    </div>
  </div>

  <script>
    // Page-specific theme change handling
    document.addEventListener('themeChanged', function(event) {
      const newTheme = event.detail.theme;
      console.log('Page received theme change event:', newTheme);
      
      // Add page-specific theme switch logic here
      if (newTheme === 'dark') {
        console.log('Apply dark mode styles');
      } else {
        console.log('Apply light mode styles');
      }
    });
    
    // Initialization after page load
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Theme sync example page loaded');
      console.log('Current theme:', document.documentElement.getAttribute('data-theme'));
      
      // Add page-specific initialization logic here
    });
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>


