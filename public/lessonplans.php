<?php
// lessonplans.php - Lesson plan management(on-demand fields:title, related course, class, textbook/homework, date, start/end time, notes)
require_once __DIR__ . '/inc/bootstrap.php';

gate_instructor();

require_once __DIR__ . '/../db.php';
$msg = '';
$error = '';
$navSection = 'plans';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    try {
        $pdo->beginTransaction();
        
        // Check whether the lesson plan belongs to the current user
        $check_stmt = $pdo->prepare("SELECT id, title FROM lesson_plans WHERE id = ? AND created_by = ?");
        $check_stmt->execute([$delete_id, $_SESSION['user']['id']]);
        $lesson_plan = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lesson_plan) {
            // Check for related teaching reports
            $check_attendance_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM attendance WHERE lesson_plan_id = ?");
            $check_attendance_stmt->execute([$delete_id]);
            $attendance_result = $check_attendance_stmt->fetch(PDO::FETCH_ASSOC);
            $attendance_count = intval($attendance_result['count']);
            
            // Delete related teaching reports(because lesson plans and teaching reports have a one-to-one relationship)
            if ($attendance_count > 0) {
                $delete_attendance_stmt = $pdo->prepare("DELETE FROM attendance WHERE lesson_plan_id = ?");
                $delete_attendance_stmt->execute([$delete_id]);
            }
            
            // Delete lesson plan
            $delete_stmt = $pdo->prepare("DELETE FROM lesson_plans WHERE id = ? AND created_by = ?");
            $delete_stmt->execute([$delete_id, $_SESSION['user']['id']]);
            
            // Check whether deletion succeeded
            if ($delete_stmt->rowCount() > 0) {
                $pdo->commit();
                $success_msg = t('lesson_plans.delete_success', ['title' => strval($lesson_plan['title'])]);
                if ($attendance_count > 0) {
                    $success_msg .= '  (and also deleted ' . $attendance_count . ' related teaching report(s))';
                }
                $msg = $success_msg;
            } else {
                $pdo->rollBack();
                $error = t('lesson_plans.delete_failed');
            }
        } else {
            $error = t('lesson_plans.not_found_or_no_permission');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = t('lesson_plans.delete_error', ['error' => $e->getMessage()]);
    }
}

// Handle edit mode
$edit_id = 0;
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    try {
        $edit_stmt = $pdo->prepare("SELECT * FROM lesson_plans WHERE id = ? AND created_by = ?");
        $edit_stmt->execute([$edit_id, $_SESSION['user']['id']]);
        $edit_data = $edit_stmt->fetch();
        if (!$edit_data) {
            $error = t('lesson_plans.edit_not_found');
            $edit_id = 0;
        }
    } catch (Exception $e) {
        $error = t('lesson_plans.get_lesson_plan_failed', ['error' => $e->getMessage()]);
        $edit_id = 0;
    }
}

// Load dropdown option data (textbook/homework, class)
try {
    // Check whether course_id field exists
    $columns = $pdo->query("DESCRIBE subjects")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('course_id', $columns)) {
        // Get all textbooks/homework (for JavaScript dynamic filtering)
        $all_subjects = $pdo->query("SELECT id, name, course_id FROM subjects WHERE is_active = 1 ORDER BY name")->fetchAll();
        $subjects = $all_subjects; // Show all by default
    } else {
        $subjects = $pdo->query("SELECT id, name FROM subjects WHERE is_active = 1 ORDER BY name")->fetchAll();
        $all_subjects = $subjects;
    }
} catch (Throwable $e) {
    $subjects = [];
    $all_subjects = [];
}

try {
    $classes = $pdo->query("SELECT id, name FROM classes WHERE is_active = 1 ORDER BY name")->fetchAll();
} catch (Throwable $e) {
    $classes = [];
}

