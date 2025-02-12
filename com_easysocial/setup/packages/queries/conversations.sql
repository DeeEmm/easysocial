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


CREATE TABLE IF NOT EXISTS `#__social_conversations` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NULL DEFAULT '',
	`title_alias` varchar(64) DEFAULT '',
	`created` datetime NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`lastreplied` datetime NOT NULL,
	`type` tinyint(3) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_conversations_message` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`conversation_id` bigint(20) NOT NULL,
	`type` varchar(200) NOT NULL,
	`message` text,
	`created` datetime NOT NULL,
	`created_by` bigint(20) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `conversation_id` (`conversation_id`),
	KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_conversations_message_maps` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`user_id` bigint(20) NOT NULL,
	`conversation_id` bigint(20) NOT NULL,
	`message_id` bigint(20) NOT NULL,
	`isread` tinyint(1) NOT NULL DEFAULT '0',
	`state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - publish, 2 - archive, 3 - trash',
	PRIMARY KEY (`id`),
	KEY `node_id` (`user_id`),
	KEY `conversation_id` (`conversation_id`),
	KEY `message_id` (`message_id`),
	KEY `idx_user_conversation` (`user_id`, `state`, `conversation_id`, `message_id`),
	KEY `idx_user_conversation_isread` (`user_id`, `state`, `isread`, `conversation_id`, `message_id`),
	KEY `idx_newmsg_count` (`conversation_id`, `isread`, `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_conversations_participants` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`conversation_id` bigint(20) NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`state` tinyint(3) NOT NULL,
	`typing` tinyint(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `social_conversation_maps_conversation_id` (`conversation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_conversations_message_deleted` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`message_id` int(11) NOT NULL,
	`conversation_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `social_conversation_message_deleted_idx` (`message_id`, `conversation_id`, `user_id`),
	KEY `social_conversation_message_deleted_id` (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
