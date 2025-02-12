<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerBadges extends EasySocialController
{
	/**
	 * Since achievers are paginated, this allows retrieving more achievers
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function loadAchievers()
	{
		ES::checkToken();

		$id = $this->input->getInt('id');
		$start = $this->input->getInt('start');

		$theme = ES::themes();
		$pageLimit = ES::getLimit('achieverslimit');

		$options = array(
						'start' => $start,
						'limit' => $pageLimit
					);

		$model = ES::model('badges');
		$achievers = $model->getAchievers($id, $options);
		$nextlimit = $model->getNextLimit();

		$this->view->call(__FUNCTION__, $achievers, $nextlimit);
	}
}
