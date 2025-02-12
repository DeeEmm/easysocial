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

require_once(__DIR__ . '/dependencies.php');
require_once(__DIR__ . '/types/abstract.php');

jimport('joomla.filesystem.archive');

class SocialGdpr extends EasySocial
{
	static private $sections = array();

	/**
	 * Allows caller to create new sections in the downloads
	 *
	 * @since	2.2
	 * @access	public
	 */
	public static function createSection(SocialUser $user, $name, $title = '', $subfolder = false)
	{
		if (!isset(self::$sections[$name])) {
			$section = new SocialGdprSection($user, $name, $title, $subfolder);

			self::$sections[$name] = $section;
		}

		return self::$sections[$name];
	}

	/**
	 * Creates a zip archive of a folder
	 *
	 * @since	2.2
	 * @access	private
	 */
	private function createZipFile($sourceFolder, $zipFile)
	{
		// Check if existing zip exists
		$exists = JFile::exists($zipFile);

		if ($exists) {
			JFile::delete($zipFile);
		}

		// get all files from
		$files = JFolder::files($sourceFolder, '', true, true);
		$data = array();

		if ($files) {
			foreach ($files as $file) {
				$file = ES::normalizeSeparator($file);
				$filename = str_replace($sourceFolder, '', $file);
				$filename = ltrim($filename, '/');

				$tmp = array();
				$tmp['name'] = $filename;
				$tmp['data'] = file_get_contents($file);
				$tmp['time'] = filemtime($file);
				$data[] = $tmp;
			}
		}

		$zip = ESArchive::getAdapter('zip');
		$state = $zip->create($zipFile, $data);

		if ($state) {

			// now delete from the tmp folder
			JFolder::delete($sourceFolder);

			return $zipFile;
		}

		return false;
	}

