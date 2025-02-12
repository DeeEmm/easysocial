/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

CREATE TABLE IF NOT EXISTS `#__social_advertisers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo` text,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `advertiser_name` (`name` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advertiser_id` int(11) NOT NULL,
  `intro` text,
  `title` varchar(255) NOT NULL,
  `cover` text,
  `link` text,
  `content` text,
  `priority` tinyint(4) NOT NULL COMMENT '1 - Low , 2 - Medium , 3 - High , 4 - Highest',
  `button_type` tinyint(4) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `click` int(11) NOT NULL DEFAULT 0,
  `view` int(11) NOT NULL DEFAULT 0,
  `log` text,
  `params` text,
  PRIMARY KEY (`id`),
  KEY `ads_title` (`title` (190)),
  KEY `ads_advertiser` (`advertiser_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

