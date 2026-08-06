<?php
/**
 * Safe guard replacement — validates preg result before writing.
 */
$root = dirname(__DIR__);
$skip = '/^(test_|debug_|demo_|example_|refactor_|gateways|login_roles)/';

$rules = [
    '#(?://[^\r\n]*[\r\n]+)?if\s*\(\s*!isset\(\$_SESSION\[\'user\'\]\)\s*\|\|\s*\$_SESSION\[\'user\'\]\[\'role\'\]\s*!==\s*\'admin\'\s*\)\s*\{\s*header\(\'Location:\s*login_roles\.php\'\);\s*exit;\s*\}#s'
        => 'gate_administrative();',
    '#(?://[^\r\n]*[\r\n]+)?if\s*\(\s*!isset\(\$_SESSION\[\'user\'\]\)\s*\|\|\s*\$_SESSION\[\'user\'\]\[\'role\'\]\s*!==\s*\'teacher\'\s*\)\s*\{\s*header\(\'Location:\s*login_roles\.php\'\);\s*exit;\s*\}#s'
        => 'gate_instructor();',
    '#(?://[^\r\n]*[\r\n]+)?if\s*\(\s*!isset\(\$_SESSION\[\'user\'\]\)\s*\)\s*\{\s*header\(\'Location:\s*login_roles\.php\'\);\s*exit;\s*\}#s'
        => 'gate_signed_in();',
];

foreach (glob($root . '/*.php') as $path) {
    $base = basename($path);
    if (preg_match($skip, $base)) {
        continue;
    }

    $src = file_get_contents($path);
    if ($src === false || $src === '') {
        continue;
    }

    $orig = $src;
    foreach ($rules as $pattern => $replacement) {
        $next = preg_replace($pattern, $replacement, $src);
        if ($next === null) {
            fwrite(STDERR, "regex failed: {$base}\n");
            continue 2;
        }
        $src = $next;
    }

    if ($src !== $orig) {
        file_put_contents($path, $src);
        echo $base . PHP_EOL;
    }
}

echo "done\n";
