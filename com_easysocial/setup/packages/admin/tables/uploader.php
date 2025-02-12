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

class SocialTableUploader extends SocialTable
{
	public $id = null;
	public $path = null;
	public $name = null;
	public $mime = null;
	public $size = null;
	public $created = null;
	public $user_id = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_uploader' , 'id' , $db );
	}

	/**
	 * Uploads the file to the temporary location.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bindFile($file, $uploadTmp = true)
	{
		// Check for file name duplication. #4451
		$model = ES::model('uploader');
		$name = $model->generateFileName($file);

		$this->name = $name;
		$this->mime = ES::uploader()->normalizeFiletype($file);
		$this->size = $file['size'];

		// Generate a hash for the new file
		jimport('joomla.filesystem.file');

		$hash = JFile::makeSafe($this->name);
		$hash = JFile::stripExt($hash);
		$hash = md5(JFactory::getDate()->toSql() . $hash);

		// // We need to set an extension for this file
		// $extension = explode('.', $this->name);

		// if (count($extension) > 1) {
		// 	$hash .= '.' . $extension[1];
		// }

		// Upload the file now
		$model = ES::model('Uploader');
		$path = $model->upload($file, $hash, $this->user_id, $uploadTmp);

		if ($path === false) {
			$this->setError($model->getError());
			return false;
		}

		$this->path = $path;

		return true;
	}

	/**
	 * Retrieves the preview url
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getPermalink()
	{
		$config = ES::config();

		$url = rtrim(JURI::root(), '/');
		$url .= '/' . ES::cleanPath($config->get('uploader.storage.container'));
		$url .= '/' . $this->user_id;
		$url .= '/' . basename($this->path);

		return $url;
	}


	/**
	 * Retrieves the file name
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Override parent's delete behavior.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete( $pk = null )
	{
		// Delete the record from the database
		$state 	= parent::delete();

		if (!$state) {
			return false;
		}

		// Delete the temporary file.
		jimport('joomla.filesystem.file');

		$file = $this->path;
		$exists = JFile::exists($file);

		if (!$exists) {
			$this->setError('File does not exist on the site');
			return false;
		}

		$state = JFile::delete($file);

		if (!$state) {
			$this->setError('Unable to delete the phsyical file due to permission issues');
			return false;
		}

		return true;
	}
}
