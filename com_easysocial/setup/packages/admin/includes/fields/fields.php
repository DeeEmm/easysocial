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

ES::import('admin:/includes/fields/triggers');
ES::import('admin:/includes/fields/handlers');
ES::import('admin:/includes/apps/apps');

class SocialFields
{
	/**
	 * The triggerer object for fields.
	 * @var	SocialFieldTriggers
	 */
	private $triggerer	= null;

	/**
	 * General handler class for fields
	 * @var SocialFieldHandlers
	 */
	private $handler = null;
	private $params = array();
	private $user = null;

	static 	$_apps = array();

	public static $conditionalAllowed = array(
											'email',
											'gender',
											'boolean',
											'checkbox',
											'radio',
											'country',
											'textarea',
											'textbox',
											'dropdown',
											'multidropdown',
											'multilist',
											'multitextbox',
											'autocomplete'
										);



	public function __construct($params = array())
	{
		$this->init($params);
	}

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance($params = array())
	{
		$args = func_get_args();

		static $obj = null;

		if (is_null($obj)) {
			$obj = new self($params);
		}

		return $obj;
	}

	/**
	 * Inits some override params to pass to the triggerer/field
	 * This is to have a master switch for certain parameter
	 *
	 * @since  1.2
	 * @access public
	 * @param  array     $params The params to override
	 */
	public function init($params = array())
	{
		if (!empty($params)) {
			$this->params = array_merge($this->params, $params);
		}
	}

	/**
	 * This is to set the target user that the fields is acting on
	 *
	 * @since  1.2
	 * @access public
	 * @param  SocialUser    $user The target user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * Manually get the field class file
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @param  string	$element	The element name
	 * @param  string	$group		The group name
	 * @param  string	$options	The options to pass into the class
	 *
	 * @param  string	$element	The element name
	 * @param  string	$options	The options to pass into the class
	 *
	 * @param  SocialTableField	$field	The field table
	 * @param  string	$options	The options to pass into the class
	 *
	 * @param  SocialTableApp	$app	The app table
	 * @param  string	$options	The options to pass into the class
	 *
	 * @return SocialFieldItem		The field item class
	 */
	public function getClass()
	{
		$args = func_get_args();
		$count = func_num_args();

		if( $count === 0 )
		{
			return false;
		}

		$element = '';
		$group = SOCIAL_FIELDS_GROUP_USER;
		$field = null;
		$options = array();

		if( $args[0] instanceof SocialTableField )
		{
			$field = array_shift( $args );

			$app = $field->getApp();

			$element = $app->element;
			$group = $app->group;
		}
		elseif( $args[0] instanceof SocialTableApp )
		{
			if( $args[0]->type !== SOCIAL_APPS_TYPE_FIELDS )
			{
				return false;
			}

			$app = array_shift($args);

			$element = $app->element;
			$group = $app->group;
		}
		elseif( is_string( $args[0] ) )
		{
			$element = array_shift($args);

			if( !empty( $args[0] ) && is_string( $args[0] ) )
			{
				$group = array_shift($args);
			}
		}

		if( !empty( $args[0] ) && is_array( $args[0] ) )
		{
			$options = array_shift($args);
		}

		$file = SOCIAL_FIELDS . '/' . $group . '/' . $element . '/' . $element . '.php';

		if( !JFile::exists( $file ) )
		{
			return false;
		}

		require_once( $file );

		$classname 	= 'SocialFields' . ucfirst( $group ) . ucfirst( $element );

		if( !class_exists( $classname ) )
		{
			return false;
		}

		$options = array_merge($options, array(
				'element' => $element,
				'group' => $group
			)
		);

		if( !empty( $field ) )
		{
			$params = $this->getFieldConfigValues( $field );
			$options['field'] = $field;
			$options['params'] = $params;
		}

		$class = new $classname( $options );

		return $class;
	}

