-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2026 at 11:36 AM
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
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted_by_sender` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted_by_receiver` tinyint(1) NOT NULL DEFAULT 0,
  `parent_message_id` int(11) DEFAULT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
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
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
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
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `title`, `title_jp`, `artist`, `duration`, `release_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Fighting My Way', 'Fighting My Way', 'Saki Hanami', '00:03:21', '2024-05-16', 'Saki Hanami\'s debut solo song. Lyrics: HIROMI. Composition & arrangement: Giga. Performed in 定期公演『初』.', 1, '2026-05-27 13:20:33', '2026-05-27 13:20:33'),
(2, 'Luna say maybe', 'Luna say maybe', 'Temari Tsukimura', '00:04:25', '2024-05-16', 'Temari Tsukimura\'s debut solo song and signature track. Lyrics & composition: Minami (美波). Arrangement: Minami & Katsuhiro Mafune. Performed in the produce scenario \"Teiki Kouen Hatsu\" (定期公演『初』). ', 1, '2026-05-27 13:20:33', '2026-05-27 13:20:33'),
(3, 'Sekai Ichi Kawaii Watashi', '世界一可愛い私', 'Kotone Fujita', '00:03:59', '2024-05-16', 'Kotone Fujita\'s debut solo song. Lyrics, composition, and arrangement all by HoneyWorks — the duo behind the wildly popular \"Kokuhaku Jikkou Iinkai\" (Confession Executive Committee) series. ', 1, '2026-05-27 13:20:33', '2026-05-27 13:20:33');

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
  `blood_type` char(2) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `hometown` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `rank` varchar(10) NOT NULL DEFAULT 'Debut',
  `vocal` int(11) NOT NULL DEFAULT 0,
  `dance` int(11) NOT NULL DEFAULT 0,
  `visual` int(11) NOT NULL DEFAULT 0,
  `bio` text DEFAULT NULL,
  `theme_primary_color` varchar(20) DEFAULT NULL,
  `theme_secondary_color` varchar(20) NOT NULL,
  `producer_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `name`, `name_jp`, `birthday`, `blood_type`, `height`, `hometown`, `school_year`, `rank`, `vocal`, `dance`, `visual`, `bio`, `theme_primary_color`, `theme_secondary_color`, `producer_id`, `created_at`, `updated_at`) VALUES
(1, 5, 'Saki Hanami', '花海 咲季', '2000-04-02', 'A', 152, 'Aichi', 'Class 1-1', 'Debut', 75, 75, 80, 'A new student who topped the entrance exam and a fierce ex-athlete with an unshakeable hatred of losing. A child prodigy since youth—quick to learn, skilled at everything she tries. She\'s extremely close with her younger sister Ume Hanami, but they\'re also lifelong rivals who have competed in every sport imaginable. Saki rates Ume\'\'s talent higher than anyone else\'s and fears it.\r\n', '#E30F25', '#FAD0D4', 1, '2026-05-26 13:13:20', '2026-05-27 15:57:18'),
(2, 6, 'Temari Tsukimura', '月村 手毬', '2000-06-03', 'AB', 162, 'Kyoto', 'Class 1-1', 'Debut', 75, 65, 55, 'A former elite middle-school idol, once called the #1 idol of her grade. Appears cool, stoic, and sarcastic on the surface but is secretly a lazy, spoiled troublemaker—a girl with two sides. She aims for the top to break away from the self she dislikes and learn to love herself. ', '#0C7BBB', '#CEE5F1', 1, '2026-05-26 13:13:20', '2026-05-27 16:43:30'),
(3, 7, 'Kotone Fujita', '藤田 ことね', '2000-04-29', 'O', 156, 'Saitama', 'Class 1-1', 'Debut', 65, 55, 75, 'A greedy girl who dreams of becoming \"an idol who can make money.\" She sees idol work as her one shot at turning her life around. Her grades are poor and her self-esteem is low overall, but she has full confidence in her cute face. She\'s a little uncomfortable around the student council president Sena, who keeps overestimating her for some reason.\r\n', '#F8C112', '#FEF3CF', 1, '2026-05-26 13:13:20', '2026-05-27 16:47:09');

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

--
-- Dumping data for table `student_songs`
--

INSERT INTO `student_songs` (`id`, `student_id`, `song_id`, `added_by`, `added_at`) VALUES
(1, 1, 1, 1, '2026-05-27 13:20:33'),
(2, 2, 2, 1, '2026-05-27 13:20:33'),
(3, 3, 3, 1, '2026-05-27 13:20:33');

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

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `name`, `specialty`) VALUES
(1, 2, 'Vocal Trainer', 'vocal'),
(2, 3, 'Dance Trainer', 'dance'),
(3, 4, 'Visual Trainer', 'visual');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('producer','teacher','student') NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `avatar`, `is_active`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'Producer', '$2y$10$7BFxM6RgeDvBv6kDTKovuuIdwTzQBM4l1sI2EcNoeHa94mU0y/OM2', 'producer', NULL, 1, '2026-05-26 13:10:43', '2026-05-27 11:39:20', NULL),
(2, 'Vocal Trainer', '$2y$10$mfACa/WYxB5KtG.u/sz0NOsEavc0F8j7fR.Zf/PF9hcEl7gdLMNdK', 'teacher', NULL, 1, '2026-05-26 13:11:33', '2026-05-27 11:39:47', NULL),
(3, 'Dance Trainer', '$2y$10$t9sRByIWadz.sKik8/7BCeBHMW8S.QZjUqdFzjr2seuuDd3Qx7dMi', 'teacher', NULL, 1, '2026-05-26 13:11:56', '2026-05-27 11:40:17', NULL),
(4, 'Visual Trainer', '$2y$10$bGNC.jMo6tcO1e0f6A0q5erVt6QZ2LbnR4kS1AD47.vwZW0J3GQV6', 'teacher', NULL, 1, '2026-05-26 13:12:30', '2026-05-27 11:40:41', NULL),
(5, 'Saki Hanami', '$2y$10$ff9abDr59dXAcmMgP6QO3uqs90jpoKPhk3xs8sS5zY3kISRRDVyV6', 'student', 'Saki Hanami.png', 1, '2026-05-26 13:13:13', '2026-05-27 15:49:44', '2026-05-27 15:31:21'),
(6, 'Temari Tsukimura', '$2y$10$iSoM6ouEBxTnTSw1CXHFKe89P9Lat.OcPxFayJwF8Y9xNkFjBLrlm', 'student', 'Temari Tsukimura.png', 1, '2026-05-26 13:13:13', '2026-05-27 16:15:47', '2026-05-27 16:15:47'),
(7, 'Kotone Fujita', '$2y$10$b0uAqh6CXeKP3rxgJbKpC.4W2fcJKUEO/qD19wI7P9zfkRx66NYRC', 'student', 'Kotone Fujita.png', 1, '2026-05-26 13:13:13', '2026-05-27 15:57:51', '2026-05-27 15:57:51');

--
-- Indexes for dumped tables
--

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
  ADD KEY `idx_messages_sender_id` (`sender_id`),
  ADD KEY `idx_messages_receiver_id` (`receiver_id`),
  ADD KEY `idx_messages_receiver_unread` (`receiver_id`,`is_read`,`is_deleted_by_receiver`),
  ADD KEY `idx_messages_parent` (`parent_message_id`),
  ADD KEY `idx_messages_sent_at` (`sent_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_user_unread` (`user_id`,`is_read`),
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
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stat_history`
--
ALTER TABLE `stat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_songs`
--
ALTER TABLE `student_songs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `fk_messages_parent` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
