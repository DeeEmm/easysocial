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

class GroupsWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Display groups as a widget
	 *
	 * @since	2.0.6
	 * @access	public
	 */
	public function sidebarBottom($user)
	{
		$params = $this->getParams();

		if ($params->get('widget_profile', true) && $this->config->get('groups.enabled')) {
			echo $this->getGroups($user, $params);
		}
	}

	/**
	 * Retrieves the list of groups
	 *
	 * @since	2.0.6
	 * @access	public
	 */
	public function getGroups($user, $params)
	{
		$model = ES::model('Groups');
		$limit = $params->get('widget_profile_total', 5);
		$groupOptions = array('userid' => $user->id, 'state' => SOCIAL_CLUSTER_PUBLISHED, 'limit' => $limit, 'types' => 'participated');

		$groups = $model->getGroups($groupOptions);

		if (!$groups) {
			return;
		}

		// Get the total groups the user owns
		$options = array('types' => 'participated');

		// if $user is the current viewer, we will get all the groups
		if ($user->isViewer()) {
			$options = array();
		}

		$viewAll = ESR::groups(array('userid' => $user->getAlias()));

		if ($user->isViewer()) {
			$viewAll = ESR::groups(array('filter' => 'mine'));
		}


		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('groups', $groups);
		$theme->set('viewAll', $viewAll);

		return $theme->output('themes:/apps/user/groups/widgets/profile/groups');
	}
}
