<?php
// course_management.php - Course management page
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

$navSection = 'courses';

$activeAccount = signed_account();

// Get course list
try {
    require_once __DIR__ . '/../db.php';
    
    $search = $_GET['search'] ?? '';
    $teacher_filter = $_GET['teacher'] ?? '';
    
    $where_conditions = [];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($teacher_filter) {
        $where_conditions[] = "ct.teacher_id = ?";
        $params[] = $teacher_filter;
    }
    
    $where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Check whether course_teachers table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'course_teachers'")->fetch();
    
    if ($table_check) {
        // Use new multi-teacher structure
        $sql = "SELECT c.*, 
                       GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') as teacher_names,
                       GROUP_CONCAT(u.id ORDER BY u.name) as teacher_ids
                FROM courses c 
                LEFT JOIN course_teachers ct ON c.id = ct.course_id
                LEFT JOIN users u ON ct.teacher_id = u.id 
                $where_clause 
                GROUP BY c.id
                ORDER BY c.created_at DESC";
    } else {
        // Use legacy single-teacher structure
        $sql = "SELECT c.*, u.name as teacher_names, u.id as teacher_ids
                FROM courses c 
                LEFT JOIN users u ON c.teacher_id = u.id 
                $where_clause 
                ORDER BY c.created_at DESC";
        
        // Adjust filter conditions for legacy structure
        if ($teacher_filter && !empty($where_conditions)) {
            $where_conditions = [];
            $params = [];
            
            if ($search) {
                $where_conditions[] = "(c.title LIKE ? OR c.description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $where_conditions[] = "c.teacher_id = ?";
            $params[] = $teacher_filter;
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    
    // Get all teachers list (for filter)
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name");
    $teachers = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = t('course_management.get_courses_failed') . ": " . $e->getMessage();
    $courses = [];
    $teachers = [];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('course_management.title') ?> - ERPH</title>
  <link rel="stylesheet" href="assets/css/admin.css?v=<?= filemtime(__DIR__ . '/assets/css/admin.css') ?>">
  <link rel="stylesheet" href="assets/css/mobile-optimization.css">
  <style>
    /* Force style priority - highest level */
    @import url('data:text/css, .course-teacher, .course-teacher *, .course-header .course-teacher, .course-header .course-teacher * { border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; }');
    
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
    [data-theme="dark"] .course-card {
      background: linear-gradient(135deg, var(--bg-secondary), var(--card-hover-bg)) !important;
      border: 1px solid var(--border-color) !important;
      box-shadow: 0 8px 25px var(--shadow-color) !important;
      backdrop-filter: blur(10px) !important;
    }
    
    [data-theme="dark"] .page-header:hover,
    [data-theme="dark"] .search-filters:hover,
    [data-theme="dark"] .course-card:hover {
      background: var(--card-hover-bg) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px var(--card-hover-shadow) !important;
      border-color: var(--accent-color) !important;
    }
    
    /* Dark mode course card header polish */
    [data-theme="dark"] .course-header {
      background: linear-gradient(135deg, var(--accent-color), var(--accent-hover)) !important;
      color: white !important;
      box-shadow: 0 4px 15px var(--accent-color) !important;
    }
    
    /* Dark mode button polish */
    [data-theme="dark"] .add-course-btn,
    [data-theme="dark"] .search-btn {
      background: linear-gradient(135deg, var(--accent-color), var(--accent-hover)) !important;
      color: white !important;
      border: none !important;
      box-shadow: 0 4px 15px var(--accent-color) !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    [data-theme="dark"] .add-course-btn:hover,
    [data-theme="dark"] .search-btn:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px var(--accent-color) !important;
    }
    
    /* Dark mode input polish */
    [data-theme="dark"] .search-input,
    [data-theme="dark"] .teacher-select {
      background: var(--bg-primary) !important;
      border: 2px solid var(--border-color) !important;
      color: var(--text-primary) !important;
      transition: all 0.3s ease !important;
    }
    
    [data-theme="dark"] .search-input:focus,
    [data-theme="dark"] .teacher-select:focus {
      border-color: var(--accent-color) !important;
      box-shadow: 0 0 0 3px rgba(100, 181, 246, 0.1) !important;
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
    [data-theme="dark"] .course-card {
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
    
    /* Keep original styles unchanged */
    .container { max-width: 1200px; margin: 0; padding: 0; }
    .page-wrap { display:flex; flex-direction:column; gap:16px; }
    .courses-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap:20px; }
    .course-card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.08); transition:transform .2s; }
    .course-card:hover { transform: translateY(-2px); }
    .course-header { background: linear-gradient(135deg, #2563eb, #3b82f6); color:#fff; padding:18px; }
    .course-header h3 { font-size:18px; margin-bottom:6px; }
    .course-teacher { font-size:14px; opacity:.9; line-height:1.4; }
    .course-body { padding:18px; }
    .course-description { color:#666; margin-bottom:12px; line-height:1.5; }
    .course-meta { display:flex; justify-content:space-between; font-size:13px; color:#888; margin-bottom:12px; }
    .course-actions { display:flex; gap:10px; }
    .edit-btn, .delete-btn { padding:8px 16px; border:none; border-radius:6px; font-size:12px; text-decoration:none; display:inline-block; transition:background .2s; }
    .edit-btn { background:#2563eb; color:#fff; }
    .delete-btn { background:#f44336; color:#fff; }
    .delete-btn:disabled { background:#ccc; cursor:not-allowed; opacity:.7; }
    
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
    
    .add-course-btn {
      background: var(--accent-color);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .add-course-btn:hover {
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
    
    .teacher-select {
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
    
    /* Course card style polish */
    .course-card {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      box-shadow: 0 4px 15px var(--shadow-color);
      transition: all 0.3s ease;
    }
    
    .course-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px var(--shadow-color);
    }
    
    .course-header {
      background: var(--header-bg);
      color: white;
      padding: 18px;
      transition: all 0.3s ease;
    }
    
    .course-header h3 {
      color: white;
      margin-bottom: 6px;
    }
    
    .course-teacher {
      color: rgba(255, 255, 255, 0.9);
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    
    /* Ensure course card header has no border */
    .course-header {
      background: var(--header-bg);
      color: white;
      padding: 18px;
      transition: all 0.3s ease;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
    }
    
    /* Globally ensure course-related elements have no border */
    .course-card *,
    .course-header *,
    .course-teacher,
    .course-teacher * {
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
    }
    
    /* Force remove all possible borders - highest priority */
    .course-teacher,
    .course-teacher *,
    .course-header,
    .course-header *,
    .course-card,
    .course-card * {
      border: 0 !important;
      border-width: 0 !important;
      border-style: none !important;
      border-color: transparent !important;
      outline: 0 !important;
      outline-width: 0 !important;
      outline-style: none !important;
      outline-color: transparent !important;
      box-shadow: none !important;
      -webkit-box-shadow: none !important;
      -moz-box-shadow: none !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Forced styles specifically for assigned teacher field */
    div.course-teacher,
    div.course-teacher *,
    .course-header div.course-teacher,
    .course-header div.course-teacher * {
      all: unset !important;
      display: block !important;
      color: rgba(255, 255, 255, 0.9) !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    
    /* Use highest-priority selectors to force-remove borders */
    body .course-teacher,
    body .course-teacher *,
    body .course-header .course-teacher,
    body .course-header .course-teacher *,
    html body .course-teacher,
    html body .course-teacher *,
    html body .course-header .course-teacher,
    html body .course-header .course-teacher * {
      all: unset !important;
      display: block !important;
      color: rgba(255, 255, 255, 0.9) !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Highest priority - attribute selectors and multi-selector combo */
    [data-theme="dark"] .course-teacher,
    [data-theme="dark"] .course-teacher *,
    [data-theme="dark"] .course-header .course-teacher,
    [data-theme="dark"] .course-header .course-teacher *,
    [data-theme="dark"] .course-card .course-teacher,
    [data-theme="dark"] .course-card .course-teacher *,
    [data-theme="dark"] .course-card .course-header .course-teacher,
    [data-theme="dark"] .course-card .course-header .course-teacher * {
      all: unset !important;
      display: block !important;
      color: rgba(255, 255, 255, 0.9) !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Use ID selector to raise priority */
    #course-management .course-teacher,
    #course-management .course-teacher *,
    #course-management .course-header .course-teacher,
    #course-management .course-header .course-teacher * {
      all: unset !important;
      display: block !important;
      color: rgba(255, 255, 255, 0.9) !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Highest-priority selector - multi attribute selectors */
    [data-theme="dark"] #course-management .course-teacher,
    [data-theme="dark"] #course-management .course-teacher *,
    [data-theme="dark"] #course-management .course-header .course-teacher,
    [data-theme="dark"] #course-management .course-header .course-teacher *,
    [data-theme="dark"] #course-management .course-card .course-teacher,
    [data-theme="dark"] #course-management .course-card .course-teacher *,
    [data-theme="dark"] #course-management .course-card .course-header .course-teacher,
    [data-theme="dark"] #course-management .course-card .course-header .course-teacher * {
      all: unset !important;
      display: block !important;
      color: #000000 !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Subject title styles in dark mode */
    [data-theme="dark"] #course-management .course-header h3,
    [data-theme="dark"] #course-management .course-card .course-header h3 {
      all: unset !important;
      display: block !important;
      color: #000000 !important;
      font-size: 18px !important;
      font-weight: bold !important;
      margin-bottom: 6px !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 0 6px 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Use CSS !important and highest-priority selectors */
    html[data-theme="dark"] body#course-management .course-teacher,
    html[data-theme="dark"] body#course-management .course-teacher *,
    html[data-theme="dark"] body#course-management .course-header .course-teacher,
    html[data-theme="dark"] body#course-management .course-header .course-teacher * {
      all: unset !important;
      display: block !important;
      color: #000000 !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Remove black border from subject title */
    html[data-theme="dark"] body#course-management .course-header h3,
    html[data-theme="dark"] body#course-management .course-card .course-header h3,
    body#course-management .course-header h3,
    body#course-management .course-card .course-header h3 {
      all: unset !important;
      display: block !important;
      color: #000000 !important;
      font-size: 18px !important;
      font-weight: bold !important;
      margin-bottom: 6px !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 0 !important;
      margin: 0 0 6px 0 !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Ensure course card header has no border */
    html[data-theme="dark"] body#course-management .course-header,
    body#course-management .course-header {
      all: unset !important;
      display: block !important;
      background: var(--header-bg) !important;
      color: #000000 !important;
      padding: 18px !important;
      transition: all 0.3s ease !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* All children of course card header have no border in dark mode */
    html[data-theme="dark"] body#course-management .course-header *,
    body#course-management .course-header * {
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    /* Forced styles specifically for dark mode */
    [data-theme="dark"] .course-card .course-header,
    [data-theme="dark"] .course-card .course-header *,
    [data-theme="dark"] .course-card .course-header h3,
    [data-theme="dark"] .course-card .course-header .course-teacher {
      all: unset !important;
      display: block !important;
      border: 0 !important;
      outline: 0 !important;
      box-shadow: none !important;
      background: transparent !important;
      text-decoration: none !important;
      text-decoration-line: none !important;
      text-decoration-style: none !important;
      text-decoration-color: transparent !important;
    }
    
    [data-theme="dark"] .course-card .course-header h3 {
      color: #000000 !important;
      font-size: 18px !important;
      font-weight: bold !important;
      margin: 0 0 6px 0 !important;
      padding: 0 !important;
    }
    
    [data-theme="dark"] .course-card .course-header .course-teacher {
      color: #000000 !important;
      font-size: 14px !important;
      opacity: 0.9 !important;
      line-height: 1.4 !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    
    .course-description {
      color: var(--text-secondary);
    }
    
    .course-meta {
      color: var(--text-muted);
    }
  </style>
</head>
<body id="course-management">
  <header class="header">
    <h1>ERPH System - <?= t('course_management.title') ?></h1>
    <div>
      <a href="admin_dashboard.php"><?= t('common.back') ?><?= t('common.dashboard') ?></a>
      <a href="logout.php"><?= t('common.logout') ?></a>
      </div>
  </header>

  <div class="admin-layout">
    <?php include 'inc/admin_sidebar.php'; ?>

    <main>
      <div class="container page-wrap">
        <div class="page-header">
          <h2><?= t('course_management.title') ?></h2>
          <a href="add_course.php" class="add-course-btn"><?= t('course_management.add_course') ?></a>
        </div>

        <div class="search-filters">
          <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="<?= t('course_management.search_placeholder') ?>" value="<?= htmlspecialchars($search) ?>" class="search-input">
            <select name="teacher" class="teacher-select">
              <option value=""><?= t('course_management.all_teachers') ?></option>
              <?php foreach ($teachers as $teacher): ?>
                <option value="<?= $teacher['id'] ?>" <?= $teacher_filter == $teacher['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($teacher['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="search-btn"><?= t('common.search') ?></button>
          </form>
        </div>

        <?php if (empty($courses)): ?>
          <div class="admin-main-card" style="text-align:center; color:#666;"><?= t('course_management.no_courses') ?></div>
        <?php else: ?>
          <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
              <div class="course-card">
                <div class="course-header">
                  <h3 style="border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; color: #000000 !important; font-size: 18px !important; font-weight: bold !important;"><?= htmlspecialchars($course['title']) ?></h3>
                </div>
                <div class="course-body">
                  <div class="course-description">
                    <?= htmlspecialchars($course['description'] ?? t('dashboard.no_description')) ?>
                  </div>
                  <div class="course-meta">
                    <span><?= t('user_management.created_at') ?>: <?= htmlspecialchars($course['created_at']) ?></span>
                    <span><?= t('common.status') ?>: <?= $course['is_active'] ? t('course_management.active', 'Active') : t('course_management.inactive', 'Inactive') ?></span>
                  </div>
                  <div class="course-actions">
                    <a href="edit_course.php?id=<?= $course['id'] ?>" class="edit-btn"><?= t('common.edit') ?></a>
                    <button class="delete-btn" onclick="deleteCourse(<?= $course['id'] ?>, '<?= htmlspecialchars(addslashes($course['title'])) ?>')"><?= t('common.delete') ?></button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    function deleteCourse(courseId, courseTitle) {
      if (confirm('<?= t('course_management.confirm_delete', 'Are you sure you want to delete course ') ?>"' + courseTitle + '"<?= t('course_management.confirm_delete_warning', '?\\n\\nThis will:\\n• Permanently delete the course\\n• Delete related lesson plans\\n• Delete related attendance records\\n\\nThis action cannot be undone!') ?>')) {
        const deleteBtn = event.target;
        const originalText = deleteBtn.textContent;
        deleteBtn.textContent = '<?= t('course_management.deleting', 'Deleting...') ?>';
        deleteBtn.disabled = true;
        fetch('delete_course.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'course_id=' + courseId })
          .then(r => r.json())
          .then(data => { if (data.success) { alert(data.message); location.reload(); } else { alert('<?= t('course_management.delete_failed', 'Delete failed: ') ?>' + data.message); deleteBtn.textContent = originalText; deleteBtn.disabled = false; } })
          .catch(err => { console.error(err); alert('<?= t('course_management.delete_error', 'An error occurred while deleting the course. Please try again.') ?>'); deleteBtn.textContent = originalText; deleteBtn.disabled = false; });
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
      
      // Force remove all borders from course teacher field
      function removeTeacherBorders() {
        const headerElements = document.querySelectorAll('.course-header h3');
        
        // Handle subject title
        headerElements.forEach(function(element) {
          element.setAttribute('style', 'border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; color: #000000 !important; font-size: 18px !important; font-weight: bold !important; display: block !important;');
        });
        
        // Force style recalculation
        headerElements.forEach(function(element) {
          element.style.cssText = 'border: none !important; outline: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; color: #000000 !important; font-size: 18px !important; font-weight: bold !important; display: block !important;';
        });
      }
      
      // Run once immediately
      removeTeacherBorders();
      
      // Run once delayed to ensure all elements rendered
      setTimeout(removeTeacherBorders, 100);
      
      // Watch DOM changes so dynamically added elements also have no border
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.addedNodes.length > 0) {
            removeTeacherBorders();
          }
        });
      });
      
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    });
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

