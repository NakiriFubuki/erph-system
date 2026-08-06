<?php
/**
 * Strip inline <style> blocks and wire shared head assets on dashboard pages.
 */
$root = dirname(__DIR__);

$targets = [
    $root . '/admin_dashboard.php' => $root . '/inc/head_assets.php',
    $root . '/teacher_dashboard.php' => $root . '/inc/head_teacher_assets.php',
];

foreach ($targets as $file => $headInclude) {
    if (!is_file($file)) {
        fwrite(STDERR, "missing: {$file}\n");
        continue;
    }

    $src = file_get_contents($file);
    $orig = $src;

    $src = preg_replace('#\s*<style>.*?</style>#s', '', $src);
    if ($src === null) {
        fwrite(STDERR, "style strip failed: {$file}\n");
        continue;
    }

    $includeLine = "<?php include __DIR__ . '" . str_replace($root, '', $headInclude) . "'; ?>";
    $src = preg_replace(
        '#\s*<link rel="stylesheet" href="assets/css/admin\.css[^"]*">\s*<link rel="stylesheet" href="assets/css/mobile-optimization\.css[^"]*">\s*<script src="assets/js/theme-sync\.js"></script>#s',
        "\n  " . $includeLine,
        $src,
        1,
        $count
    );

    if ($count === 0) {
        $src = preg_replace(
            '#\s*<link rel="stylesheet" href="assets/css/admin\.css[^"]*">\s*<link rel="stylesheet" href="assets/css/mobile-optimization\.css[^"]*">#s',
            "\n  " . $includeLine,
            $src,
            1
        );
    }

    $src = str_replace(
        'data-theme="<?= $_SESSION[\'theme\'] ?? \'light\' ?>"',
        'data-theme="<?= appearance_token() ?>"',
        $src
    );

    if ($src !== $orig) {
        file_put_contents($file, $src);
        echo basename($file) . ' patched' . PHP_EOL;
    }
}

echo "done\n";
