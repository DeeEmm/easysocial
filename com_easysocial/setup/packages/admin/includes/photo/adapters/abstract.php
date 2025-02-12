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

abstract class SocialPhotoAdapter
{
	protected $lib 		= null;
	protected $albumLib = null;
	protected $my 		= null;

	protected $config 	= null;
	protected $photo 	= null;
	protected $album 	= null;

	public function __construct(SocialPhoto $lib, SocialAlbums $albumLib)
	{
		// Get the current viewer
		$this->my = ES::user();

		// Assign the library
		$this->lib 			= $lib;
		$this->albumLib		= $albumLib;

		// Set the photo data
		$this->photo 	= $lib->data;

		// Set the album data
		$this->album 	= $albumLib->data;

		// Get the config object
		$this->config	= ES::config();
	}

	/**
	 * Displays the header of the node.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function heading();

	/**
	 * Determines if the photo has a dropdown menu
	 *
	 * @since	3.0.3
	 * @access	public
	 */
	public function hasDropdownMenu()
	{
		if ($this->editable() || $this->shareable() || $this->config->get('photos.original') || $this->downloadable() || $this->canSetProfilePicture() || $this->canSetProfileCover() || $this->moveable() || $this->deleteable()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the photo item is viewable
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function viewable();

	/**
	 * Retrieves the title for a page
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getPageTitle( $layout , $prefix = true );


	/**
	 * Sets the breadcrumb for the photos page
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function setBreadcrumbs( $layout );

	/**
	 * Determines if the photo's album is viewable
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function albumViewable();

	/**
	 * Retrieves the link for the album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getAlbumLink();

	/**
	 * Determines if the user can feature the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function featureable();

	/**
	 * Determines if the user owns the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function isMine();

	/**
	 * Determines if the user can edit the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function editable();

	/**
	 * Determines if the user can share the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function shareable();

	/**
	 * Determines if the user can download the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function downloadable();

	/**
	 * Determines if the user can move the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function moveable();

	/**
	 * Determines if the user can delete the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function deleteable();

	/**
	 * Determines if the user can tag on the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function taggable();

	/**
	 * Determines if the user can set the photo as profile picture
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canSetProfilePicture();

	/**
	 * Determines if the user can set the photo as profile cover
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canSetProfileCover();

	/**
	 * Determines if the user has already exceeded their upload photos limit
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function exceededUploadLimit();

	/**
	 * Determines if the user has already exceeded their daily upload photos limit
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function exceededDailyUploadLimit();

	/**
	 * Retrieves the maximum file upload size allowed
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getUploadFileSizeLimit();

	/**
	 * Determines if the user can set the photo as profile picture
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canRotatePhoto();

	/**
	 * Determines if the person own's the album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function isAlbumOwner();

	/**
	 * Determines if the person can use a photo as a cover
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function allowUseCover();

	/**
	 * Determines if the person can use a photo as a cover
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canDeleteCover($profileId = null);

	/**
	 * Determines if the user is allowed to upload photos
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public abstract function canUploadPhotos();

	/**
	 * Determines if the person can upload photo covers
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canUploadCovers();

	/**
	 * Determines if the person can use an existing photo for the avatar
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canUseAvatar();

	/**
	 * Retrieves the default album for this node
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getDefaultAlbum();


	/**
	 * Determines if the user is allowed to move the photo
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canMovePhoto();

	/**
	 * Determines if the user is blocked by the owner of this object or not
	 *
	 * @since	1.3
	 * @access	public
	 */
	public abstract function isblocked();
}
