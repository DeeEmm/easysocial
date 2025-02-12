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

class EasySocialControllerInstallationDownload extends EasySocialSetupController
{
	/**
	 * Downloads the file from the server
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$info = $this->getInfo();

		if (!$info) {
			$result = new stdClass();
			$result->state = false;
			$result->message = JText::_('COM_EASYSOCIAL_INSTALLATION_ERROR_REQUEST_INFO');

			$this->output($result);
			exit;
		}

		// If our server returns any error messages other than the standard ones
		if (isset($info->error) && $info->error != 408) {
			$result = new stdClass();
			$result->state = false;
			$result->message = $info->error;

			$this->output($result);
			exit;
		}

		// If it hits any error from the server, skip this
		if (isset($info->error) && $info->error == 408) {
			$result = new stdClass();
			$result->state = false;
			$result->message = $info->message;

			$this->output($result);
			exit;
		}

		// Download the component installer.
		$storage = $this->getDownloadFile($info);

		// This only happens when there is no result returned from the server
		if ($storage === false) {
			$result = new stdClass();
			$result->state = false;
			$result->message = 'There was some errors when downloading the file from the server.';

			$this->output($result);
			exit;
		}

		// Extract files here.
		$tmp = SI_TMP . '/com_easysocial_v' . $info->version;

		if (JFolder::exists($tmp)) {
			JFolder::delete($tmp);
		}

		// Try to extract the files
		$state = $this->extractArchive($storage, $tmp);

		// If there is an error extracting the zip file, then there is a possibility that the server returned a json string
		if (!$state) {

			$contents = file_get_contents($storage);
			$result = json_decode($contents);

			if (is_object($result)) {
				$result->state = false;
				$this->output($result);
				exit;
			}

			$result = new stdClass();
			$result->state = false;
			$result->message = 'There was some errors when extracting the archive from the server. If the problem still persists, please contact our support team.<br /><br /><a href="https://stackideas.com/forums" class="btn btn-default" target="_blank">Contact Support</a>';

			$this->output($result);
			exit;
		}


		// Get the md5 hash of the stored file
		$hash = md5_file($storage);

		// Check if the md5 check sum matches the one provided from the server.
		if (!in_array($hash, $info->md5)) {
			$result = new stdClass();
			$result->state = false;
			$result->message = 'The MD5 hash of the downloaded file does not match. Please contact our support team to look into this.<br /><br /><a href="https://stackideas.com/forums" class="btn btn-default" target="_blank">Contact Support</a>';

			$this->output($result);
			exit;
		}

		// After installation is completed, cleanup all zip files from the site
		$this->cleanupZipFiles(dirname($storage));

		$result = new stdClass();
		$result->message = 'Installation file downloaded successfully';
		$result->state = $state;
		$result->path = $tmp;

		$this->output($result);
	}

	/**
	 * Downloads the installation files from our installation API
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getDownloadFile($info)
	{
		$ch = curl_init(SI_DOWNLOADER);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . SI_KEY . '&version=' . $info->version);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 35000);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$contents = curl_exec($ch);
		curl_close($ch);

		// Set the storage page
		$storage = SI_PACKAGES . '/easysocial_v' . $info->version . '_component.zip';

		// Delete zip archive if it already exists.
		if (JFile::exists($storage)) {
			JFile::delete($storage);
		}

		$state = JFile::write($storage, $contents);

		if (!$state || !$contents) {
			return false;
		}

		return $storage;
	}
}
