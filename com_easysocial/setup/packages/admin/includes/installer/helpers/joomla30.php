<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once(__DIR__ . '/joomla.php');

class SocialInstallerHelperJoomla30 extends SocialInstallerJoomla
{
	/**
	 * Loads the installation file based on a given path.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The installation path to lookup for.
	 * @return	bool	True if loaded successfully, false otherwise.
	 *
	 */
	public function load($path)
	{
		// Test if the folder really exists.
		if (!JFolder::exists($path)) {
			$this->setError(JText::_('COM_EASYSOCIAL_INSTALLER_TEMPORARY_FOLDER_NOT_FOUND'));
			return false;
		}

		// Locate for the manifest file in the folder.
		$files = JFolder::files($path , '.xml' , self::RECURSIVE_SEARCH , self::RETRIEVE_FULL_PATH);

		// Set the source so the parent can manipulate it ?
		$this->source = $path;

		// If there's no .xml files, throw errors here.
		if (!$files || count( $files ) <= 0) {
			$this->setError(JText::_('COM_EASYSOCIAL_INSTALLER_MANIFEST_FILE_NOT_FOUND'));
			return false;
		}

		// Load through the list of manifest files to perform the installation.
		foreach ($files as $file) {

			$parser = ES::parser();
			$parser->load($file);

			// Set the app type.
			$this->type = (string) $parser->attributes()->type;

			// Set the app group.
			$this->group = (string) $parser->attributes()->group;

			if ($parser->getName() != self::XML_NAMESPACE || !in_array($this->type, $this->allowed)) {
				$this->setError('COM_EASYSOCIAL_INSTALLER_MANIFEST_IS_NOT_VALID_APPLICATION');
				continue;
			}

			// Set the path of the current xml file.
			$this->path = $file;

			// Retrieves the element
			$childs = $parser->children();
			$this->element = (string) $childs->element;
			$this->parser = $parser;

			return true;
		}
		
		return false;
	}
}
