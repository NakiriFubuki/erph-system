<?php
// teacher_dashboard.php - Teacher dashboard
require_once __DIR__ . '/inc/bootstrap.php';

gate_instructor();

$activeAccount = signed_account();

// Get teacher-related statistics
try {
    require_once __DIR__ . '/../db.php';
    
    $stats = [];
    
    // Get the number of courses taught by the teacher
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT c.id) as count 
                          FROM courses c 
                          JOIN course_teachers ct ON c.id = ct.course_id 
                          WHERE ct.teacher_id = ?");
    $stmt->execute([$activeAccount['id']]);
    $stats['my_courses'] = $stmt->fetch()['count'];
    
    // Get the number of attendance records for the teacher
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance a 
                          JOIN courses c ON a.course_id = c.id 
                          JOIN course_teachers ct ON c.id = ct.course_id
                          WHERE ct.teacher_id = ?");
    $stmt->execute([$activeAccount['id']]);
    $stats['my_attendance'] = $stmt->fetch()['count'];
    
    // Get the number of lesson plans uploaded by the teacher
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM lesson_plans WHERE created_by = ?");
    $stmt->execute([$activeAccount['id']]);
    $stats['my_lesson_plans'] = $stmt->fetch()['count'];
    
    // Get recent courses
    $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.title, c.description, c.created_at 
                          FROM courses c 
                          JOIN course_teachers ct ON c.id = ct.course_id 
                          WHERE ct.teacher_id = ? 
                          ORDER BY c.created_at DESC LIMIT 5");
    $stmt->execute([$activeAccount['id']]);
    $recent_courses = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = t('errors.get_statistics') . ": " . $e->getMessage();
}
?>

<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= appearance_token() ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('teacher_dashboard.title') ?> - ERPH</title>
  <?php include __DIR__ . '/inc/head_teacher_assets.php'; ?>
</head>
<body>
  <?php if (!empty($_SESSION['login_notice'])): ?>
  <div style="background:#eff6ff;border-left:4px solid #2563eb;padding:12px 16px;margin:12px auto;max-width:960px;border-radius:8px;color:#1d4ed8;">
    <?= htmlspecialchars($_SESSION['login_notice']) ?>
  </div>
  <?php unset($_SESSION['login_notice']); endif; ?>
  <header class="header">
    <h1>ERPH System - <?= t('teacher_dashboard.title') ?></h1>
    <div>
      <a href="teacher_dashboard.php"><?= t('common.back') ?><?= t('common.dashboard') ?></a>
      <a href="logout.php"><?= t('common.logout') ?></a>
      <!-- Profile dropdown menu -->
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
                <div class="user-role"><?= t('common.teacher') ?></div>
              </div>
            </div>
          </div>
          <div class="dropdown-body">
            <a href="teacher_profile.php" class="dropdown-item profile-field">
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

  <div class="teacher-layout">
    <main>
      <div class="page-header">
        <div class="header-left">
          <h2><?= t('teacher_dashboard.welcome_back') ?>, <?= htmlspecialchars($activeAccount['name']) ?>!</h2>
          <span class="user-badge"><?= t('common.teacher') ?></span>
        </div>
        <div class="header-right">
          <!-- Profile button has been moved to the header -->
        </div>
      </div>

      <?php if (isset($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Stats cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?= $stats['my_courses'] ?? 0 ?></div>
          <div class="stat-label"><?= t('teacher_dashboard.my_courses') ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?= $stats['my_attendance'] ?? 0 ?></div>
          <div class="stat-label"><?= t('teacher_dashboard.attendance_records') ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?= $stats['my_lesson_plans'] ?? 0 ?></div>
          <div class="stat-label"><?= t('teacher_dashboard.my_lesson_plans') ?></div>
        </div>
      </div>

      <!-- Function button grid -->
      <div class="functions-grid">
        <h3><?= t('teacher_dashboard.quick_functions') ?></h3>
        <div class="function-buttons">
          <a href="teaching_reports.php" class="function-btn">
            <div class="function-icon"><?= glyph('analytics') ?></div>
            <div class="function-title"><?= t('teacher_dashboard.teaching_reports') ?></div>
            <div class="function-desc"><?= t('teacher_dashboard.teaching_reports_desc') ?></div>
          </a>
          
          <a href="lessonplans.php" class="function-btn">
            <div class="function-icon"><?= glyph('clipboard') ?></div>
            <div class="function-title"><?= t('teacher_dashboard.lesson_plans') ?></div>
            <div class="function-desc"><?= t('teacher_dashboard.lesson_plans_desc') ?></div>
          </a>
          
          <a href="my_courses.php" class="function-btn">
            <div class="function-icon"><?= glyph('layers') ?></div>
            <div class="function-title"><?= t('teacher_dashboard.my_courses') ?></div>
            <div class="function-desc"><?= t('teacher_dashboard.my_courses_desc') ?></div>
          </a>
          
          <a href="teacher_profile.php" class="function-btn">
            <div class="function-icon"><?= glyph('profile') ?></div>
            <div class="function-title"><?= t('common.profile') ?></div>
            <div class="function-desc"><?= t('teacher_dashboard.profile_desc') ?></div>
            </a>
          </div>
        </div>

      <!-- Recent courses -->
      <div class="recent-courses">
        <h3><?= t('teacher_dashboard.recent_courses') ?></h3>
        <?php if (!empty($recent_courses)): ?>
          <?php foreach ($recent_courses as $course): ?>
            <div class="course-item">
              <div class="course-title"><?= htmlspecialchars($course['title']) ?></div>
              <div class="course-description"><?= htmlspecialchars($course['description'] ?? '') ?></div>
              <div class="course-date"><?= t('common.created_at') ?>: <?= date('Y-m-d', strtotime($course['created_at'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align: center; color: var(--text-muted);"><?= t('teacher_dashboard.no_courses') ?></p>
        <?php endif; ?>
      </div>
    </main>
  </div>
  
  <!-- Settings modal -->
  <div class="settings-modal" id="settingsModal">
    <div class="settings-modal-content">
      <div class="settings-modal-header">
        <h3><?= t('common.settings') ?></h3>
        <button class="close-btn" onclick="toggleSettingsModal()">&times;</button>
      </div>
      
      <div class="settings-modal-body">
        <p class="setting-hint"><?= t('common.info') ?></p>
      </div>
    </div>
  </div>
  
  <!-- Modal backdrop overlay -->
  <div class="modal-backdrop" id="modalBackdrop" onclick="toggleSettingsModal()"></div>

  <script>
    function toggleProfileDropdown() {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) {
        dropdown.classList.toggle('show');
      }
    }

    function closeProfileDropdown() {
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) {
        dropdown.classList.remove('show');
      }
    }

    function toggleSettingsModal() {
      const modal = document.getElementById('settingsModal');
      const backdrop = document.getElementById('modalBackdrop');
      if (modal && backdrop) {
        modal.classList.toggle('show');
        backdrop.classList.toggle('show');
      }
    }

    document.addEventListener('click', function(event) {
      const profileTrigger = document.querySelector('.profile-trigger');
      const profileDropdown = document.getElementById('profileDropdown');
      if (profileTrigger && profileDropdown &&
          !profileTrigger.contains(event.target) &&
          !profileDropdown.contains(event.target)) {
        closeProfileDropdown();
      }
    });

    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeProfileDropdown();
      }
    });
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

