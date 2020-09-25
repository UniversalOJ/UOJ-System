-- MySQL dump 10.13  Distrib 5.7.25, for Linux (x86_64)
--
-- Host: localhost    Database: app_uoj233
-- ------------------------------------------------------
-- Server version	5.7.25-0ubuntu0.18.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+08:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `app_uoj233`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `app_uoj233` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `app_uoj233`;

--
-- Table structure for table `best_ac_submissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `best_ac_submissions` (
  `problem_id` int(11) NOT NULL,
  `submitter` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submission_id` int(11) NOT NULL,
  `used_time` int(11) NOT NULL,
  `used_memory` int(11) NOT NULL,
  `tot_size` int(11) NOT NULL,
  `shortest_id` int(11) NOT NULL,
  `shortest_used_time` int(11) NOT NULL,
  `shortest_used_memory` int(11) NOT NULL,
  `shortest_tot_size` int(11) NOT NULL,
  PRIMARY KEY (`problem_id`,`submitter`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `best_ac_submissions`
--

LOCK TABLES `best_ac_submissions` WRITE;
/*!40000 ALTER TABLE `best_ac_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `best_ac_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_time` datetime NOT NULL,
  `poster` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_md` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `zan` int(11) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL,
  `type` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B',
  `is_draft` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs_comments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_time` datetime NOT NULL,
  `poster` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zan` int(11) NOT NULL,
  `reply_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs_comments`
--

LOCK TABLES `blogs_comments` WRITE;
/*!40000 ALTER TABLE `blogs_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `tag` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs_tags`
--

LOCK TABLES `blogs_tags` WRITE;
/*!40000 ALTER TABLE `blogs_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `click_zans`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `click_zans` (
  `type` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` int(11) NOT NULL,
  `val` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type`,`target_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `click_zans`
--

LOCK TABLES `click_zans` WRITE;
/*!40000 ALTER TABLE `click_zans` DISABLE KEYS */;
/*!40000 ALTER TABLE `click_zans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start_time` datetime NOT NULL,
  `last_min` int(11) NOT NULL,
  `player_num` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `extra_config` varchar(200) NOT NULL,
  `zan` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests`
--

LOCK TABLES `contests` WRITE;
/*!40000 ALTER TABLE `contests` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests_asks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests_asks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `post_time` datetime NOT NULL,
  `reply_time` datetime NOT NULL,
  `is_hidden` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests_asks`
--

LOCK TABLES `contests_asks` WRITE;
/*!40000 ALTER TABLE `contests_asks` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests_asks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests_notice`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests_notice` (
  `contest_id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `content` varchar(500) NOT NULL,
  `time` datetime NOT NULL,
  KEY `contest_id` (`contest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests_notice`
--

LOCK TABLES `contests_notice` WRITE;
/*!40000 ALTER TABLE `contests_notice` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests_notice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests_permissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests_permissions` (
  `username` varchar(20) NOT NULL,
  `contest_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`contest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests_permissions`
--

LOCK TABLES `contests_permissions` WRITE;
/*!40000 ALTER TABLE `contests_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests_problems`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests_problems` (
  `problem_id` int(11) NOT NULL,
  `contest_id` int(11) NOT NULL,
  PRIMARY KEY (`problem_id`,`contest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests_problems`
--

LOCK TABLES `contests_problems` WRITE;
/*!40000 ALTER TABLE `contests_problems` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests_problems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests_registrants`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests_registrants` (
  `username` varchar(20) NOT NULL,
  `user_rating` int(11) NOT NULL,
  `contest_id` int(11) NOT NULL,
  `has_participated` tinyint(1) NOT NULL,
  `rank` int(11) NOT NULL,
  PRIMARY KEY (`contest_id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests_registrants`
--

LOCK TABLES `contests_registrants` WRITE;
/*!40000 ALTER TABLE `contests_registrants` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests_registrants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contests_submissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contests_submissions` (
  `contest_id` int(11) NOT NULL,
  `submitter` varchar(20) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `penalty` int(11) NOT NULL,
  PRIMARY KEY (`contest_id`,`submitter`,`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contests_submissions`
--

LOCK TABLES `contests_submissions` WRITE;
/*!40000 ALTER TABLE `contests_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contests_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_test_submissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_test_submissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `problem_id` int(10) unsigned NOT NULL,
  `submit_time` datetime NOT NULL,
  `submitter` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `judge_time` datetime DEFAULT NULL,
  `result` blob NOT NULL,
  `status` varchar(20) NOT NULL,
  `status_details` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_test_submissions`
--

LOCK TABLES `custom_test_submissions` WRITE;
/*!40000 ALTER TABLE `custom_test_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_test_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hacks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hacks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `problem_id` int(10) unsigned NOT NULL,
  `contest_id` int(10) unsigned DEFAULT NULL,
  `submission_id` int(10) unsigned NOT NULL,
  `hacker` varchar(20) NOT NULL,
  `owner` varchar(20) NOT NULL,
  `input` varchar(150) NOT NULL,
  `input_type` char(20) NOT NULL,
  `submit_time` datetime NOT NULL,
  `judge_time` datetime DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `details` blob NOT NULL,
  `is_hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  KEY `is_hidden` (`is_hidden`,`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hacks`
--

LOCK TABLES `hacks` WRITE;
/*!40000 ALTER TABLE `hacks` DISABLE KEYS */;
/*!40000 ALTER TABLE `hacks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `important_blogs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `important_blogs` (
  `blog_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_blogs`
--

LOCK TABLES `important_blogs` WRITE;
/*!40000 ALTER TABLE `important_blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `important_blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `judger_info`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `judger_info` (
  `judger_name` varchar(50) NOT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ip` char(20) NOT NULL,
  PRIMARY KEY (`judger_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `judger_info`
--

LOCK TABLES `judger_info` WRITE;
/*!40000 ALTER TABLE `judger_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `judger_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problems`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `problems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `submission_requirement` text,
  `hackable` tinyint(1) NOT NULL DEFAULT '0',
  `extra_config` varchar(500) NOT NULL DEFAULT '{"view_content_type":"ALL","view_details_type":"ALL"}',
  `zan` int(11) NOT NULL,
  `ac_num` int(11) NOT NULL DEFAULT '0',
  `submit_num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problems`
--

LOCK TABLES `problems` WRITE;
/*!40000 ALTER TABLE `problems` DISABLE KEYS */;
/*!40000 ALTER TABLE `problems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problems_contents`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `problems_contents` (
  `id` int(11) NOT NULL,
  `statement` mediumtext NOT NULL,
  `statement_md` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problems_contents`
--

LOCK TABLES `problems_contents` WRITE;
/*!40000 ALTER TABLE `problems_contents` DISABLE KEYS */;
/*!40000 ALTER TABLE `problems_contents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problems_permissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `problems_permissions` (
  `username` varchar(20) NOT NULL,
  `problem_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problems_permissions`
--

LOCK TABLES `problems_permissions` WRITE;
/*!40000 ALTER TABLE `problems_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `problems_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problems_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `problems_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL,
  `tag` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `problem_id` (`problem_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problems_tags`
--

LOCK TABLES `problems_tags` WRITE;
/*!40000 ALTER TABLE `problems_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `problems_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_requests`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `search_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `remote_addr` varchar(50) NOT NULL,
  `type` enum('search','autocomplete') NOT NULL,
  `cache_id` int(11) NOT NULL,
  `q` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `result` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `remote_addr` (`remote_addr`,`created_at`),
  KEY `created_at` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_requests`
--

LOCK TABLES `search_requests` WRITE;
/*!40000 ALTER TABLE `search_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `problem_id` int(10) unsigned NOT NULL,
  `contest_id` int(10) unsigned DEFAULT NULL,
  `submit_time` datetime NOT NULL,
  `submitter` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `language` varchar(15) NOT NULL,
  `tot_size` int(11) NOT NULL,
  `judge_time` datetime DEFAULT NULL,
  `result` blob NOT NULL,
  `status` varchar(20) NOT NULL,
  `result_error` varchar(20) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `used_time` int(11) NOT NULL DEFAULT '0',
  `used_memory` int(11) NOT NULL DEFAULT '0',
  `is_hidden` tinyint(1) NOT NULL,
  `status_details` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_hidden` (`is_hidden`,`problem_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_info` (
  `usergroup` char(1) NOT NULL DEFAULT 'U',
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` char(32) NOT NULL,
  `svn_password` char(10) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '1500',
  `qq` bigint(20) NOT NULL,
  `sex` char(1) NOT NULL DEFAULT 'U',
  `ac_num` int(11) NOT NULL,
  `register_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `remote_addr` varchar(50) NOT NULL,
  `http_x_forwarded_for` varchar(50) NOT NULL,
  `remember_token` char(60) NOT NULL,
  `motto` varchar(200) NOT NULL,
  PRIMARY KEY (`username`),
  KEY `rating` (`rating`,`username`),
  KEY `ac_num` (`ac_num`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_msg`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_msg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` varchar(20) NOT NULL,
  `receiver` varchar(20) NOT NULL,
  `message` varchar(5000) NOT NULL,
  `send_time` datetime NOT NULL,
  `read_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_msg`
--

LOCK TABLES `user_msg` WRITE;
/*!40000 ALTER TABLE `user_msg` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_msg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_system_msg`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_system_msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiver` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_time` datetime NOT NULL,
  `read_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_system_msg`
--

LOCK TABLES `user_system_msg` WRITE;
/*!40000 ALTER TABLE `user_system_msg` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_system_msg` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
