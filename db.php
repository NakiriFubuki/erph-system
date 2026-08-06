<?php
// db.php - Database connection
try {
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception('Configuration file config.php not found');
    }
    
    $config = require __DIR__ . '/config.php';
    
    if (!isset($config['db']) || !is_array($config['db'])) {
        throw new Exception('Invalid configuration: missing database settings');
    }
    
    $db = $config['db'];
    $required_keys = ['host', 'dbname', 'user', 'pass', 'charset'];
    
    foreach ($required_keys as $key) {
        if (!isset($db[$key])) {
            throw new Exception("Missing required database configuration key: {$key}");
        }
    }

    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
    
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    $pdo->query('SELECT 1');
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        die("Database '{$db['dbname']}' does not exist. Please create it and import the schema: 1) Run setup_erph1_database.sql; 2) Import erph.sql into erph1. See README.md.");
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        die('Database access denied. Please check the username and password.');
    } else {
        die('Database connection failed: ' . $e->getMessage());
    }
} catch (Exception $e) {
    die('Configuration error: ' . $e->getMessage());
}
