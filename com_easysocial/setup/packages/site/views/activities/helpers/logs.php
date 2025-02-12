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

class EasySocialViewActivitiesLogsHelper extends EasySocial
{
	/**
	 * Get active filter type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveFilter()
	{
		static $filterType = null;

		if (is_null($filterType)) {
			$filterType = $this->input->get('type', 'all', 'default');
		}

		return $filterType;
	}


	/**
	 * Get user activities logs
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActivities($filterType)
	{
		static $_cache = array();

		$idx = $filterType;

		if (! isset($_cache[$idx])) {

			$context = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

			if ($filterType != 'all' && $filterType != 'hidden' && $filterType != 'hiddenapp' && $filterType != 'hiddenactor') {
				$context = $filterType;
				$filterType = 'all';
			}

			// Load up activities model
			$model = ES::model('Activities');

			if ($filterType == 'hiddenapp') {
				$activities = $model->getHiddenApps($this->my->id);
				$nextLimit = $model->getNextLimit('0');
			} else if($filterType == 'hiddenactor') {
				$activities = $model->getHiddenActors($this->my->id);
				$nextLimit = $model->getNextLimit('0');
			} else {
				// Retrieve user activities.
				$stream = ES::stream();
				$options = array('uId' => $this->my->id, 'context' => $context, 'filter' => $filterType);

				$activities = $stream->getActivityLogs($options);
				$nextLimit = $stream->getActivityNextLimit();
			}


			$data = new stdClass();
			$data->activities = $activities;
			$data->nextLimit = $nextLimit;
			$data->filterType = $filterType;

			$_cache[$idx] = $data;
		}

		return $_cache[$idx];
	}

	/**
	 * Get apps
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getApps()
	{
		$apps = null;

		if (is_null($apps)) {

			$model = ES::model('Activities');

			$apps = array();

			// Get a list of apps
			$result = $model->getApps();
			$apps = array();

			foreach ($result as $app) {
				if (!$app->hasActivityLog()) {
					continue;
				}

				$app->favicon = '';
				$app->image = $app->getIcon();
				$favicon = $app->getFavIcon();

				if ($favicon) {
					$app->favicon = $favicon;
				}

				// Load the app's css
				$app->loadCss();

				$apps[] = $app;
			}
		}

		return $apps;
	}

}
