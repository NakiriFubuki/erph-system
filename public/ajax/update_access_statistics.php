<?php
// update_access_statistics.php - Update access statistics AJAX API
require_once __DIR__ . '/../inc/session_config.php';

// Check whether logged in
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get database connection
    $config = require __DIR__ . '/../../config.php';
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    $action = $_POST['action'] ?? 'record_visit';
    $response = [];
    
    switch ($action) {
        case 'record_visit':
            // Record one visit
            $current_hour = (int)date('H');
            $today = date('Y-m-d');
            
            // Check whether a record already exists for this hour today
            $stmt = $pdo->prepare("
                SELECT id, total_visits, unique_users 
                FROM access_statistics 
                WHERE date = ? AND hour = ?
            ");
            $stmt->execute([$today, $current_hour]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing record
                $new_visits = $existing['total_visits'] + 1;
                $new_unique_users = $existing['unique_users'];
                
                // Check whether this is a new user(first visit today)
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count 
                    FROM access_statistics 
                    WHERE date = ? AND hour = ? AND unique_users > 0
                ");
                $stmt->execute([$today, $current_hour]);
                $has_visits = $stmt->fetch()['count'] > 0;
                
                if (!$has_visits) {
                    $new_unique_users = 1;
                }
                
                $stmt = $pdo->prepare("
                    UPDATE access_statistics 
                    SET total_visits = ?, unique_users = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([$new_visits, $new_unique_users, $existing['id']]);
            } else {
                // Create new record
                $stmt = $pdo->prepare("
                    INSERT INTO access_statistics (date, hour, total_visits, unique_users, new_users)
                    VALUES (?, ?, 1, 1, 0)
                ");
                $stmt->execute([$today, $current_hour]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Access record updated';
            break;
            
        case 'get_current_hour_data':
            // Get data for the current hour
            $current_hour = (int)date('H');
            $today = date('Y-m-d');
            
            $stmt = $pdo->prepare("
                SELECT total_visits, unique_users 
                FROM access_statistics 
                WHERE date = ? AND hour = ?
            ");
            $stmt->execute([$today, $current_hour]);
            $data = $stmt->fetch();
            
            if ($data) {
                $response['success'] = true;
                $response['data'] = $data;
            } else {
                $response['success'] = true;
                $response['data'] = ['total_visits' => 0, 'unique_users' => 0];
            }
            break;
            
        case 'simulate_activity':
            // Mock some activity data (for testing)
            $today = date('Y-m-d');
            
            // Generate today's 24 hours of mock data
            for ($hour = 0; $hour < 24; $hour++) {
                // Work hours (9-17) have more activity
                if ($hour >= 9 && $hour <= 17) {
                    $visits = rand(15, 35);
                    $unique_users = rand(8, 20);
                } else {
                    $visits = rand(0, 10);
                    $unique_users = rand(0, 5);
                }
                
                $new_users = rand(0, 2);
                
                // Check whether a record already exists for this hour
                $stmt = $pdo->prepare("
                    SELECT id FROM access_statistics 
                    WHERE date = ? AND hour = ?
                ");
                $stmt->execute([$today, $hour]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update existing record
                    $stmt = $pdo->prepare("
                        UPDATE access_statistics 
                        SET total_visits = ?, unique_users = ?, new_users = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$visits, $unique_users, $new_users, $existing['id']]);
                } else {
                    // Create new record
                    $stmt = $pdo->prepare("
                        INSERT INTO access_statistics (date, hour, total_visits, unique_users, new_users)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$today, $hour, $visits, $unique_users, $new_users]);
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Mock activity data updated; generated 24 hours of test data';
            break;
            
        case 'generate_today_data':
            // Generate today's activity monitor data - based on real teaching report data
            $today = date('Y-m-d');
            
            // First clear today's access statistics, then regenerate
            $stmt = $pdo->prepare("DELETE FROM access_statistics WHERE date = ?");
            $stmt->execute([$today]);
            
            // Try to get real activity data from teaching reports (full activity duration)
            $stmt = $pdo->prepare("
                SELECT 
                    HOUR(a.check_in) as check_in_hour,
                    HOUR(a.check_out) as check_out_hour,
                    COUNT(*) as count
                FROM attendance a
                WHERE DATE(a.date) = ? 
                AND a.check_in IS NOT NULL 
                AND a.check_out IS NOT NULL
                ORDER BY a.check_in
            ");
            $stmt->execute([$today]);
            $attendance_data = $stmt->fetchAll();
            
            if (!empty($attendance_data)) {
                // Use real teaching data - Consider the full duration from check-in to check-out
                $hourly_activity = array_fill(0, 24, 0);
                
                foreach ($attendance_data as $row) {
                    $check_in_hour = (int)$row['check_in_hour'];
                    $check_out_hour = (int)$row['check_out_hour'];
                    $count = (int)$row['count'];
                    
                    // Mark all hours between check-in and check-out as active
                    for ($hour = $check_in_hour; $hour <= $check_out_hour; $hour++) {
                        if ($hour >= 0 && $hour < 24) {
                            $hourly_activity[$hour] += $count;
                        }
                    }
                }
                
                // Insert all 24 hours of data
                for ($hour = 0; $hour < 24; $hour++) {
                    $activity_count = $hourly_activity[$hour];
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO access_statistics (date, hour, total_visits, unique_users, new_users)
                        VALUES (?, ?, ?, ?, 0)
                    ");
                    $stmt->execute([$today, $hour, $activity_count, min($activity_count, 5), 0]);
                }
                
                $response['success'] = true;
                $response['message'] = "Today's data generated successfully, based on real teaching report data";
            } else {
                // If there is no teaching data, generate mock data
                for ($hour = 0; $hour < 24; $hour++) {
                    // Simulate peak during work hours (9-17)
                    if ($hour >= 9 && $hour <= 17) {
                        $total_visits = rand(8, 25); // Random activity during work hours
                    } else {
                        $total_visits = rand(0, 8);  // Fewer activities outside work hours
                    }
                    
                    $unique_users = min($total_visits, 5); // Assume at most 5 unique users
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO access_statistics (date, hour, total_visits, unique_users, new_users)
                        VALUES (?, ?, ?, ?, 0)
                    ");
                    $stmt->execute([$today, $hour, $total_visits, $unique_users]);
                }
                
                $response['success'] = true;
                $response['message'] = "Today's data generated successfully; generated 24 hours of mock data (no real teaching data)";
            }
            break;
            
        case 'sync_attendance':
            // Sync teaching report data to access statistics
            $today = date('Y-m-d');
            
            // First clear today's access statistics, then regenerate
            $stmt = $pdo->prepare("DELETE FROM access_statistics WHERE date = ?");
            $stmt->execute([$today]);
            
            // Get today's teaching report data (full activity duration)
            $stmt = $pdo->prepare("
                SELECT 
                    HOUR(a.check_in) as check_in_hour,
                    HOUR(a.check_out) as check_out_hour,
                    COUNT(*) as count
                FROM attendance a
                WHERE DATE(a.date) = ? 
                AND a.check_in IS NOT NULL 
                AND a.check_out IS NOT NULL
                ORDER BY a.check_in
            ");
            $stmt->execute([$today]);
            $attendance_data = $stmt->fetchAll();
            
            // Log debug information
            error_log("Sync teaching data: found " . count($attendance_data) . " record(s)");
            
            if (empty($attendance_data)) {
                $response['success'] = false;
                $response['error'] = 'No teaching report data found for today';
                break;
            }
            
            // Use real teaching data - Consider the full duration from check-in to check-out
            $hourly_activity = array_fill(0, 24, 0);
            
            foreach ($attendance_data as $row) {
                $check_in_hour = (int)$row['check_in_hour'];
                $check_out_hour = (int)$row['check_out_hour'];
                $count = (int)$row['count'];
                
                // Log debug information
                error_log("Sync record: Check-in {$check_in_hour}:00, Check-out {$check_out_hour}:00, count {$count}");
                
                // Mark all hours between check-in and check-out as active
                for ($hour = $check_in_hour; $hour <= $check_out_hour; $hour++) {
                    if ($hour >= 0 && $hour < 24) {
                        $hourly_activity[$hour] += $count;
                    }
                }
            }
            
            // Insert all 24 hours of data
            for ($hour = 0; $hour < 24; $hour++) {
                $activity_count = $hourly_activity[$hour];
                
                $stmt = $pdo->prepare("
                    INSERT INTO access_statistics (date, hour, total_visits, unique_users, new_users)
                    VALUES (?, ?, ?, ?, 0)
                ");
                $stmt->execute([$today, $hour, $activity_count, min($activity_count, 5), 0]);
            }
            
            $response['success'] = true;
            $response['message'] = 'Teaching data synced successfully; updated ' . count($attendance_data) . ' hour(s) of data';
            break;
            
        default:
            $response['success'] = false;
            $response['error'] = 'Unknown action';
            break;
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Failed to update access statistics: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Failed to update access statistics: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'System error',
        'message' => $e->getMessage()
    ]);
}
?>

