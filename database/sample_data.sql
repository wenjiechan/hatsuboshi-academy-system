-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 11:59 AM
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

--
-- Dumping data for table `daily_student_stats`
--

INSERT INTO `daily_student_stats` (`id`, `student_id`, `stat_date`, `vocal`, `dance`, `visual`, `created_at`, `updated_at`) VALUES
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 14:59:21', '2026-05-28 14:59:21'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:04:16', '2026-05-28 15:04:16'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:05:12', '2026-05-28 15:05:12'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:05:14', '2026-05-28 15:05:14'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:05:15', '2026-05-28 15:05:15'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:05:48', '2026-05-28 15:05:48'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:06:28', '2026-05-28 15:06:28'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:06:29', '2026-05-28 15:06:29'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 15:07:22', '2026-05-28 15:07:22'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:09:08', '2026-05-28 15:09:08'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:09:22', '2026-05-28 15:09:22'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:19:32', '2026-05-28 15:19:32'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:28:09', '2026-05-28 15:28:09'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:28:18', '2026-05-28 15:28:18'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:39:41', '2026-05-28 15:39:41'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:42:39', '2026-05-28 15:42:39'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:44:30', '2026-05-28 15:44:30'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:49', '2026-05-28 15:53:49'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:51', '2026-05-28 15:53:51'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:52', '2026-05-28 15:53:52'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:55', '2026-05-28 15:53:55'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:57', '2026-05-28 15:53:57'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:58', '2026-05-28 15:53:58'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 15:53:59', '2026-05-28 15:53:59'),
(0, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:58:24', '2026-05-28 15:58:24'),
(0, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:59:14', '2026-05-28 15:59:14'),
(0, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:59:16', '2026-05-28 15:59:16'),
(0, 3, '2026-05-28', 65, 55, 75, '2026-05-28 15:59:17', '2026-05-28 15:59:17'),
(0, 3, '2026-05-28', 65, 55, 75, '2026-05-28 16:00:17', '2026-05-28 16:00:17'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 16:00:32', '2026-05-28 16:00:32'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 16:01:07', '2026-05-28 16:01:07'),
(0, 2, '2026-05-28', 75, 65, 55, '2026-05-28 16:20:48', '2026-05-28 16:20:48'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 16:42:44', '2026-05-28 16:42:44'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 17:26:13', '2026-05-28 17:26:13'),
(0, 1, '2026-05-28', 75, 75, 80, '2026-05-28 17:34:26', '2026-05-28 17:34:26'),
(0, 4, '2026-05-28', 55, 55, 65, '2026-05-28 17:52:10', '2026-05-28 17:52:10'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:52:58', '2026-05-28 17:52:58'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:54:17', '2026-05-28 17:54:17'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:54:39', '2026-05-28 17:54:39'),
(0, 4, '2026-05-28', 55, 55, 65, '2026-05-28 17:56:18', '2026-05-28 17:56:18'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:57:19', '2026-05-28 17:57:19'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:57:40', '2026-05-28 17:57:40'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:57:52', '2026-05-28 17:57:52'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:36', '2026-05-28 17:58:36'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:37', '2026-05-28 17:58:37'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:38', '2026-05-28 17:58:38'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:40', '2026-05-28 17:58:40'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:41', '2026-05-28 17:58:41'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:42', '2026-05-28 17:58:42'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:44', '2026-05-28 17:58:44'),
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:54', '2026-05-28 17:58:54');

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

--
-- Dumping data for table `producer_messages`
--

INSERT INTO `producer_messages` (`id`, `producer_id`, `student_id`, `message_type`, `tone`, `message_text`) VALUES
(1, 1, 1, 'morning', 'firm', 'Morning, Saki. I know you already finished your 5km run. Save some energy for the lessons.'),
(2, 1, 1, 'morning', 'warm', 'Good morning. Up before sunrise again? Of course you were. Let\'s make today count.'),
(3, 1, 1, 'morning', 'playful', 'Morning, champ. Try not to lap the other students before first period this time.'),
(4, 1, 1, 'afternoon', 'warm', 'Halfway through the day and still going strong. Hydrate before dance practice.'),
(5, 1, 1, 'afternoon', 'firm', 'Afternoon. Don\'t let the energy drop now — finish the day as hard as you started it.'),
(6, 1, 1, 'afternoon', 'gentle', 'Quick check-in. You\'ve done plenty already today. Pace yourself through the rest.'),
(7, 1, 1, 'evening', 'gentle', 'Long day. You pushed hard — now actually let yourself wind down tonight.'),
(8, 1, 1, 'evening', 'warm', 'Good work today, Saki. Stretch, eat properly, and get real sleep. You earned it.'),
(9, 1, 1, 'evening', 'firm', 'It\'s evening. Training\'s over for the day. No \"one more set\" — go rest.'),
(10, 1, 1, 'low_vocal', 'serious', 'Your vocals slipped a little today. Breath control drills tomorrow — you\'ll close the gap fast.'),
(11, 1, 1, 'low_vocal', 'warm', 'Vocals weren\'t your best today, but you\'ve fixed bigger gaps than this. We\'ll work it.'),
(12, 1, 1, 'low_vocal', 'firm', 'The singing was off. Don\'t spiral over it — just channel that frustration into practice.'),
(13, 1, 1, 'low_dance', 'firm', 'Dance was below your usual standard. Unusual for you. Rest up, then we hit it again.'),
(14, 1, 1, 'low_dance', 'serious', 'Your footwork was tired today. That tells me you\'re overtraining. Ease off, then rebuild.'),
(15, 1, 1, 'low_dance', 'gentle', 'Even you have off days on the floor. One rough session doesn\'t erase all your work.'),
(16, 1, 1, 'low_visual', 'warm', 'Visual\'s your weaker area, and that\'s fine. We\'ll work the expressions until they\'re natural.'),
(17, 1, 1, 'low_visual', 'gentle', 'Posing felt stiff today. Relax your shoulders — you\'re competing, not bracing for impact.'),
(18, 1, 1, 'low_visual', 'serious', 'Visual scores need attention. Let\'s schedule extra camera time — you close gaps fast when focused.'),
(19, 1, 1, 'audition_day', 'firm', 'Audition day. You\'ve trained harder than anyone. Don\'t overthink — just show them what you\'ve got.'),
(20, 1, 1, 'audition_day', 'warm', 'Today\'s the stage. All those early runs and late practices? This is where they pay off.'),
(21, 1, 1, 'audition_day', 'serious', 'Audition day, Saki. Win or lose, leave nothing on the table. That\'s all I ask.'),
(22, 1, 1, 'rest_day', 'firm', 'It\'s a rest day. That means REST, Saki. Put the running shoes down. That\'s an order.'),
(23, 1, 1, 'rest_day', 'gentle', 'Take today off properly. Recovery is when the body actually gets stronger — trust me on this.'),
(24, 1, 1, 'rest_day', 'playful', 'Rest day. I\'ve hidden your running shoes. Don\'t bother looking — go relax instead.'),
(25, 1, 1, 'good_progress', 'warm', 'Your numbers are climbing every week. The discipline is paying off — I\'m proud of you.'),
(26, 1, 1, 'good_progress', 'firm', 'Solid progress. This is what consistency looks like. Keep the momentum, don\'t coast.'),
(27, 1, 1, 'good_progress', 'gentle', 'You\'re growing into your own idol, not anyone\'s rival. That\'s the best progress of all.'),
(28, 1, 2, 'morning', 'playful', 'Good morning. You actually woke up on time today — small miracles. Breakfast is the healthy one.'),
(29, 1, 2, 'morning', 'gentle', 'Morning, Temari. Take it slow if you need to, just don\'t fall back asleep on me.'),
(30, 1, 2, 'morning', 'warm', 'Up and ready? Good. The cool, composed Temari the fans love starts the moment you step out.'),
(31, 1, 2, 'afternoon', 'gentle', 'Afternoon check-in. Stay focused through the next lesson, then you can have your break.'),
(32, 1, 2, 'afternoon', 'playful', 'Halfway done. Yes, there\'s a snack break later. No, it\'s not cake. We discussed this.'),
(33, 1, 2, 'afternoon', 'warm', 'You\'re doing fine today. Push through the afternoon and the evening is yours.'),
(34, 1, 2, 'evening', 'warm', 'You did well today, even if you\'d never admit it out loud. Get some real sleep tonight.'),
(35, 1, 2, 'evening', 'gentle', 'Evening. The act can come off now — relax, you worked harder than you let on.'),
(36, 1, 2, 'evening', 'playful', 'Day\'s done. One small sweet won\'t hurt. ONE, Temari. I\'m watching.'),
(37, 1, 2, 'low_vocal', 'gentle', 'Vocals dipped a bit today — surprising, since it\'s your strength. Just an off day, nothing more.'),
(38, 1, 2, 'low_vocal', 'serious', 'Even your specialty needs upkeep. Don\'t coast on talent — a little practice keeps it sharp.'),
(39, 1, 2, 'low_vocal', 'warm', 'Not your sharpest singing today, but your baseline is still higher than most. Rest and reset.'),
(40, 1, 2, 'low_dance', 'serious', 'Dance needs more from you. I know the pace bores you, but the effort will show on stage.'),
(41, 1, 2, 'low_dance', 'firm', 'You held back in dance again. I can tell the difference between tired and uninterested.'),
(42, 1, 2, 'low_dance', 'playful', 'Your dance today had the energy of a nap. Let\'s find some actual enthusiasm tomorrow, hm?'),
(43, 1, 2, 'low_visual', 'playful', 'Visual scores are sleepy today, like someone I know. Let\'s wake those poses up tomorrow.'),
(44, 1, 2, 'low_visual', 'gentle', 'Your expressions read a little flat today. A bit more feeling and they\'ll land beautifully.'),
(45, 1, 2, 'low_visual', 'serious', 'Visual could use work. You have the look — now give the camera something behind the eyes.'),
(46, 1, 2, 'audition_day', 'firm', 'Audition day. Drop the cool act for the judges and actually let them see how good you are.'),
(47, 1, 2, 'audition_day', 'warm', 'Today\'s your stage. You\'ve done this before as the No.1 — that idol is still in there. Show them.'),
(48, 1, 2, 'audition_day', 'gentle', 'Audition day. No pressure act needed. Just be the performer I already know you are.'),
(49, 1, 2, 'rest_day', 'gentle', 'Rest day — your favorite. Enjoy it. You\'ve earned the downtime, sweets included. In moderation.'),
(50, 1, 2, 'rest_day', 'playful', 'Rest day. I know you\'ll spend it napping. At least nap somewhere comfortable this time.'),
(51, 1, 2, 'rest_day', 'warm', 'Take the day fully off. Recharge properly — you\'re better when you\'re actually rested.'),
(52, 1, 2, 'good_progress', 'warm', 'You\'re improving steadily, and the talent\'s finally meeting the effort. Keep this up.'),
(53, 1, 2, 'good_progress', 'playful', 'Look at you, working hard AND it\'s showing. Who is this person? I like her.'),
(54, 1, 2, 'good_progress', 'gentle', 'Real progress this week. See what happens when you let yourself try? Proud of you.'),
(55, 1, 3, 'morning', 'warm', 'Morning, Kotone. Big day ahead. Don\'t skip breakfast just to make an early shift.'),
(56, 1, 3, 'morning', 'playful', 'Good morning, hardest worker in the academy. Lessons first, money later. Deal?'),
(57, 1, 3, 'morning', 'gentle', 'Morning. You looked tired yesterday — please tell me you slept and didn\'t take a night shift.'),
(58, 1, 3, 'afternoon', 'playful', 'Afternoon already. Lesson first, then your shift — and yes, payday is on Friday.'),
(59, 1, 3, 'afternoon', 'warm', 'Doing great so far today. Eat something before you head off to work, alright?'),
(60, 1, 3, 'afternoon', 'firm', 'Afternoon. Finish the lesson properly before you start counting tonight\'s tips in your head.'),
(61, 1, 3, 'evening', 'gentle', 'You worked a full day AND a shift. That\'s enough. Go home and rest, please.'),
(62, 1, 3, 'evening', 'warm', 'Long day, hard worker. You did well. Now actually stop and take care of yourself tonight.'),
(63, 1, 3, 'evening', 'firm', 'It\'s evening. No picking up \"just one more\" shift. Rest is part of the investment too.'),
(64, 1, 3, 'low_vocal', 'warm', 'Vocals were a touch flat today. You\'re tired from overworking — a little rest fixes this.'),
(65, 1, 3, 'low_vocal', 'serious', 'Your singing slipped. I think the shifts are catching up to you. Something has to give.'),
(66, 1, 3, 'low_vocal', 'gentle', 'Not your best vocals today, but nothing sleep can\'t fix. Be kinder to yourself, okay?'),
(67, 1, 3, 'low_dance', 'serious', 'Dance is your strong stat, so today\'s dip tells me you\'re exhausted. Let\'s cut a shift this week.'),
(68, 1, 3, 'low_dance', 'firm', 'Your dance was off, and that\'s rare for you. The overwork is the problem. We\'re fixing your schedule.'),
(69, 1, 3, 'low_dance', 'warm', 'Even your best stat had an off day. You\'re running on empty — let me help lighten the load.'),
(70, 1, 3, 'low_visual', 'playful', 'Visual scores low? Impossible with that face. Just looked tired — get some sleep, superstar.'),
(71, 1, 3, 'low_visual', 'gentle', 'Your expressions were a bit dim today. That\'s exhaustion, not ability. Rest and you\'ll glow again.'),
(72, 1, 3, 'low_visual', 'serious', 'Visual dipped today. You rely on it for paid work, so let\'s protect it — that means resting.'),
(73, 1, 3, 'audition_day', 'firm', 'Audition day. This is the stage that actually pays off. Show them the confidence I know you have.'),
(74, 1, 3, 'audition_day', 'warm', 'Today\'s the big one. Forget the side jobs for a few hours — THIS is the real investment.'),
(75, 1, 3, 'audition_day', 'playful', 'Audition day. Go dazzle them. Think of the future endorsement money, if that helps motivate you.'),
(76, 1, 3, 'rest_day', 'firm', 'Rest day means no shifts. I checked the schedule — I know you tried to sneak one in. Rest.'),
(77, 1, 3, 'rest_day', 'gentle', 'Please take today off completely. No work. Your health is worth more than any paycheck.'),
(78, 1, 3, 'rest_day', 'warm', 'Day off, Kotone. Treat yourself to something nice with what you\'ve saved. You deserve it.'),
(79, 1, 3, 'good_progress', 'warm', 'Your progress is fantastic, and yes — there\'s a reward for it. You earned every bit of it.'),
(80, 1, 3, 'good_progress', 'playful', 'Numbers are up across the board. At this rate the sponsors will be fighting over you. Love it.'),
(81, 1, 3, 'good_progress', 'gentle', 'You\'re growing so much. Whatever goal that money is for, you\'re getting closer every week.');

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

--
-- Dumping data for table `recurring_schedules`
--

INSERT INTO `recurring_schedules` (`id`, `student_id`, `weekday`, `activity_type`, `title`, `description`, `start_time`, `end_time`, `location`, `created_by`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '10:00:00', '12:00:00', 'Vocal Studio A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(2, 2, 1, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '10:00:00', '12:00:00', 'Vocal Studio A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(3, 3, 1, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '10:00:00', '12:00:00', 'Vocal Studio A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(4, 1, 1, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '14:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(5, 2, 1, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '14:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(6, 3, 1, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '14:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(7, 1, 2, 'class', 'Academic Class', 'Regular shared school lessons.', '09:00:00', '12:00:00', 'Classroom 1-A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(8, 2, 2, 'class', 'Academic Class', 'Regular shared school lessons.', '09:00:00', '12:00:00', 'Classroom 1-A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(9, 3, 2, 'class', 'Academic Class', 'Regular shared school lessons.', '09:00:00', '12:00:00', 'Classroom 1-A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(10, 1, 2, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '14:00:00', '16:00:00', 'Visual Studio', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(11, 2, 2, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '14:00:00', '16:00:00', 'Visual Studio', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(12, 3, 2, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '14:00:00', '16:00:00', 'Visual Studio', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(13, 1, 3, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '10:00:00', '12:00:00', 'Dance Studio 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(14, 2, 3, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '10:00:00', '12:00:00', 'Dance Studio 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(15, 3, 3, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '10:00:00', '12:00:00', 'Dance Studio 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(16, 1, 4, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '14:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(17, 2, 4, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '14:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(18, 3, 4, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '14:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(19, 1, 5, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '10:00:00', '12:00:00', 'Visual Studio', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(20, 2, 5, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '10:00:00', '12:00:00', 'Visual Studio', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(21, 3, 5, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '10:00:00', '12:00:00', 'Visual Studio', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(22, 1, 5, 'class', 'Academic Class', 'Regular shared school lessons.', '13:00:00', '15:00:00', 'Classroom 1-A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(23, 2, 5, 'class', 'Academic Class', 'Regular shared school lessons.', '13:00:00', '15:00:00', 'Classroom 1-A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(24, 3, 5, 'class', 'Academic Class', 'Regular shared school lessons.', '13:00:00', '15:00:00', 'Classroom 1-A', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(25, 1, 1, 'training', 'Morning Run', 'Daily 5km run. Never skipped.', '06:00:00', '07:00:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(26, 1, 1, 'training', 'Evening Conditioning', 'Stamina cooldown after dance.', '16:30:00', '17:30:00', 'Gym', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(27, 1, 2, 'training', 'Morning Run', 'Daily 5km run.', '06:00:00', '07:00:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(28, 1, 2, 'training', 'Strength Training', 'Personal core/strength workout.', '16:30:00', '18:00:00', 'Gym', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(29, 1, 3, 'training', 'Morning Run', 'Daily 5km run.', '06:00:00', '07:00:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(30, 1, 3, 'vocal', 'Solo Vocal Practice', 'Breath control and projection drills.', '14:00:00', '15:30:00', 'Practice Room 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(31, 1, 3, 'training', 'Strength Training', 'Evening lifting session.', '16:00:00', '17:30:00', 'Gym', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(32, 1, 4, 'training', 'Morning Run', 'Daily 5km run.', '06:00:00', '07:00:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(33, 1, 4, 'visual', 'Solo Visual Practice', 'Camera-facing posing practice.', '10:00:00', '12:00:00', 'Practice Room 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(34, 1, 4, 'training', 'Evening Conditioning', 'Sprint intervals.', '16:30:00', '17:30:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(35, 1, 5, 'training', 'Morning Run', 'Daily 5km run.', '06:00:00', '07:00:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(36, 1, 5, 'training', 'Sister Sparring', 'Competition with Ume Hanami. Never actually friendly.', '15:30:00', '17:30:00', 'Multipurpose Hall', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(37, 1, 6, 'training', 'Morning Run', 'Daily 5km run.', '06:00:00', '07:00:00', 'Academy Grounds', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(38, 1, 6, 'audition', 'Audition Prep', 'Self-organized mock audition.', '10:00:00', '13:00:00', 'Audition Hall', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(39, 1, 6, 'dance', 'Solo Dance Practice', 'Extra routine drilling.', '14:30:00', '16:00:00', 'Practice Room 1', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(40, 1, 7, 'rest', 'Rest Day', 'Mandatory rest. Will probably still run.', '00:00:00', '23:59:59', '-', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(41, 2, 1, 'training', 'Diet Management', 'Producer-supervised meal plan.', '12:00:00', '12:45:00', 'Cafeteria', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(42, 2, 1, 'vocal', 'Solo Vocal Practice', 'Polished technical session. Her speciality.', '16:30:00', '17:30:00', 'Practice Room 2', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(43, 2, 2, 'training', 'Diet Management', 'Supervised lunch. No sweets smuggled in.', '12:00:00', '12:45:00', 'Cafeteria', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(44, 2, 2, 'rest', 'Personal Time', 'Producer-scheduled downtime. Naps suspected.', '16:30:00', '18:00:00', 'Dormitory', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(45, 2, 3, 'vocal', 'Solo Vocal Practice', 'Recording-style practice.', '13:00:00', '14:30:00', 'Practice Room 2', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(46, 2, 3, 'training', 'Diet Management', 'Supervised lunch.', '15:00:00', '15:45:00', 'Cafeteria', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(47, 2, 4, 'class', 'Self-Study', 'Quiet academic review. Surprisingly diligent.', '09:30:00', '11:30:00', 'Library', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(48, 2, 4, 'training', 'Diet Management', 'Supervised lunch.', '12:00:00', '12:45:00', 'Cafeteria', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(49, 2, 4, 'rest', 'Personal Time', 'Producer-scheduled downtime.', '16:30:00', '18:00:00', 'Dormitory', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(50, 2, 5, 'dance', 'Solo Dance Practice', 'Light choreography refinement.', '15:30:00', '16:30:00', 'Practice Room 2', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(51, 2, 6, 'training', 'Diet Management', 'Weekend supervised brunch.', '11:00:00', '11:45:00', 'Cafeteria', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(52, 2, 6, 'photoshoot', 'Photoshoot', 'Weekly magazine/promo photoshoot.', '13:00:00', '16:00:00', 'Studio C', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(53, 2, 7, 'rest', 'Rest Day', 'Full rest day. Earned, finally.', '00:00:00', '23:59:59', '-', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(54, 3, 1, 'visual', 'Solo Visual Practice', 'Makeup & expression work. Her favorite.', '08:30:00', '09:45:00', 'Practice Room 3', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(55, 3, 1, 'work', 'Part-Time Job', 'Cafe shift. Refuses to drop it.', '17:00:00', '21:00:00', 'Off-campus', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(56, 3, 2, 'work', 'Part-Time Job', 'Convenience store shift.', '17:00:00', '22:00:00', 'Off-campus', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(57, 3, 3, 'visual', 'Solo Visual Practice', 'Magazine cover-shoot prep.', '13:00:00', '14:00:00', 'Practice Room 3', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(58, 3, 3, 'photoshoot', 'Photoshoot', 'Sponsored shoot. Pays well — never says no.', '14:30:00', '17:00:00', 'Studio C', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(59, 3, 3, 'work', 'Part-Time Job', 'Evening cafe shift.', '18:00:00', '21:00:00', 'Off-campus', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(60, 3, 4, 'dance', 'Solo Dance Practice', 'Cute & confident routine work.', '09:30:00', '11:30:00', 'Practice Room 3', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(61, 3, 4, 'work', 'Part-Time Job', 'Cafe shift.', '17:00:00', '21:00:00', 'Off-campus', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(62, 3, 5, 'vocal', 'Solo Vocal Practice', 'Solo song practice.', '15:30:00', '16:30:00', 'Practice Room 3', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(63, 3, 5, 'work', 'Part-Time Job', 'Weekend prep shift. Producer ignores the overwork.', '17:00:00', '22:00:00', 'Off-campus', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(64, 3, 6, 'photoshoot', 'Photoshoot', 'Sponsor photoshoot. Bonus pay = great mood.', '11:00:00', '15:00:00', 'Studio C', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(65, 3, 6, 'dance', 'Solo Dance Practice', 'Extra optional dance practice. She insists.', '16:00:00', '17:30:00', 'Practice Room 3', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(66, 3, 6, 'work', 'Part-Time Job', 'Evening shift.', '18:30:00', '22:00:00', 'Off-campus', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(67, 3, 7, 'rest', 'Rest Day', 'Producer-enforced rest.', '00:00:00', '23:59:59', '-', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05');

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
(3, 7, 'Kotone Fujita', '藤田 ことね', '2000-04-29', 'O', 156, 'Saitama', 'Class 1-1', 'Debut', 65, 55, 75, 'A greedy girl who dreams of becoming \"an idol who can make money.\" She sees idol work as her one shot at turning her life around. Her grades are poor and her self-esteem is low overall, but she has full confidence in her cute face. She\'s a little uncomfortable around the student council president Sena, who keeps overestimating her for some reason.\r\n', '#F8C112', '#FEF3CF', 1, '2026-05-26 13:13:20', '2026-05-27 16:47:09'),
(4, 8, 'Lilja Katsuragi', '葛城 リーリヤ', '2000-07-24', 'B', 161, 'Sweden', 'Class 1-1', 'Debut', 55, 55, 65, 'An exchange student from overseas with no prior singing or dancing experience, who is always shrinking back as if she had no confidence. She doesn\'t look talented at first glance, but her admiration for idols is genuine, and she is an incredibly hard worker. Her father is Japanese, so she speaks the language fluently. She has a promise with her best friend Sumika to stand on the same stage together one day—the reason she enrolled at Hatsuboshi Academy. ', '#7DC4D4', '#EAFDFF', 1, '2026-05-28 17:43:41', '2026-05-28 17:56:05'),
(5, 9, 'Sumika Shiun', '紫雲 清夏', '2000-11-11', 'O', 168, 'Hokkaido', 'Class 1-1', 'Debut', 65, 55, 65, 'A laid-back gyaru who skips class and lessons—a bit of an unserious slacker, but genuinely bright, cheerful, and able to get along with anyone, which is her real charm. She once had a celebrated ballet career and was expected to go far on the world stage, but she has no motivation for it anymore. She wholeheartedly cheers on her best friend Lilja, who is earnestly chasing her idol dream. ', '#7CFC00', '#D6FFB3', 1, '2026-05-28 17:43:41', '2026-05-28 17:58:33');

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
(1, 'Producer', '$2y$10$7BFxM6RgeDvBv6kDTKovuuIdwTzQBM4l1sI2EcNoeHa94mU0y/OM2', 'producer', NULL, 1, '2026-05-26 13:10:43', '2026-05-28 16:41:57', '2026-05-28 16:41:57'),
(2, 'Vocal Trainer', '$2y$10$mfACa/WYxB5KtG.u/sz0NOsEavc0F8j7fR.Zf/PF9hcEl7gdLMNdK', 'teacher', NULL, 1, '2026-05-26 13:11:33', '2026-05-27 11:39:47', NULL),
(3, 'Dance Trainer', '$2y$10$t9sRByIWadz.sKik8/7BCeBHMW8S.QZjUqdFzjr2seuuDd3Qx7dMi', 'teacher', NULL, 1, '2026-05-26 13:11:56', '2026-05-28 16:42:24', '2026-05-28 16:42:24'),
(4, 'Visual Trainer', '$2y$10$bGNC.jMo6tcO1e0f6A0q5erVt6QZ2LbnR4kS1AD47.vwZW0J3GQV6', 'teacher', NULL, 1, '2026-05-26 13:12:30', '2026-05-27 11:40:41', NULL),
(5, 'Saki Hanami', '$2y$10$ff9abDr59dXAcmMgP6QO3uqs90jpoKPhk3xs8sS5zY3kISRRDVyV6', 'student', 'Saki Hanami.png', 1, '2026-05-26 13:13:13', '2026-05-28 16:42:44', '2026-05-28 16:42:44'),
(6, 'Temari Tsukimura', '$2y$10$iSoM6ouEBxTnTSw1CXHFKe89P9Lat.OcPxFayJwF8Y9xNkFjBLrlm', 'student', 'Temari Tsukimura.png', 1, '2026-05-26 13:13:13', '2026-05-28 16:01:07', '2026-05-28 16:01:07'),
(7, 'Kotone Fujita', '$2y$10$b0uAqh6CXeKP3rxgJbKpC.4W2fcJKUEO/qD19wI7P9zfkRx66NYRC', 'student', 'Kotone Fujita.png', 1, '2026-05-26 13:13:13', '2026-05-28 15:58:24', '2026-05-28 15:58:24'),
(8, 'Lilja Katsuragi', '$2y$10$U0wQB4kmtvPYWWQX.9oi0OJ60HDhnd/yf.tuf71exJ8MoG8xS0FVa', 'student', 'Lilja Katsuragi.png', 1, '2026-05-26 13:13:13', '2026-05-28 17:56:18', '2026-05-28 17:56:18'),
(9, 'Sumika Shiun', '$2y$10$XWAu/FVsR0m4XnA9srsx7ev50L3zAlz0qQViMwbhYzSDkRh0KF6cO', 'student', 'Sumika Shiun.png', 1, '2026-05-26 13:13:13', '2026-05-28 17:58:54', '2026-05-28 17:58:54');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `recurring_schedules`
--
ALTER TABLE `recurring_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
