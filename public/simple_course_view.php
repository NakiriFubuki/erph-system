<?php
// simple_course_view.php - Simplified course view page
require_once __DIR__ . '/inc/bootstrap.php';

gate_administrative();

require_once __DIR__ . '/../db.php';
$activeAccount = signed_account();

// Get course list
try {
    $stmt = $pdo->query("
        SELECT c.*, GROUP_CONCAT(u.name SEPARATOR ', ') as teacher_names
        FROM courses c
        LEFT JOIN course_teachers ct ON c.id = ct.course_id
        LEFT JOIN users u ON ct.teacher_id = u.id
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $courses = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Failed to get course list: " . $e->getMessage();
    $courses = [];
}
?>
<!doctype html>
<html lang="en" data-theme="<?= $_SESSION['theme'] ?? 'light' ?>">
<head>
    <meta charset="utf-8">
    <title>Course View - ERPH</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/js/theme-sync.js"></script>
    <style>
        .courses-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e1e5e9;
        }
        .course-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .course-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .course-teachers {
            color: #2563eb;
            font-weight: 500;
            margin-bottom: 10px;
        }
        .course-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .course-status.active {
            background: #d4edda;
            color: #155724;
        }
        .course-status.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .no-courses {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="courses-container">
        <div class="header">
            <h1>Course View</h1>
        </div>

        <?php if (empty($courses)): ?>
            <div class="no-courses">
                <p>No course data yet</p>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-title"><?= htmlspecialchars($course['title']) ?></div>
                        <?php if ($course['description']): ?>
                            <div class="course-description"><?= htmlspecialchars($course['description']) ?></div>
                        <?php endif; ?>
                        
                        <?php if ($course['teacher_names']): ?>
                            <div class="course-teachers">
                                Teacher: <?= htmlspecialchars($course['teacher_names']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="course-status <?= ($course['is_active'] ?? 1) ? 'active' : 'inactive' ?>">
                            <?= ($course['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="course_management.php" class="back-link">← Back to course management</a>
    </div>

    <script src="assets/js/theme-sync.js"></script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

