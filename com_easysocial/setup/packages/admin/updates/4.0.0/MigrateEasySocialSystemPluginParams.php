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

class SocialMaintenanceScriptMigrateEasySocialSystemPluginParams extends SocialMaintenanceScript
{
	public static $title = 'Migrate EasySocial System Plugin Parameters';
	public static $description = 'Migrating EasySocial plugin parameters for com_user registration redirection.';

	public function main()
	{
		$db = ES::db();

		$query = [
			'SELECT `params` FROM `#__extensions`',
			'WHERE `type`=' . $db->Quote('plugin') . ' AND `element`=' . $db->Quote('easysocial') . ' AND `folder`=' . $db->Quote('system')
		];


		$db->setQuery($query);
		$result = $db->loadResult();

		$params = json_decode($result, true);

		if (is_null($params)) {
			$params = [
				'redirection' => 0
			];
		}

		$model = ES::model('Config');

		return $model->updateConfig($params);
	}
}
