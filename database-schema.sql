-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: sql5.freemysqlhosting.net
-- Generation Time: Dec 13, 2021 at 12:55 AM
-- Server version: 5.5.62-0ubuntu0.14.04.1
-- PHP Version: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "-06:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sql5457699`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar`
--

CREATE TABLE `calendar` (
  `id` int(11) NOT NULL,
  `name` varchar(1024) NOT NULL,
  `description` varchar(16384) DEFAULT NULL,
  `color` int(11) NOT NULL,
  `icon` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_access`
--

CREATE TABLE `calendar_access` (
  `userid` int(11) NOT NULL,
  `calendarid` int(11) NOT NULL,
  `accesslevel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_invite`
--

CREATE TABLE `calendar_invite` (
  `fromuser` int(11) NOT NULL,
  `touser` int(11) NOT NULL,
  `calendar` int(11) NOT NULL,
  `accesslevel` int(11) NOT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `calendar` int(11) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `location` varchar(2048) DEFAULT NULL,
  `description` varchar(16384) DEFAULT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event_access`
--

CREATE TABLE `event_access` (
  `userid` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  `accesslevel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event_invite`
--

CREATE TABLE `event_invite` (
  `fromuser` int(11) NOT NULL,
  `touser` int(11) NOT NULL,
  `event` int(11) NOT NULL,
  `accesslevel` int(11) NOT NULL,
  `expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reminder`
--

CREATE TABLE `reminder` (
  `id` int(11) NOT NULL,
  `event` int(11) NOT NULL,
  `offset` int(11) NOT NULL DEFAULT '900' COMMENT 'seconds before',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(1024) NOT NULL UNIQUE,
  `pass` varchar(2048) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar`
--
ALTER TABLE `calendar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendar_access`
--
ALTER TABLE `calendar_access`
  ADD KEY `userid` (`userid`),
  ADD KEY `calendarid` (`calendarid`);

--
-- Indexes for table `calendar_invite`
--
ALTER TABLE `calendar_invite`
  ADD KEY `fromuser` (`fromuser`),
  ADD KEY `touser` (`touser`),
  ADD KEY `calendar` (`calendar`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar` (`calendar`);

--
-- Indexes for table `event_access`
--
ALTER TABLE `event_access`
  ADD KEY `userid` (`userid`),
  ADD KEY `eventid` (`eventid`);

--
-- Indexes for table `event_invite`
--
ALTER TABLE `event_invite`
  ADD KEY `fromuser` (`fromuser`),
  ADD KEY `touser` (`touser`),
  ADD KEY `event` (`event`);

--
-- Indexes for table `reminder`
--
ALTER TABLE `reminder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event` (`event`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar`
--
ALTER TABLE `calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `reminder`
--
ALTER TABLE `reminder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `calendar_access`
--
ALTER TABLE `calendar_access`
  ADD CONSTRAINT `calendar_access_ibfk_2` FOREIGN KEY (`calendarid`) REFERENCES `calendar` (`id`),
  ADD CONSTRAINT `calendar_access_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`);

--
-- Constraints for table `calendar_invite`
--
ALTER TABLE `calendar_invite`
  ADD CONSTRAINT `calendar_invite_ibfk_3` FOREIGN KEY (`calendar`) REFERENCES `calendar` (`id`),
  ADD CONSTRAINT `calendar_invite_ibfk_1` FOREIGN KEY (`fromuser`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `calendar_invite_ibfk_2` FOREIGN KEY (`touser`) REFERENCES `users` (`id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`calendar`) REFERENCES `calendar` (`id`);

--
-- Constraints for table `event_access`
--
ALTER TABLE `event_access`
  ADD CONSTRAINT `event_access_ibfk_2` FOREIGN KEY (`eventid`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `event_access_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`);

--
-- Constraints for table `event_invite`
--
ALTER TABLE `event_invite`
  ADD CONSTRAINT `event_invite_ibfk_3` FOREIGN KEY (`event`) REFERENCES `event` (`id`),
  ADD CONSTRAINT `event_invite_ibfk_1` FOREIGN KEY (`fromuser`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `event_invite_ibfk_2` FOREIGN KEY (`touser`) REFERENCES `users` (`id`);

--
-- Constraints for table `reminder`
--
ALTER TABLE `reminder`
  ADD CONSTRAINT `reminder_ibfk_1` FOREIGN KEY (`event`) REFERENCES `event` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
