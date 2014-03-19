  --
  -- Database: `bzion`
  --

  -- --------------------------------------------------------

  --
  -- Table structure for table `bans`
  --

  CREATE TABLE IF NOT EXISTS `bans` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player` int(10) unsigned NOT NULL,
    `ip_address` varchar(15) NOT NULL,
    `expiration` datetime NOT NULL,
    `reason` varchar(200) NOT NULL,
    `created` datetime NOT NULL,
    `updated` datetime NOT NULL,
    `author` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `countries`
  --

  CREATE TABLE IF NOT EXISTS `countries` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `flag` varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `groups`
  --

  CREATE TABLE IF NOT EXISTS `groups` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `subject` varchar(50) NOT NULL,
    `last_activity` datetime NOT NULL,
    `creator` int(10) unsigned NOT NULL,
    `status` set('active', 'disabled', 'deleted', 'reported') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `invitations`
  --

  CREATE TABLE IF NOT EXISTS `invitations` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `invited_player` int(10) unsigned NOT NULL,
    `sent_by` int(10) unsigned NOT NULL,
    `team` int(10) unsigned NOT NULL,
    `expiration` datetime NOT NULL,
    `text` text NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `messages`
  --

  CREATE TABLE IF NOT EXISTS `messages` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `group_to` int(10) unsigned NOT NULL,
    `player_from` int(10) unsigned NOT NULL,
    `timestamp` datetime NOT NULL,
    `message` text NOT NULL,
    `status` set('sent', 'hidden', 'deleted', 'reported') NOT NULL DEFAULT 'sent',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `matches`
  --

  CREATE TABLE IF NOT EXISTS `matches` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `team_a` int(10) unsigned NOT NULL,
    `team_b` int(10) unsigned NOT NULL,
    `team_a_points` int(11) NOT NULL,
    `team_b_points` int(11) NOT NULL,
    `team_a_players` varchar(256) NULL,
    `team_b_players` varchar(256) NULL,
    `team_a_elo_new` int(10) unsigned NOT NULL,
    `team_b_elo_new` int(10) unsigned NOT NULL,
    `map_played` varchar(100) NULL,
    `match_details` text NULL,
    `port` int(10) unsigned NULL,
    `server` varchar(100) NULL,
    `replay_file` varchar(256) NULL,
    `elo_diff` int(10) unsigned NOT NULL,
    `timestamp` datetime NOT NULL,
    `updated` datetime NOT NULL,
    `duration` int(10) unsigned NOT NULL,
    `entered_by` int(10) unsigned NOT NULL,
    `status` set('entered', 'disabled', 'deleted', 'reported') NOT NULL DEFAULT 'entered',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `news`
  --

  CREATE TABLE IF NOT EXISTS `news` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `subject` varchar(100) NOT NULL,
    `content` text NOT NULL,
    `created` datetime NOT NULL,
    `updated` datetime NOT NULL,
    `author` int(10) unsigned NOT NULL,
    `status` set('live', 'disabled', 'deleted') NOT NULL DEFAULT 'live',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `pages`
  --

  CREATE TABLE IF NOT EXISTS `pages` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(32) NOT NULL,
    `alias` varchar(32) DEFAULT NULL,
    `content` text NOT NULL,
    `created` datetime NOT NULL,
    `updated` datetime NOT NULL,
    `author` int(10) unsigned NOT NULL,
    `home` tinyint(4) NOT NULL,
    `status` set('live', 'disabled', 'deleted') NOT NULL DEFAULT 'live',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `permissions`
  --

  CREATE TABLE IF NOT EXISTS `past_callsigns` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player` int(10) unsigned NOT NULL,
    `username` varchar(32) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `permissions`
  --

  CREATE TABLE IF NOT EXISTS `permissions` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `add_user` tinyint(4) NOT NULL,
    `edit_user` tinyint(4) NOT NULL,
    `del_user` tinyint(4) NOT NULL,
    `add_team` tinyint(4) NOT NULL,
    `edit_team` tinyint(4) NOT NULL,
    `del_team` tinyint(4) NOT NULL,
    `add_match` tinyint(4) NOT NULL,
    `edit_match` tinyint(4) NOT NULL,
    `del_match` tinyint(4) NOT NULL,
    `add_page` tinyint(4) NOT NULL,
    `edit_page` tinyint(4) NOT NULL,
    `del_page` tinyint(4) NOT NULL,
    `add_news` tinyint(4) NOT NULL,
    `edit_news` tinyint(4) NOT NULL,
    `del_news` tinyint(4) NOT NULL,
    `add_ban` tinyint(4) NOT NULL,
    `edit_ban` tinyint(4) NOT NULL,
    `del_ban` tinyint(4) NOT NULL,
    `add_server` tinyint(4) NOT NULL,
    `edit_server` tinyint(4) NOT NULL,
    `del_server` tinyint(4) NOT NULL,
    `add_perm` tinyint(4) NOT NULL,
    `edit_perm` tinyint(4) NOT NULL,
    `del_perm` tinyint(4) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `players`
  --

  CREATE TABLE IF NOT EXISTS `players` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `bzid` varchar(255) NOT NULL,
    `team` int(10) unsigned NOT NULL,
    `username` varchar(32) NOT NULL,
    `alias` varchar(32) DEFAULT NULL,
    `status` set('active', 'disabled', 'deleted', 'reported', 'banned') NOT NULL DEFAULT 'active',
    `access` tinyint(4) NOT NULL,
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

  -- --------------------------------------------------------

  --
  -- Table structure for table `player_groups`
  --

  CREATE TABLE IF NOT EXISTS `player_groups` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player` int(10) unsigned NOT NULL,
    `group` int(10) unsigned NOT NULL,
    `status` set('saved', 'archived', 'deleted') NOT NULL DEFAULT 'saved',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `servers`
  --

  CREATE TABLE IF NOT EXISTS `servers` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `address` varchar(50) NOT NULL,
    `owner` int(10) unsigned NOT NULL,
    `info` text NOT NULL,
    `updated` datetime NOT NULL,
    `status` set('active', 'disabled', 'deleted') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `teams`
  --

  CREATE TABLE IF NOT EXISTS `teams` (
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
    `status` set('open', 'closed', 'disabled', 'deleted') NOT NULL DEFAULT 'open',
    PRIMARY KEY (`id`),
    UNIQUE KEY `alias` (`alias`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

  --
  -- Table structure for table `visits`
  --

  CREATE TABLE IF NOT EXISTS `visits` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `player` int(10) unsigned NOT NULL,
    `ip` varchar(15) NOT NULL,
    `host` varchar(100) NOT NULL,
    `user_agent` text NOT NULL,
    `referer` varchar(200) NOT NULL,
    `timestamp` datetime NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  -- --------------------------------------------------------

