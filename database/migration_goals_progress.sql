-- ============================================
-- Migration: Goals & Progress System
-- Author: Member 6
-- Branch: goals-progress-system
-- Date: 2026-03-29
-- ============================================
CREATE TABLE IF NOT EXISTS goals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  target_weight INT,
  daily_calories INT,
  weekly_workouts INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_goal (user_id)
);
CREATE TABLE IF NOT EXISTS weight_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  weight FLOAT NOT NULL,
  date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_date (user_id, date)
);