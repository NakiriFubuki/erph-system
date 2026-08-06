<?php
/**
 * Application kernel — session, lexicon, shared runtime helpers.
 * Pages should require this file (directly or via legacy shims).
 */

if (!defined('ERPH_KERNEL_LOADED')) {
    define('ERPH_KERNEL_LOADED', true);
}

function bootstrap_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    $_SESSION['theme'] = 'light';

    if (!isset($_SESSION['user'])) {
        return;
    }

    $holder = &$_SESSION['user'];
    if (empty($holder['name'])) {
        $holder['name'] = 'Unknown User';
    }
    if (!array_key_exists('avatar', $holder)) {
        $holder['avatar'] = null;
    }
    if (empty($holder['role'])) {
        $holder['role'] = 'student';
    }
}

function bootstrap_lexicon(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        bootstrap_session();
    }

    $_SESSION['lang'] = 'en';
    $GLOBALS['ERPH_ACTIVE_LOCALE'] = 'en';
    $GLOBALS['ERPH_LEXICON'] = ingest_lexicon_file('en');
}

function ingest_lexicon_file(string $locale): array
{
    $path = __DIR__ . "/translations/{$locale}.php";
    if (is_file($path)) {
        return include $path;
    }
    return include __DIR__ . '/translations/en.php';
}

function lex(string $path, $tokens = null, $fallback = null)
{
    $tree = $GLOBALS['ERPH_LEXICON'] ?? [];
    $segments = explode('.', $path);
    $cursor = $tree;

    foreach ($segments as $segment) {
        if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
            return $fallback ?? $path;
        }
        $cursor = $cursor[$segment];
    }

    if (is_array($tokens) && is_string($cursor)) {
        foreach ($tokens as $token => $replacement) {
            $cursor = str_replace('{' . $token . '}', (string) $replacement, $cursor);
        }
    }

    return $cursor;
}

/** @deprecated alias — UI copy unchanged */
function t(string $path, $tokens = null, $fallback = null)
{
    return lex($path, $tokens, $fallback);
}

function renderLanguageSwitch(bool $with_caption = true): string
{
    return '';
}

function wire_datastore(): void
{
    require_once dirname(__DIR__) . '/../db.php';
}

function active_account(): ?array
{
    return $_SESSION['user'] ?? null;
}

function signed_account(): array
{
    return $_SESSION['user'];
}

function appearance_token(): string
{
    return $_SESSION['theme'] ?? 'light';
}

function locale_token(): string
{
    return $GLOBALS['ERPH_ACTIVE_LOCALE'] ?? 'en';
}
