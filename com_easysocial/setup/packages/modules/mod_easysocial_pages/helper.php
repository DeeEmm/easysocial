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

class EasySocialModPagesHelper
{
	public static function getPages(&$params)
	{
		$my = ES::user();
		$model = ES::model('Pages');

		// Get filter type
		$filter = $params->get('filter', 0);

		// Get the ordering of the pages
		$ordering = $params->get('ordering', 'latest');

		// Default options
		$options = array();

		// Limit the number of pages based on the params
		$options['limit'] = $params->get('display_limit', 5);
		$options['ordering'] = $ordering;
		$options['state'] = SOCIAL_STATE_PUBLISHED;
		$options['inclusion'] = $params->get('page_inclusion');

		$category = $params->get('category');

		if ($category) {
			$options['category'] = $category;
		}

		if ($filter == 0) {
			$pages = $model->getPages($options);
		}

		// Featured pages only
		if ($filter == 2) {
			$options['featured'] = true;

			$pages = $model->getPages($options);
		}

		// Pages from logged in user
		if ($filter == 3) {
			$options['types'] = 'currentuser';
			$options['userid'] = $my->id;
			$pages = $model->getPages($options);
		}

		return $pages;
	}
}
