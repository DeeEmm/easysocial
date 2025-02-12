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

CREATE TABLE IF NOT EXISTS `#__social_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` INT(11) NOT NULL DEFAULT 0,
  `name` text NOT NULL,
  `hits` int(11) NOT NULL DEFAULT 0,
  `hash` text NOT NULL,
  `sub` varchar(255) NULL default '',
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `size` text NOT NULL,
  `mime` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT 0,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `collection_id` (`collection_id`),
  KEY `idx_storage_cron` (`storage` (200), `created`),
  KEY `idx_created` (`created`),
  KEY `idx_utypes` (`type` (64), `uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_files_collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'This is the person that creates the item.',
  `title` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_uploader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `name` text NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_tmp` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `key` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `expired` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `node_id` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
