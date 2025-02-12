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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class SocialTextAvatar extends EasySocial
{
	private $font = null;
	private $storage = null;

	private $colors = array();
	private $fontColor = null;

	public function __construct()
	{
		parent::__construct();

		$this->storage = $this->getStoragePath(SOCIAL_TYPE_USER);
		$this->font = SOCIAL_MEDIA . '/fonts/opensans-regular.ttf';

		// Prepare the list of colors
		$this->colors = $this->getColors();
		$this->fontColor = trim(str_ireplace('#', '', $this->config->get('users.avatarFontColor')));
	}

	/**
	 * Determines if the initials file exists
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function exists($initials, $type = SOCIAL_TYPE_USER)
	{
		static $cache = array();

		// We do not want to stat too many times, we cache it on page load so we only check per initials once
		$index = $initials . $type;

		if (!isset($cache[$index])) {
			$path = $this->getFilePath($initials, $type);
			$exists = JFile::exists($path);

			if ($exists) {
				$cache[$index] = true;

				return true;
			}

			$cache[$index] = false;
		}

		return $cache[$index];
	}

	/**
	 * Generates the text based avatar
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function generate($initials, $type = SOCIAL_TYPE_USER)
	{
		$image = imagecreatetruecolor(SOCIAL_AVATAR_SQUARE_LARGE_WIDTH, SOCIAL_AVATAR_SQUARE_LARGE_WIDTH);

		$color = $this->getRandomColor($initials);
		$backgroundRgb = $this->getRGB($color);
		$backgroundColor = imagecolorallocate($image, $backgroundRgb['r'], $backgroundRgb['g'], $backgroundRgb['b']);

		$fontSize = 64;
		$fontColorRgb = $this->getRGB($this->fontColor);
		$fontColor = imagecolorallocate($image, $fontColorRgb['r'], $fontColorRgb['g'], $fontColorRgb['b']);

		$box = imagettfbbox($fontSize, 0, $this->font, $initials);

		$textWidth = $box[2] - $box[0];
		$textHeight = $box[7] - $box[1];

		$x = (SOCIAL_AVATAR_SQUARE_LARGE_WIDTH / 2) - ($textWidth / 2);
		$y = (SOCIAL_AVATAR_SQUARE_LARGE_WIDTH / 2) - ($textHeight / 2);

		imagefill($image, 0, 0, $backgroundColor);
		imagettftext($image, $fontSize, 0, $x, $y, $fontColor, $this->font, $initials);

		$file = $this->getFilePath($initials, $type);

		imagesavealpha($image, true);
		imagealphablending($image, false);

		// // For debugging only
		// header('Content-type: image/png');
		// imagepng($image);
		// exit;

		imagepng($image, $file, 9);

		// Free up resources
		imagedestroy($image);

		return true;
	}

	/**
	 * Generates the avatar
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getAvatar($name, $characters = 2, $type = SOCIAL_TYPE_USER)
	{
		static $cache = array();

		$initials = $this->getInitials($name, $characters);

		if (!isset($cache[$initials])) {
			$exists = $this->exists($initials);

			if (!$exists) {
				$state = $this->generate($initials, $type);
			}

			$cache[$initials] = $this->getFileUri($initials, $type);
		}

		return $cache[$initials];
	}

	/**
	 * Converts color code into RGB
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getRGB($code)
	{
		$rgb = array();
		$rgb['r'] = hexdec(substr($code, 0, 2));
		$rgb['g'] = hexdec(substr($code, 2, 2));
		$rgb['b'] = hexdec(substr($code, 4, 2));

		return $rgb;
	}

	/**
	 * Generate the unique file name for a particular initials
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getFileName($initials)
	{
		$file = md5($initials) . '.png';

		return $file;
	}

	/**
	 * Retrieves the file path for a specific initials
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getFilePath($initials, $type = SOCIAL_TYPE_USER)
	{
		$file = $this->getFileName($initials);
		$path = $this->storage . '/' . $file;

		return $path;
	}

	/**
	 * Retrieves the file uri for specific initials
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getFileUri($initials, $type = SOCIAL_TYPE_USER)
	{
		$fileUri = $this->getFileName($initials);
		$path = $this->getStorageUri($type) . '/' . $fileUri;

		return $path;
	}

	/**
	 * Retrieves the storage path
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getStoragePath($type)
	{
		// Get the avatars storage path.
		$avatarsPath = ES::cleanPath($this->config->get('avatars.storage.container'));

		// Let's construct the final path.
		$path = JPATH_ROOT . '/' . $avatarsPath . '/text/' . $type;

		if (!JFolder::exists($path)) {
			JFolder::create($path);
		}

		return $path;
	}

	/**
	 * Retrieves the storage uri
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getStorageUri($type)
	{
		// Get the avatars storage path.
		$avatarsPath = ES::cleanPath($this->config->get('avatars.storage.container'));

		// Let's construct the final path.
		$uri = rtrim(JURI::root(), '/') . '/' . $avatarsPath . '/text/' . $type;

		return $uri;
	}

	/**
	 * Initializes the colors available on the system
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getColors()
	{
		if ($this->colors) {
			return $this->colors;
		}

		$colors = $this->config->get('users.avatarColors');
		$colors = explode(',', $colors);

		foreach ($colors as &$color) {
			$color = trim(str_ireplace('#', '', $color));
		}

		return $colors;
	}

	/**
	 * Given a particular name, retrieve the initials
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getInitials($name, $characters = 2)
	{
		static $cache = array();

		$index = $name . $characters;

		if (!isset($cache[$index])) {

			$text = substr($name, 0, 1);
			$segments = explode(' ', $name);

			if (count($segments) >= $characters) {
				$tmp = array();
				$tmp[] = substr($segments[0], 0, 1);
				$tmp[] = substr($segments[count($segments) - 1], 0, 1);

				$text = implode('', $tmp);
			}

			$text = strtoupper($text);

			$isAscii = ES::string()->isAscii($text);

			// If the initials is not ascii, we generate other initials
			if (!$isAscii) {
				$name = strtoupper(preg_replace('/[0-9_\/]+/', '', base64_encode(sha1($name))));
				$text = ESJString::substr($name, 0, 1);
			}

			$cache[$index] = $text;
		}

		return $cache[$index];
	}

	/**
	 * Retrieves a random color
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getRandomColor($initials)
	{
		$count = rand(0, count($this->colors) - 1);
		$color = $this->colors[$count];

		return $color;
	}

	/**
	 * Purges the cache of stored avatars
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function purgeCache()
	{
		$path = $this->getStoragePath(SOCIAL_TYPE_USER);

		// Delete the cache
		$state = JFolder::delete($path);

		if (!$state) {
			return $state;
		}

		// Let it regenerate the folder again
		$this->getStoragePath(SOCIAL_TYPE_USER);

		return true;
	}
}
