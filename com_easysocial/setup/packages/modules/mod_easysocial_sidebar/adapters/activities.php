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

require_once(__DIR__ . '/abstract.php');

class SocialSidebarActivities extends SocialSidebarAbstract
{
	/**
	 * Renders the output from the sidebar
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$helper = ES::viewHelper('activities', 'logs');

		// Get the necessary attributes from the request
		$filterType = $helper->getActiveFilter();
		$active = $filterType;

		$data = $helper->getActivities($filterType);
		$filterType = $data->filterType;
		$activities = $data->activities;
		$nextLimit = $data->nextLimit;
		$apps = $helper->getApps();

		$path = $this->getTemplatePath('activities');

		require($path);
	}
}
