-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2026 at 03:24 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tamkeen`
--

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `difficulty` varchar(50) NOT NULL,
  `estimated_time` varchar(50) NOT NULL,
  `points` int(11) NOT NULL,
  `deadline` date DEFAULT NULL,
  `required_skills` varchar(255) NOT NULL,
  `task_file` varchar(255) DEFAULT NULL,
  `task_image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `responsible_person` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `country` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `company_type` varchar(100) NOT NULL,
  `industry` varchar(100) NOT NULL,
  `company_size` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `company_logo` varchar(255) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `hiring_requests`
--

CREATE TABLE `hiring_requests` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `hr_user_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `interview_id` int(11) DEFAULT NULL,
  `hiring_type` enum('full_time','internship','temporary','freelance') NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `hr_shortlists`
--

CREATE TABLE `hr_shortlists` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `hr_user_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `hiring_type` enum('full_time','internship','temporary','freelance') DEFAULT 'full_time',
  `status` enum('new','waiting_reply','replied','hiring_request') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `interviewer_name` varchar(255) NOT NULL,
  `interviewer_phone` varchar(20) NOT NULL,
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `interview_type` enum('online','onsite','phone') NOT NULL,
  `location_or_link` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seekers`
--

CREATE TABLE `job_seekers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(30) NOT NULL,
  `job_title` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_completed` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL,
  `about_me` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_courses`
--

CREATE TABLE `job_seeker_courses` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `course_title` varchar(255) NOT NULL,
  `course_provider` varchar(255) NOT NULL,
  `course_month` varchar(50) NOT NULL,
  `course_year` year(4) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_cvs`
--

CREATE TABLE `job_seeker_cvs` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `cv_file` varchar(255) NOT NULL,
  `cv_language` enum('English','Arabic') NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_educations`
--

CREATE TABLE `job_seeker_educations` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `degree` varchar(100) NOT NULL,
  `institution` varchar(255) NOT NULL,
  `field` varchar(150) NOT NULL,
  `graduation_year` year(4) NOT NULL,
  `grade` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_experiences`
--

CREATE TABLE `job_seeker_experiences` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `job_title` varchar(150) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `company_location` varchar(150) DEFAULT NULL,
  `from_month` varchar(30) NOT NULL,
  `from_year` year(4) NOT NULL,
  `to_month` varchar(30) DEFAULT NULL,
  `to_year` year(4) DEFAULT NULL,
  `is_present` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_languages`
--

CREATE TABLE `job_seeker_languages` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `language_name` varchar(100) NOT NULL,
  `proficiency_level` varchar(50) NOT NULL,
  `reading_level` varchar(50) DEFAULT NULL,
  `writing_level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_profiles`
--

CREATE TABLE `job_seeker_profiles` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `residence_country` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `job_status` varchar(100) DEFAULT NULL,
  `profile_visibility` varchar(100) DEFAULT NULL,
  `years_experience` int(11) DEFAULT NULL,
  `minimum_salary` decimal(10,2) DEFAULT NULL,
  `currency` varchar(50) DEFAULT 'JOD',
  `salary_confidential` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_skills`
--

CREATE TABLE `job_seeker_skills` (
  `id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `skill_name` varchar(150) NOT NULL,
  `skill_level` varchar(50) NOT NULL,
  `years_experience` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `learning_comments`
--

CREATE TABLE `learning_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `learning_joins`
--

CREATE TABLE `learning_joins` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `learning_messages`
--

CREATE TABLE `learning_messages` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `learning_posts`
--

CREATE TABLE `learning_posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT 'post',
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `live_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `live_sessions`
--

CREATE TABLE `live_sessions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `session_time` datetime DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `instructor_name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `difficulty` enum('Beginner','Intermediate','Advanced') NOT NULL,
  `estimated_time` varchar(50) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `required_skills` text NOT NULL,
  `evaluation_criteria` text DEFAULT NULL,
  `model_answer` text DEFAULT NULL,
  `task_file` varchar(255) DEFAULT NULL,
  `task_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending_approval','published','closed','rejected') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `minimum_focus_minutes` int(11) DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `task_attempts`
--

