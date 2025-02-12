/*
* @package    EasySocial
* @copyright  Copyright (C) 2009 - 2011 StackIdeas Private Limited. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

CREATE TABLE IF NOT EXISTS `#__social_friends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `message` text,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_friends_actor` ( `actor_id` ),
  KEY `idx_friends_target` ( `target_id` ),
  KEY `idx_friends_actor_state` ( `actor_id`, `state` ),
  KEY `idx_friends_target_state` ( `target_id`, `state` ),
  KEY `idx_actor_target` (`actor_id`, `target_id`, `state`),
  KEY `idx_target_actor` (`target_id`, `actor_id`, `state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_lists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `alias` text NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `default` tinyint(3) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'friends',
  PRIMARY KEY (`id`),
  KEY `idx_userid` ( `user_id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_lists_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `list_id` bigint(20) NOT NULL,
  `target_id` bigint(20) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target_id` ( `target_id` ),
  KEY `idx_target_list_type` ( `target_id`, `list_id`, `target_type` (128) )
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_friends_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` text NOT NULL,
  `created` datetime NOT NULL,
  `message` text NOT NULL,
  `registered_id` int(11) NOT NULL,
  `utype` varchar(255) NOT NULL DEFAULT 'site',
  `uid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;
