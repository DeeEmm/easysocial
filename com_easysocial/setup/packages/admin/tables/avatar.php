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

class SocialTableAvatar extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $type = null;
	public $avatar_id = 0;
	public $photo_id = 0;
	public $small = '';
	public $medium = '';
	public $square = '';
	public $large = '';
	public $modified = '0000-00-00 00:00:00';
	public $storage = 'joomla';

	public function __construct($db)
	{
		parent::__construct('#__social_avatars', 'id', $db);
	}

	/**
	 * Responsible to store the uploaded images.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function upload($file)
	{
		// Get config object.
		$config	= ES::config();

		// Do not proceed if image doesn't exist.
		if (empty($file) || !isset($file['tmp_name'])) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATARS_FILE_UNAVAILABLE'));
			return false;
		}

		// Get the default avatars storage location.
		$avatarsPath = JPATH_ROOT . '/' . ES::cleanPath($config->get('avatars.storage.container'));

		// Test if the avatars path folder exists. If it doesn't we need to create it.
		if (!ES::makeFolder($avatarsPath)) {
			$this->setError(JText::_('Errors when creating default container for avatar'));
			return false;
		}

		// Get the default avatars storage location for this type.
		$typePath = $config->get('avatars.storage.' . $this->type);

		if (!$typePath) {
			throw new Exception('Invalid avatar storage path');
		}

		$storagePath = $avatarsPath . '/' . ES::cleanPath($typePath);

		// Ensure storage path exists.
		if (!ES::makeFolder($storagePath)) {
			$this->setError(JText::_('Errors when creating path for avatar'));
			return false;
		}

		// Get the profile id and construct the final path.
		$idPath = ES::cleanPath($this->uid);
		$storagePath = $storagePath . '/' . $idPath;

		// Ensure storage path exists.
		if (!ES::makeFolder($storagePath)) {
			$this->setError(JText::_('Errors when creating default path for avatar'));
			return false;
		}

		// Get the image library to perform some checks.
		$image = ES::image();
		$image->load($file['tmp_name']);

		// Test if the image is really a valid image.
		if (!$image->isValid()) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATARS_FILE_NOT_IMAGE'));
			return false;
		}

		// Process avatar storage.
		$avatar = ES::avatar($image);

		// Let's create the avatar.
		$sizes = $avatar->create($storagePath);

		if ($sizes === false) {
			$this->setError(JText::_('Sorry, there was some errors when creating the avatars.'));
			return false;
		}

		// Delete previous files.
		$this->deleteFile($storagePath);

		// Assign the values back.
		foreach ($sizes as $size => $url) {
			$this->$size = $url;
		}

		return true;
	}

	/**
	 * Override parent's behavior of deleting as we also need to delete physical files.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete();

		if (!$state) {
			return false;
		}

		// Get config
		$config = ES::config();

		// Get the default avatars storage location.
		$avatarsPath = JPATH_ROOT . '/' . ES::cleanPath($config->get('avatars.storage.container'));
		$relativePath = '/' . ltrim(ES::cleanPath($config->get('avatars.storage.container')), '/');

		// Test if the avatars path folder exists. If it doesn't we need to create it.
		if (!ES::makeFolder($avatarsPath)) {
			$this->setError('Errors when creating default container for avatar');
			return false;
		}

		// Get the default avatars storage location for this type.
		$typePath = ES::cleanPath($config->get('avatars.storage.' . $this->type));
		$storagePath = $avatarsPath . '/' . $typePath;
		$relativePath = $relativePath . '/' . $typePath;

		// Set the absolute path based on the uid.
		$storagePath = $storagePath . '/' . $this->uid;
		$relativePath = $relativePath . '/' . $this->uid;

		$this->deleteFolder($storagePath, $relativePath);

		return $state;
	}

	/**
	 * Deletes the current variation of avatars given the absolute path to an item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteFolder($path, $relativePath = '')
	{
		$state = false;

		if ($this->storage == SOCIAL_STORAGE_JOOMLA) {
			jimport('joomla.filesystem.folder');

			if (!JFolder::exists($path)) {
				return false;
			}

			$state = JFolder::delete($path);
		} else {

			if ($relativePath) {
				$storage = ES::storage($this->storage);
				$state = $storage->delete($relativePath, true);
			}
		}

		return $state;
	}

	/**
	 * Deletes the current variation of avatars given the absolute path to an item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteFile($storagePath)
	{
		jimport('joomla.filesystem.file');

		// need to delete the file from the Amazon S3 as well
		if ($this->storage != SOCIAL_STORAGE_JOOMLA) {

			$config = ES::config();

			// Get the default avatars storage location.
			$absolutePath = JPATH_ROOT . '/' . ES::cleanPath($config->get('avatars.storage.container'));
			$relativePath = '/' . ltrim(ES::cleanPath($config->get('avatars.storage.container')), '/');

			// Get the default avatars storage location for this type.
			$typePath = ES::cleanPath($config->get('avatars.storage.' . $this->type));
			$absolutePath = $absolutePath . '/' . $typePath . '/' . $this->uid;
			$relativePath = $relativePath . '/' . $typePath . '/' . $this->uid;

			$this->deleteFolder($absolutePath, $relativePath);
		}

		// Delete small variations.
		$small = $storagePath . '/' . $this->small;

		if (JFile::exists($small)) {
			JFile::delete($small);
		}

		// Delete medium variations.
		$medium = $storagePath . '/' . $this->medium;

		if (JFile::exists($medium)) {
			JFile::delete($medium);
		}

		// Delete large variations.
		$large = $storagePath . '/' . $this->large;

		if (JFile::exists($large)) {
			JFile::delete($large);
		}

		// Delete medium variations.
		$square = $storagePath . '/' . $this->square;

		if (JFile::exists($square)) {
			JFile::delete($square);
		}

		return true;
	}

	/**
	 * Retrieves the path to the avatar
	 *
	 * @since	1.0
	 * @access	public
	 * @param	bool	True to retrieve absolute path, false otherwise.
	 * @return
	 */
	public function getPaths( $relative = false )
	{
		$sizes 		= array( 'small' , 'medium' , 'large' , 'square' );
		$result 	= array();

		$path 	= '';

		if( !$relative )
		{
			$path 	= JPATH_ROOT;
		}

		// Get the initial storage path.
		$config	= ES::config();
		$path 	= $path . '/' . ES::cleanPath( $config->get( 'avatars.storage.container' ) );

		// Get the container path
		$path 	= $path . '/' . ES::cleanPath( $config->get( 'avatars.storage.' . $this->type ) );

		// Get the unique id path
		$path 	= $path . '/' . $this->uid;

		foreach( $sizes as $size )
		{
			$avatarPath 		= $path . '/' . $this->$size;

			$result[ $size ]	= $avatarPath;
		}


		return $result;
	}

	/**
	 * Retrieves the path to the avatar
	 *
	 * @since	1.0
	 * @access	public
	 * @param	bool	True to retrieve absolute path, false otherwise.
	 * @return
	 */
	public function getPath( $size = SOCIAL_AVATAR_MEDIUM , $absolute = false )
	{
		$config 	= ES::config();

		$source 	= '';

		if( $absolute )
		{
			$source 	= JPATH_ROOT;
		}

		$location 	= ES::cleanPath( $config->get( 'avatars.storage.container' ) );
		$location 	= $location . '/' . ES::cleanPath( $config->get( 'avatars.storage.' . $this->type ) );

		$location 	= $location . '/' . $this->uid . '/' . $this->$size;

		return $location;
	}

	/**
	 * Get's the uri to an avatar.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	bool	Determine if the absolute uri should be returned.
	 */
	public function getSource( $size = SOCIAL_AVATAR_MEDIUM , $absolute = true )
	{
		$config = ES::config();

		// If avatar_id is not empty, means this is this from the default avatars
		if (!empty($this->avatar_id)) {
			$default = ES::table('defaultavatar');
			$default->load( $this->avatar_id );

			return $default->getSource( $size, $absolute );
		}

		// If the avatar size that is being requested is invalid, return default avatar.
		if( !isset( $this->$size ) || empty( $this->$size ) )
		{
			return false;
		}

		// @TODO: Configurable storage path.
		$avatarLocation 	= ES::cleanPath( $config->get( 'avatars.storage.container' ) );
		$typesLocation 		= ES::cleanPath( $config->get( 'avatars.storage.' . $this->type ) );

		// Build absolute path to the file.
		$path	= JPATH_ROOT . '/' . $avatarLocation . '/' . $typesLocation . '/' . $this->uid . '/' . $this->$size;

		// Try to get the avatars from remote storage
		if ($this->storage == 'amazon') {

			$remotePath = $avatarLocation . '/' . $typesLocation . '/' . $this->uid . '/' . $this->$size;
			$storage = ES::storage('amazon');
			$uri = $storage->getPermalink($remotePath);

			return $uri;
		}

		// Detect if avatar exists.
		if (!JFile::exists($path)) {
			$default = rtrim( JURI::root() , '/' ) . $config->get( 'avatars.default.user.' . $size );
			return $default;
		}

		// Build the uri path for the avatar.
		$uri 	= $avatarLocation . '/' . $typesLocation . '/' . $this->uid . '/' . $this->$size;

		if( $absolute )
		{
			$uri 	= rtrim( JURI::root() , '/' ) . '/' . $uri;
		}

		return $uri;
	}

	/**
	 * Add stream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addStream($verb)
	{
		if ($verb == 'update') {
			// Add stream item when a new photo is uploaded.
			$stream				= ES::stream();
			$streamTemplate		= $stream->getTemplate();

			// Set the actor.
			$streamTemplate->setActor( $this->uid , SOCIAL_TYPE_USER );

			// Set the context.
			$streamTemplate->setContext( $this->id , SOCIAL_TYPE_AVATAR );

			// Set the verb.
			$streamTemplate->setVerb( 'update' );

			//
			$streamTemplate->setType( 'full' );

			// Create the stream data.
			$stream->add( $streamTemplate );
		}
	}
}
