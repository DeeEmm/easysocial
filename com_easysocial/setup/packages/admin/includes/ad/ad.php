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

class SocialAd
{
	private $table = null;

	public function __construct($id = null)
	{
		$this->config = ES::config();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;

		$this->table = ES::table('Ad');

		if ($id && is_int($id)) {
			$this->table->load($id);
		}

		if (is_object($id)) {
			$this->table->bind($id);
		}
	}

	/**
	 * Magic method to access table's property
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function __get($property)
	{
		if (!property_exists($this, $property) && isset($this->table->$property)) {
			return $this->table->$property;
		}
	}

	/**
	 * Approves the ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function approve()
	{
		$this->table->state = SOCIAL_ADS_PUBLISHED;
		$this->table->store();

		// Send notification to ad creator
		$user = $this->getOwner();

		ES::notify('ads.approved', [$user->id], [
			'title' => 'COM_ES_ADS_AD_APPROVED',
			'template' => 'site/ads/approved',
			'adTitle' => $this->table->title,
			'adThumbnail' => $this->getCover(),
			'adIntro' => $this->table->intro,
			'permalink' => ESR::ads(['external' => true], false)
		], [], SOCIAL_NOTIFICATION_TYPE_EMAIL);

		return $this;
	}

	/**
	 * Binds data to the table
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function bind($data = [])
	{
		$this->table->bind($data);

		return $this;
	}

	/**
	 * Creates a new advertisement
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function create($data, SocialAdvertiser $advertiser, $file)
	{
		// Since this is a new ad, it should be going through the standard workflow
		$data['advertiser_id'] = (int) $advertiser->id;
		$data['created'] = ES::date()->toSql();
		$data['state'] = SOCIAL_ADS_MODERATION;
		$data['cover'] = '';

		// Always enforce time limit when created by user
		$data['enable_limit'] = true;

		// Default to the lowest priority when user creates a new ad
		$data['priority'] = SOCIAL_ADS_PRIORITY_LOW;

		$this->bind($data);

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->table->params) {
			$this->table->params = '';
		}

		if (!$this->table->log) {
			$this->table->log = '';
		}

		if (!$this->table->click) {
			$this->table->click = 0;
		}

		if (!$this->table->view) {
			$this->table->view = 0;
		}

		$state = $this->table->store();

		// Upload the cover
		if (!empty($file['tmp_name'])) {
			$state = $this->table->uploadCover($file);
		}

		// Notify admin that the ad needs to be approved
		$this->notifyAdmin();

		return $state;
	}

	/**
	 * Determines if the ad is editable
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canEdit($userId = null)
	{
		$viewer = ES::user($userId);
		$advertiser = $this->getAdvertiser();
		$owner = $advertiser->getUser();

		if (!ES::isSiteAdmin() && $owner->id != $viewer->id) {
			return false;
		}

		if ($this->isUnderModeration()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user can request creation for an advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canCreate($userId = null)
	{
		$user = ES::user($userId);

		static $cache = [];

		if (!isset($cache[$userId])) {
			$cache[$userId] = $user->canCreateAds();
		}

		return $cache[$userId];
	}

	/**
	 * Deletes the advertisement
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function delete()
	{
		return $this->table->delete();
	}

	/**
	 * Retrieves the user account associated with this advertising account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getAdvertiser()
	{
		static $cache = [];

		$key = $this->table->advertiser_id;

		if (!isset($cache[$key])) {
			$advertiser = ES::advertiser((int) $this->table->advertiser_id);

			$cache[$key] = $advertiser;
		}

		return $cache[$key];
	}

	/**
	 * Retrieve the cover of the ad
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function getCover()
	{
		return $this->table->getCover();
	}

	/**
	 * Retrieve the creation date of the ad
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function getCreatedDate()
	{
		return ES::date($this->table->created);
	}

	/**
	 * Retrieve params
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getParams()
	{
		$params = new JRegistry($this->table->params);
		return $params;
	}

	/**
	 * Retrieve priority text
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getPriority()
	{
		$priorities = [
				'1' => 'COM_ES_ADS_FORM_PRIORITY_LOW',
				'2' => 'COM_ES_ADS_FORM_PRIORITY_MED',
				'3' => 'COM_ES_ADS_FORM_PRIORITY_HIGH',
				'4' => 'COM_ES_ADS_FORM_PRIORITY_HIGHEST'
		];

		return JText::_($priorities[$this->priority]);
	}

	/**
	 * Retrieves the reject data
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getRejectData()
	{
		$params = $this->getParams();
		$reject = new stdClass();
		$reject->message = $params->get('rejectMessage');
		$reject->date = $params->get('rejectDate');

		return $reject;
	}

	/**
	 * Retrieves the owner of the ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getOwner()
	{
		static $cache = [];

		$advertiser = $this->getAdvertiser();

		if (!isset($cache[$advertiser->id])) {
			$cache[$advertiser->id] = $advertiser->getUser();
		}

		return $cache[$advertiser->id];
	}

	/**
	 * Retrieve the error of the ad table
	 *
	 * @since    3.3.0
	 * @access    public
	 */
	public function getError()
	{
		return $this->table->getError();
	}

