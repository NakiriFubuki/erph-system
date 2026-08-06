<?php
/**
 * Inline SVG glyphs — stroke icons, distinct from emoji-based UIs.
 */
function glyph(string $key, string $class = ''): string
{
    static $paths = null;
    if ($paths === null) {
        $paths = [
            'profile' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 19c0-3.3 3.1-6 7-6s7 2.7 7 6"/>',
            'manual' => '<path d="M5 6.5A2.5 2.5 0 0 1 7.5 4H18v16H7.5A2.5 2.5 0 0 1 5 17.5V6.5z"/><path d="M7.5 4v16"/>',
            'settings' => '<path d="M6 8h12M6 12h8M6 16h10"/><circle cx="17" cy="12" r="1.5"/><circle cx="15" cy="8" r="1.5"/><circle cx="13" cy="16" r="1.5"/>',
            'logout' => '<path d="M10 7V5.5A1.5 1.5 0 0 1 11.5 4h7A1.5 1.5 0 0 1 20 5.5v13A1.5 1.5 0 0 1 18.5 20h-7A1.5 1.5 0 0 1 10 18.5V17"/><path d="M3 12h10M14 9l3 3-3 3"/>',
            'analytics' => '<rect x="4" y="11" width="4" height="9" rx="1"/><rect x="10" y="7" width="4" height="13" rx="1"/><rect x="16" y="4" width="4" height="16" rx="1"/>',
            'clipboard' => '<path d="M9 5h6l1 2h3v14H5V7h3l1-2z"/><path d="M9 5a2 2 0 0 1 4 0"/><path d="M8 11h8M8 15h6"/>',
            'layers' => '<path d="M12 4 4 8l8 4 8-4-8-4z"/><path d="m4 12 8 4 8-4"/><path d="m4 16 8 4 8-4"/>',
            'users' => '<circle cx="9" cy="9" r="2.5"/><circle cx="16" cy="10" r="2"/><path d="M4.5 18c.8-2.2 2.6-3.5 4.5-3.5S12.7 15.8 13.5 18"/><path d="M14.5 17.5c.5-1.4 1.6-2.5 3-2.5"/>',
            'trash' => '<path d="M5 7h14M9 7V5.5h6V7"/><path d="M7 7l.8 12h8.4L17 7"/><path d="M10 11v5M14 11v5"/>',
            'check-square' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="m8 12 3 3 5-6"/>',
            'square' => '<rect x="4" y="4" width="16" height="16" rx="2"/>',
            'pen' => '<path d="M14 5l5 5-9 9H5v-5l9-9z"/><path d="M13 6l5 5"/>',
            'bulb' => '<path d="M9 18h6"/><path d="M10 21h4"/><path d="M8 14a4 4 0 1 1 8 0c0 2-2 2.5-2 4H10c0-1.5-2-2-2-4z"/>',
            'search' => '<circle cx="11" cy="11" r="5"/><path d="m16 16 4 4"/>',
            'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.5"/>',
            'eye-off' => '<path d="M2 12s3.5-6 10-6c2.2 0 4.1.8 5.6 2"/><path d="M22 12s-3.5-6-10-6c-1.4 0-2.7.3-3.9.8"/><path d="m3 3 18 18"/>',
            'scissors' => '<circle cx="7" cy="7" r="2"/><circle cx="7" cy="17" r="2"/><path d="m9 8.5 11 11M9 15.5 20 4"/>',
            'list' => '<path d="M8 6h12M8 12h12M8 18h12"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
            'calendar' => '<rect x="4" y="6" width="16" height="14" rx="2"/><path d="M8 4v4M16 4v4M4 10h16"/>',
            'check' => '<path d="m6 12 4 4 8-8"/>',
            'x-mark' => '<path d="m8 8 8 8M16 8l-8 8"/>',
            'alert' => '<path d="M12 4 3 19h18L12 4z"/><path d="M12 10v4M12 17h.01"/>',
            'arrow-left' => '<path d="M11 6 5 12l6 6"/><path d="M19 12H6"/>',
            'school' => '<path d="M4 10 12 6l8 4v8H4v-8z"/><path d="M9 18v-4h6v4"/><path d="M12 6v3"/>',
            'palette' => '<path d="M12 3a9 9 0 1 0 8 12c0-2-2-2-3-1.5S15 16 12 16a3 3 0 0 1-3-3c0-5 3-10 3-10z"/><circle cx="8" cy="9" r="1"/><circle cx="12" cy="7" r="1"/><circle cx="16" cy="9" r="1"/>',
            'trend' => '<path d="M4 17 9 11l4 4 7-9"/><path d="M15 6h5v5"/>',
            'globe' => '<circle cx="12" cy="12" r="8"/><path d="M4 12h16"/><path d="M12 4a14 14 0 0 1 0 16"/><path d="M12 4a14 14 0 0 0 0 16"/>',
            'lock' => '<rect x="6" y="11" width="12" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
            'device' => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
            'mail' => '<rect x="4" y="6" width="16" height="12" rx="2"/><path d="m4 8 8 6 8-6"/>',
            'help' => '<circle cx="12" cy="12" r="8"/><path d="M9.5 9.5a2.5 2.5 0 1 1 4.2 1.8c-.8.7-1.7 1.2-1.7 2.7"/><circle cx="12" cy="17" r=".8"/>',
            'close' => '<circle cx="12" cy="12" r="8"/><path d="m9 9 6 6M15 9l-6 6"/>',
            'notes' => '<path d="M7 4h10l3 3v13H7V4z"/><path d="M17 4v3h3"/><path d="M10 11h6M10 15h4"/>',
            'books' => '<path d="M5 6h7v14H6a1 1 0 0 1-1-1V6z"/><path d="M12 6h7v13a1 1 0 0 1-1 1h-6V6z"/><path d="M8 10h2M15 10h2"/>',
            'dashboard' => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
            'reports' => '<path d="M7 4h10v16H7z"/><path d="M10 14h6M10 11h6M10 8h4"/><path d="M16 4v4h4"/>',
            'classes' => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M8 6V4h8v2"/><path d="M3 11h18"/>',
            'background' => '<rect x="4" y="5" width="16" height="14" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m6 17 5-5 3 3 4-4 3 3"/>',
            'logo' => '<path d="M12 3 4 7v10l8 4 8-4V7l-8-4z"/><path d="M12 7v14"/><path d="m8 9 4 2 4-2"/>',
            'manage' => '<path d="M12 8v8M8 12h8"/><circle cx="12" cy="12" r="8"/>',
            'empty' => '<rect x="5" y="5" width="14" height="14" rx="3" stroke-dasharray="3 3"/><path d="M9 12h6"/>',
            'folder' => '<path d="M4 8V6.5A1.5 1.5 0 0 1 5.5 5H9l2 2h7.5A1.5 1.5 0 0 1 20 8.5V18A1.5 1.5 0 0 1 18.5 19.5h-13A1.5 1.5 0 0 1 4 18V8z"/>',
            'refresh' => '<path d="M20 12a8 8 0 1 1-2.3-5.7"/><path d="M20 4v6h-6"/>',
        ];
    }

    $body = $paths[$key] ?? $paths['empty'];
    $classes = trim('erph-glyph erph-glyph-' . preg_replace('/[^a-z0-9_-]/', '', $key) . ' ' . $class);

    return sprintf(
        '<svg class="%s" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
        htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'),
        $body
    );
}

function nav_glyph(string $section): string
{
    $map = [
        'logo' => 'logo',
        'dashboard' => 'dashboard',
        'users' => 'users',
        'courses' => 'layers',
        'admin_reports' => 'reports',
        'textbooks_homework' => 'books',
        'classes' => 'classes',
        'background_manager' => 'background',
        'user_manual' => 'manual',
    ];

    return glyph($map[$section] ?? 'empty', 'nav-glyph');
}
