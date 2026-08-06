<?php
require_once __DIR__ . '/../inc/bootstrap.php';
begin_json_buffer();

gate_signed_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emit_json(['success' => false, 'message' => lex('errors.method_not_allowed')], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    emit_json(['success' => false, 'message' => lex('errors.invalid_request_data')], 400);
}

$accountId = (int) signed_account()['id'];
$action = $payload['action'] ?? '';

try {
    wire_datastore();

    if ($action !== 'delete_multiple') {
        emit_json(['success' => false, 'message' => lex('teaching_reports.invalid_action')], 400);
    }

    $report_ids = array_values(array_filter(array_map('intval', $payload['report_ids'] ?? [])));
    if ($report_ids === []) {
        emit_json(['success' => false, 'message' => lex('teaching_reports.no_records_selected')], 400);
    }

    $placeholders = implode(',', array_fill(0, count($report_ids), '?'));
    $ownership = array_merge($report_ids, [$accountId]);

    $check_stmt = $pdo->prepare("SELECT id FROM attendance WHERE id IN ($placeholders) AND user_id = ?");
    $check_stmt->execute($ownership);
    $valid_ids = $check_stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($valid_ids) !== count($report_ids)) {
        emit_json(['success' => false, 'message' => lex('teaching_reports.partial_records_unauthorized')], 403);
    }

    $pdo->beginTransaction();

    $plan_stmt = $pdo->prepare(
        "SELECT lesson_plan_id FROM attendance WHERE id IN ($placeholders) AND user_id = ? AND lesson_plan_id IS NOT NULL"
    );
    $plan_stmt->execute($ownership);
    $lesson_plan_ids = array_values(array_filter($plan_stmt->fetchAll(PDO::FETCH_COLUMN)));

    $delete_stmt = $pdo->prepare("DELETE FROM attendance WHERE id IN ($placeholders) AND user_id = ?");
    $delete_stmt->execute($ownership);
    $deleted_count = $delete_stmt->rowCount();

    $deleted_lesson_plans = 0;
    if ($lesson_plan_ids !== []) {
        $plan_placeholders = implode(',', array_fill(0, count($lesson_plan_ids), '?'));
        $delete_plans = $pdo->prepare(
            "DELETE FROM lesson_plans WHERE id IN ($plan_placeholders) AND created_by = ?"
        );
        $delete_plans->execute(array_merge($lesson_plan_ids, [$accountId]));
        $deleted_lesson_plans = $delete_plans->rowCount();
    }

    $pdo->commit();

    $message = lex('teaching_reports.delete_success_count', ['count' => $deleted_count]);
    if ($deleted_lesson_plans > 0) {
        $message .= lex('teaching_reports.delete_success_with_plans', ['count' => $deleted_lesson_plans]);
    }

    emit_json([
        'success' => true,
        'message' => $message,
        'deleted_count' => $deleted_count,
        'deleted_lesson_plans' => $deleted_lesson_plans,
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('delete_attendance db: ' . $e->getMessage());
    emit_json(['success' => false, 'message' => lex('errors.database_operation_failed')], 500);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('delete_attendance: ' . $e->getMessage());
    emit_json(['success' => false, 'message' => lex('errors.operation_failed', ['error' => $e->getMessage()])], 500);
}
