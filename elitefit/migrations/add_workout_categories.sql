-- Migration: Create workout_categories table
CREATE TABLE IF NOT EXISTS workout_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    default_duration INT NOT NULL DEFAULT 30 -- default in minutes
);

-- Insert default categories
INSERT INTO workout_categories (name, default_duration) VALUES
('Cardio', 30),
('Strength', 45),
('Yoga', 40),
('Quick HIIT', 1); -- 15 seconds = 0.25 minutes
ALTER TABLE workout_categories
MODIFY COLUMN default_duration DECIMAL(5,2) NOT NULL DEFAULT 30.00;

ALTER TABLE workout_sessions
MODIFY COLUMN duration DECIMAL(5,2) DEFAULT NULL COMMENT 'duration in minutes (supports fractions)';
