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
  `expired` tinyint(1) NOT NULL DEFAULT '0',
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
  `category` int(11) NOT NULL DEFAULT '1',
  `subject` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `editor` int(10) unsigned NOT NULL,
  `status` set('published','draft','deleted') NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table news_categories
# ------------------------------------------------------------

CREATE TABLE `news_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) DEFAULT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `protected` tinyint(1) NOT NULL,
  `status` set('enabled','disabled','deleted') NOT NULL DEFAULT 'enabled',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `news_categories` WRITE;
/*!40000 ALTER TABLE `news_categories` DISABLE KEYS */;

INSERT INTO `news_categories` (`id`, `alias`, `name`, `protected`, `status`)
VALUES
  (1,'uncategorized','Uncategorized',1,'live');

/*!40000 ALTER TABLE `news_categories` ENABLE KEYS */;
UNLOCK TABLES;



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

INSERT INTO `permissions` (`id`, `name`, `description`)
VALUES
       (1,'add_user','The ability to create users'),
       (2,'edit_user','The ability to edit user profile settings'),
       (3,'del_user','The ability to delete a user from the website (deactivate a user)'),
       (4,'wipe_user','The ability to entirely wipe a user from the database'),
       (5,'add_team','The ability to create a new team'),
       (6,'edit_team','The ability to edit any team without being the leader of it'),
       (7,'del_team','The ability to mark a team as deleted without being the leader of it'),
       (8,'wipe_team','The ability to entirely wipe a team from the database'),
       (9,'add_match','The ability to enter a match'),
       (10,'edit_match','The ability to edit an exisitng match'),
       (11,'del_match','The ability to mark a match as deleted'),
       (12,'wipe_match','The ability to wipe a match from the database'),
       (13,'add_page','The ability to create new pages for the website as drafts'),
       (14,'edit_page','The ability to edit existing pages on the website'),
       (15,'del_page','The ability to mark a page as deleted on the website'),
       (16,'wipe_page','The ability to wipe a page from the database'),
       (17,'publish_page','The ability to publish a page marked as a draft'),
       (18,'add_news','The ability to create news content as drafts'),
       (19,'publish_news','The ability to publish news articles'),
       (20,'edit_news','The abliity to edit news articles written by other people'),
       (21,'del_news','The abliity to delete news articles written by the author or other users'),
       (22,'wipe_news','The ability to wipe a news article from the database'),
       (23,'add_ban','The ability to create a new ban'),
       (24,'edit_ban','The ability to edit an existing ban'),
       (25,'del_ban','The ability to mark a ban as deleted'),
       (26,'wipe_ban','The ability to wipe a ban from the database'),
       (27,'add_server','The ability to add a new server to the list of official league servers'),
       (28,'edit_server','The ability to edit an existing server'),
       (29,'del_server','The ability to mark a server as deleted'),
       (30,'wipe_server','The ability to wipe a server from the database'),
       (31,'add_role','The ability to create new roles for users'),
       (32,'edit_role','The ability to edit existing roles for users'),
       (33,'del_role','The ability to mark a role as deleted'),
       (34,'wipe_role','The ability to wipe a role from the database'),
       (35,'send_pm','The ability to send private messages'),
       (36,'view_visitor_log','The ability to see a player\'s visits and their IP addresses'),
       (37,'view_server_list','The ability to view the available official league servers');

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
  CONSTRAINT `player_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
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

INSERT INTO `role_permission` (`role_id`, `perm_id`)
VALUES
       (1,1),
       (1,2),
       (1,3),
       (1,4),
       (1,5),
       (1,6),
       (1,7),
       (1,8),
       (1,9),
       (1,10),
       (1,11),
       (1,12),
       (1,13),
       (1,14),
       (1,15),
       (1,16),
       (1,17),
       (1,18),
       (1,19),
       (1,20),
       (1,21),
       (1,22),
       (1,23),
       (1,24),
       (1,25),
       (1,26),
       (1,27),
       (1,28),
       (1,29),
       (1,30),
       (1,31),
       (1,32),
       (1,33),
       (1,34),
       (1,35),
       (1,36),
       (1,37),
       (5,1),
       (5,2),
       (5,3),
       (5,5),
       (5,6),
       (5,7),
       (5,9),
       (5,10),
       (5,11),
       (5,13),
       (5,14),
       (5,15),
       (5,17),
       (5,18),
       (5,19),
       (5,20),
       (5,21),
       (5,23),
       (5,24),
       (5,25),
       (5,27),
       (5,28),
       (5,29),
       (5,31),
       (5,32),
       (5,33),
       (5,35),
       (5,36),
       (5,37),
       (2,1),
       (2,2),
       (2,3),
       (2,5),
       (2,6),
       (2,7),
       (2,9),
       (2,10),
       (2,11),
       (2,13),
       (2,14),
       (2,15),
       (2,17),
       (2,18),
       (2,19),
       (2,20),
       (2,21),
       (2,23),
       (2,24),
       (2,25),
       (2,35),
       (2,36),
       (2,37),
       (3,5),
       (3,6),
       (3,9),
       (3,10),
       (3,13),
       (3,14),
       (3,18),
       (3,20),
       (3,35),
       (3,36),
       (3,37),
       (4,5),
       (4,9),
       (4,10),
       (4,35),
       (4,37),
       (6,5),
       (6,35),
       (6,37),
       (7,5),
       (7,37);


# Dump of table roles
# ------------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `reusable` tinyint(1) NOT NULL DEFAULT '0',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `roles` (`id`, `name`, `reusable`, `protected`)
VALUES
    (1,'Developer',1,1),
    (2,'Administrator',1,1),
    (3,'Cop',1,1),
    (4,'Referee',1,1),
    (5,'System Administrator',1,1),
    (6,'Player',1,1),
    (7,'Player - No Private Messages',1,1);

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
