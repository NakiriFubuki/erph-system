-- Create system settings table
-- Stores system config, including login page background settings

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL COMMENT 'Setting key',
  `setting_value` text COMMENT 'Setting value',
  `description` varchar(255) DEFAULT NULL COMMENT 'Setting description',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated at',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='System settings table';

-- Insert default background setting
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) 
VALUES ('login_background', 'default', 'Login page background setting') 
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Show table structure
DESCRIBE `system_settings`;

-- Show current settings
SELECT * FROM `system_settings`;
