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

class SocialMaintenanceScriptFixMarketplaceCategoryAvatars extends SocialMaintenanceScript
{
	public static $title = "Fix incorrect storage of marketplace category avatars";
	public static $description = 'Prior to this update, avatars were stored incorrectly without the type';

	public function main()
	{
		$db = ES::db();

		$query = [
			'select * from `#__social_marketplaces_categories`'
		];

		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (!$items) {
			return true;
		}

		// Category avatar storage path for marketplaces
		$config = ES::config();
		$typePath = $config->get('avatars.storage.' . SOCIAL_TYPE_MARKETPLACE);
		$avatarContainer = JPATH_ROOT . '/' . ES::cleanPath($config->get('avatars.storage.container'));
		$categoryAvatarStoragePath = $avatarContainer . '/' . $typePath;

		if (!JFolder::exists($categoryAvatarStoragePath)) {
			JFolder::create($categoryAvatarStoragePath);
		}

		foreach ($items as $item) {
			$category = ES::table('MarketplaceCategory');
			$category->bind($item);

			$avatar = ES::Table('Avatar');
			$exists = $avatar->load([
				'uid' => $category->id,
				'type' => SOCIAL_TYPE_MARKETPLACE
			]);

			if (!$exists) {
				continue;
			}

			// The bug in 4.0.1 causes avatars to be stored in /media/com_easysocial/avatars//[ID]
			// We need to move these folders to /media/com_easysocial/avatars/marketplaces/[ID]
			$avatarsPath = $avatarContainer . '/' . $category->id;

			$exists = JFolder::exists($avatarsPath);

			if (!$exists) {
				continue;
			}

			// If it exists, we need to relocate it
			$newPath = $categoryAvatarStoragePath . '/' . $category->id;

			// If the new path already exists, there is a possibilty someone has fixed their site so we should skip it
			if (JFolder::exists($newPath)) {
				JFolder::delete($avatarsPath);

				continue;
			}

			JFolder::move($avatarsPath, $newPath);
		}

		return true;
	}
}
