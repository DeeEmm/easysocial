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

class SocialCron extends EasySocial
{
	private $status = null;
	private $output = array();

	public static function factory()
	{
		$obj = new self();

		return $obj;
	}

	/**
	 * Allows caller to invoke sending of emails internally
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function dispatchEmails()
	{
		$path = dirname(__FILE__) . '/hooks/email.php';

		require_once($path);

		$states = array();

		$hook = new SocialCronHooksEmail();
		$hook->execute($states);
	}

	/**
	 * Allows caller to invoke sending of emails internally
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function executeCronDownload()
	{
		// Check if we need a secure phrase.
		$this->validateCronPhrase();

		$result = ES::gdpr()->cron();
		$this->output($result);

		$this->render();
	}

	/**
	 * Allows caller to invoke sending the related notification to mobile app
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function executeCronMobile()
	{
		$this->validateCronPhrase();

		$data = array();

		// @trigger: apps.onCronMobileExecute
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onCronMobileExecute', $data);

		$result = ES::response('Mobile notifications has been successfully processed', SOCIAL_MSG_INFO);

		$this->output($result);
		$this->render();
	}

	/**
	 * Triggers the cron service
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function execute()
	{
		// Check if we need a secure phrase.
		$this->validateCronPhrase();

		// Data to be passed to the triggers.
		$data = array();

		// Array of states
		$states = array();

		// @trigger: fields.onBeforeCronExecute
		// Retrieve custom fields for the current step
		$fieldsModel = ES::model('Fields');
		$customFields = $fieldsModel->getCustomFields(array('appgroup' => SOCIAL_TYPE_USER));

		$fields = ES::fields();
		$fields->trigger('onCronExecute', SOCIAL_TYPE_USER, $customFields, $data);

		// @trigger: apps.onBeforeCronExecute
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onCronExecute', $data);

		// Load up files in hooks
		$this->hook($states);

		if (!empty($states)) {
			foreach($states as $state) {
				$this->output($state);
			}
		}

		// Perform maintenance
		$maintenance = ES::get('Maintenance');
		$maintenance->cleanup();

		// Send Activation email to inactive members
		

		$this->render();
	}

	/**
	 * Validate Cron phrase
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function validateCronPhrase()
	{
		// Check if we need a secure phrase.
		$requirePhrase = $this->config->get('general.cron.secure');
		$storedPhrase = $this->config->get('general.cron.key');
		$phrase = $this->input->get('phrase', '', 'default');

		if ($requirePhrase && empty($phrase) || ($requirePhrase && $storedPhrase != $phrase)) {
			$this->setStatus('failed');
			$this->output(JText::_('COM_EASYSOCIAL_CRONJOB_PASSPHRASE_INVALID'));
			return $this->render();
		}
	}


	/**
	 * Retrieves a list of hooks for cron services
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function hook(&$state)
	{
		$path = __DIR__ . '/hooks';
		$files = JFolder::files($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

		if (!$files) {
			return;
		}

		// debug
		// $files = array('digestemail.php');

		// #910
		// we need to sort the files so that storage.php will be processed 1st before email.
		$storageIdx = array_search('storage.php', $files);
		if ($storageIdx !== false && !is_null($storageIdx)) {
			//reset the array index
			unset($files[$storageIdx]);

			// prepend storage.php to the array
			array_unshift($files, 'storage.php');
		}

		foreach ($files as $file) {
			require_once($path . '/' . $file);

			$className = 'SocialCronHooks' . str_ireplace('.php', '', $file);

			$hook = new $className();
			$hook->execute($state);
		}
	}

	/**
	 * Sets the status
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * Displays the json codes
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function output($contents, $status = '200')
	{
		$output 			= new stdClass();

		$output->status 	= $status;
		$output->contents 	= $contents;
		$output->time 		= ES::date()->toMySQL();

		$this->output[]		= $output;
	}

	/**
	 * Renders the cronjob output
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function render()
	{
		header('Content-type: application/json; UTF-8');

		echo json_encode($this->output);
		exit;
	}
}
