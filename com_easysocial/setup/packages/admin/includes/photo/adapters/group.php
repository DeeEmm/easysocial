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

class SocialPhotoAdapterGroup extends SocialPhotoAdapter
{
	private $group = null;

	public function __construct(SocialPhoto $lib, SocialAlbums $albumLib)
	{
		$this->group = ES::group($lib->uid);
		$this->access = $this->group->getAccess();

		parent::__construct($lib, $albumLib);
	}

	public function heading()
	{
		$theme = ES::themes();
		$theme->set('group', $this->group);

		$output = $theme->output('site/albums/miniheaders/group');

		return $output;
	}

	public function viewable()
	{
		// Admin can do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Open group, anyone can view anything
		if ($this->group->isOpen()) {
			return true;
		}

		// As long as the viewer is a member, allow them to view.
		if ($this->group->isMember()) {
			return true;
		}

		return false;
	}

	public function albumViewable()
	{
		return $this->albumLib->viewable();
	}

	public function getPageTitle($layout, $prefix = true)
	{
		if ($layout == 'item' || $layout == 'form') {
			$title	= $this->photo->get('title');
		}

		if ($prefix) {
			$title 	= $this->group->getName() . ' - ' . $title;
		}

		return $title;
	}

	public function setBreadcrumbs($layout)
	{
		// Set the link to the groups
		ES::document()->breadcrumb($this->group->getName(), ESR::groups(array('layout' => 'item', 'id' => $this->group->getAlias())));

		if ($layout == 'item') {
			ES::document()->breadcrumb($this->album->get('title'), $this->album->getPermalink());
		}

		// Set the albums breadcrumb
		ES::document()->breadcrumb($this->getPageTitle($layout, false));
	}

	public function getAlbumLink()
	{
		$url = ESR::albums(array('layout' => 'item', 'id' => $this->album->getAlias(), 'uid' => $this->group->getAlias(), 'type' => SOCIAL_TYPE_GROUP));

		return $url;
	}

	public function featureable()
	{
		// Allow site admin to do anything
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Allow group admin to do anything
		if ($this->group->isAdmin()) {
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
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Group admin should be treated as their own item
		if ($this->group->isAdmin()) {
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
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Only allow group admin and owner of photo to edit the photo
		if ($this->group->isAdmin() || $this->photo->user_id == $this->my->id) {
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

		// Allow sharing on open groups
		if ($this->group->isOpen()) {
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

		// Allow downloads for public group
		if ($this->group->isOpen()) {
			return true;
		}

		// If the user is member
		if ($this->group->isMember()) {
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
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Only allow group admins
		if ($this->group->isAdmin($this->my->id)) {
			return true;
		}

		return false;
	}

	public function deleteable()
	{
		// Site admins are free to do anything
		if ($this->my->isSiteAdmin())
		{
			return true;
		}

		// Group admins are allowed to delete photos
		if ($this->group->isAdmin()) {
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
	 * Determines if the group exceeded their disk storage usage
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function exceededDiskStorage()
	{
		// Not implemented on groups yet
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

		// Group members can tag people in a photo.
		if ($this->group->isMember($this->my->id)) {
			return true;
		}

		return false;
	}

	public function canSetProfilePicture()
	{
		// Do not allow users to set profile picture for photos from a group
		return false;
	}

	public function canSetProfileCover()
	{
		// Do not allow users to set profile cover for photos from a group
		return false;
	}

	public function getErrorMessage($type)
	{
		if ($type == 'upload.exceeded') {
			return JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_MAX_UPLOAD', $this->access->get('photos.max'));
		}

		if ($type == 'upload.daily.exceeded') {
			return JText::sprintf('COM_EASYSOCIAL_PHOTOS_EXCEEDED_DAILY_MAX_UPLOAD', $this->access->get('photos.maxdaily'));
		}
	}

	public function exceededUploadLimit()
	{
		// If it is 0, it means unlimited
		if ($this->access->get('photos.max') == 0) {
			return false;
		}

		if ($this->access->exceeded('photos.max', $this->group->getTotalPhotos())) {
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

		if ($this->access->exceeded('photos.maxdaily', $this->group->getTotalPhotos(true, true))) {
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
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Allow group admin to rotate photos
		if ($this->group->isAdmin()) {
			return true;
		}

		if ($this->group->isMember()) {
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
		$stream = ES::stream();
		$tpl = $stream->getTemplate();

		// Set the actor, it always has to be the user
		$tpl->setActor($this->photo->user_id, SOCIAL_TYPE_USER);

		// Set the context.
		$tpl->setContext($this->photo->id, SOCIAL_TYPE_PHOTO, $params);

		// set the target id, in this case, the album id.
		$tpl->setTarget($this->photo->album_id);

		// Set the verb
		$tpl->setVerb($verb);

		if (!empty($mysqldatestring)) {
			$tpl->setDate($mysqldatestring);
		}

		// Since this is group uploads, we want to set the cluster
		$tpl->setCluster($this->group->id, SOCIAL_TYPE_GROUP, $this->group->type);

		// Set the params to cache the group data
		$registry = ES::registry();
		$registry->set('group', $this->group);

		// Set the params to cache the group data
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
		if ($this->photo->uid == $this->group->id) {
			return true;
		}

		return false;
	}

	public function canDeleteCover($profileId = null)
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->group->isAdmin()) {
			return true;
		}

		return false;
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
		if ($this->group->canCreatePhotos()) {
			return true;
		}

		return false;
	}

	public function canUploadCovers()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->group->isAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can use the photo as the group avatar
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function canUseAvatar()
	{
		if ($this->photo->uid != $this->group->id) {
			return false;
		}

		if ($this->my->isSiteAdmin() || $this->group->isAdmin()) {
			return true;
		}

		return false;
	}

	public function getDefaultAlbum()
	{
		$model = ES::model('Albums');
		$album = $model->getDefaultAlbum($this->group->id, SOCIAL_TYPE_GROUP, SOCIAL_ALBUM_PROFILE_PHOTOS);

		return $album;
	}


	public function hasPrivacy()
	{
		return false;
	}

	public function canMovePhoto()
	{
		// If this is a system album like cover photos, profile pictures, they will not be able to move photos within this album.
		$disallowed = array(SOCIAL_ALBUM_STORY_ALBUM, SOCIAL_ALBUM_PROFILE_COVERS, SOCIAL_ALBUM_PROFILE_PHOTOS);

		if (in_array($this->album->core, $disallowed)) {
			return false;
		}

		// If user is a site admin, allow this
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If user is group admin
		if ($this->group->isAdmin()) {
			return true;
		}

		if ($this->photo->user_id != $this->my->id) {
			return false;
		}

		return false;
	}

	public function isblocked()
	{
		if (ES::user()->id != $this->group->creator_uid) {
			return ES::user()->isBlockedBy($this->group->creator_uid);
		}

		return false;
	}
}
