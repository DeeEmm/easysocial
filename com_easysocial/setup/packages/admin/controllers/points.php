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

class EasySocialControllerPoints extends EasySocialController
{
	public $points_url = 'index.php?option=com_easysocial&view=points&layout=form';

	public function __construct()
	{
		parent::__construct();

		$this->registerTask('apply', 'save');
		$this->registerTask('unpublish', 'publish');
	}

	/**
	 * Deletes a list of provided points
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_POINTS_INVALID_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$point = ES::table('Points');
			$point->load((int) $id);

			$point->delete();

			$this->actionlog->log('COM_ES_ACTION_LOG_POINTS_REMOVED', 'points', ['name' => $point->title]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_POINTS_DELETED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Publishes a point
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function publish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');
		$task = $this->getTask();

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_POINTS_INVALID_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$point = ES::table('Points');
			$point->load($id);

			$point->$task();

			$this->actionlog->log('COM_ES_ACTION_LOG_POINTS_PUBLISHED', 'points', ['link' => $this->points_url . '&id=' . $point->id, 'name' => $point->title]);
		}

		$message = $task == 'publish' ? 'COM_EASYSOCIAL_POINTS_PUBLISHED_SUCCESSFULLY' : 'COM_EASYSOCIAL_POINTS_UNPUBLISHED_SUCCESSFULLY';

		$this->view->setMessage($message);

		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Responsible to save a user point.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save()
	{
		ES::checkToken();

		$task = $this->getTask();
		$id = $this->input->get('id', 0, 'int');

		$point = ES::table('Points');
		$point->load($id);

		if (!$id || !$point->id) {
			return $this->view->exception('COM_EASYSOCIAL_POINTS_INVALID_ID_PROVIDED');
		}

		$post = $this->input->post->getArray();

		// If there are params sent from the post, we need to process them accordingly.
		if (isset($post['params']) && !empty($post['params'])) {
			$postParams = $post['params'];
			$params = $point->getParams();

			foreach ($postParams as $key => $value) {
				$params->set($key . '.value', $value);
			}

			$post['params']	= $params->toString();
		}

		$point->bind($post);

		if (!$point->store()) {
			return $this->view->exception($point->getError());
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_POINTS_SAVED', 'points', ['link' => $this->points_url . '&id=' . $point->id, 'name' => $point->title]);

		$this->view->setMessage('COM_EASYSOCIAL_POINTS_SAVED_SUCCESSFULLY');

		return $this->view->call(__FUNCTION__, $task, $point);
	}

	/**
	 * Processes the uploaded rule file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		// Get the current path that we should be searching for.
		$file = $this->input->files->get('package', []);

		// Allowed extensions
		$allowed = array('zip', 'points');

		// Install it now.
		$rules = ES::rules();
		$state = $rules->upload($file, 'points', $allowed);

		if ($state === false) {
			$this->view->setMessage($rules->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_POINTS_UPLOAD', 'points');

		$this->view->setMessage($state);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Discover .points files from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discoverFiles()
	{
		ES::checkToken();

		// Retrieve the points model to scan for the path
		$model = ES::model('Points');
		$paths = $this->config->get('points.paths');

		// Result set.
		$files = array();

		foreach ($paths as $path) {
			$data = $model->scan($path);

			foreach ($data as $file) {
				$files[] = $file;
			}
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_POINTS_DISCOVER', 'points');

		// Return the data back to the view.
		return $this->view->call(__FUNCTION__, $files);
	}

	/**
	 * Mass assign points for users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function massAssign()
	{
		ES::checkToken();

		$file = $this->input->files->get('package', []);
		$data = ES::parseCSV($file[ 'tmp_name' ] , false , false );

		if (!$data) {
			$this->view->setMessage('COM_EASYSOCIAL_POINTS_INVALID_CSV_FILE', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$points = ES::points();
		$failed = array();
		$success = array();

		foreach ($data as $row) {
			$userId = isset( $row[ 0 ] ) ? $row[ 0 ] : false;
			$value = isset( $row[ 1 ] ) ? $row[ 1 ] : false;
			$message = isset( $row[ 2 ] ) ? $row[ 2 ] : false;

			$obj = (object) $row;

			// Skip invalid point assignments
			if (!$userId || !$points) {
				$failed[] = $obj;
				continue;
			}

			$points->assignCustom($userId, $value, $message);

			$success[] = $obj;
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_POINTS_MASS_ASSIGN', 'points');

		$this->view->setMessage('COM_EASYSOCIAL_POINTS_CSV_FILE_PARSED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $success, $failed);
	}

	/**
	 * Scans for rules throughout the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan()
	{
		ES::checkToken();

		$file = $this->input->get('file', '', 'default');
		$model = ES::model('Points');

		$obj = new stdClass();
		$obj->file = str_ireplace(JPATH_ROOT, '', $file);
		$obj->rules = $model->install($file);

		return $this->view->call(__FUNCTION__, $obj);
	}
}
