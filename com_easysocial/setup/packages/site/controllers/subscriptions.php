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

class EasySocialControllerSubscriptions extends EasySocialController
{
	/**
	 * Allows a user to follow another person
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function follow()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the target person that is being followed
		$uid = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', '', 'word');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'word');

		// Load up the subscription library
		$subscriptions = ES::subscriptions();
		$state = $subscriptions->subscribe($uid, $type, $group);

		if ($state === false) {
			$this->view->setMessage($subscriptions->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $subscriptions);
	}

	/**
	 * Allows a user to unfollow an object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unfollow()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the target that is being unfollowed
		$uid = $this->input->get('id', 0, 'int');
		$type = $this->input->get('type', '', 'word');
		$group = $this->input->get('group', SOCIAL_APPS_GROUP_USER, 'word');

		// Load the subscriptions
		$subscriptions = ES::subscriptions();
		$subscriptions->load($uid, $type, $group);

		// Try to unsubscribe now
		$state = $subscriptions->unsubscribe();

		if (!$state) {
			$this->view->setMessage($subscriptions->getError(), ES_ERROR);
		}

		return $this->view->call(__FUNCTION__, $subscriptions);
	}
}
