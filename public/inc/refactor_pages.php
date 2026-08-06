<?php
/**
 * One-shot production PHP refactor — run: php inc/refactor_pages.php
 */
$root = dirname(__DIR__);
$skip = '/^(test_|debug_|demo_|example_|refactor_pages)/';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach ($rii as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (preg_match($skip, basename($rel))) {
        continue;
    }
    if (strpos($rel, 'inc/refactor') !== false) {
        continue;
    }
    $files[] = $file->getPathname();
}

$adminGuard = "/if\s*\(\s*!isset\(\$_SESSION\['user'\]\)\s*\|\|\s*\$_SESSION\['user'\]\['role'\]\s*!==\s*'admin'\s*\)\s*\{\s*header\('Location:\s*login_roles\.php'\);\s*exit;\s*\}/";
$teacherGuard = "/if\s*\(\s*!isset\(\$_SESSION\['user'\]\)\s*\|\|\s*\$_SESSION\['user'\]\['role'\]\s*!==\s*'teacher'\s*\)\s*\{\s*header\('Location:\s*login_roles\.php'\);\s*exit;\s*\}/";
$signedGuard = "/if\s*\(\s*!isset\(\$_SESSION\['user'\]\)\s*\)\s*\{\s*header\('Location:\s*login_roles\.php'\);\s*exit;\s*\}/";

foreach ($files as $path) {
    $src = file_get_contents($path);
    $orig = $src;

    $src = preg_replace(
        "/require_once __DIR__ \. '\/inc\/session_config\.php';\s*\r?\nrequire_once __DIR__ \. '\/inc\/language_config\.php';/",
        "require_once __DIR__ . '/inc/bootstrap.php';",
        $src
    );

    $src = preg_replace($adminGuard, "gate_administrative();", $src);
    $src = preg_replace($teacherGuard, "gate_instructor();", $src);

  // signed guard only when not already gated
    if (strpos($src, 'gate_administrative') === false && strpos($src, 'gate_instructor') === false) {
        $src = preg_replace($signedGuard, "gate_signed_in();", $src);
    }

    $src = preg_replace('/\$current_page\s*=/', '$navSection =', $src);
    $src = preg_replace('/\$current_page\s*===/', '$navSection ===', $src);
    $src = preg_replace('/\$current_page==/', '$navSection==', $src);

    $src = preg_replace(
        '/\$user\s*=\s*\$_SESSION\[\'user\'\];/',
        '$activeAccount = signed_account();',
        $src
    );

    if ($src !== $orig) {
        file_put_contents($path, $src);
        echo "updated: " . basename($path) . PHP_EOL;
    }
}

echo "done\n";
