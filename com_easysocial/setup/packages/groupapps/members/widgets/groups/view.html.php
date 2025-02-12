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

class MembersWidgetsGroups extends SocialAppsWidgets
{
	/**
	 * Renders members widget on mobile
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function mobileAfterIntro($groupId, $group)
	{
		// Enforce hard limit for mobile to 10
		$limit = 10;

		return $this->sidebarBottom($groupId, $group, $limit);
	}

	/**
	 * Renders the sidebar widget for group members
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function sidebarBottom($groupId, $group, $limit = null)
	{
		if (!$this->app->getParams()->get('show_members', true)) {
			return;
		}

		$params = $this->app->getParams();

		if (!$limit) {
			$limit = (int) $params->get('limit', 10);
		}

		// Load up the group
		$group = ES::group($groupId);

		$options = array('state' => SOCIAL_STATE_PUBLISHED, 'limit' => $limit, 'ordering' => 'created', 'direction' => 'desc');


		$excludeOwner = (bool) $params->get('member.excludeowner');

		// If admin decides to exclude owner from the list
		if ($excludeOwner) {
			$options['exclude'] = $group->getCreator()->id;
		}

		$model = ES::model('Groups');
		$members = $model->getMembers($group->id, $options);

		$link = ESR::groups(array('id' => $group->getAlias(),'appId' => $this->app->getAlias(),'layout' => 'item'));

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('members', $members);
		$theme->set('link', $link);

		echo $theme->output('themes:/apps/group/members/widgets/members');
	}
}