	/**
	 * Triggers a list of fields on specific position
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function triggerPosition($group, $view, $position, $object)
	{
		// Get the app that uses the unique key.
		$model = ES::model('Fields');
		$options = array('group' => $group);

		if ($object instanceof SocialCluster) {
			$options['workflow_id'] = $object->getWorkflow()->id;
		}

		// There should only be 1 field that is tied to a single unique key at all point of time.
		$fields = $model->getCustomFields($options);

		if (!$fields) {
			return false;
		}

		// Initialize default contents
		$contents = '';

		foreach ($fields as $field) {
			// Build the path to the field.
			$file = SOCIAL_FIELDS . '/' . $group . '/' . $field->element . '/widgets/' . $view . '/view.html.php';
			$exists = JFile::exists($file);

			if (!$exists) {
				continue;
			}

			require_once($file);

			// Construct the class name for the widget
			$className = ucfirst($field->element) . 'FieldWidgets' . ucfirst($view);

			// Check if the class exists in this context.
			if (!class_exists($className)) {
				return;
			}

			$widgetObj = new $className();

			// Check if the position exists as a method.
			$exists = method_exists($widgetObj, $position);

			if (!$exists) {
				return;
			}

			// Send the field as argument
			$args[] = $object;
			$args[] = $field;

			ob_start();
			call_user_func_array(array($widgetObj, $position), $args);
			$output = ob_get_contents();
			ob_end_clean();

			$contents .= $output;
		}

		if (!$contents) {
			return;
		}

		return $contents;
	}

	/**
	 * Triggers specific for custom fields. This needs to be triggered differently.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The event trigger name.
	 * @param	string	The group of the trigger. (E.g: user, groups)
	 * @param	Array	An array of SocialField objects.
	 * @param	Array	An array of data for the fields.
	 *
	 */
	public function trigger($event, $group, &$fields, &$data = array(), $callback = null)
	{
		// If there's no fields to load, we shouldn't be doing anything at all.
		if (empty($fields)) {
			return false;
		}
		// Initialize adapter if necessary.
		if (is_null($this->triggerer)) {
			// Create the triggers
			$this->triggerer = new SocialFieldTriggers($this->params);

			if (empty($this->user)) {
				$this->user = ES::user();
			}

			$this->triggerer->setUser($this->user);
		}

		// Change to is_callable because we've implemented magic method __call on SocialFieldTriggers
		$exists = is_callable(array($this->triggerer, $event));

		if (!$exists) {
			return false;
		}

		// Set the event name for element references
		$this->triggerer->setEvent($event);

		$arguments = array($group, &$fields, &$data, $callback);

		return call_user_func_array(array($this->triggerer, $event), $arguments);
	}

	public function getHandler()
	{
		if (is_null($this->handler)) {
			$this->handler = new SocialFieldHandlers();
		}

		return $this->handler;
	}

	/**
	 * Renders field widgets
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function renderWidgets($group, $view, $position, $args)
	{
		// Get the app that uses the unique key.
		$model = ES::model('Fields');

		// Get the unique key from the arguments
		$key = $args[0];

		// Get the user from the arguments
		$user = $args[1];

		$options = array('key' => $key , 'workflow_id' => $user->getWorkflow()->id , 'data' => true , 'dataId' => $user->id ,'dataType' => SOCIAL_TYPE_USER);

		// There should only be 1 field that is tied to a single unique key at all point of time.
		$fields = $model->getCustomFields($options);

		if (!isset($fields[0])) {
			return false;
		}

		$field = $fields[0];

		// Initialize default contents
		$contents = '';

		// Build the path to the field.
		$file = SOCIAL_FIELDS . '/' . $group . '/' . $field->element . '/widgets/' . $view . '/view.html.php';
		$exists = JFile::exists($file);

		if (!$exists) {
			return;
		}

		require_once($file);

		// Construct the class name for the widget
		$className = ucfirst($field->element) . 'FieldWidgets' . ucfirst($view);

		// Check if the class exists in this context.
		if (!class_exists($className)) {
			return;
		}

		$widgetObj = new $className();

		// Check if the position exists as a method.
		$exists = method_exists( $widgetObj , $position );

		if (!$exists) {
			return;
		}

		// Send the field as argument
		$args[] = $field;

		ob_start();
		call_user_func_array( array( $widgetObj , $position ) , $args );
		$output = ob_get_contents();
		ob_end_clean();

		$contents .= $output;

		return $contents;
	}

	/**
	 * Retrieves a formatted value from a particular custom field given the unique key
	 *
	 * @since	1.0
	 * @access	public
	 * @param	SocialTableField	$field	The field table
	 * @param   string display type. E.g. listing, item, header and etc
	 * @param   boolean determine if the value should link to advanced search or not.
	 * @return	Mixed	The field value
	 */
	public function getValue( SocialTableField $field )
	{
		$class = $this->getClass( $field );

		if( $class === false )
		{
			return;
		}

		return $class->getValue();
	}

	/**
	 * Retrieves raw data from a particular custom field given the unique key
	 *
	 * @since	1.0
	 * @access	public
	 * @param	SocialTableField	$field	The field table
	 * @return	Mixed	The field data
	 */
	public function getData( SocialTableField $field )
	{
		$class = $this->getClass( $field );

		if( $class === false )
		{
			return;
		}

		return $class->getData();
	}

	/**
	 * Retrieves raw data from a particular custom field given the unique key
	 *
	 * @since	1.0
	 * @access	public
	 *
	 * @param	string	$element	The element name
	 * @param	string	$group		The group name
	 *
	 * @param	SocialTableField	$field	The field table
	 *
	 * @param	SocialTableApp	$app	The app table
	 *
	 * @return	Mixed	The field data
	 */
	public function getOptions()
	{
		$class = call_user_func_array(array($this, 'getClass'), func_get_args());

		if( $class === false )
		{
			return;
		}

		return $class->getOptions();
	}

