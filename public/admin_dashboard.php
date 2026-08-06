<?php
// admin_dashboard.php - Admin dashboard
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

$activeAccount = signed_account();



// Current page id for sidebar highlight
$navSection = 'dashboard';

// Get statistics
try {
    require_once __DIR__ . '/../db.php';

    // Keep display name in sync with DB (e.g. after admin rename)
    $stmt = $pdo->prepare('SELECT name, avatar FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $activeAccount['id']]);
    $freshProfile = $stmt->fetch();
    if ($freshProfile) {
        $_SESSION['user']['name'] = $freshProfile['name'];
        $_SESSION['user']['avatar'] = $freshProfile['avatar'] ?? null;
        $activeAccount = signed_account();
    }
    
    $stats = [];
    
    // User statistics
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $role_stats = $stmt->fetchAll();
    foreach ($role_stats as $stat) {
        $stats[$stat['role']] = $stat['count'];
    }
    
    // Course statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM courses");
    $stats['courses'] = $stmt->fetch()['count'];
    
    // Attendance statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM attendance");
    $stats['attendance'] = $stmt->fetch()['count'];
    
    // Today teaching report statistics
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE DATE(date) = ?");
    $stmt->execute([$today]);
    $stats['today_reports'] = $stmt->fetch()['count'];
    
    // This week teaching report statistics
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE DATE(date) >= ?");
    $stmt->execute([$week_start]);
    $stats['week_reports'] = $stmt->fetch()['count'];
    
    // Lesson plan statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM lesson_plans");
    $stats['lesson_plans'] = $stmt->fetch()['count'];
    
    // Get today active teacher report status data
    $teacher_reports = [];
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.name as teacher_name,
            COUNT(DISTINCT a.id) as total_reports,
            COUNT(DISTINCT CASE WHEN DATE(a.date) = CURDATE() THEN a.id END) as today_reports,
            COUNT(DISTINCT CASE WHEN DATE(a.date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN a.id END) as week_reports,
            MAX(a.created_at) as last_report_time,
            COUNT(DISTINCT c.id) as assigned_courses
        FROM users u
        LEFT JOIN course_teachers ct ON ct.teacher_id = u.id
        LEFT JOIN courses c ON c.id = ct.course_id
        LEFT JOIN attendance a ON a.course_id = c.id
        WHERE u.role = 'teacher'
        GROUP BY u.id, u.name
        HAVING COUNT(DISTINCT CASE WHEN DATE(a.date) = CURDATE() THEN a.id END) > 0
        ORDER BY u.name
    ");
    $teacher_reports = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = t('errors.get_statistics') . ": " . $e->getMessage();
    $teacher_reports = [];
}
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= appearance_token() ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('dashboard.admin_title') ?></title>
  <?php include __DIR__ . '/inc/head_assets.php'; ?>
</head>
<body id="admin-dashboard">
  <?php if (!empty($_SESSION['login_notice'])): ?>
  <div style="background:#eff6ff;border-left:4px solid #2563eb;padding:12px 16px;margin:12px auto;max-width:960px;border-radius:8px;color:#1d4ed8;">
    <?= htmlspecialchars($_SESSION['login_notice']) ?>
  </div>
  <?php unset($_SESSION['login_notice']); endif; ?>
  <header class="header">
    <h1>ERPH System - <?= t('dashboard.admin_title') ?></h1>
    <div>

      <div class="profile-dropdown">
        <button class="profile-trigger" onclick="toggleProfileDropdown()" title="<?= t('common.profile') ?>">
          <span class="profile-avatar">
            <?php if (!empty($activeAccount['avatar'])): ?>
              <img src="<?= htmlspecialchars($activeAccount['avatar']) ?>" alt="<?= t('common.avatar') ?>" class="avatar-image">
            <?php else: ?>
              <?= mb_substr($activeAccount['name'], 0, 1) ?>
            <?php endif; ?>
          </span>
        </button>
        <div class="profile-dropdown-menu" id="profileDropdown">
          <div class="dropdown-header">
            <div class="user-avatar-section">
              <div class="user-avatar">
                <?php if (!empty($activeAccount['avatar'])): ?>
                  <img src="<?= htmlspecialchars($activeAccount['avatar']) ?>" alt="<?= t('common.avatar') ?>" class="avatar-image">
                <?php else: ?>
                  <?= mb_substr($activeAccount['name'], 0, 1) ?>
                <?php endif; ?>
              </div>
              <div class="user-info-text">
                <div class="user-name"><?= htmlspecialchars($activeAccount['name']) ?></div>
                <div class="user-role"><?= t('roles.admin') ?></div>
              </div>
            </div>
          </div>
          <div class="dropdown-body">
            <a href="profile.php" class="dropdown-item">
              <span class="dropdown-icon"><?= glyph('profile') ?></span>
              <span class="dropdown-text"><?= t('common.profile') ?></span>
            </a>
            <a href="user_manual.php" class="dropdown-item">
              <span class="dropdown-icon"><?= glyph('manual') ?></span>
              <span class="dropdown-text"><?= t('common.manual') ?></span>
            </a>
            <button class="dropdown-item settings-trigger" onclick="toggleSettingsModal()">
              <span class="dropdown-icon"><?= glyph('settings') ?></span>
              <span class="dropdown-text"><?= t('common.settings') ?></span>
            </button>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item logout-item">
              <span class="dropdown-icon"><?= glyph('logout') ?></span>
              <span class="dropdown-text"><?= t('common.logout') ?></span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="admin-layout">
    <!-- Use unified sidebar -->
    <?php include 'inc/admin_sidebar.php'; ?>

    <!-- Main content -->
    <main>
      <div class="admin-main-card" style="margin-bottom:16px;">
        <h2 class="welcome-title"><?= t('dashboard.welcome_back') ?>, <?= htmlspecialchars($activeAccount['name']) ?>!</h2>
        <p><?= t('dashboard.admin_description') ?></p>
      </div>

      <div class="stats-grid">
        <div class="stat-card"><div class="stat-number"><?= $stats['admin'] ?? 0 ?></div><div class="stat-label"><?= t('stats.admins') ?></div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['teacher'] ?? 0 ?></div><div class="stat-label"><?= t('stats.teachers') ?></div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['courses'] ?? 0 ?></div><div class="stat-label"><?= t('stats.courses') ?></div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['attendance'] ?? 0 ?></div><div class="stat-label"><?= t('stats.total_reports') ?></div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['today_reports'] ?? 0 ?></div><div class="stat-label"><?= t('stats.today_reports') ?></div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['week_reports'] ?? 0 ?></div><div class="stat-label"><?= t('stats.week_reports') ?></div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['lesson_plans'] ?? 0 ?></div><div class="stat-label"><?= t('stats.lesson_plans') ?></div></div>
      </div>

      <!-- Teacher report status section -->
      <div class="teacher-reports-section" id="teacherReportsSection">
        <div class="section-header">
          <h2 class="section-title"><?= t('teacher_reports.title') ?> - Today</h2>
          <p class="section-description"><?= t('teacher_reports.description') ?> (Teachers who submitted reports today only)</p>
        </div>

        <?php if (empty($teacher_reports)): ?>
          <div class="no-data-card">
            <p><?= t('teacher_reports.no_teachers') ?></p>
          </div>
        <?php else: ?>
          <div class="teacher-reports-grid">
            <?php foreach ($teacher_reports as $teacher): ?>
              <div class="teacher-report-card">
                <div class="teacher-info">
                  <h3><?= htmlspecialchars($teacher['teacher_name']) ?></h3>
                  <div class="teacher-meta">
                    <span class="assigned-courses"><?= t('teacher_reports.assigned_courses') ?>: <?= $teacher['assigned_courses'] ?><?= t('teacher_reports.courses_count') ?></span>
                  </div>
                </div>
                
                <div class="report-stats">
                  <div class="stat-row">
                    <span class="stat-label"><?= t('teacher_reports.total_reports') ?></span>
                    <span class="stat-value"><?= $teacher['total_reports'] ?></span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label"><?= t('teacher_reports.today_reports') ?></span>
                    <span class="stat-value <?= $teacher['today_reports'] > 0 ? 'active' : 'inactive' ?>">
                      <?= $teacher['today_reports'] ?>
                    </span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label"><?= t('teacher_reports.week_reports') ?></span>
                    <span class="stat-value"><?= $teacher['week_reports'] ?></span>
                  </div>
                </div>
                
                <div class="last-report">
                  <span class="last-report-label"><?= t('teacher_reports.last_submit') ?>:</span>
                  <span class="last-report-time">
                    <?php if ($teacher['last_report_time']): ?>
                      <?= date('Y-m-d H:i', strtotime($teacher['last_report_time'])) ?>
                    <?php else: ?>
                      <span class="never-submitted"><?= t('teacher_reports.never_submitted') ?></span>
                    <?php endif; ?>
                  </span>
                </div>
                
                <div class="teacher-actions">
                  <a href="admin_teaching_reports.php?teacher=<?= $teacher['id'] ?>" class="view-reports-btn">
                    <?= t('teacher_reports.view_reports') ?>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
  
  <script>
    // Profile dropdown toggle
    function toggleProfileDropdown() {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) {
      dropdown.classList.toggle('show');
        console.log('Dropdown state:', dropdown.classList.contains('show') ? 'shown' : 'hidden');
      }
    }
    
    // Close profile dropdown
    function closeProfileDropdown() {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) {
        dropdown.classList.remove('show');
        console.log('Dropdown closed');
      }
    }
    
    // Set modal toggle
    function toggleSettingsModal() {
      const modal = document.getElementById('settingsModal');
      const backdrop = document.getElementById('modalBackdrop');
      if (modal && backdrop) {
      modal.classList.toggle('show');
      backdrop.classList.toggle('show');
      }
    }
    
    // Language switch
    function changeLanguage(lang) {
      console.log('Switch language to:', lang);
      
      // Show loading state
      const currentBtn = document.querySelector(`.lang-btn[onclick*="${lang}"]`);
      if (currentBtn) {
        currentBtn.textContent = 'Switching...';
        currentBtn.disabled = true;
      }
      
      fetch('change_language.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'lang=' + lang
      })
      .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Language switch response:', data);
        if (data.success) {
          console.log('Language switched successfully, refreshing page');
          // Refresh page immediately
          location.reload();
        } else {
          console.error('Language switch failed:', data.error);
          alert('Language switch failed: ' + data.error);
          // Restore button state
          if (currentBtn) {
            currentBtn.textContent = lang === 'zh' ? 'Chinese' : '🇺🇸 English';
            currentBtn.disabled = false;
          }
        }
      })
      .catch(error => {
        console.error('Language switch request failed:', error);
        alert('Language switch request failed: ' + error.message);
        // Restore button state
        if (currentBtn) {
          currentBtn.textContent = lang === 'zh' ? 'Chinese' : '🇺🇸 English';
          currentBtn.disabled = false;
        }
      });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const profileTrigger = document.querySelector('.profile-trigger');
      const profileDropdown = document.getElementById('profileDropdown');
      
      // If click is not on trigger or dropdown content, close dropdown
      if (profileTrigger && profileDropdown && 
          !profileTrigger.contains(event.target) && 
          !profileDropdown.contains(event.target)) {
        closeProfileDropdown();
        console.log('Clicked outside, dropdown closed');
      }
    });
    
    // Add keyboard support - ESC closes dropdown
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeProfileDropdown();
      }
    });

  </script>
  
  <!-- Settings modal -->
  <div class="settings-modal" id="settingsModal">
    <div class="settings-modal-content">
      <div class="settings-modal-header">
        <h3><?= t('common.settings') ?></h3>
        <button class="close-btn" onclick="toggleSettingsModal()">&times;</button>
      </div>
      
      <div class="settings-modal-body">
        <p class="setting-hint"><?= t('common.info') ?>: <?= t('dashboard.admin_description') ?></p>
      </div>
    </div>
  </div>
  
  <!-- Modal backdrop -->
  <div class="modal-backdrop" id="modalBackdrop" onclick="toggleSettingsModal()"></div>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

