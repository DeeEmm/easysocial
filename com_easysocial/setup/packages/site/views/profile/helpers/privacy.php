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

class EasySocialViewProfilePrivacyHelper extends EasySocial
{

	/**
	 * Get current active tab
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveTab()
	{
		$activeTab = $this->input->get('activeTab', '', 'cmd');
		return $activeTab;
	}


	/**
	 * Get the custom alerts if there is any
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getBlockedUsers()
	{
		static $blockedUsers = null;

		if (is_null($blockedUsers)) {
			// Get a list of blocked users for this user
			$blockModel = ES::model('Blocks');
			$blockedUsers = $blockModel->getBlockedUsers($this->my->id);
		}

		return $blockedUsers;
	}

	/**
	 * Get the alert filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPrivacy()
	{
		static $privacy = null;

		if (is_null($privacy)) {

			// Get user's privacy
			$privacyLib = ES::privacy($this->my->id);
			$privacy = $privacyLib->getUserPrivacySettings();
		}

		return $privacy;
	}
}