	/**
	 * Invoked by cronjobs
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function cron()
	{
		if (!$this->config->get('users.download.enabled')) {
			return ES::response(JText::_('COM_ES_GDPR_DOWNLOAD_DISABLED'), SOCIAL_MSG_INFO);
		}

		@ini_set('max_execution_time', 600);
		@ini_set('memory_limit', '1024M');

		// get records from request table.
		$model = ES::model('download');
		$items = $model->getCronDownloadReq();

		if (!$items) {
			return ES::response(JText::_('COM_ES_GDPR_CRON_NO_RECORDS'), SOCIAL_MSG_INFO);
		}

		$processed = 0;

		foreach ($items as $item) {

			$tbl = ES::table('download');
			$tbl->bind($item);

			// lock this request 1st.
			$tbl->updateState(ES_DOWNLOAD_REQ_LOCKED);

			// Retrieve the params
			$params = ES::registry($tbl->params);

			// check if this user is valid or not.
			$user = ES::user($tbl->userid);

			if ($user->id) {
				$params = $this->process($user, $params);

				// this mean the process require next cycle to continue to to large data. lets mark this request as process
				if (!$params->get('complete')) {
					$tbl->updateState(ES_DOWNLOAD_REQ_PROCESS);
				} else {

					// update state to ready
					$tbl->setFilePath($params->get('path'));
					$tbl->updateState(ES_DOWNLOAD_REQ_READY);

					// prepare email and send notification to user.
					$tbl->sendNotification();

					$processed++;
				}
			}

			$tbl->params = $params->toString();
			$tbl->store();
		}

		$msg = JText::_('User data download requests processed and an email notification has sent to user.');

		if ($tbl->state != ES_DOWNLOAD_REQ_READY) {
			$msg = JText::_('User data download is currently queued for next processing.');
		}

		return ES::response($msg, SOCIAL_MSG_INFO);
	}

	/**
	 * Processes user data for download of data
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function process(SocialUser $user, $params)
	{
		$items = $this->getAvailableAdapters();

		$data = array();

		// users sections
		$section = self::createSection($user, 'user', JText::_('COM_ES_GDPR_YOUR_INFORMATION'), false);
		$states = array();

		foreach ($items as $type) {

			$adapter = $this->getAdapter($type, $user, $params);
			$adapter->execute($section);

			// Determine if the process is completed
			$states[] = $adapter->getParams('complete');
		}

		// Process 3rd party apps if they need to insert data
		$dispatcher = ES::dispatcher();
		$appStates = $dispatcher->trigger(SOCIAL_TYPE_USER, 'onExportGdpr', array(&$section, &$params));

		// Since the dispatcher returns true / false regardless if the app has the trigger or not. We need to rely on the real return value 0 or 1
		if ($appStates) {
			foreach ($appStates as $appState) {
				if ($appState === 1) {
					$states[] = true;
				}

				if ($appState === 0) {
					$states[] = false;
				}
			}
		}

		// Build html files
		$sections = self::getSections();

		// Load front end's language file.
		ES::language()->loadSite();

		foreach ($sections as $section) {

			// Process section
			$this->processSection($user, $params, $section);

			foreach ($section->tabs as $tab) {
				$this->processTabs($user, $params, $tab);
			}
		}

		// All adapters marked as completed
		$complete = false;
		$zip = '';

		if (!in_array(false, $states)) {
			$complete = true;

			// Zip archive
			$folder = self::getUserTempPath($user);
			$zip = $folder . '.zip';

			$this->createZipFile($folder, $zip);
		}

		$params->set('complete', $complete);
		$params->set('path', $zip);

		return $params;
	}

	/**
	 * Process sections
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function processSection(SocialUser $user, $params, $section)
	{
		$hasIndexFile = $section->hasIndexFile();

		if (!$hasIndexFile) {
			$section->createIndexFile($this->getSidebarContents(true));
		}
	}

	/**
	 * Process tabs
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function processTabs(SocialUser $user, $params, $tab)
	{
		$items = $tab->getItems();

		if ($items) {

			$tabContents = '';

			foreach ($items as $item) {

				// Insert contents into the temporary file
				$tabContents .= $item->getListingContent($tab);

				// Create the view file
				if ($item->hasView()) {
					$item->createViewFile($tab);
				}

				// Add the id into the tab so that it doesn't get processed again
				$tab->markItemProcessed($item);
			}

			// Create the listing file contents
			$listingFilePath = $tab->getTemporaryListingFileName();
			JFile::append($listingFilePath, $tabContents);
		}


		// Check if it is already finalized
		$finalized = $tab->isFinalized();
		$hasIndexFile = $tab->hasIndexFile();

		if ($finalized && !$hasIndexFile) {
			$tab->createIndexFile($this->getSidebarContents(false, $tab->key));
		}

		return;
	}

	/**
	 * Creates a new adapter
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getAdapter($type, Socialuser $user, $params)
	{
		$this->loadAdapter($type);

		$className = 'SocialGdpr' . ucfirst($type);
		$adapter = new $className($user, $params);

		return $adapter;
	}

	/**
	 * Retrieves a list of built-in adapters available
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getAvailableAdapters()
	{
		static $adapters = null;

		// return array('post','discussion');

		if (is_null($adapters)) {
			$files = JFolder::files(__DIR__ . '/types', '.php$', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'abstract.php'));

			foreach ($files as $file) {
				$adapters[] = str_ireplace('.php', '', $file);
			}
		}

		return $adapters;
	}

	/**
	 * Generates the contents for the sidebar
	 *
	 * @since	2.2
	 * @access	public
	 */
	public static function getSidebarContents($isRoot = false, $active = '')
	{
		static $sidebars = array();

		$key = $isRoot ? 1 : 0;
		$key .= $active;

		if (!isset($sidebars[$key])) {
			$sections = self::getSections();

			$theme = ES::themes();
			$theme->set('isRoot', $isRoot);
			$theme->set('sections', $sections);
			$theme->set('active', $active);

			$sidebars[$key] = $theme->output('site/gdpr/sidebar');
		}

		return $sidebars[$key];
	}

	/**
	 * Creates a temporary folder for a given user
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public static function getUserTempPath(SocialUser $user)
	{
		static $paths = array();

		if (!isset($paths[$user->id])) {
			$paths[$user->id] = SOCIAL_GDPR_DOWNLOADS . '/' . md5($user->id . $user->password . $user->email);
			$paths[$user->id] = ES::normalizeSeparator($paths[$user->id]);

			ES::makeFolder($paths[$user->id], false);
		}

		return $paths[$user->id];
	}

	/**
	 * Retrieves the list of sections available
	 *
	 * @since	2.2
	 * @access	public
	 */
	public static function getSections()
	{
		return self::$sections;
	}

	/**
	 * Loads an adapter
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function loadAdapter($type)
	{
		$file = __DIR__ . '/types/' . $type . '.php';
		require_once($file);
	}

	/**
	 * Purge expired download requests
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function purgeExpired($max = 10)
	{
		$model = ES::model('download');
		$items = $model->getExpiredRequest($max);

		if ($items) {
			foreach ($items as $item) {
				$tbl = ES::table('download');
				$tbl->bind($item);

				$tbl->delete();
			}
		}

		return true;
	}

}
