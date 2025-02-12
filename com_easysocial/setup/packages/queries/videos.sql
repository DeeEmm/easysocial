/*
* @package		EasySocial
* @copyright	Copyright (C) 2009 - 2011 StackIdeas Private Limited. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

CREATE TABLE IF NOT EXISTS `#__social_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key for this table',
  `title` varchar(255) NOT NULL COMMENT 'Title of the video',
  `description` text COMMENT 'The description of the video',
  `user_id` int(11) NOT NULL COMMENT 'The user id that created this video',
  `uid` int(11) NOT NULL COMMENT 'This video may belong to another node other than the user.',
  `type` varchar(255) NOT NULL COMMENT 'This video may belong to another node other than the user.',
  `post_as` VARCHAR(64) DEFAULT 'user',
  `created` datetime NOT NULL,
  `assigned_date` datetime NULL,
  `state` tinyint(3) NOT NULL,
  `isnew` tinyint(1) NOT NULL default 0,
  `scheduled` tinyint(3) NOT NULL DEFAULT 0,
  `featured` tinyint(3) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT 0 COMMENT 'Total hits received for this video',
  `duration` varchar(255) NOT NULL DEFAULT '' COMMENT 'Duration of the video',
  `size` int(11) NOT NULL DEFAULT 0 COMMENT 'The file size of the video',
  `params` text COMMENT 'Store video params',
  `storage` varchar(255) NOT NULL COMMENT 'Storage for videos',
  `path` text,
  `original` text,
  `file_title` varchar(255) NOT NULL DEFAULT '',
  `source` varchar(255) NOT NULL,
  `thumbnail` text,
  `thumbnail_custom` text,
  `access` int(11) default 0 NOT NULL,
  `custom_access` text NULL,
  `field_access` tinyint(3) default 0,
  `chk_access` tinyint(1) default 1,
  PRIMARY KEY (`id`),
  KEY `title` (`title` (190),`user_id`,`state`,`featured`,`category_id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_type_featured` (`state`, `type` (64), `featured`),
  KEY `idx_type_userid` (`state`, `type` (64), `user_id`),
  KEY `idx_type_userid_featured` (`state`, `type` (64), `user_id`, `featured`),
  KEY `idx_categoryid` (`category_id`),
  KEY `idx_access` (`access`),
  KEY `idx_custom_access` (`access`, `custom_access` (200)),
  KEY `idx_field_access` (`access`, `field_access`),
  KEY `idx_type_chkaccess` (`type` (64), `chk_access`),
  KEY `idx_scheduled` (`scheduled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_videos_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `default` tinyint(3) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL COMMENT 'The user id that created this category',
  `created` datetime NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_videos_categories_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`profile_id`,`type` (200))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;
