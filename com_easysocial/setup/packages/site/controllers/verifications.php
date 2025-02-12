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
	 * Saves a verification request
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save()
	{
		ES::requireLogin();
		ES::checkToken();

		$message = strip_tags($this->input->get('message', '', 'raw'), '<br>');
		$uid = $this->input->get('uid', $this->my->id, 'int');
		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'cmd');

		if ($type == SOCIAL_TYPE_USER && !$uid) {
			$uid = JFactory::getUser()->id;
		}

		// Do not allow users to request this again
		if ($type == SOCIAL_TYPE_USER && $this->my->isVerified()) {
			return $this->view->exception('You are already a verified member');
		}

		$obj = ES::cluster($type, $uid);
		$verification = ES::verification();

		// Check if user has requested before
		if ($verification->hasRequested($uid, $type)->state) {
			$message = 'COM_ES_VERIFICATION_REQUEST_SUBMITTED_BEFORE';

			$this->view->setMessage($message);
			return $this->view->call(__FUNCTION__, $obj);
		}

		if (!$verification->canRequest($uid, $type)) {
			return $this->view->exception('This feature is not available');
		}

		$ip = @$_SERVER['REMOTE_ADDR'];
		$request = $verification->request($message, $ip, $uid, $type);

		$message = 'COM_ES_VERIFICATION_REQUEST_SUBMITTED';

		if ($type != SOCIAL_TYPE_USER) {
			$message = 'COM_ES_VERIFICATION_' . strtoupper($type) . '_REQUEST_SUBMITTED';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $obj);
	}
}
