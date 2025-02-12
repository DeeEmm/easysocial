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

class SocialVideoAdapterUser extends SocialVideoAdapter
{
	protected $author = null;

	public function __construct($uid, $type, SocialTableVideo $table)
	{
		if ($table->user_id) {
			$this->author = ES::user($table->user_id);
		}

		parent::__construct($uid, $type, $table);
	}

	/**
	 * Retrieves the group alias
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getAlias()
	{
		$author = ES::user($this->uid);

		return $author->getAlias();
	}

	/**
	 * Generates the mini header for the video layout
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getMiniHeader()
	{
		// If there is a uid on the current page, we need to display the mini header
		$uid = $this->input->get('uid', 0, 'int');

		if (!$uid && $this->table->id) {
			$uid = $this->table->uid;
		}

		if (!$uid) {
			$uid = $this->uid;
		}

		$user = ES::user($uid);

		// we hide the miniheader if the user is banned. so that other users wont be able to navigate
		if (!$user->id || $user->isBanned()) {
			return;
		}

		$theme = ES::themes();
		$theme->set('user', $user);

		$output = $theme->output('site/videos/miniheaders/user');

		return $output;
	}

	/**
	 * Determines if the current user can edit this video
	 *
	 * @since	1.4
	 */
	public function isEditable()
	{
		// This is a new video
		if (!$this->table->id) {
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
	 * Checks if the provided user is allowed to view this video
	 *
	 * @since	1.4
	 */
	public function isViewable()
	{
		$privacy = ES::privacy(ES::user()->id);

		// privacy validation
		if (!$privacy->validate('videos.view', $this->table->id, SOCIAL_TYPE_VIDEOS, $this->table->uid)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is allowed to create videos
	 *
	 * @since	1.4
	 */
	public function allowCreation($skipVideoIds = array())
	{
		// We don't allow guest to upload video
		if (!$this->my->id) {
			return false;
		}

		$access = $this->my->getAccess();

		if (!$access->allowed('videos.create')) {
			return false;
		}

		// when user upload the video from the creation form, system is already create the video into
		// database. so we need to skip this video when checking for the creation limits. #4574
		$isNew = $this->table->id ? (bool) $this->table->isnew : true;
		if ($isNew && $this->table->id) {
			$skipVideoIds[] = $this->table->id;
		}

		if ($this->hasExceededLimit($skipVideoIds)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can upload videos
	 *
	 * @since	1.4
	 */
	public function allowUpload()
	{
		$access = $this->my->getAccess();

		if (!$this->allowCreation()) {
			return false;
		}

		if (!$access->allowed('videos.upload')) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can embed videos
	 *
	 * @since	1.4
	 */
	public function allowEmbed()
	{
		$access = $this->my->getAccess();

		if (!$this->allowCreation()) {
			return false;
		}

		if (!$access->allowed('videos.link')) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user exceeded the limit to upload videos
	 *
	 * @since	1.4
	 */
	public function hasExceededLimit($skipVideoIds = array())
	{
		$access = $this->my->getAccess();

		if ($access->exceeded('videos.daily', $this->my->getTotalVideos(true, true, $skipVideoIds))) {
			return true;
		}

		if ($access->exceeded('videos.total', $this->my->getTotalVideos(false, false, $skipVideoIds))) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the upload size limit
	 *
	 * @since	1.4
	 */
	public function getUploadLimit($withUnits = true)
	{
		$access = $this->my->getAccess();

		$limit = (int) $access->get('videos.maxsize');

		if ($withUnits) {
			$limit .= 'M';
		}

		return $limit;
	}

	/**
	 * Determines if the user can add a tag for this video
	 *
	 * @since	1.4
	 */
	public function canAddTag()
	{
		// All user should be able to add tag in the video except for guests
		if (!$this->my->id) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user can unfeature the video
	 *
	 * @since	1.4
	 */
	public function canUnfeature()
	{
		if ($this->table->isUnfeatured()) {
			return false;
		}

		// @TODO: Check for acl here

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can feature the video
	 *
	 * @since	1.4
	 */
	public function canFeature()
	{
		// If this video is featured already, it should never be possible to feature the video again
		if ($this->table->isFeatured()) {
			return false;
		}

		// @TODO: Check for acl here

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can delete the video
	 *
	 * @since	1.4
	 */
	public function canDelete()
	{
		// Allow users to delete their own video
		if ($this->my->id == $this->table->user_id) {
			return true;
		}

		// Allow site admin to delete the video
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}


	/**
	 * Determines if the user can edit the video
	 *
	 * @since	1.4
	 */
	public function canEdit($skipVideoIds = array())
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If video wasn't created yet, it needs to check against "can create".
		if ($this->table->id && $this->table->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user is allowed to process the video
	 *
	 * @since	1.4
	 */
	public function canProcess()
	{
		$owner = ($this->table->user_id == $this->my->id) || $this->my->isSiteAdmin();

		return $owner;
	}

	/**
	 * Determines if the current user can access the videos section.
	 *
	 * @since	1.4
	 */
	public function canAccessVideos()
	{
		if (!$this->config->get('video.enabled')) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		$user = ES::user($this->uid);
		if ($this->my->canView($user, 'videos.view')) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	1.4
	 */
	public function getListingPageTitle()
	{
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_ALL');

		if ($this->uid) {
			$user = ES::user($this->uid);

			// Set the title to the user's videos page title
			$title = JText::sprintf('COM_EASYSOCIAL_VIDEOS_USER_PAGE_TITLE', $user->getName());
		}

		return $title;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	1.4
	 */
	public function getFeaturedPageTitle()
	{
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS_FILTER_FEATURED');

		if ($this->uid) {
			$user = ES::user($this->uid);
			// Set the title to the user's videos page title
			$title = JText::sprintf('COM_EASYSOCIAL_VIDEOS_USER_FEATURED_PAGE_TITLE', $user->getName());
		}

		return $title;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	1.4
	 */
	public function getCategoryPageTitle(SocialTableVideoCategory $category)
	{
		$user = ES::user($this->uid);

		// Set the title to the user's videos page title
		$title = JText::sprintf('COM_EASYSOCIAL_VIDEOS_USER_CATEGORY_PAGE_TITLE', $user->getName(), $category->title);

		return $title;
	}

	/**
	 * Determine whether hits should be incremented.
	 *
	 * @since	2.0
	 */
	public function hit()
	{
		// Applying hit to the video item, not user.
		$this->table->hit();
	}

	public function getPageTitle($layout, $prefix = true)
	{
		$user = ES::user($this->uid);

		// Set page title
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_VIDEOS');

		if ($layout == 'form' && !$this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_VIDEO');
		}

		if ($layout == 'form' && $this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDITING_VIDEO');
		}

		if ($prefix && !$user->guest) {
			$title = $user->getName() . ' - ' . $title;
		}

		if ($layout == 'item') {
			$title .= ' - ' . $this->table->get('title');
		}

		return $title;
	}

	public function setBreadcrumbs($layout)
	{
		// Set the breadcrumbs
		$this->document->breadcrumb($this->getPageTitle($layout));
	}
}