	/**
	 * Get the default manifest from defaults/fields.json
	 *
	 * @since	1.0
	 * @access	public
	 * @return	Object	The default manifest object
	 *
	 */
	public function getDefaultManifest($type = SOCIAL_FIELDS_GROUP_USER)
	{
		static $manifest = array();

		if (empty($manifest[$type])) {
			$path = SOCIAL_CONFIG_DEFAULTS . '/fields/config/' . $type . '.json';

			if (JFile::exists($path)) {
				$raw		= file_get_contents( $path );
				$manifest[$type]	= ES::json()->decode( $raw );
			} else {
				$manifest[$type] = new stdClass();
			}
		}

		return $manifest[$type];
	}

	/**
	 * Get the default data from the manifest file of the core apps.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCoreManifest($fieldGroup = SOCIAL_FIELDS_GROUP_USER, $apps = [])
	{
		// Ensure that it's an array.
		$apps = ES::makeArray($apps);

		// If apps is empty, ignore.
		if( !$apps )
		{
			return false;
		}

		// Default value
		$fields 	= array();

		// Lets go through the list of apps that are core.
		foreach( $apps as $app )
		{
			// Get the full default configuration
			$config = $this->getFieldConfigParameters( $app->id );

			// Initialise an object that should stores only the default value
			$obj = new stdClass();

			// Manually extract the default values
			foreach( $config as $name => $fields )
			{
				if( property_exists( $fields, 'default' ) )
				{
					$obj->$name = $fields->default;
				}
			}

			// We need to set the application id here.
			$obj->app_id	= $app->id;

			// Add them to the fields list.
			$fields[]		= $obj;
		}

		return $fields;
	}

	private function loadAppData( $appId )
	{
		if( count( self::$_apps ) == 0 )
		{
			// lets load all apps.
			// $model		= ES::model( 'Apps' );
			// $options	= array( 'type' => SOCIAL_APPS_TYPE_FIELDS );

			// $apps		= $model->setLimit(0)->getApps( $options );

			// if( $apps )
			// {
			// 	foreach( $apps as $app )
			// 	{
			// 		self::$_apps[ $app->id ] = $app;
			// 	}
			// }

			$dbcache = ES::dbcache('app');
			// TODO: Change this to where case.
			$result = $dbcache->loadObjectList(array('type' => SOCIAL_APPS_TYPE_FIELDS));

			self::$_apps = $dbcache->bindTable($result);
		}

		return self::$_apps[ $appId ];
	}

	/**
	 * Determine if this field is allowing conditional behavior
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canShowConditional($appId)
	{
		$app = $this->loadAppData($appId);

		$config = $app->getManifest();

		if (isset($config->showConditional)) {
			return $config->showConditional;
		}

		// Default always show
		return true;
	}

	/**
	 * Retrieves a field configuration.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFieldConfigParameters($appId, $groupTabs = false, $jsonString = false)
	{
		// Note: We put this function here instead of field table because sometimes a field might not have a field id to load the parameters

		static $configParams = array();

		if (empty($configParams[$appId])) {

			$allowedFields = $this->getMiniRegistrationFields();
			$app = $this->loadAppData($appId);

			$config = $app->getManifest();

			if (empty($config)) {
				$config = new stdClass();
			}

			// Get the default core parameters
			$defaults = $this->getDefaultManifest($app->group);

			// Manually perform a deep array merge to carry the defaults over to the config object
			foreach ($defaults as $name => $params) {

				if ($name == 'visible_mini_registration') {
					if (!in_array($app->element, $allowedFields)) {
						continue;
					}
				}

				if (property_exists($config, $name)) {

					if (is_bool($config->$name)) {
						$params = $config->$name;
					} else {
						$params = (object) array_merge( (array) $params, (array) $config->$name );
					}
				}

				$config->$name = $params;
			}

			foreach ($config as $name => &$field) {
				$this->translateConfigParams($field);

				if (isset($field->subfield)) {
					foreach ($field->subfields as $subname => &$subfield) {
						$this->translateConfigParams($subfield);
					}
				}
			}

			$configParams[$appId] = $config;
		}

		// Make a clone to prevent pass by reference
		$data = clone $configParams[$appId];

		if ($groupTabs) {
			$groupedConfig = new stdClass();


			foreach ($data as $key => $value) {

				if (!is_bool($value)) {
					// This will enforce group to be either basic or advance
					// $type = property_exists( $value, 'group' ) && $value->group == 'advance' ? 'advance' : 'basic';

					// This will allow any group
					$type = property_exists($value, 'group') ? $value->group : 'basic';

					if (!property_exists($groupedConfig, $type)) {
						$groupedConfig->$type = new stdClass();
					}

					$groupedConfig->$type->title = JText::_('COM_EASYSOCIAL_PROFILES_FORM_FIELDS_TAB_' . strtoupper($type));

					if (!property_exists($groupedConfig->$type, 'fields')) {
						$groupedConfig->$type->fields = new stdClass();
					}

					$groupedConfig->$type->fields->$key = $value;
				}
			}

			$data = $groupedConfig;
		}

		if ($jsonString) {
			return json_encode($data);
		}

		return $data;
	}

	/**
	 * Get conditions value of the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getFieldConditionValues($fieldId)
	{
		$field = ES::table('field');
		$field->load($fieldId);

		$conditions = $field->conditions;

		if ($conditions) {
			return json_decode($conditions);
		}

		return false;
	}

	/**
	 * Retrieves a field configuration.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFieldConfigValues( $appId, $fieldId = 0 )
	{
		$field = null;

		// If first parameter is object, we assume caller pass in field table
		if( is_object( $appId ) )
		{
			// Reassign appid accordingly
			$field = $appId;
			$appId = $field->app_id;
		}

		$defaults	= $this->getFieldConfigParameters( $appId );

		// If the first parameter is appId, then $field should be null by now, and if second parameter is valid, then we load the table
		if( empty( $field ) && !empty( $fieldId ) )
		{
			$field = ES::table( 'field' );
			$field->load( $fieldId );
		}

		// Initialise a registry first
		$params		= ES::registry();

		// Initialise a string library
		$string = ES::string();

		// If $field is still empty then we shouldn't get the field parameters
		if( !empty( $field ) )
		{
			// Get the params from the table
			$params		= $field->getParams();

			// Get the choices
			$choices 	= $field->getOptions();

			// Manually merge in the choices into the parameter object
			foreach( $choices as $choice => $values )
			{
				foreach( $values as $id => &$value )
				{
					// $value->label = JText::_( $value->label );
					// $value->title = JText::_( $value->title );
					$value->label = $string->escape($value->label);
				}

				$params->set( $choice, $values );
			}
		}

		// This is to get the default values of the params and
		// merge it in as the value if the value does not exist yet
		foreach( $defaults as $name => $obj )
		{
			// Check if this name exists in the params
			if( !$params->exists( $name ) )
			{
				$default = '';

				if( is_bool( $obj ) )
				{
					$default = $obj;
				}

				if( isset( $obj->default ) )
				{
					$default = $obj->default;
				}

				$params->set( $name, $default );
			}

			if( isset( $obj->subfields ) )
			{
				foreach( $obj->subfields as $subname => $subfield )
				{
					if( !$params->exists( $name . '_' . $subname ) )
					{
						$default = isset( $subfield->default ) ? $subfield->default : '';

						$params->set( $name . '_' . $subname, $default );
					}
				}
			}
		}

		return $params;
	}

	/**
	 * Retrieves the custom fields configuration popup
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getForm($appId, $fieldId = 0, $previousValue = null, $availableFields = null)
	{
		$app = ES::table('App');
		$app->load($appId);

		// Get config parameters
		$params = $this->getFieldConfigParameters($appId, true);

		// Get config values
		$values = $this->getFieldConfigValues($appId, $fieldId);

		// Get conditions values
		$conditions = $this->getFieldConditionValues($fieldId);

		// Determine if this field can be set as conditional
		$showConditional = $this->canShowConditional($appId);

		// Previous Conditions
		$previousConditions = array();

		$conditionalTabs = array();

		foreach ($params as $tab => &$data) {

			foreach ($data->fields as $name => &$field) {

				// Normalize the types here
				$this->normalizeConfigType($field);

				// Normalize the subfield types too
				if (isset($field->subfields)) {

					foreach ($field->subfields as $subname => $subfield) {
						$this->normalizeConfigType($subfield);
					}
				}

				// Check the values for this name
				if (!$values->exists($name)) {
					$values->set($name, '');
				}
			}

			if ($tab == 'conditional') {
				$conditionalTabs = $data;
			}
		}

		if ($previousValue) {

			foreach ($previousValue as $value) {
				if (isset($value['conditions'])) {

					$tmpData = $value['conditions'];
					$tmpValue = false;

					if (is_array($tmpData) && isset($tmpData['value'])) {
						$tmpValue = $tmpData['value'];
					}

					if ($tmpValue) {
						foreach ($tmpValue as $tmpCondition) {
							$obj = new stdClass();
							$obj->fieldId = $tmpCondition['field'];
							$obj->operator = $tmpCondition['operator'];
							$obj->value = $tmpCondition['value'];

							$previousConditions[] = $obj;
						}
					} else {
						// The field is no longer conditional.
						$conditions = array();
					}
				}

				if (isset($value['name']) && isset($value['value'])) {

					// For some reason some of the the false value become string instead of boolean.
					if ($value['value'] === 'false') {
						$value['value'] = false;
					}

					$values->set($value['name'], $value['value']);
				}

				if (isset($value['choices']) && isset($value['choices'][0])) {
					$newChoices = array();
					$choices = $value['choices'][0];

					if (isset($choices['items']) && isset($choices['items']['value'])) {
						$choicesValue = $choices['items']['value'];

						foreach ($choicesValue as $choice) {
							$table = ES::table('FieldOptions');
							$table->bind($choice);
							$table->label = $table->title;

							$newChoices[] = $table;
						}

						$values->set('items', $newChoices);
					}
				}
			}
		}

		// Overwrite stored conditions with current conditions
		if ($previousConditions) {
			$conditions = $previousConditions;
		}

		$tabs = array('core', 'basic', 'view', 'advance');
		$tabs = array_merge($tabs, array_diff(array_keys((array) $params), $tabs));

		// Format available fields on the form
		$availableFields = $this->formatConditionaFieldsSelection($availableFields, array($fieldId));

		$theme = ES::themes();
		$theme->set('fieldId', $fieldId);
		$theme->set('app', $app);
		$theme->set('params', $params);
		$theme->set('values', $values);
		$theme->set('tabs', $tabs);
		$theme->set('conditionalTabs', $conditionalTabs);
		$theme->set('conditions', $conditions);
		$theme->set('availableFields', $availableFields);
		$theme->set('showConditional', $showConditional);

		$output = $theme->output('admin/workflows/form/browser/item');

		return $output;
	}

	/**
	 * Format the conditional fields selection
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function formatConditionaFieldsSelection($steps, $exclude = array())
	{
		$steps = isset($steps['steps']) ? $steps['steps'] : false;
		$fields = array();

		if (!$steps) {
			return $fields;
		}

		if (!isset($steps[0])) {
			return $fields;
		}

		$steps = $steps[0];

		if (!isset($steps['fields'])) {
			return $fields;
		}

		foreach ($steps['fields'] as $data) {
			$element = $data['fieldElement'];

			// Only selected fields is allowed
			if (!in_array($element, self::$conditionalAllowed)) {
				continue;
			}

			// Exclude field
			if (in_array($data['fieldId'], $exclude)) {
				continue;
			}

			$obj = new stdClass();
			$obj->id = $data['fieldId'];
			$obj->element = $element;
			$obj->title = $data['fieldTitle'];

			$fields[] = $obj;
		}

		return $fields;
	}

	/**
	 * Retrieves the custom fields configuration html output
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getConfigHtml($id, $fieldId = 0)
	{
		// Load the app
		$app = ES::table('App');
		$app->load($id);

		// App title
		$title = $app->title;

		// Get config parameters
		$params = $this->getFieldConfigParameters($id, true);

		// Get config values
		$values = $this->getFieldConfigValues($id, $fieldId);

		foreach ($params as $tab => &$data) {

			foreach ($data->fields as $name => &$field) {

				// Normalize the types here
				$this->normalizeConfigType($field);

				// Normalize the subfield types too
				if (isset($field->subfields)) {

					foreach ($field->subfields as $subname => $subfield) {
						$this->normalizeConfigType($subfield);
					}
				}

				// Check the values for this name
				if (!$values->exists($name)) {
					$values->set($name, '');
				}
			}
		}

		$tabs = array('basic', 'core', 'view', 'advance');

		$tabs = array_merge($tabs, array_diff(array_keys((array) $params), $tabs));

		$theme = ES::themes();

		$theme->set('title', $title);
		$theme->set('params', $params);
		$theme->set('values', $values);
		$theme->set('tabs', $tabs);

		return $theme->output('admin/profiles/fields/config');
	}

	/**
	 * Normalizes the field types that is retrieved from the config file
	 *
	 * @since	1.4
	 * @access	public
	 */
	private function normalizeConfigType(&$field)
	{
		$type = 'boolean';

		if (isset($field->type)) {

			if ($field->type == 'input' || $field->type == 'text') {
				$type = 'input';
			}

			if ($field->type == 'dropdown' || $field->type == 'list' || $field->type == 'select') {
				$type = 'dropdown';
			}

			$allowed = array('editors', 'checkbox', 'radio', 'textarea', 'choices', 'article', 'custom');

			if (in_array($field->type, $allowed)) {
				$type = $field->type;
			}
		}

		$field->type = $type;
	}

