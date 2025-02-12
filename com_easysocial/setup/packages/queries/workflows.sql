/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

CREATE TABLE IF NOT EXISTS `#__social_workflows` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`type` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_title` (`title` (190)),
	KEY `idx_type` (`type` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_workflows_maps` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` int(11) NOT NULL,
	`workflow_id` int(11) NOT NULL,
	`type` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `workflow_id` (`workflow_id`),
	KEY `uid` (`uid`),
	KEY `idx_workflow_type` (`workflow_id`, `type` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
