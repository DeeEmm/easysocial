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

class EasySocialControllerVerifications extends EasySocialController
{
	/**
	 * Approves a request for verification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function approve()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'array');
		$type = $this->input->get('type', '', 'cmd');

		$verification = ES::verification();

		// Remove the 's' from the type
		$clusterType = substr($type, 0, strlen($type) - 1);

		foreach ($ids as $id) {
			$request = ES::table('Verification');
			$request->load($id);

			if (!$request->id) {
				continue;
			}

			$verification->approve($id);
			$obj = ES::cluster($clusterType, (int) $request->uid);

			$name = $type == 'users' ? $obj->getName() : $obj->getTitle();

			$this->actionlog->log('COM_ES_ACTION_LOG_' . strtoupper($type) . '_VERIFICATION_APPROVED', 'verifications', [
				'name' => $name,
				'link' => 'index.php?option=com_easysocial&view=' . $type . '&layout=form&id=' . $obj->id
			]);
		}

		$this->view->setMessage('COM_ES_VERIFICATIONS_VERIFIED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $type);
	}

	/**
	 * Sets the particular items as verified
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function enable()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', [], 'int');
		$type = $this->input->get('verification_type', SOCIAL_TYPE_USER, 'cmd');

		foreach ($ids as $id) {
			$obj = ES::cluster($type, (int) $id);
			$obj->setVerified();

			$name = $type == SOCIAL_TYPE_USER ? $obj->getName() : $obj->getTitle();

			$this->actionlog->log('COM_ES_ACTION_LOG_' . strtoupper($type) . '_VERIFIED', 'verifications', [
				'name' => $name,
				'link' => 'index.php?option=com_easysocial&view=' . $type . 's&layout=form&id=' . $obj->id
			]);
		}

		$message = 'COM_ES_SELECTED_' . strtoupper($type) . 'S_VERIFIED';

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $type);
	}

	/**
	 * Removes verified status from an object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function disable()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$type = $this->input->get('verification_type', SOCIAL_TYPE_USER, 'cmd');

		foreach ($ids as $id) {
			$cluster = ES::cluster($type, (int) $id);
			$cluster->removeVerified();

			$name = $type == SOCIAL_TYPE_USER ? $cluster->getName() : $cluster->getTitle();

			$this->actionlog->log('COM_ES_ACTION_LOG_' . strtoupper($type) . '_UNVERIFIED', 'verifications', [
				'name' => $name,
				'link' => 'index.php?option=com_easysocial&view=' . $type . 's&layout=form&id=' . $cluster->id
			]);
		}

		$message = 'COM_ES_SELECTED_' . strtoupper($type) . 'S_UNVERIFIED';

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $type);
	}

	/**
	 * Approves a request for verification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function reject()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$type = $this->input->get('type', '', 'cmd');

		$verification = ES::verification();

		foreach ($ids as $id) {
			$verification->reject($id);
			$user = ES::user($id);

			$this->actionlog->log('COM_ES_ACTION_LOG_' . strtoupper($type) . '_VERIFICATION_REJECTED', 'verifications', [
				'name' => $user->getName(),
				'link' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
			]);
		}

		$this->view->setMessage('COM_ES_USERS_REJECTED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $type);
	}
}