	/*
	 * Returns html formatted data for validations
	 *
	 * @param 	string 	$element 	The field element.
	 * @return 	string 	HTML formatted values.
	 */
	public function renderValidations( $element )
	{
		$path	= SOCIAL_MEDIA . DS . SOCIAL_APPS_TYPE_FIELDS . DS . strtolower( $element ) . DS . 'tmpl' . DS . 'params.xml';

		if( !ES::get( 'Files' )->exists( $path ) )
		{
			return false;
		}

		$parser	= JFactory::getXMLParser( 'Simple' );
		$parser->loadFile( $path );

		if( !$parser->document->getElementByPath( 'validations' ) )
		{
			return false;
		}

		$validations	= $parser->document->getElementByPath( 'validations' )->children();

		return ES::get( 'Themes' )->set( 'validations' , $validations )->output( 'admin.profiles.fields_validation' );
	}

	/**
	 * Loads a specific language file given the field's element and group.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string		The group's name.
	 * @param	string		The element's name.
	 * @return
	 */
	public function loadLanguage( $group , $element )
	{
		$lang 		= JFactory::getLanguage();
		$file 		= 'plg_fields_' . $group . '_' . $element;

		// Load the language file.
		$lang->load( $file , JPATH_ROOT . '/administrator' );
	}

