-- Basic demo content for erph1 (keeps existing admin)
-- Teachers login: teacher1@erph.com / teacher123 , teacher2@erph.com / teacher123

SET NAMES utf8mb4;
USE erph1;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM attendance;
DELETE FROM lesson_plans;
DELETE FROM course_teachers;
DELETE FROM subjects;
DELETE FROM classes;
DELETE FROM courses;
DELETE FROM users WHERE role = 'teacher';

SET FOREIGN_KEY_CHECKS = 1;

-- Teachers (password: teacher123)
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Alice Tan', 'teacher1@erph.com', '$2y$10$JNJltnQFjP0O5hX2.W3bde8H9LOuSD85fGGZDjlSt97kAzRE6szPy', 'teacher'),
('Brian Lim', 'teacher2@erph.com', '$2y$10$JNJltnQFjP0O5hX2.W3bde8H9LOuSD85fGGZDjlSt97kAzRE6szPy', 'teacher');

SET @admin_id = (SELECT id FROM users WHERE email = 'admin@erph.com' LIMIT 1);
SET @t1 = (SELECT id FROM users WHERE email = 'teacher1@erph.com' LIMIT 1);
SET @t2 = (SELECT id FROM users WHERE email = 'teacher2@erph.com' LIMIT 1);

-- Classes
INSERT INTO `classes` (`name`, `is_active`) VALUES
('Class 5A', 1),
('Class 5B', 1),
('Class 6A', 1);

SET @c5a = (SELECT id FROM classes WHERE name = 'Class 5A' LIMIT 1);
SET @c5b = (SELECT id FROM classes WHERE name = 'Class 5B' LIMIT 1);
SET @c6a = (SELECT id FROM classes WHERE name = 'Class 6A' LIMIT 1);

-- Courses
INSERT INTO `courses` (`code`, `title`, `description`, `created_by`, `is_active`) VALUES
('ENG101', 'English Language', 'Basic English reading, writing and speaking for primary students.', @admin_id, 1),
('MATH101', 'Mathematics', 'Numbers, fractions and problem solving.', @admin_id, 1),
('SCI101', 'Science', 'Introduction to living things, materials and energy.', @admin_id, 1);

SET @eng = (SELECT id FROM courses WHERE code = 'ENG101' LIMIT 1);
SET @math = (SELECT id FROM courses WHERE code = 'MATH101' LIMIT 1);
SET @sci = (SELECT id FROM courses WHERE code = 'SCI101' LIMIT 1);

-- Assign teachers
INSERT INTO `course_teachers` (`course_id`, `teacher_id`) VALUES
(@eng, @t1),
(@math, @t1),
(@math, @t2),
(@sci, @t2);

-- Textbooks / subjects
INSERT INTO `subjects` (`name`, `course_id`, `is_active`) VALUES
('English Textbook A', @eng, 1),
('English Workbook A', @eng, 1),
('Math Textbook A', @math, 1),
('Math Workbook A', @math, 1),
('Science Textbook A', @sci, 1);

SET @eng_book = (SELECT id FROM subjects WHERE name = 'English Textbook A' LIMIT 1);
SET @math_book = (SELECT id FROM subjects WHERE name = 'Math Textbook A' LIMIT 1);
SET @sci_book = (SELECT id FROM subjects WHERE name = 'Science Textbook A' LIMIT 1);

-- Lesson plans
INSERT INTO `lesson_plans`
(`course_id`, `subject_id`, `class_id`, `lesson_date`, `start_time`, `end_time`, `title`, `description`, `notes`, `created_by`)
VALUES
(@eng, @eng_book, @c5a, CURDATE(), '08:00:00', '09:00:00', 'Reading: Short Stories',
 'Practice reading fluency and vocabulary.', 'Focus on new vocabulary list.', @t1),
(@math, @math_book, @c5b, CURDATE(), '09:30:00', '10:30:00', 'Fractions Basics',
 'Introduce simple fractions with visual aids.', 'Prepare fraction cards.', @t1),
(@sci, @sci_book, @c6a, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', 'Living Things',
 'Identify living vs non-living things.', 'Bring classroom plants for demo.', @t2);

SET @lp1 = (SELECT id FROM lesson_plans WHERE title = 'Reading: Short Stories' LIMIT 1);
SET @lp2 = (SELECT id FROM lesson_plans WHERE title = 'Fractions Basics' LIMIT 1);
SET @lp3 = (SELECT id FROM lesson_plans WHERE title = 'Living Things' LIMIT 1);

-- Teaching reports (attendance)
INSERT INTO `attendance`
(`user_id`, `course_id`, `lesson_plan_id`, `date`, `status`, `check_in`, `check_out`, `notes`)
VALUES
(@t1, @eng, @lp1, CURDATE(), 'present', '08:00:00', '09:00:00', 'Students engaged well in reading activity.'),
(@t1, @math, @lp2, CURDATE(), 'present', '09:30:00', '10:30:00', 'Most students understood basic fractions.'),
(@t2, @sci, @lp3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', '11:00:00', '12:00:00', 'Completed living things classification exercise.'),
(@t2, @sci, NULL, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'present', '10:00:00', '11:00:00', 'Revision class before new topic.');

-- Ensure default login background setting exists
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`)
VALUES ('login_background', 'default', 'Login page background setting')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
