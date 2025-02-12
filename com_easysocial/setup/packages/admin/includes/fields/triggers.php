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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

/**
 * List of known triggers
 *
 * onCronExecute
 * onIndexer
 * onIndexerSearch
 * onFriendSuggestSearch
 * onOAuthGetMetaFields
 * onOAuthGetUserPermission
 * onOAuthGetUserMeta
 */

// Include abstract class so that it would be visible to the fields.
ES::import( 'admin:/includes/fields/dependencies' );

class SocialFieldTriggers
{
	/**
	 * Stores a list of field items that are already loaded.
	 * @var	Array
	 */
	private $loaded = null;

	/**
	 * Stores the current event
	 * @var String
	 */
	private $event = null;

	/**
	 * Stores the field library
	 * @var SocialFields
	 */
	private $lib = null;

	/**
	 * Stores the general field handler class
	 * @var SocialFieldHandlers
	 */
	private $handler = null;

	/**
	 * JSON library.
	 * @var SocialJSON
	 */
	private $json = null;

	/**
	 * Overriding parameters
	 * @var array
	 */
	private $params = array();

	/**
	 * Target user
	 * @var SocialUser
	 */
	private $user = null;

	/**
	 * Class constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct($params = array())
	{
		// Helper json library.
		$this->json = ES::json();

		// Field library
		$this->lib = ES::fields();

		// Field handlers
		$this->handler = $this->lib->getHandler();

		// Init params
		$this->init($params);
	}

	/**
	 * Inits some override params to pass to the triggerer/field
	 * This is to have a master switch for certain parameter
	 *
	 * @since  1.2
	 * @access public
	 */
	public function init($params = array())
	{
		$this->params = array_merge($this->params, $params);
	}

	/**
	 * This is to set the target user that the fields is acting on
	 *
	 * @since  1.2
	 * @access public
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * Responsible to attach the list of field apps into the dispatcher object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($fieldGroup, &$fields)
	{
		// If there is no fields, there's no point running them at all.
		if (!$fields) {
			return false;
		}

		// Final result
		$finalItems = array();

		// Go through each of the fields.
		foreach ($fields as $field) {
			// Set the key.
			$element = $field->element;

			// Load the language file for this field.
			// $languageElement = 'plg_fields_' . $fieldGroup . '_' . $element;
			// JFactory::getLanguage()->load( $languageElement , JPATH_ROOT . '/administrator' );
			// $field->loadLanguage();

			// If field is already loaded, ignore and continue.
			if (isset($this->loaded[$fieldGroup][$element]) && $this->loaded[$fieldGroup][$element] !== false) {
				// If he field has already been loaded, add them to the final items.
				$finalItems[] = $this->loaded[$fieldGroup][$element];
				continue;
			}

			// Get the file path
			$filePath = SOCIAL_APPS . '/' . SOCIAL_APPS_TYPE_FIELDS . '/' . $fieldGroup . '/' . $element . '/' . $element . '.php';


			// If file doesn't exist, ignore this
			if (!JFile::exists($filePath)) {
				$this->loaded[$fieldGroup][$element] = false;
				continue;
			}

			// Include the fields file.
			include_once($filePath);

			// Build the class name.
			$className = 'SocialFields' . ucfirst($fieldGroup) . ucfirst($field->element);

			// If the class doesn't exist in this context, skip the whole loading.
			if (!class_exists($className)) {
				$this->loaded[$fieldGroup][$element] = false;
				continue;
			}

			// Initialize configuration.
			$config = array('element' => $field->element, 'group' => $fieldGroup);

			// Instantiate the new object here.
			$fieldObj = new $className($config);

			// If the class is not part of our package, skip this.
			if (!($fieldObj instanceof SocialFieldItem)) {
				// @TODO: Log error when class is not part of the package.
				$this->loaded[$fieldGroup][$field->element] = false;
				continue;
			}

			// Add this to the property so we know that it wouldn't get executed again.
			$this->loaded[$fieldGroup][$field->element] = $fieldObj;

			// Assign the field object to the final items.
			$finalItems[] = $fieldObj;
		}

		return $finalItems;

		// // Only people has this app.
		// ES::getInstance( 'Apps' )->load( SOCIAL_APPS_TYPE_FIELDS );
	}

	/**
	 * Set the triggered event name
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setEvent($event)
	{
		$this->event = $event;
	}

	/**
	 * Get the triggered event name
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getEvent()
	{
		return $this->event;
	}

	private function onEvent($fieldGroup, &$fields, &$data = array(), $callback = null)
	{
		// If there is no event assigned, then don't proceed
		if (empty($this->event)) {
			return false;
		}

		// If no fields are given, then don't proceed
		if (!$fields) {
			return false;
		}

		// Init the user if no target user is provided
		if (empty($this->user)) {
			$this->user = ES::user();
		}

		$this->load($fieldGroup, $fields);

		$lib = ES::fields();

		$result = array();
		$conditionalRequired = false;

		if (isset($data['conditionalRequired'])) {
			$conditionalRequired = json_decode($data['conditionalRequired'], true);

			unset($data['conditionalRequired']);
		}

		foreach ($fields as &$field) {
			if (empty($this->loaded[$fieldGroup][$field->element])) {
				continue;
			}

			$fieldApp = $this->loaded[$fieldGroup][$field->element];

			$params = $lib->getFieldConfigValues($field);

			// Manually check for parameters enforcemnet
			foreach ($this->params as $key => $val) {
				$params->set($key, $val);
			}

			$properties = array(
				'event' => $this->event,
				'params' => $params,
				'element' => $field->element,
				'group' => $fieldGroup,
				'field' => $field,
				'inputName' => SOCIAL_FIELDS_PREFIX . $field->id,
				'user' => $this->user,
				'unique_key' => $field->unique_key
			);

			if (isset($field->profile_id)) {
				$properties['profileId'] = $field->profile_id;
			}

			if (isset($field->data)) {
				$properties['value'] = $field->data;
			}

			if ($conditionalRequired && isset($conditionalRequired[$field->id])) {
				$properties['conditionalRequired'] = $conditionalRequired[$field->id];
			} else {
				$properties['conditionalRequired'] = null;
			}

			$fieldApp->init($properties, true);

			$handler = $callback;

			$arguments = array($this->event, &$fieldApp, &$data, &$result);

			// If callback is not callable, then we fallback to internal handler class with the event name as the method
			if (!is_callable($handler)) {

				// Remove the first parameter, event, from the arguments
				array_shift($arguments);
				$handler = array($this->handler, $this->event);
			}

			call_user_func_array($handler, $arguments);
		}

		return $result;
	}

	public function __call($name, $arguments)
	{
		if (empty($this->event)) {
			$this->setEvent($name);
		}

		return call_user_func_array(array($this, 'onEvent'), $arguments);
	}
}