	/**
	 * Helper function to translate any config parameters text
	 *
	 * @since  1.1
	 * @access private
	 * @param  SocialTableField    $field The field table item
	 */
	private function translateConfigParams( &$field )
	{
		// Only try to JText the label field if it exists.
		if( isset( $field->label ) )
		{
			$field->label	= JText::_( $field->label );
		}

		// Only try to JText the tooltip field if it exists.
		if( isset( $field->tooltip ) )
		{
			$field->tooltip	= JText::_( $field->tooltip );
		}

		// Do not translate the default value of the field because the default might be a language key
		// if( isset( $field->default ) && is_string( $field->default ) )
		// {
		// 	$field->default = JText::_( $field->default );
		// }

		// If there are options set, we need to jtext them as well.
		if( isset( $field->option ) )
		{
			$field->option 	= ES::makeArray( $field->option );

			foreach( $field->option as &$option )
			{
				$option->label 	= JText::_( $option->label );
			}
		}

		// Only try to JText the info value if it exist
		if( isset( $field->info ) )
		{
			$field->info = JText::_( $field->info );
		}
	}

	/**
	 * Save field in a step based on step id and field object
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveField($field, $stepId, $ordering = null, $isCopy = null)
	{
		$appTable = ES::table('App');
		$fieldTable = ES::table('Field');

		$appTable->load($field->appId);

		if (!$field->isNew) {
			$fieldTable->load($field->fieldId);
		}

		// temporary fix on fieldElement;
		$field->fieldElement = $appTable->element;

		if ($isCopy) {

			// Check choices options value if exists
			if (!isset($field->params)) {
				$fieldOptions = $fieldTable->getOptions();

				// Re-construct choices data
				if ($fieldOptions) {
					$choices = array();
					$values = array();
					$items = new stdClass();

					foreach ($fieldOptions as $key => $options) {
						$optionId = 0;

						foreach ($options as $value) {
							$option = new stdClass();
							$option->id = $optionId;
							$option->title = $value->title;
							$option->value = $value->value;
							$option->default = $value->default;

							$values[] = $option;
							$optionId++;
						}

						$items->value = $values;

						$choices[$key] = $items;
					}

					$field->choices = $choices;
				}
			}

			$fieldTable->_isCopy = true;
			$fieldTable->id = null;
		}

		// Set the application id.
		$fieldTable->app_id = $field->appId;

		// Set the step id since we now know the step id.
		$fieldTable->step_id = $stepId;

		// Generate default value if this field is new and there are no params yet
		if (!isset($field->params) && $field->isNew) {
			$fieldTable->processDefaultParams();
		}

		// Let's process the params
		if (isset($field->params)) {

			// echo '<pre>'; var_dump($field->params); echo '</pre>';

			// Extract choices from params
			foreach ($field->params as &$param) {

				if (isset($param->choices)) {

					if (isset($param->choices[0])) {
						$field->choices = $param->choices[0];
					}

					unset($param->choices);
				}

				if (isset($param->conditions)) {
					if (isset($param->conditions->value)) {
						$field->conditions = $param->conditions->value;
					} else {

						// Reset conditions value
						$field->conditions = null;
						$fieldTable->is_conditional = false;
						$fieldTable->conditions = false;
					}

					unset($param->conditions);
				}
			}

			$fieldTable->processParams($field->params);
		}

		// Check for field conditions
		if (isset($field->conditions) && $field->conditions) {
			$conditions = array();

			foreach ($field->conditions as $condition) {
				$obj = new stdClass();
				$obj->fieldId = $condition->field;
				$obj->operator = $condition->operator;
				$obj->value = $condition->value;

				$conditions[] = $obj;
			}

			$fieldTable->is_conditional = true;
			$fieldTable->conditions = json_encode($conditions);
		}

		// Set the ordering now.
		$fieldTable->ordering = $ordering;

		// The core state would be dependent on the app's settings.
		$fieldTable->core = $appTable->core;

		// make sure not nullable columns has values.
		if ($field->isNew) {
			if (!isset($fieldTable->title) && is_null($fieldTable->title)) {
				$fieldTable->title = '';
			}

			if (!isset($fieldTable->display_title) && is_null($fieldTable->display_title)) {
				$fieldTable->display_title = 1;
			}

			if (!isset($fieldTable->description) && is_null($fieldTable->description)) {
				$fieldTable->description = '';
			}

			if (!isset($fieldTable->display_description) && is_null($fieldTable->display_description)) {
				$fieldTable->display_description = 1;
			}

			if (!isset($fieldTable->params) && is_null($fieldTable->params)) {
				$fieldTable->params = '';
			}

			if (!isset($fieldTable->default) && is_null($fieldTable->default)) {
				$fieldTable->default = '';
			}

			if (!isset($fieldTable->validation) && is_null($fieldTable->validation)) {
				$fieldTable->validation = '';
			}

			if (!isset($fieldTable->visible_registration) && is_null($fieldTable->visible_registration)) {
				$fieldTable->visible_registration = 1;
			}

			if (!isset($fieldTable->visible_edit) && is_null($fieldTable->visible_edit)) {
				$fieldTable->visible_edit = 1;
			}

			if (!isset($fieldTable->visible_display) && is_null($fieldTable->visible_display)) {
				$fieldTable->visible_display = 1;
			}
		}

		// Let's try to store the field now.
		$state = $fieldTable->store();

		// If there's any problems storing the state, we should log errors and not proceed.
		if (!$state) {
			return false;
		}

		// Temporarily re-map the old field id with the new one for conditional fields
		if ($field->isNew) {
			$field->oldId = $field->fieldId;
		}

		// Assign back the field id to pass back to client
		$field->fieldId = $fieldTable->id;

		// Check if unique key for this field is valid and assign it back for the client
		$field->unique_key = $fieldTable->checkUniqueKey();

		// This is required to store the unique keys of the field
		$fieldTable->store();

		// Check for choices here
		if (isset($field->choices)) {
			foreach ($field->choices as $name => $choices) {
				$origChoices = $fieldTable->getOptions($name);

				$currentChoices = array();

				$choiceOrdering = 1;

				foreach ($choices->value as $choice) {
					$fieldoptionsTable = ES::table('FieldOptions');

					if (!empty($choice->id)) {
						$fieldoptionsTable->load($choice->id);
					}

					if ($isCopy) {
						$fieldoptionsTable->id = null;
					}

					$fieldoptionsTable->parent_id = $fieldTable->id;
					$fieldoptionsTable->key = $name;
					$fieldoptionsTable->title = $choice->title;
					$fieldoptionsTable->value = $choice->value;
					$fieldoptionsTable->ordering = $choiceOrdering;

					if (isset($choice->default)) {
						$fieldoptionsTable->default = $choice->default;
					}

					if (!$fieldoptionsTable->store()) {
						return false;
					}

					// Assign back the options id to pass back to client
					$choice->id = $fieldoptionsTable->id;

					$currentChoices[] = $choice->id;

					$choiceOrdering++;
				}

				if (!$isCopy) {
					foreach ($origChoices as $origId => $origChoices) {
						if (!in_array($origId, $currentChoices)) {
							$origChoices->delete();
						}
					}
				}
			}
		}

		return $field;
	}

	/**
	 * Save fields based on the workflow id and types
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function saveFields($uid, $type, &$data, $options = array())
	{
		$newFields = array();
		$isCopy = isset($options['copy']) && $options['copy'];

		$sequence = 1;
		foreach ($data->steps as $step) {
			$stepTable = ES::table('fieldstep');

			$state = false;

			if (!$step->isNew) {
				$state = $stepTable->load($step->stepId);
			}

			if ($isCopy) {
				$stepTable->_isCopy = true;
				$stepTable->id = null;
				$state = false;
			}

			// If there's a problem retrieving this step, this is probably a new step.
			if (!$state) {
				$stepTable->uid = $uid;
				$stepTable->workflow_id = $uid;
				$stepTable->type = $type;
				$stepTable->state = SOCIAL_STATE_PUBLISHED;
				$stepTable->created = ES::date()->toMySQL();
			}

			$stepTable->sequence = $sequence;

			// Set the step values
			$stepTable->processParams($step);

			// Try to store the step.
			$state = $stepTable->store();

			// If there's a problem storing the state, we should log errors here.
			if (!$state) {
				return false;
			}

			// Assign back the id to pass back to client
			$step->id = $stepTable->id;

			$sequence++;

			// When there's no fields for this step, just skip the rest of processing.
			if (!isset($step->fields)) {
				continue;
			}

			// Reset the ordering for the fields.
			$ordering = 0;
			$oldFieldId = array();
			$originalFieldId = array();

			// Now let's go through the list of fields for this step.
			foreach ($step->fields as $field) {

				// Store the original id for copy purpose
				if ($isCopy) {
					$originalId = $field->fieldId;
				}

				$fieldTmp = $this->saveField($field, $stepTable->id, $ordering, $isCopy);

				// Temporarily re-map the old id with the new id
				if (($fieldTmp->isNew && isset($fieldTmp->oldId))) {
					$oldFieldId[$fieldTmp->oldId] = $fieldTmp->fieldId;
				}

				// if this is a new field, we need to process the default value if there is any.
				if ($fieldTmp->isNew) {
					$newFields[] = $fieldTmp;
				}

				// Re-map original id with new id for post processing during copy
				if ($isCopy) {
					$originalFieldId[$originalId] = $fieldTmp->fieldId;
				}

				$ordering++;
			}

			// Re-map the old field id with the new id for conditional field
			if (!empty($oldFieldId) || !empty($originalFieldId)) {
				foreach ($step->fields as $field) {
					$fieldTable = ES::table('Field');
					$fieldTable->load($field->fieldId);

					if ($fieldTable->isConditional()) {
						$conditions = $fieldTable->getConditions();

						foreach ($conditions as $condition) {
							if (isset($oldFieldId[$condition->fieldId])) {
								$condition->fieldId = $oldFieldId[$condition->fieldId];
							}

							if (isset($originalFieldId[$condition->fieldId])) {
								$condition->fieldId = $originalFieldId[$condition->fieldId];
							}
						}

						$fieldTable->conditions = json_encode($conditions);
						$fieldTable->store();
					}
				}
			}
		}

		if (!$isCopy && isset($data->deleted)) {
			$deleted = $data->deleted;

			foreach ($deleted as $deletedtype => $deletedids) {

				if (!empty($deletedids)) {
					$name = $deletedtype == 'steps' ? 'fieldstep' : 'field';

					foreach ($deletedids as $deletedid) {

						$table = ES::table($name);
						$state = $table->load($deletedid);

						if ($state) {
							$table->delete();
						}
					}
				}
			}
		}

		// #2085
		if ($newFields) {
			$this->processFieldDefault($uid, $newFields);
		}

		return true;
	}


	/**
	 * Adding field's default value into #__social_fields_data for new fields
	 *
	 * @since  2.1.10
	 * @access public
	 */
	public function processFieldDefault($workflowId, $fields)
	{
		$data = array();
		$datakey = array();
		$hasMultiSelectionFields = array('multilist', 'checkbox', 'multidropdown');
		$excludeFields = array(
			'avatar',
			'autocomplete',
			'code_generator',
			'cover',
			'file',
			'header',
			'html',
			'mailchimp',
			'password',
			'recaptcha',
			'separator',
			'terms',
			'text',
			'joomla_user_editor'
		);

		foreach ($fields as $field) {

			// exclude these fields.
			if (in_array($field->fieldElement, $excludeFields)) {
				continue;
			}

			// Check for choices here
			if (isset($field->choices)) {

				$defaults = array();

				foreach ($field->choices as $name => $choices) {
					foreach ($choices->value as $choice) {

						if (isset($choice->default) && $choice->default) {
							$defaults[] = $choice->value;
						}
					}
				}

				if ($defaults) {
					if (! in_array($field->fieldElement, $hasMultiSelectionFields)) {
						$defaults = isset($defaults[0]) ? $defaults[0] : '';
					}

					$data[$field->fieldId] = $defaults;
				} else {
					$data[$field->fieldId] = '';
				}
			}

			// for other fields type, we will just use empty string as default value.
			if (!isset($field->choices)) {

				if ($field->fieldElement == 'address') {

					$addressDataKeys = array(
						'address',
						'address1',
						'address2',
						'city',
						'state',
						'zip',
						'country',
						'latitude',
						'longitude'
					);

					$datakey[$field->fieldId] = $addressDataKeys;
				}

				if ($field->fieldElement == 'birthday' || $field->fieldElement == 'datetime') {
					$datakey[$field->fieldId] = array('date', 'timezone');
				}

				$data[$field->fieldId] = '';
			}
		}

		if ($data) {

			$wf = ES::table('workflow');
			$wf->load($workflowId);

			$model = ES::model('fields');
			$model->addFieldsDefault($workflowId, $wf->type, $data, $datakey);
		}
	}

