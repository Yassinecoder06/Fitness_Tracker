-- ============================================================
--  FitTrack — Full Database Schema + Test Data
--  File: database/schema.sql
--
--  HOW TO RUN:
--    Option A (phpMyAdmin):
--      1. Open phpMyAdmin → click "SQL" tab
--      2. Paste this entire file → click Go
--
--    Option B (MySQL CLI):
--      mysql -u root -p < database/schema.sql
--
--    Option C (XAMPP MySQL CLI):
--      cd C:\xampp\mysql\bin
--      mysql -u root < C:\path\to\Fitness_Tracker\database\schema.sql
--
--  NOTES:
--    - Safe to run multiple times (uses IF NOT EXISTS / IF EXISTS).
--    - Drops existing tables in the correct order to avoid FK errors.
--    - Inserts test users with bcrypt-hashed passwords.
--    - All test passwords are: Password123
-- ============================================================


-- ── Step 1: Create (or select) the database ──────────────────
CREATE DATABASE IF NOT EXISTS fittrack
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fittrack;


-- ── Step 2: Drop tables in reverse dependency order ──────────
--    (so foreign keys don't block the drops)
DROP TABLE IF EXISTS weight_logs;
DROP TABLE IF EXISTS daily_stats;
DROP TABLE IF EXISTS goals;
DROP TABLE IF EXISTS exercise_logs;
DROP TABLE IF EXISTS meals;
DROP TABLE IF EXISTS exercises;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS users;


-- ============================================================
--  TABLE: users
--  Owner: Member 1 (Authentication)
--  Referenced by: ALL other tables via user_id FK
-- ============================================================
CREATE TABLE users (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    password   VARCHAR(255) NOT NULL,               -- stores bcrypt hash ONLY, never plain text
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_users_email UNIQUE (email)        -- duplicate emails rejected at DB level
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: foods
--  Owner: Member 4 (Food Database)
--  A master catalog of foods — NOT per-user
-- ============================================================
CREATE TABLE foods (
    id       INT          AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    category VARCHAR(50)  NOT NULL,                 -- Fruits, Vegetables, Protein, Grains, Dairy, Snacks
    calories INT          NOT NULL DEFAULT 0,
    protein  INT          NOT NULL DEFAULT 0,        -- grams
    carbs    INT          NOT NULL DEFAULT 0,        -- grams
    fat      INT          NOT NULL DEFAULT 0         -- grams
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: exercises
--  Owner: Member 5 (Exercise System)
--  A master catalog of exercise types — NOT per-user
-- ============================================================
CREATE TABLE exercises (
    id               INT          AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(100) NOT NULL,
    category         VARCHAR(50)  NOT NULL,          -- Cardio, Strength, Flexibility, Sports
    calories_per_min INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: meals
--  Owner: Member 3 (Diary System)
--  Per-user daily meal log
-- ============================================================
CREATE TABLE meals (
    id        INT          AUTO_INCREMENT PRIMARY KEY,
    user_id   INT          NOT NULL,
    food_name VARCHAR(100) NOT NULL,
    meal_type VARCHAR(50)  NOT NULL,                 -- Breakfast, Lunch, Dinner, Snack
    calories  INT          NOT NULL DEFAULT 0,
    protein   INT          NOT NULL DEFAULT 0,
    carbs     INT          NOT NULL DEFAULT 0,
    fat       INT          NOT NULL DEFAULT 0,
    date      DATE         NOT NULL,

    CONSTRAINT fk_meals_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: exercise_logs
--  Owner: Member 3 & 5 (Diary / Exercise)
--  Per-user daily exercise log
-- ============================================================
CREATE TABLE exercise_logs (
    id             INT          AUTO_INCREMENT PRIMARY KEY,
    user_id        INT          NOT NULL,
    exercise_name  VARCHAR(100) NOT NULL,
    duration       INT          NOT NULL DEFAULT 0,  -- minutes
    calories_burned INT         NOT NULL DEFAULT 0,
    date           DATE         NOT NULL,

    CONSTRAINT fk_exercise_logs_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: goals
--  Owner: Member 6 (Goals & Progress)
--  One row per user — upserted (updated) when goals change
-- ============================================================
CREATE TABLE goals (
    id               INT  AUTO_INCREMENT PRIMARY KEY,
    user_id          INT  NOT NULL,
    target_weight    INT  DEFAULT NULL,              -- kg
    daily_calories   INT  DEFAULT NULL,              -- kcal
    weekly_workouts  INT  DEFAULT NULL,              -- times per week
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_goals_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT uq_goals_user UNIQUE (user_id)       -- one goals row per user
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: weight_logs
--  Owner: Member 6 (Progress)
--  Historical weight entries for charting
-- ============================================================
CREATE TABLE weight_logs (
    id      INT   AUTO_INCREMENT PRIMARY KEY,
    user_id INT   NOT NULL,
    weight  FLOAT NOT NULL,                          -- kg
    date    DATE  NOT NULL,

    CONSTRAINT fk_weight_logs_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  TABLE: daily_stats
--  Owner: Member 2 (Dashboard)
--  Aggregated daily values (water, quick calorie totals)
-- ============================================================
CREATE TABLE daily_stats (
    id                INT  AUTO_INCREMENT PRIMARY KEY,
    user_id           INT  NOT NULL,
    date              DATE NOT NULL,
    water_glasses     INT  NOT NULL DEFAULT 0,
    calories_consumed INT  NOT NULL DEFAULT 0,

    CONSTRAINT fk_daily_stats_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT uq_daily_stats UNIQUE (user_id, date) -- one row per user per day
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- ── Food catalog (Member 4 test data) ────────────────────────
INSERT INTO foods (name, category, calories, protein, carbs, fat) VALUES
('Banana',         'Fruits',     105,  1, 27,  0),
('Apple',          'Fruits',      80,  0, 21,  0),
('Strawberries',   'Fruits',      49,  1, 12,  0),
('Orange',         'Fruits',      62,  1, 15,  0),
('Blueberries',    'Fruits',      84,  1, 21,  0),
('Broccoli',       'Vegetables',  55,  4, 11,  1),
('Spinach',        'Vegetables',  23,  3,  4,  0),
('Carrot',         'Vegetables',  41,  1, 10,  0),
('Tomato',         'Vegetables',  22,  1,  5,  0),
('Chicken Breast', 'Protein',    240, 45,  0,  5),
('Tuna (canned)',  'Protein',    132, 29,  0,  1),
('Eggs (2 large)', 'Protein',    143, 13,  1, 10),
('Greek Yogurt',   'Dairy',      100, 17,  6,  0),
('Milk (1 cup)',   'Dairy',      149,  8, 12,  8),
('Cheddar Cheese', 'Dairy',      113,  7,  0,  9),
('Brown Rice',     'Grains',     216,  5, 45,  2),
('Oatmeal',        'Grains',     158,  6, 27,  3),
('Whole Wheat Bread', 'Grains',  69,   4, 12,  1),
('Almonds (30g)',  'Snacks',     173,  6,  6, 15),
('Dark Chocolate', 'Snacks',     170,  2, 13, 12);


-- ── Exercise catalog (Member 5 test data) ────────────────────
INSERT INTO exercises (name, category, calories_per_min) VALUES
('Running',          'Cardio',      11),
('Cycling',          'Cardio',       9),
('Jump Rope',        'Cardio',      12),
('Swimming',         'Cardio',      10),
('HIIT',             'Cardio',      13),
('Bench Press',      'Strength',     6),
('Deadlift',         'Strength',     7),
('Pull-ups',         'Strength',     7),
('Squats',           'Strength',     6),
('Push-ups',         'Strength',     5),
('Yoga',             'Flexibility',  4),
('Pilates',          'Flexibility',  5),
('Stretching',       'Flexibility',  3),
('Football',         'Sports',       9),
('Basketball',       'Sports',       9),
('Tennis',           'Sports',       8);


-- ── Sample diary entries for John (user_id = 1) ──────────────
INSERT INTO meals (user_id, food_name, meal_type, calories, protein, carbs, fat, date) VALUES
(1, 'Oatmeal with Berries',  'Breakfast', 280,  8, 48,  6, CURDATE()),
(1, 'Green Smoothie',        'Breakfast', 140,  5, 28,  2, CURDATE()),
(1, 'Grilled Chicken Salad', 'Lunch',     380, 35, 18, 16, CURDATE()),
(1, 'Whole Wheat Bread',     'Lunch',     120,  4, 22,  2, CURDATE()),
(1, 'Apple',                 'Lunch',      80,  0, 21,  0, CURDATE()),
(1, 'Salmon with Rice',      'Dinner',    450, 33, 43, 19, CURDATE());
