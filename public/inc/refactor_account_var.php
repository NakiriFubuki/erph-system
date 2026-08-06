<?php
$root = dirname(__DIR__);
$skip = '/^(test_|debug_|demo_|example_|refactor_)/';

foreach (glob($root . '/*.php') as $path) {
    if (preg_match($skip, basename($path))) {
        continue;
    }
    $src = file_get_contents($path);
    if (strpos($src, '$activeAccount = signed_account()') === false) {
        continue;
    }
    $orig = $src;
    $src = str_replace('$user[', '$activeAccount[', $src);
    $src = str_replace('htmlspecialchars($user)', 'htmlspecialchars($activeAccount)', $src);
    if ($src !== $orig) {
        file_put_contents($path, $src);
        echo basename($path) . PHP_EOL;
    }
}

echo "done\n";
