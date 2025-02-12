<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableEmoticon extends SocialTable
{
	public $id = null;
	public $title = null;
	public $icon = null;
	public $state = null;
	public $created = null;
	public $type = null;

	public function __construct($db)
	{
		parent::__construct('#__social_emoticons', 'id', $db);
	}

	public function getIcon($escape = false)
	{
		$url = '<img class="emoji" src="' . JURI::root() . $this->icon . '" width="20" height="20">';

		if ($this->type == 'unicode') {
			$url = '<span class="es-emoji-unicode" data-es-emoji-unicode="' . $this->icon . '"></span>';
		}

		if ($escape) {
			$url = str_replace('"', '\"', $url);
		}

		return $url;
	}

	/**
	 * Add emoji
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function updateEmoji($emoji)
	{
		if (empty($emoji)) {
			return;
		}

		$this->icon = $emoji;
		$this->type = 'unicode';
		return $this->store();
	}

	/**
	 * Uploads a new icon for the emoticon
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function uploadIcon($file, $type)
	{
		if ($type == 'unicode') {
			return $this->updateEmoji($file);
		}

		jimport('joomla.filesystem.file');

		if (empty($file['tmp_name'])) {
			return;
		}

		$image = ES::image();
		$image->load($file['tmp_name']);

		$image->resize($image->getWidth(), 24);

		// Generate a file title
		$title = str_replace(" ", "_", $this->title);
		$fileName = $title . $image->getExtension();

		// Copy the file into the icon emoji folder
		$config = ES::config();
		$storage = JPATH_ROOT . '/' . $this->getIconStorage() . '/' . $fileName;

		if (JFile::exists($storage)) {
			JFile::delete($storage);
		}

		$state = $image->save($storage);

		if (!$state) {
			$this->setError('Error copying image file into ' . $storage);
			return false;
		}

		// the icon storage path do not store the first forward slash
		$this->icon = $this->getIconStorage() . '/' . $fileName;
		$this->type = 'image';

		return $this->store();
	}

	/**
	 * Determine if this is unicode icon
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function isUnicode()
	{
		return $this->type == 'unicode';
	}

	/**
	 * Retrieves the path to the icon storage
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getIconStorage()
	{
		$storage = 'media/com_easysocial/images/icons/emoji';
		$storage = rtrim($storage, '/');

		return $storage;
	}
}
