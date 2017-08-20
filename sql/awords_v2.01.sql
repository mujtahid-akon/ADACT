-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 09, 2017 at 12:50 AM
-- Server version: 5.5.55-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.21

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
(4, 52, 1),
(7, 56, 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=57 ;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `user_id`, `project_name`, `date_created`) VALUES
(48, 4, 'mawtestlite', '2017-06-10 06:04:40'),
(49, 4, 'test', '2017-06-10 06:14:21'),
(50, 4, 'mawtestlite', '2017-06-10 06:24:50'),
(51, 4, 'mawtestlite', '2017-06-10 06:26:07'),
(52, 4, 'mahi', '2017-07-08 11:14:22'),
(53, 7, 'testacc', '2017-07-08 12:51:04'),
(54, 7, 'testzip', '2017-07-08 13:04:59'),
(56, 7, 'temp', '2017-07-08 13:42:03');

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
  `activation_key` varchar(50) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `activation_key` (`activation_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `joined_date`, `locked`, `activation_key`) VALUES
(1, 'Muntashir Al-Islam', 'muntashir.islam96@gmail.com', '$2y$10$di/NluMKLEkld4R.PUDlD.WHQpIodOykrtr2VuGGcivZc0MIeBRpq', '2017-05-28 18:21:26', 0, '67b09ae3b19d0e13'),
(4, 'Mujtahid Akon', 'mujtahid.akon@gmail.com', '9752ad5886739703b40702a2a4315104', '2017-05-29 03:23:14', 0, 'c8e13029dc89412a'),
(7, 'Mahi', 'mahibuet045@gmail.com', '$2y$10$cMsV/F8QTQPENw7J7yj.ve/oFioe88gme7xX3eFJAwZFn0j1ASb7G', '2017-07-08 11:56:22', 0, '11b12bf7c220fbe8');

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
