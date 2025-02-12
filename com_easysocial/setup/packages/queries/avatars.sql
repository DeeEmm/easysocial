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


CREATE TABLE IF NOT EXISTS `#__social_avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary ID',
  `uid` int(11) NOT NULL COMMENT 'Node''s ID',
  `type` varchar(255) NOT NULL,
  `avatar_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'If the node is using a default avatar, this field will be populated with an id.',
  `photo_id` int(11) NOT NULL DEFAULT 0 COMMENT 'If the avatar is created from a photo, this field will be populated with the photo id.',
  `small` text NOT NULL,
  `medium` text NOT NULL,
  `square` text NOT NULL,
  `large` text NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `storage` varchar(255) NOT NULL DEFAULT 'joomla',
  PRIMARY KEY (`id`),
  KEY `avatar_id` (`avatar_id`),
  KEY `photo_id` (`photo_id`),
  KEY `idx_uid` ( `uid` ),
  KEY `idx_uid_type` ( `uid`, `type` (64)),
  KEY `idx_storage_cron` (`storage` (64), `avatar_id`, `small` (64)),
  KEY `idx_type` (`type` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_default_avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `large` text NOT NULL,
  `medium` text NOT NULL,
  `small` text NOT NULL,
  `square` text NOT NULL,
  `default` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type` (64)),
  KEY `system` (`default`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_covers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary ID',
  `uid` int(11) NOT NULL COMMENT 'Node''s ID',
  `type` varchar(255) NOT NULL,
  `photo_id` int(13) NOT NULL DEFAULT 0 COMMENT 'If the node is using a default avatar, this field will be populated with an id.',
  `cover_id` int(11) NOT NULL DEFAULT 0,
  `x` varchar(255) NOT NULL,
  `y` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `avatar_id` (`photo_id`),
  KEY `idx_uid` ( `uid` ),
  KEY `idx_uid_type` ( `uid`, `type` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_default_covers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` text,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `large` text NOT NULL,
  `small` text NOT NULL,
  `default` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type` (64)),
  KEY `system` (`default`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
