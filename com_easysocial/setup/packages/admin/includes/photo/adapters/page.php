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

class SocialPhotoAdapterPage extends SocialPhotoAdapter
{
	private $page = null;

	public function __construct(SocialPhoto $lib , SocialAlbums $albumLib)
	{
		$this->page	= ES::page($lib->uid);
		$this->access = $this->page->getAccess();

		parent::__construct($lib, $albumLib);
	}

	public function heading()
	{
		$theme = ES::themes();
		$theme->set('page', $this->page);

		$output = $theme->output('site/albums/miniheaders/page');

		return $output;
	}

	public function viewable()
	{
		// Admin can do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Open page, anyone can view anything
		if ($this->page->isOpen()) {
			return true;
		}

		// As long as the viewer is a member, allow them to view.
		if ($this->page->isMember()) {
			return true;
		}

		return false;
	}

	public function albumViewable()
	{
		return $this->albumLib->viewable();
	}

	public function getPageTitle($layout , $prefix = true)
	{
		if ($layout == 'item' || $layout == 'form') {
			$title = $this->photo->get('title');
		}

		if ($prefix) {
			$title = $this->page->getName() . ' - ' . $title;
		}

		return $title;
	}

	public function setBreadcrumbs($layout)
	{
		// Set the link to the pages
		ES::document()->breadcrumb($this->page->getName(), ESR::pages(array('layout' => 'item' , 'id' => $this->page->getAlias())));

		if ($layout == 'item') {
			ES::document()->breadcrumb($this->album->get('title'), $this->album->getPermalink());
		}

		// Set the albums breadcrumb
		ES::document()->breadcrumb($this->getPageTitle($layout, false));
	}

	public function getAlbumLink()
	{
		$url = ESR::albums(array('layout' => 'item', 'id' => $this->album->getAlias(), 'uid' => $this->page->getAlias(), 'type' => SOCIAL_TYPE_PAGE));

		return $url;
	}

