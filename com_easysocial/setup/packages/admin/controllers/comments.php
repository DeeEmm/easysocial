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

class EasySocialControllerComments extends EasySocialController
{
	/**
	 * Remove a comment from the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', [], 'array');

		if (!$ids) {
			return $this->view->exception('Invalid id provided');
		}

		foreach ($ids as $id) {
			$table = ES::table('Comments');
			$exists = $table->load((int) $id);

			// The reason that we do not throw any errors is primarily because we do not want errors to occur when the user
			// selects a parent and child comment to be deleted together.
			if ($exists) {
				$limit = 150;
				$comment = $table->comment;

				if (strlen($comment) > $limit) {
					$comment = substr($table->comment, 0, 150) . JText::_('COM_EASYSOCIAL_ELLIPSIS');
				}

				$state = $table->delete();

				if ($state) {
					$actionlog = ES::actionlog();
					$actionlog->log('COM_ES_ACTION_LOG_COMMENT_DELETED', 'comments', ['comment' => $comment]);
				}
			}
		}

		$this->view->setMessage('COM_ES_COMMENTS_DELETED');
		return $this->view->call(__FUNCTION__, $task);
	}
}
