<?php
// teaching_reports.php - Teaching reports browse page(formerly attendance.php)
require_once __DIR__ . '/inc/bootstrap.php';
gate_signed_in();

require_once __DIR__ . '/../db.php';
$activeAccount = signed_account();

// Get teaching report data for the current user
try {
    // Query teaching reports for the current user (newest date first, limit 100), including related lesson plan info
    $sql = "SELECT a.*, u.name as teacher_name, c.title as course_title, 
                   c.description as course_description, a.check_in, a.check_out,
                   a.notes, a.created_at, a.date,
                   lp.id as lesson_plan_id, lp.title as lesson_plan_title, 
                   lp.description as lesson_plan_description,
                   lp.lesson_date, lp.start_time, lp.end_time
            FROM attendance a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN courses c ON a.course_id = c.id
            LEFT JOIN lesson_plans lp ON a.lesson_plan_id = lp.id
            WHERE a.user_id = ?
            ORDER BY a.date DESC, a.created_at DESC 
            LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$activeAccount['id']]);
    $reports = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = t('teaching_reports.query_failed') . ": " . $e->getMessage();
    $reports = [];
}

// Get statistics for the current user
try {
    $today = date('Y-m-d');
    
    // Total reports - based on teaching report count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendance WHERE user_id = ?");
    $stmt->execute([$activeAccount['id']]);
    $total_reports = $stmt->fetch()['total'];
    
    // Today's reports - based on today's teaching report count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendance WHERE user_id = ? AND DATE(date) = ?");
    $stmt->execute([$activeAccount['id'], $today]);
    $today_total = $stmt->fetch()['total'];
    
    // Present reports - based on teaching reports with status present
    $stmt = $pdo->prepare("SELECT COUNT(*) as present FROM attendance WHERE user_id = ? AND status = 'present'");
    $stmt->execute([$activeAccount['id']]);
    $total_present = $stmt->fetch()['present'];
    
    // This week's reports - based on this week's teaching report count
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $stmt = $pdo->prepare("SELECT COUNT(*) as week_total FROM attendance WHERE user_id = ? AND DATE(date) >= ?");
    $stmt->execute([$activeAccount['id'], $week_start]);
    $week_total = $stmt->fetch()['week_total'];
    
} catch (PDOException $e) {
    $total_reports = $today_total = $total_present = $week_total = 0;
}


