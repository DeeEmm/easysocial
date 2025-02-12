<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// Import parent view
ES::import( 'site:/views/views' );

class EasySocialViewPoints extends EasySocialSiteView
{
	/**
	 * Displays user's points history
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getHistory()
	{
		$id = $this->input->get('id', 0, 'int');
		$user = ES::user($id);

		$options = array('limit' => ES::getLimit('points.history.limit'));

		$model = ES::model('Points');
		$histories = $model->getHistory($user->id, $options);
		$pagination = $model->getPagination();

		$this->set('paginate', true);
		$this->set('histories', $histories);
		$this->set('user', $user);

		$output = parent::display('site/points/history/item');

		$done = $pagination->total <= ($pagination->limitstart + $pagination->limit);

		return $this->ajax->resolve($output, $pagination->pagesCurrent * $pagination->limit, $done);
	}

	/**
	 * Post process of loading achievers
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function loadAchievers($achievers, $nextlimit)
	{
		$html = '';

		if ($achievers) {
			foreach ($achievers as $user) {
				$themes = ES::themes();
				$themes->set('user', $user);

				$html .= $themes->output('site/points/item/achiever');
			}
		}

		return $this->ajax->resolve($html, $nextlimit);
	}

	/**
	 * Post process of getting achievers count
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getAchieversCount($data)
	{
		return $this->ajax->resolve($data);
	}
}
