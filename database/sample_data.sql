-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 04:42 AM
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
(0, 5, '2026-05-28', 65, 55, 65, '2026-05-28 17:58:54', '2026-05-28 17:58:54'),
(0, 4, '2026-05-29', 55, 55, 65, '2026-05-29 09:11:38', '2026-05-29 09:11:38'),
(0, 5, '2026-05-29', 65, 55, 65, '2026-05-29 09:12:01', '2026-05-29 09:12:01');

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
(81, 1, 3, 'good_progress', 'gentle', 'You\'re growing so much. Whatever goal that money is for, you\'re getting closer every week.'),
(82, 1, 4, 'morning', 'gentle', 'Good morning, Lilja. Take your time waking up — there\'s no rush. I\'m here when you\'re ready.'),
(83, 1, 4, 'morning', 'warm', 'Morning. You looked nervous yesterday, so today let\'s start small and easy together.'),
(84, 1, 4, 'morning', 'gentle', 'Hyvää huomenta, Lilja. Whichever language wakes you up gentler — both work for me.'),
(85, 1, 4, 'afternoon', 'warm', 'Afternoon check-in. You\'ve done well so far. Breathe — the hard part is already behind you.'),
(86, 1, 4, 'afternoon', 'gentle', 'How are you holding up? If anything\'s overwhelming you, please tell me. We can adjust.'),
(87, 1, 4, 'afternoon', 'warm', 'You\'re halfway through the day. Quietly proud of how you\'re handling everything.'),
(88, 1, 4, 'evening', 'gentle', 'Evening, Lilja. You worked hard today. Don\'t replay the small mistakes — just rest.'),
(89, 1, 4, 'evening', 'warm', 'Day\'s done, and you got through it. That itself is something to feel good about.'),
(90, 1, 4, 'evening', 'gentle', 'Sleep well tonight. Tomorrow\'s a new day, and you don\'t have to be perfect for it.'),
(91, 1, 4, 'low_vocal', 'gentle', 'Your voice was a little quiet today. Not bad — just shy. Let\'s find that volume together.'),
(92, 1, 4, 'low_vocal', 'warm', 'Vocals will come with confidence, and confidence comes with time. We\'re in no hurry.'),
(93, 1, 4, 'low_vocal', 'serious', 'Your singing has potential people don\'t see yet because you hold back. Let\'s work on letting go.'),
(94, 1, 4, 'low_dance', 'gentle', 'Dance felt uncertain today. That\'s okay — your movements get prettier when you stop overthinking.'),
(95, 1, 4, 'low_dance', 'warm', 'Tough dance session, hm? Don\'t apologize. We just keep practicing until it feels natural.'),
(96, 1, 4, 'low_dance', 'gentle', 'You hesitated a lot on the floor today. Trust your body — it knows more than you think.'),
(97, 1, 4, 'low_visual', 'warm', 'Visual was a little tense today. You\'re beautiful when you\'re relaxed — just breathe and smile.'),
(98, 1, 4, 'low_visual', 'gentle', 'The camera caught some stiffness today. It happens. We\'ll do a softer set tomorrow.'),
(99, 1, 4, 'low_visual', 'serious', 'You have everything a top idol needs visually. The only thing missing is believing it yourself.'),
(100, 1, 4, 'audition_day', 'gentle', 'Audition day. Nervous is normal. Whatever happens out there, I\'m proud of you for going.'),
(101, 1, 4, 'audition_day', 'warm', 'Today\'s the day. Remember — you don\'t have to be perfect. You just have to be yourself.'),
(102, 1, 4, 'audition_day', 'firm', 'Audition day. You\'ve trained for this. You belong on that stage as much as anyone — go show them.'),
(103, 1, 4, 'rest_day', 'gentle', 'Rest day. Take it slow, read, call home — whatever makes you feel calm. You\'ve earned it.'),
(104, 1, 4, 'rest_day', 'warm', 'Day off today. Please rest properly — no quietly practicing in your room thinking I won\'t notice.'),
(105, 1, 4, 'rest_day', 'gentle', 'A full rest day, Lilja. The world won\'t end if you take care of yourself for one day. Promise.'),
(106, 1, 4, 'good_progress', 'warm', 'Your progress this week was real, and visible. I hope you can see it the way I do.'),
(107, 1, 4, 'good_progress', 'gentle', 'You\'re growing, Lilja. Quietly, steadily — exactly the way that lasts. Very proud.'),
(108, 1, 4, 'good_progress', 'serious', 'The numbers don\'t lie. You\'re improving fast. Now it\'s time to believe what the results say.'),
(109, 1, 5, 'morning', 'warm', 'Morning, Sumika! You sound ready to take on the world already. Channel it into the lessons today.'),
(110, 1, 5, 'morning', 'playful', 'Good morning, captain. Pre-game pep talk: stretch, eat, smile — in that order. Let\'s go.'),
(111, 1, 5, 'morning', 'firm', 'Morning. I know you\'re fired up, but pace yourself — full day ahead, not a sprint.'),
(112, 1, 5, 'afternoon', 'warm', 'Afternoon, and you\'re still going strong. That sports background really shows in your stamina.'),
(113, 1, 5, 'afternoon', 'playful', 'Halftime, Sumika. Grab water, catch your breath, then we\'re back on the court.'),
(114, 1, 5, 'afternoon', 'firm', 'Good work this morning. Stay sharp — afternoon lessons are where your focus tends to slip.'),
(115, 1, 5, 'evening', 'gentle', 'Evening, Sumika. You went full speed all day. Now actually slow down — cooldown counts too.'),
(116, 1, 5, 'evening', 'warm', 'Long day, well played. Stretch properly tonight or your legs will hate you tomorrow.'),
(117, 1, 5, 'evening', 'firm', 'Day\'s over. No extra training tonight — recovery is part of the program, not a bonus.'),
(118, 1, 5, 'low_vocal', 'warm', 'Vocals dipped today — probably out of breath from putting your whole body into it. Let\'s pace it.'),
(119, 1, 5, 'low_vocal', 'serious', 'Singing isn\'t a sprint, Sumika. Less power, more control — your vocals will climb fast that way.'),
(120, 1, 5, 'low_vocal', 'gentle', 'A little off-key today, but the energy was there. We just need to tune the instrument now.'),
(121, 1, 5, 'low_dance', 'firm', 'Dance was below your level today. The athleticism is there — focus on hitting the counts cleanly.'),
(122, 1, 5, 'low_dance', 'serious', 'You moved fast but messy in dance. Precision over speed — that\'s the next step for you.'),
(123, 1, 5, 'low_dance', 'warm', 'Tougher dance session than usual. Don\'t take it to heart — your fundamentals are still solid.'),
(124, 1, 5, 'low_visual', 'gentle', 'Visual scores were lower today. Sometimes that big smile needs a moment of softness too. We\'ll practice.'),
(125, 1, 5, 'low_visual', 'serious', 'Visual needs work. You\'ve got the bright presence — now we add expression range to go with it.'),
(126, 1, 5, 'low_visual', 'playful', 'Your \"game face\" was a little too game today. Let\'s find some other expressions to add to the lineup.'),
(127, 1, 5, 'audition_day', 'firm', 'Audition day. Treat it like a final match — leave it all on the stage. You\'ve got this, Sumika.'),
(128, 1, 5, 'audition_day', 'warm', 'Today\'s your game day. All that training, all that sweat — go show them what you can do.'),
(129, 1, 5, 'audition_day', 'playful', 'Big match today. Pre-game ritual: deep breath, big smile, then knock \'em flat. Go!'),
(130, 1, 5, 'rest_day', 'firm', 'Rest day, Sumika. I know that\'s harder for you than training. Sit still. That\'s the assignment.'),
(131, 1, 5, 'rest_day', 'gentle', 'Day off today. Even athletes have recovery days — your body grows stronger when you actually rest.'),
(132, 1, 5, 'rest_day', 'warm', 'Full rest day. Do something fun, see friends, whatever\'s NOT a workout. You\'ve earned it.'),
(133, 1, 5, 'good_progress', 'warm', 'Your numbers are up, your energy is up — you\'re in great form right now. Keep this rhythm going.'),
(134, 1, 5, 'good_progress', 'firm', 'Strong week, Sumika. The effort is showing on the scoreboard. This is how champions train.'),
(135, 1, 5, 'good_progress', 'playful', 'Look at you climbing the rankings. The whole team is going to have to catch up to you at this rate.');

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
(7, 1, 2, 'class', 'Academic Class', 'Regular shared school lessons.', '09:00:00', '12:00:00', 'Classroom 1-1', 1, 1, '2026-05-28 14:59:05', '2026-05-29 08:04:10'),
(8, 2, 2, 'class', 'Academic Class', 'Regular shared school lessons.', '09:00:00', '12:00:00', 'Classroom 1-1', 1, 1, '2026-05-28 14:59:05', '2026-05-29 08:04:10'),
(9, 3, 2, 'class', 'Academic Class', 'Regular shared school lessons.', '09:00:00', '12:00:00', 'Classroom 1-1', 1, 1, '2026-05-28 14:59:05', '2026-05-29 08:04:10'),
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
(22, 1, 5, 'class', 'Academic Class', 'Regular shared school lessons.', '13:00:00', '15:00:00', 'Classroom 1-1', 1, 1, '2026-05-28 14:59:05', '2026-05-29 08:04:10'),
(23, 2, 5, 'class', 'Academic Class', 'Regular shared school lessons.', '13:00:00', '15:00:00', 'Classroom 1-1', 1, 1, '2026-05-28 14:59:05', '2026-05-29 08:04:10'),
(24, 3, 5, 'class', 'Academic Class', 'Regular shared school lessons.', '13:00:00', '15:00:00', 'Classroom 1-1', 1, 1, '2026-05-28 14:59:05', '2026-05-29 08:04:10'),
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
(67, 3, 7, 'rest', 'Rest Day', 'Producer-enforced rest.', '00:00:00', '23:59:59', '-', 1, 1, '2026-05-28 14:59:05', '2026-05-28 14:59:05'),
(68, 4, 1, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '10:00:00', '12:00:00', 'Vocal Studio A', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(69, 5, 1, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '10:00:00', '12:00:00', 'Vocal Studio A', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(70, 4, 1, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '14:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(71, 5, 1, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '14:00:00', '16:00:00', 'Dance Studio 1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(72, 4, 2, 'class', 'Academic Class', 'Class 1-1 regular school lessons.', '09:00:00', '12:00:00', 'Classroom 1-1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(73, 5, 2, 'class', 'Academic Class', 'Class 1-1 regular school lessons.', '09:00:00', '12:00:00', 'Classroom 1-1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(74, 4, 2, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '14:00:00', '16:00:00', 'Visual Studio', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(75, 5, 2, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '14:00:00', '16:00:00', 'Visual Studio', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(76, 4, 3, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '10:00:00', '12:00:00', 'Dance Studio 1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(77, 5, 3, 'dance', 'Academy Dance Lesson', 'Group dance training class.', '10:00:00', '12:00:00', 'Dance Studio 1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(78, 4, 4, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '14:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(79, 5, 4, 'vocal', 'Academy Vocal Lesson', 'Group vocal training class.', '14:00:00', '16:00:00', 'Vocal Studio A', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(80, 4, 5, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '10:00:00', '12:00:00', 'Visual Studio', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(81, 5, 5, 'visual', 'Academy Visual Lesson', 'Group visual expression class.', '10:00:00', '12:00:00', 'Visual Studio', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(82, 4, 5, 'class', 'Academic Class', 'Class 1-1 regular school lessons.', '13:00:00', '15:00:00', 'Classroom 1-1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(83, 5, 5, 'class', 'Academic Class', 'Class 1-1 regular school lessons.', '13:00:00', '15:00:00', 'Classroom 1-1', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(84, 4, 1, 'rest', 'Morning Quiet Time', 'Solo reading or meditation before lessons start.', '07:00:00', '08:30:00', 'Library', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(85, 4, 1, 'vocal', 'Solo Vocal Practice', 'Quiet personal warmups. Builds confidence gradually.', '16:30:00', '17:30:00', 'Practice Room 4', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(86, 4, 2, 'rest', 'Call Home', 'Weekly video call with family in Finland.', '07:30:00', '08:30:00', 'Dormitory', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(87, 4, 2, 'visual', 'Solo Visual Practice', 'Self-recorded posing practice. Reviews her own film.', '16:30:00', '18:00:00', 'Practice Room 4', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(88, 4, 3, 'rest', 'Self-Study', 'Quiet study session. Catching up on Japanese reading.', '08:00:00', '09:30:00', 'Library', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(89, 4, 3, 'photoshoot', 'Magazine Modeling', 'Modeling work from her pre-academy career.', '13:30:00', '17:00:00', 'Studio C', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(90, 4, 4, 'visual', 'Solo Visual Practice', 'Mirror practice for expression and softness.', '09:30:00', '11:30:00', 'Practice Room 4', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(91, 4, 4, 'rest', 'Confidence Session', 'One-on-one talk with Producer. Gentle encouragement.', '16:30:00', '17:30:00', 'Meeting Room', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(92, 4, 5, 'vocal', 'Solo Vocal Practice', 'Personal practice with the door closed.', '15:30:00', '16:30:00', 'Practice Room 4', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(93, 4, 6, 'photoshoot', 'Modeling Job', 'Scheduled modeling shoot. Familiar territory for her.', '10:00:00', '14:00:00', 'Studio C', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(94, 4, 6, 'dance', 'Solo Dance Practice', 'Gentle choreography review. Works best when alone.', '15:30:00', '17:00:00', 'Practice Room 4', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(95, 4, 7, 'rest', 'Rest Day', 'Full rest. Reading, calls home, quiet hobbies.', '00:00:00', '23:59:59', '-', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(96, 5, 1, 'training', 'Morning Tennis', 'Solo tennis practice. Keeps her sports edge sharp.', '06:30:00', '07:45:00', 'Tennis Court', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(97, 5, 1, 'training', 'Cooldown Stretches', 'Post-dance recovery stretches.', '16:30:00', '17:15:00', 'Gym', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(98, 5, 2, 'training', 'Morning Run', 'Light morning jog around the academy.', '06:30:00', '07:30:00', 'Academy Grounds', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(99, 5, 2, 'dance', 'Solo Dance Practice', 'Working on choreography precision.', '16:30:00', '18:00:00', 'Practice Room 5', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(100, 5, 3, 'training', 'Morning Tennis', 'Solo tennis drills.', '06:30:00', '07:45:00', 'Tennis Court', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(101, 5, 3, 'training', 'Strength Training', 'Light gym workout. Keeps her stamina up.', '13:00:00', '14:30:00', 'Gym', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(102, 5, 4, 'training', 'Morning Run', 'Sprint intervals around the grounds.', '06:30:00', '07:30:00', 'Academy Grounds', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(103, 5, 4, 'dance', 'Solo Dance Practice', 'Working on expression alongside the moves.', '09:30:00', '11:30:00', 'Practice Room 5', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(104, 5, 5, 'training', 'Morning Tennis', 'Solo tennis practice.', '06:30:00', '07:45:00', 'Tennis Court', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(105, 5, 5, 'training', 'Cooldown Stretches', 'Post-week recovery routine.', '15:30:00', '16:30:00', 'Gym', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(106, 5, 6, 'training', 'Tennis Match', 'Friendly weekend tennis match with old teammates.', '09:00:00', '12:00:00', 'Tennis Court', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(107, 5, 6, 'vocal', 'Solo Vocal Practice', 'Working on breath control — sports lungs help.', '14:00:00', '15:30:00', 'Practice Room 5', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10'),
(108, 5, 7, 'rest', 'Rest Day', 'Full rest. Tries to actually sit still. Tries.', '00:00:00', '23:59:59', '-', 1, 1, '2026-05-29 08:04:10', '2026-05-29 08:04:10');

--
-- Dumping data for table `songs`
--

INSERT INTO `songs` (`id`, `title`, `title_jp`, `artist`, `duration`, `release_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Fighting My Way', 'Fighting My Way', 'Saki Hanami', '00:03:21', '2024-05-16', 'Saki Hanami\'s debut solo song. Lyrics: HIROMI. Composition & arrangement: Giga. Performed in 定期公演『初』.', 1, '2026-05-27 13:20:33', '2026-05-27 13:20:33'),
(2, 'Luna say maybe', 'Luna say maybe', 'Temari Tsukimura', '00:04:25', '2024-05-16', 'Temari Tsukimura\'s debut solo song and signature track. Lyrics & composition: Minami (美波). Arrangement: Minami & Katsuhiro Mafune. Performed in the produce scenario \"Teiki Kouen Hatsu\" (定期公演『初』). ', 1, '2026-05-27 13:20:33', '2026-05-27 13:20:33'),
(3, 'Sekai Ichi Kawaii Watashi', '世界一可愛い私', 'Kotone Fujita', '00:03:59', '2024-05-16', 'Kotone Fujita\'s debut solo song. Lyrics, composition, and arrangement all by HoneyWorks — the duo behind the wildly popular \"Kokuhaku Jikkou Iinkai\" (Confession Executive Committee) series. ', 1, '2026-05-27 13:20:33', '2026-05-27 13:20:33');

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `name`, `name_jp`, `birthday`, `blood_type`, `height`, `hometown`, `school_year`, `rank`, `vocal`, `dance`, `visual`, `bio`, `theme_primary_color`, `theme_secondary_color`, `producer_id`, `created_at`, `updated_at`) VALUES
(1, 5, 'Saki Hanami', '花海 咲季', '2000-04-02', 'A', 152, 'Aichi', 'Class 1-1', 'Debut', 75, 75, 80, 'A new student who topped the entrance exam and a fierce ex-athlete with an unshakeable hatred of losing. A child prodigy since youth—quick to learn, skilled at everything she tries. She\'s extremely close with her younger sister Ume Hanami, but they\'re also lifelong rivals who have competed in every sport imaginable. Saki rates Ume\'\'s talent higher than anyone else\'s and fears it.\r\n', '#E30F25', '#FAD0D4', 1, '2026-05-26 13:13:20', '2026-05-27 15:57:18'),
(2, 6, 'Temari Tsukimura', '月村 手毬', '2000-06-03', 'AB', 162, 'Kyoto', 'Class 1-1', 'Debut', 75, 65, 55, 'A former elite middle-school idol, once called the #1 idol of her grade. Appears cool, stoic, and sarcastic on the surface but is secretly a lazy, spoiled troublemaker—a girl with two sides. She aims for the top to break away from the self she dislikes and learn to love herself. ', '#0C7BBB', '#CEE5F1', 1, '2026-05-26 13:13:20', '2026-05-27 16:43:30'),
(3, 7, 'Kotone Fujita', '藤田 ことね', '2000-04-29', 'O', 156, 'Saitama', 'Class 1-1', 'Debut', 65, 55, 75, 'A greedy girl who dreams of becoming \"an idol who can make money.\" She sees idol work as her one shot at turning her life around. Her grades are poor and her self-esteem is low overall, but she has full confidence in her cute face. She\'s a little uncomfortable around the student council president Sena, who keeps overestimating her for some reason.\r\n', '#F8C112', '#FEF3CF', 1, '2026-05-26 13:13:20', '2026-05-27 16:47:09'),
(4, 8, 'Lilja Katsuragi', '葛城 リーリヤ', '2000-07-24', 'B', 161, 'Sweden', 'Class 1-1', 'Debut', 55, 55, 65, 'An exchange student from overseas with no prior singing or dancing experience, who is always shrinking back as if she had no confidence. She doesn\'t look talented at first glance, but her admiration for idols is genuine, and she is an incredibly hard worker. Her father is Japanese, so she speaks the language fluently. She has a promise with her best friend Sumika to stand on the same stage together one day—the reason she enrolled at Hatsuboshi Academy. ', '#7DC4D4', '#EAFDFF', 1, '2026-05-28 17:43:41', '2026-05-28 17:56:05'),
(5, 9, 'Sumika Shiun', '紫雲 清夏', '2000-11-11', 'O', 168, 'Hokkaido', 'Class 1-1', 'Debut', 65, 55, 65, 'A laid-back gyaru who skips class and lessons—a bit of an unserious slacker, but genuinely bright, cheerful, and able to get along with anyone, which is her real charm. She once had a celebrated ballet career and was expected to go far on the world stage, but she has no motivation for it anymore. She wholeheartedly cheers on her best friend Lilja, who is earnestly chasing her idol dream. ', '#7CFC00', '#D6FFB3', 1, '2026-05-28 17:43:41', '2026-05-28 17:58:33');

--
-- Dumping data for table `student_songs`
--

INSERT INTO `student_songs` (`id`, `student_id`, `song_id`, `added_by`, `added_at`) VALUES
(1, 1, 1, 1, '2026-05-27 13:20:33'),
(2, 2, 2, 1, '2026-05-27 13:20:33'),
(3, 3, 3, 1, '2026-05-27 13:20:33');

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `name`, `specialty`) VALUES
(1, 2, 'Vocal Trainer', 'vocal'),
(2, 3, 'Dance Trainer', 'dance'),
(3, 4, 'Visual Trainer', 'visual');

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
(8, 'Lilja Katsuragi', '$2y$10$U0wQB4kmtvPYWWQX.9oi0OJ60HDhnd/yf.tuf71exJ8MoG8xS0FVa', 'student', 'Lilja Katsuragi.png', 1, '2026-05-26 13:13:13', '2026-05-29 09:11:38', '2026-05-29 09:11:38'),
(9, 'Sumika Shiun', '$2y$10$XWAu/FVsR0m4XnA9srsx7ev50L3zAlz0qQViMwbhYzSDkRh0KF6cO', 'student', 'Sumika Shiun.png', 1, '2026-05-26 13:13:13', '2026-05-29 09:12:01', '2026-05-29 09:12:01');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
