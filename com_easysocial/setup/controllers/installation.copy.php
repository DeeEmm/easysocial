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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationCopy extends EasySocialSetupController
{
	/**
	 * Copies files into the necessary locations
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$type = $this->input->get('type', '', 'string');

		// Get the temporary path from the server.
		$tmpPath = $this->input->get('path', '', 'default');

		// Get the path to the zip file
		$archivePath = $tmpPath . '/' . $type . '.zip';

		// Where the extracted items should reside
		$path = $tmpPath . '/' . $type;

		// Extract the admin folder
		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			$result = new stdClass();
			$result->state = false;
			$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_COPY_ERROR_UNABLE_EXTRACT', $type);

			return $this->output($result);
		}

		// Look for files in this path
		$files = JFolder::files($path, '.', false, true);

		// Look for folders in this path
		$folders = JFolder::folders($path, '.', false, true);

		// Construct the target path first.
		if ($type == 'admin') {
			$target = JPATH_ADMINISTRATOR . '/components/com_easysocial';

			// Maintenance task to delete the defaults folder /administrator/components/com_easysocial/defaults so that
			// the folder gets refreshed during installation
			$defaultsFolder = $target . '/defaults';

			if (JFolder::exists($defaultsFolder)) {
				JFolder::delete($defaultsFolder);
			}
		}

		if ($type == 'site') {
			$target = JPATH_ROOT . '/components/com_easysocial';
		}

		// Languages
		if ($type == 'languages') {

			// Admin language files
			$adminPath = JPATH_ADMINISTRATOR . '/language/en-GB';
			$adminSource = $path . '/admin/en-GB.com_easysocial.ini';
			$adminSysSource	= $path . '/admin/en-GB.com_easysocial.sys.ini';

			JFile::copy($adminSource, $adminPath . '/en-GB.com_easysocial.ini');
			JFile::copy($adminSysSource, $adminPath . '/en-GB.com_easysocial.sys.ini');

			// Site language files
			$sitePath = JPATH_ROOT . '/language/en-GB';
			$siteSource = $path . '/site/en-GB.com_easysocial.ini';

			JFile::copy($siteSource, $sitePath . '/en-GB.com_easysocial.ini');

			$result = new stdClass();
			$result->state = true;
			$result->message = JText::_('COM_EASYSOCIAL_INSTALLATION_LANGUAGES_UPDATED');

			return $this->output($result);
		}

		if ($type == 'media') {
			$target = JPATH_ROOT . '/media/com_easysocial';
		}

		// Ensure that the target folder exists
		if (!JFolder::exists($target)) {
			JFolder::create($target);
		}

		// Scan for files in the folder
		$totalFiles = 0;

		foreach ($files as $file) {

			$name = basename($file);
			$targetFile	= $target . '/' . $name;

			// For site's cron.php and crondata.php, we need to ensure that we do not replace it.
			if ($type == 'site' && ($name == 'cron.php' || $name == 'crondata.php')) {

				// Check if the targets exists
				if (JFile::exists($targetFile)) {
					continue;
				}

			}

			JFile::copy($file, $targetFile);

			$totalFiles += 1;
		}

		// Scan for folders in this folder
		$totalFolders = 0;

		foreach ($folders as $folder) {

			$name = basename($folder);
			$targetFolder = $target . '/' . $name;

			// Try to copy the folder over
			JFolder::copy($folder, $targetFolder, '', true);

			$totalFolders += 1;
		}

		$result = new stdClass();
		$result->state = true;
		$result->message = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_COPY_FILES_SUCCESS', $totalFiles, $totalFolders);

		return $this->output($result);
	}
}
