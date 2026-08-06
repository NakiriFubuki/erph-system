<?php
/**
 * HTTP payload helpers for JSON endpoints.
 */

function emit_json(array $body, int $status = 200): void
{
    if (ob_get_level() > 0) {
        ob_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function begin_json_buffer(): void
{
    if (ob_get_level() === 0) {
        ob_start();
    }
}
