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

class EasySocialViewPollsListHelper extends EasySocial
{
	public $userTotalPolls = 0;
	public $totalPolls = 0;

	/**
	 * set user total polls
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setUserTotalPolls($total = 0)
	{
		$this->userTotalPolls = $total;
	}

	/**
	 * get user total polls
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUserTotalPolls()
	{
		return $this->userTotalPolls;
	}

	/**
	 * Determines if viewer is viewing polls from a user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUserId()
	{
		static $id = null;

		if (is_null($id)) {
			$id = $this->input->get('userid', 0, 'int');
		}

		return $id;
	}

	/**
	 * Determines if viewer can access user's polls page or not.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canUserAccess($user)
	{
		if (!$this->config->get('polls.enabled')) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->my->canView($user, 'polls.view')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if viewer is viewing polls from a user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUser()
	{
		static $user = null;

		if (is_null($user)) {
			$id = $this->getActiveUserId();

			if (!$id) {
				$user = false;
				return $user;
			}

			$user = ES::user($id);
		}

		return $user;
	}

	/**
	 * Determines the current filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCurrentFilter()
	{
		$filter = $this->input->get('filter', 'all', 'string');

		return $filter;
	}

	/**
	 * Determines if user is viewing polls from a cluster
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCluster()
	{
		static $cluster = null;

		if (is_null($cluster)) {
			$view = $this->input->get('view', '', 'cmd');
			$layout = $this->input->get('layout', '', 'cmd');

			$cluster = false;

			if ($view == 'groups' && $layout == 'item') {
				$id = $this->input->get('id', 0, 'int');

				$cluster = ES::group($id);
			}

			if ($view == 'events' && $layout == 'item') {
				$id = $this->input->get('id', 0, 'int');

				$cluster = ES::event($id);
			}

			if ($view == 'pages' && $layout == 'item') {
				$id = $this->input->get('id', 0, 'int');

				$cluster = ES::page($id);
			}
		}

		return $cluster;
	}

	/**
	 * Generates the link for the create polls button
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCreateButtonLink()
	{
		static $link = null;

		if (is_null($link)) {
			$link = ESR::polls(array('layout' => 'create'));
			$cluster = $this->getCluster();

			if ($cluster) {
				$link = ESR::polls(array('layout' => 'create', 'clusterType' => $cluster->getType(), 'clusterId' => $cluster->id));
			}
		}

		return $link;
	}

	/**
	 * Retrieves the filters that are available on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterLinks()
	{
		static $links = null;

		if (is_null($links)) {
			$links = new stdClass();

			$cluster = $this->getCluster();

			if ($cluster) {
				$appId = $this->input->get('appId', 0, 'int');
				$app = ES::table('App');
				$app->load($appId);

				$options = array(
					'layout' => 'item',
					'id' => $cluster->getAlias(),
					'appId' => $app->getAlias()
				);

				$clusterType = $cluster->getTypePlural();
				$links->all = ESR::$clusterType($options);

				$options['filter'] = 'mine';
				$links->mine = ESR::$clusterType($options);
			} else {
				$links->all = ESR::polls(array('filter' => 'all'));
				$links->mine = ESR::polls(array('filter' => 'mine'));
			}

			$user = $this->getActiveUser();

			if ($user) {
				$userId = $this->input->get('userid', 0, 'int');

				$options = array(
					'userid' => $user->getAlias()
				);

				$links->mine = ESR::polls($options);
			}
		}

		return $links;
	}

	/**
	 * Determines if the create button should be visible on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showCreateButton()
	{
		static $show = null;

		if (is_null($show)) {
			$cluster = $this->getCluster();

			if (!$cluster) {

				$user = $this->getActiveUser();

				if ($user) {
					$show = $this->my->canCreatePolls() && $user->isViewer() ? true : false;
					return $show;
				}

				$show = (bool) $this->my->canCreatePolls();
				return $show;
			}

			$show = (bool) $cluster->canCreatePolls();
		}

		return $show;
	}

	/**
	 * Determines if the statistics section should be visible
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function showStatistics()
	{
		$user = $this->getActiveUser();

		if ($user === false) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the total number of polls created on the site.
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function getTotalPolls($options = [])
	{
		$defaultOptions = [
			'countOnly' => true
		];

		$options = array_merge($defaultOptions, $options);

		$model = ES::model('Polls');
		$total = $model->getPolls($options);

		return $total;
	}

	/**
	 * Retrieves the total number of all polls by current user from the site
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function getTotalMyPolls($options = [])
	{
		$defaultOptions = [
			'user_id' => $this->my->id,
			'countOnly' => true
		];

		$options = array_merge($defaultOptions, $options);

		$model = ES::model('Polls');
		$total = $model->getPolls($options);

		return $total;
	}
}