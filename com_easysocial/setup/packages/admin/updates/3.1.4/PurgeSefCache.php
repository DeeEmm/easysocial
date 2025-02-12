<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptPurgeSefCache extends SocialMaintenanceScript
{
	public static $title = 'Purge existing SEF cache.';
	public static $description = 'Cleanup existing SEF urls so that system can regenerate the new SEF urls.';

	public function main()
	{
		$model = ES::model('Urls');

		// before purge, we need to get all the customized urls
		$customUrls = $model->getCustomUrls();

		$withCustom = $customUrls ? false : true;
		$state = $model->purge($withCustom);

		if ($state) {
			$cache = ES::fileCache();
			$cache->purge($customUrls);
		}

		return true;
	}
}