?>
<!DOCTYPE html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('teaching_reports.title') ?> - ERPH</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: var(--bg-secondary);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 32px var(--shadow-color);
            border: 2px solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px var(--shadow-hover);
            border-color: var(--accent-color);
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-number {
            font-size: 56px;
            font-weight: 800;
            color: var(--accent-color);
            margin-bottom: 15px;
            display: block;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .reports-section {
            background: var(--bg-secondary);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 8px 32px var(--shadow-color);
            border: 1px solid var(--border-color);
            margin-bottom: 40px;
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

        .report-item {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .report-item::before {
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

        .report-item:hover {
            border-color: var(--accent-color);
            box-shadow: 0 15px 50px var(--shadow-hover);
            transform: translateY(-5px);
        }

        .report-item:hover::before {
            transform: translateX(100%);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .report-date {
            font-size: 22px;
            font-weight: 700;
            color: var(--accent-color);
        }

        .report-status {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-present {
            background: var(--success-color);
            color: white;
        }

        .status-absent {
            background: var(--danger-color);
            color: white;
        }

        .status-late {
            background: var(--warning-color);
            color: var(--text-primary);
        }

        .report-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            background: var(--bg-secondary);
            transform: translateY(-2px);
        }

        .detail-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 16px;
            color: var(--text-primary);
            font-weight: 600;
        }

        .no-reports {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }

        .no-reports-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.6;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .no-reports h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .no-reports p {
            font-size: 18px;
            color: var(--text-muted);
        }

        /* Manage records button styles */
        .manage-controls {
            text-align: right;
            margin-bottom: 30px;
        }

        .manage-btn {
            background: var(--accent-color);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px var(--shadow-color);
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .manage-btn:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--shadow-hover);
        }

        .manage-icon {
            font-size: 20px;
        }

        .manage-text {
            display: inline-block;
        }

        /* Manage toolbar styles */
        .manage-toolbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-secondary);
            border-top: 2px solid var(--accent-color);
            box-shadow: 0 -4px 20px var(--shadow-color);
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .manage-toolbar.show {
            transform: translateY(0);
        }

        .toolbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .toolbar-left {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .selected-count {
            color: var(--accent-color);
        }

        .toolbar-right {
            display: flex;
            gap: 15px;
        }

        .toolbar-btn {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .toolbar-btn:hover {
            background: var(--bg-secondary);
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .toolbar-btn.delete-btn {
            background: var(--danger-color);
            color: white;
            border-color: var(--danger-color);
        }

        .toolbar-btn.delete-btn:hover {
            background: #c82333;
            border-color: #c82333;
        }

        .btn-icon {
            font-size: 16px;
        }

        /* Style adjustments in manage state */
        .manage-mode .report-card {
            border-left: 4px solid var(--accent-color);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .manage-mode .report-card::before {
            content: 'Click to select';
            position: absolute;
            top: 10px;
            right: 15px;
            background: var(--accent-color);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            opacity: 0.8;
            transition: opacity 0.3s ease;
            z-index: 10;
        }

        .manage-mode .report-card:hover::before {
            opacity: 1;
        }

        .manage-mode .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px var(--shadow-hover);
        }

        .manage-mode .report-card.selected {
            background: rgba(74, 144, 226, 0.05);
            border-color: var(--accent-color);
            animation: pulse 1.5s infinite;
        }

        .manage-mode .report-card.selected::before {
            content: 'Selected';
            background: var(--success-color);
        }

        /* Selected-state blink effect */
        @keyframes pulse {
            0% { 
                box-shadow: 0 0 0 0 rgba(74, 144, 226, 0.4);
                border-color: var(--accent-color);
            }
            50% { 
                box-shadow: 0 0 0 8px rgba(74, 144, 226, 0.2);
                border-color: var(--accent-hover);
            }
            100% { 
                box-shadow: 0 0 0 0 rgba(74, 144, 226, 0.4);
                border-color: var(--accent-color);
            }
        }

        /* Report control area styles */
        .report-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* New card styles */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        @media (min-width: 1200px) {
            .reports-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 768px) and (max-width: 1199px) {
            .reports-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 576px) and (max-width: 767px) {
            .reports-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .report-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 32px var(--shadow-color);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .report-card::before {
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

        .report-card:hover {
            border-color: var(--accent-color);
            box-shadow: 0 15px 50px var(--shadow-hover);
            transform: translateY(-5px);
        }

        .report-card:hover::before {
            transform: translateX(100%);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-date {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent-color);
        }

        .card-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-present {
            background: var(--success-color);
            color: white;
        }

        .status-absent {
            background: var(--danger-color);
            color: white;
        }

        .status-late {
            background: var(--warning-color);
            color: var(--text-primary);
        }

        .card-content {
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .card-time {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--bg-tertiary);
            border-radius: 12px;
        }

        .time-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .time-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .time-value {
            font-size: 18px;
            font-weight: 800;
            color: var(--accent-color);
        }

        .card-notes {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
            padding: 10px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            border-left: 3px solid var(--accent-color);
        }

        .notes-icon {
            font-size: 16px;
        }

        .card-footer {
            text-align: right;
            font-size: 12px;
            color: var(--text-muted);
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
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
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .stat-card {
                padding: 30px 20px;
            }
            
            .stat-number {
                font-size: 48px;
            }
            
            .reports-section {
                padding: 25px;
            }
            
            .report-item {
                padding: 20px;
            }
            
            .report-details {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            /* Manage toolbar responsive styles */
            .toolbar-content {
                flex-direction: column;
                gap: 15px;
                padding: 15px 20px;
            }

            .toolbar-right {
                flex-wrap: wrap;
                justify-content: center;
            }

            .report-controls {
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }

            /* Responsive adjustments in manage mode */
            .manage-mode .report-item::before {
                font-size: 10px;
                padding: 3px 6px;
                top: 8px;
                right: 10px;
            }

            /* Card grid responsive styles */
            .reports-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .report-card {
                padding: 20px;
            }

            .card-header {
                margin-bottom: 10px;
            }

            .card-date {
                font-size: 18px;
            }

            .card-status {
                padding: 5px 12px;
                font-size: 12px;
            }

            .card-content {
                margin-bottom: 10px;
            }

            .card-title {
                font-size: 18px;
            }

            .card-subtitle {
                font-size: 14px;
            }

            .card-time {
                gap: 15px;
                margin-bottom: 8px;
            }

            .time-label {
                font-size: 11px;
            }

            .time-value {
                font-size: 18px;
            }

            .card-notes {
                font-size: 12px;
            }

            .notes-icon {
                font-size: 16px;
            }

            .card-footer {
                text-align: left;
                font-size: 12px;
            }
        }

        @media (min-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header - based onteacher_dashboard.php -->
        <header class="header">
            <h1>ERPH System - <?= t('teaching_reports.title') ?></h1>
            <div>
                <a href="teacher_dashboard.php"><?= t('common.back') ?><?= t('common.dashboard') ?></a>
                <a href="logout.php"><?= t('common.logout') ?></a>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Manage records button -->
            <div class="manage-controls" style="margin-bottom: 30px; text-align: right;">
                <button id="manageBtn" class="manage-btn">
                    <span class="manage-icon"><?= glyph('manage') ?></span>
                    <span class="manage-text"><?= t('teaching_reports.manage_records') ?></span>
                </button>
            </div>

            <!-- Stats cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_reports ?></div>
                    <div class="stat-label"><?= t('teaching_reports.total_reports') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $today_total ?></div>
                    <div class="stat-label"><?= t('teaching_reports.today_reports') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $total_present ?></div>
                    <div class="stat-label"><?= t('teaching_reports.attendance_reports') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $week_total ?></div>
                    <div class="stat-label"><?= t('teaching_reports.week_reports') ?></div>
                </div>
            </div>

            <!-- Teaching report list -->
            <div class="reports-section">
                <h2 class="section-title"><?= t('teaching_reports.teaching_records') ?></h2>
                
                <?php if (empty($reports)): ?>
                    <div class="no-reports">
                        <div class="no-reports-icon"><?= glyph('analytics') ?></div>
                        <h3><?= t('teaching_reports.no_records') ?></h3>
                        <p><?= t('teaching_reports.no_records_desc') ?></p>
                    </div>
                <?php else: ?>
                    <div class="reports-grid">
                        <?php foreach ($reports as $report): ?>
                            <div class="report-card" data-report-id="<?= $report['id'] ?>">
                                <div class="card-header">
                                    <div class="card-date"><?= date('m/d', strtotime($report['date'])) ?></div>
                                    <div class="card-status status-<?= $report['status'] ?>">
                                        <span class="status-glyph"><?= glyph($report['status'] === 'present' ? 'check' : ($report['status'] === 'absent' ? 'x-mark' : 'alert')) ?></span>
                                    </div>
                                </div>
                                
                                <div class="card-content">
                                    <div class="card-title"><?= htmlspecialchars($report['course_title'] ?? t('common.not_specified')) ?></div>
                                    <div class="card-subtitle"><?= htmlspecialchars($report['lesson_plan_title'] ?? t('common.not_specified')) ?></div>
                                    
                                    <div class="card-time">
                                        <span class="time-item">
                                            <span class="time-label">Check-in</span>
                                            <span class="time-value"><?= $report['check_in'] ? date('H:i', strtotime($report['check_in'])) : '--' ?></span>
                                        </span>
                                        <span class="time-item">
                                            <span class="time-label">Check-out</span>
                                            <span class="time-value"><?= $report['check_out'] ? date('H:i', strtotime($report['check_out'])) : '--' ?></span>
                                        </span>
                                    </div>
                                    
                                    <?php if ($report['notes']): ?>
                                        <div class="card-notes">
                                            <span class="notes-icon"><?= glyph('notes') ?></span>
                                            <?= htmlspecialchars(mb_substr($report['notes'], 0, 30)) ?><?= mb_strlen($report['notes']) > 30 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-footer">
                                    <div class="card-year"><?= date('Y', strtotime($report['date'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Manage toolbar -->
        <div id="manageToolbar" class="manage-toolbar" style="display: none;">
            <div class="toolbar-content">
                <div class="toolbar-left">
                    <span class="selected-count">Selected <span id="selectedCount">0</span> record(s)</span>
                </div>
                <div class="toolbar-right">
                    <button id="selectAllBtn" class="toolbar-btn">
                        <span class="btn-icon"><?= glyph('check-square') ?></span>
                        Select all
                    </button>
                    <button id="deselectAllBtn" class="toolbar-btn">
                        <span class="btn-icon"><?= glyph('square') ?></span>
                        Deselect
                    </button>
                    <button id="deleteSelectedBtn" class="toolbar-btn delete-btn">
                        <span class="btn-icon"><?= glyph('trash') ?></span>
                        Delete
                    </button>
                </div>
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

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            
            // Initialize manage-records feature
            initManageMode();
            
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
            document.querySelectorAll('.stat-card, .report-item').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });

        // Manage records feature
        function initManageMode() {
            const manageBtn = document.getElementById('manageBtn');
            const manageToolbar = document.getElementById('manageToolbar');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
            const selectedCountSpan = document.getElementById('selectedCount');
            
            let isManageMode = false;
            let selectedReports = new Set();
            
            // Toggle manage mode
            manageBtn.addEventListener('click', function() {
                isManageMode = !isManageMode;
                
                if (isManageMode) {
                    // Enter manage mode
                    document.body.classList.add('manage-mode');
                    manageBtn.innerHTML = '<span class="manage-icon">' + ERPHGlyph.render('close') + '</span><span class="manage-text"><?= t('common.exit_manage') ?></span>';
                    manageToolbar.style.display = 'block';
                    setTimeout(() => manageToolbar.classList.add('show'), 10);
                } else {
                    // Exit manage mode
                    document.body.classList.remove('manage-mode');
                    manageBtn.innerHTML = '<span class="manage-icon"><?= glyph('manage') ?></span><span class="manage-text"><?= t('teaching_reports.manage_records') ?></span>';
                    manageToolbar.classList.remove('show');
                    setTimeout(() => manageToolbar.style.display = 'none', 300);
                    
                    // Clear selection
                    clearSelection();
                }
            });
            
            // Select all
            selectAllBtn.addEventListener('click', function() {
                const reportItems = document.querySelectorAll('.report-card');
                reportItems.forEach(item => {
                    item.classList.add('selected');
                    selectedReports.add(item.dataset.reportId);
                });
                updateSelectedCount();
            });
            
            // Deselect
            deselectAllBtn.addEventListener('click', function() {
                clearSelection();
            });
            
            // Delete selected
            deleteSelectedBtn.addEventListener('click', function() {
                if (selectedReports.size === 0) {
                    alert('Please select records to delete first');
                    return;
                }
                
                const confirmMessage = `Are you sure you want to delete the selected ${selectedReports.size} teaching record(s)?\n\nNote:\n• This action cannot be undone\n• If the record is linked to a lesson plan, the lesson plan will also be deleted\n• Related data cannot be recovered after deletion`;
                
                if (confirm(confirmMessage)) {
                    deleteSelectedReports();
                }
            });
            
            // Listen for card clicks
            document.addEventListener('click', function(e) {
                if (!isManageMode) return; // Only respond to clicks in manage mode
                
                const reportItem = e.target.closest('.report-card');
                if (reportItem) {
                    const reportId = reportItem.dataset.reportId;
                    const isSelected = reportItem.classList.contains('selected');

                    if (isSelected) {
                        reportItem.classList.remove('selected');
                        selectedReports.delete(reportId);
                    } else {
                        reportItem.classList.add('selected');
                        selectedReports.add(reportId);
                    }
                    updateSelectedCount();
                }
            });
            
            // Clear selection
            function clearSelection() {
                const reportItems = document.querySelectorAll('.report-card');
                reportItems.forEach(item => {
                    item.classList.remove('selected');
                });
                selectedReports.clear();
                updateSelectedCount();
            }
            
            // Update selected count
            function updateSelectedCount() {
                selectedCountSpan.textContent = selectedReports.size;
            }
            
            // Delete selected records
            function deleteSelectedReports() {
                const reportIds = Array.from(selectedReports);
                
                console.log('Prepare to delete records:', reportIds);
                console.log('Request URL:', './ajax/delete_attendance.php');
                
                // Send delete request
                fetch('./ajax/delete_attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_multiple',
                        report_ids: reportIds
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        // Delete succeeded; show detailed results
                        let message = `Successfully deleted ${data.deleted_count} teaching record(s)`;
                        if (data.deleted_lesson_plans > 0) {
                            message += `, and also deleted ${data.deleted_lesson_plans} related lesson plan(s)`;
                        }
                        alert(message);
                        location.reload();
                    } else {
                        alert('Delete failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Delete request failed:', error);
                    console.error('Error details:', error.message);
                    alert('Delete request failed: ' + error.message + '\n\nPlease check the browser console for more information');
                });
            }
        }
    </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

