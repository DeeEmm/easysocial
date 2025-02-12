<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateBlogCreationClusterAlert extends SocialMaintenanceScript
{
	public static $title = 'Update blog alert for cluster';
	public static $description = 'Update blog rule for cluster when there is a new blog post created';

	public function main()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "UPDATE `#__social_alert` SET `rule` = " . $db->Quote('group.create');
		$query .= " WHERE `extension` = " . $db->Quote('com_easyblog');
		$query .= " AND `element` = ". $db->Quote('blog') . " AND `rule` = ". $db->Quote('group.blog.create');

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		$query = "UPDATE `#__social_alert` SET `rule` = " . $db->Quote('event.create');
		$query .= " WHERE `extension` = " . $db->Quote('com_easyblog');
		$query .= " AND `element` = ". $db->Quote('blog') . " AND `rule` = ". $db->Quote('event.blog.create');

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		$query = "UPDATE `#__social_alert` SET `rule` = " . $db->Quote('page.create');
		$query .= " WHERE `extension` = " . $db->Quote('com_easyblog');
		$query .= " AND `element` = ". $db->Quote('blog') . " AND `rule` = ". $db->Quote('page.blog.create');

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}
}
