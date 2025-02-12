<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelUploader extends EasySocialModel
{
	private $data = null;
	protected $pagination = null;

	public function __construct()
	{
		parent::__construct('uploader');
	}

	/**
	 * Uploads the given file to a temporary location on the site.
	 *
	 * @since	3.2.8
	 * @access	public
	 */
	public function upload($file, $hash, $userId, $uploadTmp = true)
	{
		// Check if file exists on the server
		if (!isset($file['tmp_name']) || empty($file)) {
			$this->setError(JText::_('COM_EASYSOCIAL_UPLOADER_FILE_NOT_FOUND'));
			return false;
		}

		// Lets figure out the storage path.
		$config = ES::config();

		// Test if the folder exists for this upload type.
		$path = JPATH_ROOT . '/' . ES::cleanPath($config->get('uploader.storage.container'));

		if (!ES::makeFolder($path)) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER', $path));
			return false;
		}

		// Let's finalize the storage path.
		$storage = $path . '/' . $userId;

		if (!ES::makeFolder($storage)) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_CREATE_DESTINATION_FOLDER', $storage));
			return false;
		}

		// Once the script reaches here, we assume everything is good now.
		// Copy the files over.
		jimport('joomla.filesystem.file');

		$absolutePath = $storage . '/' . $hash;
		$state = true;

		if ($uploadTmp) {
			$state = JFile::copy($file['tmp_name'], $absolutePath);
		}

		if (!$state) {
			$this->setError(JText::sprintf('COM_EASYSOCIAL_UPLOADER_UNABLE_TO_COPY_TO_DESTINATION_FOLDER', $absolutePath));
			return false;
		}

		return $absolutePath;
	}

	/**
	 * Generate unique name for the file
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function generateFileName($file)
	{
		$name = ES::uploader()->normalizeFilename($file);

		while ($this->isFileNameExists($name)) {
			$name = ES::uploader()->normalizeFilename($file, md5(ES::date()->toSql() . rand(1, 1000)));
		}

		return $name;
	}

	/**
	 * Determines if the name exists
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isFileNameExists($name)
	{
		$db = ES::db();

		$query = "SELECT COUNT(1) as `total`";
		$query .= " FROM `#__social_uploader`";
		$query .= " WHERE `name` = " . $db->Quote($name);

		$db->setQuery($query);

		$count = $db->loadResult();
		$exists	= $count >= 1 ? true : false;

		return $exists;
	}
}
