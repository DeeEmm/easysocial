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

abstract class SocialAlbumsAdapter extends EasySocial
{
	protected $lib = null;
	protected $album = null;

	public function __construct(SocialAlbums $lib)
	{
		parent::__construct();

		$this->document = ES::document();

		// Assign the library
		$this->lib = $lib;

		// Set the album data
		$this->album = $lib->data;
	}

	/**
	 * Displays the header of the node.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function heading();

	/**
	 * It should determine if the node is valid
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function isValidNode();

	/**
	 * Retrieves the title for a page
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getPageTitle($layout, $postfix = true);

	/**
	 * Renders the text that appears on the sidebar for core albums
	 *
	 * @since	2.0
	 * @access	public
	 */
	public abstract function getCoreAlbumsTitle();

	/**
	 * Determines if the album is editable
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function editable();

	/**
	 * Sets the breadcrumb for an album page
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function setBreadcrumbs($layout);

	/**
	 * It should determine if the node has exceeded it's album limits
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function exceededLimits();

	/**
	 * Retrieves the exceeded output
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getExceededHTML();

	/**
	 * Determines if the caller can upload files into the album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canUpload();

	/**
	 * Determines if viewer can view album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public abstract function canViewAlbum();

	/**
	 * Determines if the user can set the cover of the album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function canSetCover();

	/**
	 * Determines if the user is the owner of the album
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function isOwner();

	/**
	 * Determines if should show My Albums or not
	 *
	 * @since	2.0
	 * @access	public
	 */
	public abstract function showMyAlbums();

	/**
	 * Determines if the viewer is allowed to use the album browser
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function allowMediaBrowser();

	/**
	 * Determines if albums for this type of node has privacy.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function hasPrivacy();

	/**
	 * Retrieves the albums creation link.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getCreateLink();

	/**
	 * Retrieves the upload limit allowed
	 *
	 * @since	1.2
	 * @access	public
	 */
	public abstract function getUploadLimit();

	/**
	 * check if the current logged user is being blocked by the onwer of this object.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public abstract function isBlocked();

	/**
	 * Determine whether hit has to be incremented.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public abstract function hit();
}
