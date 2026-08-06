<?php
require_once __DIR__ . '/inc/bootstrap.php';
begin_json_buffer();

gate_admin_api();

$report_id = (int) ($_GET['id'] ?? 0);
if ($report_id <= 0) {
    emit_json(['success' => false, 'message' => lex('teaching_reports.report_id_required', null, 'Report ID is required')], 400);
}

try {
    wire_datastore();

    $sql = "
        SELECT
            a.id,
            a.date,
            a.status,
            a.check_in,
            a.check_out,
            a.notes,
            a.created_at,
            u.name AS teacher_name,
            u.email AS teacher_email,
            u.id AS teacher_id,
            c.title AS course_title,
            c.description AS course_description,
            c.id AS course_id
        FROM attendance a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN courses c ON a.course_id = c.id
        WHERE a.id = ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();

    if (!$report) {
        emit_json(['success' => false, 'message' => lex('teaching_reports.report_not_found', null, 'Teaching report not found')], 404);
    }

    emit_json(['success' => true, 'report' => $report]);
} catch (Exception $e) {
    emit_json([
        'success' => false,
        'message' => lex('teaching_reports.fetch_failed', null, 'Failed to load teaching report') . ': ' . $e->getMessage(),
    ], 500);
}
