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

CREATE TABLE IF NOT EXISTS `#__social_config` (
	`type` VARCHAR(255) NOT NULL,
	`value` text NOT NULL,
	`value_binary` blob NULL,
	KEY `type` (`type` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_indexer` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` int(11) NOT NULL,
	`utype` varchar(64) DEFAULT NULL,
	`component` varchar(64) DEFAULT NULL,
	`title` text NOT NULL,
	`content` longtext NOT NULL,
	`link` text,
	`last_update` datetime NOT NULL,
	`ucreator` bigint(20) unsigned DEFAULT '0',
	`image` text,
	PRIMARY KEY (`id`),
	KEY `social_source` (`uid`,`utype`,`component`),
	FULLTEXT KEY `social_indexer_snapshot` (`title`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_likes` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`reaction` varchar(255) NULL DEFAULT 'like',
	`type` varchar(255) NOT NULL,
	`uid` bigint(20) NOT NULL,
	`stream_id` bigint(20) NULL default 0,
	`uri` text NULL,
	`created_by` bigint(20) unsigned DEFAULT '0',
	`react_as` VARCHAR(64) DEFAULT 'user',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`params` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `social_likes_uid` (`uid`),
	KEY `social_likes_contenttype` (`type` (190)),
	KEY `social_likes_createdby` (`created_by`),
	KEY `social_likes_content_type` (`type` (190),`uid`),
	KEY `social_likes_content_type_by` (`type` (190),`uid`,`created_by`),
	KEY `idx_stream_id` (`stream_id`),
	KEY `idx_reaction` (`reaction` (25)),
	KEY `idx_usermood` (`created_by`,`created`,`reaction` (25)),
	KEY `idx_rections_uid_type` (`reaction` (64), `uid`, `type` (128))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_locations` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL DEFAULT 0,
	`type` text NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`created` datetime NOT NULL,
	`short_address` varchar(255) NOT NULL DEFAULT '',
	`address` TEXT,
	`longitude` varchar(255) NOT NULL,
	`latitude` varchar(255) NOT NULL,
	`params` text,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_regions` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL DEFAULT 0,
	`type` varchar(255) NOT NULL DEFAULT '',
	`name` varchar(255) NOT NULL DEFAULT '',
	`code` varchar(64) NOT NULL DEFAULT '',
	`parent_uid` bigint(20) NOT NULL DEFAULT 0,
	`parent_type` varchar(255) NOT NULL DEFAULT '',
	`state` tinyint(4) NOT NULL DEFAULT 1,
	`ordering` int(11) NOT NULL DEFAULT 0,
	`params` TEXT,
	PRIMARY KEY (`id`),
	KEY `idx_country` (`type` (64), `state`, `ordering`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_logger` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`file` varchar(255) NOT NULL,
	`line` varchar(255) NOT NULL,
	`message` TEXT,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_mailer` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`sid` int(11) NOT NULL DEFAULT '0' COMMENT 'stream id',
	`sender_name` TEXT,
	`sender_email` TEXT,
	`replyto_email` TEXT,
	`recipient_name` TEXT,
	`recipient_email` TEXT,
	`title` TEXT,
	`content` TEXT,
	`template` TEXT,
	`html` tinyint(4) NOT NULL DEFAULT 0,
	`state` tinyint(4) NOT NULL DEFAULT 0,
	`response` TEXT,
	`created` datetime NOT NULL,
	`params` TEXT,
	`priority` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1 - Low , 2 - Medium , 3 - High , 4 - Highest',
	`language` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `state` (`state`),
	KEY `idx_sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_migrators` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`oid` bigint(20) unsigned NOT NULL,
	`element` varchar(100) NOT NULL,
	`component` varchar(100) NOT NULL,
	`uid` bigint(20) unsigned NOT NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `uid` (`uid`),
	KEY `component_content` (`component` (64),`oid`,`element` (64))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Store migrated content id and map with easysocial item id.';


CREATE TABLE IF NOT EXISTS `#__social_registrations` (
	`session_id` varchar(128) NOT NULL,
	`profile_id` bigint(20) NOT NULL DEFAULT 0,
	`created` datetime NOT NULL,
	`values` text,
	`step` bigint(20) NOT NULL DEFAULT 0,
	`step_access` text,
	`errors` text,
	UNIQUE KEY `session_id` (`session_id`),
	KEY `profile_id` (`profile_id`),
	KEY `step` (`step`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_reports` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` text NOT NULL,
	`message` text NOT NULL,
	`extension` varchar(255) NOT NULL,
	`uid` int(11) NOT NULL,
	`type` varchar(255) NOT NULL,
	`created_by` int(11) NOT NULL,
	`actor_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Report item actor user id',
	`ip` varchar(255) NOT NULL,
	`created` datetime NOT NULL,
	`state` tinyint(3) NOT NULL DEFAULT '0',
	`url` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_shares` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`element` varchar(255) NOT NULL,
	`user_id` bigint(20) NOT NULL,
	`content` text NOT NULL,
	`created` datetime NOT NULL,
	`share_as` VARCHAR(64) DEFAULT 'user',
	`params` text,
	PRIMARY KEY (`id`),
	KEY `shares_element` (`uid`,`element` (128)),
	KEY `shares_element_user` (`uid`,`element` (128),`user_id`),
	KEY `shares_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_themes` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`element` varchar(255) NOT NULL,
	`params` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `element` (`element` (190))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_themes_overrides` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`file_id` text NOT NULL,
	`notes` text NOT NULL,
	`contents`  LONGTEXT NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `#__social_subscriptions` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uid` int(11) NOT NULL COMMENT 'object id e.g userid, groupid, streamid and etc',
	`type` varchar(64) NOT NULL COMMENT 'subscription type e.g. user, group, stream and etc',
	`user_id` int(11) DEFAULT '0',
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `uid_type` (`uid`,`type`),
	KEY `uid_type_user` (`uid`,`type`,`user_id`),
	KEY `uid_type_email` (`uid`,`type`),
	KEY `idx_uid` ( `uid` ),
	KEY `idx_type_userid` ( `type`, `user_id` ),
	KEY `idx_userid` (`user_id`),
	KEY `idx_userid_type` (`user_id`, `type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__social_users` (
	`user_id` bigint(20) NOT NULL,
	`alias` varchar(255) NOT NULL DEFAULT '',
	`state` tinyint(3) NOT NULL,
	`params` text,
	`connections` int(11) NOT NULL DEFAULT 0,
	`permalink` VARCHAR( 255 ) NOT NULL,
	`type` varchar(255) NOT NULL DEFAULT 'joomla',
	`auth` varchar(255) NOT NULL DEFAULT '',
	`completed_fields` int(11) NOT NULL DEFAULT 0,
	`reminder_sent` tinyint(1) DEFAULT 0,
	`activation_reminder_sent` tinyint(1) DEFAULT 0,
	`require_reset` tinyint(1) DEFAULT 0,
	`block_date` datetime null,
	`block_period` int(11) DEFAULT 0,
	`social_params` longtext NOT NULL,
	`verified` tinyint(3) DEFAULT 0,
	`affiliation_id` VARCHAR(32) NOT NULL DEFAULT 0,
	`robots` VARCHAR(16) DEFAULT 'inherit',
	PRIMARY KEY (`user_id`),
	KEY `state` (`state`),
	KEY `alias` (`alias` (190)),
	KEY `connections` (`connections`),
	KEY `permalink` (`permalink` (190)),
	KEY `idx_types` (`user_id`,`type` (128)),
	KEY `idx_reminder` (`reminder_sent`),
	KEY `idx_activation_reminder` (`activation_reminder_sent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_users_import_history` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`user_id` bigint(20) NOT NULL,
	`data` text NOT NULL,
	`params` text NOT NULL,
	`created` datetime NOT NULL,
	`state` tinyint(3) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_verification_requests` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`uid` int(11) NOT NULL,
	`type` varchar(255) NOT NULL,
	`created_by` int(11) NOT NULL,
	`message` text,
	`params` text,
	`created` datetime NOT NULL,
	`state` tinyint(3) NOT NULL,
	`ip` varchar(15) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_comments` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`element` varchar(255) NOT NULL,
	`uid` bigint(20) NOT NULL,
	`comment` text NOT NULL,
	`stream_id` bigint(20) NULL default 0,
	`created_by` bigint(20) unsigned DEFAULT '0',
	`post_as` VARCHAR(64) DEFAULT 'user',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`depth` bigint(10) DEFAULT '0',
	`parent` bigint(20) DEFAULT '0',
	`child` bigint(20) DEFAULT '0',
	`lft` bigint(20) DEFAULT '0',
	`rgt` bigint(20) DEFAULT '0',
	`params` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `social_comments_uid` (`uid`),
	KEY `social_comments_type` (`element` (190)),
	KEY `social_comments_createdby` (`created_by`),
	KEY `social_comments_content_type` (`element` (190),`uid`),
	KEY `social_comments_content_type_by` (`element` (190),`uid`,`created_by`),
	KEY `social_comments_content_parent` (`element` (190),`uid`,`parent`),
	KEY `idx_comment_batch` (`stream_id`, `element` (190), `uid`),
	KEY `idx_comment_stream_id` (`stream_id`),
	KEY `idx_parents` (`parent`, `lft`),
	KEY `idx_rgt` (`rgt`),
	KEY `idx_lft` (`lft`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_languages` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`locale` varchar(255) NOT NULL,
	`updated` datetime NOT NULL,
	`state` tinyint(3) NOT NULL,
	`translator` varchar(255) NOT NULL,
	`progress` int(11) NOT NULL,
	`params` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_links` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hash` varchar(255) NOT NULL,
	`data` text NOT NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `hash` (`hash` (190))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_search_filter` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`element` varchar(255) NOT NULL,
	`uid` bigint(20) NOT NULL,
	`title` varchar(255) NOT NULL,
	`alias` varchar(255) NOT NULL,
	`filter` text NOT NULL,
	`created_by` bigint(20) unsigned DEFAULT '0',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`sitewide` tinyint(1) default 0,
	PRIMARY KEY (`id`),
	KEY `idx_searchfilter_element_id` (`element` (200),`uid`),
	KEY `idx_searchfilter_owner` (`element` (200),`uid`, `created_by`),
	KEY `idx_searchfilter_alias` (`alias` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_storage_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`object_id` int(11) NOT NULL,
	`object_type` varchar(255) NOT NULL,
	`target` varchar(255) NOT NULL,
	`state` tinyint(3) NOT NULL,
	`created` datetime NOT NULL,
	`message` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_moods` (
	`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key of the row',
	`namespace` varchar(255) NOT NULL COMMENT 'Determines if this item is tied to a specific item',
	`namespace_uid` int(11) NOT NULL DEFAULT 0,
	`icon` varchar(255) NOT NULL COMMENT 'Contains the css class for the emoticon',
	`verb` varchar(255) NOT NULL COMMENT 'Feeling, Watching, Eating etc',
	`subject` text NOT NULL COMMENT 'Happy, Sad, Angry etc',
	`custom` tinyint(3) NOT NULL COMMENT 'Determines if the user supplied a custom text',
	`text` text NOT NULL COMMENT 'If there is a custom text, based on the custom column, this text will be used.',
	`user_id` int(11) NOT NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_broadcasts` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`stream_id` int(11) NOT NULL,
	`target_id` int(11) NOT NULL,
	`target_type` varchar(255) NOT NULL,
	`title` varchar(255) NOT NULL,
	`content` text NOT NULL,
	`link` text NOT NULL,
	`state` tinyint(3) NOT NULL,
	`created` datetime NOT NULL,
	`created_by` int(11) NOT NULL,
	`expiry_date` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `idx_broadcast` (`target_id`, `target_type` (128), `state`, `created`),
	KEY `idx_created` (`created`),
	KEY `idx_stream_id` (`stream_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_bookmarks` (
	`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
	`uid` int(11) NOT NULL COMMENT 'The bookmarked item id',
	`type` varchar(255) NOT NULL COMMENT 'The bookmarked type',
	`created` datetime NOT NULL,
	`user_id` int(11) NOT NULL COMMENT 'The owner of the bookmarked item',
	PRIMARY KEY (`id`),
	KEY `uid` (`uid`,`type` (190)),
	KEY `user_id` (`user_id`),
	KEY `idx_uid` (`uid`),
	KEY `idx_user_utype` (`uid`, `type` (190), `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_block_users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`target_id` int(11) NOT NULL,
	`reason` text NOT NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `user_id` (`user_id`,`target_id`),
	KEY `idx_userid` (`user_id`),
	KEY `idx_targetid` (`target_id`),
	KEY `idx_target_user` (`target_id`, `user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_links_images` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`source_url` text NOT NULL,
	`internal_url` text NOT NULL,
	`storage` varchar(255) NOT NULL DEFAULT 'joomla',
	PRIMARY KEY (`id`),
	KEY `idx_storage_cron` (`storage` (190))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_polls_users` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`poll_id` bigint(20) unsigned NOT NULL,
	`poll_itemid` bigint(20) not null default 0,
	`user_id` bigint(20) not null,
	`session_id` varchar(255) NULL,
	`state` tinyint(1) NOT NULL default 1,
	 PRIMARY KEY (`id`),
	 KEY `idx_pollid` (`poll_id`),
	 KEY `idx_userid` (`user_id`),
	 KEY `idx_pollitem` (`poll_itemid`),
	 KEY `idx_poll_user` (`poll_id`, `user_id`),
	 KEY `idx_poll_item_user` (`poll_id`, `poll_itemid`, `user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_polls_items` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`poll_id` bigint(20) unsigned NOT NULL,
	`value` text not null,
	`count` bigint(20) not null default 0,
	 PRIMARY KEY (`id`),
	 KEY `idx_pollid` (`poll_id`),
	 KEY `idx_polls` (poll_id, id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_polls` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`element` varchar(255) NOT NULL,
	`uid` bigint(20) NOT NULL,
	`title` text NOT NULL,
	`multiple` tinyint(1) NULL default 0,
	`locked` tinyint(1) NULL default 0,
	`cluster_id` bigint(20) null,
	`created_by` bigint(20) unsigned DEFAULT '0',
	`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`expiry_date` datetime NULL,
	`state` tinyint(1) NOT NULL default 1,
	PRIMARY KEY (`id`),
	KEY `idx_element_id` (`element` (128),`uid`),
	KEY `idx_clusterid` (`cluster_id`),
	KEY `idx_state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_apps_directory` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`app_id` int(11) NOT NULL,
	`category` varchar(255) NOT NULL,
	`version` varchar(255) NOT NULL,
	`title` text NOT NULL,
	`info` text NOT NULL,
	`price` varchar(255) NOT NULL,
	`logo` text NOT NULL,
	`element` varchar(255) NOT NULL,
	`group` varchar(255) NOT NULL,
	`type` varchar(255) NOT NULL,
	`permalink` text NOT NULL,
	`created` datetime NOT NULL,
	`updated` datetime NOT NULL,
	`download` tinyint(3) NOT NULL,
	`version_checking` tinyint(3) NOT NULL,
	`raw` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_reactions` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`action` varchar(255) NOT NULL,
	`published` tinyint(3) NOT NULL,
	`created` datetime NOT NULL,
	`params` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_emoticons` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`icon` text NOT NULL,
	`type` varchar(64) NULL,
	`state` tinyint(3) NOT NULL,
	`created` datetime NOT NULL,
	PRIMARY KEY (`id`),
	KEY `emoticons_title` (`title` (190))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_download` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `state` tinyint(3) NOT NULL default 0,
  `params` longtext NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`userid`),
  KEY `idx_state` (`state`),
  KEY `idx_created` (`created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_backgrounds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `params` longtext NOT NULL,
  `state` tinyint(3) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sefurl` text NOT NULL,
  `rawurl` text NOT NULL,
  `params` longtext NULL,
  `custom` tinyint(1) default 0,
  PRIMARY KEY (`id`),
  KEY `sefurl` (`sefurl` (200)),
  KEY `rawurl` (`rawurl` (200)),
  KEY `custom` (`custom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_optimizer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `status` tinyint(3) NOT NULL,
  `log` longtext NOT NULL,
  `created` datetime NOT NULL,
  `filepath` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `idx_uid_type` (`uid`,`type`) USING BTREE,
  KEY `idx_uid_type_status` (`uid`,`type`,`status`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_honeypot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  `created` datetime NOT NULL,
  `key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_storage_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT 0,
  `notify` tinyint(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `element` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `updated` datetime NOT NULL,
  `state` tinyint(3) NOT NULL,
  `version` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_restapi_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varbinary(192) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_sessionid` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
