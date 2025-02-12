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

CREATE TABLE IF NOT EXISTS `#__social_stream` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`actor_id` bigint(20) unsigned NOT NULL,
	`alias` varchar(255) DEFAULT '',
	`actor_type` varchar(64) DEFAULT 'user',
	`post_as` VARCHAR(64) DEFAULT 'user',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`edited` DATETIME NULL,
	`title` text,
	`content` text,
	`context_type` varchar(64) DEFAULT '',
	`verb` varchar(64) DEFAULT '',
	`stream_type` varchar(15) DEFAULT NULL,
	`sitewide` tinyint(1) DEFAULT '0',
	`target_id` BIGINT( 20 ) NOT NULL DEFAULT 0,
	`location_id` int(11) NOT NULL DEFAULT 0,
	`mood_id` int(11) NOT NULL DEFAULT 0,
	`background_id` int(11) NOT NULL DEFAULT 0,
	`with` text,
	`ispublic` tinyint(3) default 0 NOT NULL,
	`cluster_id` int(11) default 0 null,
	`cluster_type` varchar(64) null,
	`cluster_access` tinyint(3) default 0,
	`params` longtext null,
	`state` tinyint(3) default 1 NOT NULL,
	`privacy_id` int(11) NULL,
	`access` int(11) default 0 NOT NULL,
	`custom_access` text NULL,
	`field_access` tinyint(3) default 0,
	`last_action` varchar(255) NULL,
	`last_userid` bigint(20) unsigned default 0,
	`last_action_date` datetime NULL,
	`sticky_id` bigint(20) unsigned default 0,
	`anywhere_id` varchar(255) DEFAULT NULL,
	`datafix` tinyint(1) default 1,
	PRIMARY KEY (`id`),
	KEY `stream_actor` (`actor_id`),
	KEY `stream_created` (`created`),
	KEY `stream_modified` (`modified`),
	KEY `stream_alias` (`alias` (190)),
	KEY `stream_source` (`actor_type`),
	KEY `idx_stream_context_type` ( `context_type` ),
	KEY `idx_stream_target` ( `target_id` ),
	KEY `idx_actor_modified` ( `actor_id`, `modified` ),
	KEY `idx_target_context_modified` ( `target_id`, `context_type`, `modified` ),
	KEY `idx_sitewide_modified` ( `sitewide`, `modified` ),
	KEY `idx_ispublic` ( `ispublic`, `modified` ),
	KEY `idx_clusterid` ( `cluster_id` ),
	KEY `idx_cluster_items` ( `cluster_id`, `cluster_type`, `modified` ),
	KEY `idx_cluster_access` ( `cluster_id`, `cluster_access` ),
	KEY `idx_access` (`access`),
	KEY `idx_custom_access` (`access`, `custom_access` (200)),
	KEY `idx_stream_total_cluster` (`cluster_id`, `cluster_access`, `context_type`, `id`, `actor_id`),
	KEY `idx_stream_total_user` (`cluster_id`, `access`, `actor_id`, `context_type`),
	KEY `idx_stickyid` (`sticky_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_stream_history` (
	`id` bigint(20) unsigned NOT NULL,
	`actor_id` bigint(20) unsigned NOT NULL,
	`alias` varchar(255) DEFAULT '',
	`actor_type` varchar(64) DEFAULT 'user',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`edited` DATETIME NULL,
	`title` text,
	`content` text,
	`context_type` varchar(64) DEFAULT '',
	`verb` varchar(64) DEFAULT '',
	`stream_type` varchar(15) DEFAULT NULL,
	`sitewide` tinyint(1) DEFAULT '0',
	`target_id` BIGINT( 20 ) NOT NULL,
	`location_id` int(11) NOT NULL,
	`mood_id` int(11) NOT NULL,
	`with` text NOT NULL,
	`ispublic` tinyint(3) default 0 NOT NULL,
	`cluster_id` int(11) default 0 null,
	`cluster_type` varchar(64) null,
	`cluster_access` tinyint(3) default 0,
	`params` longtext null,
	`state` tinyint(3) default 1 NOT NULL,
	`privacy_id` int(11) NULL,
	`access` int(11) default 0 NOT NULL,
	`custom_access` text NULL,
	`field_access` tinyint(3) default 0,
	`last_action` varchar(255) NULL,
	`last_userid` bigint(20) unsigned default 0,
	`sticky_id` bigint(20) unsigned default 0,
	`anywhere_id` varchar(255) DEFAULT NULL,
	`datafix` tinyint(1) default 1,
	PRIMARY KEY (`id`),
	KEY `stream_history_created` (`created`),
	KEY `stream_history_modified` (`modified`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_assets` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`stream_id` int(11) NOT NULL,
	`type` varchar(255) NOT NULL,
	`data` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_hide` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` bigint(20) unsigned NOT NULL,
	`uid` bigint(20) unsigned NOT NULL,
	`type` varchar(255) NOT NULL,
	`context` varchar(255) DEFAULT NULL,
	`actor_id` bigint(20) DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `stream_hide_user` (`user_id`),
	KEY `stream_hide_uid` (`uid`),
	KEY `stream_hide_actorid` (`actor_id`),
	KEY `stream_hide_user_uid` (`user_id`,`uid`),
	KEY `idx_stream_hide_context` (`context` (190), `user_id`, `uid`, `actor_id`),
	KEY `idx_stream_hide_actor` (`actor_id`, `user_id`, `uid`, `context` (190)),
	KEY `idx_stream_hide_uid` (`uid`, `user_id`, `type` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_item` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`actor_id` bigint(20) unsigned NOT NULL,
	`actor_type` varchar(255) DEFAULT 'people',
	`context_type` varchar(64) DEFAULT '',
	`context_id` bigint(20) unsigned DEFAULT '0',
	`verb` varchar(64) DEFAULT '',
	`target_id` bigint(20) unsigned DEFAULT '0',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`uid` bigint(20) unsigned NOT NULL DEFAULT 0,
	`sitewide` tinyint(1) DEFAULT '0',
	`params` text null,
	`state` tinyint(3) default 1 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `activity_actor` (`actor_id`),
	KEY `activity_created` (`created`),
	KEY `activity_context` (`context_type`),
	KEY `activity_context_id` (`context_id`),
	KEY `idx_context_verb` (`context_type`, `verb`),
	KEY `idx_uid` (`uid`),
	KEY `idx_context_type_id` (`context_type`, `context_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_item_history` (
	`id` bigint(20) unsigned NOT NULL,
	`actor_id` bigint(20) unsigned NOT NULL,
	`actor_type` varchar(255) DEFAULT 'people',
	`context_type` varchar(64) DEFAULT '',
	`context_id` bigint(20) unsigned DEFAULT '0',
	`verb` varchar(64) DEFAULT '',
	`target_id` bigint(20) unsigned DEFAULT '0',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`uid` bigint(20) unsigned NOT NULL,
	`sitewide` tinyint(1) DEFAULT '0',
	`params` text null,
	`state` tinyint(3) default 1 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_history_uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_tags` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`stream_id` bigint(20) unsigned NOT NULL,
	`uid` bigint(20) unsigned NOT NULL,
	`utype` varchar(255) DEFAULT 'user',
	`with` tinyint(3) unsigned DEFAULT '0',
	`offset` int(11) DEFAULT '0',
	`length` int(11) DEFAULT '0',
	`title` varchar(255) NULL,
	`state` tinyint(1) default 1 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `streamtags_streamid` (`stream_id`),
	KEY `streamtags_uidtype` (`uid`,`utype` (64)),
	KEY `streamtags_uidoffset` (`stream_id`, `offset`),
	KEY `streamtags_title` (`title` (190)),
	KEY `streamtags_state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_stream_filter` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) unsigned NOT NULL,
	`utype` varchar(255) DEFAULT 'user',
	`title` varchar(255) not null,
	`alias` varchar(255) not null,
	`user_id` bigint(20) unsigned NOT NULL,
	`global` tinyint(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `streamfilter_uidtype` (`uid`, `utype` (64)),
	KEY `streamfilter_alias` (`alias` (190)),
	KEY `streamfilter_cluster_user` ( `uid`, `utype` (64), `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_filter_item` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`filter_id` bigint(20) unsigned NOT NULL,
	`type` varchar(255) NOT NULL,
	`content` TEXT NULL,
	PRIMARY KEY (`id`),
	KEY `filteritem_fid` (`filter_id`),
	KEY `filteritem_type` (`type` (190)),
	KEY `filteritem_fidtype` (`filter_id`, `type` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_sticky` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`stream_id` bigint(20) unsigned NOT NULL,
	`created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY `idx_streamid` (`stream_id`)
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


CREATE TABLE IF NOT EXISTS `#__social_stream_privacy_field` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`stream_id` int(11) NOT NULL,
	`field_key` varchar(255) COMMENT 'unique_key for the field',
	`field_value` varchar(255),
	PRIMARY KEY (`id`),
	KEY `idx_streamid` (`stream_id`),
	KEY `idx_fieldkey` (`field_key` (190)),
	KEY `idx_fieldvalue` (`field_value` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_stream_scheduled` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`stream_id` int(11) unsigned NOT NULL,
	`actor_id` bigint(20) unsigned NOT NULL,
	`actor_type` varchar(255) DEFAULT 'people',
	`context_type` varchar(64) DEFAULT '',
	`context_id` bigint(20) unsigned DEFAULT '0',
	`verb` varchar(64) DEFAULT '',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`scheduled` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`state` tinyint(3) default 1 NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_scheduled_uid` (`stream_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