CREATE TABLE `task_attempts` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `started_at` datetime NOT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `disqualified_at` datetime DEFAULT NULL,
  `minimum_minutes` int(11) NOT NULL DEFAULT 2,
  `tab_switch_count` int(11) DEFAULT 0,
  `leave_count` int(11) DEFAULT 0,
  `copy_paste_count` int(11) DEFAULT 0,
  `right_click_count` int(11) DEFAULT 0,
  `violation_count` int(11) DEFAULT 0,
  `answer_text` text DEFAULT NULL,
  `answer_file` varchar(255) DEFAULT NULL,
  `status` enum('in_progress','submitted','disqualified') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL,
  `auto_submitted` tinyint(1) DEFAULT 0,
  `autosave_text` longtext DEFAULT NULL,
  `autosave_updated_at` datetime DEFAULT NULL,
  `drawing_file` varchar(255) DEFAULT NULL,
  `ai_score` int(11) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL,
  `ai_recommendation` text DEFAULT NULL,
  `ai_status` enum('recommended','needs_review','not_recommended') DEFAULT NULL,
  `review_status` enum('pending','approved','rejected','edited') DEFAULT 'pending',
  `final_points` int(11) DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `recommended_to_hr` tinyint(1) DEFAULT 0,
  `recommended_at` datetime DEFAULT NULL,
  `speed_risk` enum('low','medium','high') DEFAULT 'low',
  `integrity_risk` enum('low','medium','high') DEFAULT 'low',
  `integrity_note` text DEFAULT NULL,
  `hr_status` enum('pending','shortlisted','rejected','hired') DEFAULT 'pending',
  `hr_note` text DEFAULT NULL,
  `hr_reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `task_proctoring_events`
--

CREATE TABLE `task_proctoring_events` (
  `id` int(11) NOT NULL,
  `task_attempt_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high') DEFAULT 'medium',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `task_proctoring_reviews`
--

