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

class EasySocialViewPolls extends EasySocialSiteView
{
	/**
	 * Renders a list of polls created on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Check for user profile completeness
		ES::checkCompleteProfile();

		ES::setMeta();

		$helper = $this->getHelper('List');
		$filter = $helper->getCurrentFilter();
		$user = $helper->getActiveUser();

		if ($user && $user->id) {
			return $this->displayUser($user->id);
		}

		// normalize the canonical link
		$canonicalOptions = ES::normalizeCanonicalOptions(true);
		$title = 'COM_EASYSOCIAL_PAGE_TITLE_ALL_POLLS';
		$options = [];

		if ($filter == 'mine' && !$this->my->id) {
			$filter = 'all';
		}

		if ($filter == 'mine') {
			$options['user_id'] = $this->my->id;
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_MY_POLLS';
		}

		$this->page->title($title);
		$this->page->breadcrumb($title);
		$this->page->canonical(ESR::polls($canonicalOptions));

		$model = ES::model('Polls');
		$result = $model->getPolls($options);
		$pagination = $model->getPagination();

		$polls = [];

		if ($result) {
			foreach ($result as $row) {
				$poll = ES::table('Polls');
				$poll->bind($row);

				$polls[] = $poll;
			}
		}

		$snackbar = JText::_('COM_EASYSOCIAL_POLLS');
		$filterLinks = $helper->getFilterLinks();
		$showCreateButton = $helper->showCreateButton();
		$createButtonLink = $helper->getCreateButtonLink();
		$cluster = $helper->getCluster();

		$this->set('cluster', $cluster);
		$this->set('createButtonLink', $createButtonLink);
		$this->set('filterLinks', $filterLinks);
		$this->set('showCreateButton', $showCreateButton);
		$this->set('filter', $filter);
		$this->set('polls', $polls);
		$this->set('snackbar', $snackbar);
		$this->set('pagination', $pagination);
		$this->set('user', false);

		return parent::display('site/polls/default/default');
	}

	/**
	 * Display user polls
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function displayUser($userId)
	{
		$helper = $this->getHelper('List');
		$user = ES::user($userId);

		// If the user is blocked, they should not be accessible #5069
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		if (!$helper->canUserAccess($user)) {
			return $this->restricted($user);
		}

		$options = [];
		$options['user_id'] = $userId;

		$title = JText::sprintf('COM_ES_PAGE_TITLE_USER_POLLS', $user->getName());

		$this->page->title($title);
		$this->page->breadcrumb($title);

		$model = ES::model('Polls');
		$result = $model->getPolls($options);
		$pagination = $model->getPagination();

		$polls = [];

		if ($result) {
			foreach ($result as $row) {
				$poll = ES::table('Polls');
				$poll->bind($row);

				$polls[] = $poll;
			}
		}

		// Directly get the total from the query above
		$total = $model->getState('total');

		// set the total so that module can retrieve this information without
		// needing to rerun the query
		$helper->setUserTotalPolls($total);

		$createButtonLink = $helper->getCreateButtonLink();
		$showCreateButton = $helper->showCreateButton();

		$filter = $user->isViewer() ? 'mine' : 'user';
		$filterLinks = $helper->getFilterLinks();

		$this->input->set('filter', $filter);

		$this->set('filterLinks', $filterLinks);
		$this->set('createButtonLink', $createButtonLink);
		$this->set('filter', $filter);
		$this->set('polls', $polls);
		$this->set('pagination', $pagination);
		$this->set('snackbar', false);
		$this->set('total', $total);
		$this->set('showCreateButton', $showCreateButton);
		$this->set('user', $user);
		$this->set('cluster', false);

		echo parent::display('site/polls/default/default');
	}

	/**
	 * Displays a restricted page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function restricted($node)
	{
		$this->set('showProfileHeader', true);
		$this->set('node', $node);

		echo parent::display('site/polls/restricted');
	}

	/**
	 * Renders poll creation form for cluster
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function createCluster()
	{
		$clusterType = $this->input->get('clusterType', SOCIAL_TYPE_USER, 'default');
		$clusterId = $this->input->get('clusterId', 0, 'int');

		if ($clusterType == SOCIAL_TYPE_USER || !$clusterId) {
			return $this->redirect(ESR::polls([], false));
		}

		$cluster = ES::cluster($clusterType, $clusterId);

		if (!$cluster->id) {
			return $this->redirect(ESR::polls([], false));
		}

		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());
		}

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_CREATE_POLL');

		$polls = ES::polls();

		$this->set('polls', $polls);
		$this->set('cluster', $cluster);

		parent::display('site/polls/create/default');
	}

	/**
	 * Renders the poll creation form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function create()
	{
		$clusterType = $this->input->get('clusterType', SOCIAL_TYPE_USER, 'default');
		$clusterId = $this->input->get('clusterId', 0, 'int');

		if ($clusterType != SOCIAL_TYPE_USER && $clusterId) {
			return $this->createCluster();
		}

		// User might invoke the url to reach this page
		if (!$this->my->canCreatePolls()) {
			$this->redirect(ESR::polls([], false));
			return;
		}

		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());
		}

		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_CREATE_POLL');

		$polls = ES::polls();

		$this->set('polls', $polls);

		parent::display('site/polls/create/default');
	}

	/**
	 * Called during saving poll from form. Story uses a different method from the app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function postCreate(SocialTablePolls $table)
	{
		$this->info->set($this->getMessage());

		$redirect = $table->getPermalink(false);

		$this->redirect($redirect);
		return;
	}
}
