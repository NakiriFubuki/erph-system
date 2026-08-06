<?php
/**
 * Route guards — redirect unauthenticated or unauthorized visitors.
 */

function gate_signed_in(string $fallback = 'login_roles.php'): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: ' . $fallback);
        exit;
    }
}

function gate_administrative(string $fallback = 'login_roles.php'): void
{
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
        header('Location: ' . $fallback);
        exit;
    }
}

function gate_instructor(string $fallback = 'login_roles.php'): void
{
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'teacher') {
        header('Location: ' . $fallback);
        exit;
    }
}

function gate_admin_api(): void
{
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
        emit_json(['success' => false, 'message' => lex('errors.permission_denied')], 403);
    }
}

function gate_signed_api(): void
{
    if (!isset($_SESSION['user'])) {
        emit_json(['success' => false, 'message' => lex('errors.not_logged_in')], 401);
    }
}
