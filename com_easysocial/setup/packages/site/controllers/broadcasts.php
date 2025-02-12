<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerBroadcasts extends EasySocialController
{
	/**
	 * Since achievers are paginated, this allows retrieving more achievers
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public function delete()
	{
		ES::requireLogin();
		ES::checkToken();

		if (!$this->my->isSiteAdmin()) {
			$this->view->setMessage('COM_ES_BROADCASTS_NOT_ALLOWED_TO_DELETE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}


		$streamId = $this->input->get('id', 0, 'int');

		if (!$streamId) {
			$this->view->setMessage('COM_ES_INVALID_DATA_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// Load the stream item.
		$stream = ES::table('Stream');
		$stream->load($streamId);

		// Check if the user can delete this stream
		if (!$stream->canDelete()) {
			$this->view->setMessage($item->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		// 1. delete broadcast notification (for notification type)
		// 2. delete broadcast (for popup type)
		// 3. delete broadcast itself.

		$broadcast = ES::table('broadcast');
		$broadcast->load(array('stream_id' => $streamId));

		if ($broadcast->id) {
			$state = $broadcast->delete();

			if ($state) {
				// 4. finally delete stream
				$state = $stream->delete();
			}
		}

		return $this->view->call(__FUNCTION__);
	}
}
