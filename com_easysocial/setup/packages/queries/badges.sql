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

CREATE TABLE IF NOT EXISTS `#__social_badges` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `command` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `alias` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `howto` text NOT NULL,
  `avatar` text NOT NULL,
  `created` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `frequency` bigint(20) NOT NULL,
  `achieve_type` varchar(65) NOT NULL DEFAULT 'frequency',
  `points_increase_rule` int(11) NULL DEFAULT '0',
  `points_decrease_rule` int(11) NULL DEFAULT '0',
  `points_threshold` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `discuss_badges_alias` (`alias` (190)),
  KEY `discuss_badges_published` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_badges_maps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `custom_message` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `badge_id` (`badge_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_badges_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `badge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achieved` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_badges` (`badge_id`, `user_id`),
  KEY `idx_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
