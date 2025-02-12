<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateAdvertisementPaths extends SocialMaintenanceScript
{
	public static $title = 'Update storage location for advertisements';
	public static $description = 'To prevent ad blocks, this script will rename the ads and advertiser folder to a better name';

	public function main()
	{
		$config = ES::config();

		// Rename ads path
		$ads = JPATH_ROOT . $config->get('ads.storage') . '/ads';

		if (JFolder::exists($ads)) {
			$newAds = JPATH_ROOT . $config->get('ads.storage') . '/covers';

			JFolder::move($ads, $newAds);
		}

		$previousPath = ltrim($config->get('ads.storage'), '/') . '/ads/';

		$db = ES::db();
		$query = 'UPDATE `#__social_ads` SET `cover` = replace(`cover`, "' . $previousPath . '", "")';

		// echo $query;exit;
		$db->setQuery($query);
		$db->Query();

		$advertiser = JPATH_ROOT . $config->get('ads.storage') . '/advertiser';

		if (JFolder::exists($advertiser)) {
			$newAdvertiser = JPATH_ROOT . $config->get('ads.storage') . '/logos';

			JFolder::move($advertiser, $newAdvertiser);
		}

		$previousPath = ltrim($config->get('ads.storage'), '/') . '/advertiser/';

		$query = 'UPDATE `#__social_advertisers` SET `logo` = replace(`logo`, "' . $previousPath . '", "")';
		$db->setQuery($query);
		$db->Query();

		return true;
	}
}
