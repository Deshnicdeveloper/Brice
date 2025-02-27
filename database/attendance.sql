-- Create attendance table
CREATE TABLE `attendance` (
  `attendance_id` INT PRIMARY KEY AUTO_INCREMENT,
  `pupil_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present', 'absent', 'late') NOT NULL,
  `term` TINYINT NOT NULL,
  `reason` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`pupil_id`) REFERENCES `pupils`(`pupil_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_attendance` (`pupil_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for common queries
CREATE INDEX `idx_attendance_pupil_date` ON `attendance` (`pupil_id`, `date`);
CREATE INDEX `idx_attendance_date` ON `attendance` (`date`);
CREATE INDEX `idx_attendance_term` ON `attendance` (`term`); 