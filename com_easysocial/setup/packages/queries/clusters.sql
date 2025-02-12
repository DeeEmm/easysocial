/*
* @package    EasySocial
* @copyright  Copyright (C) StackIdeas Private Limited. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

CREATE TABLE IF NOT EXISTS `#__social_clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `cluster_type` varchar(255) NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `creator_uid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `isnew` tinyint(1) NOT NULL DEFAULT 0,
  `featured` tinyint(3) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `params` text,
  `hits` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(3) NOT NULL,
  `notification` tinyint(3) NOT NULL DEFAULT 1,
  `verified` TINYINT(3) NOT NULL DEFAULT 0,
  `key` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `parent_type` varchar(255) NOT NULL DEFAULT '',
  `longitude` varchar(255) NOT NULL DEFAULT '' COMMENT 'The longitude value of the event for proximity search purposes',
  `latitude` varchar(255) NOT NULL DEFAULT '' COMMENT 'The latitude value of the event for proximity search purposes',
  `address` text COMMENT 'The full address value of the event for displaying purposes',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `idx_state` (`state`),
  KEY `idx_clustertype` (`cluster_type` (190)),
  KEY `idx_user_cluster` (`creator_uid`, `creator_type` (64), `cluster_type` (64)),
  KEY `idx_repetitive_clusters` (`cluster_type` (15), `parent_id`, `state`, `type`, `featured`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_clusters_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `container` tinyint(3) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'The creator of the category',
  `ordering` int(11) NOT NULL DEFAULT 0,
  `lft` int(11) unsigned DEFAULT '0',
  `rgt` int(11) unsigned DEFAULT '0',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `type` (`type` (190)),
  KEY `idx_parentid` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_clusters_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `content_type` varchar(65) NOT NULL default 'tinymce',
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `hits` int(11) NOT NULL,
  `comments` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`,`created_by`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_clusters_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(4) NOT NULL,
  `owner` tinyint(3) NOT NULL,
  `admin` tinyint(3) NOT NULL,
  `invited_by` int(11) NOT NULL DEFAULT 0,
  `reminder_sent` tinyint(3) NULL default 0,
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`,`state`),
  KEY `invited_by` (`invited_by`),
  KEY `idx_clusters_nodes_uid` (`uid`),
  KEY `idx_clusters_nodes_user` (`uid`,`state`, `created`),
  KEY `idx_members` (`cluster_id`, `type` (64), `state` ),
  KEY `idx_reminder_sent` (`reminder_sent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_step_sessions` (
  `session_id` varchar(128) NOT NULL DEFAULT '',
  `uid` bigint(20) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `values` text,
  `step` bigint(20) NOT NULL DEFAULT 0,
  `step_access` text,
  `errors` text,
  UNIQUE KEY `session_id` (`session_id`),
  KEY `profile_id` (`uid`),
  KEY `step` (`step`),
  KEY `type` (`type` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_clusters_categories_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'create',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`profile_id`),
  KEY `category_id_2` (`category_id`,`profile_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_events_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL COMMENT 'The event cluster id',
  `start` datetime NOT NULL COMMENT 'The start datetime of the event',
  `end` datetime NOT NULL COMMENT 'The end datetime of the event',
  `timezone` varchar(255) NOT NULL COMMENT 'The optional timezone of the event for datetime calculation',
  `all_day` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Flag if this event is an all day event',
  `group_id` int(11) NOT NULL DEFAULT 0 COMMENT 'The group id if this is a group event',
  `page_id` int(11) NOT NULL DEFAULT 0 COMMENT 'The page id if this is a page event',
  `reminder` int(11) NULL default 0 COMMENT 'the number of days before the actual event date',
  `start_gmt` datetime NOT NULL COMMENT 'The GMT start datetime of the event',
  `end_gmt` datetime NOT NULL COMMENT 'The GMT end datetime of the event',
  PRIMARY KEY (`id`),
  KEY `cluster_id` (`cluster_id`),
  KEY `idx_reminder` (`reminder`),
  KEY `idx_upcoming_reminder` (`reminder`,`start`),
  KEY `idx_start` (`start`),
  KEY `idx_startend` (`start`, `end`),
  KEY `idx_groupid` (`group_id`),
  KEY `idx_pageid` (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_clusters_reject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `cluster_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cluster_id` (`cluster_id`),
  KEY `idx_created` (`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_clusters_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `count` int(11) NOT NULL default 0,
  `interval` varchar(25) NOT NULL,
  `sent` datetime NOT NULL,
  `created` datetime NOT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
