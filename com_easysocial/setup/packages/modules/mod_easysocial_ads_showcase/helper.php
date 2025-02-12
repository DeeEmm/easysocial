<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialModAdsShowcaseHelper
{
	public static function getAds($params)
	{
		// get filter type
		$filter = $params->get('filter', 'all');

		$priority = $params->get('priority', 'all');
		$limit = $params->get('limit', 5);

		$model = ES::model('Ads');

		$options = array('priority' => $priority, 'limit' => $limit, 'order' => 'random');

		if ($filter == 'advertiser') {
			$options['advertiser'] = $params->get('advertiser', 0, 'int');
		}

		$results = $model->getItems($options);
		$ads = array();

		foreach ($results as $item) {
			$table = ES::table('Ad');
			$table->bind($item);
			$ads[] = $table;
		}

		return $ads;
	}
}
