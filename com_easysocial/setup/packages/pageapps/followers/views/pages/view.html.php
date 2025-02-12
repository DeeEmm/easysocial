<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class FollowersViewPages extends SocialAppsView
{
	public function display($pageId = null, $docType = null)
	{
		$options = array();

		$page = ES::page($pageId);

		// Get the pagination settings
		$themes = ES::themes();
		$appParam = $this->app->getParams();
		$sorting = $appParam->get('follower.sorting');
		$ordering = $appParam->get('follower.ordering');
		$limit = (int) $appParam->get('follower.limit');

		$this->setTitle('APP_FOLLOWERS_APP_TITLE');

		// Followers sorting and ordering
		$options['ordering'] = $sorting;
		$options['direction'] = $ordering;

		// Followers to display per page.
		$options['limit'] = $limit;
		$options['state'] = SOCIAL_PAGES_MEMBER_PUBLISHED;

		// Get the current filter.
		$filter = $this->input->get('filter', '', 'word');

		// List only page admins
		if ($filter == 'admin') {
			$options['admin'] = true;
		}

		// List only pending users
		if ($filter == 'pending') {
			$options['state'] = SOCIAL_PAGES_MEMBER_PENDING;
		}

		// Display all followers including admins of the page
		if ((!$this->config->get('pages.followers.includeadmin') && !$page->isAdmin()) || $filter == 'followers') {
			$options['followers'] = true;
		}

		$model = ES::model('Pages');
		$users = $model->getMembers($page->id, $options);
		$pending = $model->getTotalPendingFollowers($page->id);

		// Set pagination properties
		$pagination	= $model->getPagination();
		$pagination->setVar('view', 'pages');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $page->getAlias());
		$pagination->setVar('appId', $this->app->getAlias());
		$pagination->setVar('Itemid', ESR::getItemId('pages', 'item', $page->id));

		if ($pagination && $filter) {
			$pagination->setVar('filter', $filter);
		}

		// Redirection url when an action is performed on a page follower
		$redirectOptions = array('layout' => "item", 'id' => $page->getAlias(), 'appId' => $this->app->getAlias());

		if ($filter) {
			$redirectOptions['filter'] = $filter;
		}

		$returnUrl = ESR::pages($redirectOptions, false);
		$returnUrl = base64_encode($returnUrl);

		$filterLinks = $this->getFilterLinks($page);

		$theme = ES::themes();
		$theme->set('returnUrl', $returnUrl);
		$theme->set('active', $filter);
		$theme->set('page', $page);
		$theme->set('users', $users);
		$theme->set('pagination', $pagination);
		$theme->set('filterLinks', $filterLinks);
		$theme->set('emptyText', 'APP_PAGE_FOLLOWERS_EMPTY');
		$theme->set('filter', $filter);
		$theme->set('pending', $pending);

		echo $theme->output('apps/page/followers/pages/default');
	}

	public function sidebar($moduleLib, $cluster)
	{
		if (!$cluster->isAdmin() && !$this->config->get('pages.followers.includeadmin')) {
			return;
		}

		// Get the current filter.
		$filter = $this->input->get('filter', '', 'word');

		$counters = new stdClass;
		$counters->followers = $cluster->getTotalMembers();
		$counters->admins = $cluster->getTotalAdmins();
		$counters->pending = 0;
		$counters->invited = 0;
		$counters->total = $counters->followers + $counters->admins;

		if ($cluster->isAdmin() || $cluster->isOwner() || $this->my->isSiteAdmin()) {

			if ($cluster->isClosed()) {
				$counters->pending = $cluster->getTotalPendingFollowers();
			}

			if ($cluster->isInviteOnly()) {
				$counters->invited = $cluster->getTotalInvitedFollowers();
			}
		}

		$theme = ES::themes();
		$theme->set('moduleLib', $moduleLib);
		$theme->set('counters', $counters);
		$theme->set('cluster', $cluster);
		$theme->set('active', $filter);

		echo $theme->output('apps/page/followers/pages/sidebar');
	}

	/**
	 * Retrieves the filters that are available on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterLinks($page)
	{
		static $links = null;

		if (is_null($links)) {
			$links = new stdClass();

			$appId = $this->input->get('appId', 0, 'int');
			$app = ES::table('App');
			$app->load($appId);

			$options = array(
				'layout' => 'item',
				'id' => $page->getAlias(),
				'appId' => $app->getAlias()
			);

			$links->all = ESR::pages($options);

			$options['filter'] = 'followers';
			$links->followers = ESR::pages($options);

			$options['filter'] = 'admin';
			$links->admin = ESR::pages($options);

			$options['filter'] = 'pending';
			$links->pending = ESR::pages($options);
		}

		return $links;
	}
}
