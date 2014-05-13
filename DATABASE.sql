# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.37-0ubuntu0.12.04.1)
# Database: bzion
# Generation Time: 2014-05-13 04:43:58 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table banned_ips
# ------------------------------------------------------------

CREATE TABLE `banned_ips` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ban_id` int(11) NOT NULL,
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table bans
# ------------------------------------------------------------

CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL,
  `expiration` datetime NOT NULL,
  `server_message` varchar(150) DEFAULT '',
  `reason` text NOT NULL,
  `allow_server_join` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `author` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table countries
# ------------------------------------------------------------

CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `flag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table groups
# ------------------------------------------------------------

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(50) NOT NULL,
  `last_activity` datetime NOT NULL,
  `creator` int(10) unsigned NOT NULL,
  `status` set('active','disabled','deleted','reported') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table invitations
# ------------------------------------------------------------

CREATE TABLE `invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invited_player` int(10) unsigned NOT NULL,
  `sent_by` int(10) unsigned NOT NULL,
  `team` int(10) unsigned NOT NULL,
  `expiration` datetime NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table matches
# ------------------------------------------------------------

CREATE TABLE `matches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_a` int(10) unsigned NOT NULL,
  `team_b` int(10) unsigned NOT NULL,
  `team_a_points` int(11) NOT NULL,
  `team_b_points` int(11) NOT NULL,
  `team_a_players` varchar(256) DEFAULT NULL,
  `team_b_players` varchar(256) DEFAULT NULL,
  `team_a_elo_new` int(10) unsigned NOT NULL,
  `team_b_elo_new` int(10) unsigned NOT NULL,
  `map_played` varchar(100) DEFAULT NULL,
  `match_details` text,
  `port` int(10) unsigned DEFAULT NULL,
  `server` varchar(100) DEFAULT NULL,
  `replay_file` varchar(256) DEFAULT NULL,
  `elo_diff` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `duration` int(10) unsigned NOT NULL,
  `entered_by` int(10) unsigned NOT NULL,
  `status` set('entered','disabled','deleted','reported') NOT NULL DEFAULT 'entered',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table messages
# ------------------------------------------------------------

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_to` int(10) unsigned NOT NULL,
  `player_from` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `message` text NOT NULL,
  `status` set('sent','hidden','deleted','reported') NOT NULL DEFAULT 'sent',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table news
# ------------------------------------------------------------

CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `status` set('live','disabled','deleted') NOT NULL DEFAULT 'live',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pages
# ------------------------------------------------------------

CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `alias` varchar(32) DEFAULT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `home` tinyint(4) NOT NULL,
  `status` set('live','disabled','deleted') NOT NULL DEFAULT 'live',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table past_callsigns
# ------------------------------------------------------------

CREATE TABLE `past_callsigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL,
  `username` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table permissions
# ------------------------------------------------------------

CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table player_groups
# ------------------------------------------------------------

CREATE TABLE `player_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL,
  `group` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table player_roles
# ------------------------------------------------------------

CREATE TABLE `player_roles` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `player_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `players` (`id`),
  CONSTRAINT `player_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table players
# ------------------------------------------------------------

CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bzid` varchar(255) NOT NULL,
  `team` int(10) unsigned NOT NULL,
  `username` varchar(32) NOT NULL,
  `alias` varchar(32) DEFAULT NULL,
  `status` set('active','disabled','deleted','reported','banned') NOT NULL DEFAULT 'active',
  `avatar` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `country` int(10) unsigned NOT NULL,
  `timezone` tinyint(4) NOT NULL,
  `joined` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `admin_notes` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bzid` (`bzid`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table role_permission
# ------------------------------------------------------------

CREATE TABLE `role_permission` (
  `role_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  KEY `role_id` (`role_id`),
  KEY `perm_id` (`perm_id`),
  CONSTRAINT `role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `role_permission_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `permissions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table roles
# ------------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `reusable` tinyint(1) NOT NULL DEFAULT '0',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table servers
# ------------------------------------------------------------

CREATE TABLE `servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(50) NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `info` text NOT NULL,
  `updated` datetime NOT NULL,
  `status` set('active','disabled','deleted') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table teams
# ------------------------------------------------------------

CREATE TABLE `teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `alias` varchar(32) DEFAULT NULL,
  `description` text NOT NULL,
  `avatar` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  `elo` int(10) unsigned NOT NULL,
  `activity` float NOT NULL,
  `leader` int(10) unsigned NOT NULL,
  `matches_won` int(10) unsigned NOT NULL,
  `matches_lost` int(10) unsigned NOT NULL,
  `matches_draw` int(10) unsigned NOT NULL,
  `members` int(10) unsigned NOT NULL,
  `status` set('open','closed','disabled','deleted') NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table visits
# ------------------------------------------------------------

CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host` varchar(100) NOT NULL,
  `user_agent` text NOT NULL,
  `referer` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
