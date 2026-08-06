<?php
$cssBase = 'assets/css';
$v = function ($file) use ($cssBase) {
    $path = dirname(__DIR__) . '/' . $file;
    return file_exists($path) ? filemtime($path) : time();
};
?>
<link rel="stylesheet" href="<?= $cssBase ?>/admin.css?v=<?= $v($cssBase . '/admin.css') ?>">
<link rel="stylesheet" href="<?= $cssBase ?>/teacher-common.css?v=<?= $v($cssBase . '/teacher-common.css') ?>">
<link rel="stylesheet" href="<?= $cssBase ?>/glyphs.css?v=<?= $v($cssBase . '/glyphs.css') ?>">
<link rel="stylesheet" href="<?= $cssBase ?>/mobile-optimization.css?v=<?= $v($cssBase . '/mobile-optimization.css') ?>">
<script src="assets/js/surface-lock.js"></script>
<script src="assets/js/glyph-runtime.js"></script>
