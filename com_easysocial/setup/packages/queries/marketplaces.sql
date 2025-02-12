/*
* @package    EasySocial
* @copyright  Copyright (C) StackIdeas Private Limited. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
CREATE TABLE IF NOT EXISTS `#__social_marketplaces` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL DEFAULT '',
	`description` text NOT NULL,
	`user_id` int(11) NOT NULL,
	`uid` int(11) NOT NULL DEFAULT '0',
	`type` varchar(255) NOT NULL DEFAULT '',
	`post_as` VARCHAR(64) DEFAULT 'user',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`state` tinyint(3) NOT NULL,
	`isnew` tinyint(1) NOT NULL DEFAULT '0',
	`scheduled` tinyint(3) NOT NULL DEFAULT '0',
	`featured` tinyint(3) NULL DEFAULT '0',
	`category_id` int(11) NOT NULL,
	`price` decimal(15,2) NOT NULL DEFAULT '0.00',
	`currency` varchar(11) DEFAULT NULL,
	`condition` varchar(255) NOT NULL DEFAULT '',
	`stock` int(11) DEFAULT NULL,
	`album_id` int(11) DEFAULT NULL,
	`hits` int(11) NOT NULL DEFAULT 0,
	`params` text,
	`access` int(11) NOT NULL DEFAULT 0,
	`custom_access` text,
	`longitude` varchar(255) NOT NULL DEFAULT '' COMMENT 'The longitude value of the marketplace listing',
	`latitude` varchar(255) NOT NULL DEFAULT '' COMMENT 'The latitude value of the marketplace listing',
	`address` text COMMENT 'The full address value of the marketplace listing',
	PRIMARY KEY (`id`),
	KEY `title` (`title`(200),`user_id`,`state`,`featured`,`category_id`),
	KEY `idx_userid` (`user_id`),
	KEY `idx_type_featured` (`state`,`type`(64),`featured`),
	KEY `idx_type_userid` (`state`,`type`(64),`user_id`),
	KEY `idx_type_userid_featured` (`state`,`type`(64),`user_id`,`featured`),
	KEY `idx_categoryid` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_marketplaces_categories` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`alias` varchar(255) NOT NULL,
	`uid` int(11) NOT NULL,
	`parent_id` int(11) DEFAULT '0',
	`container` tinyint(3) NOT NULL DEFAULT '0',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`state` tinyint(3) NOT NULL,
	`ordering` int(11) NOT NULL DEFAULT 0,
	`lft` int(11) unsigned DEFAULT '0',
	`rgt` int(11) unsigned DEFAULT '0',
	`params` text,
	PRIMARY KEY (`id`),
	KEY `idx_parentid` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
