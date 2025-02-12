<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerAlerts extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('toggleEmailPublish', 'togglePublish');
		$this->registerTask('toggleSystemPublish', 'togglePublish');
		$this->registerTask('toggleAllowModifyEmail', 'togglePublish');
		$this->registerTask('toggleAllowModifySystem', 'togglePublish');

		$this->registerTask('publish', 'togglePublishState');
		$this->registerTask('unpublish', 'togglePublishState');
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

		$ids = $this->input->get('cid', array(), 'array');
		$reset = $this->app->input->get('reset', 0, 'int');

		// Get the current task
		$task = $this->getTask();

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_ALERTS_INVALID_ID_PROVIDED');
		}

		$resetValue = null;

		foreach ($ids as $id) {
			$alert = ES::table('Alert');
			$alert->load((int) $id);
			$actionString = 'COM_ES_ACTION_LOG_ALERT_TOGGLE_PUBLISH';

			if ($task == 'toggleEmailPublish') {
				$alert->email = ($alert->email == SOCIAL_STATE_UNPUBLISHED) ? SOCIAL_STATE_PUBLISHED : SOCIAL_STATE_UNPUBLISHED;
				$resetValue = $alert->email;

				if (!$alert->email) {
					$actionString = 'COM_ES_ACTION_LOG_ALERT_TOGGLE_UNPUBLISH';
				}
			}

			if ($task == 'toggleSystemPublish') {
				$alert->system = ($alert->system == SOCIAL_STATE_UNPUBLISHED) ? SOCIAL_STATE_PUBLISHED : SOCIAL_STATE_UNPUBLISHED;
				$resetValue = $alert->system;

				if (!$alert->system) {
					$actionString = 'COM_ES_ACTION_LOG_ALERT_TOGGLE_UNPUBLISH';
				}
			}

			if ($task == 'toggleAllowModifyEmail') {
				$alert->email_published = ($alert->email_published == SOCIAL_STATE_UNPUBLISHED) ? SOCIAL_STATE_PUBLISHED : SOCIAL_STATE_UNPUBLISHED;

				if (!$alert->email_published) {
					$actionString = 'COM_ES_ACTION_LOG_ALERT_TOGGLE_UNPUBLISH';
				}
			}

			if ($task == 'toggleAllowModifySystem') {
				$alert->system_published = ($alert->system_published == SOCIAL_STATE_UNPUBLISHED) ? SOCIAL_STATE_PUBLISHED : SOCIAL_STATE_UNPUBLISHED;

				if (!$alert->system_published) {
					$actionString = 'COM_ES_ACTION_LOG_ALERT_TOGGLE_UNPUBLISH';
				}
			}

			$alert->store();

			$this->actionlog->log($actionString, 'alerts', [
				'link' => 'index.php?option=com_easysocial&view=alerts',
				'actionTitle' => $alert->getTitle(),
				'element' => ucfirst($alert->element)
			]);
		}

		// check if we need to reset user setttings or not.
		if ($reset && ($task == 'toggleEmailPublish' || $task == 'toggleSystemPublish')) {
			$type = 'email';
			if ($task == 'toggleSystemPublish') {
				$type = 'system';
			}

			$model = ES::model('alert');
			$model->resetUserSettings($ids, $type, $resetValue);
		}

		$this->view->setMessage('COM_EASYSOCIAL_ALERTS_CHANGED_SUCCESS');

		return $this->view->call(__FUNCTION__, $task);
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

		$model = ES::model('Alert');
		$paths = $this->config->get('alerts.paths');

		// Result set.
		$files = array();

		foreach ($paths as $path) {
			$data = $model->scan($path);

			foreach($data as $file) {
				$files[] = $file;
			}
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_LANGUAGES_DISCOVER', 'alerts', [
			'link' => 'index.php?option=com_easysocial&view=languages'
		]);

		// Return the data back to the view.
		return $this->view->call(__FUNCTION__, $files);
	}

	/**
	 * Scans for .alert rules throughout the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan()
	{
		ES::checkToken();

		// Get the current path that we should be searching for.
		$file = $this->input->get('file', '', 'default');
		$model = ES::model('Alert');

		$obj = new stdClass();

		// Format the output to display the relative path.
		$obj->file = str_ireplace(JPATH_ROOT, '', $file);
		$obj->rules = $model->install($file);

		return $this->view->call(__FUNCTION__, $obj);
	}

	/**
	 * Allows caller to upload files to install new access rules
	 *
	 * @since	1.4.9
	 * @access	public
	 */
	public function upload()
	{
		// Get the current path that we should be searching for.
		$file = $this->input->files->get('package', []);

		// Allowed extensions
		$allowed = array('zip', 'alert');

		// Install it now.
		$rules = ES::rules();
		$state = $rules->upload($file, 'alert', $allowed);

		if ($state === false) {
			$this->view->setMessage($rules->getError(), ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_ALERT_INSTALL', 'alerts', [
			'link' => 'index.php?option=com_easysocial&view=languages'
		]);

		$this->view->setMessage($state);
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Publish or unpublish an alert rule
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function togglePublishState()
	{
		ES::checkToken();

		$ids = $this->input->get('cid', 0, 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_ALERTS_INVALID_ID_PROVIDED');
		}

		$state = $this->getTask() === 'publish';

		foreach ($ids as $id) {
			$table = ES::table('alert');
			$table->load((int) $id);
			$table->published = $state;

			$table->store();
		}

		$message = $state ? 'COM_EASYSOCIAL_ALERTS_PUBLISHED_SUCCESS' : 'COM_EASYSOCIAL_ALERTS_UNPUBLISHED_SUCCESS';

		$this->view->setMessage($message);		
		return $this->view->call('togglePublishState');
	}
}