	/**
	 * Deprecated from 1.3.
	 * Checks if the user's profile is complete by triggering onFieldCheck.
	 *
	 * @since  1.2
	 * @deprecated Deprecated from 1.3. Filled fields are now denormalized.
	 * @access public
	 */
	public function checkCompleteProfile()
	{
		$me = ES::user();

		// If current user is a guest or no profile is assigned, we cannot check against anything
		if (!$me->isRegistered() || empty($me->profile_id)) {
			return true;
		}

		static $result = null;

		if (!isset($result)) {
			$model = ES::model('fields');

			$fields = $model->getCustomFields(array('workflow_id' => $me->getWorkflow()->id, 'data' => true, 'dataId' => $me->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_EDIT, 'group' => SOCIAL_FIELDS_GROUP_USER));

			if (empty($fields)) {
				$result = array();
				return $result;
			}

			$args 	= array(&$me);

			$result = $this->trigger('onFieldCheck', SOCIAL_FIELDS_GROUP_USER, $fields, $args);
		}

		return $result;
	}

	/**
	 * Get allowed fields to be display in mini registration form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getMiniRegistrationFields()
	{
		$fields = array(
				'joomla_username',
				'joomla_fullname',
				'joomla_email',
				'joomla_password',
				'birthday',
				'gender',
				'recaptcha',
				'address',
				'checkbox',
				'acymailing',
				'terms',
				'textbox',
				'multidropdown',
				'dropdown',
				'mailchimp',
				'code_generator',
				'invitation_code',
				'avatar',
				'cover'
			);

		return $fields;
	}
}
