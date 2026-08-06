<?php
// edit_class.php - Handle class edit request
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

require_once __DIR__ . '/../db.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = intval($_POST['edit_id']);
    $name = trim($_POST['name'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') {
        $error = t('classes.enter_name');
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE classes SET name = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $is_active, $edit_id]);
            $msg = t('classes.update_success');
        } catch (Throwable $e) {
            $error = t('errors.update_failed') . ': ' . $e->getMessage();
        }
    }
}

// Redirect back to class management page
if ($error) {
    $_SESSION['error'] = $error;
} else {
    $_SESSION['msg'] = $msg;
}

header('Location: classes.php');
exit;
?>

