<?php
// test_teacher_dashboard_translations.php - Test teacher dashboard translations
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Set language to English for testing
$_SESSION['lang'] = 'en';
$current_language = $_SESSION['lang'];
$translations = loadTranslations($current_language);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Test Teacher Dashboard Translations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-item { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        .key { font-weight: bold; color: #333; }
        .value { color: #666; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Teacher Dashboard Translation Test</h1>
    <p>Current Language: <strong><?= $current_language ?></strong></p>
    
    <h2>Testing Translation Keys:</h2>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.title:</div>
        <div class="value"><?= t('teacher_dashboard.title') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.welcome_back:</div>
        <div class="value"><?= t('teacher_dashboard.welcome_back') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.my_courses:</div>
        <div class="value"><?= t('teacher_dashboard.my_courses') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.attendance_records:</div>
        <div class="value"><?= t('teacher_dashboard.attendance_records') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.my_lesson_plans:</div>
        <div class="value"><?= t('teacher_dashboard.my_lesson_plans') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.quick_functions:</div>
        <div class="value"><?= t('teacher_dashboard.quick_functions') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.teaching_reports:</div>
        <div class="value"><?= t('teacher_dashboard.teaching_reports') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.teaching_reports_desc:</div>
        <div class="value"><?= t('teacher_dashboard.teaching_reports_desc') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.lesson_plans:</div>
        <div class="value"><?= t('teacher_dashboard.lesson_plans') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.lesson_plans_desc:</div>
        <div class="value"><?= t('teacher_dashboard.lesson_plans_desc') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.my_courses_desc:</div>
        <div class="value"><?= t('teacher_dashboard.my_courses_desc') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.profile_desc:</div>
        <div class="value"><?= t('teacher_dashboard.profile_desc') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.recent_courses:</div>
        <div class="value"><?= t('teacher_dashboard.recent_courses') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">teacher_dashboard.no_courses:</div>
        <div class="value"><?= t('teacher_dashboard.no_courses') ?></div>
    </div>
    
    <h2>Common Keys Test:</h2>
    
    <div class="test-item">
        <div class="key">common.back:</div>
        <div class="value"><?= t('common.back') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.dashboard:</div>
        <div class="value"><?= t('common.dashboard') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.logout:</div>
        <div class="value"><?= t('common.logout') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.teacher:</div>
        <div class="value"><?= t('common.teacher') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.profile:</div>
        <div class="value"><?= t('common.profile') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.settings:</div>
        <div class="value"><?= t('common.settings') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.avatar:</div>
        <div class="value"><?= t('common.avatar') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.language:</div>
        <div class="value"><?= t('common.language') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.theme:</div>
        <div class="value"><?= t('common.theme') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.light_mode:</div>
        <div class="value"><?= t('common.light_mode') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.dark_mode:</div>
        <div class="value"><?= t('common.dark_mode') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.created_at:</div>
        <div class="value"><?= t('common.created_at') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.switch_to_light:</div>
        <div class="value"><?= t('common.switch_to_light') ?></div>
    </div>
    
    <div class="test-item">
        <div class="key">common.switch_to_dark:</div>
        <div class="value"><?= t('common.switch_to_dark') ?></div>
    </div>
    
    <hr>
    <p><a href="teacher_dashboard.php">Back to Teacher Dashboard</a></p>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

