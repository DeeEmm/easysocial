<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(__FILE__) . '/dependencies.php');

class SocialNotification extends JObject
{
	static $instance = null;

	/**
	 * The notification class is always a singleton object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {

			// Just to be sure that the language files on the front end is loaded
			ES::language()->loadSite();

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Creates a new notification item.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function create(SocialNotificationTemplate $template)
	{
		// Load the Notification table
		$table = ES::table('Notification');

		$dispatcher = ES::getInstance('Dispatcher');

		// Notification aggregation will only happen if there is the same `uid`,`type`
		if ($template->aggregate) {

			// Load any existing records to see if it exists.
			$type = $template->type;
			$uid = $template->uid;
			$targetId = $template->target_id;
			$targetType = $template->target_type;
			$contextType = $template->context_type;
			$command = $template->cmd;

			$options = array('uid' => $uid, 'type' => $type, 'target_id' => $targetId, 'target_type' => $targetType, 'context_type' => $contextType, 'cmd' => $command);
			$exists = $table->load($options);

			// If it doesn't exist, go through the normal routine of binding the item.
			if (!$exists) {
				$table->bind($template);
			} else {

				if (!empty($template->title)) {
					$table->title = $template->title;
				}

				if (!empty($template->content)) {
					$table->content = $template->content;
				}

				if (!empty($template->actor_id)) {
					$table->actor_id = $template->actor_id;
				}

				if (!empty($template->actor_type)) {
					$table->actor_type = $template->actor_type;
				}

				if (!empty($template->target_type)) {
					$table->target_type = $template->target_type;
				}

				// Reset to unread state since this is new.
				$table->state = SOCIAL_NOTIFICATION_STATE_UNREAD;
			}

			// Update this item to the latest since we want this to appear in the top of the list.
			$table->created	= ES::date()->toMySQL();
		} else {
			$table->bind($template);
		}

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$table->content) {
			$table->content = '';
		}

		if (!$table->context_ids) {
			$table->context_ids = '';
		}

		if (!$table->image) {
			$table->image = '';
		}

		if (!$table->context_type) {
			$table->context_type = '';
		}

		$args = array(&$table, &$template);

		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onSystemNotificationBeforeCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onSystemNotificationBeforeCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onSystemNotificationBeforeCreate', $args);

		$state = $table->store();

		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onSystemNotificationAfterCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onSystemNotificationAfterCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onSystemNotificationAfterCreate', $args);

		if (!$state) {
			$this->setError($table->getError());
			return false;
		}



		return true;
	}

	/**
	 * Generates a new notification object template.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTemplate()
	{
		$template = new SocialNotificationTemplate();

		return $template;
	}

	/**
	 * Marks an item as read
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function read($id)
	{
		$table = ES::table('Notification');
		$table->load($id);

		return $table->markAsRead();
	}

	/**
	 * Delete all notification from the system for a particular user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteAll($userId = null)
	{
		$model = ES::model('Notifications');
		$result = $model->setAllState('clear');

		return $result;
	}

	/**
	 * Deletes a notification item from the site.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function delete($id)
	{
		$table = ES::table('Notification');
		$table->load($id);

		$state = $table->delete();

		return $state;
	}

	/**
	 * Hide's notification item but not delete. Still visible when viewing all notification items.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function hide($id)
	{
		$table = ES::table('Notification');
		$table->load($id);

		return $table->markAsHidden();
	}

	/**
	 * Retrieves the notification output.
	 *
	 * @since	1.0.0
	 * @access	public
	 */
	public function toHTML($userId)
	{
		$model = ES::model('Notifications');

		// Get the list of notification items
		$options = array('user_id' => $userId);
		$items = $model->getItems($options);

		if (!$items) {
			return false;
		}

		// Retrieve applications and trigger onNotificationLoad
		$dispatcher = ES::getInstance('Dispatcher');

		$result = array();

		// Trigger apps
		foreach ($items as $item) {

			$type = $item->type;
			$args = array(&$item);

			// @trigger onNotificationLoad from user apps
			$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onNotificationLoad', $args, $type);

			// @trigger onNotificationLoad from group apps
			$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onNotificationLoad', $args, $type);

			// @trigger onNotificationLoad from event apps
			$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onNotificationLoad', $args, $type);

			// @trigger onNotificationLoad from page apps
			$dispatcher->trigger(SOCIAL_APPS_GROUP_PAGE, 'onNotificationLoad', $args, $type);

			// If an app lets us know that they want to exclude the stream, we should exclude it.
			if (isset($item->exclude) && $item->exclude) {
				continue;
			}

			$result[] = $item;
		}

		$theme = ES::themes();
		$theme->set('items', $result);

		return $theme->output('site/notifications/default/default');
	}

