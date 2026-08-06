<?php
/**
 * Privacy-oriented string masking for profile displays.
 */

function obscure_fragment(string $value, int $visible = 2): string
{
    $length = mb_strlen($value);
    if ($length <= $visible) {
        return str_repeat('*', $length);
    }
    return mb_substr($value, 0, $visible) . str_repeat('*', $length - $visible);
}

function obscure_mailbox(string $mailbox): string
{
    $parts = explode('@', $mailbox, 2);
    if (count($parts) !== 2) {
        return obscure_fragment($mailbox);
    }
    return obscure_fragment($parts[0], 2) . '@' . obscure_fragment($parts[1], 2);
}

/** Legacy aliases */
function maskString(string $s, int $n = 2): string { return obscure_fragment($s, $n); }
function maskEmail(string $e): string { return obscure_mailbox($e); }
