<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialLogin extends EasySocial
{
	public $hasOverride = false;
	public $extension = 'png';

	public function __construct()
	{
		if ($this->getOverrideImage()) {
			$this->hasOverride = true;
		}
	}

	/**
	 * Generates a login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLoginImage($css = false, $override = false)
	{
		$config = ES::config();

		// Default login image
		$image = $this->getDefaultImage();

		if ($config->get('login.custom.image') || $override) {

			// Check for the override image
			$overrideImage = $this->getOverrideImage();

			// If the image is exist, let's use it.
			if ($overrideImage) {
				$image = $overrideImage;
			}
		}

		// css background image output format
		if ($css) {
			$image = "background-image: url('" . $image . "');";
		}

		return $image;
	}

	/**
	 * Generate a default login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getDefaultImage()
	{
		$image = 'https://s3.amazonaws.com/es.assets/default_1.jpg';

		return $image;
	}

	/**
	 * Generate an override login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOverrideImage()
	{
		$image = false;

		$overridePath = '/images/easysocial_login/login_background.' . $this->extension;
		$templatePath = JPATH_ROOT . $overridePath;
		$exists = JFile::exists($templatePath);

		if ($exists) {
			$image = rtrim(JURI::root(), '/') . $overridePath;
		}

		return $image;
	}

	/**
	 * Method to check if override login image is exist
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasLoginImage()
	{
		return $this->hasOverride;
	}

	/**
	 * Process uploaded image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function uploadImage($file)
	{
		$config = ES::config();

		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			$this->setError(JText::_('COM_EASYSOCIAL_LOGIN_IMAGE_IS_NOT_AVAILABLE'));
			return false;
		}

		$source = $file['tmp_name'];
		$fileName = 'login_background.' . $this->extension;
		$overridePath = JPATH_ROOT . '/images/easysocial_login/' . $fileName;

		// Try to upload the image
		$state = JFile::upload($source, $overridePath);

		if (!$state) {
			$this->setError(JText::_('COM_EASYSOCIAL_UPLOAD_LOGIN_IMAGE_ERROR'));
			return false;
		}

		return true;
	}

	/**
	 * Method to delete background login image
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteImage()
	{
		$overridePath = '/images/easysocial_login/login_background.' . $this->extension;
		$templatePath = JPATH_ROOT . $overridePath;

		return JFile::delete($templatePath);
	}
}