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

class EasySocialViewVerifications extends EasySocialSiteView
{
	public function display($tpl = null)
	{
		return $this->request($tpl);
	}

	/**
	 * Post processing after saving verification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function save($obj)
	{
		$this->info->set($this->getMessage());

		$redirect = $obj->getPermalink(false);

		return $this->app->redirect($redirect);
	}

	/**
	 * Allows caller to request to be submitted for verification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function request()
	{
		ES::setMeta();

		$verification = ES::verification();

		$type = $this->input->get('type', SOCIAL_TYPE_USER, 'cmd');
		$uid = $this->input->get('uid', 0, 'int');

		// Display a message if user has already a verified user
		if ($this->my->isVerified()) {
			$this->setMessage('COM_ES_ALREADY_VERIFIED_THIS_USER', 'info');
			$this->info->set($this->getMessage());
			return $this->redirect(ESR::dashboard());
		}

		if (!$verification->canRequest($uid, $type)) {

			$verify = $verification->hasRequested($uid, $type);

			// Display proper message if user has already request for verification previously.
			if ($verify->state) {
				$this->setMessage($verify->message, 'error');
				$this->info->set($this->getMessage());
				return $this->redirect(ESR::dashboard());
			}

			throw ES::exception('This feature is not available', 500);
		}

		$obj = ES::cluster($type, $uid);

		$this->set('type', $type);
		$this->set('obj', $obj);

		parent::display('site/verifications/request/default');
	}

}
