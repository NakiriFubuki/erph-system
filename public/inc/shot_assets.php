<?php
if (empty($_SESSION['screenshot_mode'])) {
    return;
}
$shotCss = 'assets/css/shot-mode.css';
$shotPath = __DIR__ . '/../' . $shotCss;
$shotVer = is_file($shotPath) ? filemtime($shotPath) : time();
?>
<link rel="stylesheet" href="<?= $shotCss ?>?v=<?= $shotVer ?>">
<style>
  /* Extra hardening for screenshot clarity */
  * { -webkit-font-smoothing: antialiased; }
  body { background: #dbeafe !important; }
</style>
