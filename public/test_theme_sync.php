<?php
// test_theme_sync.php - Test theme sync
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="utf-8">
    <title>Theme sync test - ERPH</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/dark-mode-unified.css">
    <script src="assets/js/theme-sync.js"></script>
    <style>
        /* Light mode CSS variables */
        :root {
            --bg-primary: #f5f5f5;
            --bg-secondary: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
            --accent-color: #2563eb;
            --accent-hover: #3b82f6;
            --header-bg: linear-gradient(90deg, #2563eb, #3b82f6);
        }
        
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 0;
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            padding: 20px;
        }
        
        .header {
            background: var(--header-bg);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 12px;
        }
        
        .test-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--bg-secondary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .theme-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .theme-btn {
            padding: 12px 24px;
            border: 2px solid var(--accent-color);
            background: var(--bg-secondary);
            color: var(--accent-color);
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .theme-btn:hover {
            background: var(--accent-color);
            color: white;
        }
        
        .theme-btn.active {
            background: var(--accent-color);
            color: white;
        }
        
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid var(--text-secondary);
            border-radius: 8px;
        }
        
        .test-section h3 {
            margin-top: 0;
            color: var(--accent-color);
        }
        
        .status {
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
            font-weight: 500;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border: 1px solid #f5c6cb;
        }
        
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .test-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .test-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px var(--shadow-color);
            border-color: var(--accent-color);
        }
        
        .test-card h4 {
            margin: 0 0 15px 0;
            color: var(--accent-color);
        }
        
        .test-card p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .compatibility-check {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .compatibility-item {
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
        }
        
        .compatibility-item.supported {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .compatibility-item.not-supported {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .compatibility-item.unknown {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎨 Theme sync feature test</h1>
        <p>Test whether theme toggle and sync work correctly</p>
    </div>

    <div class="test-container">
        <div class="theme-buttons">
            <button class="theme-btn" onclick="changeTheme('light')">☀️ Light mode</button>
            <button class="theme-btn" onclick="changeTheme('dark')">🌙 Dark mode</button>
        </button>
        
        <div class="test-section">
            <h3>📊 Current status</h3>
            <div id="currentStatus" class="status info">
                Detecting current theme status...
            </div>
            <div id="sessionStatus" class="status info">
                Detecting session status...
            </div>
        </div>
        
        <div class="test-section">
            <h3>🔗 Page link tests</h3>
            <p>Click the links below to test theme sync across pages:</p>
            <div class="test-grid">
                <a href="add_user.php" class="test-card">
                    <h4>👤 Add user</h4>
                    <p>Test theme sync on the add-user page</p>
                </a>
                <a href="edit_user.php?id=1" class="test-card">
                    <h4>✏️ Edit user</h4>
                    <p>Test theme sync on the edit-user page</p>
                </a>
                <a href="add_course.php" class="test-card">
                    <h4>📚 Add course</h4>
                    <p>Test theme sync on the add-course page</p>
                </a>
                <a href="edit_course.php?id=1" class="test-card">
                    <h4>📝 Edit course</h4>
                    <p>Test theme sync on the edit-course page</p>
                </a>
                <a href="admin_dashboard.php" class="test-card">
                    <h4>🏠 Admin dashboard</h4>
                    <p>Test theme sync on the main dashboard</p>
                </a>
            </div>
        </div>
        
        <div class="test-section">
            <h3>🔧 Compatibility check</h3>
            <p>Check whether the browser supports required features:</p>
            <div class="compatibility-check">
                <div id="cssVars" class="compatibility-item unknown">
                    <strong>CSS variables</strong><br>
                    Checking...
                </div>
                <div id="localStorage" class="compatibility-item unknown">
                    <strong>localStorage</strong><br>
                    Checking...
                </div>
                <div id="broadcastChannel" class="compatibility-item unknown">
                    <strong>BroadcastChannel</strong><br>
                    Checking...
                </div>
                <div id="fetchAPI" class="compatibility-item unknown">
                    <strong>Fetch API</strong><br>
                    Checking...
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h3>📝 Test notes</h3>
            <ol>
                <li>Click the theme buttons above to switch themes</li>
                <li>Watch the page colors change</li>
                <li>Click page links to test whether the theme syncs</li>
                <li>Return here and verify the theme stayed consistent</li>
                <li>Check compatibility status</li>
            </ol>
        </div>
    </div>

    <script>
        // Initialize after page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStatus();
            updateButtonStates();
            checkCompatibility();
        });
        
        // Theme switch function
        function changeTheme(theme) {
            // Set theme
            document.documentElement.setAttribute('data-theme', theme);
            
            // Update button state
            updateButtonStates();
            
            // Save to server
            fetch('change_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'theme=' + theme
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Theme switch succeeded:', theme);
                    updateStatus();
                } else {
                    console.error('Theme switch failed:', data.error);
                }
            }).catch(error => {
                console.error('Theme switch request failed:', error);
            });
        }
        
        // Update button state
        function updateButtonStates() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            if (currentTheme === 'light') {
                document.querySelector('.theme-btn[onclick*="light"]').classList.add('active');
            } else {
                document.querySelector('.theme-btn[onclick*="dark"]').classList.add('active');
            }
        }
        
        // Update status display
        function updateStatus() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const currentStatus = document.getElementById('currentStatus');
            const sessionStatus = document.getElementById('sessionStatus');
            
            currentStatus.innerHTML = `Current theme: <strong>${currentTheme === 'light' ? 'Light mode ☀️' : 'Dark mode 🌙'}</strong>`;
            currentStatus.className = `status ${currentTheme === 'light' ? 'info' : 'success'}`;
            
            // Check session status
            fetch('check_session_theme.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        sessionStatus.innerHTML = `Session theme: <strong>${data.theme === 'light' ? 'Light mode ☀️' : 'Dark mode 🌙'}</strong>`;
                        sessionStatus.className = `status ${data.theme === 'light' ? 'info' : 'success'}`;
                    } else {
                        sessionStatus.innerHTML = `Session status: <strong>Unknown</strong>`;
                        sessionStatus.className = 'status error';
                    }
                })
                .catch(error => {
                    sessionStatus.innerHTML = `Session status: <strong>Check failed</strong>`;
                    sessionStatus.className = 'status error';
                });
        }
        
        // Check compatibility
        function checkCompatibility() {
            // Check CSS variable support
            const cssVars = document.getElementById('cssVars');
            if (CSS.supports('color', 'var(--test)')) {
                cssVars.className = 'compatibility-item supported';
                cssVars.innerHTML = '<strong>CSS variables</strong><br>✅ Supported';
            } else {
                cssVars.className = 'compatibility-item not-supported';
                cssVars.innerHTML = '<strong>CSS variables</strong><br>❌ Not supported';
            }
            
            // Check localStorage support
            const localStorage = document.getElementById('localStorage');
            try {
                const test = 'test';
                window.localStorage.setItem(test, test);
                window.localStorage.removeItem(test);
                localStorage.className = 'compatibility-item supported';
                localStorage.innerHTML = '<strong>localStorage</strong><br>✅ Supported';
            } catch (e) {
                localStorage.className = 'compatibility-item not-supported';
                localStorage.innerHTML = '<strong>localStorage</strong><br>❌ Not supported';
            }
            
            // Check BroadcastChannel support
            const broadcastChannel = document.getElementById('broadcastChannel');
            if (typeof BroadcastChannel !== 'undefined') {
                broadcastChannel.className = 'compatibility-item supported';
                broadcastChannel.innerHTML = '<strong>BroadcastChannel</strong><br>✅ Supported';
            } else {
                broadcastChannel.className = 'compatibility-item not-supported';
                broadcastChannel.innerHTML = '<strong>BroadcastChannel</strong><br>❌ Not supported';
            }
            
            // Check Fetch API support
            const fetchAPI = document.getElementById('fetchAPI');
            if (typeof fetch !== 'undefined') {
                fetchAPI.className = 'compatibility-item supported';
                fetchAPI.innerHTML = '<strong>Fetch API</strong><br>✅ Supported';
            } else {
                fetchAPI.className = 'compatibility-item not-supported';
                fetchAPI.innerHTML = '<strong>Fetch API</strong><br>❌ Not supported';
            }
        }
        
        // Listen for theme change events
        window.addEventListener('themeChanged', function(event) {
            console.log('Received theme change event:', event.detail.theme);
            updateStatus();
            updateButtonStates();
        });
    </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

