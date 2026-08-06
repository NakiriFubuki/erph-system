<?php
/**
 * Replace emoji UI icons with glyph() SVG — production pages only.
 */
$root = dirname(__DIR__);
$skip = '/^(test_|debug_|demo_|example_|refactor_)/';

$map = [
    '<span class="dropdown-icon">👤</span>' => '<span class="dropdown-icon"><?= glyph(\'profile\') ?></span>',
    '<span class="dropdown-icon">📖</span>' => '<span class="dropdown-icon"><?= glyph(\'manual\') ?></span>',
    '<span class="dropdown-icon">⚙</span>' => '<span class="dropdown-icon"><?= glyph(\'settings\') ?></span>',
    '<span class="dropdown-icon">⚙️</span>' => '<span class="dropdown-icon"><?= glyph(\'settings\') ?></span>',
    '<span class="dropdown-icon">🚪</span>' => '<span class="dropdown-icon"><?= glyph(\'logout\') ?></span>',
    '<div class="function-icon">📊</div>' => '<div class="function-icon"><?= glyph(\'analytics\') ?></div>',
    '<div class="function-icon">📝</div>' => '<div class="function-icon"><?= glyph(\'clipboard\') ?></div>',
    '<div class="function-icon">📚</div>' => '<div class="function-icon"><?= glyph(\'layers\') ?></div>',
    '<div class="function-icon">👤</div>' => '<div class="function-icon"><?= glyph(\'profile\') ?></div>',
    '<div class="feature-icon">📊</div>' => '<div class="feature-icon"><?= glyph(\'analytics\') ?></div>',
    '<div class="feature-icon">👥</div>' => '<div class="feature-icon"><?= glyph(\'users\') ?></div>',
    '<div class="feature-icon">📚</div>' => '<div class="feature-icon"><?= glyph(\'layers\') ?></div>',
    '<div class="feature-icon">📝</div>' => '<div class="feature-icon"><?= glyph(\'clipboard\') ?></div>',
    '<div class="feature-icon">📖</div>' => '<div class="feature-icon"><?= glyph(\'manual\') ?></div>',
    '<div class="feature-icon">🏫</div>' => '<div class="feature-icon"><?= glyph(\'school\') ?></div>',
    '<div class="feature-icon">🎨</div>' => '<div class="feature-icon"><?= glyph(\'palette\') ?></div>',
    '<div class="feature-icon">📈</div>' => '<div class="feature-icon"><?= glyph(\'trend\') ?></div>',
    '<div class="feature-icon">👤</div>' => '<div class="feature-icon"><?= glyph(\'profile\') ?></div>',
    '<div class="feature-icon">⚙️</div>' => '<div class="feature-icon"><?= glyph(\'settings\') ?></div>',
    '<div class="feature-icon">🌙</div>' => '<div class="feature-icon"><?= glyph(\'palette\') ?></div>',
    '<div class="feature-icon">🌍</div>' => '<div class="feature-icon"><?= glyph(\'globe\') ?></div>',
    '<div class="feature-icon">🔒</div>' => '<div class="feature-icon"><?= glyph(\'lock\') ?></div>',
    '<div class="feature-icon">📱</div>' => '<div class="feature-icon"><?= glyph(\'device\') ?></div>',
    '<div class="feature-icon">❓</div>' => '<div class="feature-icon"><?= glyph(\'help\') ?></div>',
    '<div class="stat-icon">📚</div>' => '<div class="stat-icon"><?= glyph(\'layers\') ?></div>',
    '<div class="stat-icon">📝</div>' => '<div class="stat-icon"><?= glyph(\'clipboard\') ?></div>',
    '<div class="stat-icon">📋</div>' => '<div class="stat-icon"><?= glyph(\'list\') ?></div>',
    '<div class="stat-icon">📅</div>' => '<div class="stat-icon"><?= glyph(\'calendar\') ?></div>',
    '<div class="stat-icon">👥</div>' => '<div class="stat-icon"><?= glyph(\'users\') ?></div>',
    '<div class="stat-icon">📊</div>' => '<div class="stat-icon"><?= glyph(\'analytics\') ?></div>',
    '<div class="stat-icon">📖</div>' => '<div class="stat-icon"><?= glyph(\'manual\') ?></div>',
    '<div class="no-reports-icon">📊</div>' => '<div class="no-reports-icon"><?= glyph(\'analytics\') ?></div>',
    '<div class="no-courses-icon">📚</div>' => '<div class="no-courses-icon"><?= glyph(\'layers\') ?></div>',
    '<div class="no-plans-icon">📝</div>' => '<div class="no-plans-icon"><?= glyph(\'clipboard\') ?></div>',
    '<div class="no-activities-icon">📊</div>' => '<div class="no-activities-icon"><?= glyph(\'analytics\') ?></div>',
    '<span class="manage-icon">⚙️</span>' => '<span class="manage-icon"><?= glyph(\'manage\') ?></span>',
    '<span class="notes-icon">📝</span>' => '<span class="notes-icon"><?= glyph(\'notes\') ?></span>',
    '<span class="btn-icon">☑️</span>' => '<span class="btn-icon"><?= glyph(\'check-square\') ?></span>',
    '<span class="btn-icon">☐</span>' => '<span class="btn-icon"><?= glyph(\'square\') ?></span>',
    '<span class="btn-icon">🗑️</span>' => '<span class="btn-icon"><?= glyph(\'trash\') ?></span>',
    '<span class="label-icon">📝</span>' => '<span class="label-icon"><?= glyph(\'clipboard\') ?></span>',
    '<span class="label-icon">👤</span>' => '<span class="label-icon"><?= glyph(\'profile\') ?></span>',
    '<span class="label-icon">📅</span>' => '<span class="label-icon"><?= glyph(\'calendar\') ?></span>',
    '<span class="label-icon">✅</span>' => '<span class="label-icon"><?= glyph(\'check\') ?></span>',
    '<span class="tips-icon">💡</span>' => '<span class="tips-icon"><?= glyph(\'bulb\') ?></span>',
    '<span class="back-icon">←</span>' => '<span class="back-icon"><?= glyph(\'arrow-left\') ?></span>',
    '<h3>👤 <?= t(\'profile.personal_info\') ?></h3>' => '<h3><?= glyph(\'profile\') ?> <?= t(\'profile.personal_info\') ?></h3>',
    '<h4 class="section-title">🔍 <?= t(\'profile.current_info\') ?></h4>' => '<h4 class="section-title"><?= glyph(\'search\') ?> <?= t(\'profile.current_info\') ?></h4>',
    '<h3 class="section-title">📊 <?= t(\'profile.recent_activities\') ?></h3>' => '<h3 class="section-title"><?= glyph(\'analytics\') ?> <?= t(\'profile.recent_activities\') ?></h3>',
    '<button class="avatar-edit-btn" onclick="openAvatarModal()" title="<?= t(\'teacher_profile.edit_avatar\') ?>">✏️</button>' => '<button class="avatar-edit-btn" onclick="openAvatarModal()" title="<?= t(\'teacher_profile.edit_avatar\') ?>"><?= glyph(\'pen\') ?></button>',
    '<h3>📚 Course edit notes</h3>' => '<h3><?= glyph(\'layers\') ?> Course edit notes</h3>',
    '<h3>📚 Course sharing notes</h3>' => '<h3><?= glyph(\'layers\') ?> Course sharing notes</h3>',
    '<p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 14px;">📋 <?= t(\'lesson_plans.show_recent_plans\') ?></p>' => '<p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 14px;"><?= glyph(\'list\') ?> <?= t(\'lesson_plans.show_recent_plans\') ?></p>',
    "'logo_text' => '📚'," => "'logo_text' => '',",
];

$themePatterns = [
    '#<button class="theme-toggle-btn"[^>]*>\s*🌙\s*</button>\s*#s' => '',
    "#themeBtn\.innerHTML = newTheme === 'light' \? '🌙' : '☀️';\s*" => '',
    "#themeBtn\.innerHTML = savedTheme === 'light' \? '🌙' : '☀️';\s*" => '',
    '#\s*<button class="theme-toggle-btn" onclick="toggleTheme\(\)"[^>]*>🌙</button>\s*#s' => '',
];

foreach (glob($root . '/*.php') as $path) {
    if (preg_match($skip, basename($path))) {
        continue;
    }

    $src = file_get_contents($path);
    if ($src === false || $src === '') {
        continue;
    }

    $orig = $src;
    foreach ($map as $from => $to) {
        $src = str_replace($from, $to, $src);
    }

    foreach ($themePatterns as $pattern => $replacement) {
        $next = preg_replace($pattern, $replacement, $src);
        if ($next !== null) {
            $src = $next;
        }
    }

    if ($src !== $orig) {
        file_put_contents($path, $src);
        echo basename($path) . PHP_EOL;
    }
}

echo "done\n";
