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

class EasySocialViewDiscussionsListHelper extends EasySocial
{
	/**
	 * Determines the current filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCurrentFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', 'all', 'string');
		}

		return $filter;
	}

	/**
	 * Determines if user is viewing discussions from a cluster
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
	 * Generates the link for the create discussions button
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCreateButtonLink()
	{
		static $link = null;

		if (is_null($link)) {
			$cluster = $this->getCluster();

			$appId = $this->input->get('appId', 0, 'int');
			$app = ES::table('App');
			$app->load($appId);

			$link = ESR::apps(array('layout' => 'canvas', 'uid' => $cluster->getAlias(), 'type' => $cluster->getType(), 'id' => $app->getAlias(), 'customView' => 'create'));
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

			$options['filter'] = 'unanswered';
			$links->unanswered = ESR::$clusterType($options);

			$options['filter'] = 'resolved';
			$links->resolved = ESR::$clusterType($options);

			$options['filter'] = 'unresolved';
			$links->unresolved = ESR::$clusterType($options);

			$options['filter'] = 'locked';
			$links->locked = ESR::$clusterType($options);
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

			$show = (bool) $cluster->canCreateDiscussion();
		}

		return $show;
	}

	/**
	 * Get counters for each filter
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getCounters()
	{
		static $counters = null;

		if (is_null($counters)) {
			$cluster = $this->getCluster();
			$model = ES::model('Discussions');
			$counters = $model->getCounters($cluster);
		}

		return $counters;
	}
}
