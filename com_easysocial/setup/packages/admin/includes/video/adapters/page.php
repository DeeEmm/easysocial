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

class SocialVideoAdapterPage extends SocialVideoAdapter
{
	protected $page = null;
	protected $pageAccess = null;

	public function __construct($uid, $type, SocialTableVideo $table)
	{
		$this->page = ES::page($uid);
		$this->pageAccess = $this->page->getAccess();

		parent::__construct($uid, $type, $table);
	}

	/**
	 * Retrieves the page alias
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAlias()
	{
		return $this->page->getAlias();
	}

	/**
	 * Renders the page mini header
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMiniHeader()
	{
		return ES::template()->html('cover.page', $this->page, 'videos');
	}

	/**
	 * Determines if the current user can edit this video
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isEditable()
	{
		// If this is a new video, they should be able to edit
		if (!$this->table->id && $this->allowCreation()) {
			return true;
		}

		// page owners and admins are allowed
		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		// Edited video
		if ($this->table->id && $this->table->user_id == $this->my->id) {
			return true;
		}

		// If user is a site admin they should be allowed to edit videos
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
	 * Checks if the provided user is allowed to view this video
	 *
	 * @since	2.0
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
	 * @since	2.0
	 * @access	public
	 */
	public function getUploadLimit()
	{
		$limit = (int) $this->pageAccess->get('videos.maxsize');

		// If this is set to unlimited, the limit should be based on the php's upload limit
		if ($limit === 0) {
			$limit = ini_get('upload_max_filesize');

			return $limit;
		}

		return $limit . 'M';
	}

	/**
	 * Determines if the user is allowed to create videos
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function allowCreation()
	{
		// We don't allow guest to upload video
		if (!$this->my->id) {
			return false;
		}

		// we only allow page admin to add video in the video listing page
		if (!$this->my->isSiteAdmin() && (!$this->page->isAdmin() && !$this->page->isMember())) {
			return false;
		}

		// Ensure that the videos feature is enabled for the page
		if (!$this->pageAccess->allowed('videos.create', true)) {
			return false;
		}

		if ($this->hasExceededLimit()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can upload videos
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function allowUpload()
	{
		if (!$this->allowCreation()) {
			return false;
		}

		if (!$this->page->canCreateVideos()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can upload videos
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function allowEmbed()
	{
		if (!$this->allowCreation()) {
			return false;
		}

		if (!$this->pageAccess->allowed('videos.link')) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user exceeded the limit to upload videos
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function hasExceededLimit()
	{
		static $items = array();

		$total = $this->page->getTotalVideos();

		if ($this->pageAccess->exceeded('videos.total', $this->page->getTotalVideos())) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can add a tag for this video
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
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

		// Only allow user that liked the page to add tag in the video
		if ($this->page->isMember()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can unfeature the video
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canUnfeature()
	{
		if ($this->table->isUnfeatured()) {
			return false;
		}


		// Page owners and admins are allowed to feature videos in a page
		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can feature the video
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canFeature()
	{
		// If this video is featured already, it should never be possible to feature the video again
		if ($this->table->isFeatured()) {
			return false;
		}

		// Page owners and admins are allowed to feature videos in a page
		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can delete the video
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canDelete()
	{
		// Allow page admin and owners to delete videos
		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// Allow users to delete their own video
		if ($this->my->id == $this->table->user_id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can edit the video
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canEdit()
	{
		// If video wasn't created yet, it needs to check against "can create".
		if (!$this->table->id) {
			return $this->allowCreation();
		}

		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		// Allow page admin and owners to edit videos
		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user is allowed to process the video
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canProcess()
	{
		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		// Allow page admin and owners to edit videos
		if ($this->page->isAdmin() || $this->page->isOwner()) {
			return true;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can access the videos section.
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function canAccessVideos()
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
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getListingPageTitle()
	{
		// Set the title to the user's videos page title
		$title = JText::sprintf('COM_EASYSOCIAL_VIDEOS_PAGE_PAGE_TITLE', $this->page->getName());

		return $title;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getFeaturedPageTitle()
	{
		$user = ES::user($this->uid);

		// Set the title to the user's videos page title
		$title = JText::sprintf('COM_EASYSOCIAL_VIDEOS_PAGE_FEATURED_PAGE_TITLE', $this->page->getName());

		return $title;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getCategoryPageTitle(SocialTableVideoCategory $category)
	{
		// Set the title to the user's videos page title
		$title = JText::sprintf('COM_EASYSOCIAL_VIDEOS_PAGE_CATEGORY_PAGE_TITLE', $this->page->getName(), $category->title);

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
		// Adding hit to page.
		$this->page->hit();

		// Applying hit to the video item.
		$this->table->hit();
	}

	/**
	 * Get a proper page title for breadcrumb
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getPageTitle($layout, $prefix = true)
	{
		if ($layout == 'item') {
			$title = $this->table->get('title');
		}

		if ($layout == 'form' && !$this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_VIDEO');
		}

		if ($layout == 'form' && $this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDITING_VIDEO');
		}

		if ($layout == 'default') {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS');
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
			$this->document->breadcrumb($this->getPageTitle('default', false), ESR::videos(array('uid' => $this->page->id, 'type' => SOCIAL_TYPE_PAGE)));
		}

		// Set the video breadcrumb
		$this->document->breadcrumb($this->getPageTitle($layout, false));
	}
}

