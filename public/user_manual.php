<?php
// user_manual.php - System User Manual
require_once __DIR__ . '/inc/bootstrap.php';

gate_signed_in();

$activeAccount = signed_account();
$user_role = $activeAccount['role'];
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('manual.title', 'User Manual') ?></title>
  <link rel="stylesheet" href="assets/css/manual.css?v=<?= @filemtime(__DIR__ . '/assets/css/manual.css') ?>">
  <?php include __DIR__ . '/inc/shot_assets.php'; ?>
  <style>
    /* Reset styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    /* Manual page styles */
    body {
      background: linear-gradient(135deg, #2563eb 0%, #87ceeb 50%, #e6f3ff 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
    }
    
    .manual-container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 40px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(74, 144, 226, 0.2);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    
    .manual-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #2563eb, #87ceeb, #2563eb);
      border-radius: 20px 20px 0 0;
    }
    
    .manual-header {
      text-align: center;
      margin-bottom: 40px;
      padding-bottom: 30px;
      border-bottom: 2px solid #2563eb;
      position: relative;
    }
    
    .manual-title {
      color: #2563eb;
      font-size: 36px;
      font-weight: 800;
      margin-bottom: 15px;
      text-shadow: 0 2px 4px rgba(74, 144, 226, 0.3);
      background: linear-gradient(135deg, #2563eb, #87ceeb);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .manual-subtitle {
      color: #666;
      font-size: 18px;
      margin-bottom: 20px;
      font-weight: 300;
    }
    
    .role-badge {
      display: inline-block;
      background: linear-gradient(135deg, #2563eb, #87ceeb);
      color: white;
      padding: 10px 25px;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      margin-top: 15px;
      box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    .section {
      margin-bottom: 40px;
      animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .section-title {
      color: #2563eb;
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 20px;
      padding-left: 15px;
      border-left: 5px solid #2563eb;
      position: relative;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 15px;
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, #2563eb, transparent);
      border-radius: 2px;
    }
    
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 25px;
      margin-bottom: 25px;
    }
    
    .feature-card {
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(74, 144, 226, 0.2);
      border-radius: 15px;
      padding: 25px;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      border-left: 5px solid #2563eb;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(74, 144, 226, 0.1);
    }
    
    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), rgba(135, 206, 235, 0.05));
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .feature-card:hover::before {
      opacity: 1;
    }
    
    .feature-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 15px 40px rgba(74, 144, 226, 0.2);
      border-color: #2563eb;
    }
    
    .feature-icon {
      font-size: 32px;
      margin-bottom: 15px;
      color: #2563eb;
      display: block;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-10px); }
      60% { transform: translateY(-5px); }
    }
    
    .feature-title {
      color: #333;
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 12px;
      position: relative;
      z-index: 1;
    }
    
    .feature-desc {
      color: #666;
      font-size: 15px;
      line-height: 1.6;
      position: relative;
      z-index: 1;
    }
    
    .quick-start {
      background: linear-gradient(135deg, #2563eb, #87ceeb);
      color: white;
      padding: 30px;
      border-radius: 15px;
      margin-bottom: 30px;
      position: relative;
      overflow: hidden;
    }
    
    .quick-start::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .quick-start h3 {
      margin: 0 0 20px 0;
      font-size: 22px;
      font-weight: 700;
      position: relative;
      z-index: 1;
    }
    
    .quick-start ol {
      margin: 0;
      padding-left: 25px;
      position: relative;
      z-index: 1;
    }
    
    .quick-start li {
      margin-bottom: 12px;
      line-height: 1.6;
      font-size: 16px;
      position: relative;
    }
    
    .quick-start li::marker {
      color: rgba(255, 255, 255, 0.8);
      font-weight: bold;
    }
    
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #2563eb, #87ceeb);
      color: white;
      padding: 12px 24px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      margin-bottom: 25px;
      box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .back-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .back-btn:hover::before {
      left: 100%;
    }
    
    .back-btn:hover {
      background: linear-gradient(135deg, #87ceeb, #2563eb);
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
    }
    
    .back-btn:active {
      transform: translateY(0) scale(0.98);
    }
    
    .back-icon {
      font-size: 16px;
      transition: transform 0.3s ease;
    }
    
    .back-btn:hover .back-icon {
      transform: translateX(-3px);
    }
    
    /* Dark mode styles */
    [data-theme="dark"] body {
      background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1e40af 100%);
    }
    
    [data-theme="dark"] .manual-container {
      background: rgba(30, 58, 138, 0.95);
      border: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    [data-theme="dark"] .feature-card {
      background: rgba(30, 58, 138, 0.8);
      border: 1px solid rgba(59, 130, 246, 0.3);
    }
    
    [data-theme="dark"] .feature-card:hover {
      background: rgba(30, 58, 138, 0.9);
    }
    
    [data-theme="dark"] .manual-title {
      color: #93c5fd;
    }
    
    [data-theme="dark"] .manual-subtitle {
      color: #cbd5e1;
    }
    
    [data-theme="dark"] .feature-title {
      color: #f1f5f9;
    }
    
    [data-theme="dark"] .feature-desc {
      color: #cbd5e1;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .manual-container {
        margin: 10px;
        padding: 20px;
        border-radius: 15px;
      }
      
      .manual-title {
        font-size: 24px;
      }
      
      .manual-subtitle {
        font-size: 16px;
      }
      
      .feature-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .feature-card {
        padding: 20px;
      }
      
      .quick-start {
        padding: 20px;
      }
      
      .section-title {
        font-size: 20px;
      }
    }
    
    @media (max-width: 480px) {
      .manual-container {
        margin: 5px;
        padding: 15px;
      }
      
      .manual-title {
        font-size: 20px;
      }
      
      .feature-card {
        padding: 15px;
      }
      
      .quick-start {
        padding: 15px;
      }
    }
    
    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 8px;
    }
    
    ::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }
    
    ::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #2563eb, #87ceeb);
      border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(135deg, #87ceeb, #2563eb);
    }
  </style>
