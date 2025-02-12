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

class EasySocialControllerBadges extends EasySocialController
{
	protected $logContext = 'badges';

	public function __construct()
	{
		parent::__construct();

		$this->registerTask('save', 'store');
		$this->registerTask('apply', 'store');
		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');
	}

	/**
	 * Removes badge from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function remove()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_BADGES_INVALID_BADGE_ID_PROVIDED');
		}

		foreach ($ids as $id) {
			$badge = ES::table('Badge');
			$badge->load((int) $id);

			if (!$badge->id) {
				continue;
			}

			$title = $badge->getTitle();

			$badge->delete();

			$this->logAction('COM_ES_ACTION_LOG_BADGE_REMOVED', [
					'name' => $title
				]);
		}

		$this->view->setMessage('COM_EASYSOCIAL_BADGES_DELETED');
		return $this->view->call(__FUNCTION__, $task);
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

		// Get the file from the request
		$file = $this->input->files->get('package', []);
		$data = ES::parseCSV($file['tmp_name'], false, false);

		if (!$data) {
			return $this->view->exception('COM_EASYSOCIAL_BADGES_INVALID_CSV_FILE');
		}

		// Load up the points library
		$badges = ES::badges();

		// Collect the list of failed and successfull items
		$failed = [];
		$success = [];

		foreach ($data as $row) {
			// Get the user id from the csv file
			$userId = isset($row[0]) ? $row[0] : false;

			// it seems like the first line first data will have the bom characters. we need to manually
			// remove it.
			$bom = pack('H*','EFBBBF');
			$userId = preg_replace("/^$bom/", '', $userId);

			// Get the badge id
			$badgeId = isset($row[1]) ? $row[1] : false;

			// Get the date of achievement.
			$dateAchieved = isset($row[2]) ? $row[2] : false;

			// Get the custom message
			$message = isset($row[3]) ? $row[3] : false;

			// Should the item be published on the stream
			$publishStream = isset($row[4]) && $row[4] == 1 ? true : false;

			// Convert the row of items into an object.
			$obj = (object) $row;

			// Try to load the badge
			$badge = ES::table('Badge');
			$badge->load($badgeId);

			// Skip this if we don't have sufficient data
			if (!$userId || !$badgeId || !$badge->id) {
				$failed[] = $obj;
				continue;
			}

			// Load up the user object
			$user = ES::user($userId);
			$state = $badges->create($badge, $user, $message, $dateAchieved, $publishStream);

			// Skip this if assignment failed.
			if (!$state) {
				continue;
			}

			$this->logAction('COM_ES_ACTION_LOG_BADGE_ASSIGNED', [
				'name' => $badge->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=badges&layout=form&id=' . $badge->id,
				'userName' => $user->getName(),
				'userLink' => 'index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id
			]);

			$success[] = $obj;
		}

		$this->view->setMessage('COM_EASYSOCIAL_BADGES_CSV_FILE_PARSED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__, $success, $failed);
	}

	/**
	 * Toggles the publish state for the badges
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_BADGES_INVALID_BADGE_ID_PROVIDED');
		}

		$task = $this->getTask();

		$message = 'COM_EASYSOCIAL_BADGES_PUBLISHED';
		$actionString = 'COM_ES_ACTION_LOG_BADGE_PUBLISHED';

		if ($task == 'unpublish') {
			$message = 'COM_EASYSOCIAL_BADGES_UNPUBLISHED';
			$actionString = 'COM_ES_ACTION_LOG_BADGE_UNPUBLISHED';
		}

		foreach ($ids as $id) {
			$badge = ES::table('Badge');
			$badge->load((int) $id);

			if (!$badge->id) {
				continue;
			}

			$badge->$task();

			$this->logAction($actionString, [
					'name' => $badge->getTitle(),
					'link' => 'index.php?option=com_easysocial&view=badges&layout=form&id=' . $badge->id
				]);
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__, $task);
	}

	/**
	 * Saves a badge from the back end
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store()
	{
		ES::checkToken();

		// Get the badge id from the request
		$id = $this->input->get('id', 0, 'int');

		$badge = ES::table('Badge');
		$actionString = 'COM_ES_ACTION_LOG_BADGE_CREATED';

		if ($id) {
			$badge->load($id);
			$actionString = 'COM_ES_ACTION_LOG_BADGE_UPDATED';
		}

		$post = $this->input->post->getArray();

		// Ensure that points threshold should always be positive integer
		$post['points_threshold'] = abs((int) $post['points_threshold']);

		if (isset($post['points_decrease_rule']) && $post['points_decrease_rule'] === 'null') {
			$post['points_decrease_rule'] = 0;
		}

		$badge->bind($post);
		$state = $badge->store();

		// Process image
		$image = $this->input->files->get('image');

		if (isset($image['tmp_name']) && isset($image['error']) && $image['error'] == 0) {
			$badge->uploadAvatar($image);
		}

		$this->logAction($actionString, [
				'name' => $badge->getTitle(),
				'link' => 'index.php?option=com_easysocial&view=badges&layout=form&id=' . $badge->id
			]);

		$this->view->setMessage('COM_EASYSOCIAL_BADGES_UPDATED_SUCCESS');
		return $this->view->call(__FUNCTION__, $this->getTask(), $badge);
	}

	/**
	 * Processes the uploaded rule file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload()
	{
		$file = $this->input->files->get('package', []);

		// Allowed extensions
		$allowed = array('zip', 'badge', 'badges');

		// Install it now.
		$rules = ES::rules();
		$state = $rules->upload($file, 'badges', $allowed);

		if ($state === false) {
			$this->view->setMessage($rules->getError(), ES_ERROR);

			return $this->view->call(__FUNCTION__);
		}

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

		$model = ES::model('Badges');
		$paths = $this->config->get('badges.paths');
		$files	= array();

		foreach ($paths as $path) {
			$data = $model->scan($path);

			foreach ($data as $file) {
				$files[] = $file;
			}
		}

		$this->logAction('COM_ES_ACTION_LOG_BADGES_DISCOVERED', [
			'total' => count($files)
		]);

		return $this->view->call(__FUNCTION__, $files);
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
		$model = ES::model('Badges');

		$obj = new stdClass();
		$obj->file = str_ireplace(JPATH_ROOT, '', $file);
		$obj->rules = $model->install($file);

		return $this->view->call(__FUNCTION__, $obj);
	}
}
