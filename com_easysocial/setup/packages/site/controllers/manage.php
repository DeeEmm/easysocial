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

jimport('joomla.mail.helper');

class EasySocialControllerManage extends EasySocialController
{
	/**
	 * Retrieve the counts
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getClusterCounters()
	{
		ES::requireLogin();
		ES::checkToken();

		$model = ES::model('Clusters');

		$pendingCounters = array();
		$pendingCounters['event'] = $model->getTotalPendingModeration(array('filter' => 'event'));
		$pendingCounters['group'] = $model->getTotalPendingModeration(array('filter' => 'group'));
		$pendingCounters['page'] = $model->getTotalPendingModeration(array('filter' => 'page'));

		return $this->view->call(__FUNCTION__, $pendingCounters);
	}

	/**
	 * Allow caller to filter the cluster
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function filterCluster()
	{
		ES::requireLogin();
		ES::checkToken();

		// Load clusters model.
		$model = ES::model('Clusters');

		// Get the filter types.
		$type = $this->input->get('filter', 'all', 'cmd');

		// Determines the total items to show per page
		$limit = ES::getLimit('clusterslimit');

		$clusters = array();
		$options = array('limit' => $limit, 'filter' => $type);

		$clusters = $model->getPendingModeration($options);

		// Get the pagination
		$pagination	= $model->getPagination();

		// Set additional vars for the pagination
		$itemId = ESR::getItemId('manage');

		$pagination->setVar('Itemid', $itemId);
		$pagination->setVar('view', 'manage');
		$pagination->setVar('layout', 'clusters');

		if ($type != 'all') {
			$pagination->setVar('filter', $type);
		}

		return $this->view->call(__FUNCTION__, $type, $clusters, $pagination);
	}

	/**
	 * Allows user to approve cluster moderation
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function approveCluster()
	{
		ES::requireLogin();
		ES::checkToken();

		// Only site admins are allowed to perform this
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('pendings.manage')) {
			$this->view->setMessage('COM_ES_MANAGE_CLUSTER_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$id = $this->input->get('clusterId', 0, 'int');
		$type = $this->input->get('clusterType', '', 'default');
		$sendMail = $this->input->get('sendMail', 0, 'int');

		$cluster = ES::cluster($type, $id);

		$state = $cluster->approve($sendMail);

		if (!$state) {
			return $this->view->exception($cluster->getError());
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Rejects a cluster
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function rejectCluster()
	{
		ES::requireLogin();
		ES::checkToken();

		// Only site admins are allowed to perform this
		if (!$this->my->isSiteAdmin() && !$this->my->getAccess()->get('pendings.manage')) {
			$this->view->setMessage('COM_ES_MANAGE_CLUSTER_NO_ACCESS', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$id = $this->input->get('clusterId', 0, 'int');
		$type = $this->input->get('clusterType', '', 'default');
		$rejectMessage = $this->input->get('rejectMessage', '', 'default');
		$sendMail = $this->input->get('sendMail', 0, 'int');
		$deleteCluster = $this->input->get('deleteCluster', 0, 'int');

		$cluster = ES::cluster($type, $id);

		$state = $cluster->reject($rejectMessage, $sendMail, $deleteCluster);

		if (!$state) {
			return $this->view->exception($cluster->getError());
		}

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Allows caller to approve an event if the parent event has recurring data
	 *
	 * @since   3.2.12
	 * @access  public
	 */
	public function approveEventHasRecurringData()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$sendMail = $this->input->get('sendMail', 0, 'int');

		if (!$id) {
			return $this->view->exception('COM_EASYSOCIAL_EVENTS_INVALID_IDS');
		}

		$event = ES::event($id);

		$event->approve($sendMail);

		return $this->view->call(__FUNCTION__, $event);
	}
}
