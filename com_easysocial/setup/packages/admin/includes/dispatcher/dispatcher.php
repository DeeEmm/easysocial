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

ES::import('admin:/includes/themes/themes');

class SocialDispatcher
{
	private $adapters = [];
	private $observers = [];

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance()
	{
		static $obj = null;

		if (is_null($obj)) {
			$obj = new self();
		}

		return $obj;
	}

	/**
	 * Renders Joomla plugins
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadPlugins($group = 'easysocial')
	{
		// Import Joomla's plugins
		return JPluginHelper::importPlugin($group);
	}

	/**
	 * Single method to run specific triggers. Caller can specify callbacks which can be
	 * executed by the caller.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function trigger($group, $eventName, $args, $elements = null, $callbacks = array())
	{
		// Hot load this so that trigger caller doesn't need to load the apps
		ES::apps()->load($group);

		// Check if there's anything to load at all.
		if (!isset($this->observers[$group])) {
			return false;
		}

		// Get the list of observers
		$observers = $this->observers[$group];

		// If elements is an array, this means that
		// we only want to trigger those specific group of apps,
		// in that specific ordering as in that array.
		if (is_array($elements)) {

			$observers = array();

			foreach ($elements as $element) {
				if (isset($this->observers[$group][$element])) {
					$observers[] = $this->observers[$group][$element];
				}
			}
		}

		// Arguments must always be an array.
		if (!is_array($args)) {
			$args = array($args);
		}

		// Trigger Joomla plugins
		$this->loadPlugins();

		$jEventName = $eventName . ucfirst($group);

		$result = ESDispatcher::trigger($jEventName, $args);

		foreach ($observers as $observer) {
			// If the observer is not an instance of SocialAppItem, we just skip this.
			if (!$observer instanceof SocialAppItem) {
				continue;
			}

			// Execute any callback methods.
			if (!empty($callbacks)) {
				foreach ($callbacks as $callback => $value) {
					if (method_exists($observer, $callback)) {
						call_user_func_array(array($observer, $callback), array($value));
					}
				}
			}

			// Run the initial execution.
			$result[] = $observer->update($eventName, $args);
		}

		return $result;
	}

	/**
	 * Loads the language file for the app.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadLanguage($type, $element)
	{
		// Determine the key
		$key = $type . '_' . $element;

		// Load the language file for fields.
		$state	= JFactory::getLanguage()->load($key, JPATH_ROOT . '/administrator');

		return $state;
	}

	/**
	 * Allows caller to attach a list of observers.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function attach($type, $element, SocialAppItem $app)
	{
		// Only add it if it hasn't been added yet.
		if (!isset($this->observers[$type][$element])) {
			$this->observers[$type][$element] = $app;
		}
	}

	public function detach()
	{
	}
}
