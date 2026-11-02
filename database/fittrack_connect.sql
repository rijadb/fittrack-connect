
-- MySQL 8+ or recent MariaDB. Import once into a fresh database using phpMyAdmin.
-- This file does not delete an existing database or existing records.
CREATE DATABASE IF NOT EXISTS fittrack_connect
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fittrack_connect;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'trainer', 'client') NOT NULL DEFAULT 'client',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS workout_plans (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    trainer_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    focus VARCHAR(120) NOT NULL,
    level VARCHAR(40) NOT NULL DEFAULT 'Beginner',
    sessions_per_week TINYINT UNSIGNED NOT NULL DEFAULT 3,
    notes TEXT NULL,
    nutrition_guidance TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_workout_trainer (trainer_id),
    KEY idx_workout_client (client_id),
    CONSTRAINT fk_workout_trainer FOREIGN KEY (trainer_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_workout_client FOREIGN KEY (client_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exercises (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    muscle_group VARCHAR(80) NOT NULL,
    equipment VARCHAR(120) NOT NULL DEFAULT 'Bodyweight',
    difficulty VARCHAR(40) NOT NULL DEFAULT 'Beginner',
    description TEXT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Junction table: one plan may have many exercises and vice versa.
CREATE TABLE IF NOT EXISTS plan_exercises (
    plan_id INT UNSIGNED NOT NULL,
    exercise_id INT UNSIGNED NOT NULL,
    sets TINYINT UNSIGNED NOT NULL DEFAULT 3,
    reps SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    rest_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (plan_id, exercise_id),
    KEY idx_plan_exercises_exercise (exercise_id),
    CONSTRAINT fk_plan_exercises_plan FOREIGN KEY (plan_id) REFERENCES workout_plans(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_plan_exercises_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS progress_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    workout_plan_id INT UNSIGNED NULL,
    exercise_id INT UNSIGNED NULL,
    log_date DATE NOT NULL,
    log_type ENUM('weight', 'water', 'training', 'food') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    unit VARCHAR(25) NOT NULL,
    notes TEXT NULL,
    external_food_id VARCHAR(150) NULL,
    calories_kcal DECIMAL(10,2) NULL,
    protein_g DECIMAL(10,2) NULL,
    carbs_g DECIMAL(10,2) NULL,
    fat_g DECIMAL(10,2) NULL,
    PRIMARY KEY (id),
    KEY idx_logs_client_date (client_id, log_date),
    KEY idx_logs_plan (workout_plan_id),
    KEY idx_logs_exercise (exercise_id),
    CONSTRAINT fk_logs_client FOREIGN KEY (client_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_logs_plan FOREIGN KEY (workout_plan_id) REFERENCES workout_plans(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_logs_exercise FOREIGN KEY (exercise_id) REFERENCES exercises(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    trainer_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    scheduled_at DATETIME NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 45,
    status ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    meeting_url VARCHAR(500) NULL,
    PRIMARY KEY (id),
    KEY idx_appointments_trainer_time (trainer_id, scheduled_at),
    KEY idx_appointments_client_time (client_id, scheduled_at),
    CONSTRAINT fk_appointments_trainer FOREIGN KEY (trainer_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_appointments_client FOREIGN KEY (client_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- The six sample accounts share a non-production password hash.
-- Test-only password: DemoPass123!  (never reuse for real users).
INSERT IGNORE INTO users (id, name, email, password_hash, role, created_at) VALUES
(1, 'Jordan Davis', 'client@example.com', '$2y$12$Vd5wKdyB8J6.cjGX9YVbJup.g9U078jrBE.sH8naJtvgwuOL2zrCW', 'client', '2026-09-11 12:00:00'),
(2, 'Alex Morgan', 'alex.trainer@example.com', '$2y$12$Vd5wKdyB8J6.cjGX9YVbJup.g9U078jrBE.sH8naJtvgwuOL2zrCW', 'trainer', '2026-08-23 12:00:00'),
(3, 'Sam Taylor', 'sam.trainer@example.com', '$2y$12$Vd5wKdyB8J6.cjGX9YVbJup.g9U078jrBE.sH8naJtvgwuOL2zrCW', 'trainer', '2026-08-29 12:00:00'),
(4, 'Casey Brooks', 'casey@example.com', '$2y$12$Vd5wKdyB8J6.cjGX9YVbJup.g9U078jrBE.sH8naJtvgwuOL2zrCW', 'client', '2026-09-19 12:00:00'),
(5, 'Jamie Reed', 'jamie@example.com', '$2y$12$Vd5wKdyB8J6.cjGX9YVbJup.g9U078jrBE.sH8naJtvgwuOL2zrCW', 'client', '2026-10-02 12:00:00'),
(6, 'Taylor Smith', 'admin@example.com', '$2y$12$Vd5wKdyB8J6.cjGX9YVbJup.g9U078jrBE.sH8naJtvgwuOL2zrCW', 'admin', '2026-08-10 12:00:00');

INSERT IGNORE INTO workout_plans (id, trainer_id, client_id, title, focus, level, sessions_per_week, notes, nutrition_guidance) VALUES
(1, 2, 1, 'Strength Foundations', 'Full body strength', 'Beginner', 3, 'Compound movements and progressive overload.', 'Aim for balanced meals with protein after training.'),
(2, 3, 4, 'Build & Sculpt', 'Hypertrophy', 'Intermediate', 4, 'Upper/lower split with controlled tempo.', 'Discuss a consistent protein goal and adequate hydration.'),
(3, 2, 5, 'Athletic Conditioning', 'Cardio & mobility', 'All levels', 3, 'Mix cardio intervals with mobility.', 'Fuel longer sessions and hydrate throughout the day.'),
(4, 3, 1, 'Next-Level Push/Pull', 'Strength & muscle', 'Intermediate', 5, 'Alternating push and pull days.', 'Prioritize recovery, sleep, and regular meals.');

INSERT IGNORE INTO exercises (id, name, muscle_group, equipment, difficulty, description) VALUES
(1, 'Barbell Squat', 'Legs', 'Barbell', 'Intermediate', 'Keep the torso braced and drive through the feet.'),
(2, 'Bench Press', 'Chest', 'Barbell', 'Intermediate', 'Control the bar down and press evenly.'),
(3, 'Deadlift', 'Back', 'Barbell', 'Advanced', 'Engage the lats and extend through the hips.'),
(4, 'Lat Pulldown', 'Back', 'Cable machine', 'Beginner', 'Pull elbows down while keeping the core stable.'),
(5, 'Dumbbell Shoulder Press', 'Shoulders', 'Dumbbells', 'Beginner', 'Press overhead without arching your lower back.'),
(6, 'Plank', 'Core', 'Bodyweight', 'Beginner', 'Hold a straight line and breathe steadily.'),
(7, 'Walking Lunges', 'Legs', 'Bodyweight', 'Beginner', 'Take controlled steps and keep knees aligned.'),
(8, 'Biceps Curl', 'Arms', 'Dumbbells', 'Beginner', 'Keep elbows stable and lift with control.');

INSERT IGNORE INTO plan_exercises (plan_id, exercise_id, sets, reps, rest_seconds, sort_order) VALUES
(1, 1, 3, 8, 90, 1), (1, 2, 3, 10, 60, 2), (1, 6, 3, 30, 45, 3),
(2, 5, 4, 12, 60, 1), (2, 7, 4, 10, 60, 2),
(3, 6, 3, 30, 45, 1), (3, 7, 3, 15, 60, 2),
(4, 2, 4, 8, 90, 1), (4, 3, 4, 6, 120, 2);

INSERT IGNORE INTO progress_logs (id, client_id, log_date, log_type, value, unit, notes, calories_kcal) VALUES
(1, 1, '2026-10-25', 'weight', 75.20, 'kg', 'Morning weigh-in', NULL),
(2, 1, '2026-10-25', 'water', 2.40, 'L', 'Daily hydration total', NULL),
(3, 1, '2026-10-24', 'training', 12, 'sets', 'Full-body session', NULL),
(4, 4, '2026-10-24', 'weight', 67.50, 'kg', 'Weekly check-in', NULL),
(5, 1, '2026-10-23', 'food', 580, 'kcal', 'Lunch - example entry', 580),
(6, 5, '2026-10-23', 'training', 8, 'sets', 'Conditioning circuit', NULL),
(7, 1, '2026-10-22', 'water', 2.10, 'L', 'Daily hydration total', NULL);

INSERT IGNORE INTO appointments (id, trainer_id, client_id, scheduled_at, duration_minutes, status, meeting_url) VALUES
(1, 2, 1, '2026-10-28 18:00:00', 45, 'confirmed', NULL),
(2, 3, 4, '2026-10-29 10:30:00', 60, 'pending', NULL),
(3, 2, 5, '2026-10-30 16:00:00', 45, 'confirmed', NULL),
(4, 3, 1, '2026-11-02 18:00:00', 30, 'pending', NULL),
(5, 2, 4, '2026-10-19 10:00:00', 45, 'completed', NULL);
