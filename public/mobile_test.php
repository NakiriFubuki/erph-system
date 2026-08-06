<?php
// mobile_test.php - Mobile test page
require_once __DIR__ . '/inc/bootstrap.php';

gate_signed_in();

$activeAccount = signed_account();
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mobile test - ERPH</title>
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="assets/css/mobile-optimization.css">
  <style>
    .test-section {
      background: var(--bg-secondary);
      padding: 2rem;
      margin-bottom: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 15px var(--shadow-color);
    }
    
    .test-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin: 1rem 0;
    }
    
    .test-card {
      background: var(--bg-primary);
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
      border: 1px solid var(--border-color);
    }
    
    .screen-info {
      background: var(--accent-color);
      color: white;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>ERPH System - Mobile Test</h1>
    <div>
      <a href="<?= $activeAccount['role'] === 'admin' ? 'admin_dashboard.php' : 'teacher_dashboard.php' ?>">Back to dashboard</a>
      <a href="logout.php">Log out</a>
      </div>
  </header>

  <div class="container">
    <div class="screen-info">
      <h2>Screen info</h2>
      <p>Screen width: <span id="screen-width"></span>px</p>
      <p>Screen height: <span id="screen-height"></span>px</p>
      <p>Device pixel ratio: <span id="device-pixel-ratio"></span></p>
      <p>User agent: <span id="user-agent"></span></p>
    </div>

    <div class="test-section">
      <h2>Responsive grid test</h2>
      <div class="test-grid">
        <div class="test-card">Card 1</div>
        <div class="test-card">Card 2</div>
        <div class="test-card">Card 3</div>
        <div class="test-card">Card 4</div>
        <div class="test-card">Card 5</div>
        <div class="test-card">Card 6</div>
      </div>
    </div>

    <div class="test-section">
      <h2>Table test</h2>
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Email</th>
              <th>Registered at</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Alice</td>
              <td>Admin</td>
              <td>zhangsan@example.com</td>
              <td>2024-01-01</td>
              <td><button class="btn btn-sm">Edit</button></td>
            </tr>
            <tr>
              <td>Bob</td>
              <td>Teacher</td>
              <td>lisi@example.com</td>
              <td>2024-01-02</td>
              <td><button class="btn btn-sm">Edit</button></td>
            </tr>
            <tr>
              <td>Carol</td>
              <td>Teacher</td>
              <td>wangwu@example.com</td>
              <td>2024-01-03</td>
              <td><button class="btn btn-sm">Edit</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="test-section">
      <h2>Form test</h2>
      <form>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" class="form-input" placeholder="Enter name">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" placeholder="Enter email">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-textarea" placeholder="Enter description"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <select class="form-select">
            <option>Admin</option>
            <option>Teacher</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
    </div>

    <div class="test-section">
      <h2>Button test</h2>
      <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
        <button class="btn btn-primary">Primary button</button>
        <button class="btn btn-secondary">Secondary button</button>
        <button class="btn btn-outline">Outline button</button>
        <button class="btn btn-danger">Danger button</button>
        <button class="btn btn-sm">Small button</button>
        <button class="btn btn-lg">Large button</button>
      </div>
    </div>

    <div class="test-section">
      <h2>Stat cards test</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">123</div>
          <div class="stat-label">Total users</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">45</div>
          <div class="stat-label">Active users</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">67</div>
          <div class="stat-label">Courses</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">89</div>
          <div class="stat-label">Lesson plans</div>
        </div>
      </div>
    </div>

    <div class="test-section">
      <h2>Touch test</h2>
      <p>Try the following:</p>
      <ul>
        <li>Tap buttons to see touch feedback</li>
        <li>Scroll the page for smooth scrolling</li>
        <li>Double-tap zoom test</li>
        <li>Long-press test</li>
      </ul>
      <div style="display: flex; gap: 1rem; margin-top: 1rem;">
        <button class="btn" onclick="alert('Touch test succeeded!')">Tap test</button>
        <button class="btn" oncontextmenu="alert('Right-click test succeeded!'); return false;">Right-click test</button>
      </div>
    </div>
  </div>

  <script src="assets/js/theme-sync.js"></script>
  <script>
    // Show screen info
    function updateScreenInfo() {
      document.getElementById('screen-width').textContent = window.innerWidth;
      document.getElementById('screen-height').textContent = window.innerHeight;
      document.getElementById('device-pixel-ratio').textContent = window.devicePixelRatio;
      document.getElementById('user-agent').textContent = navigator.userAgent.substring(0, 50) + '...';
    }
    
    // Update screen info on page load
    updateScreenInfo();
    
    // Update screen info on resize
    window.addEventListener('resize', updateScreenInfo);
    
    // Touch event test
    document.addEventListener('touchstart', function(e) {
      console.log('Touch start:', e.touches.length, 'touch points');
    });
    
    document.addEventListener('touchmove', function(e) {
      e.preventDefault(); // Prevent page scroll
    });
    
    document.addEventListener('touchend', function(e) {
      console.log('Touch end');
    });
    
    // Prevent double-tap zoom
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
      const now = (new Date()).getTime();
      if (now - lastTouchEnd <= 300) {
        event.preventDefault();
      }
      lastTouchEnd = now;
    }, false);
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