</head>
<body>
  <div class="manual-container">
    <a href="javascript:history.back()" class="back-btn">
      <span class="back-icon"><?= glyph('arrow-left') ?></span>
      <span><?= t('common.back', 'Back') ?></span>
    </a>
    
    <div class="manual-header">
      <h1 class="manual-title"><?= t('manual.title', 'User Manual') ?></h1>
      <p class="manual-subtitle"><?= t('manual.subtitle', 'ERPH User Guide — step-by-step instructions') ?></p>
      <div class="role-badge">
        <?php if ($user_role === 'admin'): ?>
          <?= t('roles.admin', 'Administrator') ?>
        <?php elseif ($user_role === 'teacher'): ?>
          <?= t('roles.teacher', 'Teacher') ?>
        <?php else: ?>
          <?= t('roles.student', 'Student') ?>
        <?php endif; ?>
      </div>
    </div>

    <nav class="manual-toc" aria-label="<?= t('manual.toc', 'On this page') ?>">
      <h3><?= t('manual.toc', 'On this page') ?></h3>
      <ol>
        <li><a href="#login-guide"><?= t('manual.toc_login', 'How to log in') ?></a></li>
        <li><a href="#howto"><?= t('manual.toc_howto', 'How-to guides') ?></a></li>
        <li><a href="#features"><?= t('manual.toc_features', 'Feature overview') ?></a></li>
        <li><a href="#common"><?= t('manual.toc_common', 'Common features') ?></a></li>
        <li><a href="#help"><?= t('manual.toc_help', 'Help') ?></a></li>
      </ol>
    </nav>

    <div class="quick-start" id="login-guide">
      <h3><?= t('manual.quick_start', 'Quick Start: How to Log In') ?></h3>
      <ol>
        <li><?= t('manual.step1') ?></li>
        <li><?= t('manual.step2') ?></li>
        <li><?= t('manual.step3') ?></li>
        <li><?= t('manual.step4') ?></li>
      </ol>
      <p style="margin:16px 0 0;position:relative;z-index:1;opacity:.95;font-size:14px;"><?= t('manual.login_tip') ?></p>
    </div>

    <div class="section" id="howto">
      <h2 class="section-title"><?= t('manual.howto_section', 'How-to Guides') ?></h2>

      <?php if ($user_role === 'admin'): ?>
        <div class="guide-block">
          <h3><?= t('manual.guide_nav') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_nav_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_nav_1') ?></li>
            <li><?= t('manual.guide_nav_2') ?></li>
            <li><?= t('manual.guide_nav_3') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_add_user') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_add_user_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_add_user_1') ?></li>
            <li><?= t('manual.guide_add_user_2') ?></li>
            <li><?= t('manual.guide_add_user_3') ?></li>
            <li><?= t('manual.guide_add_user_4') ?></li>
            <li><?= t('manual.guide_add_user_5') ?></li>
          </ol>
          <div class="guide-tip"><?= t('manual.guide_add_user_tip') ?></div>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_add_course') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_add_course_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_add_course_1') ?></li>
            <li><?= t('manual.guide_add_course_2') ?></li>
            <li><?= t('manual.guide_add_course_3') ?></li>
            <li><?= t('manual.guide_add_course_4') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_reports_admin') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_reports_admin_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_reports_admin_1') ?></li>
            <li><?= t('manual.guide_reports_admin_2') ?></li>
            <li><?= t('manual.guide_reports_admin_3') ?></li>
            <li><?= t('manual.guide_reports_admin_4') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_classes') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_classes_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_classes_1') ?></li>
            <li><?= t('manual.guide_classes_2') ?></li>
            <li><?= t('manual.guide_classes_3') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_textbooks') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_textbooks_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_textbooks_1') ?></li>
            <li><?= t('manual.guide_textbooks_2') ?></li>
            <li><?= t('manual.guide_textbooks_3') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_background') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_background_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_background_1') ?></li>
            <li><?= t('manual.guide_background_2') ?></li>
            <li><?= t('manual.guide_background_3') ?></li>
          </ol>
        </div>

      <?php elseif ($user_role === 'teacher'): ?>
        <div class="guide-block">
          <h3><?= t('manual.guide_teacher_home') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_teacher_home_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_teacher_home_1') ?></li>
            <li><?= t('manual.guide_teacher_home_2') ?></li>
            <li><?= t('manual.guide_teacher_home_3') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_submit_report') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_submit_report_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_submit_report_1') ?></li>
            <li><?= t('manual.guide_submit_report_2') ?></li>
            <li><?= t('manual.guide_submit_report_3') ?></li>
          </ol>
          <div class="guide-tip"><?= t('manual.guide_submit_report_tip') ?></div>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_lesson') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_lesson_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_lesson_1') ?></li>
            <li><?= t('manual.guide_lesson_2') ?></li>
            <li><?= t('manual.guide_lesson_3') ?></li>
            <li><?= t('manual.guide_lesson_4') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_my_courses_t') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_my_courses_t_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_my_courses_t_1') ?></li>
            <li><?= t('manual.guide_my_courses_t_2') ?></li>
            <li><?= t('manual.guide_my_courses_t_3') ?></li>
          </ol>
        </div>

        <div class="guide-block">
          <h3><?= t('manual.guide_profile_t') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_profile_t_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_profile_t_1') ?></li>
            <li><?= t('manual.guide_profile_t_2') ?></li>
            <li><?= t('manual.guide_profile_t_3') ?></li>
          </ol>
        </div>
      <?php else: ?>
        <div class="guide-block">
          <h3><?= t('manual.guide_profile_t') ?></h3>
          <p class="guide-intro"><?= t('manual.guide_profile_t_intro') ?></p>
          <ol class="guide-steps">
            <li><?= t('manual.guide_profile_t_1') ?></li>
            <li><?= t('manual.guide_profile_t_2') ?></li>
            <li><?= t('manual.guide_profile_t_3') ?></li>
          </ol>
        </div>
      <?php endif; ?>

      <div class="guide-block">
        <h3><?= t('manual.guide_common_settings') ?></h3>
        <p class="guide-intro"><?= t('manual.guide_common_settings_intro') ?></p>
        <ol class="guide-steps">
          <li><?= t('manual.guide_common_settings_1') ?></li>
          <li><?= t('manual.guide_common_settings_2') ?></li>
          <li><?= t('manual.guide_common_settings_3') ?></li>
        </ol>
      </div>
    </div>

    <?php if ($user_role === 'admin'): ?>
      <!-- Administrator feature descriptions -->
      <div class="section" id="features">
        <h2 class="section-title"><?= t('manual.admin_features', 'Administrator Features') ?></h2>
        <div class="feature-grid">
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('analytics') ?></div>
            <div class="feature-title"><?= t('manual.dashboard', 'Dashboard') ?></div>
            <div class="feature-desc"><?= t('manual.dashboard_desc', 'View overall system statistics including user count, course count, teaching reports and other key data') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('users') ?></div>
            <div class="feature-title"><?= t('manual.user_management', 'User Management') ?></div>
            <div class="feature-desc"><?= t('manual.user_management_desc', 'Add, edit, delete user accounts, manage user roles and permissions') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('layers') ?></div>
            <div class="feature-title"><?= t('manual.course_management', 'Course Management') ?></div>
            <div class="feature-desc"><?= t('manual.course_management_desc', 'Create and manage courses, assign teachers, set course information') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('clipboard') ?></div>
            <div class="feature-title"><?= t('manual.teaching_reports', 'Teaching Reports') ?></div>
            <div class="feature-desc"><?= t('manual.teaching_reports_desc', 'View all teachers\' teaching reports, monitor teaching progress and attendance') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('manual') ?></div>
            <div class="feature-title"><?= t('manual.textbooks_homework', 'Textbooks & Homework') ?></div>
            <div class="feature-desc"><?= t('manual.textbooks_homework_desc', 'Manage teaching materials and homework assignments, track learning progress') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('school') ?></div>
            <div class="feature-title"><?= t('manual.classes', 'Class Management') ?></div>
            <div class="feature-desc"><?= t('manual.classes_desc', 'Manage class information, assign students to appropriate classes') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('palette') ?></div>
            <div class="feature-title"><?= t('manual.background_manager', 'Login Background Manager') ?></div>
            <div class="feature-desc"><?= t('manual.background_manager_desc', 'Customize login page background to enhance user experience') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('trend') ?></div>
            <div class="feature-title"><?= t('manual.activity_monitor', 'Activity Monitor') ?></div>
            <div class="feature-desc"><?= t('manual.activity_monitor_desc', 'Real-time monitoring of system activities, view user operation logs and statistics') ?></div>
          </div>
        </div>
      </div>

    <?php elseif ($user_role === 'teacher'): ?>
      <!-- Teacher feature descriptions -->
      <div class="section" id="features">
        <h2 class="section-title"><?= t('manual.teacher_features', 'Teacher Features') ?></h2>
        <div class="feature-grid">
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('analytics') ?></div>
            <div class="feature-title"><?= t('manual.teacher_dashboard', 'Teacher Dashboard') ?></div>
            <div class="feature-desc"><?= t('manual.teacher_dashboard_desc', 'View personal teaching statistics including course count, teaching reports, lesson plans, etc.') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('clipboard') ?></div>
            <div class="feature-title"><?= t('manual.submit_reports', 'Submit Teaching Reports') ?></div>
            <div class="feature-desc"><?= t('manual.submit_reports_desc', 'Record daily teaching situations including attendance, teaching content, student performance, etc.') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('layers') ?></div>
            <div class="feature-title"><?= t('manual.my_courses', 'My Courses') ?></div>
            <div class="feature-desc"><?= t('manual.my_courses_desc', 'View and manage courses assigned to you, understand course details and progress') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('manual') ?></div>
            <div class="feature-title"><?= t('manual.lesson_plans', 'Lesson Plan Management') ?></div>
            <div class="feature-desc"><?= t('manual.lesson_plans_desc', 'Upload and manage teaching plans, share teaching resources') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('profile') ?></div>
            <div class="feature-title"><?= t('manual.profile', 'Personal Profile') ?></div>
            <div class="feature-desc"><?= t('manual.profile_desc', 'Manage personal information including avatar, contact details, etc.') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('settings') ?></div>
            <div class="feature-title"><?= t('manual.settings', 'System Settings') ?></div>
            <div class="feature-desc"><?= t('manual.settings_desc', 'Adjust language, theme and other personal preferences') ?></div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- Student feature descriptions -->
      <div class="section" id="features">
        <h2 class="section-title"><?= t('manual.student_features', 'Student Features') ?></h2>
        <div class="feature-grid">
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('analytics') ?></div>
            <div class="feature-title"><?= t('manual.student_dashboard', 'Student Dashboard') ?></div>
            <div class="feature-desc"><?= t('manual.student_dashboard_desc', 'View personal learning statistics including course progress, attendance records, etc.') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('layers') ?></div>
            <div class="feature-title"><?= t('manual.my_courses', 'My Courses') ?></div>
            <div class="feature-desc"><?= t('manual.my_courses_desc', 'View registered courses, understand course schedule and requirements') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('manual') ?></div>
            <div class="feature-title"><?= t('manual.course_materials', 'Course Materials') ?></div>
            <div class="feature-desc"><?= t('manual.course_materials_desc', 'Download and view course-related materials including lesson plans, homework, etc.') ?></div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon"><?= glyph('profile') ?></div>
            <div class="feature-title"><?= t('manual.profile', 'Personal Profile') ?></div>
            <div class="feature-desc"><?= t('manual.profile_desc', 'Manage personal information,including avatar, contact details, etc.') ?></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="section" id="common">
      <h2 class="section-title"><?= t('manual.common_features', 'Common Features') ?></h2>
      <div class="feature-grid">
        <div class="feature-card">
          <div class="feature-icon"><?= glyph('palette') ?></div>
          <div class="feature-title"><?= t('manual.theme_switch', 'Theme Switch') ?></div>
          <div class="feature-desc"><?= t('manual.theme_switch_desc', 'Switch between light and dark themes to adapt to different usage environments') ?></div>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon"><?= glyph('globe') ?></div>
          <div class="feature-title"><?= t('manual.language_switch', 'Language Switch') ?></div>
          <div class="feature-desc"><?= t('manual.language_switch_desc', 'Support Chinese and English interfaces for users of different languages') ?></div>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon"><?= glyph('lock') ?></div>
          <div class="feature-title"><?= t('manual.security', 'Security Features') ?></div>
          <div class="feature-desc"><?= t('manual.security_desc', 'Adopt secure user authentication mechanisms to protect user data and privacy') ?></div>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon"><?= glyph('device') ?></div>
          <div class="feature-title"><?= t('manual.responsive', 'Responsive Design') ?></div>
          <div class="feature-desc"><?= t('manual.responsive_desc', 'Support access from various devices including computers, tablets and phones') ?></div>
        </div>
      </div>
    </div>

    <div class="section" id="help">
      <h2 class="section-title"><?= t('manual.help_support', 'Help & Support') ?></h2>
      <div class="feature-card">
        <div class="feature-icon"><?= glyph('help') ?></div>
        <div class="feature-title"><?= t('manual.need_help', 'Need Help?') ?></div>
        <div class="feature-desc">
          <?= t('manual.help_text', 'If you run into issues, contact the system administrator. Follow the How-to Guides on this page to practice each task.') ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Restore theme on page load
    function initTheme() {
      const savedTheme = sessionStorage.getItem('theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
    }
    
    // Smooth scroll to top
    function scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
    
    // Add scroll-to-top button
    function addScrollToTopButton() {
      const scrollBtn = document.createElement('button');
      scrollBtn.innerHTML = '↑';
      scrollBtn.className = 'scroll-to-top';
      scrollBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent-color), var(--accent-hover));
        color: white;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        opacity: 0;
        visibility: hidden;
        z-index: 1000;
      `;
      
      scrollBtn.addEventListener('click', scrollToTop);
      document.body.appendChild(scrollBtn);
      
      // Listen for scroll events
      window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
          scrollBtn.style.opacity = '1';
          scrollBtn.style.visibility = 'visible';
        } else {
          scrollBtn.style.opacity = '0';
          scrollBtn.style.visibility = 'hidden';
        }
      });
      
      // Hover effects
      scrollBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
        this.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.4)';
      });
      
      scrollBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
        this.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.3)';
      });
    }
    
    // Add card hover effects
    function addCardHoverEffects() {
      const cards = document.querySelectorAll('.feature-card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0) scale(1)';
        });
      });
    }
    
    // Add page load animation
    function addPageLoadAnimation() {
      const sections = document.querySelectorAll('.section');
      sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
          section.style.transition = 'all 0.6s ease-out';
          section.style.opacity = '1';
          section.style.transform = 'translateY(0)';
        }, index * 100);
      });
    }
    
    // Add enhanced back button behavior
    function enhanceBackButton() {
      const backBtn = document.querySelector('.back-btn');
      if (backBtn) {
        backBtn.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Add click animation
          this.style.transform = 'scale(0.95)';
          setTimeout(() => {
            this.style.transform = 'scale(1)';
          }, 150);
          
          // Check whether history exists
          if (window.history.length > 1) {
            window.history.back();
          } else {
            // If there is no history,navigate to the dashboard
            const userRole = '<?= $user_role ?>';
            if (userRole === 'admin') {
              window.location.href = 'admin_dashboard.php';
            } else if (userRole === 'teacher') {
              window.location.href = 'teacher_dashboard.php';
            } else {
              window.location.href = 'index.php';
            }
          }
        });
      }
    }
    
    // Initialize all features after the page finishes loading
    document.addEventListener('DOMContentLoaded', function() {
      initTheme();
      addScrollToTopButton();
      addCardHoverEffects();
      addPageLoadAnimation();
      enhanceBackButton();
      
      // Add fade-in effect after page load completes
      document.body.style.opacity = '0';
      document.body.style.transition = 'opacity 0.5s ease-in';
      setTimeout(() => {
        document.body.style.opacity = '1';
      }, 100);
    });
    
    // Add keyboard shortcut support
    document.addEventListener('keydown', function(e) {
      // ESC key to go back
      if (e.key === 'Escape') {
        const backBtn = document.querySelector('.back-btn');
        if (backBtn) {
          backBtn.click();
        }
      }
      
      // Ctrl + Home to go back to top
      if (e.ctrlKey && e.key === 'Home') {
        e.preventDefault();
        scrollToTop();
      }
    });
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

