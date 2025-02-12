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

CREATE TABLE IF NOT EXISTS `#__social_privacy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(64) NOT NULL COMMENT 'object type e.g. photos, friends, albums, profile and etc',
  `rule` varchar(64) NOT NULL COMMENT 'rule type e.g. view_friends, view, search, comment, tag and etc',
  `value` int(11) DEFAULT '0',
  `options` text,
  `description` text,
  `state` TINYINT( 3 ) NOT NULL DEFAULT '1',
  `core` TINYINT( 3 ) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `type_rule` (`type`,`rule`),
  KEY `type_rule_privacy` (`type`,`rule`,`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_privacy_customize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'id from user or profile or item',
  `utype` varchar(64) NOT NULL COMMENT 'user or profile or item',
  `user_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_type` (`uid`,`utype` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_privacy_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'id from profile or item',
  `utype` varchar(64) NOT NULL COMMENT 'profile or item',
  `field_key` varchar(255) COMMENT 'element|unique_key for the field',
  `field_value` text,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_type` (`uid`,`utype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_privacy_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) NOT NULL COMMENT 'key to social_privacy.id',
  `user_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL COMMENT 'object id e.g streamid, activityid and etc',
  `type` varchar(64) NOT NULL COMMENT 'object type e.g. stream, activity and etc',
  `value` int(11) DEFAULT '0',
  `field_access` tinyint(3) default 0,
  PRIMARY KEY (`id`),
  KEY `privacy_id` (`privacy_id`),
  KEY `user_privacy_item` (`user_id`,`uid`,`type`),
  KEY `idx_uid_type` ( `uid`, `type` ),
  KEY `idx_user_type` (`user_id`, `type`),
  KEY `idx_value_user` (`value`, `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_privacy_items_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT 'id from profile or item',
  `utype` varchar(64) NOT NULL COMMENT 'profile or item',
  `unique_key` varchar(255) COMMENT 'unique_key for the field',
  `value` text,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_type` (`uid`,`utype`),
  KEY `unique_key` (`unique_key` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_privacy_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `privacy_id` int(11) NOT NULL COMMENT 'key to social_privacy.id',
  `uid` int(11) NOT NULL COMMENT 'userid or profileid',
  `utype` varchar(64) NOT NULL COMMENT 'user or profile',
  `value` int(11) DEFAULT '0',
  `params` text NULL,
  PRIMARY KEY (`id`),
  KEY `privacy_id` (`privacy_id`),
  KEY `uid_type` (`uid`,`utype`),
  KEY `uid_type_value` (`uid`,`utype`,`value`),
  KEY `idx_privacy_uid_type` (`privacy_id`, `uid`, `utype`),
  KEY `idx_uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