try {
    // Check whether course_teachers table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'course_teachers'")->fetch();
    
    if ($table_check) {
        // Use new multi-teacher structure
        $courses = $pdo->prepare("
            SELECT DISTINCT c.id, c.title 
            FROM courses c 
            JOIN course_teachers ct ON c.id = ct.course_id 
            WHERE ct.teacher_id = ? 
            ORDER BY c.title
        ");
        $courses->execute([$_SESSION['user']['id']]);
        $courses = $courses->fetchAll();
    } else {
        // Use legacy single-teacher structure
        $courses = $pdo->prepare("SELECT id, title FROM courses WHERE teacher_id = ? ORDER BY title");
        $courses->execute([$_SESSION['user']['id']]);
        $courses = $courses->fetchAll();
    }
} catch (Throwable $e) {
    $courses = [];
}

// Get historical lesson plan options(for quick apply)
$history_options = [];
try {
    $history_stmt = $pdo->prepare("
        SELECT 
            lp.id,
            lp.title,
            lp.subject_id, s.name as subject_name,
            lp.class_id, c.name as class_name,
            lp.start_time, lp.end_time,
            lp.lesson_date,
            lp.created_at,
            co.id as course_id, co.title as course_title
        FROM lesson_plans lp
        LEFT JOIN subjects s ON lp.subject_id = s.id
        LEFT JOIN classes c ON lp.class_id = c.id
        LEFT JOIN attendance a ON lp.id = a.lesson_plan_id
        LEFT JOIN courses co ON a.course_id = co.id
        WHERE lp.created_by = ?
        ORDER BY lp.created_at DESC
        LIMIT 3
    ");
    $history_stmt->execute([$_SESSION['user']['id']]);
    $history_options = $history_stmt->fetchAll();
} catch (Throwable $e) {
    $history_options = [];
}

// Handle submit(create or edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = trim($_POST['title'] ?? '');
    $subject_id = $_POST['subject_id'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $course_id = $_POST['course_id'] ?? '';
    $lesson_date = trim($_POST['lesson_date'] ?? date('Y-m-d'));
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $creator = $_SESSION['user']['id'];
    $edit_id = intval($_POST['edit_id'] ?? 0);

    // Validate
    if ($title === '') {
        $error = t('lesson_plans.validation_title_required');
    } elseif (empty($course_id)) {
        $error = t('lesson_plans.select_course_required');
    } elseif (empty($subject_id)) {
        $error = t('lesson_plans.select_subject_required');
    } elseif (empty($class_id)) {
        $error = t('lesson_plans.select_class_required');
    } elseif ($lesson_date === '') {
        $error = t('lesson_plans.select_lesson_date_required');
    } elseif ($start_time === '' || $end_time === '') {
        $error = t('lesson_plans.set_lesson_time_required');
    } elseif ($start_time >= $end_time) {
        $error = t('lesson_plans.end_time_after_start_time');
    } else {
        try {
            // Start database transaction
            $pdo->beginTransaction();
            
            if ($edit_id > 0) {
                // Edit existing lesson plan
                $stmt = $pdo->prepare(
                    "UPDATE lesson_plans SET title = ?, subject_id = ?, class_id = ?, lesson_date = ?, 
                     start_time = ?, end_time = ?, notes = ?, course_id = ? WHERE id = ? AND created_by = ?"
                );
                $stmt->execute([
                    $title, $subject_id, $class_id, $lesson_date, 
                    $start_time, $end_time, $notes, $course_id, $edit_id, $creator
                ]);
                
                if ($stmt->rowCount() > 0) {
                    // Check whether a matching teaching report already exists
                    $check_attendance_stmt = $pdo->prepare(
                        "SELECT id FROM attendance WHERE lesson_plan_id = ? AND user_id = ?"
                    );
                    $check_attendance_stmt->execute([$edit_id, $creator]);
                    $existing_attendance = $check_attendance_stmt->fetch();
                    
                    if ($existing_attendance) {
                        // Update existing teaching report
                        $update_attendance_stmt = $pdo->prepare(
                            "UPDATE attendance SET check_in = ?, check_out = ?, notes = ?, date = ? 
                             WHERE lesson_plan_id = ? AND user_id = ?"
                        );
                        $update_attendance_stmt->execute([$start_time, $end_time, $notes, $lesson_date, $edit_id, $creator]);
                        $msg = t('lesson_plans.update_success') . ' (teaching report updated)';
                    } else {
                        // Create new teaching report
                        $attendance_stmt = $pdo->prepare(
                            "INSERT INTO attendance (user_id, course_id, lesson_plan_id, date, status, check_in, check_out, notes) 
                             VALUES (?, ?, ?, ?, 'present', ?, ?, ?)"
                        );
                        $attendance_stmt->execute([
                            $creator, $course_id, $edit_id, $lesson_date,
                            $start_time, $end_time, $notes
                        ]);
                        $msg = t('lesson_plans.update_success') . ' (new teaching report created)';
                    }
                    
                    // Clear edit state
                    $edit_id = 0;
                    $edit_data = null;
                } else {
                    $error = t('lesson_plans.update_failed');
                }
            } else {
                // Create new lesson plan
                $stmt = $pdo->prepare(
                    "INSERT INTO lesson_plans (title, subject_id, class_id, lesson_date, start_time, end_time, notes, created_by, course_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $title, $subject_id, $class_id, $lesson_date, 
                    $start_time, $end_time, $notes, $creator, $course_id
                ]);
                
                // Get the newly created lesson plan ID
                $lesson_plan_id = $pdo->lastInsertId();
                
                // Always create the matching teaching report,ensure data consistency
                $attendance_stmt = $pdo->prepare(
                    "INSERT INTO attendance (user_id, course_id, lesson_plan_id, date, status, check_in, check_out, notes) 
                     VALUES (?, ?, ?, ?, 'present', ?, ?, ?)"
                );
                $attendance_stmt->execute([
                    $creator, $course_id, $lesson_plan_id, $lesson_date,
                    $start_time, $end_time, $notes
                ]);
                
                $attendance_id = $pdo->lastInsertId();
                $msg = t('lesson_plans.create_success_auto_report') . ' (teaching report ID: ' . $attendance_id . ')';
            }
            
            // Commit transaction
            $pdo->commit();
            
        } catch (Throwable $e) {
            // Rollback transaction
            $pdo->rollBack();
            $error = t('lesson_plans.save_failed', ['error' => $e->getMessage()]);
        }
    }
}

// Query lesson plan list for the current teacher
try {
    $stmt = $pdo->prepare(
        "SELECT lp.id, lp.title, lp.subject_id, lp.class_id, lp.lesson_date, lp.start_time, lp.end_time, lp.notes, lp.created_at,
                s.name AS subject_name, c.name AS class_name, u.name AS author,
                co.id AS course_id, co.title AS course_title
         FROM lesson_plans lp
         LEFT JOIN subjects s ON lp.subject_id = s.id
         LEFT JOIN classes c ON lp.class_id = c.id
         LEFT JOIN users u ON lp.created_by = u.id
         LEFT JOIN attendance a ON lp.id = a.lesson_plan_id
         LEFT JOIN courses co ON a.course_id = co.id
         WHERE lp.created_by = ?
         ORDER BY lp.created_at DESC
         LIMIT 3"
    );
    $stmt->execute([$_SESSION['user']['id']]);
    $plans = $stmt->fetchAll();
} catch (Throwable $e) {
    $plans = [];
    if ($error === '') {
        $error = t('errors.query_failed', ['error' => $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('lesson_plans.title') ?> - ERPH</title>
    <style>
        :root {
            --bg-primary: #f5f5f5;
            --bg-secondary: #ffffff;
            --bg-tertiary: #f8f9fa;
            --text-primary: #333333;
            --text-secondary: #666666;
            --text-muted: #999999;
            --accent-color: #2563eb;
            --accent-hover: #3b82f6;
            --border-color: #e1e5e9;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --shadow-hover: rgba(0, 0, 0, 0.15);
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --header-bg: linear-gradient(90deg, #2563eb, #3b82f6);
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #1e2328;
            --bg-tertiary: #2d3748;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #999999;
            --accent-color: #60a5fa;
            --accent-hover: #93c5fd;
            --border-color: #2d3748;
            --shadow-color: rgba(0, 0, 0, 0.3);
            --shadow-hover: rgba(0, 0, 0, 0.5);
            --header-bg: linear-gradient(90deg, #1e3a8a, #3b82f6);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Header styles - based onteacher_dashboard.php */
        .header {
            background: var(--header-bg);
            color: white;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px var(--shadow-color);
            transition: background 0.3s ease;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .header > div {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.15);
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.2);
            outline: none;
            box-shadow: none;
            backdrop-filter: blur(10px);
        }

        .header a:hover {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        .content-wrapper {
            padding: 40px;
            background: var(--bg-primary);
        }

        .profile-section {
            background: var(--bg-secondary);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 8px 32px var(--shadow-color);
            border: 1px solid var(--border-color);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .profile-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--accent-color);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--accent-color);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px var(--shadow-color);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
            transform: translateY(-2px);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px var(--shadow-color);
        }

        .btn:hover {
            background: var(--accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow-hover);
        }

        .btn-secondary {
            background: var(--text-secondary);
        }

        .btn-small {
            padding: 10px 20px;
            font-size: 14px;
        }

        .btn-danger {
            background: var(--danger-color);
        }

        .form-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .plan-item {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .plan-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 30%, rgba(74, 144, 226, 0.03) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .plan-item:hover {
            border-color: var(--accent-color);
            box-shadow: 0 15px 50px var(--shadow-hover);
            transform: translateY(-5px);
        }

        .plan-item:hover::before {
            transform: translateX(100%);
        }

        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .plan-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--accent-color);
        }

        .plan-date {
            color: var(--text-secondary);
            font-size: 16px;
        }

        .plan-actions {
            display: flex;
            gap: 12px;
        }

        .plan-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            padding: 12px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            min-height: 80px;
            justify-content: center;
        }

        .detail-item:hover {
            background: var(--bg-secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-hover);
        }

        .detail-label {
            font-size: 10px;
            color: var(--text-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 600;
            line-height: 1.3;
        }

        .no-plans {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }

        .no-plans-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.6;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .no-plans h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .no-plans p {
            font-size: 18px;
            color: var(--text-muted);
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .alert-success {
            background: var(--success-color);
            color: white;
        }

        .alert-success::before {
            background: var(--success-color);
        }

        .alert-error {
            background: var(--danger-color);
            color: white;
        }

        .alert-error::before {
            background: var(--danger-color);
        }

        .btn-apply {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-apply:hover {
            background: var(--success-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .history-section {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px var(--shadow-color);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .history-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
        }

        .history-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .history-item {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .history-item:hover {
            border-color: var(--accent-color);
            box-shadow: 0 6px 20px var(--shadow-hover);
            transform: translateY(-3px);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .history-title-text {
            font-size: 16px;
            font-weight: 600;
            color: var(--accent-color);
        }

        .history-date {
            font-size: 12px;
            color: var(--text-muted);
        }

        .history-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 15px;
        }

        .history-detail {
            display: flex;
            flex-direction: column;
        }

        .history-detail-label {
            font-size: 10px;
            color: var(--text-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .history-detail-value {
            font-size: 13px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .history-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header > div {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .content-wrapper {
                padding: 20px;
            }
            
            .profile-section {
                padding: 25px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .plan-item {
                padding: 20px;
            }
            
            .plan-details {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .detail-item {
                min-height: 70px;
                padding: 10px;
            }
            
            .form-buttons {
                flex-direction: column;
            }
            
            .history-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .history-item {
                padding: 15px;
            }
            
            .history-details {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .plan-actions {
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .plan-details {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .detail-item {
                min-height: 60px;
                padding: 8px;
            }
        }

        @media (min-width: 1200px) {
            .plan-details {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header - based onteacher_dashboard.php -->
        <header class="header">
            <h1>ERPH System - <?= t('lesson_plans.title') ?></h1>
            <div>
                <a href="teacher_dashboard.php"><?= t('common.back') ?><?= t('common.dashboard') ?></a>
                <a href="logout.php"><?= t('common.logout') ?></a>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Create/Edit lesson plan form -->
            <div class="profile-section">
                <h2 class="section-title"><?= $edit_id ? t('lesson_plans.edit_lesson_plan') : t('lesson_plans.create_new_lesson_plan') ?></h2>
                
                <?php if ($msg): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Historical plan selection -->
                <?php if (!empty($history_options)): ?>
                <div class="history-section">
                    <h3 class="history-title"><?= t('lesson_plans.quick_apply_last_plan') ?></h3>
                    <div class="history-grid">
                        <?php foreach ($history_options as $history): ?>
                        <div class="history-item">
                            <div class="history-header">
                                <div class="history-title-text"><?= htmlspecialchars($history['title']) ?></div>
                                <div class="history-date"><?= date('m-d', strtotime($history['created_at'])) ?></div>
                            </div>
                            <div class="history-details">
                                <div class="history-detail">
                                    <div class="history-detail-label"><?= t('common.course') ?></div>
                                    <div class="history-detail-value"><?= htmlspecialchars($history['course_title'] ?? t('lesson_plans.not_specified')) ?></div>
                                </div>
                                <div class="history-detail">
                                    <div class="history-detail-label"><?= t('lesson_plans.textbook') ?></div>
                                    <div class="history-detail-value"><?= htmlspecialchars($history['subject_name'] ?? t('lesson_plans.not_specified')) ?></div>
                                </div>
                                <div class="history-detail">
                                    <div class="history-detail-label"><?= t('common.class') ?></div>
                                    <div class="history-detail-value"><?= htmlspecialchars($history['class_name'] ?? t('lesson_plans.not_specified')) ?></div>
                                </div>
                                <div class="history-detail">
                                    <div class="history-detail-label"><?= t('common.time') ?></div>
                                    <div class="history-detail-value"><?= $history['start_time'] ?> - <?= $history['end_time'] ?></div>
                                </div>
                                <div class="history-detail">
                                    <div class="history-detail-label"><?= t('common.date') ?></div>
                                    <div class="history-detail-value"><?= date('m-d', strtotime($history['lesson_date'])) ?></div>
                                </div>
                            </div>
                            <div class="history-actions">
                                <button type="button" class="btn-apply" onclick="applyHistory(<?= htmlspecialchars(json_encode($history)) ?>)">
                                    <?= t('lesson_plans.apply_this_plan') ?>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?= t('lesson_plans.title_label') ?> *</label>
                            <input type="text" name="title" class="form-input" value="<?= htmlspecialchars($edit_data['title'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('common.course') ?> *</label>
                            <select name="course_id" class="form-input" required onchange="updateSubjects()">
                                <option value=""><?= t('lesson_plans.select_course') ?></option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= ($edit_data['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($course['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?= t('lesson_plans.subject_textbook') ?></label>
                            <select name="subject_id" class="form-input" id="subjectSelect">
                                <option value=""><?= t('lesson_plans.please_select_course_first') ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('common.class') ?></label>
                            <select name="class_id" class="form-input">
                                <option value=""><?= t('lesson_plans.select_class') ?></option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" <?= ($edit_data['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($class['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?= t('lesson_plans.lesson_date') ?> *</label>
                            <input type="date" name="lesson_date" class="form-input" value="<?= $edit_data['lesson_date'] ?? date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?= t('lesson_plans.start_time') ?> *</label>
                            <input type="time" name="start_time" class="form-input" value="<?= $edit_data['start_time'] ?? '08:00' ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                                                    <label class="form-label"><?= t('lesson_plans.end_time') ?> *</label>
                        <input type="time" name="end_time" class="form-input" value="<?= $edit_data['end_time'] ?? '09:00' ?>" required>
                    </div>
                    
                    <div class="form-group">
                                                    <label class="form-label"><?= t('common.notes') ?></label>
                        <textarea name="notes" class="form-input" rows="4" placeholder="<?= t('lesson_plans.notes_placeholder') ?>"><?= htmlspecialchars($edit_data['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                    <input type="hidden" name="save" value="1">
                    
                    <div class="form-buttons">
                        <?php if ($edit_id): ?>
                            <button type="submit" class="btn"><?= t('lesson_plans.update_lesson_plan') ?></button>
                            <a href="lessonplans.php" class="btn btn-secondary"><?= t('lesson_plans.cancel_edit') ?></a>
                        <?php else: ?>
                            <button type="submit" class="btn"><?= t('lesson_plans.create_lesson_plan') ?></button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lesson plan list -->
            <div class="profile-section">
                <h2 class="section-title"><?= t('lesson_plans.my_lesson_plans') ?></h2>
                <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 14px;"><?= glyph('list') ?> <?= t('lesson_plans.show_recent_plans') ?></p>
                
                <?php if (empty($plans)): ?>
                    <div class="no-plans">
                        <div class="no-plans-icon"><?= glyph('clipboard') ?></div>
                        <h3><?= t('lesson_plans.no_plans') ?></h3>
                        <p><?= t('lesson_plans.no_plans_desc') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-item">
                            <div class="plan-header">
                                <div class="plan-title"><?= htmlspecialchars($plan['title']) ?></div>
                                <div class="plan-actions">
                                    <a href="?edit=<?= $plan['id'] ?>" class="btn btn-small"><?= t('common.edit') ?></a>
                                                                            <button type="button" class="btn-apply" onclick="applyPlanAsTemplate(<?= htmlspecialchars(json_encode($plan)) ?>)">
                                            <?= t('lesson_plans.apply_as_template') ?>
                                        </button>
                                                                            <form method="POST" action="" style="display: inline;" onsubmit="return confirmDelete(<?= $plan['id'] ?>, '<?= htmlspecialchars($plan['title']) ?>')">
                                        <input type="hidden" name="delete_id" value="<?= $plan['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger"><?= t('common.delete') ?></button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="plan-details">
                                <div class="detail-item">
                                    <div class="detail-label"><?= t('common.course') ?></div>
                                    <div class="detail-value"><?= htmlspecialchars($plan['course_title'] ?? t('common.not_specified')) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label"><?= t('lesson_plans.textbook') ?></div>
                                    <div class="detail-value"><?= htmlspecialchars($plan['subject_name'] ?? t('common.not_specified')) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label"><?= t('common.class') ?></div>
                                    <div class="detail-value"><?= htmlspecialchars($plan['class_name'] ?? t('common.not_specified')) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label"><?= t('common.date') ?></div>
                                    <div class="detail-value"><?= date('Y-m-d', strtotime($plan['lesson_date'])) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label"><?= t('common.time') ?></div>
                                    <div class="detail-value"><?= $plan['start_time'] ?> - <?= $plan['end_time'] ?></div>
                                </div>
                                
                                <?php if ($plan['notes']): ?>
                                    <div class="detail-item" style="grid-column: 1 / -1;">
                                        <div class="detail-label"><?= t('common.notes') ?></div>
                                        <div class="detail-value"><?= htmlspecialchars($plan['notes']) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle feature
        function initTheme() {
            const savedTheme = sessionStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        }

        function changeTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            sessionStorage.setItem('theme', theme);
            
            // Send to server to save
            fetch('change_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'theme=' + theme
            });
        }

        // Initialize theme and form on page load
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            initForm();
            
            // Add scroll animation
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe all cards
            document.querySelectorAll('.plan-item, .profile-section').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });

        // Initialize form
        function initForm() {
            // If edit data exists,Initialize course and textbook
            <?php if ($edit_id && $edit_data): ?>
            if (document.querySelector('select[name="course_id"]').value) {
                updateSubjects();
            }
            <?php endif; ?>
        }

        // Update textbook options based on course
        function updateSubjects() {
            const courseSelect = document.querySelector('select[name="course_id"]');
            const subjectSelect = document.getElementById('subjectSelect');
            const selectedCourseId = courseSelect.value;
            
            // Clear textbook options
            subjectSelect.innerHTML = '<option value=""><?= t('lesson_plans.please_select_course_first') ?></option>';
            
            if (!selectedCourseId) {
                return;
            }
            
            // Get textbooks related to this course
            const subjects = <?= json_encode($all_subjects) ?>;
            const courseSubjects = subjects.filter(subject => 
                subject.course_id == selectedCourseId
            );
            
            // Add textbook options
            courseSubjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                <?php if ($edit_id && $edit_data): ?>
                if (subject.id == <?= $edit_data['subject_id'] ?? 'null' ?>) {
                    option.selected = true;
                }
                <?php endif; ?>
                subjectSelect.appendChild(option);
            });
            
            // If there are no related textbooks,show a hint
            if (courseSubjects.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '<?= t('lesson_plans.no_subjects_for_course') ?>';
                option.disabled = true;
                subjectSelect.appendChild(option);
            }
        }

        // Apply historical plan
        function applyHistory(history) {
            document.querySelector('input[name="title"]').value = history.title;
            document.querySelector('select[name="course_id"]').value = history.course_id;
            document.querySelector('select[name="subject_id"]').value = history.subject_id;
            document.querySelector('select[name="class_id"]').value = history.class_id;
            document.querySelector('input[name="lesson_date"]').value = history.lesson_date;
            document.querySelector('input[name="start_time"]').value = history.start_time;
            document.querySelector('input[name="end_time"]').value = history.end_time;
            document.querySelector('textarea[name="notes"]').value = history.notes;

            // Reload textbook options to reflect course changes
            updateSubjects();
            // Ensure the correct textbook is selected
            document.querySelector('select[name="subject_id"]').value = history.subject_id;

            alert('<?= t('lesson_plans.plan_applied') ?>');
        }

        // Apply current lesson plan as template
        function applyPlanAsTemplate(plan) {
            const titleInput = document.querySelector('input[name="title"]');
            const courseSelect = document.querySelector('select[name="course_id"]');
            const subjectSelect = document.getElementById('subjectSelect');
            const classSelect = document.querySelector('select[name="class_id"]');
            const lessonDateInput = document.querySelector('input[name="lesson_date"]');
            const startTimeInput = document.querySelector('input[name="start_time"]');
            const endTimeInput = document.querySelector('input[name="end_time"]');
            const notesTextarea = document.querySelector('textarea[name="notes"]');

            titleInput.value = plan.title;
            courseSelect.value = plan.course_id;
            subjectSelect.value = plan.subject_id;
            classSelect.value = plan.class_id;
            lessonDateInput.value = plan.lesson_date;
            startTimeInput.value = plan.start_time;
            endTimeInput.value = plan.end_time;
            notesTextarea.value = plan.notes;

            // Reload textbook options to reflect course changes
            updateSubjects();
            // Ensure the correct textbook is selected
            subjectSelect.value = plan.subject_id;

            alert('<?= t('lesson_plans.plan_applied_as_template') ?>');
        }

        // Delete confirmation function
        function confirmDelete(planId, planTitle) {
            const confirmMessage = `Are you sure you want to delete lesson plan "${planTitle}"?\n\nNote:\n• This action cannot be undone\n• Related teaching reports will also be deleted\n• Related data cannot be recovered after deletion`;
            return confirm(confirmMessage);
        }
    </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

