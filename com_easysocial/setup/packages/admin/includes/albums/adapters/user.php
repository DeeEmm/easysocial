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

class SocialAlbumsAdapterUser extends SocialAlbumsAdapter
{
	private $user = null;

	public function __construct(SocialAlbums $lib)
	{
		$this->user = ES::user($lib->uid);

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
		// we hide the miniheader if the user is banned. so that other users wont be able to navigate
		if ($this->user->isBanned()) {
			return;
		}

		$theme = ES::themes();
		$theme->set('user', $this->user);

		$output = $theme->output('site/albums/miniheaders/user');

		return $output;
	}

	public function isValidNode()
	{
		if (!$this->user->id) {
			$this->lib->setError(JText::_('COM_EASYSOCIAL_ALBUMS_INVALID_USER_PROVIDED'));
			return false;
		}

		return true;
	}

	public function getCoreAlbumsTitle()
	{
		return 'COM_EASYSOCIAL_ALBUMS_PROFILE_ALBUMS';
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
			$additionalTitle = $this->album->get('title');
		}

		$title = '';

		if ($prefix) {
			$title = $this->user->getName() . ' - ' . $mainTitle;

			if ($additionalTitle) {
				$title .= ' - ' . $additionalTitle;
			}
		}

		if ($postfix) {
			if ($additionalTitle) {
				$title = $additionalTitle . ' - ';
			}

			$title .= $mainTitle . ' - ' . $this->user->getName();
		}

		// Fallback title
		if (!$title) {
			$title = $mainTitle;
		}

		return $title;
	}

	public function editable()
	{
		$my = ES::user();

		// Super admins are allowed to edit
		if ($my->isSiteAdmin()) {
			return true;
		}

		// If the current album is new album, they should be allowed
		if (!$this->album->id) {
			return true;
		}

		// Owner of the albums are allowed to edit
		if ($my->id == $this->album->user_id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to move the photo inside the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isPhotoMoveable()
	{
		// If this is a system album like cover photos, profile pictures, they will not be able to move photos within this album.
		if ($this->album->isCore()) {
			return false;
		}

		// Allow site admins to move anything
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Allow owners to move the photo
		if ($this->album->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if user is allowed to delete the photo inside the album
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isPhotoDeleteable()
	{
		$my = ES::user();

		// Admins are allowed to delete
		if ($my->isSiteAdmin()) {
			return true;
		}

		// If the owner of the album is the user.
		if ($this->album->user_id == $my->id) {
			return true;
		}

		return false;
	}

	public function getViewAlbumsLink($xhtml = true)
	{
		$url = ESR::albums(array('uid' => $this->user->getAlias(), 'type' => SOCIAL_TYPE_USER), $xhtml);

		return $url;
	}

	public function viewable()
	{
		// Get the privacy library
		$privacy = ES::privacy(ES::user()->id);

		if ($privacy->validate('albums.view', $this->album->id, 'albums', $this->user->id)) {
			return true;
		}


		return false;
	}

	public function setBreadcrumbs($layout)
	{
		// Set the breadcrumbs
		$this->document->breadcrumb($this->getPageTitle($layout));
	}

	public function deleteable()
	{
		// If this is a core album, it should never be allowed to delete
		if ($this->album->isCore()) {
			return false;
		}

		$my = ES::user();

		// Admins are allowed to delete
		if ($my->isSiteAdmin()) {
			return true;
		}

		// If the owner of the album is the user.
		if ($this->album->user_id == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current viewer can create albums
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canCreateAlbums()
	{
		// Super admins should always be able to create a new album
		// regardless if it is on their own account or another user's account.
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If the current albums being viewed is by a different user, we shouldn't display the create button
		if (!$this->user->isViewer()) {
			return false;
		}

		// We need to check for albums and photos creation access as well.
		if ($this->my->id && $this->access->allowed('albums.create') && $this->access->allowed('photos.create')) {
			return true;
		}

		return false;
	}

	public function setPrivacy($privacy, $customPrivacy = null, $fieldPrivacy = null)
	{
		// If the current albums is being edited by a different user (admin), we should define the target here.
		$target = !$this->user->isViewer() ? $this->user->id : null;

		$lib = ES::privacy($target);
		$lib->add('albums.view', $this->album->id, 'albums', $privacy, null, $customPrivacy, $fieldPrivacy);

		$privacyLib = ES::privacy();
		$access = $privacyLib->toValue($privacy);

		$model = ES::model('Privacy');
		$model->updateMediaAccess('albums', $this->album->id, $access, $customPrivacy, $fieldPrivacy);

	}

	public function exceededLimits()
	{
		$my = ES::user();
		$access = $my->getAccess();

		// If it is unlimited, it should never exceed the limit
		if ($access->get('albums.total', 0) == 0) {
			return false;
		}

		if ($access->exceeded('albums.total', $my->getTotalAlbums(true))) {
			return true;
		}

		return false;
	}

	public function getExceededHTML()
	{
		$my = ES::user();

		$theme = ES::themes();
		$theme->set('user', $my);

		$output	= $theme->output('site/albums/exceeded.user');

		return $output;
	}

	public function canViewAlbum()
	{

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->my->id == $this->album->user_id) {
			return true;
		}

		if (!$this->my->isBlockedBy($this->album->user_id)) {
			return true;
		}

		return false;
	}

	public function canUpload()
	{
		$my = ES::user();

		if (!$my->getAccess()->allowed('photos.create')) {
			return false;
		}

		// This could be a new album
		if (!$this->lib->data->id && $my->id) {
			return true;
		}

		if ($this->lib->data->user_id == $my->id) {
			return true;
		}

		return false;
	}

	public function canSetCover()
	{
		// Site admin's can do anything they want
		if ($this->my->isSiteAdmin()) {
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
		// Site admin can do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If the user is the owner, they are allowed
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
		return false;
	}

	public function allowMediaBrowser()
	{
		// Site admin can do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If the user is the owner, they are allowed
		if ($this->user->id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if there are privacy for such albums
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function hasPrivacy()
	{
		$disallowed = array(SOCIAL_ALBUM_PROFILE_COVERS, SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (!$this->config->get('privacy.enabled') || in_array($this->album->core, $disallowed)) {
			return false;
		}

		return true;
	}

	public function getCreateLink()
	{
		$options = array('layout' => 'form', 'uid' => $this->user->getAlias(), 'type' => SOCIAL_TYPE_USER);

		return ESR::albums($options);
	}

	public function getUploadLimit()
	{
		$access = $this->my->getAccess();

		return $access->get('photos.uploader.maxsize') . 'M';
	}

	public function isBlocked()
	{
		if (ES::user()->id != $this->user->id) {
			return ES::user()->isBlockedBy($this->user->id);
		}

		return false;
	}

	/**
	 * Determine whether hits should be incremented.
	 *
	 * @since	2.0
	 */
	public function hit()
	{
		// Applying hit to the album item, not user.
		$this->lib->data->hit();
	}

	/**
	 * Verifies the password for an album
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function verifyPassword()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If the owner is viewing their own album no need to verify password
		$table = ES::table('Album');
		$isMine = $table->isMine($this->user->id);
		if ($isMine) {
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
