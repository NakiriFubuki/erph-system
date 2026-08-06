<?php
/**
 * Attendance / teaching-report status label resolution.
 */

function resolve_attendance_label(string $state): string
{
    $map = [
        'present' => lex('teaching_reports.status_present'),
        'absent' => lex('teaching_reports.status_absent'),
        'leave' => lex('teaching_reports.status_leave'),
        'late' => lex('teaching_reports.status_late'),
    ];
    return $map[$state] ?? lex('common.unknown');
}

function resolve_attendance_chip_class(string $state): string
{
    $map = [
        'present' => 'status-present',
        'absent' => 'status-absent',
        'leave' => 'status-leave',
        'late' => 'status-late',
    ];
    return $map[$state] ?? '';
}

/** Legacy aliases */
function getStatusText(string $s): string { return resolve_attendance_label($s); }
function getStatusClass(string $s): string { return resolve_attendance_chip_class($s); }
