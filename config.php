<?php
// config.php - ERPH system settings (override via config.local.php on cPanel)
$config = [
    'db' => [
        'host' => 'localhost',
        'dbname' => 'erph1',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'upload_dir' => __DIR__ . '/public/uploads/',
    'app_name' => 'ERPH System',
    'version' => '1.0.0',
];

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $local = require $localConfig;
    if (is_array($local)) {
        if (isset($local['db']) && is_array($local['db'])) {
            $config['db'] = array_merge($config['db'], $local['db']);
        }
        foreach ($local as $key => $value) {
            if ($key !== 'db') {
                $config[$key] = $value;
            }
        }
    }
}

return $config;
