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

class EasySocialControllerInstallationExtract extends EasySocialSetupController
{
	/**
	 * Full package installation
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$storage = SI_PACKAGES . '/' . SI_PACKAGE;

		$exists = JFile::exists($storage);

		// Test if package really exists
		if (!$exists) {
			$result = new stdClass();
			$result->state = false;
			$result->message = 'The component package does not exist on the site.<br />Please contact our support team to look into this.';

			$this->output($result);
			exit;
		}

		// Get the folder name
		$folderName = basename($storage);
		$folderName = str_ireplace('.zip', '', $folderName);

		// Extract files here.
		$tmp = SI_TMP . '/' . $folderName;

		// Ensure that there is no such folders exists on the site
		if (JFolder::exists($tmp)) {
			JFolder::delete($tmp);
		}

		// Try to extract the files
		$state = $this->extractArchive($storage, $tmp);

		// Regardless of the extraction state, delete the zip file otherwise anyone can download the zip file.
		@JFile::delete($storage);

		if (!$state) {
			$result = new stdClass();
			$result->state = false;
			$result->message = 'There was some errors when extracting the zip file';

			$this->output($result);
			exit;
		}

		$result = new stdClass();

		$result->message = 'Installation archive extracted successfully';
		$result->state = $state;
		$result->path = $tmp;

		$this->output($result);
	}
}
