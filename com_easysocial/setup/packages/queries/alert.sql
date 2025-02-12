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


CREATE TABLE IF NOT EXISTS `#__social_alert` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `extension` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `rule` varchar(255) NOT NULL,
  `email` int(1) NOT NULL DEFAULT '1',
  `system` int(1) NOT NULL DEFAULT '1',
  `core` int(1) NOT NULL DEFAULT '0',
  `app` int(1) NOT NULL DEFAULT '0',
  `field` tinyint(3) NOT NULL DEFAULT '0',
  `group` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `email_published` tinyint(3) NOT NULL DEFAULT '1',
  `system_published` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_alert_field` (`field`),
  KEY `idx_alert_published` (`published`),
  KEY `idx_alert_element` (`element` (190)),
  KEY `idx_alert_rule` (`rule` (190)),
  KEY `idx_alert_published_field` (`published`, `field`),
  KEY `idx_alert_isfield` (`published`, `field`, `element` (64), `rule` (64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_alert_map` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT '0',
  `alert_id` bigint(20) NOT NULL,
  `email` int(1) DEFAULT '1',
  `system` int(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_alertmap_alertid` (`alert_id`),
  KEY `idx_alertmap_userid` (`user_id`),
  KEY `idx_alertmap_alertuser` (`alert_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


