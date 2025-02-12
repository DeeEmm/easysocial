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

class MembersViewGroups extends SocialAppsView
{
	public function display($groupId = null, $docType = null)
	{
		$options = array();

		$this->setTitle('APP_MEMBERS_APP_TITLE');

		$group = ES::group($groupId);

		// Get the pagination settings
		$themes = ES::themes();
		$appParam = $this->app->getParams();
		$limit = (int) $appParam->get('member.limit', 10);
		$sorting = $appParam->get('member.sorting');
		$ordering = $appParam->get('member.ordering');
		$pinAdmins = (bool) $appParam->get('member.pinadmins');
		$excludeOwner = (bool) $appParam->get('member.excludeowner');

		// Members sorting and ordering
		$options['ordering'] = $sorting;
		$options['direction'] = $ordering;

		// Members to display per page.
		$options['limit'] = $limit;

		// Get the current filter.
		$filter = $this->input->get('filter', '', 'word');

		// List only group admins
		if ($filter == 'admin') {
			$options['admin'] = true;
		}

		// List only pending users
		if ($filter == 'pending') {
			$options['state']	= SOCIAL_GROUPS_MEMBER_PENDING;
		}

		if ($filter == 'invited') {
			$options['state']	= SOCIAL_GROUPS_MEMBER_INVITED;
		}

		if ($filter == 'members') {
			$options['members'] = true;
			$options['state'] = true;
		}

		// If admin decides to exclude owner from the list
		if ($excludeOwner) {
			$options['exclude'] = $group->getCreator()->id;
		}

		// If admin decides to pin group admins
		if ($pinAdmins && !$filter) {
			$options['members'] = true;
			$options['admin'] = false;
		}

		$model = ES::model('Groups');
		$users = $model->getMembers($group->id, $options);
		$pending = $model->getTotalPendingMembers($group->id);
		// Set pagination properties
		$pagination	= $model->getPagination();
		$pagination->setVar('view', 'groups');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $group->getAlias() );
		$pagination->setVar('appId', $this->app->getAlias());
		$pagination->setVar('Itemid', ESR::getItemId('groups', 'item', $group->id));

		if ($pagination && $filter) {
			$pagination->setVar('filter', $filter);
		}

		$admins = array();

		if ($pinAdmins && !$filter) {
			$options['admin'] = true;
			$options['members'] = false;

			$admins = $model->getMembers($group->id, $options);
		}

		// Redirection url when an action is performed on a group member
		$redirectOptions = array('layout' => "item", 'id' => $group->getAlias(), 'appId' => $this->app->getAlias());

		if ($filter) {
			$redirectOptions['filter'] = $filter;
		}

		$returnUrl = ESR::groups($redirectOptions, false);
		$returnUrl = base64_encode($returnUrl);

		$filterLinks = $this->getFilterLinks($group);

		$theme = ES::themes();

		$theme->set('returnUrl', $returnUrl);
		$theme->set('active', $filter);
		$theme->set('group', $group);
		$theme->set('users', $users);
		$theme->set('pagination', $pagination);
		$theme->set('filterLinks', $filterLinks);
		$theme->set('emptyText', 'APP_GROUP_MEMBERS_EMPTY');
		$theme->set('appId', $this->app->id);
		$theme->set('admins', $admins);
		$theme->set('filterType', $filter);
		$theme->set('pending', $pending);

		echo $theme->output('apps/group/members/groups/default');
	}

	public function sidebar($moduleLib, $cluster)
	{
		// Get the current filter.
		$filter = $this->input->get('filter', '', 'word');

		$appParam	= $this->app->getParams();
		$pinAdmin = (bool) $appParam->get('member.pinadmins');
		$excludeOwner = (bool) $appParam->get('member.excludeowner');

		$total = $cluster->getTotalMembers();
		$totalAdmins = $cluster->getTotalAdmins();

		if ($excludeOwner) {
			$totalAdmins -= 1;
		}

		$counters = new stdClass;
		$counters->total = $total;
		$counters->admins = $totalAdmins;
		$counters->members = $counters->total - $counters->admins;
		$counters->pending = 0;
		$counters->invited = 0;

		if ($cluster->isAdmin() || $cluster->isOwner() || $this->my->isSiteAdmin()) {
			if ($cluster->isClosed() || $cluster->isSemiOpen()) {
				$counters->pending = $cluster->getTotalPendingMembers();
			}

			if ($cluster->isInviteOnly()) {
				$counters->invited = $cluster->getTotalInvitedMembers();
			}
		}

		$theme = ES::themes();
		$theme->set('moduleLib', $moduleLib);
		$theme->set('counters', $counters);
		$theme->set('cluster', $cluster);
		$theme->set('active', $filter);

		echo $theme->output('apps/group/members/groups/sidebar');
	}

	/**
	 * Retrieves the filters that are available on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterLinks($group)
	{
		static $links = null;

		if (is_null($links)) {
			$links = new stdClass();

			$appId = $this->input->get('appId', 0, 'int');
			$app = ES::table('App');
			$app->load($appId);

			$options = array(
				'layout' => 'item',
				'id' => $group->getAlias(),
				'appId' => $app->getAlias()
			);

			$links->all = ESR::groups($options);

			$options['filter'] = 'members';
			$links->members = ESR::groups($options);

			$options['filter'] = 'admin';
			$links->admin = ESR::groups($options);

			$options['filter'] = 'pending';
			$links->pending = ESR::groups($options);

			$options['filter'] = 'invited';
			$links->invited = ESR::groups($options);
		}

		return $links;
	}
}
