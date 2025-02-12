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

jimport('joomla.filesystem.file');

class SocialTableConfig extends SocialTable
{
	public $type = null;
	public $value = null;
	public $value_binary = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_config', 'type', $db);
	}

	public function store($updateNulls = false)
	{
		$db = ES::db();
		$query  = 'SELECT COUNT(1) FROM ' . $db->nameQuote( $this->_tbl ) . ' '
				. 'WHERE ' . $db->nameQuote( $this->_tbl_key ) . '=' . $db->Quote( $this->{$this->_tbl_key} );
		$db->setQuery( $query );

		$exist  = (bool) $db->loadResult();

		if (!$this->type) {
			$this->type = '';
		}

		if (!$exist) {
			return $db->insertObject( $this->_tbl , $this , $this->_tbl_key );
		}

		return $db->updateObject( $this->_tbl , $this , $this->_tbl_key );
	}

	/**
	 * Updates the default avatar
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateDefaultAvatar($file, $group)
	{
		$sizes = array('large' => '180', 'medium' => '64', 'small' => '32', 'square' => '180');

		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];

		$tmp = JPATH_ROOT . '/images/easysocial_override/' . $group . '/tmp.png';

		// Try to upload the image
		$state = JFile::upload($source, $tmp);

		if (!$state) {
			return false;
		}

		// Resize the image file
		foreach ($sizes as $size => $pixel) {

			$image = ES::image();
			$image->load($tmp);
			$image->resize($pixel, $pixel);

			$path = JPATH_ROOT . '/images/easysocial_override/' . $group . '/avatar/' . $size . '.png';

			if (JFile::exists($path)) {
				JFile::delete($path);
			}

			$image->save($path);
		}

		JFile::delete($tmp);

		return true;
	}

	/**
	 * Updates the default cover
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateDefaultCover($file, $group)
	{

		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];

		$tmp = JPATH_ROOT . '/images/easysocial_override/' . $group . '/tmp.jpg';

		// Try to upload the image
		$state = JFile::upload($source, $tmp);

		if (!$state) {
			return false;
		}

		$image = ES::image();
		$image->load($tmp);
		$image->resize(940);

		$path = JPATH_ROOT . '/images/easysocial_override/' . $group . '/cover/default.jpg';

		if (JFile::exists($path)) {
			JFile::delete($path);
		}

		$state = $image->save($path);

		JFile::delete($tmp);

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Updates the logo
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateLogo($file)
	{
		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];
		$overridePath = JPATH_ROOT . '/images/easysocial_override/email_logo.png';

		// Try to upload the image
		$state = JFile::upload($source, $overridePath);

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Updates Apple keyfile
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function updateAppleKeyfile($file)
	{
		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];
		$filePath = JPATH_ROOT . '/media/com_easysocial/tmp/' . $file['name'];

		// Try to upload the image
		$state = JFile::upload($source, $filePath);

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Updates the logo
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function updateSharerLogo($file)
	{
		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];
		$overridePath = JPATH_ROOT . '/images/easysocial_override/sharer_logo.png';

		// Try to upload the image
		$state = JFile::upload($source, $overridePath);

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Updates the mobile shortcut icon
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateIcon($file)
	{
		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];
		$overridePath = JPATH_ROOT . '/images/easysocial_override/mobile_icon.png';

		// Try to upload the image
		$state = JFile::upload($source, $overridePath);

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Update video logo
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateVideoLogo($file)
	{
		$source = $file['tmp_name'];

		$tmpPath = JPATH_ROOT . '/images/easysocial_override/tmp.png';

		// Try to upload the image
		$state = JFile::upload($source, $tmpPath);

		if (!$state) {
			return false;
		}

		// Resize the image file
		$image = ES::image();
		$image->load($tmpPath);
		$image->resize(24, 24);

		$overridePath = JPATH_ROOT . '/images/easysocial_override/video_logo.png';

		if (JFile::exists($overridePath)) {
			JFile::delete($overridePath);
		}

		$image->save($overridePath);

		JFile::delete($tmpPath);

		return true;
	}

	/**
	 * Update video watermark
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateVideoWatermark($file)
	{
		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			return false;
		}

		$source = $file['tmp_name'];

		$tmp = JPATH_ROOT . '/images/easysocial_override/video_watermark.png';

		// Try to upload the image
		$state = JFile::upload($source, $tmp);

		if (!$state) {
			return false;
		}

		return true;
	}


	/**
	 * Determine if the upload image the dimension meet the requirement or not
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function checkUploadImageDimension($file, $width, $height, $mode = 'maximum')
	{
		// for now the mode is only one which is maximum. The image dimention cannot exceed the specified dimension.

		// Load the image object
		$image = ES::image();
		$image->load($file['tmp_name'], $file['name']);

		$imageWidth = $image->getWidth();
		$imageHeight = $image->getHeight();

		if ($imageWidth > $width ||$imageHeight > $height) {
			return false;
		}

		return true;
	}
}
