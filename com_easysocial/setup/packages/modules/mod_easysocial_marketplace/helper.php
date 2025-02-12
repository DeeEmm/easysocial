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

class EasySocialModMarketplaceHelper
{
	public static function getListings(&$params)
	{
		$my = ES::user();
		$model = ES::model('Marketplaces');

		// Determine filter type
		$filter = $params->get('filter', 0);

		// Determine the ordering of the listings
		$ordering = $params->get('ordering', 'latest');

		// Default options
		$options = array();

		// Limit the number of listings based on the params
		$options['limit'] = $params->get('display_limit', 5);
		$options['sort'] = $ordering;
		$options['state'] = SOCIAL_STATE_PUBLISHED;

		$categoryIds = $params->get('category');

		if ($categoryIds) {
			foreach ($categoryIds as $categoryId) {
				$category = ES::table('MarketplaceCategory');
				$category->load($categoryId);

				// We only process show the child category's listing
				// if this category is a container
				if (!$category->container) {
					continue;
				}

				$categoryModel = ES::model('MarketplaceCategories');
				$childs = $categoryModel->getChildCategories($category->id, array(), array('state' => SOCIAL_STATE_PUBLISHED));

				foreach ($childs as $child) {
					$categoryIds[] = $child->id;
				}
			}

			$options['category'] = $categoryIds;
		}

		if ($filter == 0) {
			$listings = $model->getListings($options);
		}

		// Featured listings only
		if ($filter == 2) {
			$options['featured'] = true;
			$listings = $model->getListings($options);
		}

		// listings from logged in user
		if ($filter == 3) {
			$options['filter'] = 'created';
			$options['userid'] = $my->id;
			$listings = $model->getListings($options);
		}

		return $listings;
	}
}
