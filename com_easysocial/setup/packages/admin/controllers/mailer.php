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

class EasySocialControllerMailer extends EasySocialController
{
	public function __construct()
	{
		parent::__construct();

		$this->registerTask('publish', 'togglePublish');
		$this->registerTask('unpublish', 'togglePublish');

		// Task aliases for purging items
		$this->registerTask('purgeSent', 'purge');
		$this->registerTask('purgePending', 'purge');
		$this->registerTask('purgeAll', 'purge');
	}

	/**
	 * Resets a list of email template files to it's original state
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function reset()
	{
		// Check for request forgeries
		ES::checkToken();

		$files = $this->input->get('file', array(), 'default');
		$files = ES::makeArray($files);

		if (!$files) {
			return $this->view->exception('COM_EASYSOCIAL_EMAIL_INVALID_ID_PROVIDED');
		}

		$model = ES::model("Emails");

		foreach ($files as $file) {

			$file = base64_decode($file);
			$path = $model->getOverrideFolder($file);

			JFile::delete($path);

			$this->actionlog->log('COM_ES_ACTION_LOG_MAILER_RESET_FILE', 'mailer', [
					'filename' => $file,
					'link' => 'index.php?option=com_easysocial&view=mailer&layout=editfile&file=' . urlencode($file)
				]);
		}

		// Get the current editor
		$this->view->setMessage('COM_EASYSOCIAL_EMAIL_DELETED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Saves an email template
	 *
	 * @since	2.0.0
	 * @access	public
	 */
	public function saveFile()
	{
		ES::checkToken();

		// Get the contents of the email template
		$contents = $this->input->get('source', '', 'raw');

		$file = $this->input->get('file', '', 'default');
		$file = base64_decode($file);

		// Get the overriden path
		$model = ES::model("Emails");
		$path = $model->getOverrideFolder($file);

		$model->write($path, $contents);

		$this->actionlog->log('COM_ES_ACTION_LOG_MAILER_SAVED_FILE', 'mailer', [
				'filename' => $file,
				'link' => 'index.php?option=com_easysocial&view=mailer&layout=editfile&file=' . urlencode($file)
			]);

		$this->view->setMessage('COM_EASYSOCIAL_EMAILS_TEMPLATE_FILE_SAVED_SUCCESSFULLY');

		return $this->view->call(__FUNCTION__, $file, $path);
	}

	/**
	 * Purge mail items from the spool.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		ES::checkToken();

		$task = $this->getTask();
		$method = strtolower($task);

		$model = ES::model('Mailer');
		$state = $model->$task();

		if (!$state) {
			$message = 'COM_EASYSOCIAL_ERRORS_MAILER_PURGE_ALL';

			if ($task == 'purgePending') {
				$message = 'COM_EASYSOCIAL_ERRORS_MAILER_PURGE_PENDING';
			}

			if ($task == 'purgeSent') {
				$message = 'COM_EASYSOCIAL_ERRORS_MAILER_PURGE_SENT';
			}

			return $this->view->exception($message);
		}

		$message = 'COM_EASYSOCIAL_MAILER_ALL_ITEMS_PURGED_SUCCESSFULLY';

		if ($task == 'purgePending') {
			$message = 'COM_EASYSOCIAL_MAILER_PENDING_ITEMS_PURGED_SUCCESSFULLY';
		}

		if ($task == 'purgeSent') {
			$message = 'COM_EASYSOCIAL_MAILER_SENT_ITEMS_PURGED_SUCCESSFULLY';
		}

		$this->actionlog->log('COM_ES_ACTION_LOG_MAILER_' . strtoupper($task), 'mailer');

		$this->view->setMessage($message);
		return $this->view->call('purge');
	}

	/**
	 * Toggle publish button
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function togglePublish()
	{
		ES::checkToken();

		$task = $this->getTask();
		$method = strtolower($task);

		$ids = $this->input->get('cid', array(), 'int');

		if (!$ids) {
			return $this->view->exception('COM_EASYSOCIAL_ERRORS_MAILER_NO_ID');
		}

		$lib = ES::mailer();

		foreach ($ids as $id) {
			$mailer = ES::table('Mailer');
			$mailer->load((int) $id);

			if (!$mailer->$method()) {
				return $this->view->exception($mailer->getError());
			}

			$mailer->loadLanguage();
			$mailer->title = $lib->translate($mailer->title, $mailer->params);

			$this->actionlog->log('COM_ES_ACTION_LOG_MAILER_' . strtoupper($method), 'mailer', [
					'name' => $mailer->title
				]);
		}

		$message = 'COM_EASYSOCIAL_MAILER_ITEMS_MARKED_AS_SENT';

		if ($task != 'publish') {
			$message = 'COM_EASYSOCIAL_MAILER_ITEMS_MARKED_AS_PENDING';
		}

		$this->view->setMessage($message);
		return $this->view->call(__FUNCTION__);
	}
}
