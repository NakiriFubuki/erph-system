<?php
// user_management.php - User management page
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

$navSection = 'users';

$activeAccount = signed_account();

// Get user list
try {
    require_once __DIR__ . '/../db.php';
    
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($role_filter) {
        $where_conditions[] = "role = ?";
        $params[] = $role_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Modify query to include avatar field and prioritize admins
    $stmt = $pdo->prepare("SELECT id, name, email, role, created_at, avatar FROM users $where_clause ORDER BY 
        CASE 
            WHEN role = 'admin' THEN 1
            WHEN role = 'teacher' THEN 2
            WHEN role = 'student' THEN 3
        END,
        created_at DESC");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = t('user_management.get_users_failed') . ": " . $e->getMessage();
    $users = [];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('user_management.title') ?> - ERPH</title>
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
      --header-bg: linear-gradient(90deg, #2563eb, #3b82f6) !important;  /* Keep original blue gradient */
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
      --gradient-start: #1e3a8a !important;
      --gradient-end: #3b82f6 !important;
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
    
    /* Dark mode force-override all dark elements - exclude header */
    [data-theme="dark"] *:not(.header):not(header):not(.header *):not(header *) {
      background-color: var(--bg-secondary) !important;
      color: var(--text-primary) !important;
    }
    
    /* Dark mode force-override all dark text - exclude header */
    [data-theme="dark"] *:not(.header):not(header):not(.header *):not(header *) {
      color: var(--text-primary) !important;
    }
    
    /* Dark mode page polish */
    [data-theme="dark"] {
      color-scheme: dark !important;
    }
    
    /* Dark mode header fully protected - highest priority */
    [data-theme="dark"] header,
    [data-theme="dark"] .header {
      all: unset !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      padding: 14px 20px !important;
      background: linear-gradient(90deg, #2563eb, #3b82f6) !important;
      background-image: linear-gradient(90deg, #2563eb, #3b82f6) !important;
      color: white !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
      position: relative !important;
      z-index: 100 !important;
      width: 100% !important;
      box-sizing: border-box !important;
    }
    
    [data-theme="dark"] header h1,
    [data-theme="dark"] .header h1 {
      all: unset !important;
      font-size: 20px !important;
      font-weight: 600 !important;
      margin: 0 !important;
      color: white !important;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
      display: block !important;
    }
    
    [data-theme="dark"] header a,
    [data-theme="dark"] .header a {
      all: unset !important;
      display: inline-block !important;
      color: white !important;
      text-decoration: none !important;
      background: rgba(255,255,255,0.2) !important;
      padding: 8px 12px !important;
      border-radius: 6px !important;
      margin-left: 8px !important;
      transition: all 0.2s ease !important;
    }
    
    [data-theme="dark"] header a:hover,
    [data-theme="dark"] .header a:hover {
      background: rgba(255,255,255,0.3) !important;
      transform: translateY(-1px) !important;
    }
    
    /* Dark mode theme toggle button protection */
    [data-theme="dark"] .theme-toggle-btn {
      all: unset !important;
      display: inline-block !important;
      background: rgba(255,255,255,0.2) !important;
      color: white !important;
      border: 1px solid rgba(255,255,255,0.3) !important;
      border-radius: 6px !important;
      padding: 8px 12px !important;
      cursor: pointer !important;
      font-size: 16px !important;
      transition: all 0.2s ease !important;
      margin-left: 8px !important;
    }
    
    [data-theme="dark"] .theme-toggle-btn:hover {
      background: rgba(255,255,255,0.3) !important;
      border-color: rgba(255,255,255,0.5) !important;
      transform: translateY(-1px) !important;
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
    [data-theme="dark"] .search-filters,
    [data-theme="dark"] .users-grid {
      background: linear-gradient(135deg, var(--bg-secondary), var(--card-hover-bg)) !important;
      border: 1px solid var(--border-color) !important;
      box-shadow: 0 8px 25px var(--shadow-color) !important;
      backdrop-filter: blur(10px) !important;
    }
    
    [data-theme="dark"] .page-header:hover,
    [data-theme="dark"] .search-filters:hover {
      background: var(--card-hover-bg) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px var(--card-hover-shadow) !important;
      border-color: var(--accent-color) !important;
    }
    
    /* Dark mode user card polish */
    [data-theme="dark"] .user-card {
      background: linear-gradient(135deg, var(--bg-secondary), var(--card-hover-bg)) !important;
      border: 1px solid var(--border-color) !important;
      box-shadow: 0 8px 25px var(--shadow-color) !important;
      backdrop-filter: blur(10px) !important;
    }
    
    [data-theme="dark"] .user-card:hover {
      background: var(--card-hover-bg) !important;
      transform: translateY(-5px) !important;
      box-shadow: 0 12px 35px var(--card-hover-shadow) !important;
      border-color: var(--accent-color) !important;
    }
    
    /* Dark mode button polish */
    [data-theme="dark"] .add-user-btn,
    [data-theme="dark"] .search-btn {
      background: linear-gradient(135deg, var(--accent-color), var(--accent-hover)) !important;
      color: white !important;
      border: none !important;
      box-shadow: 0 4px 15px var(--accent-color) !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    [data-theme="dark"] .add-user-btn:hover,
    [data-theme="dark"] .search-btn:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px var(--accent-color) !important;
    }
    
    /* Dark mode input polish */
    [data-theme="dark"] .search-input,
    [data-theme="dark"] .role-select {
      background: var(--bg-primary) !important;
      border: 2px solid var(--border-color) !important;
      color: var(--text-primary) !important;
      transition: all 0.3s ease !important;
    }
    
    [data-theme="dark"] .search-input:focus,
    [data-theme="dark"] .role-select:focus {
      border-color: var(--accent-color) !important;
      box-shadow: 0 0 0 3px rgba(100, 181, 246, 0.1) !important;
    }
    
    /* Dark mode role badge polish */
    [data-theme="dark"] .role-badge {
      background: linear-gradient(135deg, var(--accent-color), var(--accent-hover)) !important;
      color: white !important;
      box-shadow: 0 2px 8px var(--accent-color) !important;
    }
    
    /* Dark mode action button polish */
    [data-theme="dark"] .edit-btn {
      background: linear-gradient(135deg, var(--accent-color), var(--accent-hover)) !important;
      color: white !important;
      box-shadow: 0 2px 8px var(--accent-color) !important;
    }
    
    [data-theme="dark"] .delete-btn {
      background: linear-gradient(135deg, var(--error-bg), #f44336) !important;
      color: white !important;
      box-shadow: 0 2px 8px var(--error-bg) !important;
    }
    
    [data-theme="dark"] .edit-btn:hover,
    [data-theme="dark"] .delete-btn:hover {
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 15px var(--accent-color) !important;
    }
    
    /* Dark mode animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    [data-theme="dark"] .page-header,
    [data-theme="dark"] .search-filters,
    [data-theme="dark"] .users-grid {
      animation: fadeInUp 0.6s ease-out !important;
    }
    
    /* Dark mode sidebar polish - unify colors and remove blue bar */
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
    
    /* Light mode sidebar polish - unify colors and remove blue bar */
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
    
    /* User card grid styles */
    .users-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      padding: 20px;
      background: var(--bg-secondary);
      border-radius: 12px;
      box-shadow: 0 4px 15px var(--shadow-color);
    }
    
    .user-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 15px var(--shadow-color);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .user-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px var(--shadow-color);
      border-color: var(--accent-color);
    }
    
    .user-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--accent-color), var(--accent-hover));
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .user-card:hover::before {
      opacity: 1;
    }
    
    .user-header {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      gap: 15px;
    }
    
    .user-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      overflow: hidden;
      border: 3px solid var(--accent-color);
      background: var(--bg-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: var(--text-muted);
      flex-shrink: 0;
    }
    
    .user-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .user-info {
      flex: 1;
      min-width: 0;
    }
    
    .user-name {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0 0 5px 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .user-email {
      font-size: 14px;
      color: var(--text-secondary);
      margin: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .user-details {
      margin-bottom: 15px;
    }
    
    .user-detail-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
      font-size: 14px;
    }
    
    .detail-label {
      color: var(--text-muted);
      font-weight: 500;
    }
    
    .detail-value {
      color: var(--text-primary);
      font-weight: 600;
    }
    
    .role-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .role-admin {
      background: linear-gradient(135deg, #e3f2fd, #bbdefb);
      color: #1976d2;
    }
    
    .role-teacher {
      background: linear-gradient(135deg, #f3e5f5, #e1bee7);
      color: #7b1fa2;
    }
    
    .role-student {
      background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
      color: #388e3c;
    }
    
    .user-actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    
    .edit-btn, .delete-btn {
      flex: 1;
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 500;
      text-decoration: none;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .edit-btn {
      background: var(--accent-color);
      color: white;
    }
    
    .edit-btn:hover {
      background: var(--accent-hover);
      transform: translateY(-1px);
    }
    
    .delete-btn {
      background: #f44336;
      color: white;
    }
    
    .delete-btn:hover {
      background: #d32f2f;
      transform: translateY(-1px);
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
    
    .add-user-btn {
      background: var(--accent-color);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .add-user-btn:hover {
      background: var(--accent-hover);
      transform: translateY(-1px);
    }
    
    /* Search/filter styles */
    .search-filters {
      background: var(--bg-secondary);
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 4px 15px var(--shadow-color);
    }
    
    .search-form {
      display: flex;
      gap: 15px;
      align-items: end;
      flex-wrap: wrap;
    }
    
    .search-input {
      flex: 1;
      min-width: 200px;
      padding: 10px 12px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      font-size: 14px;
    }
    
    .role-select {
      min-width: 150px;
      padding: 10px 12px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      font-size: 14px;
    }
    
    .search-btn {
      background: var(--accent-color);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .search-btn:hover {
      background: var(--accent-hover);
      transform: translateY(-1px);
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .users-grid {
        grid-template-columns: 1fr;
        padding: 15px;
      }
      
      .user-card {
        padding: 15px;
      }
      
      .user-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }
      
      .user-actions {
        flex-direction: column;
      }
      
      .search-form {
        flex-direction: column;
        align-items: stretch;
      }
      
      .search-input,
      .role-select {
        min-width: auto;
      }
    }
  </style>
</head>
<body>
  <header class="header">
    <h1>ERPH System - <?= t('user_management.title') ?></h1>
    <div>
      <a href="admin_dashboard.php"><?= t('common.back') ?><?= t('common.dashboard') ?></a>
      <a href="logout.php"><?= t('common.logout') ?></a>
      </div>
  </header>

  <div class="admin-layout">
    <?php include 'inc/admin_sidebar.php'; ?>

    <main>
      <div class="page-header">
        <h2><?= t('user_management.title') ?></h2>
        <a href="add_user.php" class="add-user-btn"><?= t('user_management.add_user') ?></a>
      </div>

      <div class="search-filters">
        <form class="search-form" method="GET">
          <input type="text" name="search" placeholder="<?= t('user_management.search_placeholder') ?>" value="<?= htmlspecialchars($search) ?>" class="search-input">
          <select name="role" class="role-select">
            <option value=""><?= t('user_management.all_roles') ?></option>
            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>><?= t('common.admin') ?></option>
            <option value="teacher" <?= $role_filter === 'teacher' ? 'selected' : '' ?>><?= t('common.teacher') ?></option>
            <option value="student" <?= $role_filter === 'student' ? 'selected' : '' ?>><?= t('user_management.student') ?></option>
          </select>
          <button type="submit" class="search-btn"><?= t('common.search') ?></button>
        </form>
      </div>

      <div class="users-grid">
        <?php if (empty($users)): ?>
          <div style="text-align:center;padding:40px;color:var(--text-muted);grid-column:1/-1;">
            <?= t('user_management.no_users') ?>
          </div>
        <?php else: ?>
          <?php foreach ($users as $user_item): ?>
          <div class="user-card">
            <div class="user-header">
              <div class="user-avatar">
                <?php if (!empty($user_item['avatar'])): ?>
                  <img src="<?= htmlspecialchars($user_item['avatar']) ?>" alt="User avatar">
                <?php else: ?>
                  <?= glyph('profile') ?>
                <?php endif; ?>
              </div>
              <div class="user-info">
                <h3 class="user-name"><?= htmlspecialchars($user_item['name']) ?></h3>
                <p class="user-email"><?= htmlspecialchars($user_item['email']) ?></p>
              </div>
            </div>
            
            <div class="user-details">
              <div class="user-detail-item">
                <span class="detail-label">ID:</span>
                <span class="detail-value">#<?= htmlspecialchars($user_item['id']) ?></span>
              </div>
              <div class="user-detail-item">
                <span class="detail-label">Role:</span>
                <span class="role-badge role-<?= htmlspecialchars($user_item['role']) ?>">
                  <?= htmlspecialchars(ucfirst($user_item['role'])) ?>
                </span>
              </div>
              <div class="user-detail-item">
                <span class="detail-label">Created at:</span>
                <span class="detail-value"><?= htmlspecialchars($user_item['created_at']) ?></span>
              </div>
            </div>
            
            <div class="user-actions">
              <a href="edit_user.php?id=<?= $user_item['id'] ?>" class="edit-btn"><?= t('common.edit') ?></a>
              <button class="delete-btn" onclick="deleteUser(<?= $user_item['id'] ?>)"><?= t('common.delete') ?></button>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    function deleteUser(userId) {
      if (confirm('<?= t('user_management.confirm_delete') ?>')) {
        // Get delete button element
        const deleteBtn = event.target;
        const originalText = deleteBtn.textContent;
        
        // Show deleting state
        deleteBtn.textContent = '<?= t('user_management.deleting') ?>';
        deleteBtn.disabled = true;
        
        // Send delete request
        fetch('delete_user.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Delete succeeded, show success message
            alert('<?= t('user_management.delete_success') ?>');
            
            // Remove user card from page
            const userCard = deleteBtn.closest('.user-card');
            if (userCard) {
              userCard.remove();
            }
            
            // If no users left, show empty message
            const usersGrid = document.querySelector('.users-grid');
            if (usersGrid && usersGrid.children.length === 0) {
              const noUsersDiv = document.createElement('div');
              noUsersDiv.style.cssText = 'text-align:center;padding:40px;color:var(--text-muted);grid-column:1/-1;';
              noUsersDiv.textContent = '<?= t('user_management.no_users') ?>';
              usersGrid.appendChild(noUsersDiv);
            }
            
            console.log('Delete succeeded:', data);
          } else {
            // Delete failed, show error message
            alert('<?= t('user_management.delete_failed') ?>' + data.message);
            console.error('Delete failed:', data);
          }
        })
        .catch(err => {
          console.error(err);
          alert('<?= t('user_management.delete_error') ?>');
        })
        .finally(() => {
          // Restore button state
          deleteBtn.textContent = originalText;
          deleteBtn.disabled = false;
        });
      }
    }
    
    // Theme toggle feature
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