	/**
	 * Retrieves a list of notification items.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems(&$options = array())
	{
		$model = ES::model('Notifications');
		$items = $model->getItems($options);

		if (!$items) {
			return false;
		}

		// Retrieve applications and trigger onNotificationLoad
		$dispatcher = ES::dispatcher();

		$defaultEvent = 'onNotificationLoad';
		$viewer = ES::user();

		$result = array();

		// Trigger apps
		foreach ($items as $item) {

			// Add a `since` column to the result so that user's could use the `since` time format.
			$item->since= ES::date($item->created)->toLapsed();

			$isRest = isset($options['rest']) && $options['rest'];

			// Rest notification trigger
			if ($isRest) {
				$defaultEvent = 'onPrepareRestNotification';
				$viewer = isset($options['restViewer']) ? $options['restViewer'] : $viewer;

				// Predefined properties
				$target = new stdClass();
				$target->id = ''; // The id of the target
				$target->type = ''; // The type of the target
				$target->endpoint = ''; // Endpoint use to reach the target. eg, stream.item
				$target->query_string = ''; // String that consist of both endpoint and the id of the target. eg, stream.item&id=1
				$target->nav = ''; // A method to directly route the user to specific page within the endpoint. eg, group.reviews
				$target->nav_id = ''; // Navigation id if exists. eg: id of the photo within an album

				$item->target = $target;
			}

			$args = array(&$item, $viewer);

			$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, $defaultEvent, $args);
			$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, $defaultEvent, $args);
			$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, $defaultEvent, $args);
			$dispatcher->trigger(SOCIAL_APPS_GROUP_PAGE, $defaultEvent, $args);

			// If an app lets us know that they want to exclude the stream, we should exclude it.
			if (isset($item->exclude) && $item->exclude) {
				continue;
			}

			if ($isRest && !$item->target->id && !$item->target->type) {
				$args = array(&$item, $viewer);

				$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onNotificationLoad', $args);
				$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onNotificationLoad', $args);
				$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onNotificationLoad', $args);
				$dispatcher->trigger(SOCIAL_APPS_GROUP_PAGE, 'onNotificationLoad', $args);

				$item->target->type = 'external';

				if (stristr($item->url , 'http://') === false && stristr($item->url , 'https://') === false) {
					$subFolder = JURI::root(true);
					$item->url = ltrim($item->url, '/');

					if ($subFolder) {
						$subFolder = ltrim($subFolder, '/');
						$parts = explode('/', $item->url);

						// Determine if the source already included the sub folder
						if ($parts[0] !== $subFolder) {
							$item->url = rtrim(JURI::root(), '/') . '/' . $item->url;
						} else {
							$uri = JURI::getInstance();
							$root = $uri->toString(array('scheme', 'host'));
							$root = rtrim($root, '/');

							if (isset($uri->port) && $uri->port) {
								$root = $root . '/' . ltrim($uri->port, '/');
							}

							$item->url = $root . '/' . $item->url;
						}

					} else {
						$item->url = rtrim(JURI::root(), '/') . '/' . $item->url;
					}
				}

				$item->target->query_string = $item->url;
			}

			// Let's format the item title.
			$this->formatItem($item, $isRest);

			$result[] = $item;
		}

		// If there is no result here means that the app might not be supported. Let's rerun it
		if (!$result) {

			// Max 10 retry
			if (isset($options['retry']) && $options['retry'] < 10) {
				$options['startlimit'] = $options['startlimit'] + $options['limit'];
				$options['retry'] = isset($options['retry']) ? $options['retry'] + 1 : 1;

				$this->getItems($options);
			}
		}

		// Group up items.
		if (isset($options['group']) && $options['group'] == SOCIAL_NOTIFICATION_GROUP_ITEMS) {
			$result = $this->group($result);
		}

		return $result;
	}


	public function getItemsTotal($options = array())
	{
		$model = ES::model('Notifications');

		$notificationOptions = array('target' => array('id' => $options['target_id'], 'type' => $options['target_type']));
		$count = $model->getCount($notificationOptions);

		return $count;
	}

	/**
	 * Method to retrieve total unread
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTotalUnread($options = array())
	{
		$model = ES::model('Notifications');

		$notificationOptions = array('target' => array('id' => $options['target_id'], 'type' => $options['target_type']));
		$notificationOptions['unread'] = true;

		$count = $model->getCount($notificationOptions);

		return $count;
	}

	/**
	 * Format the notification title
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function formatItem(&$item, $isRest = false)
	{
		if (!$isRest) {
			// Escape the original title first.
			$item->title = ES::string()->escape($item->title);
		}

		// We have our own custom tags
		$item->title = $this->formatKnownTags($item->title);

		// Replace actor first.
		$item->title = $this->formatActor($item->title, $item->actor_id, $item->actor_type);

		// Replace target.
		$item->title = $this->formatTarget($item->title, $item->target_id, $item->target_type);

		// Replace variables from parameters.
		$item->title = $this->formatParams($item->title, $item->params);

		// Get the icon of this app if needed.
		$item->icon = $this->getIcon($item);

		// Set the actor
		$item->user = $item->getActorAlias();

		// Legacy fix prior to 2.0, actor_id may contain 0
		if (!$item->user && $item->target_type == SOCIAL_TYPE_USER) {
			$item->user = ES::user($item->target_id);
		}
	}

	public function formatKnownTags($title)
	{
		$title = str_ireplace('{b}', '<b>', $title);
		$title = str_ireplace('{/b}', '</b>', $title);

		return $title;
	}

	/**
	 * Retrieves the icon for this notification item.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIcon(&$item)
	{
		$obj = ES::makeObject($item->params);

		if (isset($obj->icon)) {
			return $obj->icon;
		}

		// @TODO: Return a default notification icon.

		return false;
	}

	/**
	 * Replaces {ACTOR} with the proper actor data.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function formatParams($content , $params)
	{
		$obj = ES::makeObject($params);

		if ($obj) {
			$keys = get_object_vars($obj);

			if ($keys) {
				foreach ($keys as $key => $value) {
					$content = str_ireplace('{%' . $key . '%}' , $value , $content);
				}
			}
		}

		return $content;
	}

	/**
	 * Replaces {ACTOR} with the proper actor data.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function formatActor($content, $actorId, $actorType = SOCIAL_TYPE_USER)
	{
		// @TODO: Actor might not necessarily be a user.
		$actor = ES::user($actorId, true);

		$theme = ES::themes();
		$theme->set('title', $actor->getName());
		$theme->set('link', $actor->getPermalink());

		$content = str_ireplace('{ACTOR}', $theme->output('site/notifications/actor/default'), $content);

		return $content;
	}

	/**
	 * Replaces {TARGET} with the proper actor data.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function formatTarget($content, $targetId, $targetType = SOCIAL_TYPE_USER)
	{
		$output = '';

		// Get the current logged in user.
		if ($targetType == SOCIAL_TYPE_USER) {
			$target = ES::user($targetId);

			$theme = ES::themes();
			$theme->set('title', $target->getStreamName());
			$theme->set('link', $target->getPermalink());

			$output = $theme->output('site/notifications/target/default');
		}

		$content = str_ireplace('{TARGET}', $output, $content);

		return $content;
	}

	/**
	 * Group up items by days
	 *
	 * @since	1.0
	 * @access	private
	 *
	 */
	private function group(&$items, $dateFormat = '')
	{
		$result	= array();

		foreach ($items as $item) {

			$today = ES::date();
			$date = ES::date($item->created);

			if ($today->format('j/n/Y') == $date->format('j/n/Y')) {
				$index = JText::_('COM_EASYSOCIAL_NOTIFICATION_TODAY');
			} else {
				$index = $date->format(JText::_('COM_EASYSOCIAL_NOTIFICATION_DATE_FORMAT'));
			}

			if (!isset($result[$index])) {
				$result[$index] = array();
			}

			$result[$index][] = $item;
		}

		return $result;
	}

	/**
	 * Retrieves the notification output in JSON format.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function toJSON()
	{
	}
}
