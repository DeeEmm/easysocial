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

require_once(dirname(dirname(__DIR__)) . '/helper.php');

class KunenaViewProfile extends SocialAppsView
{	
	public function kunenaHelper()
	{
		$helper = new KunenaHelper();
		return $helper;
	}

	public function display($userId = null, $docType = null)
	{
		$kunenaHelper = $this->kunenaHelper();

		if (!$kunenaHelper->isEnabled()) {
			return;
		}

		// Load language file from Kunena
		$kunenaHelper->loadLanguage('com_kunena.libraries', 'admin');

		JFactory::getLanguage()->load('com_kunena.libraries', JPATH_ADMINISTRATOR);
		JFactory::getLanguage()->load('com_kunena');

		$this->setTitle('APP_FORUMS_APP_TITLE');

		// Get the current user
		$user = ES::user($userId);

		// Get the user params
		$params = $this->getUserParams($user->id);

		// Get the app params
		$appParams = $this->app->getParams();

		// Get the total items to display
		$total = (int) $params->get('total_post_display', $appParams->get('total_post_display', 5));

		// Get the posts created by the user.
		$model = $this->getModel('Posts');
		$posts = $model->getPosts($user->id, $total);

		// Get the replies
		$replies = $model->getReplies($user->id);

		// Get stats
		$stats = $model->getStats($user->id);

		// Get total replies
		$totalReplies = $model->getTotalReplies($user->id);

		// Get Kunena's template
		$kunenaTemplate = $kunenaHelper->getTemplate();

		$kunenaUser = $kunenaHelper->user($userId);

		$this->set('totalReplies', $totalReplies);
		$this->set('stats', $stats);
		$this->set('thanks', $kunenaUser->thankyou);
		$this->set('totalPosts', $kunenaUser->posts);
		$this->set('kunenaTemplate', $kunenaTemplate);
		$this->set('user', $user);
		$this->set('params', $params);
		$this->set('posts', $posts);
		$this->set('replies', $replies);
		$this->set('kunenaHelper', $kunenaHelper);

		echo parent::display('profile/default');
	}

	/**
	 * Method to display the sidebar from a module
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebar($moduleLib, $user)
	{
		$kunenaHelper = $this->kunenaHelper();

		if (!$kunenaHelper->isEnabled()) {
			return;
		}

		$kunenaUser = $kunenaHelper->user($user->id);
		$model = $this->getModel('Posts');

		$totalPosts = $kunenaUser->posts;
		$totalReplies = $model->getTotalReplies($user->id);
		$thanks = $kunenaUser->thankyou;

		$this->set('totalPosts', $totalPosts);
		$this->set('totalReplies', $totalReplies);
		$this->set('thanks', $thanks);
		$this->set('user', $user);
		$this->set('moduleLib', $moduleLib);
		$this->set('kunenaHelper', $kunenaHelper);

		echo parent::display('profile/sidebar/default');
	}
}