	/**
	 * Determines if the ad is in a draft state
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isDraft()
	{
		return $this->table->state == SOCIAL_ADS_DRAFT;
	}

	/**
	 * Determines if the advertiser account is published
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isPublished()
	{
		return $this->table->state == SOCIAL_ADS_PUBLISHED;
	}

	/**
	 * Determines if the advertiser account is unpublished
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isUnpublished()
	{
		return $this->table->state == SOCIAL_ADS_UNPUBLISHED;
	}

	/**
	 * Determines if the ad is pending moderation
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isUnderModeration()
	{
		return $this->table->state == SOCIAL_ADS_MODERATION;
	}

	/**
	 * Notify admin when an ad is submitted for approvals
	 *
	 * @since	4.0.2
	 * @access	public
	 */
	public function notifyAdmin()
	{
		$params = [
			'author' => $this->getOwner()->getName(),
			'title' => $this->table->title,
			'thumbnail' => $this->getCover(),
			'intro' => $this->table->intro,
			'permalink' => ESR::ads(['external' => true], false),
			'manageLink' => rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_easysocial&view=ads&layout=pending'
		];

		ES::notifyAdmins('COM_ES_EMAILS_NEW_AD_MODERATION_SUBJECT', 'site/ads/moderate', $params);

		return true;
	}

	/**
	 * Publishes an ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function publish()
	{
		return $this->table->publish();
	}

	/**
	 * Rejects an ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function reject($message = '', $delete = false)
	{
		// Delete the ad if necessary
		if ($delete) {
			$this->table->delete();
		}

		// Notify advertiser that their ad is rejected
		$user = $this->getOwner();

		ES::notify('ads.approved', [$user->id], [
			'title' => 'COM_ES_ADS_AD_REJECTED',
			'template' => 'site/ads/rejected',
			'adTitle' => $this->table->title,
			'adThumbnail' => $this->getCover(),
			'adIntro' => $this->table->intro,
			'rejectedMessage' => $message,
			'permalink' => ESR::ads(['external' => true], false)
		], [], SOCIAL_NOTIFICATION_TYPE_EMAIL);

		// Since ad is already deleted, we do not need to do anything else here.
		if ($delete) {
			return $this;
		}

		$params = $this->getParams();
		$params->set('rejectMessage', $message);
		$params->set('rejectDate', JFactory::getDate()->toSql());

		$this->table->params = $params->toString();

		// Set the state back to draft
		$this->saveAsDraft();

		return $this;
	}

	/**
	 * Saves the data on the table
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function save()
	{
		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->table->cover) {
			$this->table->cover = '';
		}

		if (!$this->table->params) {
			$this->table->params = '';
		}

		if (!$this->table->log) {
			$this->table->log = '';
		}

		if (!$this->table->click) {
			$this->table->click = 0;
		}

		if (!$this->table->view) {
			$this->table->view = 0;
		}

		return $this->table->store();
	}

	/**
	 * Set the ad to draft mode
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function saveAsDraft()
	{
		$this->table->state = SOCIAL_ADS_DRAFT;
		$this->table->store();
	}

	/**
	 * Publishes an ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function unpublish()
	{
		return $this->table->unpublish();
	}

	/**
	 * Updates an existing ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function update($data, SocialAdvertiser $advertiser, $file)
	{
		// Ensure that the current viewer can truly edit this ad
		if (!$this->canEdit()) {
			return false;
		}

		// Default to the lowest priority when user creates a new ad
		$data['priority'] = SOCIAL_ADS_PRIORITY_LOW;

		// Update the moderation state
		$data['state'] = SOCIAL_ADS_MODERATION;

		$this->bind($data);

		$state = $this->table->store();

		// Upload the cover
		if (!empty($file['tmp_name'])) {
			$state = $this->table->uploadCover($file);
		}

		// Notify admin that the ad needs to be approved
		$this->notifyAdmin();

		return $state;
	}

	/**
	 * Uploads the cover for this ad
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function uploadCover($file)
	{
		if (!$this->table->id) {
			return false;
		}

		return $this->table->uploadCover($file);
	}
}
