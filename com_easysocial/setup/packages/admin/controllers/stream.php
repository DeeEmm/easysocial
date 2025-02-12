<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerStream extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		// Register task aliases here.
		$this->registerTask('archive', 'archive');
		$this->registerTask('trash', 'trash');
		$this->registerTask('restoreTrash', 'restoreTrash');
	}

	public function archive()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		if ($ids) {
			$model = ES::model('Stream');
			$state = $model->archive($ids);

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_STREAM_ARCHIVE_STREAM_FAILED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			foreach ($ids as $id) {
				$this->actionlog->log('COM_ES_ACTION_LOG_STREAM_ARCHIVED', 'stream', ['id' => $id]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_ARCHIVE_STREAM_SUCCESS');

		return $this->view->call('standardRedirection');
	}

	public function purge()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		if ($ids) {
			foreach ($ids as $id) {
				$id = (int) $id;

				// Load the stream item.
				$item = ES::table('Stream');
				$item->load($id);

				$state = $item->delete();

				if (!$state) {
					$this->view->setMessage('COM_EASYSOCIAL_STREAM_DELETE_STREAM_FAILED', ES_ERROR);
					return $this->view->call(__FUNCTION__);
				}

				$this->actionlog->log('COM_ES_ACTION_LOG_STREAM_DELETED', 'stream', ['id' => $item->id]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_DELETE_STREAM_SUCCESS');
		return $this->view->call('standardRedirection');
	}

	public function restoreTrash()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		if ($ids) {
			$model = ES::model('Stream');
			$state = $model->restoreStreamItem($ids);

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_STREAM_RESTORE_STREAM_FAILED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			foreach ($ids as $id) {
				$this->actionlog->log('COM_ES_ACTION_LOG_STREAM_RESTORE_TRASH', 'stream', ['id' => $id]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_RESTORE_STREAM_SUCCESS');
		return $this->view->call('standardRedirection');
	}

	public function trash()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		if ($ids) {
			$model = ES::model('Stream');
			$state = $model->trashStreamItem($ids);

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_STREAM_TRASH_STREAM_FAILED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			foreach ($ids as $id) {
				$this->actionlog->log('COM_ES_ACTION_LOG_STREAM_TRASH', 'stream', ['id' => $id]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_TRASH_STREAM_SUCCESS');
		return $this->view->call('standardRedirection');
	}

	public function restore()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		if ($ids) {
			$model = ES::model('Stream');
			$state = $model->restoreArchivedItem($ids);

			if (!$state) {
				$this->view->setMessage('COM_EASYSOCIAL_STREAM_RESTORE_STREAM_FAILED', ES_ERROR);
				return $this->view->call(__FUNCTION__);
			}

			foreach ($ids as $id) {
				$this->actionlog->log('COM_ES_ACTION_LOG_STREAM_RESTORE', 'stream', ['id' => $id]);
			}
		}

		$this->view->setMessage('COM_EASYSOCIAL_STREAM_RESTORE_STREAM_SUCCESS');
		return $this->view->call('standardRedirection');
	}

	/**
	 * Publishing scheduled streams requires to remove items from #__social_stream_scheduled and update item in #__social_stream to published.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', '', 'var');

		if ($ids) {
			foreach ($ids as $id) {
				$stream = ES::table('Stream');
				$stream->load($id);

				$streamItem = ES::table('StreamItem');
				$streamItem->load(array('uid' => $stream->id));

				$scheduled = ES::table('StreamScheduled');
				$scheduled->load(array('stream_id' => $stream->id));

				// Load up the dispatcher so that we can trigger this.
				$dispatcher = ES::dispatcher();

				$group = SOCIAL_TYPE_USER;

				if ($stream->isCluster()) {
					$cluster = $stream->getCluster();
					$group = $cluster->getType();
				}

				// Build the arguments for the trigger
				$args = array(&$stream, &$streamItem, &$scheduled);

				// @trigger onScheduledAppStoryPublish
				$dispatcher->trigger($group, 'onPublishScheduledAppStory', $args);

				$this->actionlog->log('COM_ES_ACTION_LOG_STREAM_PUBLISH', 'stream', ['id' => $id]);
			}
		}

		$this->view->setMessage('COM_ES_STREAM_PUBLISHED_SCHEDULED_STREAM_SUCCESS');
		return $this->view->call('standardRedirection');
	}
}
