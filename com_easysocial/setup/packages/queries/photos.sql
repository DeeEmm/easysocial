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

CREATE TABLE IF NOT EXISTS `#__social_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cover_id` int(11) NOT NULL DEFAULT 0,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `caption` text,
  `created` datetime NOT NULL,
  `assigned_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `params` text NOT NULL,
  `core` tinyint(3) NOT NULL DEFAULT 0,
  `hits` int(11) NOT NULL DEFAULT 0,
  `notified` tinyint(1) NOT NULL DEFAULT 0,
  `finalized` tinyint(1) NOT NULL default 1,
  `access` int(11) default 0 NOT NULL,
  `custom_access` text NULL,
  `field_access` tinyint(3) default 0,
  `chk_access` tinyint(1) default 1,
  `password` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type` (64)),
  KEY `user_id` (`user_id`),
  KEY `idx_albums_user_assigned` (`uid`, `type` (64), `assigned_date`),
  KEY `idx_access` (`access`),
  KEY `idx_custom_access` (`access`, `custom_access` (200)),
  KEY `idx_field_access` (`access`, `field_access`),
  KEY `idx_type_chkaccess` (`type` (64), `chk_access`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `post_as` VARCHAR(64) DEFAULT 'user',
  `user_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `caption` text NULL,
  `created` datetime NOT NULL,
  `assigned_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `featured` tinyint(3) NOT NULL DEFAULT 0,
  `state` tinyint(3) NOT NULL DEFAULT 0,
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  `total_size` bigint(20) NULL default 0,
  `access` int(11) default 0 NOT NULL,
  `custom_access` text NULL,
  `field_access` tinyint(3) default 0,
  `chk_access` tinyint(1) default 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_photos_user_photos` (`state`, `uid`, `type` (64), `ordering`),
  KEY `idx_albums` (`state`, `album_id`, `ordering`),
  KEY `idx_storage_cron` (`state`, `storage` (128), `created`),
  KEy `idx_created` (`created`),
  KEY `idx_access` (`access`),
  KEY `idx_custom_access` (`access`, `custom_access` (200)),
  KEY `idx_field_access` (`access`, `field_access`),
  KEY `idx_type_chkaccess` (`type` (64), `chk_access`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_photos_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL DEFAULT 0,
  `group` varchar(255) NOT NULL,
  `property` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `photo_id` (`photo_id`),
  KEY `group` (`group` (190)),
  KEY `idx_sql1` (`photo_id`, `group` (64), `property` (64)),
  KEY `idx_sql2` (`photo_id`, `group` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_photos_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `label` text NOT NULL,
  `top` varchar(255) NOT NULL,
  `left` varchar(255) NOT NULL,
  `width` varchar(255) NOT NULL,
  `height` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_albums_favourite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

