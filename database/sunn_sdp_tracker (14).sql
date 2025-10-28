-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 28, 2025 at 07:31 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunn_sdp_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_accomplishment`
--

CREATE TABLE `tbl_accomplishment` (
  `accomplishment_id` int(11) NOT NULL,
  `sdp_id` int(11) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `accomplishment` varchar(255) DEFAULT NULL COMMENT 'Actual value achieved',
  `r_matrix_id` int(11) DEFAULT NULL,
  `evidence` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity_logs`
--

CREATE TABLE `tbl_activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_activity_logs`
--

INSERT INTO `tbl_activity_logs` (`log_id`, `user_id`, `action`, `module`, `details`, `created_at`) VALUES
(1, 3, 'Updated User', 'Users', 'Updated user &#039;Sys  John&#039;: full name from &#039;Sys  John&#039; to &#039;Sys John&#039;, office from &#039;&#039; to &#039;Information Unit&#039;, password updated.', '2025-10-14 07:22:15'),
(2, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Attempted to update office/unit &#039;Information Unit&#039;, but no values were changed.', '2025-10-14 07:33:46'),
(3, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;Information Unit&#039;: office/unit from &#039;Information Unit&#039; to &#039;Information Unit1&#039;.', '2025-10-14 07:33:57'),
(4, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;1&#039; (ID: 2)', '2025-10-14 07:34:02'),
(5, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;1&#039; (ID: 2)', '2025-10-14 07:34:08'),
(6, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;Information Unit1&#039;: office/unit from &#039;Information Unit1&#039; to &#039;Information Unit&#039;.', '2025-10-14 07:34:11'),
(7, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 21)', '2025-10-14 07:39:37'),
(8, 3, 'Created User', 'Users', 'Created new user &#039;2&#039; (ID: 22)', '2025-10-14 07:40:49'),
(9, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;1 &#039;.', '2025-10-14 07:40:54'),
(10, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;1 &#039;, office from &#039;Information Unit&#039; to &#039;&#039;.', '2025-10-14 07:42:31'),
(11, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;1 1&#039;, username from &#039;1&#039; to &#039;11&#039;, position from &#039;1&#039; to &#039;11&#039;, office from &#039;&#039; to &#039;Information Unit&#039;, password updated.', '2025-10-14 07:42:48'),
(12, 3, 'Deleted User', 'Users', 'Deleted user &#039;1  1&#039; (ID: 21)', '2025-10-14 07:42:53'),
(13, 3, 'Deleted User', 'Users', 'Deleted user &#039;2&#039; (ID: 22)', '2025-10-14 07:42:56'),
(14, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 23)', '2025-10-14 07:43:01'),
(15, 3, 'Deleted User', 'Users', 'Deleted user &#039;1&#039; (ID: 23)', '2025-10-14 07:43:04'),
(16, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;1&#039; (ID: 3)', '2025-10-14 07:43:08'),
(17, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) To foster a supportive environment for research and development to thrive in.&#039; (ID: 1)', '2025-10-14 07:56:21'),
(18, 3, 'Updated Objective', 'Objectives', 'Attempted to update objective &#039;1) To foster a supportive environment for research and development to thrive in.&#039;, but no values were changed.', '2025-10-14 07:56:30'),
(19, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 24)', '2025-10-15 06:56:37'),
(20, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;11 &#039;, username from &#039;1&#039; to &#039;11&#039;, position from &#039;1&#039; to &#039;11&#039;, password updated.', '2025-10-15 06:56:45'),
(21, 3, 'Deleted User', 'Users', 'Deleted user &#039;11&#039; (ID: 24)', '2025-10-15 06:56:49'),
(22, 3, 'Updated Objective', 'Objectives', 'Attempted to update objective &#039;1) To foster a supportive environment for research and development to thrive in.&#039;, but no values were changed.', '2025-10-15 06:56:56'),
(23, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;2) To take a lead in innovation, trends and up-to-date education and leadership practices and promote research and creative outputs that are beneficial for the stakeholders and the community.&#039; (ID: 2)', '2025-10-15 07:00:14'),
(24, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;3) To cultivate an environment that fully supports and promotes research and creative outputs from students&#039; (ID: 3)', '2025-10-15 07:00:35'),
(25, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) To increase the number of university linkages and international partners to enhance institutional representation and involvement of in multi-sectoral activities and programs&#039; (ID: 4)', '2025-10-15 07:00:55'),
(26, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;2) To facilitate international exchange programs and promote local and international mobility of SUNN faculty, students and staff and their involvement in international relations and other related activities&#039; (ID: 5)', '2025-10-15 07:01:07'),
(27, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;3) To acquire accreditation and certifications from international accrediting bodies for university recognition&#039; (ID: 6)', '2025-10-15 07:01:20'),
(28, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;4) To strengthen outsourcing strategies for research, extension, scholarship and income generation for institutional projects.&#039; (ID: 7)', '2025-10-15 07:01:29'),
(29, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;5) To establish a supportive environment that fosters healthy and productive community engagement and effective implementation of extension services.&#039; (ID: 8)', '2025-10-15 07:01:41'),
(30, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;6) To strengthen and support faculty in engaging extension services.&#039; (ID: 9)', '2025-10-15 07:01:54'),
(31, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;7) To maximize the capability of the institution in conducting extension activities.To take a lead in innovation, trends and up-to- date education and leadership practices.&#039; (ID: 10)', '2025-10-15 07:02:28'),
(32, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;8) To promote and encourage extension service programs that are beneficial for the stakeholders and the community.&#039; (ID: 11)', '2025-10-15 07:02:39'),
(33, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) To ensure transparent processes for students to access free tuition, uphold statutory requirements, integrate gender- responsive policies, and enhance institution credibility and efficiency.&#039; (ID: 12)', '2025-10-15 07:03:02'),
(34, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;2) To develop and implement plans for uninterrupted education and holistic student support, fostering academic success, personal growth, and well-being amid challenges.&#039; (ID: 13)', '2025-10-15 07:03:14'),
(35, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;3) To foster innovation through enhanced customer satisfaction, interdisciplinary collaboration, and knowledge exchange, cultivating a supportive R&amp;amp;D environment and amplifying research impact.&#039; (ID: 14)', '2025-10-15 07:03:25'),
(36, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;4) To develop an HR framework emphasizing competency-based recruitment, continuous training, strategic hiring, and merit-based rewards to foster excellence and professional growth within the University.&#039; (ID: 15)', '2025-10-15 07:03:35'),
(37, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;5) To optimize supply and property management processes through efficient, inclusive, and adaptable practices that adhere to regulatory standards, promoting streamlined operations and resource utilization within the institution.&#039; (ID: 16)', '2025-10-15 07:05:01'),
(38, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;6) To implement robust financial management strategies aimed at optimizing resource allocation, minimizing financial risks, and enhancing transparency and accountability, thereby fostering fiscal sustainability and supporting the institution&amp;#039;s strategic objectives.&#039; (ID: 17)', '2025-10-15 07:05:55'),
(39, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) To provide students with quality, holistic and proactive services&#039; (ID: 18)', '2025-10-15 07:06:14'),
(40, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;2) To provide sustainable service oriented programs that contribute positively to the improvement of quality of life of the stakeholders.&#039; (ID: 19)', '2025-10-15 07:07:41'),
(41, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;3) To ensure relevance of curricular programs to national and international demand by engaging in continuous learning and improvement, and actively participating in academic activities fostering intellectual growth and holistic development, as well as engage in industry collaboration both in local and international.&#039; (ID: 20)', '2025-10-15 07:07:52'),
(42, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;4) To provide students with quality, holistic and proactive services.&#039; (ID: 21)', '2025-10-15 07:08:04'),
(43, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;5) To provide sustainable service oriented programs that contribute positively to the improvement of quality of life of the stakeholders.To take a lead in innovation, trends and up-to- date education and leadership practices.&#039; (ID: 22)', '2025-10-15 07:08:13'),
(44, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) To develop a comprehensive understanding of potential future scenarios to proactively prepare and adapt institutional strategies.&#039; (ID: 23)', '2025-10-15 07:08:40'),
(45, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;2) To develop and implement novel modalities to improve climate resilience, food security and reduce poverty within communities.&#039; (ID: 24)', '2025-10-15 07:09:00'),
(46, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;3) To streamline and enhance the institution&amp;#039;s transaction processes for increased efficiency by integrating IT solutions.&#039; (ID: 25)', '2025-10-15 07:09:16'),
(47, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) To secure widespread institutional recognition, fostering trust, credibility, and relevance within our stakeholders, while advancing our mission and values.&#039; (ID: 26)', '2025-10-15 07:09:31'),
(48, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1) Provide students the quality education to develop their cognitive skills and abilities and also attitudes, behaviors and values related to their discipline.&#039; (ID: 27)', '2025-10-15 07:09:51'),
(49, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;2) Develop gender- sensitive employees who are respectful of human rights and promote gender equality.&#039; (ID: 28)', '2025-10-15 07:10:10'),
(50, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;3) Provide a mechanism that empowers the University to develop its HRM competencies, system and practices toward advance and strategic HR excellence.&#039; (ID: 29)', '2025-10-15 07:10:23'),
(51, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;4) Provide HR the benefits pertaining to healthcare, pursuance to higher education and other motivational factors such as commitment, job satisfaction.&#039; (ID: 30)', '2025-10-15 07:10:36'),
(52, 3, 'Created SDP', 'SDP', 'Created SDP #3', '2025-10-15 07:39:16'),
(53, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 07:39:56'),
(54, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 07:42:08'),
(55, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 07:42:32'),
(56, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 07:42:38'),
(57, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 07:43:18'),
(58, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 07:43:23'),
(59, 3, 'Created SDP', 'SDP', 'Created SDP #4', '2025-10-15 07:52:53'),
(60, 3, 'Updated SDP', 'SDP', 'Updated SDP #3: office changed', '2025-10-15 08:22:15'),
(61, 3, 'Created SDP', 'SDP', 'Created SDP #5', '2025-10-20 11:14:01'),
(62, 3, 'Deleted SDP', 'SDP', 'Deleted SDP #5', '2025-10-20 11:14:16'),
(63, 3, 'Deleted SDP', 'SDP', 'Deleted SDP #4', '2025-10-20 12:20:29'),
(64, 3, 'Created SDP', 'SDP', 'Created new SDP &#039;123&#039; (ID: 6) for objective &#039;1) Provide students the quality education to develop their cognitive skills and abilities and also attitudes, behaviors and values related to their discipline.&#039;', '2025-10-20 13:54:53'),
(65, 3, 'Created SDP', 'SDP', 'Created new SDP &#039;132123&#039; (ID: 7) for objective &#039;1) To develop a comprehensive understanding of potential future scenarios to proactively prepare and adapt institutional strategies.&#039;', '2025-10-20 14:06:07'),
(66, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;Research Collaborations with Agencies and Academic Institutions&#039;, but no values were changed.', '2025-10-20 14:06:19'),
(67, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;123&#039;, but no values were changed.', '2025-10-20 14:41:52'),
(68, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;123&#039;, but no values were changed.', '2025-10-20 14:42:13'),
(69, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;123&#039;, but no values were changed.', '2025-10-21 07:02:48'),
(70, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 25)', '2025-10-25 09:41:11'),
(71, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;11&#039; (ID: 28)', '2025-10-25 09:41:18'),
(72, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;11&#039;: office/unit from &#039;11&#039; to &#039;111&#039;.', '2025-10-25 09:41:25'),
(73, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;111&#039; (ID: 28)', '2025-10-25 09:41:31'),
(74, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;11 &#039;.', '2025-10-25 09:41:40'),
(75, 3, 'Updated User', 'Users', 'Updated user &#039;11&#039;: full name from &#039;11&#039; to &#039;11 1&#039;.', '2025-10-25 09:41:47'),
(76, 3, 'Deleted User', 'Users', 'Deleted user &#039;11  1&#039; (ID: 25)', '2025-10-25 09:41:53'),
(77, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1&#039; (ID: 31)', '2025-10-25 09:42:01'),
(78, 3, 'Updated Objective', 'Objectives', 'Attempted to update objective &#039;1&#039;, but no values were changed.', '2025-10-25 09:42:06'),
(79, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;1&#039; (ID: 31)', '2025-10-25 09:42:21'),
(80, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;123&#039;, but no values were changed.', '2025-10-25 09:51:40'),
(81, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 26)', '2025-10-25 10:06:08'),
(82, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;1 &#039;.', '2025-10-25 10:14:13'),
(83, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;1 &#039;.', '2025-10-25 10:14:18'),
(84, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;11&#039;.', '2025-10-25 10:14:27'),
(85, 3, 'Created User', 'Users', 'Created new user &#039;2&#039; (ID: 27)', '2025-10-25 10:14:50'),
(86, 3, 'Deleted User', 'Users', 'Deleted user &#039;11&#039; (ID: 26)', '2025-10-25 10:20:06'),
(87, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 28)', '2025-10-25 10:20:29'),
(88, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;1&#039; (ID: 3)', '2025-10-25 10:22:17'),
(89, 3, 'Deleted User', 'Users', 'Deleted user &#039;1&#039; (ID: 28)', '2025-10-25 10:22:27'),
(90, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;1&#039; (ID: 29)', '2025-10-25 10:22:37'),
(91, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;1&#039; (ID: 29)', '2025-10-25 10:22:45'),
(92, 3, 'Deleted User', 'Users', 'Deleted user &#039;2&#039; (ID: 27)', '2025-10-25 11:16:53'),
(93, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 29)', '2025-10-25 11:17:04'),
(94, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;1 &#039;.', '2025-10-25 11:17:08'),
(95, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;11&#039;, username from &#039;1&#039; to &#039;11&#039;, position from &#039;1&#039; to &#039;11&#039;, office from &#039;Deans&#039; to &#039;Deans and Directors&#039;, role from &#039;Staff&#039; to &#039;Dean&#039;, password updated.', '2025-10-25 11:17:29'),
(96, 3, 'Deleted User', 'Users', 'Deleted user &#039;11&#039; (ID: 29)', '2025-10-25 11:17:33'),
(97, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;1&#039; (ID: 30)', '2025-10-25 11:17:38'),
(98, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;11&#039; (ID: 31)', '2025-10-25 11:17:43'),
(99, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;1&#039;: office/unit from &#039;1&#039; to &#039;111&#039;.', '2025-10-25 11:17:57'),
(100, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Attempted to update office/unit &#039;11&#039;, but no values were changed.', '2025-10-25 11:20:37'),
(101, 3, 'Updated Objective', 'Objectives', 'Attempted to update objective &#039;1) Provide students the quality education to develop their cognitive skills and abilities and also attitudes, behaviors and values related to their discipline.&#039;, but no values were changed.', '2025-10-25 11:20:43'),
(102, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;123&#039;, but no values were changed.', '2025-10-25 11:20:55'),
(103, 3, 'Updated App Setting', 'App Settings', 'Attempted to update app settings, but no values were changed.', '2025-10-25 11:20:59'),
(104, 3, 'Updated App Setting', 'App Settings', 'Updated app settings: app name from &#039;SUNN SDP TRACKER&#039; to &#039;SUNN SDP TRACKER1&#039;.', '2025-10-25 11:21:05'),
(105, 3, 'Updated App Setting', 'App Settings', 'Updated app settings: app name from &#039;SUNN SDP TRACKER1&#039; to &#039;SUNN SDP TRACKER&#039;.', '2025-10-25 11:21:10'),
(106, 3, 'Updated App Setting', 'App Settings', 'Attempted to update app settings, but no values were changed.', '2025-10-25 11:21:11'),
(107, 3, 'Created User', 'Users', 'Created new user &#039;1&#039; (ID: 30)', '2025-10-25 11:21:36'),
(108, 3, 'Updated User', 'Users', 'Updated user &#039;1&#039;: full name from &#039;1&#039; to &#039;11&#039;, username from &#039;1&#039; to &#039;11&#039;, position from &#039;1&#039; to &#039;11&#039;, office from &#039;11&#039; to &#039;111&#039;, role from &#039;Staff&#039; to &#039;Faculty&#039;, password updated.', '2025-10-25 11:21:48'),
(109, 3, 'Deleted User', 'Users', 'Deleted user &#039;11&#039; (ID: 30)', '2025-10-25 11:21:51'),
(110, 3, 'Created User', 'Users', 'Created new user &#039;123&#039; (ID: 31)', '2025-10-25 11:33:14'),
(111, 3, 'Updated User', 'Users', 'Updated user &#039;123&#039;: full name from &#039;123&#039; to &#039;123 12&#039;, username from &#039;123&#039; to &#039;1231&#039;, position from &#039;123&#039; to &#039;1231&#039;, office from &#039;111&#039; to &#039;11&#039;, role from &#039;Staff&#039; to &#039;Dean&#039;, password updated.', '2025-10-25 11:33:25'),
(112, 3, 'Deleted User', 'Users', 'Deleted user &#039;123  12&#039; (ID: 31)', '2025-10-25 11:33:28'),
(113, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;Research Collaborations with Agencies and Academic Institutions&#039;, but no values were changed.', '2025-10-25 11:34:09'),
(114, 3, 'Deleted SDP', 'SDP', 'Deleted SDP &#039;132123&#039; (ID: 7)', '2025-10-25 11:34:25'),
(115, 3, 'Deleted SDP', 'SDP', 'Deleted SDP &#039;123&#039; (ID: 6)', '2025-10-25 11:34:32'),
(116, 3, 'Created SDP', 'SDP', 'Created new SDP &#039;123&#039; (ID: 8) for objective &#039;1) Provide students the quality education to develop their cognitive skills and abilities and also attitudes, behaviors and values related to their discipline.&#039;', '2025-10-25 11:34:49'),
(117, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;123&#039;, but no values were changed.', '2025-10-25 11:35:10'),
(118, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;123&#039; (ID: 32)', '2025-10-25 11:35:22'),
(119, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Attempted to update office/unit &#039;11&#039;, but no values were changed.', '2025-10-25 11:42:40'),
(120, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;111&#039; (ID: 30)', '2025-10-25 11:42:47'),
(121, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:49'),
(122, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:50'),
(123, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:50'),
(124, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:51'),
(125, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:51'),
(126, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:51'),
(127, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;&#039; (ID: 30)', '2025-10-25 11:42:51'),
(128, 3, 'Updated Objective', 'Objectives', 'Attempted to update objective &#039;1) Provide students the quality education to develop their cognitive skills and abilities and also attitudes, behaviors and values related to their discipline.&#039;, but no values were changed.', '2025-10-25 11:43:32'),
(129, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;1&#039; (ID: 33)', '2025-10-25 11:43:41'),
(130, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;1&#039; (ID: 33)', '2025-10-25 11:43:45'),
(131, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;&#039; (ID: 33)', '2025-10-25 11:43:46'),
(132, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;&#039; (ID: 33)', '2025-10-25 11:43:46'),
(133, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;&#039; (ID: 33)', '2025-10-25 11:43:46'),
(134, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Attempted to update office/unit &#039;11&#039;, but no values were changed.', '2025-10-25 12:06:02'),
(135, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;11&#039;: office/unit from &#039;11&#039; to &#039;1123&#039;.', '2025-10-25 12:06:07'),
(136, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;1123&#039; (ID: 31)', '2025-10-25 12:06:10'),
(137, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;123&#039; (ID: 32)', '2025-10-25 12:06:18'),
(138, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;1233&#039; (ID: 33)', '2025-10-25 12:06:23'),
(139, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;123&#039; (ID: 32)', '2025-10-25 12:06:26'),
(140, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;1233&#039; (ID: 33)', '2025-10-25 12:06:29'),
(141, 3, 'Updated Objective', 'Objectives', 'Attempted to update objective &#039;123&#039;, but no values were changed.', '2025-10-25 12:06:49'),
(142, 3, 'Updated Objective', 'Objectives', 'Updated objective &#039;123&#039;: details from &#039;123&#039; to &#039;123123&#039;, focus area from &#039;123&#039; to &#039;123123&#039;.', '2025-10-25 12:06:55'),
(143, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;123123&#039; (ID: 32)', '2025-10-25 12:06:58'),
(144, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;123&#039; (ID: 34)', '2025-10-25 12:07:05'),
(145, 3, 'Created Objective', 'Objectives', 'Created new objective &#039;123123&#039; (ID: 35)', '2025-10-25 12:07:13'),
(146, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;123&#039; (ID: 34)', '2025-10-25 12:07:17'),
(147, 3, 'Deleted Objective', 'Objectives', 'Deleted objective &#039;123123&#039; (ID: 35)', '2025-10-25 12:07:20'),
(148, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;123&#039; (ID: 34)', '2025-10-25 12:07:26'),
(149, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Attempted to update office/unit &#039;123&#039;, but no values were changed.', '2025-10-25 12:07:34'),
(150, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;123&#039;: office/unit from &#039;123&#039; to &#039;123123&#039;.', '2025-10-25 12:07:37'),
(151, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;w&#039; (ID: 35)', '2025-10-25 12:11:29'),
(152, 3, 'Created Office/Unit', 'Responsibility Matrix', 'Created new office/unit &#039;ww&#039; (ID: 36)', '2025-10-25 12:11:33'),
(153, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;123123&#039; (ID: 34)', '2025-10-25 12:11:37'),
(154, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;w&#039;: office/unit from &#039;w&#039; to &#039;www&#039;.', '2025-10-25 12:11:50'),
(155, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;ww&#039; (ID: 36)', '2025-10-25 12:11:53'),
(156, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;www&#039; (ID: 35)', '2025-10-25 12:11:56'),
(157, 3, 'Deleted SDP', 'SDP', 'Deleted SDP &#039;123&#039; (ID: 8)', '2025-10-26 00:03:54'),
(158, 3, 'Created SDP', 'SDP', 'Created new SDP &#039;Number of International Research/Creative Collaborations&#039; (ID: 9) for objective &#039;1) To foster a supportive environment for research and development to thrive in.&#039;', '2025-10-26 01:16:39'),
(159, 3, 'Created SDP', 'SDP', 'Created new SDP &#039;Research Fund Utilization Rate&#039; (ID: 10) for objective &#039;1) To foster a supportive environment for research and development to thrive in.&#039;', '2025-10-26 01:18:08'),
(160, 3, 'Updated Office/Unit', 'Responsibility Matrix', 'Updated office/unit &#039;Program/Project Leaders&#039;: office/unit from &#039;Program/Project Leaders&#039; to &#039;Project Leaders&#039;.', '2025-10-26 01:18:23'),
(161, 3, 'Deleted Office/Unit', 'Responsibility Matrix', 'Deleted office/unit &#039;Research Office&#039; (ID: 10)', '2025-10-26 01:18:51'),
(162, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;Number of International Research/Creative Collaborations&#039;, but no values were changed.', '2025-10-26 01:19:46'),
(163, 3, 'Created SDP', 'SDP', 'Created new SDP &#039;Research Laboratories/Centers established; Complete with laboratory components&#039; (ID: 11) for objective &#039;1) To foster a supportive environment for research and development to thrive in.&#039;', '2025-10-26 01:20:59'),
(164, 3, 'Updated SDP', 'SDP', 'Attempted to update SDP &#039;Number of International Research/Creative Collaborations&#039;, but no values were changed.', '2025-10-28 03:26:50'),
(165, 3, 'Updated App Setting', 'App Settings', 'Attempted to update app settings, but no values were changed.', '2025-10-28 05:43:42');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_app_setting`
--

CREATE TABLE `tbl_app_setting` (
  `setting_id` int(11) NOT NULL,
  `app_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logo` varchar(255) NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_app_setting`
--

INSERT INTO `tbl_app_setting` (`setting_id`, `app_name`, `address`, `contact_number`, `email`, `about`, `updated_at`, `logo`) VALUES
(1, 'SUNN SDP TRACKER', 'SUNN', '0934569876', 'sunn_eat@sunn.edu.ph', 'SUNN SDP TRACKER', '2025-10-25 11:21:10', 'logo_68edb6df0b78a4.59004219.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_objectives`
--

CREATE TABLE `tbl_objectives` (
  `objectives_id` int(11) NOT NULL,
  `objectives_details` text NOT NULL,
  `focus_area` varchar(255) DEFAULT NULL COMMENT 'RISENUP areas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_objectives`
--

INSERT INTO `tbl_objectives` (`objectives_id`, `objectives_details`, `focus_area`) VALUES
(1, '1) To foster a supportive environment for research and development to thrive in.', 'RESEARCH, TECHNOLOGY AND INNOVATION'),
(2, '2) To take a lead in innovation, trends and up-to-date education and leadership practices and promote research and creative outputs that are beneficial for the stakeholders and the community.', 'RESEARCH, TECHNOLOGY AND INNOVATION'),
(3, '3) To cultivate an environment that fully supports and promotes research and creative outputs from students', 'RESEARCH, TECHNOLOGY AND INNOVATION'),
(4, '1) To increase the number of university linkages and international partners to enhance institutional representation and involvement of in multi-sectoral activities and programs', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(5, '2) To facilitate international exchange programs and promote local and international mobility of SUNN faculty, students and staff and their involvement in international relations and other related activities', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(6, '3) To acquire accreditation and certifications from international accrediting bodies for university recognition', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(7, '4) To strengthen outsourcing strategies for research, extension, scholarship and income generation for institutional projects.', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(8, '5) To establish a supportive environment that fosters healthy and productive community engagement and effective implementation of extension services.', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(9, '6) To strengthen and support faculty in engaging extension services.', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(10, '7) To maximize the capability of the institution in conducting extension activities.To take a lead in innovation, trends and up-to- date education and leadership practices.', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(11, '8) To promote and encourage extension service programs that are beneficial for the stakeholders and the community.', 'INTERNATIONALIZATION, EXTENSION AND LINKAGES'),
(12, '1) To ensure transparent processes for students to access free tuition, uphold statutory requirements, integrate gender- responsive policies, and enhance institution credibility and efficiency.', 'SUSTAINABILITY OF GOOD GOVERNANCE'),
(13, '2) To develop and implement plans for uninterrupted education and holistic student support, fostering academic success, personal growth, and well-being amid challenges.', 'SUSTAINABILITY OF GOOD GOVERNANCE'),
(14, '3) To foster innovation through enhanced customer satisfaction, interdisciplinary collaboration, and knowledge exchange, cultivating a supportive R&amp;D environment and amplifying research impact.', 'SUSTAINABILITY OF GOOD GOVERNANCE'),
(15, '4) To develop an HR framework emphasizing competency-based recruitment, continuous training, strategic hiring, and merit-based rewards to foster excellence and professional growth within the University.', 'SUSTAINABILITY OF GOOD GOVERNANCE'),
(16, '5) To optimize supply and property management processes through efficient, inclusive, and adaptable practices that adhere to regulatory standards, promoting streamlined operations and resource utilization within the institution.', 'SUSTAINABILITY OF GOOD GOVERNANCE'),
(17, '6) To implement robust financial management strategies aimed at optimizing resource allocation, minimizing financial risks, and enhancing transparency and accountability, thereby fostering fiscal sustainability and supporting the institution&#039;s strategic objectives.', 'SUSTAINABILITY OF GOOD GOVERNANCE'),
(18, '1) To provide students with quality, holistic and proactive services', 'EXCELLENCE IN ACADEMICS AND SERVICES'),
(19, '2) To provide sustainable service oriented programs that contribute positively to the improvement of quality of life of the stakeholders.', 'EXCELLENCE IN ACADEMICS AND SERVICES'),
(20, '3) To ensure relevance of curricular programs to national and international demand by engaging in continuous learning and improvement, and actively participating in academic activities fostering intellectual growth and holistic development, as well as engage in industry collaboration both in local and international.', 'EXCELLENCE IN ACADEMICS AND SERVICES'),
(21, '4) To provide students with quality, holistic and proactive services.', 'EXCELLENCE IN ACADEMICS AND SERVICES'),
(22, '5) To provide sustainable service oriented programs that contribute positively to the improvement of quality of life of the stakeholders.To take a lead in innovation, trends and up-to- date education and leadership practices.', 'EXCELLENCE IN ACADEMICS AND SERVICES'),
(23, '1) To develop a comprehensive understanding of potential future scenarios to proactively prepare and adapt institutional strategies.', 'NOVELTY IN PRACTICES'),
(24, '2) To develop and implement novel modalities to improve climate resilience, food security and reduce poverty within communities.', 'NOVELTY IN PRACTICES'),
(25, '3) To streamline and enhance the institution&#039;s transaction processes for increased efficiency by integrating IT solutions.', 'NOVELTY IN PRACTICES'),
(26, '1) To secure widespread institutional recognition, fostering trust, credibility, and relevance within our stakeholders, while advancing our mission and values.', 'UNIVERSITY RANKING AND RECOGNITION'),
(27, '1) Provide students the quality education to develop their cognitive skills and abilities and also attitudes, behaviors and values related to their discipline.', 'PEOPLE'),
(28, '2) Develop gender- sensitive employees who are respectful of human rights and promote gender equality.', 'PEOPLE'),
(29, '3) Provide a mechanism that empowers the University to develop its HRM competencies, system and practices toward advance and strategic HR excellence.', 'PEOPLE'),
(30, '4) Provide HR the benefits pertaining to healthcare, pursuance to higher education and other motivational factors such as commitment, job satisfaction.', 'PEOPLE');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_responsibility_matrix`
--

CREATE TABLE `tbl_responsibility_matrix` (
  `r_matrix_id` int(11) NOT NULL,
  `office_unit` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_responsibility_matrix`
--

INSERT INTO `tbl_responsibility_matrix` (`r_matrix_id`, `office_unit`) VALUES
(1, 'Information Unit'),
(4, 'Research and Development Office'),
(5, 'Legal Office'),
(6, 'External Linkages and International Affairs Office'),
(7, 'Finance Office'),
(8, 'Planning Office'),
(9, 'Project Development Office'),
(11, 'HR Office'),
(12, 'International Affairs Office'),
(13, 'GAD Office'),
(14, 'Human Resource Management Office'),
(15, 'Extension Services Office'),
(16, 'Intellectual Property Management Office'),
(17, 'Student Affairs Services Office'),
(18, 'Vice President for Academic Affairs'),
(19, 'Quality Assurance Office'),
(20, 'Finance Services Division'),
(21, 'Vice President for Administration'),
(22, 'Deans'),
(23, 'Program Heads'),
(24, 'Faculty'),
(25, 'Project Leaders'),
(26, 'Extension Project Leaders'),
(27, 'Deans and Directors');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sdp`
--

CREATE TABLE `tbl_sdp` (
  `sdp_id` int(11) NOT NULL,
  `objectives_id` int(11) DEFAULT NULL,
  `kpi` varchar(255) DEFAULT NULL,
  `initiatives` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_sdp`
--

INSERT INTO `tbl_sdp` (`sdp_id`, `objectives_id`, `kpi`, `initiatives`) VALUES
(3, 1, 'Research Collaborations with Agencies and Academic Institutions', 'MOAs and MOUs\r\nSigning for research\r\ncollaborations with\r\nagencies and\r\nacademic institution'),
(9, 1, 'Number of International Research/Creative Collaborations', 'MOAs and MOUs\r\nSigning for\r\ninternational\r\nresearch/creative\r\noutputs'),
(10, 1, 'Research Fund Utilization Rate', 'Conduct routine\r\nchecks on the actual\r\nusage of the\r\nallocated research\r\nfunds'),
(11, 1, 'Research Laboratories/Centers established; Complete with laboratory components', 'Augment research\r\ninitiatives by\r\nproviding dedicated\r\nlaboratories and\r\ncenters for science\r\nand technology and\r\nsocial development\r\nby allocating funds\r\nfrom internal or\r\nexternal source');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sdp_responsibility`
--

CREATE TABLE `tbl_sdp_responsibility` (
  `s_responsibility_id` int(11) NOT NULL,
  `sdp_id` int(11) NOT NULL,
  `r_matrix_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_sdp_responsibility`
--

INSERT INTO `tbl_sdp_responsibility` (`s_responsibility_id`, `sdp_id`, `r_matrix_id`) VALUES
(91, 3, 6),
(92, 3, 5),
(93, 3, 4),
(104, 10, 7),
(105, 10, 23),
(106, 10, 4),
(112, 11, 7),
(113, 11, 8),
(114, 11, 9),
(115, 11, 4),
(116, 9, 22),
(117, 9, 6),
(118, 9, 24),
(119, 9, 5),
(120, 9, 23);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_target`
--

CREATE TABLE `tbl_target` (
  `target_id` int(11) NOT NULL,
  `sdp_id` int(11) NOT NULL,
  `target_value` decimal(12,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `value_type` enum('Percentage','Count','Index','Other') DEFAULT 'Other',
  `quarter` enum('Q1','Q2','Q3','Q4') DEFAULT NULL,
  `year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_target`
--

INSERT INTO `tbl_target` (`target_id`, `sdp_id`, `target_value`, `description`, `value_type`, `quarter`, `year`) VALUES
(41, 3, 2.00, 'At least 2 researches conducted in collaboration with agencies and academic institutions', 'Count', 'Q1', '2025'),
(42, 3, 2.00, 'At least 2 researches conducted in collaboration with agencies and academic institutions', 'Count', 'Q2', '2025'),
(43, 3, 2.00, 'At least 2 researches conducted in collaboration with agencies and academic institutions', 'Count', 'Q3', '2025'),
(44, 3, 1.00, 'At least 2 researches conducted in collaboration with agencies and academic institutions', 'Count', 'Q4', '2025'),
(49, 10, 96.00, 'At least 96% of the research fund should be utilized', 'Percentage', 'Q4', '2025'),
(51, 11, 1.00, 'At least 1 research laboratory/center for science and technology and 1 for social development are established', 'Count', NULL, '2025'),
(52, 9, 1.00, 'At least 1 research/creative collaboration (International)', 'Count', NULL, '2025');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Faculty','Staff','Dean','Director') DEFAULT 'Staff',
  `created_at` datetime DEFAULT current_timestamp(),
  `r_matrix_id` int(11) DEFAULT NULL COMMENT 'office'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `first_name`, `middle_name`, `last_name`, `position`, `username`, `password`, `role`, `created_at`, `r_matrix_id`) VALUES
(3, 'Sys', NULL, 'John', 'System Admin', 'admin', '$2y$10$ofq8sNB07Buq9CMWszNBoukK0EEGeeT99HTxZkddR1fGE48JHGIOG', 'Admin', '2025-08-05 08:30:59', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_accomplishment`
--
ALTER TABLE `tbl_accomplishment`
  ADD PRIMARY KEY (`accomplishment_id`),
  ADD KEY `sdp_id` (`sdp_id`),
  ADD KEY `r_matrix_id` (`r_matrix_id`),
  ADD KEY `fk_accomplishment_target` (`target_id`);

--
-- Indexes for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_app_setting`
--
ALTER TABLE `tbl_app_setting`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `tbl_objectives`
--
ALTER TABLE `tbl_objectives`
  ADD PRIMARY KEY (`objectives_id`);

--
-- Indexes for table `tbl_responsibility_matrix`
--
ALTER TABLE `tbl_responsibility_matrix`
  ADD PRIMARY KEY (`r_matrix_id`);

--
-- Indexes for table `tbl_sdp`
--
ALTER TABLE `tbl_sdp`
  ADD PRIMARY KEY (`sdp_id`),
  ADD KEY `objectives_id` (`objectives_id`);

--
-- Indexes for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  ADD PRIMARY KEY (`s_responsibility_id`),
  ADD KEY `sdp_id` (`sdp_id`),
  ADD KEY `r_matrix_id` (`r_matrix_id`);

--
-- Indexes for table `tbl_target`
--
ALTER TABLE `tbl_target`
  ADD PRIMARY KEY (`target_id`),
  ADD KEY `sdp_id` (`sdp_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `r_matrix_id` (`r_matrix_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_accomplishment`
--
ALTER TABLE `tbl_accomplishment`
  MODIFY `accomplishment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `tbl_app_setting`
--
ALTER TABLE `tbl_app_setting`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_objectives`
--
ALTER TABLE `tbl_objectives`
  MODIFY `objectives_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tbl_responsibility_matrix`
--
ALTER TABLE `tbl_responsibility_matrix`
  MODIFY `r_matrix_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `tbl_sdp`
--
ALTER TABLE `tbl_sdp`
  MODIFY `sdp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  MODIFY `s_responsibility_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `tbl_target`
--
ALTER TABLE `tbl_target`
  MODIFY `target_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_accomplishment`
--
ALTER TABLE `tbl_accomplishment`
  ADD CONSTRAINT `fk_accomplishment_target` FOREIGN KEY (`target_id`) REFERENCES `tbl_target` (`target_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_accomplishment_ibfk_1` FOREIGN KEY (`sdp_id`) REFERENCES `tbl_sdp` (`sdp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_accomplishment_ibfk_2` FOREIGN KEY (`r_matrix_id`) REFERENCES `tbl_responsibility_matrix` (`r_matrix_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  ADD CONSTRAINT `tbl_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_sdp`
--
ALTER TABLE `tbl_sdp`
  ADD CONSTRAINT `tbl_sdp_ibfk_1` FOREIGN KEY (`objectives_id`) REFERENCES `tbl_objectives` (`objectives_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  ADD CONSTRAINT `tbl_sdp_responsibility_ibfk_1` FOREIGN KEY (`sdp_id`) REFERENCES `tbl_sdp` (`sdp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_sdp_responsibility_ibfk_2` FOREIGN KEY (`r_matrix_id`) REFERENCES `tbl_responsibility_matrix` (`r_matrix_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_target`
--
ALTER TABLE `tbl_target`
  ADD CONSTRAINT `tbl_target_ibfk_1` FOREIGN KEY (`sdp_id`) REFERENCES `tbl_sdp` (`sdp_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD CONSTRAINT `fk_user_rmatrix_1` FOREIGN KEY (`r_matrix_id`) REFERENCES `tbl_responsibility_matrix` (`r_matrix_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
