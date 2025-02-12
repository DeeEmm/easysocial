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

class SocialMaintenanceScriptUpdateCommentPhotoElements extends SocialMaintenanceScript
{
	public static $title = 'Update photo element in comments.';
	public static $description = 'Standardize the photo upload element used in comments.';

	public function main()
	{
		$db = ES::db();

		$mapping = array('stream.group.add' => 'stream.group.upload',
				'photos.group.add' => 'photos.group.upload',
				'stream.page.add' => 'stream.page.upload',
				'photos.page.add' => 'photos.page.upload');

		foreach ($mapping as $old => $new) {
			$query = "update `#__social_comments` set `element` = " . $db->Quote($new);
			$query .= " where `element` = " . $db->Quote($old);

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
}
