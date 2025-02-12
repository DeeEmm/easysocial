<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateMailerTableColumn extends SocialMaintenanceScript
{
	public static $title = "Update mailer table to support Joomla 4.";
	public static $description = 'Making the necessary changes for the mailer tables to support Joomla 4 strict mode';

	public function main()
	{
		$db = ES::db();

		$queries = [];

		$queries[] = "ALTER TABLE `#__social_mailer` 
					MODIFY `sender_name` TEXT,
					MODIFY `sender_email` TEXT,
					MODIFY `replyto_email` TEXT,
					MODIFY `recipient_name` TEXT,
					MODIFY `recipient_email` TEXT,
					MODIFY `title` TEXT,
					MODIFY `content` TEXT,
					MODIFY `template` TEXT,
					MODIFY `response` TEXT,
					MODIFY `params` TEXT,
					ALTER `html` SET DEFAULT 0,
					ALTER `state` SET DEFAULT 0,
					ALTER `priority` SET DEFAULT 0,
					ALTER `language` SET DEFAULT ''
				";

		foreach ($queries as $query) {
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}
