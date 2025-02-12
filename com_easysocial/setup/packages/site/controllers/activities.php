<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerActivities extends EasySocialController
{
	/**
	 * Hide / unhide an activity log item.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function toggle()
	{
		ES::requireLogin();

		ES::checkToken();

		// get required form post variable
		$id = $this->input->get('id', 0, 'int');
		$cState = $this->input->get('curState', 0, 'int');

		// Load the stream item
		$item = ES::table('StreamItem');
		$item->load($id);

		// If id is invalid, throw an error.
		if (!$id || !$item->id) {
			$this->view->setError(JText::_('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID'));
			return $this->view->call(__FUNCTION__ , $id, $cState);
		}

		// The user needs to be at least logged in to perform this action.
		if (!$this->my->id) {
			$this->view->setError(JText::_('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID'));
			return $this->view->call(__FUNCTION__ , $id, $cState);
		}

		// Get the model
		$model = ES::model('Activities');
		$state = $model->toggle($id , $this->my->id);

		// If there's an error, log this down.
		if (!$state) {
			$this->view->setError($model->getError());

			return $this->view->call(__FUNCTION__ , $id, $cState);
		}

		return $this->view->call(__FUNCTION__ , $id, $cState);
	}

	/**
	 * Allows caller to delete an activity item
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		// get required form post variable
		$id = $this->input->get('id', 0, 'int');

		// Load the stream item
		$item = ES::table('StreamItem');
		$item->load($id);

		// If id is invalid, throw an error.
		if (!$id || !$item->id) {
			$this->view->setError(JText::_('COM_EASYSOCIAL_ERROR_UNABLE_TO_LOCATE_ID'));
			return $this->view->call(__FUNCTION__);
		}

		// Check if the current user is allowed to delete this stream item
		if (!$item->deleteable()) {
			$this->view->setError(JText::_('COM_EASYSOCIAL_ACTIVITIES_NOT_ALLOWED_TO_DELETE_ITEM'));
			return $this->view->call(__FUNCTION__);
		}

		$model = ES::model('Activities');
		$state = $model->delete($id);

		if (!$state) {
			$this->view->setError($model->getError());

			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}


	/**
	 * get activity logs.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getActivities()
	{
		// Ensure that the user is logged in before allowing such actions.
		ES::requireLogin();

		ES::checkToken();

		$filterType = $this->input->get('type', 'all', 'default');
		$isloadmore = $this->input->get('loadmore', '', 'default');
		$limitstart = $this->input->get('limitstart', '0', 'int');

		$context = SOCIAL_STREAM_CONTEXT_TYPE_ALL;

		if ($filterType != 'all' && $filterType != 'hidden' && $filterType != 'hiddenapp' && $filterType != 'hiddenactor') {
			$context = $filterType;
			$filterType = 'all';
		}

		if ($filterType == 'hiddenapp') {
			return $this->getHiddenApps();
		}
		else if ($filterType == 'hiddenactor') {
			return $this->getHiddenActors();
		}

		$options = array('uId' => $this->my->id,
			'context' => $context,
			'filter' => $filterType,
			'limitstart' => $limitstart);

		$stream = ES::stream();
		$activities = $stream->getActivityLogs($options);
		$nextlimit = $stream->getActivityNextLimit();

		return $this->view->call(__FUNCTION__, $filterType, $activities, $nextlimit, $isloadmore);
	}

	public function getHiddenActors()
	{
		ES::requireLogin();

		ES::checkToken();

		$model = ES::model('Activities');
		$data = $model->getHiddenActors($this->my->id);

		return $this->view->call(__FUNCTION__, $data);
	}


	public function getHiddenApps()
	{
		ES::requireLogin();

		ES::checkToken();

		$model = ES::model('Activities');
		$data = $model->getHiddenApps($this->my->id);

		return $this->view->call(__FUNCTION__, $data);
	}

	public function unhideapp()
	{
		ES::requireLogin();

		ES::checkToken();

		$context = $this->input->get('context', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		$model = ES::model('Activities');
		$state = $model->unhideapp($context, $id);

		if (!$state) {
			$this->view->setErrors(JText::_('COM_EASYSOCIAL_STREAM_FAILED_UNHIDE'));
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

	public function unhideactor()
	{
		ES::requireLogin();

		ES::checkToken();

		$actor = $this->input->get('actor', '', 'default');
		$id = $this->input->get('id', 0, 'int');

		$model = ES::model('Activities');
		$state  = $model->unhideactor($actor, $id);

		if (!$state) {
			$this->view->setErrors(JText::_('COM_EASYSOCIAL_STREAM_FAILED_UNHIDE'));
			return $this->view->call(__FUNCTION__);
		}

		return $this->view->call(__FUNCTION__);
	}

}