CREATE TABLE `task_proctoring_reviews` (
  `id` int(11) NOT NULL,
  `task_attempt_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `risk_score` int(11) DEFAULT 0,
  `risk_level` enum('low','medium','high') DEFAULT 'low',
  `ai_summary` text DEFAULT NULL,
  `evaluator_decision` enum('pending','accepted','suspicious','rejected') DEFAULT 'pending',
  `evaluator_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(11) NOT NULL,
  `training_type` enum('voluntary','university') NOT NULL,
  `field` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `duration` int(100) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `required_skills` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `status` enum('draft','pending_approval','published','closed','rejected') DEFAULT 'draft',
  `deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `training_applications`
--

CREATE TABLE `training_applications` (
  `id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `university` varchar(255) NOT NULL,
  `major` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `expected_graduation_year` year(4) NOT NULL,
  `cv_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','reviewing','interview','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `training_comments`
--

CREATE TABLE `training_comments` (
  `id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `training_reactions`
--

CREATE TABLE `training_reactions` (
  `id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `reaction_type` varchar(50) DEFAULT 'useful',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `training_saved`
--

CREATE TABLE `training_saved` (
  `id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','hr','task_manager','training_manager') NOT NULL,
  `company_id` int(11) NOT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `hiring_requests`
--
ALTER TABLE `hiring_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hiring_requests_company_id` (`company_id`),
  ADD KEY `idx_hiring_requests_hr_user_id` (`hr_user_id`),
  ADD KEY `idx_hiring_requests_job_seeker_id` (`job_seeker_id`),
  ADD KEY `idx_hiring_requests_interview_id` (`interview_id`);

--
-- Indexes for table `hr_shortlists`
--
ALTER TABLE `hr_shortlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_company_candidate` (`company_id`,`job_seeker_id`),
  ADD KEY `idx_hr_shortlists_hr_user_id` (`hr_user_id`),
  ADD KEY `idx_hr_shortlists_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_interviews_company_id` (`company_id`),
  ADD KEY `idx_interviews_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `qr_token` (`qr_token`);

--
-- Indexes for table `job_seeker_courses`
--
ALTER TABLE `job_seeker_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_seeker_courses_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seeker_cvs`
--
ALTER TABLE `job_seeker_cvs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seeker_educations`
--
ALTER TABLE `job_seeker_educations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_seeker_educations_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seeker_experiences`
--
ALTER TABLE `job_seeker_experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_seeker_experiences_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seeker_languages`
--
ALTER TABLE `job_seeker_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_seeker_languages_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seeker_profiles`
--
ALTER TABLE `job_seeker_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `job_seeker_skills`
--
ALTER TABLE `job_seeker_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_seeker_skills_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `learning_comments`
--
ALTER TABLE `learning_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_learning_comments_post_id` (`post_id`),
  ADD KEY `idx_learning_comments_user_id` (`user_id`);

--
-- Indexes for table `learning_joins`
--
ALTER TABLE `learning_joins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_join` (`post_id`,`user_id`),
  ADD KEY `idx_learning_joins_user_id` (`user_id`);

--
-- Indexes for table `learning_messages`
--
ALTER TABLE `learning_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_learning_messages_post_id` (`post_id`),
  ADD KEY `idx_learning_messages_sender_id` (`sender_id`),
  ADD KEY `idx_learning_messages_receiver_id` (`receiver_id`);

--
-- Indexes for table `learning_posts`
--
ALTER TABLE `learning_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_learning_posts_user_id` (`user_id`);

--
-- Indexes for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_company_id` (`company_id`),
  ADD KEY `idx_tasks_created_by` (`created_by`);

--
-- Indexes for table `task_attempts`
--
ALTER TABLE `task_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_task` (`task_id`,`job_seeker_id`),
  ADD KEY `idx_task_attempts_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `task_proctoring_events`
--
ALTER TABLE `task_proctoring_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_proctoring_events_task_attempt_id` (`task_attempt_id`),
  ADD KEY `idx_task_proctoring_events_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `task_proctoring_reviews`
--
ALTER TABLE `task_proctoring_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_proctoring_reviews_task_attempt_id` (`task_attempt_id`),
  ADD KEY `idx_task_proctoring_reviews_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trainings_company_id` (`company_id`),
  ADD KEY `idx_trainings_created_by` (`created_by`);

--
-- Indexes for table `training_applications`
--
ALTER TABLE `training_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_training_application` (`training_id`,`job_seeker_id`),
  ADD KEY `idx_training_applications_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `training_comments`
--
ALTER TABLE `training_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_training_comments_training_id` (`training_id`),
  ADD KEY `idx_training_comments_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `training_reactions`
--
ALTER TABLE `training_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_training_reaction` (`training_id`,`job_seeker_id`),
  ADD KEY `idx_training_reactions_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `training_saved`
--
ALTER TABLE `training_saved`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_saved_training` (`training_id`,`job_seeker_id`),
  ADD KEY `idx_training_saved_job_seeker_id` (`job_seeker_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hiring_requests`
--
ALTER TABLE `hiring_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hr_shortlists`
--
ALTER TABLE `hr_shortlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_seekers`
--
ALTER TABLE `job_seekers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_seeker_courses`
--
ALTER TABLE `job_seeker_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `job_seeker_cvs`
--
ALTER TABLE `job_seeker_cvs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_seeker_educations`
--
ALTER TABLE `job_seeker_educations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_seeker_experiences`
--
ALTER TABLE `job_seeker_experiences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `job_seeker_languages`
--
ALTER TABLE `job_seeker_languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `job_seeker_profiles`
--
ALTER TABLE `job_seeker_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_seeker_skills`
--
ALTER TABLE `job_seeker_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `learning_comments`
--
ALTER TABLE `learning_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `learning_joins`
--
ALTER TABLE `learning_joins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `learning_messages`
--
ALTER TABLE `learning_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `learning_posts`
--
ALTER TABLE `learning_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `live_sessions`
--
ALTER TABLE `live_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `task_attempts`
--
ALTER TABLE `task_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `task_proctoring_events`
--
ALTER TABLE `task_proctoring_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `task_proctoring_reviews`
--
ALTER TABLE `task_proctoring_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `training_applications`
--
ALTER TABLE `training_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_comments`
--
ALTER TABLE `training_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `training_reactions`
--
ALTER TABLE `training_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `training_saved`
--
ALTER TABLE `training_saved`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hiring_requests`
--
ALTER TABLE `hiring_requests`
  ADD CONSTRAINT `fk_hiring_requests_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hiring_requests_hr_user` FOREIGN KEY (`hr_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hiring_requests_interview` FOREIGN KEY (`interview_id`) REFERENCES `interviews` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hiring_requests_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hr_shortlists`
--
ALTER TABLE `hr_shortlists`
  ADD CONSTRAINT `fk_hr_shortlists_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hr_shortlists_hr_user` FOREIGN KEY (`hr_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hr_shortlists_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `fk_interviews_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_interviews_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_courses`
--
ALTER TABLE `job_seeker_courses`
  ADD CONSTRAINT `fk_job_seeker_courses_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_cvs`
--
ALTER TABLE `job_seeker_cvs`
  ADD CONSTRAINT `fk_job_seeker_cvs_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_educations`
--
ALTER TABLE `job_seeker_educations`
  ADD CONSTRAINT `fk_job_seeker_educations_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_experiences`
--
ALTER TABLE `job_seeker_experiences`
  ADD CONSTRAINT `fk_job_seeker_experiences_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_languages`
--
ALTER TABLE `job_seeker_languages`
  ADD CONSTRAINT `fk_job_seeker_languages_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_profiles`
--
ALTER TABLE `job_seeker_profiles`
  ADD CONSTRAINT `fk_job_seeker_profiles_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_seeker_skills`
--
ALTER TABLE `job_seeker_skills`
  ADD CONSTRAINT `fk_job_seeker_skills_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `learning_comments`
--
ALTER TABLE `learning_comments`
  ADD CONSTRAINT `fk_learning_comments_post` FOREIGN KEY (`post_id`) REFERENCES `learning_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_learning_comments_user` FOREIGN KEY (`user_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `learning_joins`
--
ALTER TABLE `learning_joins`
  ADD CONSTRAINT `fk_learning_joins_post` FOREIGN KEY (`post_id`) REFERENCES `learning_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_learning_joins_user` FOREIGN KEY (`user_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `learning_messages`
--
ALTER TABLE `learning_messages`
  ADD CONSTRAINT `fk_learning_messages_post` FOREIGN KEY (`post_id`) REFERENCES `learning_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_learning_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_learning_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `learning_posts`
--
ALTER TABLE `learning_posts`
  ADD CONSTRAINT `fk_learning_posts_user` FOREIGN KEY (`user_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_attempts`
--
ALTER TABLE `task_attempts`
  ADD CONSTRAINT `fk_task_attempts_job_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `job_seekers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_task_attempts_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
