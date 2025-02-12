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

require_once(__DIR__ . '/abstract.php');

class SocialAlbumsAdapterEvent extends SocialAlbumsAdapter
{
	private $event = null;

	public function __construct(SocialAlbums $lib)
	{
		$this->event = ES::event($lib->uid);

		parent::__construct($lib);
	}

	/**
	 * Generates the mini header of the albums area
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function heading()
	{
		$theme = ES::themes();
		$theme->set('event', $this->event);

		$output = $theme->output('site/albums/miniheaders/event');

		return $output;
	}

	public function isValidNode()
	{
		if (!$this->event || !$this->event->id) {
			$this->lib->setError(JText::_('COM_EASYSOCIAL_ALBUMS_EVENT_INVALID_EVENT_ID_PROVIDED'));
			return false;
		}

		return true;
	}

	/**
	 * Get the album link for this event album
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getViewAlbumsLink($xhtml = true)
	{
		$url = ESR::albums(array('uid' => $this->event->getAlias(), 'type' => SOCIAL_TYPE_EVENT), $xhtml);

		return $url;
	}

	public function getCoreAlbumsTitle()
	{
		return 'COM_EASYSOCIAL_ALBUMS_EVENT_ALBUMS';
	}

	public function getMyAlbumsTitle()
	{
		return 'COM_EASYSOCIAL_ALBUMS_EVENT_MY_ALBUMS';
	}

	/**
	 * Retrieve the page title
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getPageTitle($layout, $postfix = true)
	{
		$prefix = false;
		$mainTitle = JText::_('COM_EASYSOCIAL_PAGE_TITLE_ALBUMS');
		$additionalTitle = '';

		if ($layout == 'form') {
			if (!$this->album->id) {
				$mainTitle = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_ALBUM');
			} else {
				$mainTitle = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDITING_ALBUM');
			}
		}

		if ($layout == 'item') {
			$additionalTitle = $this->lib->data->get('title');
		}

		$title = '';

		if ($prefix) {
			$title = $this->event->getName() . ' - ' . $mainTitle;

			if ($additionalTitle) {
				$title .= ' - ' . $additionalTitle;
			}
		}

		if ($postfix) {
			if ($additionalTitle) {
				$title = $additionalTitle . ' - ';
			}

			$title .= $mainTitle . ' - ' . $this->event->getName();
		}

		// Fallback title
		if (!$title) {
			$title = $mainTitle;
		}

		return $title;
	}

	/**
	 * Determines if the current viewer can view the album
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function viewable()
	{
		// Site admin should always be able to view
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Event admin is always allowed to view
		if ($this->event->isAdmin()) {
			return true;
		}

		// If the event is public, it should be viewable
		if ($this->event->isOpen()) {
			return true;
		}

		// Event members should be allowed
		if ($this->event->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current viewer can delete the album
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function deleteable()
	{
		// If this is a core album, it should never be allowed to delete
		if ($this->album->isCore()) {
			return false;
		}

		// Super admins are allowed to edit
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Event admin's are always allowed
		if ($this->event->isAdmin()) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($this->album->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if user is able to delete the photo in this albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isPhotoDeleteable()
	{
		// Super admins are allowed to edit
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Event admin's are always allowed
		if ($this->event->isAdmin()) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($this->album->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the viewer can move the photo inside the albums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isPhotoMoveable()
	{
		// Core album cannot be move anywhere
		if ($this->album->isCore()) {
			return false;
		}

		// Site admins are free to do anything
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Only allow group admins
		if ($this->event->isAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the viewer can edit the album
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function editable()
	{
		// Perhaps the person is creating a new album
		if (!$this->album->id) {
			return true;
		}

		// Super admins are allowed to edit
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Group admin's are always allowed
		if ($this->event->isAdmin()) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($this->my->id == $this->album->user_id) {
			return true;
		}

		return false;
	}

	/**
	 * Set the current breadcrumbs for the page
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function setBreadcrumbs($layout)
	{
		// Set the link to the groups
		$this->document->breadcrumb($this->event->getName(), $this->event->getPermalink());

		if ($layout == 'form') {
			$this->document->breadcrumb($this->getPageTitle('default', false));
		}

		if ($layout == 'item') {
			$this->document->breadcrumb($this->getPageTitle('default', false), ESR::albums(array('uid' => $this->event->id, 'type' => SOCIAL_TYPE_EVENT)));
		}

		// Set the albums breadcrumb
		$this->document->breadcrumb($this->getPageTitle($layout, false));
	}

	public function setPrivacy($privacy, $customPrivacy = null, $fieldPrivacy = null)
	{
		// We don't really need to use the privacy library here.
	}

	/**
	 * Determines if the user is allowed to create albums in this event
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function canCreateAlbums()
	{
		// If the user is a site admin, they are allowed to
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if (!$this->my->getAccess()->get('photos.create') || !$this->my->getAccess()->get('albums.create')) {
			return false;
		}

		if (!$this->event->canCreatePhotos()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the viewer can upload into the album
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function canUpload()
	{
		// Site admins are always allowed
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if (!$this->my->getAccess()->get('photos.create')) {
			return false;
		}

		// Event admins are always allowed
		if ($this->event->isAdmin()) {
			return true;
		}

		// Event members are allowed to upload and collaborate in albums
		if ($this->event->isMember()) {
			return true;
		}

		// If the current viewer is the owner of the album
		if ($this->lib->data->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	public function exceededLimits()
	{
		// @TODO: Check for group limits

		return false;
	}

	public function getExceededHTML()
	{
		$theme = ES::themes();
		$theme->set('user', $my );
		$html = $theme->output('site/albums/exceeded' );

		return $this->output($html, $album->data );
	}

	public function canViewAlbum()
	{
		// Public group should be accessible.
		if ($this->event->isOpen()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->event->isAdmin()) {
			return true;
		}

		// Group members are allowed to upload and collaborate in albums
		if ($this->event->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to set the cover for the album
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function canSetCover()
	{
		// Site admin's can do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Group admins are allowed
		if ($this->event->isAdmin()) {
			return true;
		}

		// If the user is the owner, they are allowed
		if ($this->album->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the album is owned by the current user
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function isOwner()
	{
		// Site admins should always be treated as the owner
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Group admins should always be treated as the owner
		if ($this->event->isAdmin()) {
			return true;
		}

		// If the user is the creator of the album, they should also be treated as the owner
		if ($this->album->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if should show My Albums or not
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function showMyAlbums()
	{
		if ($this->my->guest) {
			return false;
		}

		return true;
	}

	public function allowMediaBrowser()
	{
		// Site admins should always be treated as the owner
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->event->isAdmin()) {
			return true;
		}

		return false;
	}

	public function hasPrivacy()
	{
		return false;
	}

	/**
	 * Retrieves the creation link
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getCreateLink()
	{
		$url = ESR::albums(array('layout' => 'form', 'uid' => $this->event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));

		return $url;
	}

	/**
	 * Retrieves the upload limit
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getUploadLimit()
	{
		$access = $this->event->getAccess();

		return $access->get('photos.maxsize') . 'M';
	}

	public function isBlocked()
	{
		if (ES::user()->id != $this->event->creator_uid) {
			return ES::user()->isBlockedBy($this->event->creator_uid);
		}
		return false;
	}

	/**
	 * Determine whether hits should be incremented.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hit()
	{
		// Applying hit to the cluster.
		$this->event->hit();

		// Applying hit to the album item.
		if ($this->lib->data->id) {
			$this->lib->data->hit();
		}
	}

	/**
	 * Verifies the password for an album
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function verifyPassword()
	{
		if ($this->my->id == $this->album->user_id) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		$session = JFactory::getSession();
		$password = $session->get('PROTECTEDALBUM_' . $this->album->id, '', 'EASYSOCIAL');

		if ($password == $this->album->password) {
			return true;
		}

	return false;

	}

	/**
	 * Determines if the current viewer can set password
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function canSetPassword()
	{
		// Super admins should always be able to create a new album
		// regardless if it is on their own account or another user's account.
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->my->id && $this->access->allowed('albums.set.password')) {
			return true;
		}

		return false;
	}
}
