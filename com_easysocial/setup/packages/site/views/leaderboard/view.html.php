<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('site:/views/views');

class EasySocialViewLeaderBoard extends EasySocialSiteView
{
	/**
	 * Renders the leaderboard listing for users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		ES::checkCompleteProfile();

		ES::setMeta();

		$limit = ES::getLimit('userslimit');

		$model = ES::model('Leaderboard');
		$excludeAdmin = !$this->config->get('leaderboard.listings.admin' );

		$options = array('ordering' => 'points', 'limit' => $limit, 'excludeAdmin' => $excludeAdmin);
		$users = $model->getLadder($options, false);

		// calculate the next limitstart
		$limitstart = $limit;
		if (count($users) < $limit) {
			$limitstart = 0;
		}

		// get current user ranking.
		$userRank = false;
		if (!$this->my->guest) {
			$userRank = $model->getUserRank($this->my->id);
		}

		// Set page properties
		$this->page->title('COM_EASYSOCIAL_PAGE_TITLE_LEADERBOARD');
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_LEADERBOARD');

		$this->set('users', $users);
		$this->set('userRank', $userRank);
		$this->set('limitstart', $limitstart);

		echo parent::display('site/leaderboard/default/default');
	}
}
