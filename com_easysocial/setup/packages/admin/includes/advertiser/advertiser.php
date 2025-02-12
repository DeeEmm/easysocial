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

class SocialAdvertiser
{
	private $table = null;

	public function __construct($id = null)
	{
		$this->config = ES::config();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;

		$this->table = ES::table('Advertiser');

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
	 * Approves an advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function approve()
	{
		$this->table->state = SOCIAL_ADS_PUBLISHED;
		$this->table->store();

		// Send notification to ad creator
		$user = $this->getUser();

		// Notify owner
		ES::notify('ads.advertiser.approved', [$user->id], [
			'title' => 'COM_ES_ADS_ADVERTISER_APPROVED',
			'template' => 'site/ads/advertiser.approved',
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
	 * Determines if the user can request creation for an advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canCreate()
	{
		// @TODO: Check if the user is allowed to create

		return true;
	}

	/**
	 * Creates a new advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function create($company, $logo, $userId = null, $state = SOCIAL_ADS_MODERATION)
	{
		// Check if the user already has an account
		if ($this->hasAccount()) {
			return false;
		}

		$table = ES::table('Advertiser');
		$table->name = $company;
		$table->state = $state;
		$table->created = ES::date()->toSql();
		$table->logo = '';

		if ($userId) {
			$table->user_id = $userId;
		}

		$state = $table->store();

		if (!empty($logo['tmp_name'])) {
			$state = $table->uploadLogo($logo);
		}

		$this->table = $table;

		$this->notifyAdmin();

		return $this;
	}

	/**
	 * Retrieves the logo for the advertiser
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getCompanyName()
	{
		return $this->table->name;
	}

	/**
	 * Retrieves the logo for the advertiser
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getLogo()
	{
		return $this->table->getLogo();
	}

	/**
	 * Retrieves the user account associated with this advertising account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getUser()
	{
		$user = ES::user($this->user_id);

		return $user;
	}

	/**
	 * Determines if a user has an advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function hasAccount()
	{
		// @TODO: Check if the user already has an account
		return false;
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
		return !$this->isPublished();
	}

	/**
	 * Determines if the advertiser is in a draft state
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isDraft()
	{
		return $this->table->state == SOCIAL_ADS_DRAFT;
	}

	/**
	 * Determines if the advertiser account is pending moderation
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isUnderModeration()
	{
		return $this->table->state == SOCIAL_ADS_MODERATION;
	}

	/**
	 * Notify admin when an advertiser account is submitted for approvals
	 *
	 * @since	4.0.2
	 * @access	public
	 */
	public function notifyAdmin()
	{
		$params = [
			'author' => $this->getUser()->getName(),
			'title' => $this->table->name,
			'thumbnail' => $this->getLogo(),
			'manageLink' => rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_easysocial&view=ads&layout=pendingAdvertisers'
		];

		ES::notifyAdmins('COM_ES_EMAILS_NEW_ADVERTISER_MODERATION_SUBJECT', 'site/ads/advertiser.moderate', $params);


		return true;
	}

	/**
	 * Rejects an advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function reject()
	{
		// Delete the advertiser request
		$this->table->delete();

		$user = $this->getUser();

		// Send notification to the user that their request has been rejected
		ES::notify('ads.advertiser.rejected', [$user->id], [
			'title' => 'COM_ES_ADS_ADVERTISER_REJECTED',
			'template' => 'site/ads/advertiser.rejected',
			'permalink' => ESR::advertiser(['external' => true, 'layout' => 'form'], false)
		], [], SOCIAL_NOTIFICATION_TYPE_EMAIL);

		return $this;
	}

	/**
	 * Set the advertiser account to draft mode
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
	 * Saves the data on the table
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function save()
	{
		return $this->table->store();
	}

	/**
	 * Updates an existing advertiser account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function update($company, $logo, $userId = null)
	{
		$this->table->name = $company;

		// Set the advertiser account as under moderation
		$this->table->state = SOCIAL_ADS_MODERATION;

		if ($userId) {
			$this->table->user_id = $userId;
		}

		$state = $this->table->store();

		if (!empty($logo['tmp_name'])) {
			$state = $this->table->uploadLogo($logo);
		}

		// Since the user is updating their advertising account, all their ads should be unpublished
		$model = ES::model('Ads');
		$model->updateAdvertiserAdsToModeration($this->id);

		$this->notifyAdmin();

		return $this;
	}

	/**
	 * Given the FILES data, save the cover image for the advertiser
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function uploadLogo($file)
	{
		return $this->table->uploadLogo($file);
	}

	/**
	 * Retrieve the error of the advertiser table
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getError()
	{
		return $this->table->getError();
	}
}
