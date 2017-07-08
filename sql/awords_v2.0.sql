-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 06, 2017 at 06:26 AM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `awords`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE IF NOT EXISTS `active_sessions` (
  `user_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `type` varchar(15) NOT NULL COMMENT 'session or cookie'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `active_sessions`
--

INSERT INTO `active_sessions` (`user_id`, `session_id`, `type`) VALUES
(1, 'ecd3969137625af4', 'session'),
(1, 'f953c0fa77e46143', 'cookie'),
(1, 'd62095fa79929feb', 'cookie'),
(1, '6eb9904669caab32', 'cookie');

-- --------------------------------------------------------

--
-- Table structure for table `last_projects`
--

CREATE TABLE IF NOT EXISTS `last_projects` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `seen` tinyint(1) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `last_projects`
--

INSERT INTO `last_projects` (`user_id`, `project_id`, `seen`) VALUES
(1, 14, 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `user_id` int(11) NOT NULL,
  `attempts` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_name` varchar(50) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`project_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `user_id`, `project_name`, `date_created`) VALUES
(8, 1, 'Test', '2017-05-31 04:05:09'),
(9, 1, 'Test', '2017-05-31 04:05:44'),
(10, 1, 'New', '2017-05-31 04:13:06'),
(11, 1, 'New', '2017-05-31 04:32:02'),
(12, 1, 'New project', '2017-05-31 04:34:28'),
(13, 1, 'New project', '2017-05-31 04:36:13'),
(14, 1, 'Demo Project', '2017-05-31 17:12:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `joined_date` datetime NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `activition_key` varchar(50) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `activition_key` (`activition_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `joined_date`, `locked`, activation_key) VALUES
(1, 'Muntashir Al-Islam', 'muntashir.islam96@gmail.com', '$2y$10$di/NluMKLEkld4R.PUDlD.WHQpIodOykrtr2VuGGcivZc0MIeBRpq', '2017-05-28 18:21:26', 0, ''),
(4, 'Muntashir Al-Islam', 'muntashir@live.com', '$2y$10$6uRwLola8PKotn8sqPDaPukcmSqOCvofnSxk.SU4nMuwVP2JhaGZO', '2017-05-29 03:23:14', 0, 'null');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `last_projects`
--
ALTER TABLE `last_projects`
  ADD CONSTRAINT `last_projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `last_projects_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
