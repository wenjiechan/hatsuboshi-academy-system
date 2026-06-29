-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 11:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gakumas_sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `conversation_type` enum('direct','system') NOT NULL DEFAULT 'direct',
  `direct_key` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read_at` datetime DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_muted` tinyint(1) NOT NULL DEFAULT 0,
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_student_stats`
--

CREATE TABLE `daily_student_stats` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `vocal` int(11) NOT NULL,
  `dance` int(11) NOT NULL,
  `visual` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('audition','concert','photoshoot') NOT NULL,
  `date` datetime NOT NULL,
  `required_vocal` int(11) NOT NULL DEFAULT 0,
  `required_dance` int(11) NOT NULL DEFAULT 0,
  `required_visual` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `result` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('vocal','dance','visual') NOT NULL,
  `stat_gain` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message_type` enum('text','birthday','producer_add_request','producer_remove_request','system') NOT NULL DEFAULT 'text',
  `body` text NOT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `dedupe_key` varchar(190) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `dedupe_key` varchar(190) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `producer_messages`
--

CREATE TABLE `producer_messages` (
  `id` int(11) NOT NULL,
  `producer_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `message_type` varchar(50) NOT NULL,
  `tone` varchar(20) DEFAULT NULL,
  `message_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `producer_student_requests`
--

CREATE TABLE `producer_student_requests` (
  `id` int(11) NOT NULL,
  `producer_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `request_type` enum('add','remove') NOT NULL,
  `status` enum('pending','accepted','rejected','cancelled','expired') NOT NULL DEFAULT 'pending',
  `request_message_id` int(11) DEFAULT NULL,
  `response_message_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recurring_schedules`
--

CREATE TABLE `recurring_schedules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `songs`
--

CREATE TABLE `songs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `title_jp` varchar(150) DEFAULT NULL,
  `artist` varchar(150) DEFAULT NULL,
  `duration` time DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `song_type` enum('Solo','Group','Remix','Cover') NOT NULL DEFAULT 'Group',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stat_history`
--

CREATE TABLE `stat_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `stat_type` enum('vocal','dance','visual') NOT NULL,
  `old_value` int(11) NOT NULL,
  `new_value` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_jp` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `zodiac` varchar(30) DEFAULT NULL,
  `blood_type` char(2) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `three_size` varchar(20) DEFAULT NULL,
  `hometown` varchar(50) DEFAULT NULL,
  `hobbies` text DEFAULT NULL,
  `special_skill` text DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `vocal` int(11) NOT NULL DEFAULT 0,
  `dance` int(11) NOT NULL DEFAULT 0,
  `visual` int(11) NOT NULL DEFAULT 0,
  `rank` varchar(10) GENERATED ALWAYS AS (
    CASE
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 35000 THEN 'S5'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 26000 THEN 'S4'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 23000 THEN 'SSS+'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 20000 THEN 'SSS'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 16000 THEN 'SS'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 14500 THEN 'S+'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 13000 THEN 'S'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 12000 THEN 'A+'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 11000 THEN 'A'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 10000 THEN 'B+'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 9000 THEN 'B'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 7500 THEN 'C+'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 6500 THEN 'C'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 5000 THEN 'D'
      WHEN (`vocal` + `dance` + `visual`) * 2.3 >= 3000 THEN 'E'
      ELSE 'Debut'
    END
  ) STORED,
  `bio` text DEFAULT NULL,
  `producer_id` int(11) DEFAULT NULL,
  `producer_status` enum('active','removal_pending','released','unassigned') DEFAULT 'unassigned',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_songs`
--

CREATE TABLE `student_songs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `song_id` int(11) NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialty` enum('vocal','dance','visual') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','producer','teacher','student') NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `theme_primary_color` varchar(20) DEFAULT '#FF6B9D',
  `theme_secondary_color` varchar(20) DEFAULT '#FFB3D1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_conversations_direct_key` (`direct_key`),
  ADD KEY `idx_conversations_updated_at` (`updated_at`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `idx_participants_user` (`user_id`),
  ADD KEY `idx_participants_user_archived` (`user_id`,`is_archived`),
  ADD KEY `idx_participants_last_read` (`last_read_at`);

--
-- Indexes for table `daily_student_stats`
--
ALTER TABLE `daily_student_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_daily_student_stats_student_date` (`student_id`,`stat_date`),
  ADD KEY `idx_daily_student_stats_date` (`stat_date`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_events_date` (`date`),
  ADD KEY `idx_events_type` (`type`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_event_student` (`event_id`,`student_id`),
  ADD KEY `idx_event_participants_student_id` (`student_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lessons_type` (`type`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_chat_messages_dedupe` (`dedupe_key`),
  ADD KEY `idx_chat_messages_conversation` (`conversation_id`,`created_at`),
  ADD KEY `idx_chat_messages_sender` (`sender_id`),
  ADD KEY `idx_chat_messages_related` (`related_type`,`related_id`),
  ADD KEY `idx_chat_messages_created` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_user_unread` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_related` (`related_type`,`related_id`),
  ADD KEY `idx_notifications_dedupe_key` (`dedupe_key`),
  ADD KEY `idx_notifications_read_at` (`read_at`),
  ADD KEY `idx_notifications_created_at` (`created_at`);

--
-- Indexes for table `producer_messages`
--
ALTER TABLE `producer_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producer_messages_producer_id` (`producer_id`),
  ADD KEY `idx_producer_messages_student_id` (`student_id`),
  ADD KEY `idx_producer_messages_lookup` (`student_id`,`message_type`);

--
-- Indexes for table `producer_student_requests`
--
ALTER TABLE `producer_student_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_psr_producer_id` (`producer_id`),
  ADD KEY `idx_psr_student_id` (`student_id`),
  ADD KEY `idx_psr_status` (`status`),
  ADD KEY `idx_psr_request_message` (`request_message_id`),
  ADD KEY `idx_psr_response_message` (`response_message_id`);

--
-- Indexes for table `recurring_schedules`
--
ALTER TABLE `recurring_schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedules_student_id` (`student_id`),
  ADD KEY `idx_schedules_date` (`date`),
  ADD KEY `idx_schedules_student_date` (`student_id`,`date`),
  ADD KEY `idx_schedules_created_by` (`created_by`),
  ADD KEY `idx_schedules_status` (`status`);

--
-- Indexes for table `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_songs_title` (`title`),
  ADD KEY `idx_songs_release_date` (`release_date`),
  ADD KEY `idx_songs_created_by` (`created_by`);

--
-- Indexes for table `stat_history`
--
ALTER TABLE `stat_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stat_history_student_id` (`student_id`),
  ADD KEY `idx_stat_history_recorded_at` (`recorded_at`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_students_user_id` (`user_id`),
  ADD KEY `idx_students_producer_id` (`producer_id`),
  ADD KEY `idx_students_rank` (`rank`);

--
-- Indexes for table `student_songs`
--
ALTER TABLE `student_songs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_song` (`student_id`,`song_id`),
  ADD KEY `idx_student_songs_song_id` (`song_id`),
  ADD KEY `idx_student_songs_added_by` (`added_by`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_teachers_user_id` (`user_id`),
  ADD KEY `idx_teachers_specialty` (`specialty`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_users_username` (`username`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_student_stats`
--
ALTER TABLE `daily_student_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `producer_messages`
--
ALTER TABLE `producer_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `producer_student_requests`
--
ALTER TABLE `producer_student_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recurring_schedules`
--
ALTER TABLE `recurring_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stat_history`
--
ALTER TABLE `stat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_songs`
--
ALTER TABLE `student_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `fk_participants_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `fk_event_participants_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_participants_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_chat_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `producer_messages`
--
ALTER TABLE `producer_messages`
  ADD CONSTRAINT `fk_producer_messages_producer` FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_producer_messages_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `producer_student_requests`
--
ALTER TABLE `producer_student_requests`
  ADD CONSTRAINT `fk_psr_producer` FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_psr_request_message` FOREIGN KEY (`request_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_psr_response_message` FOREIGN KEY (`response_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_psr_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_schedules_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_schedules_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `songs`
--
ALTER TABLE `songs`
  ADD CONSTRAINT `fk_songs_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `stat_history`
--
ALTER TABLE `stat_history`
  ADD CONSTRAINT `fk_stat_history_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_producer` FOREIGN KEY (`producer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_songs`
--
ALTER TABLE `student_songs`
  ADD CONSTRAINT `fk_student_songs_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_songs_song` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_student_songs_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
