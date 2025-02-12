<?php
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
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateStoryPrivacyOptions extends SocialMaintenanceScript
{
	public static $title = 'Update privacy options to support custom fields.';
	public static $description = 'Update privacy options to support custom fields.';

	public function main()
	{
		$db = ES::db();

		$newOptions = '{"options":["public","member","friend","only_me","custom","field"]}';

		// these rules having the same privacy options.
		$rules = array('story' => array('view'),
				'photos' => array('view'),
				'albums' => array('view'),
				'polls' => array('view'),
				'videos' => array('view'),
				'audios' => array('view')
			);

		foreach ($rules as $rule => $commands) {
			foreach ($commands as $cmd) {
				$query = "update `#__social_privacy` set `options` = " . $db->Quote($newOptions);
				$query .= " where `type` = " . $db->Quote($rule);
				$query .= " and `rule` = " . $db->Quote($cmd);

				$db->setQuery($query);
				$db->query();
			}
		}

		return true;
	}
}
