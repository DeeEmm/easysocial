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

class SocialAudioAdapterUser extends SocialAudioAdapter
{
	protected $author = null;

	public function __construct($uid, $type, SocialTableAudio $table)
	{
		if ($table->user_id) {
			$this->author = ES::user($table->user_id);
		}

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
		$author = ES::user($this->uid);

		return $author->getAlias();
	}

	/**
	 * Generates the mini header for the audio layout
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getMiniHeader()
	{
		// If there is a uid on the current page, we need to display the mini header
		$uid = $this->input->get('uid', 0, 'int');

		if (! $uid) {
			$uid = $this->uid;
		}

		$user = ES::user($uid);

		// we hide the miniheader if the user is banned. so that other users wont be able to navigate
		if ($user->isBanned()) {
			return;
		}

		$theme = ES::themes();
		$theme->set('user', $user);

		$output = $theme->output('site/audios/miniheaders/user');

		return $output;
	}

	/**
	 * Determines if the current user can edit this audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isEditable()
	{
		// This is a new audio
		if (!$this->table->id) {
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
		return true;
	}

	/**
	 * Checks if the provided user is allowed to view this audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isViewable()
	{
		$privacy = ES::user()->getPrivacy();

		// privacy validation
		if (!$privacy->validate('audios.view', $this->table->id, SOCIAL_TYPE_AUDIOS, $this->table->uid)) {
			return false;
		}

		return true;
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

		$access = $this->my->getAccess();

		// If the user doesn't has access for these two, return false
		if (!$access->allowed('audios.upload') && !$access->allowed('audios.link')) {
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
		$access = $this->my->getAccess();

		if (!$this->allowCreation()) {
			return false;
		}

		if (!$access->allowed('audios.upload')) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user can embed audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function allowEmbed()
	{
		$access = $this->my->getAccess();

		if (!$this->allowCreation()) {
			return false;
		}

		if (!$access->allowed('audios.link')) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user exceeded the limit to upload audios
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasExceededLimit()
	{
		$access = $this->my->getAccess();

		if ($access->exceeded('audios.daily', $this->my->getTotalAudios(true, true))) {
			return true;
		}

		if ($access->exceeded('audios.total', $this->my->getTotalAudios(false, true))) {
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
	public function getUploadLimit($withUnits = true)
	{
		$access = $this->my->getAccess();

		$limit = (int) $access->get('audios.maxsize');

		if ($withUnits) {
			$limit .= 'M';
		}

		return $limit;
	}

	/**
	 * Determines if the user can add a tag for this audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canAddTag()
	{
		// All user should be able to add tag in the audio except for guests
		if (!$this->my->id) {
			return false;
		}

		return true;
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

		// @TODO: Check for acl here

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

		// @TODO: Check for acl here

		if ($this->my->isSiteAdmin()) {
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
		// Allow users to delete their own audio
		if ($this->my->id == $this->table->user_id) {
			return true;
		}

		// Allow site admin to delete the audio
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
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		// If audio wasn't created yet, it needs to check against "can create".
		if ($this->table->id && $this->table->user_id == $this->my->id) {
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
		$owner = ($this->table->user_id == $this->my->id) || $this->my->isSiteAdmin();

		return $owner;
	}

	/**
	 * Determines if the current user can access the audios section.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canAccessAudios()
	{
		if (!$this->config->get('audio.enabled')) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		$user = ES::user($this->uid);
		if ($this->my->canView($user, 'audios.view')) {
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
		$title = JText::_('COM_ES_AUDIO_FILTERS_ALL_AUDIOS');

		if ($this->uid) {
			$user = ES::user($this->uid);

			// Set the title to the user's audios page title
			$title = JText::sprintf('COM_ES_AUDIO_USER_PAGE_TITLE', $user->getName());
		}

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
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO_FILTER_FEATURED');

		if ($this->uid) {
			$user = ES::user($this->uid);
			// Set the title to the user's audios page title
			$title = JText::sprintf('COM_ES_AUDIO_USER_FEATURED_PAGE_TITLE', $user->getName());
		}

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
		$user = ES::user($this->uid);

		// Set the title to the user's audios page title
		$title = JText::sprintf('COM_ES_AUDIO_USER_GENRE_PAGE_TITLE', $user->getName(), $genre->title);

		return $title;
	}

	public function getPageTitle($layout, $prefix = true)
	{
		$user = ES::user($this->uid);

		// Set page title
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_AUDIO');

		if ($layout == 'form' && !$this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_CREATE_AUDIO');
		}

		if ($layout == 'form' && $this->table->id) {
			$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_EDITING_AUDIO');
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

	/**
	 * Determine whether hits should be incremented.
	 *
	 * @since	2.0
	 */
	public function hit()
	{
		// Applying hit to the audio item, not user.
		if ($this->table->id) {
			$this->table->hit();
		}
	}
}
