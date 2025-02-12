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

class EasySocialControllerPolls extends EasySocialController
{
	/**
	 * Deletes the polls
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', [], 'array');

		foreach ($ids as $id) {
			$poll = ES::table('Polls');
			$poll->load((int) $id);

			$title = $poll->title;

			$state = $poll->delete();

			if ($state) {
				$actionlog = ES::actionlog();
				$actionlog->log('COM_ES_ACTION_LOG_POLL_DELETED', 'polls', ['pollTitle' => $title]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_POLLS_POLL_ITEM_HAS_BEEN_DELETED');

		return $this->view->call(__FUNCTION__);
	}
}