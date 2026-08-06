<?php
require_once __DIR__ . '/inc/bootstrap.php';
begin_json_buffer();

gate_admin_api();

if (isset($_SESSION['csrf_token']) && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    emit_json(['success' => false, 'message' => lex('errors.csrf_failed')], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emit_json(['success' => false, 'message' => lex('errors.method_not_allowed')], 405);
}

$course_id = (int) ($_POST['course_id'] ?? 0);
if ($course_id <= 0) {
    emit_json(['success' => false, 'message' => lex('course_management.invalid_course_id')], 400);
}

$operator = signed_account();

try {
    wire_datastore();
    $pdo->beginTransaction();

    $course_stmt = $pdo->prepare('SELECT id, title FROM courses WHERE id = ?');
    $course_stmt->execute([$course_id]);
    $course = $course_stmt->fetch();

    if (!$course) {
        throw new RuntimeException(lex('course_management.course_not_found', null, 'Course not found'));
    }

    $count_stmt = $pdo->prepare('SELECT COUNT(*) FROM lesson_plans WHERE course_id = ?');
    $count_stmt->execute([$course_id]);
    $lesson_plans_count = (int) $count_stmt->fetchColumn();

    $attendance_stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance WHERE course_id = ?');
    $attendance_stmt->execute([$course_id]);
    $attendance_count = (int) $attendance_stmt->fetchColumn();

    if ($lesson_plans_count > 0) {
        $pdo->prepare('DELETE FROM lesson_plans WHERE course_id = ?')->execute([$course_id]);
    }

    if ($attendance_count > 0) {
        $pdo->prepare('DELETE FROM attendance WHERE course_id = ?')->execute([$course_id]);
    }

    $pdo->prepare('DELETE FROM courses WHERE id = ?')->execute([$course_id]);

    try {
        $table_exists = $pdo->query("SHOW TABLES LIKE 'system_logs'")->fetch();
        if ($table_exists) {
            $log_stmt = $pdo->prepare(
                'INSERT INTO system_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())'
            );
            $log_stmt->execute([
                $operator['id'],
                'delete_course',
                "Removed course {$course['title']} [#{$course_id}]",
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
        }
    } catch (Exception $ignored) {
    }

    $pdo->commit();

    emit_json([
        'success' => true,
        'message' => lex('course_management.delete_success', null, 'Course deleted successfully'),
        'course_id' => $course_id,
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    emit_json([
        'success' => false,
        'message' => lex('course_management.delete_failed', null, 'Delete failed') . ': ' . $e->getMessage(),
    ], 500);
}
