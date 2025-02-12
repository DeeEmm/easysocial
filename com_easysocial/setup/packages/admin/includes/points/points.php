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

class SocialPoints extends EasySocial
{
	public static function getInstance()
	{
		static $instance = null;

		if (is_null($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Assign points to a specific user.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function assign($command, $extension, $userId)
	{
		// Check if points system is enabled.
		if (!$this->config->get('points.enabled')) {
			return false;
		}

		// If user id is empty or 0, we shouldn't assign anything
		if (!$userId) {
			return false;
		}

		// Check if the user really exists on the site
		$user = ES::user($userId);

		if (!$user->id) {
			return false;
		}

		$profile = $user->getProfile();

		// #2735 allows admin to define total points a user can achieve in a day
		$pointsLimit = $profile->getDailyPointsLimit();

		if ($pointsLimit) {
			$totalAchieved = $this->getTotalPointsAchievedToday($user);

			// Do not allow user to retrieve points if they reached their limit
			if ($totalAchieved >= $pointsLimit) {
				return false;
			}
		}

		// Retrieve the points table.
		$points = ES::table('Points');
		$state = $points->load([
			'command' => $command,
			'extension' => $extension
		]);

		// Check the command and extension and see if it is valid.
		if (!$state) {
			return false;
		}

		// Check the rule and see if it is published.
		if ($points->state != SOCIAL_STATE_PUBLISHED) {
			return false;
		}

		// @TODO: Check points threshold.
		if ($points->threshold) {
		}

		// @TODO: Check the interval to see if the user has achieved this for how many times.
		if ($points->interval != SOCIAL_POINTS_EVERY_TIME) {

			$model = ES::model('Points');
			$options = array('pointsId' => $points->id);
			$achieved = $model->getHistory($userId, $options);

			// do not proceed if user already achieved it more than interval specified.
			// Info: count(false) == 1
			if ($achieved !== false && (count($achieved) >= $points->interval)) {
				return false;
			}
		}

		// now check for daily interval limit
		if ($points->daily_interval != SOCIAL_POINTS_EVERY_TIME) {

			$model = ES::model('Points');
			$usage = $model->getDailyUsage($userId, $points->id);

			// do not proceed if user already achieved it more than interval specified.
			// Info: count(false) == 1
			if ($usage && ($usage >= $points->daily_interval)) {
				return false;
			}
		}

		// @TODO: Customizable point system where only users from specific profile type may achieve this point.
		$totalPointsToGive = $points->points;

		// trigger plugins to override points given if needed.
		$totalPointsToGive = $this->triggerPointsBeforeAssign($command, $extension, $userId, $totalPointsToGive);


		// Determines if we should minus the user's points until negative
		if (!$this->config->get('points.negative') && $points->isNegative()) {
			$userPoints = $user->getPoints();

			// Do not proceed if user points is already 0
			if ($userPoints <= 0) {
				return false;
			}

			$computedPoints = ($userPoints + $points->points);

			// If after minusing the points is lower than 0, we need to set the max deduction
			if ($computedPoints < 0) {
				$totalPointsToGive = ($userPoints * -1);
			}
		}

		// Add history.
		$history = ES::table('PointsHistory');
		$history->points_id = $points->id;
		$history->user_id = $userId;
		$history->points = $totalPointsToGive;
		$history->state = SOCIAL_STATE_PUBLISHED;
		$history->store();

		$this->updateUserPoints($userId, $totalPointsToGive, $command);

		// Add badges based on total points achieved from specific command rules
		$this->updateUserBadges($userId, $points, $command);

		// Assign a badge to the user for earning points.
		$badge = ES::badges();
		$badge->log('com_easysocial', 'points.achieve', $userId, JText::_('COM_EASYSOCIAL_POINTS_BADGE_EARNED_POINT'));

		return true;
	}

	/**
	 * Trigger plugins to override points before assign
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function triggerPointsBeforeAssign($command, $extension, $userId, $pointsToGive)
	{
		$totalPointsToGive = $pointsToGive;

		ESDispatcher::trigger('onEasySocialBeforePointsAssign', array($command, $extension, $userId, &$totalPointsToGive));

		// $totalPointsToGive = $totalPointsToGive * 2;

		return $totalPointsToGive;
	}



	/**
	 * Allows caller to assign a custom point
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assignCustom($userId, $points, $message = '', $extension = 'com_easysocial', $command = 'custom')
	{
		$history = ES::table('PointsHistory');
		$history->user_id = $userId;
		$history->points = $points;
		$history->state = SOCIAL_STATE_PUBLISHED;
		$history->message = $message;
		$history->points_id = 0;

		// Try to see if we are able to identify the rules that can be associated with the assignment
		$table = ES::table('Points');
		$options = array('command' => $command, 'extension' => $extension);
		$state = $table->load($options);

		if ($state) {
			$history->points_id = $table->id;
		}

		$state = $history->store();

		if ($state) {
			$this->updateUserPoints($userId, $points, $command);
		}

		return $state;
	}

	/**
	 * Allows 3rd party to discover rule files with the given path
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover($path)
	{
		if (!$path) {
			return false;
		}

		$model = ES::model('Points');
		$state = $model->install($path);

		return $state;
	}

	/**
	 * Retrieve the params of a specific points
	 *
	 * @since	1.2
	 * @access	public
	 */
	public static function getParams($command, $extension)
	{
		$table = ES::table('Points');
		$table->load(array('command' => $command, 'extension' => $extension));

		$params = $table->getParams();

		return $params;
	}

	/**
	 * Retrieves total number of points a user achieved in a day
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTotalPointsAchievedToday(SocialUser $user)
	{
		static $users = array();

		if (!isset($users[$user->id])) {
			$today = ES::date();

			$model = ES::model('Points');
			$users[$user->id] = $model->getTotalPointsAchieved($user, $today);
		}

		return $users[$user->id];
	}

	/**
	 * Updates the cache copy of the user's points.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function updateUserPoints($userId , $points, $command = '')
	{
		ES::apps()->load(SOCIAL_TYPE_USER);

		$user = ES::user($userId);
		$args = array(&$user, &$points, $command);

		$dispatcher = ES::dispatcher();

		// @trigger onBeforeAssignPoints
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onBeforeAssignPoints', $args);

		// Add points for the user
		$user->addPoints($points);

		// @trigger onAfterAssignPoints
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onAfterAssignPoints', $args);

		return true;
	}

	/**
	 * Updates badge for user when the points reached the threshold
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function updateUserBadges($userId, $points, $command = '')
	{
		if (!$points) {
			return;
		}

		// Get list of badges based on command type
		$options = array('achieveType' => 'points', 'pointsRuleId' => $points->id, 'state' => SOCIAL_STATE_PUBLISHED);

		$badgesModel = ES::model('Badges');
		$badges = $badgesModel->getItems($options);

		// Nothing to process
		if (empty($badges)) {
			return true;
		}

		$pointsModel = ES::model('Points');

		// Get total accumulation points from all badges
		$increaseArray = array();
		$decreaseArray = array();

		// There are instances the points is associated with multiple badges.
		foreach ($badges as $badge) {
			if ($badge->points_increase_rule) {
				$increaseArray[] = $badge->points_increase_rule;
			}

			if ($badge->points_decrease_rule) {
				$decreaseArray[] = $badge->points_decrease_rule;
			}
		}

		// Compute all the points
		$overallIncreasePoints = $pointsModel->getPointsById($userId, $increaseArray);
		$overallDecreasePoints = $pointsModel->getPointsById($userId, $decreaseArray);

		foreach ($badges as $badge) {

			$pointsIncrease = 0;
			$pointsDecrease = 0;

			if (isset($overallIncreasePoints[$badge->points_increase_rule])) {
				$pointsIncrease = $overallIncreasePoints[$badge->points_increase_rule];
			}

			if (isset($overallDecreasePoints[$badge->points_decrease_rule])) {
				$pointsDecrease = $overallDecreasePoints[$badge->points_decrease_rule];
			}

			// Compute the points
			$totalPoints = $pointsIncrease + $pointsDecrease;

			// We need to check for the achieve rule is it positive or negative
			$tablePoints = ES::table('Points');
			$tablePoints->load($badge->points_increase_rule);

			$threshold = $badge->points_threshold;

			// Convert to negative value
			if ($tablePoints->points < 0) {
				$threshold = -1 * abs($threshold);

				// Remove badges if total points is greater than negative threshold value
				// eg: totalpoints = -25 and threshold is -50, total points is greater than threshold
				if ($totalPoints > $threshold) {
					$lib = ES::badges();
					$lib->remove($badge->id, $userId);
				}

				// Add badge if total points is lesser than negative threshold value
				// eg: totalpoints = -55 and threshold is -50
				if ($totalPoints <= $threshold) {
					$lib = ES::badges();
					$lib->create($badge, ES::user($userId));
				}
			} else {

				// Remove badges if necessary
				if ($totalPoints < $badge->points_threshold) {
					$lib = ES::badges();
					$lib->remove($badge->id, $userId);
				}

				// Add badge
				if ($totalPoints >= $badge->points_threshold) {
					$lib = ES::badges();
					$lib->create($badge, ES::user($userId));
				}
			}
		}

		return true;
	}

	/**
	 * Allows caller to reset points for a given user
	 *
	 * @since	1.4.7
	 * @access	public
	 */
	public function reset($userId)
	{
		$model = ES::model('Points');
		$state = $model->reset($userId);

		return $state;
	}

	/**
	 * Determine if the user has achieved the given point command or not
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function hasAchievedBefore($command, $extension , $userId)
	{
		$model = ES::model('Points');
		$hasAchievedBefore = $model->hasAchievedBefore($command, $extension , $userId);

		return $hasAchievedBefore;
	}
}
