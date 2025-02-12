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

CREATE TABLE IF NOT EXISTS `#__social_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `core` tinyint(4) NOT NULL DEFAULT '0',
  `system` tinyint(3) NOT NULL DEFAULT '0',
  `unique` tinyint(4) NOT NULL DEFAULT '0',
  `default` tinyint(3) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'It could be widgets,fields or applications',
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `ordering` int(11) NOT NULL,
  `params` text NOT NULL,
  `version` varchar(255) NOT NULL,
  `widget` tinyint(3) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `installable` tinyint(3) NOT NULL,
  `webview` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `type` (`type` (190)),
  KEY `core` (`core`),
  KEY `idx_default_widget` ( `state`, `group` (64), `widget`, `default` ),
  KEY `idx_group` ( `group` (190)),
  KEY `idx_apps_element` (`element` (190)),
  KEY `idx_apps_type_group` (`type` (64),`group` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_apps_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `app_id` int(11) NOT NULL,
  `position` varchar(50) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_uid_type` ( `app_id`, `uid`, `type` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_apps_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `view` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_view` ( `app_id`, `view` (128))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `milestone_id` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL DEFAULT 0,
  `type` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `due` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`),
  KEY `milestone_id` (`milestone_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_tasks_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `due` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_discussions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'This determines if this is a reply to a discussion. If it is a reply, it should contain the parent''s id here.',
  `uid` int(11) NOT NULL COMMENT 'The unique id this discussion is associated to. For example, if it is associated with a group, it should store the group''s id.',
  `type` varchar(255) NOT NULL COMMENT 'The unique type this discussion is associated to. For example, if it is associated with a group, it should store the type as group',
  `answer_id` int(11) NOT NULL DEFAULT 0 COMMENT 'This is only applicable to main question. This should contain the reference to the discussion that is an answer.',
  `last_reply_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Determines the last reply for the discussion',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `content_type` varchar(65) default 'bbcode',
  `created_by` int(11) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT 'Stores the total views for this discussion.',
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `last_replied` datetime NOT NULL COMMENT 'Stores the last replied date time.',
  `votes` int(11) NOT NULL COMMENT 'Determines the vote count for this discussion.',
  `total_replies` int(11) NOT NULL DEFAULT '0' COMMENT 'This is to denormalize the reply count of a discussion.',
  `lock` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Determines if this discussion is locked',
  `params` text COMMENT 'Stores additional raw parameters for the discussion that doesn''t need to be indexed',
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `uid_2` (`uid`,`type` (128)),
  KEY `id` (`id`,`parent_id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_discussions_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `discussion_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`,`discussion_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `url` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_apps_calendar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `reminder` tinyint(3) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `all_day` tinyint(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_rss` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `url` text NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`state`),
  KEY `uid` (`uid`,`type` (128))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_apps_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_apps_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `info` text NOT NULL,
  `price` varchar(255) NOT NULL,
  `logo` text NOT NULL,
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `permalink` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `download` tinyint(3) NOT NULL,
  `download_api` tinyint(3) NOT NULL,
  `version_checking` tinyint(3) NOT NULL,
  `raw` text NOT NULL,
  `ratings` int(11) NOT NULL,
  `votes` int(11) NOT NULL,
  `payment` tinyint(3) NOT NULL,
  `featured` tinyint(3) NOT NULL,
  `stackideas` TINYINT(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
