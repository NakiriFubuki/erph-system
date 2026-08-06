<?php
// test_login_language.php - Test login page language switching
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Handle language switch request
if (isset($_GET['lang']) && in_array($_GET['lang'], ['zh', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    echo "Language switched to: " . $_GET['lang'] . "<br>";
    echo "Current session language: " . $_SESSION['lang'] . "<br>";
    echo "Translation test - title: " . t('login.title') . "<br>";
    echo "Translation test - select role: " . t('login.select_role') . "<br>";
    echo "Translation test - admin: " . t('login.admin') . "<br>";
    echo "Translation test - teacher: " . t('login.teacher') . "<br>";
    echo "Translation test - email: " . t('login.email') . "<br>";
    echo "Translation test - password: " . t('login.password') . "<br>";
    echo "Translation test - login button: " . t('login.login_button') . "<br>";
    echo "<br><a href='?lang=zh'>Switch to Chinese</a> | <a href='?lang=en'>Switch to English</a>";
} else {
    echo "Current session language: " . ($_SESSION['lang'] ?? 'Not set') . "<br>";
    echo "Translation test - title: " . t('login.title') . "<br>";
    echo "Translation test - select role: " . t('login.select_role') . "<br>";
    echo "Translation test - admin: " . t('login.admin') . "<br>";
    echo "Translation test - teacher: " . t('login.teacher') . "<br>";
    echo "Translation test - email: " . t('login.email') . "<br>";
    echo "Translation test - password: " . t('login.password') . "<br>";
    echo "Translation test - login button: " . t('login.login_button') . "<br>";
    echo "<br><a href='?lang=zh'>Switch to Chinese</a> | <a href='?lang=en'>Switch to English</a>";
}
?>

