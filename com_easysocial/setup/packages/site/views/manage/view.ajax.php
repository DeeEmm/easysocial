<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewManage extends EasySocialSiteView
{
	/**
	 * Responsible to return html codes to the ajax calls.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function filterCluster($filter, $clusters, $pagination)
	{
		$theme = ES::themes();
		$theme->set('pagination', $pagination);
		$theme->set('clusters', $clusters);
		$theme->set('filter', $filter);

		$output = $theme->output('site/manage/clusters/items');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays the confirmation to approve cluster moderation
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function confirmClusterApprove()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('clusterId', 0, 'int');
		$type = $this->input->get('clusterType', '', 'cmd');
		$cluster = ES::cluster($type, $id);

		$theme = ES::themes();
		$theme->set('cluster', $cluster);

		if ($type == SOCIAL_TYPE_EVENT) {

			$params = $cluster->getParams();

			$hasEventRecurring = $params->exists('recurringData');

			// Only process this if the event has schedule
			if ($hasEventRecurring) {

				$eventDate = ES::date($cluster->getMeta('start'), false);

				// Get the recurring schedule
				$schedule = ES::model('Events')->getRecurringSchedule(array(
					'eventStart' => $eventDate,
					'end' => $params->get('recurringData')->end,
					'type' => $params->get('recurringData')->type,
					'daily' => $params->get('recurringData')->daily
				));

				if ($schedule) {

					$contents = $theme->output('site/manage/dialogs/approve.cluster.event');

					return $this->ajax->resolve($contents);
				}
			}
		}

		$contents = $theme->output('site/manage/dialogs/approve.cluster');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a approve cluster
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function approveCluster()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Post processing after rejecting a cluster
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function rejectCluster()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the confirmation to reject cluster moderation
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function confirmClusterReject()
	{
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('clusterId', 0, 'int');
		$type = $this->input->get('clusterType', '', 'cmd');
		$cluster = ES::cluster($type, $id);

		$theme = ES::themes();
		$theme->set('cluster', $cluster);

		$contents = $theme->output('site/manage/dialogs/reject.cluster');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Retrieve counters
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getClusterCounters($counters)
	{
		return $this->ajax->resolve($counters);
	}
}
