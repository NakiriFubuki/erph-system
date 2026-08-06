<?php
/**
 * Standard ERPH1 stylesheets — include after <title>
 * Usage: <?php include __DIR__ . '/inc/head_assets.php'; ?>
 */
$cssBase = 'assets/css';
$v = function ($file) {
    $path = __DIR__ . '/../' . $file;
    return file_exists($path) ? filemtime($path) : time();
};
?>
<link rel="stylesheet" href="<?= $cssBase ?>/admin.css?v=<?= $v($cssBase . '/admin.css') ?>">
<link rel="stylesheet" href="<?= $cssBase ?>/glyphs.css?v=<?= $v($cssBase . '/glyphs.css') ?>">
<link rel="stylesheet" href="<?= $cssBase ?>/mobile-optimization.css?v=<?= $v($cssBase . '/mobile-optimization.css') ?>">
<?php include __DIR__ . '/shot_assets.php'; ?>
<script src="assets/js/surface-lock.js"></script>
<script src="assets/js/glyph-runtime.js"></script>
