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

class SocialAlbumsAdapterPage extends SocialAlbumsAdapter
{
	private $page = null;

	public function __construct(SocialAlbums $lib)
	{
		$this->page = ES::page($lib->uid);

		parent::__construct($lib);
	}

	public function heading()
	{
		$theme = ES::themes();
		$theme->set('page', $this->page);

		$output = $theme->output('site/albums/miniheaders/page');

		return $output;
	}

	public function isValidNode()
	{
		if (!$this->page->id) {
			$this->lib->setError(JText::_('Sorry, but the page id provided is not a valid page id.'));
			return false;
		}

		if (ES::user()->id != $this->page->creator_uid) {
			if(ES::user()->isBlockedBy($this->page->creator_uid)) {
				ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGES_PAGE_NOT_FOUND'));
			}
		}

		return true;
	}

	public function getViewAlbumsLink($xhtml = true)
	{
		$url = FRoute::albums(array('uid' => $this->page->getAlias(), 'type' => SOCIAL_TYPE_PAGE), $xhtml);

		return $url;
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
			$title = $this->page->getName() . ' - ' . $mainTitle;

			if ($additionalTitle) {
				$title .= ' - ' . $additionalTitle;
			}
		}

		if ($postfix) {
			if ($additionalTitle) {
				$title = $additionalTitle . ' - ';
			}

			$title .= $mainTitle . ' - ' . $this->page->getName();
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
	 * @since	2.0
	 * @access	public
	 */
	public function viewable()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Public page should be accessible.
		if ($this->page->isOpen()) {
			return true;
		}

		// Page members should be allowed
		if ($this->page->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current viewer can delete the album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteable()
	{
		// If this is a core album, it should never be allowed to delete
		if ($this->album->isCore()) {
			return false;
		}

		// Super admins and Page admin are allowed to delete
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($this->my->id == $this->album->user_id) {
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
		// Super admins and Page admin are allowed to delete
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($this->my->id == $this->album->user_id) {
			return true;
		}

		return false;
	}

	public function isPhotoMoveable()
	{
		// Core album cannot be move anywhere
		if ($this->album->isCore()) {
			return false;
		}

		// Allow site admin to do anything
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the viewer can edit the album
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function editable()
	{
		// Perhaps the person is creating a new album
		if (!$this->album->id) {
			return true;
		}

		// Super admins and Page admin are allowed to edit
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($this->my->id == $this->album->user_id) {
			return true;
		}

		return false;
	}

	/**
     * Set the page's breadcrumb
     *
     * @since   2.0
     * @access  public
     */
	public function setBreadcrumbs($layout)
	{
		// Set the link to the pages
		$this->document->breadcrumb($this->page->getName(), ESR::pages(array('layout' => 'item', 'id' => $this->page->getAlias())));

		if ($layout == 'form') {
			$this->document->breadcrumb($this->getPageTitle('default', false));
		}

		if ($layout == 'item') {
			$this->document->breadcrumb($this->getPageTitle('default', false), FRoute::albums(array('uid' => $this->page->id, 'type' => SOCIAL_TYPE_PAGE)));
		}

		// Set the albums breadcrumb
		$this->document->breadcrumb($this->getPageTitle($layout, false));
	}

	public function setPrivacy($privacy, $customPrivacy = null, $fieldPrivacy = null)
	{
		// We don't really need to use the privacy library here.
	}

	public function canCreateAlbums()
	{
		// Super admins should always be able to create a new album
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if (!$this->my->getAccess()->get('photos.create') || !$this->my->getAccess()->get('albums.create')) {
			return false;
		}

		if (!$this->page->canCreatePhotos()) {
			return false;
		}

		return true;
	}

	public function canUpload()
	{
		// Super admins should always be able to create a new album
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if (!$this->my->getAccess()->get('photos.create')) {
			return false;
		}

		if ($this->page->isAdmin()) {
			return true;
		}

		if ($this->lib->data->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	public function exceededLimits()
	{
		// @TODO: Check for page limits

		return false;
	}

	public function getExceededHTML()
	{
		$theme = ES::themes();
		$theme->set('user', $this->my);
		$html = $theme->output('site/albums/exceeded');

		return $this->output($html, $album->data);
	}

	public function canSetCover()
	{
		// Super admins and Page admin are allowed
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		// If the user is the owner, they are allowed
		if ($this->album->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	public function isOwner()
	{
		// Super admins and Page admin are the owner
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

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

		return false;
	}

	public function allowMediaBrowser()
	{
		// Super admins and Page admin are allowed
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		return false;
	}

	public function hasPrivacy()
	{
		return false;
	}

	public function getCreateLink()
	{
		$options = array('layout' => 'form', 'uid' => $this->page->getAlias(), 'type' => SOCIAL_TYPE_PAGE);

		return FRoute::albums($options);
	}

	public function getUploadLimit()
	{
		$access = $this->page->getAccess();

		return $access->get('photos.maxsize') . 'M';
	}

	public function isBlocked()
	{
		if (ES::user()->id != $this->page->creator_uid) {
			return ES::user()->isBlockedBy($this->page->creator_uid);
		}
		return false;
	}

	public function canViewAlbum()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->page->isAdmin() || $this->page->isOpen()) {
			return true;
		}

		// Group members are allowed to upload and collaborate in albums
		if ($this->page->isMember()) {
			return true;
		}

		return false;
	}

	public function getCoreAlbumsTitle()
	{
		return 'COM_EASYSOCIAL_ALBUMS_PAGE_ALBUMS';
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
		$this->page->hit();

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
