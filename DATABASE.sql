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
  `ban_id` int(10) unsigned NOT NULL COMMENT 'The corresponding Ban entry',
  `ip_address` varchar(15) NOT NULL DEFAULT '' COMMENT 'The IP address that was banned, only IPv4 due to BZFlag only supporting IPv4',
  PRIMARY KEY (`id`),
  KEY `ban_id` (`ban_id`),
  CONSTRAINT `banned_ips_ibfk_1` FOREIGN KEY (`ban_id`) REFERENCES `bans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table bans
# ------------------------------------------------------------

CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL COMMENT 'The victim of the ban',
  `expiration` datetime NOT NULL COMMENT 'The timestamp of when the ban will expire',
  `server_message` varchar(150) DEFAULT '' COMMENT 'The ban summary that is displayed when a player is rejected from joining a server',
  `reason` text NOT NULL COMMENT 'The official reason for a ban',
  `allow_server_join` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not to allow players to join servers while banned',
  `created` datetime NOT NULL COMMENT 'The timestamp this ban was created',
  `updated` datetime NOT NULL COMMENT 'The timestamp of the last update for this ban',
  `author` int(10) unsigned NOT NULL COMMENT 'The person who issued the ban',
  PRIMARY KEY (`id`),
  KEY `author` (`author`),
  KEY `player` (`player`),
  CONSTRAINT `bans_ibfk_3` FOREIGN KEY (`player`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bans_ibfk_2` FOREIGN KEY (`author`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table countries
# ------------------------------------------------------------

CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `flag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;

INSERT INTO `countries` (`id`, `name`, `flag`)
VALUES
	(1,'Unknown','none');

/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table groups
# ------------------------------------------------------------

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(50) NOT NULL DEFAULT '' COMMENT 'The subject of a group message',
  `last_activity` datetime NOT NULL COMMENT 'The time the last message was sent to this group',
  `creator` int(10) unsigned NOT NULL COMMENT 'The person who created this group',
  `status` set('active','disabled','deleted','reported') NOT NULL DEFAULT 'active' COMMENT 'The status of the group message',
  PRIMARY KEY (`id`),
  KEY `creator` (`creator`),
  CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`creator`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table invitations
# ------------------------------------------------------------

CREATE TABLE `invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invited_player` int(10) unsigned NOT NULL COMMENT 'The player being invited to join a team',
  `sent_by` int(10) unsigned NOT NULL COMMENT 'The player who sent the invitation',
  `team` int(10) unsigned NOT NULL COMMENT 'The team that the player is being invted to',
  `expiration` datetime NOT NULL COMMENT 'The time the invitation will expire',
  `text` text NOT NULL COMMENT 'The message sent when inviting a player to a team',
  PRIMARY KEY (`id`),
  KEY `invited_player` (`invited_player`),
  KEY `sent_by` (`sent_by`),
  KEY `team` (`team`),
  CONSTRAINT `invitations_ibfk_3` FOREIGN KEY (`team`) REFERENCES `teams` (`id`),
  CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`invited_player`) REFERENCES `players` (`id`),
  CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`sent_by`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table matches
# ------------------------------------------------------------

CREATE TABLE `matches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_a` int(10) unsigned NOT NULL COMMENT 'Team 1 who played in this match',
  `team_b` int(10) unsigned NOT NULL COMMENT 'Team 2 who played in this match',
  `team_a_points` int(11) NOT NULL COMMENT 'Team 1''s points',
  `team_b_points` int(11) NOT NULL COMMENT 'Team 2''s points',
  `team_a_players` varchar(256) DEFAULT NULL COMMENT 'A comma-separated list of BZIDs of players who where on Team 1',
  `team_b_players` varchar(256) DEFAULT NULL COMMENT 'A comma-separated list of BZIDs of players who where on Team 2',
  `team_a_elo_new` int(10) unsigned NOT NULL COMMENT 'The new ELO for Team 1',
  `team_b_elo_new` int(10) unsigned NOT NULL COMMENT 'The new ELO for Team 2',
  `map_played` varchar(100) DEFAULT NULL COMMENT 'The name of the map that was played',
  `match_details` text COMMENT 'JSON data about events that occurred during the match',
  `port` int(10) unsigned DEFAULT NULL COMMENT 'The port the match was held on',
  `server` varchar(100) DEFAULT NULL COMMENT 'The server address the match was held on',
  `replay_file` varchar(256) DEFAULT NULL COMMENT 'The name of the replay file the match was stored on',
  `elo_diff` int(10) unsigned NOT NULL COMMENT 'The difference in ELO to both teams',
  `timestamp` datetime NOT NULL COMMENT 'The timestamp of the match',
  `updated` datetime NOT NULL COMMENT 'The timestamp of when the match was last updated',
  `duration` int(10) unsigned NOT NULL COMMENT 'The duration of the match',
  `entered_by` int(10) unsigned NOT NULL COMMENT 'The ID of the player who inserted this match',
  `status` set('entered','disabled','deleted','reported') NOT NULL DEFAULT 'entered' COMMENT 'The status of the match',
  PRIMARY KEY (`id`),
  KEY `team_a` (`team_a`),
  KEY `team_b` (`team_b`),
  KEY `entered_by` (`entered_by`),
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`entered_by`) REFERENCES `players` (`id`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`team_a`) REFERENCES `teams` (`id`),
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team_b`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table messages
# ------------------------------------------------------------

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_to` int(10) unsigned NOT NULL COMMENT 'The group message this invidividual message is being sent to',
  `player_from` int(10) unsigned NOT NULL COMMENT 'The author of the message',
  `timestamp` datetime NOT NULL COMMENT 'The timestamp of when the message was sent',
  `message` text NOT NULL COMMENT 'The actual message being sent',
  `status` set('sent','hidden','deleted','reported') NOT NULL DEFAULT 'sent' COMMENT 'That status of the message',
  PRIMARY KEY (`id`),
  KEY `group_to` (`group_to`),
  KEY `player_from` (`player_from`),
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`player_from`) REFERENCES `players` (`id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`group_to`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table news
# ------------------------------------------------------------

CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL COMMENT 'The ID of the original news post. If this coulmn is set, then it is a revision',
  `category` int(11) unsigned NOT NULL DEFAULT '1' COMMENT 'The ID of the category this post will use',
  `subject` varchar(100) NOT NULL DEFAULT '' COMMENT 'The subjec of the post',
  `content` text NOT NULL COMMENT 'The content of the psot',
  `created` datetime NOT NULL COMMENT 'The timestamp the post was created',
  `updated` datetime NOT NULL COMMENT 'The timestamp of when the post was last updated',
  `author` int(10) unsigned NOT NULL COMMENT 'The ID of the author',
  `editor` int(10) unsigned NOT NULL COMMENT 'The ID of the last editor',
  `status` set('live','revision','disabled','deleted') NOT NULL DEFAULT 'live' COMMENT 'The status of the post',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `author` (`author`),
  KEY `editor` (`editor`),
  CONSTRAINT `news_ibfk_1` FOREIGN KEY (`category`) REFERENCES `news_categories` (`id`),
  CONSTRAINT `news_ibfk_2` FOREIGN KEY (`author`) REFERENCES `players` (`id`),
  CONSTRAINT `news_ibfk_3` FOREIGN KEY (`editor`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table news_categories
# ------------------------------------------------------------

CREATE TABLE `news_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL DEFAULT '' COMMENT 'The URL slug of the category',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT 'The name of the category to be used on the website',
  `protected` tinyint(1) NOT NULL COMMENT 'Whether or not the category is protected from being deleted from the database via PHP',
  `status` set('enabled','deleted') NOT NULL DEFAULT 'enabled' COMMENT 'The status of the category',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pages
# ------------------------------------------------------------

CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'The ID of the original page. If this coulmn is set, then it is a revision',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT 'The name of the page',
  `alias` varchar(32) DEFAULT NULL COMMENT 'The URL slug used for the page',
  `content` text NOT NULL COMMENT 'The content of the page',
  `created` datetime NOT NULL COMMENT 'The timestamp of when the page was created',
  `updated` datetime NOT NULL COMMENT 'The timestamp of when the page was last updated',
  `author` int(10) unsigned NOT NULL COMMENT 'The ID of the author',
  `home` tinyint(4) DEFAULT NULL COMMENT '(Deprecated) Whether or not the page is the home page',
  `status` set('live','revision','disabled','deleted') NOT NULL DEFAULT 'live' COMMENT 'The status of the page',
  PRIMARY KEY (`id`),
  KEY `author` (`author`),
  CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`author`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table past_callsigns
# ------------------------------------------------------------

CREATE TABLE `past_callsigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL COMMENT 'The player ID',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT 'The old username the player used',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `player` (`player`),
  CONSTRAINT `past_callsigns_ibfk_1` FOREIGN KEY (`player`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table permissions
# ------------------------------------------------------------

CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT 'The name of the permission',
  `description` varchar(256) DEFAULT NULL COMMENT 'The description of the permission',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;

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
	(36,'view_visitor_log','The ability to see a player''s visits and their IP addresses'),
	(37,'view_server_list','The ability to view the available official league servers');

/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table player_groups
# ------------------------------------------------------------

CREATE TABLE `player_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(10) unsigned NOT NULL COMMENT 'The player ID',
  `group` int(10) unsigned NOT NULL COMMENT 'The group ID a player belongs to',
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  CONSTRAINT `player_groups_ibfk_1` FOREIGN KEY (`player`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table player_roles
# ------------------------------------------------------------

CREATE TABLE `player_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'The player ID we''re referencing',
  `role_id` int(10) unsigned NOT NULL COMMENT 'The role a player belongs too',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `player_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `player_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table players
# ------------------------------------------------------------

CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bzid` varchar(255) NOT NULL DEFAULT '' COMMENT 'A player''s BZID',
  `team` int(10) unsigned DEFAULT NULL COMMENT 'A player''s team',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT 'A player''s username',
  `alias` varchar(32) DEFAULT NULL COMMENT 'The player''s URL slug that will appear when viewing their profile',
  `status` set('active','disabled','deleted','reported','banned') NOT NULL DEFAULT 'active' COMMENT 'The player''s status',
  `avatar` varchar(200) NOT NULL DEFAULT '' COMMENT 'The URL to the player''s avatar',
  `description` text NOT NULL COMMENT 'The description or biography of a player',
  `country` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The country a player belongs to',
  `timezone` tinyint(4) NOT NULL COMMENT 'The timezone a player belongs to',
  `joined` datetime NOT NULL COMMENT 'The timestamp when the player joined',
  `last_login` datetime DEFAULT NULL COMMENT 'The timestamp a player last logged in',
  `admin_notes` text COMMENT 'Administrative notes about players',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bzid` (`bzid`),
  UNIQUE KEY `alias` (`alias`),
  KEY `team` (`team`),
  KEY `country` (`country`),
  CONSTRAINT `players_ibfk_2` FOREIGN KEY (`country`) REFERENCES `countries` (`id`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table role_permission
# ------------------------------------------------------------

CREATE TABLE `role_permission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL COMMENT 'The role a permission is a part of',
  `perm_id` int(10) unsigned NOT NULL COMMENT 'The permission',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `perm_id` (`perm_id`),
  CONSTRAINT `role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permission_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `role_permission` WRITE;
/*!40000 ALTER TABLE `role_permission` DISABLE KEYS */;

INSERT INTO `role_permission` (`id`, `role_id`, `perm_id`)
VALUES
	(1,1,1),
	(2,1,2),
	(3,1,3),
	(4,1,4),
	(5,1,5),
	(6,1,6),
	(7,1,7),
	(8,1,8),
	(9,1,9),
	(10,1,10),
	(11,1,11),
	(12,1,12),
	(13,1,13),
	(14,1,14),
	(15,1,15),
	(16,1,16),
	(17,1,17),
	(18,1,18),
	(19,1,19),
	(20,1,20),
	(21,1,21),
	(22,1,22),
	(23,1,23),
	(24,1,24),
	(25,1,25),
	(26,1,26),
	(27,1,27),
	(28,1,28),
	(29,1,29),
	(30,1,30),
	(31,1,31),
	(32,1,32),
	(33,1,33),
	(34,1,34),
	(35,1,35),
	(36,1,36),
	(37,1,37),
	(38,5,1),
	(39,5,2),
	(40,5,3),
	(41,5,5),
	(42,5,6),
	(43,5,7),
	(44,5,9),
	(45,5,10),
	(46,5,11),
	(47,5,13),
	(48,5,14),
	(49,5,15),
	(50,5,17),
	(51,5,18),
	(52,5,19),
	(53,5,20),
	(54,5,21),
	(55,5,23),
	(56,5,24),
	(57,5,25),
	(58,5,27),
	(59,5,28),
	(60,5,29),
	(61,5,31),
	(62,5,32),
	(63,5,33),
	(64,5,35),
	(65,5,36),
	(66,5,37),
	(67,2,1),
	(68,2,2),
	(69,2,3),
	(70,2,5),
	(71,2,6),
	(72,2,7),
	(73,2,9),
	(74,2,10),
	(75,2,11),
	(76,2,13),
	(77,2,14),
	(78,2,15),
	(79,2,17),
	(80,2,18),
	(81,2,19),
	(82,2,20),
	(83,2,21),
	(84,2,23),
	(85,2,24),
	(86,2,25),
	(87,2,35),
	(88,2,36),
	(89,2,37),
	(90,3,5),
	(91,3,6),
	(92,3,9),
	(93,3,10),
	(94,3,13),
	(95,3,14),
	(96,3,18),
	(97,3,20),
	(98,3,35),
	(99,3,36),
	(100,3,37),
	(101,4,5),
	(102,4,9),
	(103,4,10),
	(104,4,35),
	(105,4,37),
	(106,6,5),
	(107,6,35),
	(108,6,37),
	(109,7,5),
	(110,7,37);

/*!40000 ALTER TABLE `role_permission` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table roles
# ------------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT 'The name of the role',
  `reusable` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the group is reusable, this will be used for players with custom permissions',
  `protected` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not this entry can be deleted via PHP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;

INSERT INTO `roles` (`id`, `name`, `reusable`, `protected`)
VALUES
	(1,'Developer',1,1),
	(2,'Administrator',1,1),
	(3,'Cop',1,1),
	(4,'Referee',1,1),
	(5,'System Administrator',1,1),
	(6,'Player',1,1),
	(7,'Player - No Private Messages',1,1);

/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table servers
# ------------------------------------------------------------

CREATE TABLE `servers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT 'The name of the server',
  `address` varchar(50) NOT NULL DEFAULT '' COMMENT 'The address of the server (e.g. host:port)',
  `owner` int(10) unsigned NOT NULL COMMENT 'The owner of the server',
  `info` text NOT NULL COMMENT 'Information regarding the server',
  `updated` datetime NOT NULL COMMENT 'The timestamp of when the server was last pinged',
  `status` set('active','disabled','deleted') NOT NULL DEFAULT 'active' COMMENT 'The status of the server relative to BZiON',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table teams
# ------------------------------------------------------------

CREATE TABLE `teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT 'The team''s name',
  `alias` varchar(32) DEFAULT NULL COMMENT 'The team''s URL slug for viewing the team''s profile',
  `description` text NOT NULL COMMENT 'The description of the team',
  `avatar` varchar(200) NOT NULL DEFAULT '' COMMENT 'The URL to the team avatar',
  `created` datetime NOT NULL COMMENT 'The timestamp of when the team was created',
  `elo` int(10) unsigned NOT NULL COMMENT 'The ELO of the team',
  `activity` float NOT NULL COMMENT 'The activity of the team',
  `leader` int(10) unsigned NOT NULL COMMENT 'The ID of the leader',
  `matches_won` int(10) unsigned NOT NULL COMMENT 'The amount of matches won',
  `matches_lost` int(10) unsigned NOT NULL COMMENT 'The amount of matches resulting in a draw',
  `matches_draw` int(10) unsigned NOT NULL COMMENT 'The amount of matches lost',
  `members` int(10) unsigned NOT NULL COMMENT 'The amount of members',
  `status` set('open','closed','disabled','deleted') NOT NULL DEFAULT 'open' COMMENT 'The status of the team',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `alias` (`alias`),
  KEY `leader` (`leader`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`leader`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table visits
# ------------------------------------------------------------

CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player` int(11) unsigned NOT NULL COMMENT 'The ID of the player',
  `ip` varchar(46) NOT NULL DEFAULT '' COMMENT 'The IPv4 (or IPv6) of the player',
  `host` varchar(100) NOT NULL DEFAULT '' COMMENT 'The host of the player',
  `user_agent` text NOT NULL COMMENT 'The browser''s user agent',
  `referer` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL COMMENT 'The timestamp this host was used to visit the website',
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`player`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
