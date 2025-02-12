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

class SocialAudioAdapterGroup extends SocialAudioAdapter
{
	protected $group = null;
	protected $groupAccess = null;

	public function __construct($uid, $type, SocialTableAudio $table)
	{
		$this->group = ES::group($uid);
		$this->groupAccess = $this->group->getAccess();

		parent::__construct($uid, $type, $table);
	}

	/**
	 * Retrieves the group alias
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAlias()
	{
		return $this->group->getAlias();
	}

	/**
	 * Renders the group mini header
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getMiniHeader()
	{
		return ES::template()->html('cover.group', $this->group, 'audios');
	}

	/**
	 * Determines if the current user can edit this audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isEditable()
	{
		// If this is a new audio, they should be able to edit
		if (!$this->table->id && $this->allowCreation()) {
			return true;
		}

		// Group owners and admins are allowed
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		// Edited audio
		if ($this->table->id && $this->table->user_id == $this->my->id) {
			return true;
		}

		// If user is a site admin they should be allowed to edit audios
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this adapter has a valid object
	 *
	 * @since	4.0.4
	 * @access	public
	 */
	public function isValid()
	{
		if ($this->group->id) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided user is allowed to view this audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isViewable($userId = null)
	{
		// Open groups allows anyone to view the contents from the group
		if ($this->group->isOpen()) {
			return true;
		}

		// Allow group owner
		if (($this->group->isClosed() || $this->group->isInviteOnly()) && $this->group->isMember()) {
			return true;
		}

		// Allow group owner
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		// Allow site admin
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the upload size limit
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getUploadLimit()
	{
		$limit = (int) $this->groupAccess->get('audios.maxsize');

		// If this is set to unlimited, the limit should be based on the php's upload limit
		if ($limit === 0) {
			$limit = ini_get('upload_max_filesize');

			return $limit;
		}

		return $limit . 'M';
	}

	/**
	 * Determines if the user is allowed to create audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function allowCreation()
	{
		// We don't allow guest to upload audio
		if (!$this->my->id) {
			return false;
		}

		if (!$this->groupAccess->allowed('audios.create', true)) {
			return false;
		}

		if (!$this->allowUpload() && !$this->allowEmbed()) {
			return false;
		}

		if ($this->hasExceededLimit()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can upload audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function allowUpload()
	{
		// Check if the feature is enabled
		if (!$this->config->get('audio.enabled')) {
			return false;
		}

		// Site admins and cluster admins are always allowed to create audios
		if ($this->my->isSiteAdmin() || $this->group->isAdmin($this->my->id)) {
			return true;
		}

		if ($this->groupAccess->get('audios.upload', 'members') == 'members' && $this->group->isMember($this->my->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can embed audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function allowEmbed()
	{
		// Check if the feature is enabled
		if (!$this->config->get('audio.enabled')) {
			return false;
		}
		
		// Site admins and cluster admins are always allowed to create audios
		if ($this->my->isSiteAdmin() || $this->group->isAdmin($this->my->id)) {
			return true;
		}

		if ($this->groupAccess->get('audios.link', 'members') == 'members' && $this->group->isMember($this->my->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user exceeded the limit to upload audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasExceededLimit()
	{
		static $items = array();

		$total = $this->group->getTotalAudios();

		if ($this->groupAccess->exceeded('audios.total', $this->group->getTotalAudios())) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can add a tag for this audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canAddTag()
	{
		// Super admin can always do anything they want
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		// Allow owner to add tags.
		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		// Determine if current user is part of the group
		if ($this->group->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can unfeature the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canUnfeature()
	{
		if ($this->table->isUnfeatured()) {
			return false;
		}


		// Group owners and admins are allowed to feature audios in a group
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can feature the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canFeature()
	{
		// If this audio is featured already, it should never be possible to feature the audio again
		if ($this->table->isFeatured()) {
			return false;
		}

		// Group owners and admins are allowed to feature audios in a group
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can download the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canDownload()
	{
		if ($this->config->get('audio.downloads') && $this->table->isUpload()) {
			return true;
		}

		return false;
	}
	
	/**
	 * Determines if the user can delete the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canDelete()
	{
		// Allow group admin and owners to delete audios
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can edit the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canEdit()
	{
		// If audio wasn't created yet, it needs to check against "can create".
		if (!$this->table->id) {
			return $this->allowCreation();
		}

		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		// Allow group admin and owners to edit audios
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user is allowed to process the audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canProcess()
	{
		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		// Allow group admin and owners to edit audios
		if ($this->group->isAdmin() || $this->group->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can access the audios section.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canAccessAudios()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->group->isMember()) {
			return true;
		}

		if ($this->group->isOpen()) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getListingPageTitle()
	{
		// Set the title to the user's audios page title
		$title = JText::sprintf('COM_ES_AUDIO_GROUP_PAGE_TITLE', $this->group->getName());

		return $title;
	}

	/**
	 * Retrieves the page title for featured
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getFeaturedPageTitle()
	{
		$user = ES::user($this->uid);

		// Set the title to the user's audios page title
		$title = JText::sprintf('COM_ES_AUDIO_GROUP_FEATURED_PAGE_TITLE', $this->group->getName());

		return $title;
	}

	/**
	 * Retrieves the page title for genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getGenrePageTitle(SocialTableAudioGenre $genre)
	{
		// Set the title to the user's audios page title
		$title = JText::sprintf('COM_ES_AUDIO_GROUP_GENRE_PAGE_TITLE', $this->group->getName(), $genre->title);

		return $title;
	}

	public function getPageTitle($layout, $prefix = true)
	{
		$title = '';

		if ($layout == 'item') {
			$title = $this->table->get('title');
		}

		if ($layout == 'form' && !$this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_AUDIO');
		}

		if ($layout == 'form' && $this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDITING_AUDIO');
		}

		if ($layout == 'default') {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO');
		}

		if ($prefix) {
			$title = $this->group->getName() . ' - ' . $title;
		}

		return $title;
	}

	/**
	 * Set the group's breadcrumb
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function setBreadcrumbs($layout)
	{
		// Set the link to the groups
		$this->document->breadcrumb($this->group->getName(), ESR::groups(array('layout' => 'item', 'id' => $this->group->getAlias())));

		if ($layout == 'form') {
			$this->document->breadcrumb($this->getPageTitle('default', false));
		}

		if ($layout == 'item') {
			$this->document->breadcrumb($this->getPageTitle('default', false), ESR::audios(array('uid' => $this->group->id, 'type' => SOCIAL_TYPE_GROUP)));
		}

		// Set the audio breadcrumb
		$this->document->breadcrumb($this->getPageTitle($layout, false));
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
		if ($this->group->id) {
			$this->group->hit();
		}

		// Applying hit to the audio item.
		if ($this->table->id) {
			$this->table->hit();
		}
	}
}

