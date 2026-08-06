<?php
// check_session_theme.php - Check session theme status
session_start();

if (isset($_SESSION['theme'])) {
    echo json_encode(['success' => true, 'theme' => $_SESSION['theme']]);
} else {
    echo json_encode(['success' => false, 'theme' => 'light']);
}
?>


