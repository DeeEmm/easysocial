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

CREATE TABLE IF NOT EXISTS `#__social_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_id` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `type` text NOT NULL,
  `client` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `secret` text NOT NULL,
  `created` datetime NOT NULL,
  `expires` varchar(255) NOT NULL,
  `pull` tinyint(3) NOT NULL,
  `push` tinyint(3) NOT NULL,
  `params` text NOT NULL,
  `last_pulled` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_pushed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `permissions` text NULL,
  PRIMARY KEY (`id`),
  KEY `pull` (`pull`,`push`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `#__social_oauth_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_id` int(11) NOT NULL,
  `remote_id` int(11) NOT NULL,
  `remote_type` varchar(255) NOT NULL,
  `local_id` int(11) NOT NULL,
  `local_type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
