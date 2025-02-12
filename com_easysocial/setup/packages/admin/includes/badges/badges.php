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

class SocialBadges extends EasySocial
{
	public static function getInstance()
	{
		static $instance = null;

		if ($instance === null) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Log user action
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function log($extension, $command, $userId, $message)
	{
		if (!$this->config->get('badges.enabled')) {
			return false;
		}

		// If user id is not provided we shouldn't log anything
		if (!$userId) {
			return false;
		}

		// Load the user object
		$user = ES::user($userId);

		// Translate the message
		$message = JText::_($message);

		// Do not assign badge history to unknown or guest users.
		if (!$user->id || $user->guest) {
			return false;
		}

		// Load up the table
		$badge = ES::table('Badge');
		$state = $badge->load(array('extension' => $extension, 'command' => $command));

		// Badge needs to be published.
		// If the extension / command does not exist, quit this.
		if (!$state || !$badge->state) {
			return false;
		}

		// if badges is configured to only be achievable by points, skip this.
		if ($badge->achieve_type != 'frequency') {
			return false;
		}

		// Load badges model
		$model = ES::model('Badges');

		// Check if the user reached the specified frequency already or not.
		$achieving = $model->hasReachedFrequency($badge->id, $userId);
		$achieved = $model->hasAchieved($badge->id, $userId);

		// If the frequency of the badge is only 1, the achieving will not return anything.
		if ($badge->frequency == 1 && !$achieved) {
			$achieving 	= true;
		}

		$log = ES::table('BadgeHistory');
		$log->badge_id = $badge->id;
		$log->user_id = $user->id;
		$log->achieved = $achieving && !$achieved;

		// Try to store the history action.
		$state = $log->store();

		// Only add a badge for this user when they have never achieved it before.
		if ($achieving && !$achieved) {

			// Create the new badge maps
			$state = $this->create($badge, $user);
		}

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user has already earned this badge
	 *
	 * @since	1.4.9
	 * @access	public
	 * @param	int		The user's id
	 * @param	int		The badge id.
	 * @return	bool	True if exists, false otherwise
	 */
	public function exists($userId, $badgeId)
	{
		// Load up the badge model
		$model = ES::model('Badges');

		$exists = $model->exists($userId, $badgeId);

		return $exists;
	}

	/**
	 * Delete a badge from user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function remove($badgeId, $userId = null)
	{
		// Load up the badge model
		$model = ES::model('Badges');

		// Removes the history for this badge
		$state = $model->deleteHistory($badgeId, $userId);

		// Removes the mapping for this badge
		$state = $model->deleteAssociations($badgeId, $userId);

		return true;
	}

	/**
	 * Allow Caller to manually create the badge
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function create(SocialTableBadge $badge, SocialUser $user, $customMessage = '', $achieved = '', $publishStream = true)
	{
		// Check if the badge already exists. If it already exists, do not assign it again.
		$exists = $this->exists($user->id, $badge->id);

		if ($exists) {
			return false;
		}

		// Create the new badge maps
		$mapping = ES::table('BadgeMap');
		$mapping->badge_id = $badge->id;
		$mapping->user_id = $user->id;
		$mapping->custom_message = (string) $customMessage;

		if (!empty($achieved)) {
			$date = ES::date($achieved);
			$mapping->created = $date->toSql();
		}

		$state = $mapping->store();

		if (!$state) {
			return false;
		}

		// Send a notification to the user when they achieved a badge.
		$this->sendNotification($badge, $user->id);

		if ($publishStream) {
			// Log stream here that the user achieved a new badge.
			$this->addStream($badge, $user->id);
		}

		// Add points for the user when they achieve a badge.
		$points = ES::points();
		$points->assign('badges.achieve', 'com_easysocial', $user->id);

		return true;
	}

	/**
	 * Responsible to create a new stream item when user unlocked a badge.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addStream(SocialTableBadge $badge, $userId)
	{
		$stream = ES::stream();
		$exists = $stream->exists($badge->id, 'badges', 'unlocked', $userId);

		if ($exists) {
			return;
		}

		$streamTemplate = $stream->getTemplate();

		// Set the actor.
		$streamTemplate->setActor($userId, SOCIAL_TYPE_USER);

		// Set the context.
		$streamTemplate->setContext($badge->id, SOCIAL_TYPE_BADGES);

		// Set the verb.
		$streamTemplate->setVerb('unlocked');

		// set the ispublic
		$streamTemplate->setAccess('core.view');

		// Set the params for the badge
		$streamTemplate->setParams($badge);

		// Create the stream data.
		$stream->add($streamTemplate);
	}

	/**
	 * Send notification to the user when they achieved a badge
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sendNotification(SocialTableBadge $badge, $userId)
	{
		// Load the language file from the front end too since badge titles are loaded from the back end language
		ES::language()->loadAdmin();

		// We need the language file from the front end
		ES::language()->loadSite();

		// We want to send a notification to the user who earned the badge
		$recipient = array($userId);

		// Add notification to the requester that the user accepted his friend request.
		$systemOptions = array(
							'uid' => $badge->id,
							'type' => SOCIAL_TYPE_BADGES,
							'actor_id' => $userId,
							'url' => ESR::badges(array('id' => $badge->getAlias(), 'layout' => 'item', 'sef' => false)),
							'image' => $badge->getAvatar(true)
						);

		$params = array(
					'badgeTitle' => $badge->get('title'),
					'badgePermalink' => $badge->getPermalink(false, true),
					'badgeAvatar' => $badge->getAvatar(),
					'badgeDescription' => $badge->get('description')
				);

		// Email template
		$emailOptions = array(
						'title' => 'COM_EASYSOCIAL_EMAILS_UNLOCKED_NEW_BADGE_SUBJECT',
						'badge' => $badge->get('title'),
						'template' => 'site/badges/unlocked',
						'params' => $params
					);

		// Send notifications to the receivers when they unlock the badge
		ES::notify('badges.unlocked', $recipient, $emailOptions, $systemOptions);
	}
}
