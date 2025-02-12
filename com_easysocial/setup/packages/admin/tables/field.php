<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableField extends SocialTable
{
	/**
	 * The unique id of the field item.
	 * @var	int
	 */
	public $id = null;

	/**
	 * The unique key of the field item.
	 * @var	int
	 */
	public $unique_key = null;

	/**
	 * The foreign key to #__social_apps
	 * @var	int
	 */
	public $app_id = null;

	/**
	 * The foreign key to #__social_fields_steps
	 * @var	int
	 */
	public $step_id = null;

	/**
	 * The title for the field
	 * @var	int
	 */
	public $title = null;

	/**
	 * Determines whether or not the title should be displayed in the form.
	 * @var	bool
	 */
	public $display_title = null;

	/**
	 * The tooltip or description for the field
	 * @var	string
	 */
	public $description = null;

	/**
	 * Determines whether or not the title should be displayed in the form.
	 * @var	bool
	 */
	public $display_description = null;

	/**
	 * The default value for the field.
	 * @var	mixed
	 */
	public $default = null;

	/**
	 * The validation for this field.
	 * @var	mixed
	 */
	public $validation = null;

	/**
	 * The state of this field.
	 * @var	int
	 */
	public $state = null;

	/**
	 * Determines if this field can be searchable.
	 * @var	mixed
	 */
	public $searchable = null;

	/**
	 * Determines if this field is required
	 * @var	bool
	 */
	public $required = null;

	/**
	 * Stores the JSON string of the raw parameters from Registry
	 * @var	string
	 */
	public $params = null;

	/**
	 * Stores the ordering number of the field. With 0 being the lowest order.
	 * @var	int
	 */
	public $ordering = null;

	/**
	 * Determines if this is a core field that cannot be deleted.
	 * @var	int
	 */
	public $core = null;

	/**
	 * Determines if the page is visible during registration.
	 * @var	int
	 */
	public $visible_registration = null;

	public $visible_mini_registration = null;

	/**
	 * Determines if the page is visible during editing.
	 * @var	int
	 */
	public $visible_edit = null;

	/**
	 * Determines if the page is visible during viewing.
	 * @var	int
	 */
	public $visible_display = null;

	/**
	 * Determines if this field is used in friend suggestion.
	 * @var	mixed
	 */
	public $friend_suggest = null;

	public $_isCopy = false;

	public $is_conditional = null;
	public $conditions = null;

	// Non table related.
	// Used in field processing
	private $childs = null;
	public $value = null;
	protected $value_binary = null;
	public $element = null;
	public $data = null;
	public $raw = null;

	// Extra constant information
	// Used to process/generate/combine core parameters and user parameters
	private $coreParams = array(
		'visible_registration',
		'visible_mini_registration',
		'visible_edit',
		'visible_display',
		'title',
		'display_title',
		'description',
		'display_description',
		'default',
		'validation',
		'required',
		'searchable',
		'unique_key',
		'friend_suggest'
	);

	/**
	 * Used to optimize the db queries for getting the field options.
	 * @var array
	 */
	static $_fieldoptions = array();

	/**
	 * Used to optimize the db queries for getting the field data.
	 * @var array
	 */
	static $_fielddata = array();

	/**
	 * Class Constructor
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__social_fields', 'id', $db);
	}

	/**
	 * Override parent's store implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bind($data, $ignore = array())
	{
		$state	= parent::bind($data, $ignore);

		if (isset($data->element)) {
			$this->element = $data->element;
		}

		if (isset($data->value)) {
			$this->value = $data->value;
		}

		if (isset($data->value_binary)) {
			$this->value_binary = $data->value_binary;
		}

		return $state;
	}

	/*
	 * Binds child objects during posts
	 *
	 */
	public function bindChilds($data)
	{
		if (empty($data))
		{
			return false;
		}

		$this->childs	= $data;

		return true;
	}

	/**
	 * Override parent's store implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		// Set the element back again.
		if (isset($this->element)) {
			$element = $this->element;

			unset($this->element);
		}

		// Update ordering column if this is a new item.
		if (!$this->id && is_null($this->ordering)) {
			$this->ordering = $this->getNextOrder(array('step_id' => $this->step_id));
		}

		$status	= parent::store($updateNulls);

		// Set the element back again.
		if (isset($element)) {
			$this->element = $element;
		}

		return $status;
	}

	/**
	 * Retrieves a list of options for this particular field.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOptions($key = null)
	{
		$options = array();

		if (!isset(self::$_fieldoptions[$this->id]))
		{
			self::$_fieldoptions[$this->id] = array();

			$db		= ES::db();
			$sql	= $db->sql();

			$sql->select('#__social_fields_options');
			$sql->where('parent_id', $this->id);
			$sql->order('key');
			$sql->order('ordering');

			$db->setQuery($sql);

			$result = $db->loadObjectList();

			foreach($result as $row)
			{
				self::$_fieldoptions[$this->id][$row->key][] = $row;
			}
		}

		$data = self::$_fieldoptions[$this->id];

		foreach($data as $rKey => $rows)
		{
			if (is_array($rows))
			{
				foreach($rows as $row)
				{
					$option = ES::table('FieldOptions');
					$option->bind($row);

					$option->label = $option->title;

					// If no key is provided we group result based on key and id
					if (empty($key))
					{
						$options[$row->key][$row->id]  = $option;
					}
					else
					{
						if ($option->key === $key)
						{
							$options[$row->id] = $option;
						}
					}
				}
			}
		}

		return $options;
	}


	/**
	 * set fields options in batch for later reference.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setBatchFieldOptions($fieldIds)
	{
		if (empty($fieldIds)) {
			return;
		}

		$db = ES::db();
		$sql = $db->sql();

		$needDbLoad = array();

		foreach ($fieldIds as $fid) {
			if(!isset(self::$_fieldoptions[$fid])) {
				$needDbLoad[] = $fid;
			}
		}

		if (!$needDbLoad) {
			// all loaded. skip the process.
			return true;
		}

		$fieldIds = $needDbLoad;
		$ids = implode(',', $fieldIds);

		$sql->select('#__social_fields_options')
			->where('parent_id', $fieldIds, 'in')
			->order('parent_id')
			->order('key')
			->order('ordering');

		$db->setQuery($sql);

		$results = $db->loadObjectList();

		if ($results) {
			foreach ($results as $result) {
				self::$_fieldoptions[$result->parent_id][$result->key][] = $result;
			}
		}

		// lets check if all the passed in fields has result or not.
		foreach ($fieldIds as $fid) {
			if (!isset(self::$_fieldoptions[$fid])) {
				self::$_fieldoptions[$fid] = array();
			}
		}
	}

	/**
	 * Get the element of the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getElement()
	{
		if (isset($this->element) && !empty($this->element)) {
			return $this->element;
		} else {
			$app = $this->getApp();
			return $app->getElement();
		}
	}

	/**
	 * Get the title of the field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTitle()
	{
		$title = $this->title;

		// If there are no title set, we just return the element as the title.
		if (!$title || $title === 0) {
			$title = $this->getElement();
		}

		return $title;
	}

	public function render($element)
	{
		$path	= SOCIAL_MEDIA . DS . 'fields' . DS . strtolower($element) . DS . strtolower($element) . '.xml';

		if (ES::get('Files')->exists($path))
		{
			return parent::renderParams($this->params, $path);
		}

		// params.xml file not found
		return;
	}

	/**
	 * Determines if the custom field is searchable
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isSearchable()
	{
		if ($this->type == SOCIAL_FIELDS_GROUP_USER && $this->searchable) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the current is required or not
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isRequired()
	{
		return (bool) $this->required;
	}

	public function export()
	{
		$obj    	= new stdClass();
		$properties = get_class_vars(get_class($this));

		foreach($properties as $key => $value)
		{
			if ($key[0] != '_')
			{
				$obj->$key      = $this->$key;
			}
		}

		return (array) $obj;
	}

	public function getChildFields($match = '')
	{
		$db 	= ES::db();
		$query  = 'SELECT field_id FROM ' . $db->nameQuote('#__social_fields_rules') . ' '
				. 'WHERE ' . $db->nameQuote('parent_id') . ' = ' . $db->Quote($this->id) . ' '
				. 'AND ' . $db->nameQuote('match_text') . ' LIKE ' . $db->Quote('%' . $match . '%');
		$db->setQuery($query);

		$result	= $db->loadColumn();

		return $result;
	}

	/**
	 * Get's the stored parameters for this field.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getParams($jsonString = false)
	{
		static $cachedParams 	= array();

		$index 	= $this->id;
		$index 	.= $jsonString ? '1' : '0';

		if (!isset($cachedParams[$index])) {
			$params		= ES::json()->decode($this->params);

			if (!is_object($params)) {
				$params = new stdClass();
			}

			// Manually assign all the core columns into the parameter object
			foreach ($this->coreParams as $coreParam) {
				$params->$coreParam = $this->$coreParam;
			}

			if ($jsonString) {
				$params = ES::json()->encode($params);
			} else {
				$params = ES::registry($params);
			}

			$cachedParams[$index]	= $params;
		}

		return $cachedParams[$index];
	}

	/**
	 * Given a set of parameter, process the argument as params of this field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function processParamsLegacy($params)
	{
		$jsonLib	= ES::json();

		if (is_string($params)) {
			$params = $jsonLib->decode($params);
		}

		// Get the config parameters for this app
		$configParameters = ES::fields()->getFieldConfigParameters($this->app_id);

		// Get the default values of this app
		$defaults = $this->getConfigDefaultValues();

		// Process core params first by extracting the values out from the parameter object
		foreach($this->coreParams as $coreParam) {
			if (isset($params->$coreParam)) {
				$this->$coreParam = $params->$coreParam;
				unset($params->$coreParam);
			} else {
				// Check for enforced value
				if (property_exists($configParameters, $coreParam) && is_bool($configParameters->$coreParam)) {
					$this->$coreParam = $configParameters->$coreParam ? 1 : 0;
				} else {
					if (!$this->_isCopy && $this->isNew() && property_exists($defaults, $coreParam))
					{
						$this->$coreParam = $defaults->$coreParam;
					}
				}
			}

			// Remove coreParam from defaults so that only extended params remains in the default
			if (property_exists($defaults, $coreParam)) {
				unset($defaults->$coreParam);
			}
		}

		// If this is a new field, then check if remaining param is empty to fill in the default values
		if ($this->isNew() && !$this->_isCopy) {
			foreach($defaults as $key => $value) {
				if (!property_exists($params, $key)) {
					$params->$key = $value;
				}
			}
		}

		// Merge the remaining params in to the existing params

		// As long as it is empty ('', null, false, array()), just force it to be an array anyway
		if (empty($this->params)) {
			$this->params = array();
		}

		// If it is a string (won't be empty at this point), then we try to decode it
		// Else just force type casting it to array
		if (is_string($this->params)) {
			$this->params = $jsonLib->isJsonString($this->params) ? (array) $jsonLib->decode($this->params) : array();
		} else {
			$this->params = (array) $this->params;
		}

		// Convert the new params into array
		$newParams = (array) $params;

		// Merge the remaining params in with the newParams taking precedence
		$this->params = $jsonLib->encode(array_merge($this->params, $newParams));

		return true;
	}

	/**
	 * Generate default params
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processDefaultParams()
	{
		$jsonLib = ES::json();

		// Get the config parameters for this app
		$configParameters = ES::fields()->getFieldConfigParameters($this->app_id);

		// Get the default values of this app
		$defaults = $this->getConfigDefaultValues();

		// Process core params first by extracting the values out from the parameter object
		foreach($this->coreParams as $coreParam) {
			// Check for enforced value
			if (property_exists($configParameters, $coreParam) && is_bool($configParameters->$coreParam)) {
				$this->$coreParam = $configParameters->$coreParam ? 1 : 0;
			} else {
				if (!$this->_isCopy && $this->isNew() && property_exists($defaults, $coreParam))
				{
					$this->$coreParam = $defaults->$coreParam;
				}
			}

			// Remove coreParam from defaults so that only extended params remains in the default
			if (property_exists($defaults, $coreParam)) {
				unset($defaults->$coreParam);
			}
		}

		$newParams = new stdClass();

		// If this is a new field, then check if remaining param is empty to fill in the default values
		if ($this->isNew() && !$this->_isCopy) {
			foreach($defaults as $key => $value) {
				if (!property_exists($newParams, $key)) {
					$newParams->$key = $value;
				}
			}
		}

		if (empty($this->params)) {
			$this->params = array();
		}

		// If it is a string (won't be empty at this point), then we try to decode it
		// Else just force type casting it to array
		if (is_string($this->params)) {
			$this->params = $jsonLib->isJsonString($this->params) ? (array) $jsonLib->decode($this->params) : array();
		} else {
			$this->params = (array) $this->params;
		}

		// Convert the new params into array
		$newParams = (array) $newParams;

		$this->params = $jsonLib->encode(array_merge($this->params, $newParams));

		return true;
	}

	/**
	 * Given a set of parameter, process the argument as params of this field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processParams($params, $forceReload = false)
	{
		$jsonLib = ES::json();

		if (is_string($params)) {
			$params = $jsonLib->decode($params);
		}

		$newParams = new stdClass();

		// Format the params
		if ($params) {
			foreach ($params as $key => $param) {
				$data = (array) $param;

				if (!empty($data) && isset($param->name) && isset($param->value)) {
					$name = $param->name;
					$value = $param->value;

					$newParams->$name = $value;
				}
			}
		}

		// Get the config parameters for this app
		$configParameters = ES::fields()->getFieldConfigParameters($this->app_id);

		// Get the default values of this app
		$defaults = $this->getConfigDefaultValues();

		// Process core params first by extracting the values out from the parameter object
		foreach($this->coreParams as $coreParam) {
			if (isset($newParams->$coreParam)) {
				$this->$coreParam = $newParams->$coreParam;
				unset($newParams->$coreParam);
			} else {
				// Check for enforced value
				if (property_exists($configParameters, $coreParam) && is_bool($configParameters->$coreParam)) {
					$this->$coreParam = $configParameters->$coreParam ? 1 : 0;
				} else {
					if (!$this->_isCopy && property_exists($defaults, $coreParam) && ($forceReload || $this->isNew())) {
						$this->$coreParam = $defaults->$coreParam;
					}
				}
			}

			// Remove coreParam from defaults so that only extended params remains in the default
			if (property_exists($defaults, $coreParam)) {
				unset($defaults->$coreParam);
			}
		}

		// If this is a new field, then check if remaining param is empty to fill in the default values
		if ($this->isNew() && !$this->_isCopy) {
			foreach($defaults as $key => $value) {
				if (!property_exists($newParams, $key)) {
					$newParams->$key = $value;
				}
			}
		}

		// Merge the remaining params in to the existing params

		// As long as it is empty ('', null, false, array()), just force it to be an array anyway
		if (empty($this->params)) {
			$this->params = array();
		}

		// If it is a string (won't be empty at this point), then we try to decode it
		// Else just force type casting it to array
		if (is_string($this->params)) {
			$this->params = $jsonLib->isJsonString($this->params) ? (array) $jsonLib->decode($this->params) : array();
		} else {
			$this->params = (array) $this->params;
		}

		// Convert the new params into array
		$newParams = (array) $newParams;

		// Merge the remaining params in with the newParams taking precedence
		$this->params = $jsonLib->encode(array_merge($this->params, $newParams));

		return true;
	}

	/**
	 * Generate a unique key for this field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function generateUniqueKey($existingKeys = null)
	{
		// If this field has not beed saved, then return false
		if (!$this->id) {
			return false;
		}

		// If this field doesn't have a valid app_id, then return false
		if (!$this->app_id) {
			return false;
		}

		$appTable = ES::table('app');
		$appTable->load($this->app_id);

		if (!$appTable->element) {
			return false;
		}

		if (is_null($existingKeys)) {
			$model = ES::model('fields');
			$existingKeys = $model->getProfileUniqueKeys($this->step_id, $this->id);
		}

		$uniqueIndex = 0;

		// Filter by related uniquekeys
		if ($existingKeys) {
			foreach($existingKeys as $key) {
				if (stripos($key, $appTable->element) !== false) {
					$tmp = explode('-', $key);

					if (count($tmp) === 1 && $uniqueIndex === 0) {
						$uniqueIndex = 1;
					}

					if (count($tmp) === 2 && $uniqueIndex <= $tmp[1]) {
						$uniqueIndex = $tmp[1] + 1;
					}
				}
			}
		}

		$this->unique_key = ESJString::strtoupper($appTable->element);

		if ($uniqueIndex !== 0) {
			$this->unique_key .= '-' . $uniqueIndex;
		}

		return $this->unique_key;
	}

	/**
	 * Checks unique key for this field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function checkUniqueKey()
	{
		// Check if this unique key is used
		$model = ES::model('fields');
		$keys = $model->getProfileUniqueKeys($this->step_id, $this->id);


		if (empty($this->unique_key) || empty($keys) || in_array($this->unique_key, $keys)) {
			$this->generateUniqueKey($keys);
		}

		return $this->unique_key;
	}

	/**
	 * Override the parent's delete method to carry out extra maintenance action
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$fieldid = $this->id;
		$result = parent::delete($pk);

		$model = ES::model('fields');
		$model->deleteOptions($fieldid);

		return $result;
	}

	/**
	 * Function to check if this field is a new field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isNew()
	{
		return !($this->id > 0);
	}

	/**
	 * Function to get the app table of this field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getApp()
	{
		// static $app = array();

		// if (empty($app)) {
		// 	$db = ES::db();
		// 	$sql = $db->sql();
		// 	$sql->select('#__social_apps');
		// 	$sql->where('type', 'fields');

		// 	$db->setQuery($sql);
		// 	$result = $db->loadObjectList();

		// 	foreach ($result as $row) {
		// 		$table = ES::table('app');
		// 		$table->bind($row);

		// 		$app[$row->id] = $table;
		// 	}
		// }

		// if (!isset($app[$this->app_id])) {
		// 	return false;
		// }

		// return $app[$this->app_id];

		$table = ES::table('app');
		$table->load($this->app_id);

		return $table;
	}

	/**
	 * Deprecated. Manually load the table instead.
	 *
	 * Function to get the app table of this field.
	 *
	 * @since	1.0
	 * @access	public
	 * @deprecated Deprecated since 1.2. Manually load the table instead.
	 */
	public function getDataTable($uid, $type = SOCIAL_TYPE_USER)
	{
		if (is_null($uid) && $type === SOCIAL_TYPE_USER) {
			$uid = ES::user()->id;
		}

		$table = ES::table('fielddata');
		$state = $table->load(array('field_id' => $this->id, 'uid' => $uid, 'type' => $type));

		if (!$state) {
			$table->field_id = $this->id;
			$table->uid = $uid;
			$table->type = $type;
		}

		return $table;
	}

	/**
	 * Retrieves the icon class for this field
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIcon()
	{
		$params 	= $this->getParams();

		$icon 		= $params->get('icon', '');

		return $icon;
	}

	public function getConfigDefaultValues()
	{
		$config = ES::fields()->getFieldConfigParameters($this->app_id);

		$defaults = new stdClass();

		// Filter out only name and default values
		foreach($config as $name => $params)
		{
			if (is_bool($params))
			{
				$defaults->$name = $params ? 1 : 0;
			}
			else
			{
				if (isset($params->type) && $params->type == 'checkbox')
				{
					$defaults->$name = array();

					foreach($params->option as $option)
					{
						if (isset($option->default) && $option->default)
						{
							$defaults->{$name}[] = $option->value;
						}
					}
				}
				else
				{
					if (property_exists($params, 'default'))
					{
						$defaults->$name = $params->default;
					}
				}
			}
		}

		return $defaults;
	}

	/**
	 * Shorthand to load this field's language
	 *
	 * @since	1.1
	 * @access	public
	 *
	 */
	public function loadLanguage()
	{
		$app = $this->getApp();

		if (!$app)
		{
			return false;
		}

		return $app->loadLanguage();
	}

	public function getClass()
	{
		if (empty($this->id))
		{
			return false;
		}

		$app = $this->getApp();

		$element = $app->element;

		$group = $app->group;

		$file = SOCIAL_FIELDS . '/' . $group . '/' . $element . '/' . $element . '.php';

		if (!JFile::exists($file))
		{
			return false;
		}

		require_once($file);

		$classname 	= 'SocialFields' . ucfirst($group) . ucfirst($element);

		if (!class_exists($classname))
		{
			return false;
		}

		$lib = ES::fields();

		$options = array(
			'element' => $element,
			'group' => $group,
			'params' => $lib->getFieldConfigValues($this),
			'field' => $this
		);

		$class = new $classname($options);

		return $class;
	}

	/**
	 * Returns the data set in accordance to the target uid and type.
	 *
	 * @since  1.2
	 * @access public
	 * @return Mixed    The data set. If the data have key, then it will be array of key value pair. If no key, then it will be the direct data.
	 */
	public function getData($uid, $type)
	{
		$key = $uid . $type;

		// Cache by target uid and type because field are usually loaded on per user basis.

		// Caching all the users on the site might be too heavy

		if (!isset(self::$_fielddata[$key])) {

			self::$_fielddata[$key] = array();

			$db = ES::db();

			$sql = $db->sql();

			$sql->select('#__social_fields_data')
				->where('uid', $uid)
				->where('type', $type)
				->order('field_id')
				->order('datakey');

			$db->setQuery($sql);

			$result = $db->loadObjectList();

			$tmpfieldid = 0;

			$data = array();

			foreach ($result as $row) {

				// If field id doesn't match up to the previous one, then we start anew
				if ($row->field_id != $tmpfieldid) {

					// Mark the last changed field id
					$tmpfieldid = $row->field_id;

					if (empty($row->datakey)) {
						$data[$row->field_id] = $row->data;
					} else {
						$data[$row->field_id][$row->datakey] = $row->data;
					}
				} else {
					if (!is_array($data[$row->field_id])) {
						$data[$row->field_id] = array($data[$row->field_id]);
					}

					if (empty($row->datakey)) {
						$data[$row->field_id][] = $row->data;
					} else {
						$data[$row->field_id][$row->datakey] = $row->data;
					}
				}

				self::$_fielddata[$key] = $data;
			}
		}

		// echo '<pre>';print_r( self::$_fielddata[$key] );echo '</pre>';

		$fielddata = isset(self::$_fielddata[$key][$this->id]) ? self::$_fielddata[$key][$this->id] : '';

		return $fielddata;
	}

	public function bindData($uid, $type, $userData)
	{
		$key = $uid . $type;

		if ($userData) {

			$tmpfieldid = 0;

			$data = array();

			foreach ($userData as $row) {

				// If field id doesn't match up to the previous one, then we start anew
				if ($row->field_id != $tmpfieldid) {

					// Mark the last changed field id
					$tmpfieldid = $row->field_id;

					if (empty($row->datakey)) {
						$data[$row->field_id] = $row->data;
					} else {
						$data[$row->field_id][$row->datakey] = $row->data;
					}
				} else {
					if (!is_array($data[$row->field_id])) {
						$data[$row->field_id] = array($data[$row->field_id]);
					}

					if (empty($row->datakey)) {
						$data[$row->field_id][] = $row->data;
					} else {
						$data[$row->field_id][$row->datakey] = $row->data;
					}
				}

				self::$_fielddata[$key] = $data;
			}

		}

		return true;
	}

	/**
	 * API for field to store data.
	 * Central function to store this fields data for the targetted object. This is cater for field saving that is coming from both User and Group.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function saveData($value, $uid, $type = SOCIAL_TYPE_USER)
	{
		$json = ES::json();

		// Before proceeding, due to multirow feature, we first have to clear out all old data first
		$options = array('field_id' => $this->id, 'uid' => $uid, 'type' => $type);
		$model = ES::model('Fields');
		$model->clearData($options);

		// Since 1.2.6
		// Multi row data feature
		// Added a new column `key`, and it is used to identify the parts of data belonging to a single field
		// Depends on the value type
		// If it is string/integer/boolean, then just proceed as usual, no key is needed
		// If it is an array, then the key should be the index integer
		// If it is an object, then extract the class variable and the key is the property name
		if (is_array($value) || is_object($value)) {

			foreach ($value as $key => $v) {
				$table = ES::table('FieldData');
				$table->load(array('field_id' => $this->id, 'uid' => $uid, 'type' => $type, 'datakey' => $key));

				$table->data = $v;
				$table->raw = $v;

				if ($json->isJsonString($v)) {
					$obj = $json->decode($v);

					$table->data = $v;

					if (is_object($obj)) {
						$table->raw = implode(' ', (array) $obj);
					}
				}

				if (is_array($v) || is_object($v)) {
					$table->data = $json->encode($v);

					if (is_object($v)) {
						$v = ES::makeArray($v);
					}

					// We'll need to check whether submitter wants to store different value for data and raw.
					if (isset($v['data'])) {
						$table->data = $v['data'];

						if (is_array($v['data']) || is_object($v['data'])) {
							$table->data = $json->encode($v['data']);
						}
					}

					// We'll need to check whether the value contains a flat array or a multidimensional array.
					// The value could be a multidimensional array. eg: group/fields/hours #4363#note_157626
					$rv = array_filter((array) $v, 'is_array');

					if (count($rv) > 0) {
						$table->raw = $json->encode($v);
					} else {
						$tmp = (isset($v['raw'])) ? $v['raw'] : $v;
						$table->raw = implode(' ', (array) $tmp);
					}
				}

				$table->store();
			}

			return true;
		}

		$table = ES::table('FieldData');
		$table->load(array('field_id' => $this->id, 'uid' => $uid, 'type' => $type, 'datakey' => ''));

		$table->data = $value;
		$table->raw = $value;

		// Temporary fallback until all fields get their act together and assign proper values
		// Remove this part after fixing all the fields properly
		if ($json->isJsonString($value)) {
			$obj = json_decode($value);

			$table->data = $value;

			if (is_object($obj)) {
				$obj = ES::makeArray($obj);

				foreach ($obj as $k => $v) {
					if (is_array($v)) {
						$obj[$k] = implode(' ', $v);
					}
				}

				$table->raw = implode(' ', $obj);
			} else {

				$table->raw = implode(' ', (array) $obj);
			}

		}

		return $table->store();
	}

	/**
	 * Method to check if this field is conditional
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isConditional()
	{
		return $this->is_conditional;
	}

	/**
	 * Retrieve the conditions rule for this field
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getConditions($decode = true)
	{
		if ($decode) {
			return json_decode($this->conditions);
		}

		return $this->conditions;
	}

	/**
	 * Retrieve the logic for conditional criteria
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getConditionsLogic()
	{
		$params = $this->getParams();

		return $params->get('conditions_criteria', 'and');
	}

	/**
	 * Determine if the field is core/mandatory
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isCore()
	{
		$app = $this->getApp();
		return $app->core;
	}

	/**
	 * Determine if the field is unique
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isUnique()
	{
		$app = $this->getApp();
		return !$this->isCore() && $app->unique;
	}

	/**
	 * Determine if the field is standard type
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isStandard()
	{
		return !$this->isCore() && !$this->isUnique();
	}

	/**
	 * Get the type of the field
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getType()
	{
		if ($this->isCore()) {
			return 'core';
		}

		if ($this->isUnique()) {
			return 'unique';
		}

		return 'standard';
	}
}
