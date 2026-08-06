<?php
// change_language.php - Language is fixed to English
require_once __DIR__ . '/inc/session_config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$_SESSION['lang'] = 'en';

echo json_encode([
    'success' => true,
    'message' => 'Language set to English',
    'language' => 'en'
]);
