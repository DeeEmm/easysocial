<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptSetFieldMiniRegistration extends SocialMaintenanceScript
{
	public static $title = 'Set mini registration value for fields';

	public static $description = 'Set some fields to display in mini registration by default';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		/*
		update jos_social_fields as a
		left join jos_social_apps as b
		on a.app_id = b.id
		set visible_mini_registration = 1
		where b.type = 'fields'
		and b.group = 'user'
		and b.element in ('joomla_fullname', 'joomla_username', 'joomla_password', 'joomla_email');
		 */

		$sql->update('#__social_fields', 'a')
			->leftjoin('#__social_apps', 'b')
			->on('a.app_id', 'b.id')
			->set('visible_mini_registration', 1)
			->where('b.type', 'fields')
			->where('b.group', 'user')
			->where('b.element', array('joomla_fullname', 'joomla_username', 'joomla_password', 'joomla_email'), 'in');

		$db->setQuery($sql);
		$db->query();

		return true;
	}
}
