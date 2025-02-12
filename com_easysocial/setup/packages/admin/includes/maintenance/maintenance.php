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

class SocialMaintenance
{
	public $error;

	public static function getInstance()
	{
		static $instance = null;

		if (empty($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	public static function factory()
	{
		return new self();
	}

	public function debug()
	{
		var_dump($this->session_id);
		exit;
	}


	public function cleanup()
	{
		$config = ES::config();

		// Clean up sent emails that more than 7 days
		$this->cleanupSentEmails();

		// Clean up temporary files from uploader
		$this->cleanupUploader();

		// Clean up temporary data
		$this->cleanFromTmp();

		//clearing tmp date from social_registration.
		$this->cleanFromRegistration();

		// Clean up temporary sessions for steps
		$this->cleanSteps();

		// Cleanup expired gdpr download request
		$this->purgeExpiredDownload();

		// Cleanup expired folders/files from media/com_easysocial/tmp/...
		// Because those temporary file will store it there for longer if the user upload the file then stop the process.
		$this->cleanupMediaTmpFiles();

		// Check for apple JWT token expiration date
		$this->generateJwtToken();
	}

	/**
	 * Processes the garbage collector on expired download request
	 *
	 * @since	2.2.4
	 * @access	public
	 */
	public function purgeExpiredDownload()
	{
		$config = ES::config();

		if (!$config->get('users.download.enabled')) {
			return;
		}

		$gdpr = ES::gdpr();
		$gdpr->purgeExpired();
	}

	/**
	 * Purge sent emails automatically
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function cleanupSentEmails()
	{
		$db = ES::db();

		$query = array();

		$query[] = 'DELETE FROM ' . $db->nameQuote('#__social_mailer');
		$query[] = 'WHERE ' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = 'AND DATEDIFF(NOW(), ' . $db->quoteName('created') . ') >=' . $db->Quote(7);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$db->Query();
	}

	/**
	 * Clean up temporary uploader files
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function cleanupUploader()
	{
		$db = ES::db();
		$sql = $db->sql();
		$now = ES::date();

		$query = 'DELETE FROM ' . $db->nameQuote('#__social_uploader');
		$query .= ' WHERE DATE_ADD(' . $db->nameQuote('created') . ', INTERVAL 60 MINUTE) <=' . $db->Quote($now->toSql());

		$sql->raw($query);

		$db->setQuery($sql);
		$db->Query();
	}

	/**
	 * Cleanup temporary uploaded files
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function cleanFromTmp()
	{
		$db = ES::db();
		$sql = $db->sql();

		$now = ES::date();
		$query = 'delete from `#__social_tmp`';
		$query .= ' where `expired` <= ' . $db->Quote($now->toMySQL());

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();
	}

	/**
	 * Re-generate apple token if needed
	 *
	 * @since	3.2
	 * @access	public
	 */
	private function generateJwtToken()
	{
		$config = ES::config();

		if (!$config->get('oauth.apple.registration.enabled')) {
			return;
		}

		$expired = $config->get('oauth.apple.expired');

		if (empty($expired)) {
			return;
		}

		// We set the expired to 7 days earlier
		$preExpired = strtotime("-7 DAY", $expired);
		$nowUnix = ES::date()->toUnix();

		// if current time is earlier than expired date, skip.
		if ($nowUnix < $preExpired) {
			return;
		}

		// Regenerate the token
		ES::generateJWTToken();
	}

	private function cleanSteps()
	{
		$db = ES::db();
		$sql = $db->sql();
		$now = ES::date();

		// clean the steps data for records that exceeded 2 hour.
		// We increased it to 2 hours because has 1 customer report 1 hour is not enough due to too many form to fill in #4081
		$query = 'DELETE FROM ' . $db->nameQuote('#__social_step_sessions');
		$query .= ' WHERE DATE_ADD(' . $db->nameQuote('created') . ', INTERVAL 12 HOUR) <=' . $db->Quote($now->toSql());

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();
	}

	private function cleanFromRegistration()
	{
		$db = ES::db();
		$sql = $db->sql();
		$now = ES::date();

		// clean the registration temp data for records that exceeded 1 hour.
		$query = 'delete from `#__social_registrations`';
		$query .= ' where date_add(`created` , INTERVAL 60 MINUTE) <= ' . $db->Quote($now->toMySQL());

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();
	}

	/**
	 * Cleanup expired temporary uploaded files
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	private function cleanupMediaTmpFiles()
	{
		$config = ES::config();
		$tmpPath = JPATH_ROOT . $config->get('uploader.storage.container');

		// current datetime unix timestamp
		$currentDate = ES::date()->toUnix(true);

		// Check whether there got any file under /media/com_easysocial/tmp which included the directory folder
		$files = JFolder::files($tmpPath, '.', true, true);

		if ($files) {

			// Only process 30 files per cron executes
			$files = array_slice($files, 0, 30);

			foreach ($files as $filePath) {

				$fileExist = JFile::exists($filePath);

				if ($fileExist) {

					// current file modified time
					$modifiedDate = filemtime($filePath);

					// hardcoded 3 days for expired date
					$modifiedDate = strtotime("+3 DAY", $modifiedDate);

					// Only delete this file if the modified file datetime less than the current datetime
					if ($modifiedDate < $currentDate) {
						JFile::delete($filePath);
					}
				}
			}
		}

		// Check whether there got any directory folder under /media/com_easysocial/tmp
		$folders = JFolder::folders($tmpPath, '.', false, true);

		if ($folders) {

			// Only process 20 folders per cron executes
			$folders = array_slice($folders, 0, 20);

			foreach ($folders as $folderPath) {

				// check the folder whether still got any file or not
				// check for the subfolder as well
				$directoryFiles = JFolder::files($folderPath, '.', true, true);

				$noFileExist = count($directoryFiles) < 1 ? true : false;
				$directoryFolderExist = JFolder::exists($folderPath);

				// current folder modified time
				$modifiedDate = filemtime($folderPath);

				// hardcoded 3 days for expired date
				$modifiedDate = strtotime("+3 DAY", $modifiedDate);

				// if there do not have any files then we need to delete the folder
				if ($noFileExist && $directoryFolderExist && ($modifiedDate < $currentDate)) {
					JFolder::delete($folderPath);
				}
			}
		}
	}

	/**
	 * Get the available scripts and returns the script object in an array
	 *
	 * @since  1.2
	 * @access public
	 */
	public function getScripts($from = null)
	{
		$files = $this->getScriptFiles($from);

		$result = array();

		foreach ($files as $file) {
			$classname = $this->getScriptClassName($file);

			if ($classname === false) {
				continue;
			}

			$class = new $classname;

			$result[] = $class;
		}

		return $result;
	}

	/**
	 * Get the available script files and return the file path in an array
	 *
	 * @since  1.2
	 * @access public
	 */
	public function getScriptFiles($from = null, $operator = '>')
	{
		$files = array();

		// If from is empty, means it is a new installation, and new installation we do not want maintenance to run
		// Explicitly changed backend maintenance to pass in 'all' to get all the scripts instead.
		if (empty($from)) {
			return $files;
		}

		$path = ES::normalizeSeparator(SOCIAL_ADMIN_UPDATES);

		if ($from === 'all') {
			$files = array_merge($files, JFolder::files(SOCIAL_ADMIN_UPDATES, '.php$', true, true));
		} else {
			$folders = JFolder::folders(SOCIAL_ADMIN_UPDATES);

			if (!empty($folders)) {
				foreach ($folders as $folder) {

					// We don't want things from "manual" folder
					if ($folder === 'manual') {
						continue;
					}

					// We cannot do $folder > $from because '1.2.8' > '1.2.15' is TRUE
					// We want > $from by default, NOT >= $from, unless manually specified through $operator
					if (version_compare($folder, $from, $operator)) {
						$fullpath = SOCIAL_ADMIN_UPDATES . '/' . $folder;

						$files = array_merge($files, JFolder::files($fullpath, '.php$', false, true));
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Get the script class name
	 *
	 * @since  1.2
	 * @access public
	 */
	public function getScriptClassName($file)
	{
		static $classnames = array();

		if (!isset($classnames[$file])) {
			if (!JFile::exists($file)) {
				$this->setError('Script file not found: ' . $file);
				$classnames[$file] = false;
				return false;
			}

			require_once($file);

			$filename = basename($file, '.php');

			$classname = 'SocialMaintenanceScript' . $filename;

			if (!class_exists($classname)) {
				$this->setError('Class not found: ' . $classname);
				$classnames[$file] = false;
				return false;
			}

			$classnames[$file] = $classname;
		}

		return $classnames[$file];
	}

	/**
	 * Wraooer function to execute the script
	 *
	 * @since  1.2
	 * @access public
	 * @param  String/SocialMaintenanceScript    $file The path of the script or the script object
	 * @return Boolean          State of the script execution result
	 */
	public function runScript($file)
	{
		$class = null;

		if (is_string($file)) {
			$classname = $this->getScriptClassName($file);

			if ($classname === false) {
				return false;
			}

			$class = new $classname;
		}

		if (is_object($file)) {
			$class = $file;
		}

		if (!$class instanceof SocialMaintenanceScript) {
			$this->setError('Class ' . $classname . ' is not instance of SocialMaintenanceScript');
			return false;
		}

		$state = true;

		// Clear the error
		$this->error = null;

		try {
			$state = $class->main();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		if (!$state) {
			if ($class->hasError()) {
				$this->setError($class->getError());
			}

			return false;
		}

		return true;
	}

	/**
	 * Get the script title
	 *
	 * @since  1.2
	 * @access public
	 * @param  String    $file The path of the script
	 * @return String          The title of the script
	 */
	public function getScriptTitle($file)
	{
		$classname = $this->getScriptClassName($file);

		if ($classname === false) {
			return false;
		}

		$vars = get_class_vars($classname);
		return JText::_($vars['title']);
	}

	/**
	 * Get the script description
	 *
	 * @since  1.2
	 * @access public
	 * @param  String    $file The path of the script
	 * @return String          The description of the script
	 */
	public function getScriptDescription($file)
	{
		$classname = $this->getScriptClassName($file);

		if ($classname === false) {
			return false;
		}

		$vars = get_class_vars($classname);
		return JText::_($vars['description']);
	}

	/**
	 * General set error function for the wrapper execute function
	 *
	 * @since  1.2
	 * @access public
	 * @param  String    $msg The error message
	 */
	public function setError($msg)
	{
		$this->error = $msg;
	}

	/**
	 * Checks if there are any error generated by executing the script
	 *
	 * @since  1.2
	 * @access public
	 * @return boolean   True if there is an error
	 */
	public function hasError()
	{
		return !empty($this->error);
	}

	/**
	 * General get error function that returns error set by executing the script
	 *
	 * @since  1.2
	 * @access public
	 * @return String    The error message
	 */
	public function getError()
	{
		return $this->error;
	}
}
