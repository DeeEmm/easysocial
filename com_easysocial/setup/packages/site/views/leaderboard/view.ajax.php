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

ES::import('site:/views/views');

class EasySocialViewLeaderboard extends EasySocialSiteView
{
	/**
	 * Display dialog to confirm deleting of attachment
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function loadmore()
	{
		// Users must be logged in
		ES::requireLogin();

		$limitstart = $this->input->get('limitstart', 0, 'int');

		$excludeAdmin = !$this->config->get('leaderboard.listings.admin' );
		$limit = ES::getLimit('userslimit');

		$options = array('ordering' => 'points', 'limit' => ($limit + 1), 'excludeAdmin' => $excludeAdmin, 'limitstart' => $limitstart);
		$model = ES::model('Leaderboard');
		$users = $model->getLadder($options, false);

		$contents = '';
		$nextlimit = 0;
		$hasNext = false;

		if ($users) {

			if (count($users) > $limit) {
				// need to remove the last item.
				array_pop($users);
				$hasNext = true;
			}

			$theme = ES::themes();

			// user ranking
			$i = $limitstart;

			foreach ($users as $user) {
				$contents .= $theme->loadTemplate('site/leaderboard/default/item', array('user' => $user, 'pos' => ++$i));
			}

			// calculate the next limitstart
			if ($hasNext) {
				$nextlimit = $limitstart + $limit;
			}
		}

		return $this->ajax->resolve($contents, $nextlimit);
	}
}
