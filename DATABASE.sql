--
-- Database: `bzion`
--

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(10) unsigned NOT NULL,
  `player` int(10) unsigned NOT NULL,
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
  `id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `flag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE IF NOT EXISTS `invitations` (
  `id` int(10) unsigned NOT NULL,
  `invited_player` int(10) unsigned NOT NULL,
  `team` int(10) unsigned NOT NULL,
  `expiration` datetime NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(10) unsigned NOT NULL,
  `to` int(10) unsigned NOT NULL,
  `from` int(10) unsigned NOT NULL,
  `subject` varchar(100) NOT NULL,
  `timestamp` datetime NOT NULL,
  `message` text NOT NULL,
  `status` set('opened', 'unopened', 'deleted', 'reported') NOT NULL DEFAULT 'unopened',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(10) unsigned NOT NULL,
  `team_a` int(10) unsigned NOT NULL,
  `team_b` int(10) unsigned NOT NULL,
  `team_a_points` int(11) NOT NULL,
  `team_b_points` int(11) NOT NULL,
  `team_a_elo` int(10) unsigned NOT NULL,
  `team_b_elo` int(10) unsigned NOT NULL,
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
  `id` int(10) unsigned NOT NULL,
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
  `id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `status` set('live', 'disabled', 'deleted') NOT NULL DEFAULT 'live',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(10) unsigned NOT NULL,
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
  `bzid` int(10) unsigned NOT NULL,
  `team` int(10) unsigned NOT NULL,
  `username` varchar(32) NOT NULL,
  `status` set('active', 'disabled', 'deleted', 'reportted', 'banned') NOT NULL DEFAULT 'active',
  `access` tinyint(4) NOT NULL,
  `avatar` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `country` int(10) unsigned NOT NULL,
  `timezone` tinyint(4) NOT NULL,
  `joined` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE IF NOT EXISTS `servers` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(50) NOT NULL,
  `owner` int(10) unsigned NOT NULL,
  `info` text NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE IF NOT EXISTS `visits` (
  `id` int(10) unsigned NOT NULL,
  `bzid` int(10) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `host` varchar(100) NOT NULL,
  `referer` varchar(200) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
