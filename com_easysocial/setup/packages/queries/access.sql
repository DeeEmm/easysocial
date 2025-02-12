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


CREATE TABLE IF NOT EXISTS `#__social_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type` (64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_access_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `extension` (`extension` (128)),
  KEY `element` (`element` (64)),
  KEY `group` (`group` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_access_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rule` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `utype` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rule` (`rule` (128)),
  KEY `idx_userid` (`user_id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_utypes` (`uid`, `utype` (64)),
  KEY `idx_created` (`created`),
  KEY `idx_useritems` (`rule` (128), `user_id`, `created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
