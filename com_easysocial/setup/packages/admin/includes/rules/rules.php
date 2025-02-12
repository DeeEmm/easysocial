<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRules extends EasySocial
{
	/**
	 * Allows caller to perform installation of rule files on the site
	 *
	 * @since	1.4.9
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function upload($file, $type, $allowedExtensions = array())
	{
		// Default we should extract if it is zip file
		$extract = true;
		
		// Check for request forgeries
		ES::checkToken();

		// Ensure that the file is allowed
		if (!isset($file['tmp_name']) || !$file['tmp_name']) {

			$this->setError(JText::_('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE'));

			return false;
		}

		// Check for invalid file types
		if ($file['type'] !== 'application/octet-stream') {

			$this->setError(JText::_('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE'));
			return false;
		}

		// Get info about the file
		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

		// If caller did not provide us with the allowed extensions, we use the type as default
		if (!$allowedExtensions) {
			$allowedExtensions = array($type);
		}

		// We need to ensure that the extension is allowed
		if (!in_array($extension, $allowedExtensions)) {
			$this->setError(JText::_('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_INVALID_TYPE'));
			return false;
		}

		$files = array();

		if (in_array($extension, $allowedExtensions) && ($extension !== 'zip' || !$extract)) {
			$files[] = $file['tmp_name'];
		}

		$tmpPath = null;

		// If this is a zip, we need to extract it.
		if ($extension === 'zip') {

			jimport('joomla.filesystem.archive');

			$key = md5(uniqid() . $file['tmp_name']);

			// Create a temporary folder
			$tmpPath = SOCIAL_TMP . '/' . $key;
			$state = ES::makeFolder($tmpPath);

			if (!$state) {
				$this->setError(JText::_('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_UNABLE_TO_CREATE_TMP_FOLDER'));
				return false;
			}

			// Try to extract the file now
			$state = ESArchive::extract($file['tmp_name'], $tmpPath);

			if (!$state) {
				$this->setError(JText::_('COM_EASYSOCIAL_INSTALL_UPLOAD_ERROR_UNABLE_TO_EXTRACT_PACKAGE'));
				return false;
			}

			// Get a list of files from the extracted folder
			$scanExtensions = array_diff($allowedExtensions, array('zip'));

			foreach ($scanExtensions as $scanExtension) {
				$files = array_merge($files, JFolder::files($tmpPath, '.' . $scanExtension . '$', true, true));
			}
		}

		// Load up the model
		$model = ES::model($type);

		// Now we need to install each of the files provided
		foreach ($files as $file) {

			$state = $model->install($file);

			if (!$state) {
				$this->setError($model->getError());
				return false;
			}
		}

		// We need to delete the temporary folder once it is installed
		if (!empty($tmpPath)) {
			JFolder::delete($tmpPath);
		}

		$message = JText::_('COM_EASYSOCIAL_INSTALL_UPLOAD_SUCCESSFULLY');
		
		return $message;
	}
}
