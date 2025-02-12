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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

// Include the main table.
ES::import( 'admin:/tables/table' );

/**
 * Default avatar table mapping.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialTableDefaultAvatar extends SocialTable
{
	/**
	 * The unique id of the default avatar.
	 * @var	int
	 */
	public $id			= null;

	/**
	 * The unique item id.
	 * @var	int
	 */
	public $uid 		= null;

	/**
	 * The unique item type. E.g: @SOCIAL_TYPE_USER
	 * @var string
	 */
	public $type 		= null;

	/**
	 * The title of this default avatar.
	 * @var string
	 */
	public $title       = null;

	/**
	 * The creation date of the default avatar.
	 * @var datetime
	 */
	public $created     = null;

	/**
	 * State of the avatar. 0 - unpublished , 1 -published.
	 * @var	int
	 */
	public $state			= null;

	/**
	 * The storage path to the avatar for large size.
	 * @var string
	 */
	public $large			= null;

	/**
	 * The storage path to the avatar for medium size.
	 * @var string
	 */
	public $medium			= null;

	/**
	 * The storage path to the avatar for small size.
	 * @var string
	 */
	public $small			= null;

	/**
	 * The storage path to the avatar for square size.
	 * @var string
	 */
	public $square			= null;


	/**
	 * Determines if this avatar is the default avatar.
	 * @var bool
	 */
	public $default          = false;

	/**
	 * Class constructor
	 *
	 * @since	1.0
	 * @access	public
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function __construct( $db )
	{
		parent::__construct('#__social_default_avatars', 'id', $db);
	}

	/**
	 * Overrides parent's load implementation
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function load( $keys = null, $reset = true )
	{
		$state = null;

		$cachekey = 'DefaultAvatar.' . $keys;

		if (ES::cache()->exists($cachekey)) {
			$state = parent::bind(ES::cache()->get($cachekey));
		} else {
			$state = parent::load( $keys, $reset );
		}

		return $state;
	}

	/**
	 * Get's the absolute url for the image source.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The size to get. (SOCIAL_AVATAR_SMALL , SOCIAL_AVATAR_MEDIUM , SOCIAL_AVATAR_LARGE)
	 * @param	bool	True to use absolute path. (Optional, default is true)
	 * @return	string	The absolute url to the image.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function getSource( $size = SOCIAL_AVATAR_LARGE , $absolute = true )
	{
		// Get configuration object.
		$config 	= ES::config();

		// Get the avatars storage path.
		$avatarsPath 	= ES::cleanPath( $config->get( 'avatars.storage.container' ) );

		// Get the defaults storage path.
		$defaultsPath	= ES::cleanPath( $config->get( 'avatars.storage.default' ) );

		// Get the types storage path.
		$typesPath		= ES::cleanPath( $config->get( 'avatars.storage.defaults.' . $this->type ) );

		// Get the id storage path
		$idPath			= ES::cleanPath( $this->uid );

		// Let's construct the final path.
		$storagePath	= JPATH_ROOT . '/' . $avatarsPath . '/' . $defaultsPath . '/' . $typesPath . '/' . $idPath . '/' . $this->$size;

		// Let's test if the file exists.
		$exists 		= JFile::exists( $storagePath );

		if( !$exists )
		{
			$default = rtrim( JURI::root() , '/' ) . $config->get( 'avatars.default.user.' . $size );
			return $default;
		}

		// Construct the final uri;
		$uri 		= $avatarsPath . '/' . $defaultsPath . '/' . $typesPath . '/' . $idPath . '/' . $this->$size;

		// If caller wants absolute url, give them the site url.
		if( $absolute )
		{
			return rtrim( JURI::root() , '/' ) . '/' . $uri;
		}

		return $uri;
	}

	/**
	 * Loads an avatar object based on the given unique id and type.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique id.
	 * @param	string	The unique type.
	 * @return	bool	True if success false otherwise.
	 */
	public function loadByType( $id , $type )
	{
		$db 		= ES::db();
		$query[]	= 'SELECT * FROM ' . $db->nameQuote( $this->_tbl );
		$query[]	= 'WHERE ' . $db->nameQuote( 'uid' ) . '=' . $db->Quote( $id );
		$query[]	= 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );

		// Glue back the query.
		$query 		= implode( ' ' , $query );

		$db->setQuery( $query );

		$result 	= $db->loadObject();

		if( !$result )
		{
			return false;
		}

		return parent::bind( $result );
	}

	/**
	 * Responsible to store the uploaded images.
	 *
	 * @since	1.0
	 * @access	public
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function upload($files)
	{
		// Get config object.
		$config = ES::config();

		// Do not proceed if image doesn't exist.
		if (empty($files) || !isset($files['file'])) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATARS_FILE_UNAVAILABLE'));
			return false;
		}

		// Get the single file input since the $files is an array.
		$file = $files['file'];

		// Get the default avatars storage location.
		$avatarsPath = JPATH_ROOT . '/' . ES::cleanPath($config->get('avatars.storage.container'));

		// Test if the avatars path folder exists. If it doesn't we need to create it.
		if (!ES::makeFolder($avatarsPath)) {
			$this->setError(JText::_('Errors when creating default container for avatar'));
			return false;
		}

		// Get the defaults avatar path.
		$defaultsPath = $avatarsPath . '/' . ES::cleanPath($config->get('avatars.storage.default' ));

		// Ensure that the defaults path exist
		if (!ES::makeFolder($defaultsPath)) {
			$this->setError(JText::_('Errors when creating default path for avatar'));
			return false;
		}

		// Get the default avatars storage location for this type.
		$typePath = $config->get('avatars.storage.defaults.' . $this->type);
		$storagePath = $defaultsPath . '/' . ES::cleanPath($typePath);

		// Ensure storage path exists.
		if (!ES::makeFolder($storagePath)) {
			$this->setError(JText::_( 'Errors when creating default path for avatar'));
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
		$image = ES::get('Image');
		$image->load($file['tmp_name']);

		// Test if the image is really a valid image.
		if (!$image->isValid()) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATARS_FILE_NOT_IMAGE'));
			return false;
		}

		// Process avatar storage.
		$avatar = ES::avatar($image , $this->uid , $this->type);

		// Let's create the avatar.
		$sizes 	= $avatar->create($storagePath);

		// Assign the values back.
		foreach ($sizes as $size => $url) {
			$this->$size = $url;
		}

		return true;
	}

	/**
	 * Override's parent delete implementation since we need to also delete the images and perform some
	 * cleanup.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		Primary key (Optional)
	 * @return	bool	True on success, false otherwise.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function delete( $pk = null )
	{
		$state 	= parent::delete( $pk );

		// Remove the avatars physical files.
		if( !$this->removeImage( SOCIAL_AVATAR_SMALL ) )
		{
			return false;
		}

		if( !$this->removeImage( SOCIAL_AVATAR_MEDIUM ) )
		{
			return false;
		}

		if( !$this->removeImage( SOCIAL_AVATAR_LARGE ) )
		{
			return false;
		}

		// @TODO: Remove any association of user's profile with the current default avatar.

		return $state;
	}

	/**
	 * Removes phsyical avatar file from the system.
	 *
	 * @since	1.0
	 * @access	private
	 * @param	string	The size to remove.
	 * @return	bool	True on success, false otherwise.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	private function removeImage( $size = SOCIAL_AVATAR_SMALL )
	{
		jimport( 'joomla.filesystem.file' );

		if( !empty( $this->$size ) )
		{
			// Build the path to the image item.
			$config	= ES::config();

			$path 	= JPATH_ROOT;

			$path 	= $path . '/' . ES::cleanPath( $config->get( 'avatars.storage.container' ) );
 			$path 	= $path . '/' . ES::cleanPath( $config->get( 'avatars.storage.default' ) );
			$path 	= $path . '/' . ES::cleanPath( $config->get( 'avatars.storage.defaults.' . $this->type ) );

			$path 	= $path . '/' . ES::cleanPath( $this->uid );

			$path 	= $path . '/' . $this->$size;


			if( !JFile::exists( $path ) )
			{
				// dump( $path );
				$this->setError( JText::_( 'COM_EASYSOCIAL_PROFILES_DEFAULT_AVATARS_FILE_NOT_FOUND' ) );
				return false;
			}

			return JFile::delete( $path );
		}

		return false;
	}

	/**
	 * Removes all default avatars based on the current `uid` and `type`
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	bool	True if success false otherwise.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function removeDefault()
	{
		$db		= ES::db();

		$query  = 'UPDATE ' . $db->nameQuote( $this->_tbl ) . ' SET '
				. $db->nameQuote( 'default' ) . ' = ' . $db->Quote( 0 ) . ' '
				. 'WHERE ' . $db->nameQuote( 'uid' ) . ' = ' . $db->Quote( $this->uid ) . ' '
				. 'AND ' . $db->nameQuote( 'type' ) . ' = ' . $db->Quote( $this->type );
		$db->setQuery( $query );
		$db->Query();

		return true;
	}

	/**
	 * Sets an avatar as the default avatar for the unique `uid` and `type`.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 *
	 * @return	bool	True if success, false otherwise.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function setDefault()
	{
		// Remove all existing default items since there can only be one default item at a time.
		$this->removeDefault();

		$this->default 	= SOCIAL_STATE_PUBLISHED;

		return $this->store();
	}
}
