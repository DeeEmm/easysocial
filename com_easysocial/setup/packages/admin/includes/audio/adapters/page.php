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

class SocialAudioAdapterPage extends SocialAudioAdapter
{
	protected $page = null;
	protected $pageAccess = null;

	public function __construct($uid, $type, SocialTableAudio $table)
	{
		$this->page = ES::page($uid);
		$this->pageAccess = $this->page->getAccess();

		parent::__construct($uid, $type, $table);
	}

	/**
	 * Retrieves the page alias
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAlias()
	{
		return $this->page->getAlias();
	}

	/**
	 * Renders the page mini header
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getMiniHeader()
	{
		return ES::template()->html('cover.page', $this->page, 'audios');
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

		// page owners and admins are allowed
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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
		if ($this->page->id) {
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
		// Open pages allows anyone to view the contents from the page
		if ($this->page->isOpen()) {
			return true;
		}

		// Allow page owner
		if (($this->page->isClosed() || $this->page->isInviteOnly()) && $this->page->isMember()) {
			return true;
		}

		// Allow page owner
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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
		$limit = (int) $this->pageAccess->get('audios.maxsize');

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

		if (!$this->pageAccess->allowed('audios.create', true)) {
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
		if ($this->my->isSiteAdmin() || $this->page->isAdmin($this->my->id)) {
			return true;
		}

		if ($this->pageAccess->get('audios.upload', 'members') == 'members' && $this->page->isMember($this->my->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can upload audios
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
		if ($this->my->isSiteAdmin() || $this->page->isAdmin($this->my->id)) {
			return true;
		}

		if ($this->pageAccess->get('audios.link', 'members') == 'members' && $this->page->isMember($this->my->id)) {
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

		if ($this->pageAccess->exceeded('audios.total', $this->page->getTotalAudios())) {
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

		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		// Allow owner to add tags.
		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		// Only allow user that liked the page to add tag in the audio
		if ($this->page->isMember()) {
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


		// Page owners and admins are allowed to feature audios in a page
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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

		// Page owners and admins are allowed to feature audios in a page
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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
		// Allow page admin and owners to delete audios
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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

		// Allow page admin and owners to edit audios
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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

		// Allow page admin and owners to edit audios
		if ($this->page->isAdmin() || $this->page->isOwner()) {
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

		if ($this->page->isMember()) {
			return true;
		}

		if ($this->page->isOpen()) {
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
		$title = JText::sprintf('COM_ES_AUDIO_PAGE_PAGE_TITLE', $this->page->getName());

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
		$title = JText::sprintf('COM_ES_AUDIO_PAGE_FEATURED_PAGE_TITLE', $this->page->getName());

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
		$title = JText::sprintf('COM_ES_AUDIO_PAGE_GENRE_PAGE_TITLE', $this->page->getName(), $genre->title);

		return $title;
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
		if ($this->page->id) {
			$this->page->hit();
		}

		// Applying hit to the audio item.
		if ($this->table->id) {
			$this->table->hit();
		}
	}

	public function getPageTitle($layout, $prefix = true)
	{
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
			$title = $this->page->getName() . ' - ' . $title;
		}

		return $title;
	}

	/**
	 * Set the page's breadcrumb
	 *
	 * @since   2.1
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
			$this->document->breadcrumb($this->getPageTitle('default', false), ESR::audios(array('uid' => $this->page->id, 'type' => SOCIAL_TYPE_PAGE)));
		}

		// Set the audio breadcrumb
		$this->document->breadcrumb($this->getPageTitle($layout, false));
	}
}

