-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 26, 2025 at 02:47 AM
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
  `accomplishment` varchar(255) DEFAULT NULL COMMENT 'Actual value achieved',
  `r_matrix_id` int(11) DEFAULT NULL,
  `evidence` varchar(255) DEFAULT NULL,
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
(10, 'Research Office'),
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
(25, 'Program/Project Leaders'),
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
(3, 1, 'Research Collaborations with Agencies and Academic Institutions', 'MOAs and MOUs\r\nSigning for research\r\ncollaborations with\r\nagencies and\r\nacademic institution');

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
(93, 3, 4);

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
(44, 3, 1.00, 'At least 2 researches conducted in collaboration with agencies and academic institutions', 'Count', 'Q4', '2025');

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
  ADD KEY `r_matrix_id` (`r_matrix_id`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `sdp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_sdp_responsibility`
--
ALTER TABLE `tbl_sdp_responsibility`
  MODIFY `s_responsibility_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `tbl_target`
--
ALTER TABLE `tbl_target`
  MODIFY `target_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
