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

class PagesWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Display pages as a widget
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		$params = $this->getParams();

		if ($params->get('widget_profile', true) && $this->config->get('pages.enabled')) {
			echo $this->getPages($user, $params);
		}
	}

	/**
	 * Retrieves the list of pages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPages($user, $params)
	{
		$model = ES::model('Pages');
		$limit = $params->get('widget_profile_total', 5);
		$pageOptions = array('liked' => $user->id, 'state' => SOCIAL_CLUSTER_PUBLISHED, 'limit' => $limit);

		// if $user is the current viewer, we will get all the pages
		if ($user->isViewer()) {
			$pageOptions['types'] = 'all';
		}

		$pages = $model->getPages($pageOptions);

		if (!$pages) {
			return;
		}

		// Get the total pages the user owns
		$options = array('types' => 'open');

		// if $user is the current viewer, we will get all the pages
		if (!$user->isViewer()) {
			$options = array();
		}

		$viewAll = ESR::pages(array('userid' => $user->getAlias()));

		if ($user->isViewer()) {
			$viewAll = ESR::pages(array('filter' => 'mine'));
		}

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('pages', $pages);
		$theme->set('viewAll', $viewAll);

		return $theme->output('themes:/apps/user/pages/widgets/profile/pages');
	}
}
