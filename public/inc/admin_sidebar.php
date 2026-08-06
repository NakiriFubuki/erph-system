<?php
/**
 * Admin navigation rail — set $navSection before include.
 */
$navSection = $navSection ?? ($current_page ?? '');

if (!function_exists('lex')) {
    require_once __DIR__ . '/bootstrap.php';
}

$navItems = [
    ['key' => 'dashboard', 'href' => 'admin_dashboard.php', 'label' => 'navigation.dashboard'],
    ['key' => 'users', 'href' => 'user_management.php', 'label' => 'navigation.user_management'],
    ['key' => 'courses', 'href' => 'course_management.php', 'label' => 'navigation.course_management'],
    ['key' => 'admin_reports', 'href' => 'admin_teaching_reports.php', 'label' => 'navigation.teaching_reports'],
    ['key' => 'textbooks_homework', 'href' => 'textbooks_homework.php', 'label' => 'navigation.textbooks_homework'],
    ['key' => 'classes', 'href' => 'classes.php', 'label' => 'navigation.classes'],
];

$systemItems = [
    ['key' => 'user_manual', 'href' => 'user_manual.php', 'label' => 'navigation.user_manual'],
    ['key' => 'background_manager', 'href' => 'login_background_manager.php', 'label' => 'navigation.login_background_manager'],
];
?>
<aside class="admin-sidebar">
  <div class="brand"><?= nav_glyph('logo') ?> <?= lex('navigation.admin_menu') ?></div>
  <div class="menu-title"><?= lex('navigation.quick_nav') ?></div>
  <ul class="menu">
    <?php foreach ($navItems as $item): ?>
    <li>
      <a class="<?= $navSection === $item['key'] ? 'active' : '' ?>" href="<?= $item['href'] ?>">
        <?= nav_glyph($item['key']) ?>
        <span><?= lex($item['label']) ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="menu-title" style="margin-top:12px;"><?= lex('common.system') ?></div>
  <ul class="menu">
    <?php foreach ($systemItems as $item): ?>
    <li>
      <a class="<?= $navSection === $item['key'] ? 'active' : '' ?>" href="<?= $item['href'] ?>">
        <?= nav_glyph($item['key']) ?>
        <span><?= lex($item['label']) ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</aside>
