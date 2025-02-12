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

class FeedsViewProfile extends SocialAppsView
{
	public function display($userId = null, $docType = null)
	{
		$user = ES::user($userId);
		$model = $this->getModel("Feeds");
		$params = $this->getUserParams($user->id);

		$this->setTitle('APP_FEEDS_APP_TITLE');

		// Get the app params
		$appParams = $this->app->getParams();

		$limit = $params->get('total', $appParams->get('total', 5));
		$result = $model->getItems($user->id, $limit);
		$total = $model->getTotalFeeds($user->id);

		$feeds = array();

		if ($result) {
			foreach ($result as $row) {
				$table = $this->getTable('Feed');
				$table->bind($row);

				$feeds[] = $table;
			}
		}

		$this->set('app', $this->app);
		$this->set('total', $total);
		$this->set('user', $user);
		$this->set('totalDisplayed', $limit);
		$this->set('params', $params);
		$this->set('feeds', $feeds);

		echo parent::display('profile/default');
	}

	/**
	 * Method to display the sidebar of this app
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebar($moduleLib, $user)
	{
		$model = $this->getModel('Feeds');
		$total = $model->getTotalFeeds($user->id);

		$this->set('total', $total);
		$this->set('moduleLib', $moduleLib);
		$this->set('user', $user);

		echo parent::display('profile/sidebar/default');
	}
}
