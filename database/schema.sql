-- ============================================================
-- Hatsuboshi Gakuen — Student Schedule Management System
-- Database Schema
-- ============================================================
-- Target: MySQL 8.0+ / MariaDB 10.5+
-- Engine: InnoDB (required for foreign keys)
-- Charset: utf8mb4 with unicode_ci collation (proper Japanese support)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Drop tables in reverse-dependency order (safe re-import)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `producer_messages`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `event_participants`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `stat_history`;
DROP TABLE IF EXISTS `schedules`;
DROP TABLE IF EXISTS `student_songs`;
DROP TABLE IF EXISTS `songs`;
DROP TABLE IF EXISTS `lessons`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- Table: users
-- Master authentication table for all user types.
-- ============================================================
CREATE TABLE `users` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('producer','teacher','student') NOT NULL,
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: teachers
-- Teacher-specific profile (specialty: vocal/dance/visual).
-- ============================================================
CREATE TABLE `teachers` (
  `id`        INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`   INT(11) NOT NULL,
  `name`      VARCHAR(100) NOT NULL,
  `specialty` ENUM('vocal','dance','visual') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_teachers_user_id` (`user_id`),
  KEY `idx_teachers_specialty` (`specialty`),
  CONSTRAINT `fk_teachers_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: students
-- Idol-specific profile information.
-- ============================================================
CREATE TABLE `students` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11) NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `name_jp`     VARCHAR(100) DEFAULT NULL,
  `birthday`    DATE DEFAULT NULL,
  `blood_type`  CHAR(2) DEFAULT NULL,
  `height`      INT(11) DEFAULT NULL,
  `hometown`    VARCHAR(50) DEFAULT NULL,
  `school_year` VARCHAR(20) DEFAULT NULL,
  `rank`        VARCHAR(10) NOT NULL DEFAULT 'Debut',
  `vocal`       INT(11) NOT NULL DEFAULT 0,
  `dance`       INT(11) NOT NULL DEFAULT 0,
  `visual`      INT(11) NOT NULL DEFAULT 0,
  `bio`         TEXT DEFAULT NULL,
  `theme_color` VARCHAR(20) DEFAULT NULL,
  `producer_id` INT(11) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_students_user_id` (`user_id`),
  KEY `idx_students_producer_id` (`producer_id`),
  KEY `idx_students_rank` (`rank`),
  CONSTRAINT `fk_students_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_students_producer`
    FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: lessons
-- Lesson catalog (type, stat gain, description).
-- ============================================================
CREATE TABLE `lessons` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `type`        ENUM('vocal','dance','visual') NOT NULL,
  `stat_gain`   INT(11) NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lessons_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: songs
-- Catalog of songs in an idol's repertoire.
-- A song can be solo (one student) or shared (units/duets).
-- ============================================================
CREATE TABLE `songs` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(150) NOT NULL,
  `artist`       VARCHAR(150) DEFAULT NULL,
  `duration`     TIME DEFAULT NULL,
  `release_date` DATE DEFAULT NULL,
  `notes`        TEXT DEFAULT NULL,
  `lyrics_url`   VARCHAR(255) DEFAULT NULL,
  `created_by`   INT(11) DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_songs_title` (`title`),
  KEY `idx_songs_release_date` (`release_date`),
  KEY `idx_songs_created_by` (`created_by`),
  CONSTRAINT `fk_songs_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: student_songs
-- Many-to-many join between students and songs.
-- Solo song = 1 row; shared song (unit/duet) = multiple rows.
-- ============================================================
CREATE TABLE `student_songs` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `student_id` INT(11) NOT NULL,
  `song_id`    INT(11) NOT NULL,
  `added_by`   INT(11) DEFAULT NULL,
  `added_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_student_song` (`student_id`, `song_id`),
  KEY `idx_student_songs_song_id` (`song_id`),
  KEY `idx_student_songs_added_by` (`added_by`),
  CONSTRAINT `fk_student_songs_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_songs_song`
    FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_songs_added_by`
    FOREIGN KEY (`added_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: schedules
-- Calendar entries linked to a student.
-- ============================================================
CREATE TABLE `schedules` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT,
  `student_id`    INT(11) NOT NULL,
  `activity_type` VARCHAR(50) NOT NULL,
  `title`         VARCHAR(100) NOT NULL,
  `description`   TEXT DEFAULT NULL,
  `date`          DATE NOT NULL,
  `start_time`    TIME NOT NULL,
  `end_time`      TIME NOT NULL,
  `location`      VARCHAR(100) DEFAULT NULL,
  `created_by`    INT(11) DEFAULT NULL,
  `status`        ENUM('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_schedules_student_id` (`student_id`),
  KEY `idx_schedules_date` (`date`),
  KEY `idx_schedules_student_date` (`student_id`, `date`),
  KEY `idx_schedules_created_by` (`created_by`),
  KEY `idx_schedules_status` (`status`),
  CONSTRAINT `fk_schedules_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_schedules_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: stat_history
-- Append-only log of stat changes.
-- Use ON DELETE SET NULL to preserve history if a student is removed.
-- ============================================================
CREATE TABLE `stat_history` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `student_id`  INT(11) DEFAULT NULL,
  `stat_type`   ENUM('vocal','dance','visual') NOT NULL,
  `old_value`   INT(11) NOT NULL,
  `new_value`   INT(11) NOT NULL,
  `reason`      VARCHAR(200) DEFAULT NULL,
  `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stat_history_student_id` (`student_id`),
  KEY `idx_stat_history_recorded_at` (`recorded_at`),
  CONSTRAINT `fk_stat_history_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: events
-- Auditions, concerts, photoshoots with stat requirements.
-- ============================================================
CREATE TABLE `events` (
  `id`              INT(11) NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(100) NOT NULL,
  `type`            ENUM('audition','concert','photoshoot') NOT NULL,
  `date`            DATETIME NOT NULL,
  `required_vocal`  INT(11) NOT NULL DEFAULT 0,
  `required_dance`  INT(11) NOT NULL DEFAULT 0,
  `required_visual` INT(11) NOT NULL DEFAULT 0,
  `location`        VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_events_date` (`date`),
  KEY `idx_events_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: event_participants
-- Many-to-many join between events and students, with results.
-- ============================================================
CREATE TABLE `event_participants` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `event_id`   INT(11) NOT NULL,
  `student_id` INT(11) NOT NULL,
  `result`     VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_event_student` (`event_id`, `student_id`),
  KEY `idx_event_participants_student_id` (`student_id`),
  CONSTRAINT `fk_event_participants_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_event_participants_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: messages
-- User-to-user messaging with read tracking and soft delete.
-- ============================================================
CREATE TABLE `messages` (
  `id`                     INT(11) NOT NULL AUTO_INCREMENT,
  `sender_id`              INT(11) DEFAULT NULL,
  `receiver_id`            INT(11) DEFAULT NULL,
  `subject`                VARCHAR(200) NOT NULL,
  `body`                   TEXT NOT NULL,
  `is_read`                TINYINT(1) NOT NULL DEFAULT 0,
  `is_deleted_by_sender`   TINYINT(1) NOT NULL DEFAULT 0,
  `is_deleted_by_receiver` TINYINT(1) NOT NULL DEFAULT 0,
  `parent_message_id`      INT(11) DEFAULT NULL,
  `sent_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at`                DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_messages_sender_id` (`sender_id`),
  KEY `idx_messages_receiver_id` (`receiver_id`),
  KEY `idx_messages_receiver_unread` (`receiver_id`, `is_read`, `is_deleted_by_receiver`),
  KEY `idx_messages_parent` (`parent_message_id`),
  KEY `idx_messages_sent_at` (`sent_at`),
  CONSTRAINT `fk_messages_sender`
    FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_receiver`
    FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_messages_parent`
    FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: notifications
-- In-app notification feed (bell icon with unread badge).
-- ============================================================
CREATE TABLE `notifications` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) NOT NULL,
  `type`       VARCHAR(50) NOT NULL,
  `title`      VARCHAR(200) NOT NULL,
  `body`       TEXT DEFAULT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_id` (`user_id`),
  KEY `idx_notifications_user_unread` (`user_id`, `is_read`),
  KEY `idx_notifications_created_at` (`created_at`),
  CONSTRAINT `fk_notifications_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- Table: producer_messages
-- Pre-written personality-based message pool for each idol.
-- ============================================================
CREATE TABLE `producer_messages` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `producer_id`  INT(11) DEFAULT NULL,
  `student_id`   INT(11) DEFAULT NULL,
  `message_type` VARCHAR(50) NOT NULL,
  `tone`         VARCHAR(20) DEFAULT NULL,
  `message_text` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_producer_messages_producer_id` (`producer_id`),
  KEY `idx_producer_messages_student_id` (`student_id`),
  KEY `idx_producer_messages_lookup` (`student_id`, `message_type`),
  CONSTRAINT `fk_producer_messages_producer`
    FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_producer_messages_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- End of schema
-- ============================================================