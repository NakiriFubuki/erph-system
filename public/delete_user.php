<?php
require_once __DIR__ . '/inc/bootstrap.php';
begin_json_buffer();

gate_admin_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emit_json(['success' => false, 'message' => lex('user_management.invalid_request')], 405);
}

$user_id = (int) ($_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    emit_json(['success' => false, 'message' => lex('user_management.user_id_required')], 400);
}

$operator = signed_account();

try {
    wire_datastore();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $target = $stmt->fetch();

    if (!$target) {
        emit_json(['success' => false, 'message' => lex('user_management.user_not_found')], 404);
    }

    if ((int) $target['id'] === (int) $operator['id']) {
        emit_json(['success' => false, 'message' => lex('user_management.cannot_delete_self')], 400);
    }

    if ($target['role'] === 'admin') {
        $admin_count = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($admin_count <= 1) {
            emit_json(['success' => false, 'message' => lex('user_management.cannot_delete_last_admin')], 400);
        }
    }

    $course_count = 0;
    try {
        $probe = $pdo->query("SHOW TABLES LIKE 'course_teachers'");
        if ($probe && $probe->rowCount() > 0) {
            $count_stmt = $pdo->prepare('SELECT COUNT(*) FROM course_teachers WHERE teacher_id = ?');
            $count_stmt->execute([$user_id]);
            $course_count = (int) $count_stmt->fetchColumn();
        }
    } catch (Exception $ignored) {
        $course_count = 0;
    }

    $attendance_stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance WHERE user_id = ?');
    $attendance_stmt->execute([$user_id]);
    $attendance_count = (int) $attendance_stmt->fetchColumn();

    $plan_stmt = $pdo->prepare('SELECT COUNT(*) FROM lesson_plans WHERE created_by = ?');
    $plan_stmt->execute([$user_id]);
    $lesson_plan_count = (int) $plan_stmt->fetchColumn();

    $pdo->beginTransaction();

    if ($course_count > 0) {
        try {
            $pdo->prepare('DELETE FROM course_teachers WHERE teacher_id = ?')->execute([$user_id]);
        } catch (Exception $ignored) {
        }
    }

    if ($attendance_count > 0) {
        $pdo->prepare('DELETE FROM attendance WHERE user_id = ?')->execute([$user_id]);
    }

    if ($lesson_plan_count > 0) {
        $pdo->prepare('DELETE FROM lesson_plans WHERE created_by = ?')->execute([$user_id]);
    }

    $delete_stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $delete_stmt->execute([$user_id]);

    if ($delete_stmt->rowCount() <= 0) {
        throw new RuntimeException(lex('user_management.delete_operation_failed'));
    }

    try {
        $log_stmt = $pdo->prepare(
            'INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)'
        );
        $log_stmt->execute([
            $operator['id'],
            'delete_user',
            "Removed user {$target['name']} ({$target['email']}) [#{$user_id}]",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    } catch (Exception $ignored) {
    }

    $pdo->commit();

    emit_json([
        'success' => true,
        'message' => lex('user_management.delete_success'),
        'deleted_user' => [
            'id' => $target['id'],
            'name' => $target['name'],
            'email' => $target['email'],
            'role' => $target['role'],
        ],
        'cascaded_data' => [
            'courses' => $course_count,
            'attendance' => $attendance_count,
            'lesson_plans' => $lesson_plan_count,
        ],
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    emit_json([
        'success' => false,
        'message' => lex('user_management.delete_failed') . $e->getMessage(),
    ], 500);
}
