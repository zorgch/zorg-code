# ************************************************************
# Sequel Ace SQL dump
# Version 20039
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: zorg.ch (MySQL 5.7.40-0ubuntu0.18.04.1)
# Database: zorg_live
# Generation Time: 2022-11-05 09:00:53 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table activities
# ------------------------------------------------------------

CREATE TABLE `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activity_area` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_user_id` int(11) unsigned NOT NULL,
  `owner` int(11) unsigned NOT NULL,
  `activity` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `values` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Enthält alle Activities-Einträge für die Startseite';



# Dump of table activities_areas
# ------------------------------------------------------------

CREATE TABLE `activities_areas` (
  `area` varchar(2) COLLATE latin1_german2_ci NOT NULL,
  `title` varchar(50) COLLATE latin1_german2_ci DEFAULT NULL,
  KEY `area` (`area`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Gibt die verfügbaren Bereiche für die Activities vor';



# Dump of table activities_votes
# ------------------------------------------------------------

CREATE TABLE `activities_votes` (
  `activity_id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL,
  `rating` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  KEY `activity_id` (`activity_id`,`rating`(1))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Enthält die Ratings (Like/Dislike) einzelner Activities';



# Dump of table addle
# ------------------------------------------------------------

CREATE TABLE `addle` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `player1` int(10) unsigned NOT NULL DEFAULT '0',
  `player2` int(10) unsigned NOT NULL DEFAULT '0',
  `score1` int(2) unsigned NOT NULL DEFAULT '0',
  `score2` int(2) unsigned NOT NULL DEFAULT '0',
  `data` varchar(64) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `last_pick_row` int(1) unsigned NOT NULL DEFAULT '0',
  `last_pick_data` int(2) unsigned NOT NULL DEFAULT '0',
  `nextturn` int(1) unsigned NOT NULL DEFAULT '1',
  `nextrow` int(1) unsigned NOT NULL DEFAULT '0',
  `finish` int(1) unsigned NOT NULL DEFAULT '0',
  `dwz_dif` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `player1` (`player1`),
  KEY `player2` (`player2`),
  KEY `finish` (`finish`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table addle_dwz
# ------------------------------------------------------------

CREATE TABLE `addle_dwz` (
  `rank` int(10) NOT NULL DEFAULT '9999',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL DEFAULT '0',
  `prev_score` int(10) NOT NULL DEFAULT '0',
  `prev_rank` int(10) NOT NULL DEFAULT '9999',
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table aficks
# ------------------------------------------------------------

CREATE TABLE `aficks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wort` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `typ` tinyint(2) NOT NULL DEFAULT '0',
  `counter` int(11) NOT NULL DEFAULT '0',
  `wort_user_id` int(11) NOT NULL DEFAULT '0',
  `note` float(9,8) NOT NULL DEFAULT '4.00000000',
  `votes` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `wort_user_id` (`wort_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table aficks_log
# ------------------------------------------------------------

CREATE TABLE `aficks_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `afick_am` text COLLATE latin1_german1_ci NOT NULL,
  `afick_user` text COLLATE latin1_german1_ci NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table anficker_anficks
# ------------------------------------------------------------

CREATE TABLE `anficker_anficks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text COLLATE latin1_german1_ci NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `note` float(9,8) DEFAULT '4.00000000',
  `votes` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table anficker_log
# ------------------------------------------------------------

CREATE TABLE `anficker_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `anficker_id` int(11) NOT NULL DEFAULT '0',
  `anfick_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table books
# ------------------------------------------------------------

CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `titel_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `autor` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `verlag` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `isbn` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `preis` float(10,2) NOT NULL DEFAULT '0.00',
  `seiten` int(11) NOT NULL DEFAULT '0',
  `jahrgang` mediumint(9) NOT NULL DEFAULT '0',
  `ersteller` tinyint(4) NOT NULL DEFAULT '0',
  `text` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `title` (`title`),
  KEY `autor` (`autor`),
  KEY `verlag` (`verlag`),
  KEY `isbn` (`isbn`),
  KEY `preis` (`preis`),
  KEY `besitzer` (`ersteller`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=1;



# Dump of table books_holder
# ------------------------------------------------------------

CREATE TABLE `books_holder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table books_title
# ------------------------------------------------------------

CREATE TABLE `books_title` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `typ` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=1;



# Dump of table bugtracker_bugs
# ------------------------------------------------------------

CREATE TABLE `bugtracker_bugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `priority` enum('1','2','3','4') CHARACTER SET utf8mb4 NOT NULL DEFAULT '4',
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `reporter_id` int(11) unsigned NOT NULL DEFAULT '0',
  `reported_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `assigned_date` datetime DEFAULT NULL,
  `assignedto_id` int(11) unsigned DEFAULT NULL,
  `resolved_date` datetime DEFAULT NULL,
  `denied_date` datetime DEFAULT NULL,
  `code_commit` varchar(7) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `resolved_date` (`resolved_date`),
  KEY `denied_date` (`denied_date`),
  KEY `assignedto_id` (`assignedto_id`),
  KEY `resolved_date_2` (`resolved_date`,`denied_date`,`assignedto_id`,`assigned_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table bugtracker_categories
# ------------------------------------------------------------

CREATE TABLE `bugtracker_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `text_2` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table chat
# ------------------------------------------------------------

CREATE TABLE `chat` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `from_mobile` binary(2) DEFAULT '0\0',
  `text` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`user_id`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table chess_board_old
# ------------------------------------------------------------

CREATE TABLE `chess_board_old` (
  `ID` int(10) unsigned NOT NULL DEFAULT '0',
  `farbe` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `figur` varchar(7) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `x` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `y` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `noMoves` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`x`,`y`,`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table chess_dwz
# ------------------------------------------------------------

CREATE TABLE `chess_dwz` (
  `rank` int(10) NOT NULL DEFAULT '9999',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL DEFAULT '0',
  `prev_score` int(10) NOT NULL DEFAULT '0',
  `prev_rank` int(10) NOT NULL DEFAULT '9999',
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table chess_game_old
# ------------------------------------------------------------

CREATE TABLE `chess_game_old` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user1` int(10) unsigned DEFAULT NULL,
  `user2` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table chess_games
# ------------------------------------------------------------

CREATE TABLE `chess_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_turn` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `next_turn` int(10) unsigned NOT NULL DEFAULT '0',
  `offering_remis` enum('0','1') COLLATE latin1_german1_ci NOT NULL DEFAULT '0',
  `white` int(10) unsigned NOT NULL DEFAULT '0',
  `black` int(10) unsigned NOT NULL DEFAULT '0',
  `state` set('running','remis','patt','matt','aufgabe') COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `winner` int(10) unsigned NOT NULL DEFAULT '0',
  `dwz_dif` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table chess_history
# ------------------------------------------------------------

CREATE TABLE `chess_history` (
  `game` int(10) unsigned NOT NULL DEFAULT '0',
  `nr` int(10) unsigned NOT NULL DEFAULT '0',
  `white` varchar(10) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `black` varchar(10) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`game`,`nr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table chess_history_old
# ------------------------------------------------------------

CREATE TABLE `chess_history_old` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gameID` int(10) NOT NULL DEFAULT '0',
  `figure` varchar(7) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `xFrom` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `yFrom` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `xTo` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `yTo` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `info` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table comments
# ------------------------------------------------------------

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_edited` datetime DEFAULT NULL,
  `board` char(1) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `error` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `BOARD_THREAD_PARENT` (`board`,`thread_id`,`parent_id`),
  KEY `DATE_THREAD` (`date`,`thread_id`),
  KEY `THREAD_DATE` (`thread_id`,`date`),
  KEY `BOARD_PARENT` (`board`,`parent_id`),
  KEY `USERID` (`user_id`),
  KEY `THREAD_ID` (`thread_id`,`id`),
  KEY `DATE` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci PACK_KEYS=1;



# Dump of table comments_backup
# ------------------------------------------------------------

CREATE TABLE `comments_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `text` longtext COLLATE latin1_german2_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_edited` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `board` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `error` text COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `BOARD_THREAD_PARENT` (`board`,`thread_id`,`parent_id`),
  KEY `DATE_THREAD` (`date`,`thread_id`),
  KEY `THREAD_DATE` (`thread_id`,`date`),
  KEY `BOARD_PARENT` (`board`,`parent_id`),
  KEY `USERID` (`user_id`),
  KEY `THREAD_ID` (`thread_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=1;



# Dump of table comments_boards
# ------------------------------------------------------------

CREATE TABLE `comments_boards` (
  `board` char(1) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `title` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `field` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `link` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`board`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table comments_subscriptions
# ------------------------------------------------------------

CREATE TABLE `comments_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board` char(1) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `comment_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `comment_id` (`board`,`comment_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table comments_threads
# ------------------------------------------------------------

CREATE TABLE `comments_threads` (
  `board` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `comment_id` int(11) DEFAULT NULL,
  `last_comment_id` int(11) NOT NULL DEFAULT '0',
  `last_seen` date DEFAULT NULL,
  `rights` tinyint(4) NOT NULL DEFAULT '0',
  `sticky` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`board`,`thread_id`),
  KEY `board` (`last_comment_id`,`board`,`thread_id`),
  KEY `sticky` (`sticky`,`last_comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=1;



# Dump of table comments_threads_favorites
# ------------------------------------------------------------

CREATE TABLE `comments_threads_favorites` (
  `board` char(1) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `comment_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`board`,`thread_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table comments_threads_ignore
# ------------------------------------------------------------

CREATE TABLE `comments_threads_ignore` (
  `board` char(1) COLLATE latin1_german2_ci NOT NULL,
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  KEY `thread_id` (`thread_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Speichert die vom User als "nicht verfolgen" markierten Thre';



# Dump of table comments_threads_rights
# ------------------------------------------------------------

CREATE TABLE `comments_threads_rights` (
  `board` char(1) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`board`,`thread_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table comments_unread
# ------------------------------------------------------------

CREATE TABLE `comments_unread` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `comment_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`comment_id`),
  KEY `comment_id` (`comment_id`),
  CONSTRAINT `comments_unread_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table dnd_cr_xp
# ------------------------------------------------------------

CREATE TABLE `dnd_cr_xp` (
  `level` int(11) NOT NULL DEFAULT '0',
  `cr1` int(11) NOT NULL DEFAULT '0',
  `cr2` int(11) NOT NULL DEFAULT '0',
  `cr3` int(11) NOT NULL DEFAULT '0',
  `cr4` int(11) NOT NULL DEFAULT '0',
  `cr5` int(11) NOT NULL DEFAULT '0',
  `cr6` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`level`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table dnd_level_xp
# ------------------------------------------------------------

CREATE TABLE `dnd_level_xp` (
  `level` smallint(6) NOT NULL DEFAULT '0',
  `xp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`level`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table dnd_log
# ------------------------------------------------------------

CREATE TABLE `dnd_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `player1` int(11) NOT NULL DEFAULT '0',
  `player2` int(11) NOT NULL DEFAULT '0',
  `action` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `text` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table dnd_players
# ------------------------------------------------------------

CREATE TABLE `dnd_players` (
  `id` int(11) NOT NULL DEFAULT '0',
  `x` int(11) NOT NULL DEFAULT '0',
  `y` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `str` smallint(6) NOT NULL DEFAULT '8',
  `con` tinyint(4) NOT NULL DEFAULT '8',
  `dex` tinyint(4) NOT NULL DEFAULT '8',
  `bab` smallint(6) NOT NULL DEFAULT '1',
  `hp_current` smallint(6) NOT NULL DEFAULT '10',
  `hp_max` smallint(6) NOT NULL DEFAULT '10',
  `ac` smallint(6) NOT NULL DEFAULT '10',
  `xp` int(11) NOT NULL DEFAULT '0',
  `gold` int(11) NOT NULL DEFAULT '60',
  `weapontype_id` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `x` (`x`,`y`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table dnd_weapons
# ------------------------------------------------------------

CREATE TABLE `dnd_weapons` (
  `player_id` int(11) NOT NULL DEFAULT '0',
  `weapon_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table dnd_weapontypes
# ------------------------------------------------------------

CREATE TABLE `dnd_weapontypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `numdice` tinyint(4) NOT NULL DEFAULT '0',
  `dicetype` tinyint(4) NOT NULL DEFAULT '0',
  `critical_low` tinyint(4) NOT NULL DEFAULT '20',
  `critical_high` tinyint(4) NOT NULL DEFAULT '20',
  `critical_multiplier` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table dreamjournal
# ------------------------------------------------------------

CREATE TABLE `dreamjournal` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `userid` tinyint(4) NOT NULL DEFAULT '0',
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `titel` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `text` blob NOT NULL,
  `emotion` tinyint(4) NOT NULL DEFAULT '0',
  `zeit` tinyint(4) NOT NULL DEFAULT '0',
  `lucid` tinyint(4) NOT NULL DEFAULT '0',
  `emotion1` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `emotion2` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `emotion3` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `emotion4` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `emotion5` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `keyword1` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `keyword2` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `keyword3` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `keyword4` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `keyword5` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `private` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table error
# ------------------------------------------------------------

CREATE TABLE `error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `do` text COLLATE latin1_german2_ci NOT NULL,
  `ip` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table events
# ------------------------------------------------------------

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `startdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `enddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reportedby_id` int(11) unsigned NOT NULL DEFAULT '0',
  `reportedon_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `gallery_id` int(11) unsigned DEFAULT NULL,
  `review_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tweet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(250)),
  KEY `location` (`location`(250)),
  KEY `startdate` (`startdate`,`enddate`),
  KEY `reportedon_date` (`reportedon_date`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table events_to_user
# ------------------------------------------------------------

CREATE TABLE `events_to_user` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `event_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Welche User an welchem event sind';



# Dump of table files
# ------------------------------------------------------------

CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `upload_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `mime` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table fretsonzorg
# ------------------------------------------------------------

CREATE TABLE `fretsonzorg` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` tinytext COLLATE latin1_german2_ci NOT NULL,
  `score` int(4) DEFAULT NULL,
  `stars` tinyint(4) NOT NULL,
  `difficulty` tinyint(4) NOT NULL,
  `song` tinytext COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table gallery_albums
# ------------------------------------------------------------

CREATE TABLE `gallery_albums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_german2_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table gallery_pics
# ------------------------------------------------------------

CREATE TABLE `gallery_pics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `album` int(10) unsigned NOT NULL DEFAULT '0',
  `extension` varchar(25) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `zensur` enum('0','1') CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `picsize` text CHARACTER SET latin1 COLLATE latin1_german2_ci,
  `tnsize` text CHARACTER SET latin1 COLLATE latin1_german2_ci,
  `shows_person` enum('0','1') CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT '1',
  `pic_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table gallery_pics_faceplusplus
# ------------------------------------------------------------

CREATE TABLE `gallery_pics_faceplusplus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_id_tagged` int(11) DEFAULT NULL,
  `face_token` text CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `top` int(11) NOT NULL,
  `left` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `date` datetime DEFAULT '0000-00-00 00:00:00',
  `gender_value` varchar(20) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `age_value` smallint(6) DEFAULT NULL,
  `smile_treshold` double DEFAULT NULL,
  `smile_value` double DEFAULT NULL,
  `glass_value` varchar(20) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `headpose_pitch_angle` double DEFAULT NULL,
  `headpose_roll_angle` double DEFAULT NULL,
  `headpose_yaw_angle` double DEFAULT NULL,
  `blurness_treshold` double DEFAULT NULL,
  `blurness_value` double DEFAULT NULL,
  `lefteyestatus_normal_glass_eye_open` double DEFAULT NULL,
  `lefteyestatus_no_glass_eye_close` double DEFAULT NULL,
  `lefteyestatus_occlusion` double DEFAULT NULL,
  `lefteyestatus_no_glass_eye_open` double DEFAULT NULL,
  `lefteyestatus_normal_glass_eye_close` double DEFAULT NULL,
  `lefteyestatus_dark_glasses` double DEFAULT NULL,
  `righteyestatus_normal_glass_eye_open` double DEFAULT NULL,
  `righteyestatus_no_glass_eye_close` double DEFAULT NULL,
  `righteyestatus_occlusion` double DEFAULT NULL,
  `righteyestatus_no_glass_eye_open` double DEFAULT NULL,
  `righteyestatus_normal_glass_eye_close` double DEFAULT NULL,
  `righteyestatus_dark_glasses` double DEFAULT NULL,
  `emotion_sadness` double DEFAULT NULL,
  `emotion_neutral` double DEFAULT NULL,
  `emotion_disgust` double DEFAULT NULL,
  `emotion_anger` double DEFAULT NULL,
  `emotion_surprise` double DEFAULT NULL,
  `emotion_fear` double DEFAULT NULL,
  `emotion_happiness` double DEFAULT NULL,
  `facequality_treshold` double DEFAULT NULL,
  `facequality_value` double DEFAULT NULL,
  `ethnicity_value` varchar(20) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT NULL,
  `beauty_female_score` double DEFAULT NULL,
  `beauty_male_score` double DEFAULT NULL,
  `mouthstatus_close` double DEFAULT NULL,
  `mouthstatus_surgical_mask_or_respirator` double DEFAULT NULL,
  `mouthstatus_open` double DEFAULT NULL,
  `mouthstatus_other_occlusion` double DEFAULT NULL,
  `righteyegaze_position_x_coordinate` double DEFAULT NULL,
  `righteyegaze_vector_z_component` double DEFAULT NULL,
  `righteyegaze_vector_x_component` double DEFAULT NULL,
  `righteyegaze_vector_y_component` double DEFAULT NULL,
  `righteyegaze_position_y_coordinate` double DEFAULT NULL,
  `lefteyegaze_position_x_coordinate` double DEFAULT NULL,
  `lefteyegaze_vector_z_component` double DEFAULT NULL,
  `lefteyegaze_vector_x_component` double DEFAULT NULL,
  `lefteyegaze_vector_y_component` double DEFAULT NULL,
  `lefteyegaze_position_y_coordinate` double DEFAULT NULL,
  `skinstatus_dark_circle` double DEFAULT NULL,
  `skinstatus_stain` double DEFAULT NULL,
  `skinstatus_acne` double DEFAULT NULL,
  `skinstatus_health` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `pic_id` (`pic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Speichert die Das-bin-ich-Markierungen einzelner Bilder';



# Dump of table gallery_pics_users
# ------------------------------------------------------------

CREATE TABLE `gallery_pics_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pos_x` int(11) NOT NULL,
  `pos_y` int(11) NOT NULL,
  `datum` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `pic_id` (`pic_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Speichert die Das-bin-ich-Markierungen einzelner Bilder';



# Dump of table gallery_pics_votes
# ------------------------------------------------------------

CREATE TABLE `gallery_pics_votes` (
  `pic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(1) NOT NULL,
  KEY `pic_id` (`pic_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table gallery_user
# ------------------------------------------------------------

CREATE TABLE `gallery_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pic_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table go_games
# ------------------------------------------------------------

CREATE TABLE `go_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pl1` int(11) NOT NULL,
  `pl2` int(11) NOT NULL,
  `pl1lost` mediumint(8) unsigned DEFAULT '0',
  `pl2lost` mediumint(8) unsigned DEFAULT '0',
  `pl1luck` binary(1) NOT NULL DEFAULT '0',
  `pl2luck` binary(1) NOT NULL DEFAULT '0',
  `pl1thank` binary(1) NOT NULL DEFAULT '0',
  `pl2thank` binary(1) NOT NULL DEFAULT '0',
  `size` tinyint(5) unsigned NOT NULL,
  `data` varchar(361) COLLATE latin1_german2_ci NOT NULL,
  `last1` smallint(5) NOT NULL DEFAULT '-2',
  `last2` smallint(5) NOT NULL DEFAULT '-2',
  `ko_sit` smallint(5) NOT NULL DEFAULT '-1',
  `nextturn` int(11) NOT NULL,
  `state` enum('open','running','counting','finished') COLLATE latin1_german2_ci DEFAULT NULL,
  `round` int(11) NOT NULL,
  `countchanged` binary(1) NOT NULL DEFAULT '1',
  `handicap` tinyint(6) unsigned DEFAULT '0',
  `pl1points` float DEFAULT NULL,
  `pl2points` float DEFAULT NULL,
  `winner` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_aims
# ------------------------------------------------------------

CREATE TABLE `hz_aims` (
  `map` int(10) unsigned NOT NULL DEFAULT '0',
  `station` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`map`,`station`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_dwz
# ------------------------------------------------------------

CREATE TABLE `hz_dwz` (
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT '9999',
  `score` int(11) NOT NULL DEFAULT '0',
  `prev_rank` int(11) NOT NULL DEFAULT '9999',
  `prev_score` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_games
# ------------------------------------------------------------

CREATE TABLE `hz_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `map` int(10) unsigned NOT NULL DEFAULT '0',
  `z_score` int(11) NOT NULL DEFAULT '0',
  `state` enum('open','running','finished') COLLATE latin1_german2_ci NOT NULL DEFAULT 'open',
  `nextturn` enum('z','players') COLLATE latin1_german2_ci NOT NULL DEFAULT 'z',
  `round` int(11) NOT NULL,
  `turncount` int(11) NOT NULL DEFAULT '0',
  `turndate` datetime DEFAULT NULL,
  `dwz_dif` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_maps
# ------------------------------------------------------------

CREATE TABLE `hz_maps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `players` int(1) unsigned NOT NULL DEFAULT '0',
  `state` enum('active','inactive') COLLATE latin1_german2_ci NOT NULL DEFAULT 'active',
  `width` int(4) unsigned NOT NULL DEFAULT '0',
  `height` int(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_players
# ------------------------------------------------------------

CREATE TABLE `hz_players` (
  `game` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('z','1','2','3','4','5','6','7','8') COLLATE latin1_german2_ci NOT NULL DEFAULT 'z',
  `station` int(10) unsigned NOT NULL DEFAULT '0',
  `money` int(11) NOT NULL DEFAULT '20',
  `turndone` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_routes
# ------------------------------------------------------------

CREATE TABLE `hz_routes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `map` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('taxi','ubahn','bus','black') COLLATE latin1_german2_ci NOT NULL DEFAULT 'taxi',
  `start` int(10) unsigned NOT NULL DEFAULT '0',
  `end` int(10) unsigned NOT NULL DEFAULT '0',
  `transit` text COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_sentinels
# ------------------------------------------------------------

CREATE TABLE `hz_sentinels` (
  `game` int(10) unsigned NOT NULL DEFAULT '0',
  `station` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_stations
# ------------------------------------------------------------

CREATE TABLE `hz_stations` (
  `map` int(10) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `x` int(4) unsigned NOT NULL DEFAULT '0',
  `y` int(4) unsigned NOT NULL DEFAULT '0',
  `ubahn` set('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `bus` set('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`map`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table hz_tracks
# ------------------------------------------------------------

CREATE TABLE `hz_tracks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game` int(10) unsigned NOT NULL DEFAULT '0',
  `ticket` enum('taxi','bus','ubahn','black','stay','sentinel') COLLATE latin1_german2_ci NOT NULL DEFAULT 'taxi',
  `station` int(10) unsigned NOT NULL DEFAULT '0',
  `nr` int(11) NOT NULL DEFAULT '0',
  `player` enum('z','1','2','3','4','5','6','7','8') COLLATE latin1_german2_ci NOT NULL DEFAULT 'z',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table innodb_monitor
# ------------------------------------------------------------

CREATE TABLE `innodb_monitor` (
  `a` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table joinus
# ------------------------------------------------------------

CREATE TABLE `joinus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `f1` text COLLATE latin1_german2_ci NOT NULL,
  `f2` text COLLATE latin1_german2_ci NOT NULL,
  `f3` text COLLATE latin1_german2_ci NOT NULL,
  `f4` text COLLATE latin1_german2_ci NOT NULL,
  `f5` text COLLATE latin1_german2_ci NOT NULL,
  `f6` text COLLATE latin1_german2_ci NOT NULL,
  `f7` text COLLATE latin1_german2_ci NOT NULL,
  `f8` text COLLATE latin1_german2_ci NOT NULL,
  `f9` text COLLATE latin1_german2_ci NOT NULL,
  `f10` text COLLATE latin1_german2_ci NOT NULL,
  `f11` text COLLATE latin1_german2_ci NOT NULL,
  `f12` text COLLATE latin1_german2_ci NOT NULL,
  `f13` text COLLATE latin1_german2_ci NOT NULL,
  `f14` text COLLATE latin1_german2_ci NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=1;



# Dump of table menus
# ------------------------------------------------------------

CREATE TABLE `menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_german2_ci NOT NULL,
  `tpl_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table messages
# ------------------------------------------------------------

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL DEFAULT '0',
  `owner` int(11) NOT NULL DEFAULT '0',
  `subject` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text COLLATE utf8mb4_unicode_ci,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isread` enum('0','1') CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT '0',
  `to_users` text CHARACTER SET latin1 COLLATE latin1_german2_ci,
  PRIMARY KEY (`id`),
  KEY `Datum` (`date`),
  KEY `isread` (`isread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table packages
# ------------------------------------------------------------

CREATE TABLE `packages` (
  `id` tinyint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table periodic
# ------------------------------------------------------------

CREATE TABLE `periodic` (
  `name` varchar(20) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table peter
# ------------------------------------------------------------

CREATE TABLE `peter` (
  `card_id` tinyint(3) NOT NULL DEFAULT '0',
  `value` tinyint(3) NOT NULL DEFAULT '0',
  `col` tinyint(2) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`card_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table peter_cardsets
# ------------------------------------------------------------

CREATE TABLE `peter_cardsets` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `card_id` tinyint(3) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `status` enum('nicht gelegt','gelegt') COLLATE latin1_german2_ci NOT NULL DEFAULT 'nicht gelegt',
  `spezial` enum('rosen','') COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`game_id`,`card_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table peter_games
# ------------------------------------------------------------

CREATE TABLE `peter_games` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `players` tinyint(3) NOT NULL DEFAULT '0',
  `next_player` int(11) NOT NULL DEFAULT '0',
  `status` enum('offen','lauft','geschlossen') COLLATE latin1_german2_ci NOT NULL DEFAULT 'offen',
  `winner_id` int(11) NOT NULL DEFAULT '0',
  `last_activity` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table peter_players
# ------------------------------------------------------------

CREATE TABLE `peter_players` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `join_id` tinyint(3) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `make` enum('karte','aus','fertig') COLLATE latin1_german2_ci NOT NULL DEFAULT 'karte',
  PRIMARY KEY (`game_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table peter_spezialregeln
# ------------------------------------------------------------

CREATE TABLE `peter_spezialregeln` (
  `card_id` tinyint(3) NOT NULL DEFAULT '0',
  `value` tinyint(3) NOT NULL DEFAULT '0',
  `col` tinyint(2) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `typ` enum('rosen') COLLATE latin1_german2_ci NOT NULL DEFAULT 'rosen',
  PRIMARY KEY (`card_id`,`typ`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table peter_wunsche
# ------------------------------------------------------------

CREATE TABLE `peter_wunsche` (
  `game_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `card_id` int(11) NOT NULL DEFAULT '0',
  `wunsch` enum('Rosen','Schellen','Schilten','Eichel') COLLATE latin1_german2_ci NOT NULL DEFAULT 'Rosen',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`game_id`,`user_id`,`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table pimp
# ------------------------------------------------------------

CREATE TABLE `pimp` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(200) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `suffix` varchar(200) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prefix_sufix` (`prefix`,`suffix`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table poll_answers
# ------------------------------------------------------------

CREATE TABLE `poll_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poll` int(10) unsigned NOT NULL DEFAULT '0',
  `text` text COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table poll_votes
# ------------------------------------------------------------

CREATE TABLE `poll_votes` (
  `poll` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `answer` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user`,`answer`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table polls
# ------------------------------------------------------------

CREATE TABLE `polls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('standard','member') COLLATE latin1_german2_ci NOT NULL DEFAULT 'standard',
  `text` text COLLATE latin1_german2_ci NOT NULL,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` enum('open','closed') COLLATE latin1_german2_ci NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table quiz
# ------------------------------------------------------------

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `kategorie` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `frage` text COLLATE latin1_german2_ci NOT NULL,
  `antwort` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `regexp` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `level` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table quotes
# ------------------------------------------------------------

CREATE TABLE `quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `text` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `USERID` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table quotes_votes
# ------------------------------------------------------------

CREATE TABLE `quotes_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `score` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quote_id` (`quote_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table rezepte
# ------------------------------------------------------------

CREATE TABLE `rezepte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  `zutaten` blob NOT NULL,
  `anz_personen` tinyint(3) unsigned DEFAULT NULL,
  `prep_time` smallint(4) unsigned NOT NULL,
  `cook_time` smallint(4) unsigned NOT NULL,
  `difficulty` tinyint(2) DEFAULT NULL,
  `description` longblob NOT NULL,
  `ersteller_id` tinyint(4) DEFAULT NULL,
  `erstellt_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `id` (`id`),
  KEY `category_id` (`category_id`),
  KEY `anz_personen` (`anz_personen`,`prep_time`,`cook_time`,`difficulty`),
  KEY `erstelltam_date` (`erstellt_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table rezepte_categories
# ------------------------------------------------------------

CREATE TABLE `rezepte_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table rezepte_votes
# ------------------------------------------------------------

CREATE TABLE `rezepte_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rezept_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rezept_id` (`rezept_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table rezepte_zutaten
# ------------------------------------------------------------

CREATE TABLE `rezepte_zutaten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  `einheit` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  `menge` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table seti
# ------------------------------------------------------------

CREATE TABLE `seti` (
  `name` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `num_results` int(11) NOT NULL DEFAULT '0',
  `total_cpu` float(20,3) NOT NULL DEFAULT '0.000',
  `avg_cpu` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `date_last_result` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `account` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table seti_tage
# ------------------------------------------------------------

CREATE TABLE `seti_tage` (
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `name` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `num_results` int(11) NOT NULL DEFAULT '0',
  `total_cpu` float(20,3) NOT NULL DEFAULT '0.000',
  `avg_cpu` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `date_last_result` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `account` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`,`datum`),
  KEY `datum` (`datum`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table spaceweather
# ------------------------------------------------------------

CREATE TABLE `spaceweather` (
  `wert` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table spaceweather_pha
# ------------------------------------------------------------

CREATE TABLE `spaceweather_pha` (
  `asteroid` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `distance` varchar(10) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `mag` float(10,5) NOT NULL DEFAULT '0.00000',
  PRIMARY KEY (`asteroid`,`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table sql_error
# ------------------------------------------------------------

CREATE TABLE `sql_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `page` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `file` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `line` varchar(10) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `function` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `query` text COLLATE latin1_german2_ci NOT NULL,
  `msg` text COLLATE latin1_german2_ci NOT NULL,
  `referer` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `file` (`file`),
  KEY `status` (`status`),
  KEY `sql_error_idx_status_date` (`status`,`date`),
  KEY `sql_error_idx_date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table stl
# ------------------------------------------------------------

CREATE TABLE `stl` (
  `game_id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `game_size` tinyint(4) NOT NULL DEFAULT '0',
  `winner_team` int(11) NOT NULL DEFAULT '0',
  `creator_id` int(11) NOT NULL DEFAULT '0',
  `num_players` tinyint(4) NOT NULL DEFAULT '0',
  `game_title` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`game_id`),
  KEY `creater_id` (`creator_id`),
  KEY `game_size` (`game_size`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table stl_players
# ------------------------------------------------------------

CREATE TABLE `stl_players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `team_id` int(11) NOT NULL DEFAULT '0',
  `game_id` int(11) NOT NULL DEFAULT '0',
  `last_shoot` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`team_id`),
  KEY `game_id` (`game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table stl_positions
# ------------------------------------------------------------

CREATE TABLE `stl_positions` (
  `pos_id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL DEFAULT '0',
  `grid_x` int(11) NOT NULL DEFAULT '0',
  `grid_y` int(11) NOT NULL DEFAULT '0',
  `hit_user_id` int(11) NOT NULL DEFAULT '0',
  `hit_team_id` tinyint(4) NOT NULL DEFAULT '0',
  `ship_user_id` int(11) NOT NULL DEFAULT '0',
  `ship_team_id` tinyint(4) NOT NULL DEFAULT '0',
  `shoot_date` datetime DEFAULT NULL,
  PRIMARY KEY (`pos_id`),
  KEY `game_id` (`game_id`),
  KEY `ship_team_id` (`ship_team_id`),
  KEY `hit_team_id` (`hit_team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table stock_items
# ------------------------------------------------------------

CREATE TABLE `stock_items` (
  `symbol` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `market` tinyint(4) NOT NULL DEFAULT '0',
  `description` text COLLATE latin1_german2_ci NOT NULL,
  `company` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `kurs_last_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`symbol`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table stock_markets
# ------------------------------------------------------------

CREATE TABLE `stock_markets` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `market` varchar(50) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `market` (`market`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table stock_new2
# ------------------------------------------------------------

CREATE TABLE `stock_new2` (
  `company` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `currency` char(3) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `market` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `branche` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `symbol` varchar(20) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`symbol`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table stock_new3
# ------------------------------------------------------------

CREATE TABLE `stock_new3` (
  `company` varchar(255) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `symbol` varchar(20) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `currency` char(3) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `market` varchar(100) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `branche` varchar(100) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`symbol`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table stock_quotes
# ------------------------------------------------------------

CREATE TABLE `stock_quotes` (
  `tag` date NOT NULL DEFAULT '0000-00-00',
  `zeit` time NOT NULL DEFAULT '00:00:00',
  `symbol` varchar(11) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `kurs` float(9,3) NOT NULL DEFAULT '0.000',
  `kurs_gestern` float(9,3) NOT NULL DEFAULT '0.000',
  `proz_steigerung` float(9,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`tag`,`symbol`),
  KEY `symbol` (`symbol`,`tag`),
  KEY `tag` (`tag`,`zeit`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table stock_trades
# ------------------------------------------------------------

CREATE TABLE `stock_trades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` date NOT NULL DEFAULT '0000-00-00',
  `zeit` time NOT NULL DEFAULT '00:00:00',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `symbol` varchar(11) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `menge` int(11) NOT NULL DEFAULT '0',
  `action` enum('buy','sell') CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT 'buy',
  `kurs` float(6,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `user_action` (`user_id`,`action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table stock_warnings
# ------------------------------------------------------------

CREATE TABLE `stock_warnings` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `symbol` varchar(11) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `comparison` char(2) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
  `kurs` float(6,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`user_id`,`symbol`,`comparison`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;



# Dump of table tauschboerse
# ------------------------------------------------------------

CREATE TABLE `tauschboerse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `art` enum('nachfrage','angebot') CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT 'nachfrage',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bezeichnung` varchar(255) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `zustand` varchar(100) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `wertvorstellung` varchar(25) CHARACTER SET latin1 COLLATE latin1_german2_ci DEFAULT '0',
  `lieferbedingung` varchar(150) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `kommentar` blob NOT NULL,
  `aktuell` enum('0','1') CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table templates
# ------------------------------------------------------------

CREATE TABLE `templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `word` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `border` enum('0','1','2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `page_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tpl` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `update_user` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` datetime DEFAULT NULL,
  `read_rights` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `write_rights` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `lock_user` int(10) unsigned NOT NULL DEFAULT '0',
  `lock_time` datetime DEFAULT NULL,
  `force_compile` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `error` text COLLATE utf8mb4_unicode_ci,
  `del` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `sidebar_tpl` smallint(5) unsigned DEFAULT NULL,
  `allow_comments` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `tpl_match_search` (`title`,`tpl`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci PACK_KEYS=0;



# Dump of table templates_backup
# ------------------------------------------------------------

CREATE TABLE `templates_backup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `word` varchar(30) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `border` enum('0','1','2') COLLATE latin1_german2_ci NOT NULL DEFAULT '1',
  `page_title` text COLLATE latin1_german2_ci NOT NULL,
  `tpl` text COLLATE latin1_german2_ci NOT NULL,
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `update_user` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` datetime DEFAULT NULL,
  `read_rights` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `write_rights` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `lock_user` int(10) unsigned NOT NULL DEFAULT '0',
  `lock_time` datetime DEFAULT NULL,
  `force_compile` enum('1','0') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `error` text COLLATE latin1_german2_ci NOT NULL,
  `del` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `sidebar_tpl` smallint(5) unsigned DEFAULT NULL,
  `allow_comments` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  FULLTEXT KEY `title` (`title`,`tpl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=0;



# Dump of table tpl_favourites
# ------------------------------------------------------------

CREATE TABLE `tpl_favourites` (
  `user` int(11) NOT NULL DEFAULT '0',
  `tpl` int(11) NOT NULL DEFAULT '0',
  `display` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`user`,`tpl`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table tpl_menus
# ------------------------------------------------------------

CREATE TABLE `tpl_menus` (
  `tpl_id` int(11) unsigned NOT NULL,
  `menu_id` tinyint(5) unsigned NOT NULL,
  `group_id` smallint(2) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table tpl_packages
# ------------------------------------------------------------

CREATE TABLE `tpl_packages` (
  `tpl_id` int(11) unsigned NOT NULL,
  `package_id` tinyint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table user
# ------------------------------------------------------------

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `clan_tag` varchar(75) COLLATE latin1_german2_ci DEFAULT '',
  `userpw` varchar(75) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `telegram_chat_id` int(11) DEFAULT NULL,
  `irc_username` varchar(9) COLLATE latin1_german2_ci DEFAULT NULL,
  `regdate` datetime DEFAULT NULL,
  `regcode` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `z_gremium` enum('','z') COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `active` smallint(2) NOT NULL DEFAULT '0',
  `lastlogin` datetime DEFAULT NULL,
  `currentlogin` datetime DEFAULT NULL,
  `ausgesperrt_bis` datetime DEFAULT NULL,
  `activity` datetime DEFAULT NULL,
  `notifications` text COLLATE latin1_german2_ci,
  `forummaxthread` int(4) unsigned NOT NULL DEFAULT '10',
  `usertype` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `from_mobile` enum('','midp','240x320','blackberry','netfront','nokia','panasonic','portalmmm','sharp','sie-','sonyericsson','symbian','windows ce','benq','mda','mot-','opera mini','philips','pocket pc','sagem','samsung','sda','sgh-','vodafone','xda','iphone','android') COLLATE latin1_german2_ci DEFAULT NULL,
  `image` varchar(255) COLLATE latin1_german2_ci DEFAULT NULL,
  `image_date` datetime DEFAULT NULL,
  `addle` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `chess` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `show_comments` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '1',
  `sql_tracker` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `forum_boards` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '["b","e","f","o","r","t"]',
  `forum_boards_unread` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '["b","e","f","g","h","i","o","t"]',
  `button_use` int(11) NOT NULL DEFAULT '0',
  `posts_lost` int(11) NOT NULL DEFAULT '0',
  `menulayout` enum('','1','2','3') COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `mymenu` int(10) unsigned DEFAULT NULL,
  `zorger` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  `activities_allow` enum('0','1') COLLATE latin1_german2_ci NOT NULL DEFAULT '1',
  `firstname` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `lastname` varchar(20) COLLATE latin1_german2_ci DEFAULT NULL,
  `beitritt` date DEFAULT NULL,
  `austritt` date DEFAULT NULL,
  `vereinsmitglied` enum('0','Mitglied','Kenner','Vorstand') COLLATE latin1_german2_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usertype` (`usertype`),
  KEY `username` (`username`(20)),
  KEY `activity` (`activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table user_locations
# ------------------------------------------------------------

CREATE TABLE `user_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip` varchar(16) COLLATE latin1_german2_ci NOT NULL,
  `coord_x` varchar(16) COLLATE latin1_german2_ci NOT NULL,
  `coord_y` varchar(16) COLLATE latin1_german2_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci COMMENT='Erfasst die IP und Coordinaten des Logins eines Users für Au';



# Dump of table userpics
# ------------------------------------------------------------

CREATE TABLE `userpics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `image_name` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  `image_title` varchar(255) COLLATE latin1_german2_ci NOT NULL,
  `image_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `image_replaced` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table v2_flightclub
# ------------------------------------------------------------

CREATE TABLE `v2_flightclub` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `km` int(3) unsigned NOT NULL DEFAULT '0',
  `min` int(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table v2_polls
# ------------------------------------------------------------

CREATE TABLE `v2_polls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pollid` int(11) NOT NULL DEFAULT '0',
  `thema` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `radios` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `votes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci PACK_KEYS=1;



# Dump of table verein_correspondence
# ------------------------------------------------------------

CREATE TABLE `verein_correspondence` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `communication_type` enum('EMAIL','LETTER','CHAT','VERBAL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `sender_id` int(11) unsigned DEFAULT NULL,
  `recipient_id` int(11) unsigned DEFAULT NULL,
  `subject_text` text COLLATE utf8mb4_unicode_ci,
  `preview_text` text COLLATE utf8mb4_unicode_ci,
  `message_text` longtext COLLATE utf8mb4_unicode_ci,
  `recipient_confirmation` enum('TRUE','FALSE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_confirmationdate` datetime DEFAULT NULL,
  `recipient_remarks` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



# Dump of table wetten
# ------------------------------------------------------------

CREATE TABLE `wetten` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('offen','laeuft','geschlossen') COLLATE latin1_german2_ci NOT NULL DEFAULT 'offen',
  `titel` varchar(255) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `wette` text COLLATE latin1_german2_ci NOT NULL,
  `einsatz` text COLLATE latin1_german2_ci NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `datum` datetime DEFAULT NULL,
  `dauer` int(11) NOT NULL DEFAULT '0',
  `start` datetime DEFAULT NULL,
  `ende` datetime DEFAULT NULL,
  `geschlossen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table wetten_teilnehmer
# ------------------------------------------------------------

CREATE TABLE `wetten_teilnehmer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wetten_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `seite` enum('wetter','gegner') COLLATE latin1_german2_ci NOT NULL DEFAULT 'wetter',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `wetten_id` (`wetten_id`,`user_id`,`seite`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table wiki
# ------------------------------------------------------------

CREATE TABLE `wiki` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(100) COLLATE latin1_german2_ci NOT NULL DEFAULT '',
  `text` blob NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `word` (`word`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
