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

ES::import('admin:/tables/table');

class SocialTableAdvertiser extends SocialTable
{
	public $id = null;
	public $name = null;
	public $logo = null;
	public $state = null;
	public $created = null;
	public $user_id = null;

	public function __construct($db)
	{
		parent::__construct('#__social_advertisers', 'id', $db);
	}

	/**
	 * Override the implementation of delete as we also need to delete the cover
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if (!$state) {
			return $state;
		}

		// If deletion was successful, we need to delete the cover file
		$storagePath = JPATH_ROOT . '/' . ltrim($this->getLogoStorage(), '/');

		$exists = JFolder::exists($storagePath);

		if ($exists) {
			JFolder::delete($storagePath);
		}

		return $state;
	}

	/**
	 * Uploads a logo for advertiser
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function uploadLogo($file)
	{
		jimport('joomla.filesystem.file');

		if (!isset($file['tmp_name']) || (isset($file['error']) && $file['error'] != 0)) {
			$this->setError('COM_ES_ADS_UPLOADED_FILE_ERROR');
			return false;
		}

		$image = ES::image();
		$image->load($file['tmp_name']);

		// If a file previously exist, delete it first
		$existingLogo = null;

		if ($this->logo) {
			$existingLogo = JPATH_ROOT . '/' . ltrim($this->getLogoStorage(false), '/') . '/' . $this->logo;
		}

		// Generate a file title
		$fileName = md5($this->id . JFactory::getDate()->toSql()) . $image->getExtension();

		// Copy the file into the icon emoji folder
		$config = ES::config();
		$storage = JPATH_ROOT . $this->getLogoStorage();

		if (!JFolder::exists($storage)) {
			JFolder::create($storage);
		}

		$state = JFile::copy($file['tmp_name'], $storage . '/' . $fileName);

		if (!$state) {
			$this->setError('Error copying image file into ' . $storage);
			return false;
		}

		$this->logo = $this->id . '/' . $fileName;

		if ($existingLogo && file_exists($existingLogo)) {
			JFile::delete($existingLogo);
		}

		return $this->store();
	}

	/**
	 * Retrieves the path to the logo storage
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getLogoStorage($withId = true)
	{
		$config = ES::config();
		$storage = $config->get('ads.storage') . '/logos';

		if ($withId) {
			$storage .= '/' . $this->id;
		}

		$storage = rtrim($storage, '/');

		return $storage;
	}

	/**
	 * Retrieve company logo
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getLogo()
	{
		$default = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/defaults/advertisement/logo.png';

		if (!$this->logo) {
			return $default;
		}

		$storage = $this->getLogoStorage(false);

		$url = rtrim(JURI::root(), '/') . $storage . '/' . $this->logo;

		return $url;
	}
}
