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

CREATE TABLE IF NOT EXISTS `#__social_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `item_id` int(11) NOT NULL DEFAULT 0,
  `item_type` varchar(255) NOT NULL DEFAULT '',
  `target_id` int(11) NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `creator_type` varchar(255) NOT NULL,
  `offset` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_targets` (`target_id`, `target_type` (200))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_tags_filter` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cid` bigint(20) NOT NULL,
  `filter_type` varchar(255) NOT NULL,
  `cluster_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_filter` (`user_id`, `filter_type` (200), `cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_tags_filter_item` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filter_id` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `idx_filter_id` (`filter_id`),
  KEY `idx_type` (`type` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
