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

class EasySocialControllerFollowers extends EasySocialController
{
	/**
	 * Allows caller to filter followers by type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter()
	{
		ES::requireLogin();
				
		// Check for valid tokens.
		ES::checkToken();

		// Load friends model.
		$model = ES::model('Followers');
		$limit = ES::getLimit('followersLimit');

		// Get the filter type
		$type = $this->input->get('type', '', 'word');

		// Get the user id that we should load for.
		$userId = $this->input->get('id', 0, 'int');

		if (!$userId) {
			$userId = null;
		}

		// Load the target user
		$user = ES::user($userId);
		$users = array();

		if ($type == 'followers') {
			$users = $model->getFollowers($userId, array('limit' => $limit));
		}

		if ($type == 'following') {
			$users = $model->getFollowing($userId, array('limit' => $limit));
		}

		if ($type == 'suggest') {
			$users = $model->getSuggestions($user->id);
		}

		$pagination = $model->getPagination();

		// Define those query strings here
		$pagination->setVar('Itemid', ESR::getItemId('followers'));
		$pagination->setVar('view', 'followers');
		$pagination->setVar('filter', $type);

		if (!$user->isViewer()) {
			$pagination->setVar('userid', $user->getAlias());
		}

		return $this->view->call(__FUNCTION__, $type, $user, $users, $pagination);
	}
}
