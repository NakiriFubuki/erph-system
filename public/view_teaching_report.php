<?php
// view_teaching_report.php - View teaching report details
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

require_once __DIR__ . '/../db.php';
$activeAccount = signed_account();
$error = '';
$report = null;

// Get report ID
$report_id = $_GET['id'] ?? 0;
if (!$report_id) {
    header('Location: admin_teaching_reports.php');
    exit;
}

try {
    // Get teaching report details
    $sql = "
        SELECT 
            a.id,
            a.date,
            a.status,
            a.check_in,
            a.check_out,
            a.created_at,
            u.name as teacher_name,
            u.email as teacher_email,
            u.id as teacher_id,
            c.title as course_title,
            c.description as course_description,
            c.id as course_id
        FROM attendance a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN courses c ON a.course_id = c.id
        WHERE a.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();
    
    if (!$report) {
        $error = 'Teaching report not found';
    }
    
} catch (Exception $e) {
    $error = 'Failed to load teaching report: ' . $e->getMessage();
}

// Status translation
function getStatusText($status) {
    switch ($status) {
        case 'present': return 'Present';
        case 'absent': return 'Absent';
        case 'leave': return 'Leave';
        default: return 'Unknown';
    }
}

// Status styles
function getStatusClass($status) {
    switch ($status) {
        case 'present': return 'status-present';
        case 'absent': return 'status-absent';
        case 'leave': return 'status-leave';
        default: return '';
    }
}
?>
<!doctype html>
<html lang="<?= t('common.language_code') ?>" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="utf-8">
    <title><?= t('teaching_reports.report_info') ?> - ERPH</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* Page layout styles */
        .admin-layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        main {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        /* Page header styles */
        .page-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e9ecef;
        }
        
        .page-header h2 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        
        /* Report detail styles */
        .report-details {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        .report-section {
            padding: 25px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .report-section:last-child {
            border-bottom: none;
        }
        
        .report-section h3 {
            color: #2563eb;
            margin: 0 0 20px 0;
            font-size: 18px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        
        .report-section p {
            margin: 12px 0;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .report-section strong {
            color: #495057;
            font-weight: 600;
            min-width: 120px;
            display: inline-block;
        }
        
        /* Status styles */
        .status-present {
            background: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-absent {
            background: #f8d7da;
            color: #721c24;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-leave {
            background: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Button styles */
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        /* Error message styles */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .admin-layout {
                padding: 10px;
            }
            
            main {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .report-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>ERPH System - <?= t('teaching_reports.report_info') ?></h1>
        <div>
            <a href="admin_teaching_reports.php"><?= t('common.back') ?> <?= t('teaching_reports.title') ?></a>
            <a href="admin_dashboard.php"><?= t('common.back') ?> <?= t('common.dashboard') ?></a>
            <a href="logout.php"><?= t('common.logout') ?></a>
        </div>
    </header>

    <div class="admin-layout">
        <main style="margin-left: 0; width: 100%;">
            <div class="page-header">
                <h2><?= t('teaching_reports.report_info') ?></h2>
                <div class="action-buttons">
                    <a href="admin_teaching_reports.php" class="btn btn-secondary"><?= t('common.back') ?></a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif ($report): ?>
                <div class="report-details">
                    <!-- Basic information -->
                    <div class="report-section">
                        <h3><?= t('teaching_reports.report_info') ?></h3>
                        <p><strong><?= t('teaching_reports.report_info') ?> ID:</strong> #<?= htmlspecialchars($report['id']) ?></p>
                        <p><strong><?= t('teaching_reports.date') ?>:</strong> <?= htmlspecialchars($report['date']) ?></p>
                        <p><strong><?= t('common.status') ?>:</strong> 
                            <span class="<?= getStatusClass($report['status']) ?>">
                                <?= getStatusText($report['status']) ?>
                            </span>
                        </p>
                        <p><strong><?= t('common.created_at') ?>:</strong> <?= htmlspecialchars($report['created_at']) ?></p>
                    </div>

                    <div class="report-section">
                        <h3><?= t('teacher_reports.teacher_name') ?></h3>
                        <p><strong><?= t('teacher_reports.teacher_name') ?>:</strong> <?= htmlspecialchars($report['teacher_name']) ?></p>
                        <p><strong><?= t('common.email') ?>:</strong> <?= htmlspecialchars($report['teacher_email']) ?></p>
                        <p><strong><?= t('roles.teacher') ?> ID:</strong> #<?= htmlspecialchars($report['teacher_id']) ?></p>
                    </div>

                    <div class="report-section">
                        <h3><?= t('teaching_reports.course_title') ?></h3>
                        <p><strong><?= t('teaching_reports.course_title') ?>:</strong> <?= htmlspecialchars($report['course_title'] ?? t('teaching_reports.unassigned_course')) ?></p>
                        <p><strong><?= t('common.course') ?> ID:</strong> <?= $report['course_id'] ? '#' . htmlspecialchars($report['course_id']) : t('common.none') ?></p>
                        <?php if ($report['course_description']): ?>
                        <p><strong><?= t('common.description') ?>:</strong> <?= htmlspecialchars($report['course_description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="report-section">
                        <h3><?= t('common.time') ?></h3>
                        <p><strong><?= t('teaching_reports.check_in_time') ?>:</strong> <?= $report['check_in'] ? htmlspecialchars($report['check_in']) : t('teaching_reports.not_checked_in') ?></p>
                        <p><strong><?= t('teaching_reports.check_out_time') ?>:</strong> <?= $report['check_out'] ? htmlspecialchars($report['check_out']) : t('teaching_reports.not_checked_out') ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

