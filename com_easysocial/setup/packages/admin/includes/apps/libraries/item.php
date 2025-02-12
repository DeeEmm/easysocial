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

require_once(__DIR__ . '/abstract.php');

class SocialAppItem extends SocialAppsAbstract
{
	static $notificationCache = array();
	static $counter = 0;

	/**
	 * For now, this method can only process likes and comments notification.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		$key = $item->id;
		if (isset(self::$notificationCache[$key])) {
			// item already process
			return;
		}

		$commmentsAllowed = array('comments.item', 'comments.involved', 'comments.tagged', 'comments.like');
		$likesAllowed = array('likes.item', 'likes.involved');

		// we only process likes and comments notification here.
		if (!in_array($item->cmd, $commmentsAllowed) && !in_array($item->cmd, $likesAllowed)) {
			return;
		}

		// mark this item as processed.
		self::$notificationCache[$key] = 1;

		if (in_array($item->cmd, $commmentsAllowed)) {

			$hook = $this->getHook('notification', 'comments', true);
			$hook->execute($item);

			return;
		}

		if (in_array($item->cmd, $likesAllowed)) {
			$hook = $this->getHook('notification', 'likes', true);
			$hook->execute($item);

			return;
		}

		return;
	}

	/**
	 * Determines if the app has stream filter
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function hasStreamFilter()
	{
		$params = $this->getApp()->getParams();

		$filter = $params->get('stream_filter', true);

		if ($filter) {
			return true;
		}

		return false;
	}

	/**
	 * Executes when a trigger is called.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function update($eventName, &$args)
	{
		if (method_exists($this, $eventName)) {
			return call_user_func_array(array($this, $eventName), $args);
		}

		return false;
	}

	/**
	 * Retrieves a list of notification targets
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getStreamNotificationTargets($uid, $element, $group, $verb, $targets = array(), $exclusion = array())
	{
		// Get people who are part of the comments
		$comments = ES::comments($uid, $element, $verb, $group);

		// Check if this stream belong to a stream.
		$streamId = $comments->stream_id;

		// Update the notification exlusion. #4648
		if ($streamId) {
			$stream = ES::table('stream');
			$stream->load($streamId);

			$exclusion = $this->getStreamExcludeNotificationTarget($stream, $exclusion);
		}

		$targets = array_merge($targets, $comments->getParticipants(array(), false));

		// Remove exclustion
		$targets = array_diff($targets, $exclusion);

		// Ensure that recipients are unique now.
		$targets = array_unique($targets);

		// Reset all indexes
		$targets = array_values($targets);

		return $targets;
	}

	/**
	 * Processes the stream action rules
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function processStream(SocialStreamItem &$item)
	{
		// Get the current verb of the stream item
		$verb = $item->verb;

		// Get the path to the file
		$file = strtolower($verb) . '.php';
		$path = $this->paths['streams'] . '/' . $file;

		require_once($path);

		// Build the class name
		$class = 'Social' . ucfirst($this->group) . 'App' . ucfirst($this->element) . 'Stream' . ucfirst($verb);

		// Get the app params
		$app = $this->getApp();
		$params = $app->getParams();

		$options = array('group' => $this->group, 'element' => $this->element);

		$obj = new $class($options);
		$obj->execute($item, $params);

		return $obj;
	}

	/**
	 * Retrieves the trigger object for an app
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	protected function getHook($type, $hook, $system = false)
	{
		$file = strtolower($type) . '.' . strtolower($hook) . '.php';
		$path = $this->paths['hooks'] . '/' . $file;

		$className = 'Social' . ucfirst($this->group) . 'App' . ucfirst($this->element) . 'Hook' . ucfirst($type) . ucfirst($hook);

		// Include the main hooks base
		require_once(__DIR__ . '/hooks.php');

		if ($system) {
			$file = strtolower($type) . '.' . strtolower($hook) . '.php';
			$path = SOCIAL_LIB . '/apps/hooks/' . $file;
			$className = 'SocialAppHook' . ucfirst($type) . ucfirst($hook);
		}

		require_once($path);

		$obj = new $className();
		return $obj;
	}

	/**
	 * Method to retrieve the target exclusion for notification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getStreamExcludeNotificationTarget(SocialTableStream $stream, $excludeId = array())
	{
		// Exclude list of blocked user by the owner of the stream since they cannot view the rest of stream. #4648
		$blockModel = ES::model('blocks');
		$blockedUsers = $blockModel->getBlockedUsers($stream->actor_id);
		$blockedIds = array();

		if ($blockedUsers) {
			foreach ($blockedUsers as $blocked) {
				$user = $blocked->user;
				$blockedIds[] = $user->id;
			}
		}

		$exclude = array_merge($excludeId, $blockedIds);
		$exclude = array_unique($exclude);

		return $exclude;
	}
}
