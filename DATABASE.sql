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
  `iso` char(2) NOT NULL,
  `name` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `iso` (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;

INSERT INTO `countries` (`id`, `iso`, `name`)
VALUES
  (NULL,'UU','Unknown'),
  (NULL,'AF','Afghanistan'),
  (NULL,'AL','Albania'),
  (NULL,'DZ','Algeria'),
  (NULL,'AS','American Samoa'),
  (NULL,'AD','Andorra'),
  (NULL,'AO','Angola'),
  (NULL,'AI','Anguilla'),
  (NULL,'AQ','Antarctica'),
  (NULL,'AG','Antigua and Barbuda'),
  (NULL,'AR','Argentina'),
  (NULL,'AM','Armenia'),
  (NULL,'AW','Aruba'),
  (NULL,'AU','Australia'),
  (NULL,'AT','Austria'),
  (NULL,'AZ','Azerbaijan'),
  (NULL,'BS','Bahamas'),
  (NULL,'BH','Bahrain'),
  (NULL,'BD','Bangladesh'),
  (NULL,'BB','Barbados'),
  (NULL,'BY','Belarus'),
  (NULL,'BE','Belgium'),
  (NULL,'BZ','Belize'),
  (NULL,'BJ','Benin'),
  (NULL,'BM','Bermuda'),
  (NULL,'BT','Bhutan'),
  (NULL,'BO','Bolivia'),
  (NULL,'BA','Bosnia and Herzegovina'),
  (NULL,'BW','Botswana'),
  (NULL,'BV','Bouvet Island'),
  (NULL,'BR','Brazil'),
  (NULL,'IO','British Indian Ocean Territory'),
  (NULL,'BN','Brunei Darussalam'),
  (NULL,'BG','Bulgaria'),
  (NULL,'BF','Burkina Faso'),
  (NULL,'BI','Burundi'),
  (NULL,'KH','Cambodia'),
  (NULL,'CM','Cameroon'),
  (NULL,'CA','Canada'),
  (NULL,'CV','Cape Verde'),
  (NULL,'KY','Cayman Islands'),
  (NULL,'CF','Central African Republic'),
  (NULL,'TD','Chad'),
  (NULL,'CL','Chile'),
  (NULL,'CN','China'),
  (NULL,'CX','Christmas Island'),
  (NULL,'CC','Cocos (Keeling) Islands'),
  (NULL,'CO','Colombia'),
  (NULL,'KM','Comoros'),
  (NULL,'CG','Congo'),
  (NULL,'CD','Congo, the Democratic Republic of the'),
  (NULL,'CK','Cook Islands'),
  (NULL,'CR','Costa Rica'),
  (NULL,'CI','Cote D\'Ivoire'),
  (NULL,'HR','Croatia'),
  (NULL,'CU','Cuba'),
  (NULL,'CY','Cyprus'),
  (NULL,'CZ','Czech Republic'),
  (NULL,'DK','Denmark'),
  (NULL,'DJ','Djibouti'),
  (NULL,'DM','Dominica'),
  (NULL,'DO','Dominican Republic'),
  (NULL,'EC','Ecuador'),
  (NULL,'EG','Egypt'),
  (NULL,'SV','El Salvador'),
  (NULL,'GQ','Equatorial Guinea'),
  (NULL,'ER','Eritrea'),
  (NULL,'EE','Estonia'),
  (NULL,'ET','Ethiopia'),
  (NULL,'FK','Falkland Islands (Malvinas)'),
  (NULL,'FO','Faroe Islands'),
  (NULL,'FJ','Fiji'),
  (NULL,'FI','Finland'),
  (NULL,'FR','France'),
  (NULL,'GF','French Guiana'),
  (NULL,'PF','French Polynesia'),
  (NULL,'TF','French Southern Territories'),
  (NULL,'GA','Gabon'),
  (NULL,'GM','Gambia'),
  (NULL,'GE','Georgia'),
  (NULL,'DE','Germany'),
  (NULL,'GH','Ghana'),
  (NULL,'GI','Gibraltar'),
  (NULL,'GR','Greece'),
  (NULL,'GL','Greenland'),
  (NULL,'GD','Grenada'),
  (NULL,'GP','Guadeloupe'),
  (NULL,'GU','Guam'),
  (NULL,'GT','Guatemala'),
  (NULL,'GN','Guinea'),
  (NULL,'GW','Guinea-Bissau'),
  (NULL,'GY','Guyana'),
  (NULL,'HT','Haiti'),
  (NULL,'HM','Heard Island and Mcdonald Islands'),
  (NULL,'VA','Holy See (Vatican City State)'),
  (NULL,'HN','Honduras'),
  (NULL,'HK','Hong Kong'),
  (NULL,'HU','Hungary'),
  (NULL,'IS','Iceland'),
  (NULL,'IN','India'),
  (NULL,'ID','Indonesia'),
  (NULL,'IR','Iran, Islamic Republic of'),
  (NULL,'IQ','Iraq'),
  (NULL,'IE','Ireland'),
  (NULL,'IL','Israel'),
  (NULL,'IT','Italy'),
  (NULL,'JM','Jamaica'),
  (NULL,'JP','Japan'),
  (NULL,'JO','Jordan'),
  (NULL,'KZ','Kazakhstan'),
  (NULL,'KE','Kenya'),
  (NULL,'KI','Kiribati'),
  (NULL,'KP','Korea, Democratic People\'s Republic of'),
  (NULL,'KR','Korea, Republic of'),
  (NULL,'KW','Kuwait'),
  (NULL,'KG','Kyrgyzstan'),
  (NULL,'LA','Lao People\'s Democratic Republic'),
  (NULL,'LV','Latvia'),
  (NULL,'LB','Lebanon'),
  (NULL,'LS','Lesotho'),
  (NULL,'LR','Liberia'),
  (NULL,'LY','Libyan Arab Jamahiriya'),
  (NULL,'LI','Liechtenstein'),
  (NULL,'LT','Lithuania'),
  (NULL,'LU','Luxembourg'),
  (NULL,'MO','Macao'),
  (NULL,'MK','Macedonia, the Former Yugoslav Republic of'),
  (NULL,'MG','Madagascar'),
  (NULL,'MW','Malawi'),
  (NULL,'MY','Malaysia'),
  (NULL,'MV','Maldives'),
  (NULL,'ML','Mali'),
  (NULL,'MT','Malta'),
  (NULL,'MH','Marshall Islands'),
  (NULL,'MQ','Martinique'),
  (NULL,'MR','Mauritania'),
  (NULL,'MU','Mauritius'),
  (NULL,'YT','Mayotte'),
  (NULL,'MX','Mexico'),
  (NULL,'FM','Micronesia, Federated States of'),
  (NULL,'MD','Moldova, Republic of'),
  (NULL,'MC','Monaco'),
  (NULL,'MN','Mongolia'),
  (NULL,'MS','Montserrat'),
  (NULL,'MA','Morocco'),
  (NULL,'MZ','Mozambique'),
  (NULL,'MM','Myanmar'),
  (NULL,'NA','Namibia'),
  (NULL,'NR','Nauru'),
  (NULL,'NP','Nepal'),
  (NULL,'NL','Netherlands'),
  (NULL,'AN','Netherlands Antilles'),
  (NULL,'NC','New Caledonia'),
  (NULL,'NZ','New Zealand'),
  (NULL,'NI','Nicaragua'),
  (NULL,'NE','Niger'),
  (NULL,'NG','Nigeria'),
  (NULL,'NU','Niue'),
  (NULL,'NF','Norfolk Island'),
  (NULL,'MP','Northern Mariana Islands'),
  (NULL,'NO','Norway'),
  (NULL,'OM','Oman'),
  (NULL,'PK','Pakistan'),
  (NULL,'PW','Palau'),
  (NULL,'PS','Palestinian Territory, Occupied'),
  (NULL,'PA','Panama'),
  (NULL,'PG','Papua New Guinea'),
  (NULL,'PY','Paraguay'),
  (NULL,'PE','Peru'),
  (NULL,'PH','Philippines'),
  (NULL,'PN','Pitcairn'),
  (NULL,'PL','Poland'),
  (NULL,'PT','Portugal'),
  (NULL,'PR','Puerto Rico'),
  (NULL,'QA','Qatar'),
  (NULL,'RE','Reunion'),
  (NULL,'RO','Romania'),
  (NULL,'RU','Russian Federation'),
  (NULL,'RW','Rwanda'),
  (NULL,'SH','Saint Helena'),
  (NULL,'KN','Saint Kitts and Nevis'),
  (NULL,'LC','Saint Lucia'),
  (NULL,'PM','Saint Pierre and Miquelon'),
  (NULL,'VC','Saint Vincent and the Grenadines'),
  (NULL,'WS','Samoa'),
  (NULL,'SM','San Marino'),
  (NULL,'ST','Sao Tome and Principe'),
  (NULL,'SA','Saudi Arabia'),
  (NULL,'SN','Senegal'),
  (NULL,'CS','Serbia and Montenegro'),
  (NULL,'SC','Seychelles'),
  (NULL,'SL','Sierra Leone'),
  (NULL,'SG','Singapore'),
  (NULL,'SK','Slovakia'),
  (NULL,'SI','Slovenia'),
  (NULL,'SB','Solomon Islands'),
  (NULL,'SO','Somalia'),
  (NULL,'ZA','South Africa'),
  (NULL,'GS','South Georgia and the South Sandwich Islands'),
  (NULL,'ES','Spain'),
  (NULL,'LK','Sri Lanka'),
  (NULL,'SD','Sudan'),
  (NULL,'SR','Suriname'),
  (NULL,'SJ','Svalbard and Jan Mayen'),
  (NULL,'SZ','Swaziland'),
  (NULL,'SE','Sweden'),
  (NULL,'CH','Switzerland'),
  (NULL,'SY','Syrian Arab Republic'),
  (NULL,'TW','Taiwan, Province of China'),
  (NULL,'TJ','Tajikistan'),
  (NULL,'TZ','Tanzania, United Republic of'),
  (NULL,'TH','Thailand'),
  (NULL,'TL','Timor-Leste'),
  (NULL,'TG','Togo'),
  (NULL,'TK','Tokelau'),
  (NULL,'TO','Tonga'),
  (NULL,'TT','Trinidad and Tobago'),
  (NULL,'TN','Tunisia'),
  (NULL,'TR','Turkey'),
  (NULL,'TM','Turkmenistan'),
  (NULL,'TC','Turks and Caicos Islands'),
  (NULL,'TV','Tuvalu'),
  (NULL,'UG','Uganda'),
  (NULL,'UA','Ukraine'),
  (NULL,'AE','United Arab Emirates'),
  (NULL,'GB','United Kingdom'),
  (NULL,'US','United States'),
  (NULL,'UM','United States Minor Outlying Islands'),
  (NULL,'UY','Uruguay'),
  (NULL,'UZ','Uzbekistan'),
  (NULL,'VU','Vanuatu'),
  (NULL,'VE','Venezuela'),
  (NULL,'VN','Viet Nam'),
  (NULL,'VG','Virgin Islands, British'),
  (NULL,'VI','Virgin Islands, U.s.'),
  (NULL,'WF','Wallis and Futuna'),
  (NULL,'EH','Western Sahara'),
  (NULL,'YE','Yemen'),
  (NULL,'ZM','Zambia'),
  (NULL,'ZW','Zimbabwe');

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
  `status` set('published','revision','draft','disabled','deleted') NOT NULL DEFAULT 'published' COMMENT 'The status of the post',
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
	(NULL,'add_user','The ability to create users'),
	(NULL,'edit_user','The ability to edit user profile settings'),
	(NULL,'del_user','The ability to delete a user from the website (deactivate a user)'),
	(NULL,'wipe_user','The ability to entirely wipe a user from the database'),
	(NULL,'add_team','The ability to create a new team'),
	(NULL,'edit_team','The ability to edit any team without being the leader of it'),
	(NULL,'del_team','The ability to mark a team as deleted without being the leader of it'),
	(NULL,'wipe_team','The ability to entirely wipe a team from the database'),
	(NULL,'add_match','The ability to enter a match'),
	(NULL,'edit_match','The ability to edit an exisitng match'),
	(NULL,'del_match','The ability to mark a match as deleted'),
	(NULL,'wipe_match','The ability to wipe a match from the database'),
	(NULL,'add_page','The ability to create new pages for the website as drafts'),
	(NULL,'edit_page','The ability to edit existing pages on the website'),
	(NULL,'del_page','The ability to mark a page as deleted on the website'),
	(NULL,'wipe_page','The ability to wipe a page from the database'),
	(NULL,'publish_page','The ability to publish a page marked as a draft'),
	(NULL,'add_news','The ability to create news content as drafts'),
	(NULL,'publish_news','The ability to publish news articles'),
	(NULL,'edit_news','The abliity to edit news articles written by other people'),
	(NULL,'del_news','The abliity to delete news articles written by the author or other users'),
	(NULL,'wipe_news','The ability to wipe a news article from the database'),
	(NULL,'add_ban','The ability to create a new ban'),
	(NULL,'edit_ban','The ability to edit an existing ban'),
	(NULL,'del_ban','The ability to mark a ban as deleted'),
	(NULL,'wipe_ban','The ability to wipe a ban from the database'),
	(NULL,'add_server','The ability to add a new server to the list of official league servers'),
	(NULL,'edit_server','The ability to edit an existing server'),
	(NULL,'del_server','The ability to mark a server as deleted'),
	(NULL,'wipe_server','The ability to wipe a server from the database'),
	(NULL,'add_role','The ability to create new roles for users'),
	(NULL,'edit_role','The ability to edit existing roles for users'),
	(NULL,'del_role','The ability to mark a role as deleted'),
	(NULL,'wipe_role','The ability to wipe a role from the database'),
	(NULL,'send_pm','The ability to send private messages'),
	(NULL,'view_visitor_log','The ability to see a player''s visits and their IP addresses'),
	(NULL,'view_server_list','The ability to view the available official league servers');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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
	(NULL,1,1),
	(NULL,1,2),
	(NULL,1,3),
	(NULL,1,4),
	(NULL,1,5),
	(NULL,1,6),
	(NULL,1,7),
	(NULL,1,8),
	(NULL,1,9),
	(NULL,1,10),
	(NULL,1,11),
	(NULL,1,12),
	(NULL,1,13),
	(NULL,1,14),
	(NULL,1,15),
	(NULL,1,16),
	(NULL,1,17),
	(NULL,1,18),
	(NULL,1,19),
	(NULL,1,20),
	(NULL,1,21),
	(NULL,1,22),
	(NULL,1,23),
	(NULL,1,24),
	(NULL,1,25),
	(NULL,1,26),
	(NULL,1,27),
	(NULL,1,28),
	(NULL,1,29),
	(NULL,1,30),
	(NULL,1,31),
	(NULL,1,32),
	(NULL,1,33),
	(NULL,1,34),
	(NULL,1,35),
	(NULL,1,36),
	(NULL,1,37),
	(NULL,5,1),
	(NULL,5,2),
	(NULL,5,3),
	(NULL,5,5),
	(NULL,5,6),
	(NULL,5,7),
	(NULL,5,9),
	(NULL,5,10),
	(NULL,5,11),
	(NULL,5,13),
	(NULL,5,14),
	(NULL,5,15),
	(NULL,5,17),
	(NULL,5,18),
	(NULL,5,19),
	(NULL,5,20),
	(NULL,5,21),
	(NULL,5,23),
	(NULL,5,24),
	(NULL,5,25),
	(NULL,5,27),
	(NULL,5,28),
	(NULL,5,29),
	(NULL,5,31),
	(NULL,5,32),
	(NULL,5,33),
	(NULL,5,35),
	(NULL,5,36),
	(NULL,5,37),
	(NULL,2,1),
	(NULL,2,2),
	(NULL,2,3),
	(NULL,2,5),
	(NULL,2,6),
	(NULL,2,7),
	(NULL,2,9),
	(NULL,2,10),
	(NULL,2,11),
	(NULL,2,13),
	(NULL,2,14),
	(NULL,2,15),
	(NULL,2,17),
	(NULL,2,18),
	(NULL,2,19),
	(NULL,2,20),
	(NULL,2,21),
	(NULL,2,23),
	(NULL,2,24),
	(NULL,2,25),
	(NULL,2,35),
	(NULL,2,36),
	(NULL,2,37),
	(NULL,3,5),
	(NULL,3,6),
	(NULL,3,9),
	(NULL,3,10),
	(NULL,3,13),
	(NULL,3,14),
	(NULL,3,18),
	(NULL,3,20),
	(NULL,3,35),
	(NULL,3,36),
	(NULL,3,37),
	(NULL,4,5),
	(NULL,4,9),
	(NULL,4,10),
	(NULL,4,35),
	(NULL,4,37),
	(NULL,6,5),
	(NULL,6,35),
	(NULL,6,37),
	(NULL,7,5),
	(NULL,7,37);

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
	(NULL,'Developer',1,1),
	(NULL,'Administrator',1,1),
	(NULL,'Cop',1,1),
	(NULL,'Referee',1,1),
	(NULL,'System Administrator',1,1),
	(NULL,'Player',1,1),
	(NULL,'Player - No Private Messages',1,1);

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
  `description_md` text NULL COMMENT 'The description of the team written in markdown',
  `description_html` text NULL COMMENT 'The parsed description of the team',
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