	/**
	 * Determines if the user has admin access
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function isAdminAccess()
	{
		// Allow site admin to do anything
		if ($this->my->isSiteAdmin() || $this->page->isAdmin()) {
			return true;
		}

		return false;
	}

	public function featureable()
	{
		// Allow site admin to do anything
		if ($this->isAdminAccess()) {
			return true;
		}

		// Allow album owner to feature the photo
		if ($this->isAlbumOwner()) {
			return true;
		}

		return false;
	}

	public function isMine()
	{
		// Site admin should be treated as their own item
		if ($this->isAdminAccess()) {
			return true;
		}

		// The creator should be treated as their own item
		if ($this->photo->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	public function editable()
	{
		// By default, super admin is free to do anything
		if ($this->isAdminAccess()) {
			return true;
		}

		// Only allow page admin and owner of photo to edit the photo
		if ($this->photo->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	public function shareable($id = null)
	{
		// Check for global settings
		if (!$this->config->get('sharing.enabled')) {
			return false;
		}

		// Allow sharing on open pages
		if ($this->page->isOpen()) {
			return true;
		}

		return false;
	}

	public function downloadable($id = null)
	{
		if (!$this->config->get('photos.downloads', true)) {
			return false;
		}

		// Site admins are free to do anything
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Allow downloads for public page
		if ($this->page->isOpen()) {
			return true;
		}

		// If the user is member
		if ($this->page->isMember()) {
			return true;
		}

		return false;
	}

	public function moveable()
	{
		// Core album cannot be move anywhere
		$disallowed = array(SOCIAL_ALBUM_STORY_ALBUM, SOCIAL_ALBUM_PROFILE_COVERS, SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (in_array($this->album->core, $disallowed)) {
			return false;
		}

		// Site admins are free to do anything
		return $this->isAdminAccess();
	}

	public function deleteable()
	{
		// Site admins are free to do anything
		if ($this->isAdminAccess()) {
			return true;
		}

		// Allow photo owner to delete this photo
		if ($this->photo->user_id == $this->my->id) {
			return true;
		}

		// Allow album owner to delete this photo
		if ($this->isAlbumOwner()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the page exceeded their disk storage usage
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function exceededDiskStorage()
	{
		return false;
	}

	public function taggable()
	{
		if (!$this->config->get('photos.tagging')) {
			return false;
		}

		// Site admin can do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Page members can tag people in a photo.
		if ($this->page->isMember($this->my->id)) {
			return true;
		}

		return false;
	}

	public function canSetProfilePicture()
	{
		// Do not allow users to set profile picture for photos from a page
		return false;
	}

	public function canSetProfileCover()
	{
		// Do not allow users to set profile cover for photos from a page
		return false;
	}

	public function getErrorMessage($type)
	{
		$access = $this->page->getAccess();

		if ($type == 'upload.exceeded') {
			return JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_MAX_UPLOAD', $access->get('photos.max'));
		}

		if ($type == 'upload.daily.exceeded') {
			return JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_DAILY_MAX_UPLOAD', $access->get('photos.maxdaily'));
		}
	}

	public function exceededUploadLimit()
	{
		// If it is 0, it means unlimited
		if ($this->access->get('photos.max') == 0) {
			return false;
		}

		if ($this->access->exceeded('photos.max', $this->page->getTotalPhotos())) {
			$this->lib->setError($this->getErrorMessage('upload.exceeded'));
			return true;
		}

		return false;
	}

	public function exceededDailyUploadLimit()
	{
		// If it is 0, it means unlimited
		if ($this->access->get('photos.maxdaily') == 0) {
			return false;
		}

		if ($this->access->exceeded('photos.maxdaily', $this->page->getTotalPhotos(true, true))) {
			$this->lib->setError($this->getErrorMessage('upload.daily.exceeded'));
			return true;
		}

		return false;
	}

	public function getUploadFileSizeLimit()
	{
		$limit = $this->access->get('photos.maxsize') . 'M';

		return $limit;
	}

	public function canRotatePhoto()
	{
		// Animated gif cannot be rotated
		if ($this->photo->isAnimated()) {
			return false;
		}

		// Allow site admins to rotate any photo they want
		if ($this->isAdminAccess()) {
			return true;
		}

		if ($this->page->isMember()) {
			return true;
		}

		// Allow photo owner to rotate photos
		if ($this->photo->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	public function createStream($verb, $mysqldatestring = '', $aggregate = true, $privacyValue = '', $privacyCustom = '', $privacyField = '')
	{
		// Encode the photo as a json string to offload the weight
		$params = ES::json()->encode($this->photo);

		// Get the stream lib
		$stream	= ES::stream();
		$tpl = $stream->getTemplate();

		// Set the actor, it always has to be the user
		$tpl->setActor($this->photo->user_id , SOCIAL_TYPE_USER);

		// We know only page admin allow to upload photo
		$tpl->setPostAs(SOCIAL_TYPE_PAGE);

		// Set the context.
		$tpl->setContext($this->photo->id, SOCIAL_TYPE_PHOTO, $params);

		// set the target id, in this case, the album id.
		$tpl->setTarget($this->photo->album_id);

		// Set the verb
		$tpl->setVerb($verb);

		if (!empty($mysqldatestring)) {
			$tpl->setDate($mysqldatestring);
		}

		// Since this is page uploads, we want to set the cluster
		$tpl->setCluster($this->page->id , SOCIAL_TYPE_PAGE, $this->page->type);

		// Set the params to cache the page data
		$registry = ES::registry();
		$registry->set('page' , $this->page);

		// Set the params to cache the page data
		$tpl->setParams($registry);

		// Public viewing of the photo should rely on photos.view privacy.
		$tpl->setAccess('photos.view');

		if ($aggregate) {

			// We want to aggregate new photo uploads to an album.
			if ($verb == 'create') {
				$tpl->setAggregate(true, true);
			}

			// We shouldnt aggregate avatar uploads.
			if ($verb == 'uploadAvatar' || $verb == 'updateCover') {
				$tpl->setAggregate(false);
			}
		}

		// Create the stream data.
		return $stream->add($tpl);
	}

	public function isAlbumOwner()
	{
		return $this->albumLib->isOwner();
	}

	public function allowUseCover()
	{
		if ($this->photo->uid == $this->page->id) {
			return true;
		}

		return false;
	}

	public function canDeleteCover($profileId = null)
	{
		return $this->isAdminAccess();
	}

	/**
	 * Determines if the user is allowed to upload photos
	 *
	 * @since	2.0.14
	 * @access	public
	 */
	public function canUploadPhotos()
	{
		// Ensure that the albums feature is enabled for the group
		if (!$this->access->allowed('photos.enabled', true)) {
			return false;
		}

		// Ensure that the user is really allowed to upload photos
		if ($this->page->canCreatePhotos()) {
			return true;
		}

		return false;
	}

	public function canUploadCovers()
	{
		return $this->isAdminAccess();
	}

	public function canUseAvatar()
	{
		if ($this->photo->uid != $this->page->id) {
			return false;
		}

		if ($this->isAdminAccess()) {
			return true;
		}

		return false;
	}

	public function getDefaultAlbum()
	{
		$model = ES::model('Albums');
		$album = $model->getDefaultAlbum($this->page->id, SOCIAL_TYPE_PAGE, SOCIAL_ALBUM_PROFILE_PHOTOS);

		return $album;
	}


	public function hasPrivacy()
	{
		return false;
	}

	public function canMovePhoto()
	{
		// If this is a system album like cover photos, profile pictures, they will not be able to move photos within this album.
		$disallowed = array(SOCIAL_ALBUM_STORY_ALBUM , SOCIAL_ALBUM_PROFILE_COVERS , SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (in_array($this->album->core, $disallowed)) {
			return false;
		}

		// If user is a site admin, allow this
		if ($this->isAdminAccess()) {
			return true;
		}

		if ($this->photo->user_id != $this->my->id) {
			return false;
		}

		return false;
	}

	public function isblocked()
	{
		if (ES::user()->id != $this->page->creator_uid) {
			return ES::user()->isBlockedBy($this->page->creator_uid);
		}

		return false;
	}
}
