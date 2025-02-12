<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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
	public function __construct($options = array())
	{
		parent::__construct($options);
	}

	/**
	 * Determines if the app has stream filter
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
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
	 * @since	1.0
	 * @param	string	The event name.
	 * @param	Array	An array of arguments
	 * @access	public
	 */
	public final function update($eventName, &$args)
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
	 * @param	string
	 * @return
	 */
	public function getStreamNotificationTargets($uid, $element, $group, $verb, $targets = array(), $exclusion = array())
	{
		// Get a list of people that also likes this
		$likes		= ES::likes($uid, $element, $verb, $group);
		$targets	= array_merge($targets, $likes->getParticipants(false));

		// Get people who are part of the comments
		$comments 	= ES::comments($uid, $element, $verb, $group);
		$targets 	= array_merge($targets, $comments->getParticipants(array(), false));

		// Remove exclustion
		$targets	= array_diff($targets, $exclusion);

		// Ensure that recipients are unique now.
		$targets 	= array_unique($targets);

		// Reset all indexes
		$targets 	= array_values($targets);

		return $targets;
	}

	/**
	 * Processes the stream action rules
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
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
	 * @since	1.2
	 * @access	public
	 * @param	string	The type of hook. Used for separating between different types of hooks "notifications", "triggers"
	 * @param	string	The name of the hook. Mostly for actions E.g: "comments", "likes"
	 * @return
	 */
	protected function getHook($type, $hook)
	{
		$file = strtolower($type) . '.' . strtolower($hook) . '.php';
		$path = $this->paths['hooks'] . '/' . $file;

		require_once($path);

		$className = 'Social' . ucfirst($this->group) . 'App' . ucfirst($this->element) . 'Hook' . ucfirst($type) . ucfirst($hook);

		$obj = new $className();

		return $obj;
	}
}
