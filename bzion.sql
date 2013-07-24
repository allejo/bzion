-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 68.178.138.79
-- Generation Time: Apr 10, 2013 at 04:32 PM
-- Server version: 5.0.96
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `sjvbzion`
--

-- --------------------------------------------------------

--
-- Table structure for table `bzion_countries`
--

CREATE TABLE `bzion_countries` (
  `id` int(3) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_group_perms`
--

CREATE TABLE `bzion_group_perms` (
  `bzbb_group` varchar(50) NOT NULL,
  `allow_add_news` tinyint(1) NOT NULL,
  `allow_edit_news` tinyint(1) NOT NULL,
  `allow_delete_news` tinyint(1) NOT NULL,
  `allow_edit_static_pages` tinyint(1) NOT NULL,
  `allow_add_bans` tinyint(1) NOT NULL,
  `allow_edit_bans` tinyint(1) NOT NULL,
  `allow_delete_bans` tinyint(1) NOT NULL,
  `allow_add_messages` tinyint(1) NOT NULL,
  `allow_delete_messages` tinyint(1) NOT NULL,
  `allow_kick_any_team_members` tinyint(1) NOT NULL,
  `allow_edit_any_team_profile` tinyint(1) NOT NULL,
  `allow_delete_any_team` tinyint(1) NOT NULL,
  `allow_invite_in_any_team` tinyint(1) NOT NULL,
  `allow_reactivate_teams` tinyint(1) NOT NULL,
  `allow_edit_any_user_profile` tinyint(1) NOT NULL,
  `allow_add_admin_comments_to_user_profile` tinyint(1) NOT NULL,
  `allow_ban_any_user` tinyint(1) NOT NULL,
  `allow_view_user_visits` tinyint(1) NOT NULL,
  `allow_add_match` tinyint(1) NOT NULL,
  `allow_edit_match` tinyint(1) NOT NULL,
  `allow_delete_match` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_matches`
--

CREATE TABLE `bzion_matches` (
  `mID` int(11) NOT NULL auto_increment,
  `uID` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `duration` int(11) NOT NULL,
  `server` varchar(100) NOT NULL,
  `teamOne_id` int(11) NOT NULL,
  `teamOne_points` int(11) NOT NULL,
  `teamOne_newScore` int(11) NOT NULL,
  `teamTwo_id` int(11) NOT NULL,
  `teamTwo_points` int(11) NOT NULL,
  `teamTwo_newScore` int(11) NOT NULL,
  PRIMARY KEY  (`mID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_messages`
--

CREATE TABLE `bzion_messages` (
  `mID` int(11) NOT NULL auto_increment COMMENT 'The unique message ID',
  `recipient` int(11) NOT NULL COMMENT 'The person the message is intended for.',
  `sender` int(11) NOT NULL COMMENT 'The person who sent the message.',
  `sentTime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'The time the message was sent',
  `readTime` timestamp NULL default NULL COMMENT 'The time a message was read by the player who opened it.',
  `read` tinyint(1) NOT NULL default '0' COMMENT 'Whether or not a message has been read.',
  `message` text NOT NULL COMMENT 'The actual message content',
  PRIMARY KEY  (`mID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_news`
--

CREATE TABLE `bzion_news` (
  `nID` int(11) NOT NULL,
  `authorID` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `bb_content` text NOT NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_pages`
--

CREATE TABLE `bzion_pages` (
  `page_name` varchar(250) NOT NULL,
  `content` text NOT NULL,
  `last_edit` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `last_author` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_players`
--

CREATE TABLE `bzion_players` (
  `uID` int(11) NOT NULL auto_increment,
  `bzID` int(11) NOT NULL,
  `teamID` int(11) NOT NULL,
  `callsign` varchar(60) NOT NULL,
  `location` int(3) NOT NULL,
  `join_date` datetime NOT NULL,
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL,
  `banned` tinyint(1) NOT NULL,
  PRIMARY KEY  (`uID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3601 ;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_teams`
--

CREATE TABLE `bzion_teams` (
  `tID` int(11) NOT NULL,
  `leaderID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `description` text NOT NULL,
  `logo` varchar(250) NOT NULL,
  `wins` int(11) NOT NULL,
  `ties` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `score` int(5) NOT NULL,
  `member_count` int(3) NOT NULL,
  `activity` float NOT NULL,
  `active` tinyint(1) NOT NULL,
  `open` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bzion_visits`
--

CREATE TABLE `bzion_visits` (
  `uID` int(11) NOT NULL,
  `IP` varchar(19) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
