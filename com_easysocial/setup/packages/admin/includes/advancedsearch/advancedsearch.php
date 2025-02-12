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

// Dependencies
ES::import('admin:/includes/fields/dependencies');

class SocialAdvancedSearch extends EasySocial
{
	public $group = 'user';
	public $displayOptions = array();
	public $asField = null;

	public function __construct($group = 'user')
	{
		parent::__construct();

		$this->group = $group;
	}

	public static function factory($group = 'user')
	{
		return new self($group);
	}

	/**
	 * Maps back the call method functions to the adapter.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {
			foreach ($args as &$arg) {
				$refArray[] =& $arg;
			}
		}

		$adapter = $this->getAdapter();

		return call_user_func_array([$adapter, $method], $refArray);
	}

	/**
	 * Determine if the adapter is enabled globaly
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isAdapterEnabled($type)
	{
		if ($type != 'user') {
			return $this->config->get($type . 's.enabled');
		}

		return true;
	}

	/**
	 * Retrieves a list of advanced search types
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAdapters()
	{
		$files = JFolder::files(__DIR__ . '/helpers', '.');
		$adapters = array();

		foreach ($files as $file) {
			$type = str_ireplace('.php', '', $file);

			if (!$this->isAdapterEnabled($type)) {
				continue;
			}

			$className = $this->getAdapterClass($type);
			$obj = new $className();

			$adapters[] = $obj;
		}

		return $adapters;
	}

	public function getAdapterClass($type)
	{
		$fileName = strtolower($type);

		$helperFile	= dirname(__FILE__) . '/helpers/' . $fileName . '.php';

		require_once($helperFile);
		$className = 'SocialAdvancedSearchHelper' . ucfirst($type);

		return $className;
	}

	/**
	 * Retrieves adapter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAdapter()
	{
		static $adapter = null;

		if (is_null($adapter)) {
			$fileName = strtolower($this->group);

			$helperFile	= dirname(__FILE__) . '/helpers/' . $fileName . '.php';

			if (!file_exists($helperFile)) {
				$adapter = false;
				return false;
			}

			require_once($helperFile);
			$className = 'SocialAdvancedSearchHelper' . ucfirst($this->group);

			$adapter = new $className();
		}

		return $adapter;
	}

	/**
	 * Initial a field search object.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function prepField($group, $key, $element, $datakey)
	{
		$field = new SocialAdvancedSearchField($group);

		$field->code = $key;
		$field->type = $element;
		$field->keys = $datakey;

		// $this->asField = $field;

		return $field;
	}


	/**
	 * trigger custom fields app's event
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function trigger($event, SocialAdvancedSearchField $field, $arguments)
	{
		$fieldGroup = $field->group;
		$element = $field->type;

		// Get the file path
		$filePath 	= SOCIAL_APPS . '/' . SOCIAL_APPS_TYPE_FIELDS . '/' . $fieldGroup . '/' . $element . '/' . $element . '.php';

		// If file doesn't exist, ignore this
		if( !JFile::exists( $filePath ) ) {
			return false;
		}

		// Include the fields file.
		include_once( $filePath );

		// Build the class name.
		$className 	= 'SocialFields' . ucfirst( $fieldGroup ) . ucfirst( $element );

		$prop['group'] = $fieldGroup;
		$prop['element'] = $element;

		$class = new $className($prop);

		if (!method_exists($class, $event)) {
			return false;
		}

		// $state = $class->$event($field, $arguments);
		call_user_func_array(array($class, $event), $arguments);
		return true;
	}


	/**
	 * Creates a new criteria object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function createCriteria($fields)
	{
		$criteria = new stdClass();
		$criteria->fields = $fields;
		$criteria->haskeys = $this->hasDataKey();
		$criteria->datakeys = $this->getDataKeyHTML();
		$criteria->operator = $this->getOperatorHTML();
		$criteria->condition = $this->getConditionHTML();
		$criteria->selected = false;

		return $criteria;
	}

	/**
	 * Renders the datakeys html codes
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getDataKeyHTML($options = array() , &$hasKey = false, $selected = '')
	{
		// Determine if the field code and field type is provided
		$fieldCode = $this->normalize($options, 'fieldCode', null);
		$fieldType = $this->normalize($options, 'fieldType', null);

		// Get the list of operators
		$keys = $this->getDataKeys($fieldCode, $fieldType);

		$hasKey = $this->hasDataKey($fieldType);

		$field = $this->prepField($this->group, $fieldCode, $fieldType, $selected);

		$arguments = array($field, &$keys, &$hasKey, &$selected);
		$this->trigger('onPrepareDataKey', $field, $arguments);

		$theme = ES::themes();
		$theme->set('keys', $keys);
		$theme->set('selected', $selected);

		$output = $theme->output('site/search/advanced/datakey');

		return $output;
	}

	/**
	 * Renders the criteria's operator html codes
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOperatorHTML($options = [], $selected = '')
	{
		// Determine if the field code and field type is provided
		$fieldCode = $this->normalize($options, 'fieldCode', null);
		$fieldType = $this->normalize($options, 'fieldType', null);
		$fieldKey = $this->normalize($options, 'fieldKey', null);

		// Get the list of operators
		$operators = $this->getOperators($fieldCode, $fieldType, $fieldKey);

		$field = $this->prepField($this->group, $fieldCode, $fieldType, $fieldKey);
		$arguments = [$field, &$operators, &$selected];
		$this->trigger('onPrepareOperator', $field, $arguments);

		$theme = ES::themes();
		$theme->set('operators', $operators);
		$theme->set('selected', $selected);

		$output = $theme->output('site/search/advanced/operator');

		return $output;
	}

	/**
	 * Renders the condition html codes
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getConditionHTML($options = [], $selected = '')
	{
		// Determine if the field code and field type is provided
		$fieldCode = $this->normalize($options, 'fieldCode', null);
		$fieldType = $this->normalize($options, 'fieldType', null);
		$fieldKey = $this->normalize($options, 'fieldKey', null);
		$operator = $this->normalize($options, 'fieldOperator', null);

		// Birthday has a different type of field type
		if ($fieldType === 'birthday' && $fieldKey) {
			$fieldType = $fieldType . '.' . $fieldKey;
		}

		// Address has a different type of field type
		if ($fieldType === 'address' && $fieldKey && $fieldKey === 'distance') {
			$fieldType = $fieldType . '.' . $fieldKey;
		}

		$condition = $this->getCondition($operator, $fieldType);
		$show = $operator === 'blank' || $operator === 'notblank' ? false : true;

		// Try to find if this is a list type
		$allowed = ['date','dates', 'joomla_lastlogin', 'joomla_joindate','age', 'ages', 'text', 'distance', 'startend'];

		$list = '';

		if (!in_array($condition->type, $allowed)) {
			$list = $this->getOptionList($fieldCode, $fieldType);

			if ($list) {
				$condition->type = 'list';
				$condition->list = $list;
			}
		}

		// Construct the namespace
		$namespace = $condition->type;

		if ($condition->range) {
			$namespace .= '.range';
		}

		$condition->theme = 'site/search/advanced/conditions/' . $namespace;
		$condition->show = $show;

		$field = $this->prepField($this->group, $fieldCode, $fieldType, $fieldKey);
		$arguments = [$field, &$condition, &$selected];

		$this->trigger('onPrepareCondition', $field, $arguments);

		$theme = ES::themes();
		$theme->set('condition', $condition);
		$theme->set('selected', $selected);
		$theme->set('show', $condition->show);

		if ($condition->list) {
			$theme->set('list', $condition->list);
		}

		$output = $theme->output('site/search/advanced/conditions/default');

		return $output;
	}

	/**
	 * get the options list for dropdown list fields.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOptionList($fieldCode, $fieldType)
	{
		$options = null;

		if ($fieldType ==='boolean' || $fieldType === 'allday') {
			$options = [];

			// YES
			$obj = new stdClass();
			$obj->title = JText::_('COM_EASYSOCIAL_YES');
			$obj->value = '1';
			$options[] = $obj;

			// NO
			$obj = new stdClass();
			$obj->title = JText::_('COM_EASYSOCIAL_NO');
			$obj->value = '0';
			$options[] = $obj;

		} else if ($fieldType === 'country') {
			$countryField = ES::table('Field');
			$countryField->load(['unique_key' => $fieldCode]);

			$countryParams = json_decode($countryField->params);
			$source = isset($countryParams->data_source) ? $countryParams->data_source : 'regions';

			$model = ES::model('Regions');
			$dbcountries = $model->getRegions([
				'type' => SOCIAL_REGION_TYPE_COUNTRY,
				'state' => SOCIAL_STATE_PUBLISHED,
				'ordering' => 'name',
				'codeAsIndex' => true
			]);

			if ($source === 'file') {
				$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/countries.json';
				$contents = file_get_contents($file);

				$json = ES::json();
				$countries = $json->decode($contents);
				$countries = (array) $countries;

				if ($countries) {
					foreach($countries as $code => $title) {
						$obj = new stdClass();
						$obj->title = $title;

						$val = $code . '|' . $title;

						if ($dbcountries && isset($dbcountries[$code])) {
							$countryId = $dbcountries[$code]->id;
							$val = $code . '|' . $code . ":" . $countryId . '|' . $title;
						}

						$obj->value = $val;

						$options[] = $obj;
					}
				}
			}

			if ($source === 'regions') {

				if ($dbcountries) {
					foreach($dbcountries as $country) {
						$obj = new stdClass();
						$obj->title = $country->name;
						$obj->value = $country->code . '|' . $country->code . ":" . $country->id . '|' . $obj->title;

						$options[] = $obj;
					}
				}
			}
		} else if ($fieldType === 'gender') {
			$options = [];

			// Male
			$obj = new stdClass();
			$obj->title = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_MALE');
			$obj->value = '1';
			$options[] = $obj;

			// Female
			$obj = new stdClass();
			$obj->title = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_FEMALE');
			$obj->value = '2';
			$options[] = $obj;

			// we should show below option only when gender field support custom value.
			$genderField = ES::table('Field');
			$genderField->load(['unique_key' => $fieldCode]);

			$genderParams = json_decode($genderField->params);
			$allowCustom = isset($genderParams->custom) ? $genderParams->custom : false;

			if ($allowCustom) {
				$model = ES::model('Search');
				$items = $model->getFieldOptionList($fieldCode, $fieldType);
				if ($items) {
					foreach ($items as $item) {
						if ($item->value) {
							$obj = new stdClass();
							$obj->title = JText::_($item->title);
							$obj->value = $item->value;
							$options[] = $obj;
						}
					}
				}

				// Other
				$obj = new stdClass();
				$obj->title = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_GENDER_OTHERS');
				$obj->value = '3';
				$options[] = $obj;
			}

		} else if ($fieldType == 'relationship') {
			$relationship = ES::table('Field');
			$relationship->load(array('unique_key' => $fieldCode));
			$allowedTypes = $relationship->getParams()->get('relationshiptype');

			// load up relationshop options.
			$file = JPATH_ROOT . '/media/com_easysocial/apps/fields/user/relationship/config/config.json';
			$contents = file_get_contents( $file );

			$json = ES::json();
			$data = $json->decode($contents);

			if ($data && isset($data->relationshiptype) && isset($data->relationshiptype->option)) {
				foreach ($data->relationshiptype->option as $item) {
					if (empty($allowedTypes) || (!empty($allowedTypes) && in_array($item->value, $allowedTypes))) {
						$obj = new stdClass();
						$obj->title = JText::_($item->label);
						$obj->value = $item->value;
						$options[] = $obj;
					}
				}
			}
		} else {
			$model = ES::model('Search');
			$options = $model->getFieldOptionList($fieldCode, $fieldType);

			if (!$options) {
				$options = null;
			}
		}

		return $options;
	}

	/**
	 * Renders the criteria's html codes
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCriteriaHTML($options = array(), $values = array())
	{
		// Default values
		$criterias = array();

		// Get the list of fields
		$fields = $this->getFields();

		// Set the default values for condition and operator
		$operatorHTML = '';
		$conditionHTML = '';

		$isTemplate = isset($options['isTemplate']) ? $options['isTemplate'] : false;

		// Check if there are any values that need to be pre-populated
		if (isset($values['criterias']) && !empty($values['criterias'])) {

			$total = count($values['criterias']);

			for ($i = 0; $i < $total; $i++) {
				$field = $values['criterias'][$i];

				if (!$field) {
					continue;
				}

				// Since the values are stored in CODE|TYPE, we need to get the correct values
				$data = $this->getFieldData($field);

				// Get the operator base on the current index
				$datakey = isset($values['datakeys'][$i]) ? $values['datakeys'][ $i ] : '';

				// Get the operator base on the current index
				$operator = $values['operators'][$i];

				// Get the entered value base on the current index
				$value = isset($values['conditions'][$i]) ? $values['conditions'][$i] : '';

				$fieldOptions = array('fieldCode' => $data->code, 'fieldType' => $data->type);

				$criteria = new stdClass();
				$criteria->fields = $fields;
				$hasKey = false;
				$criteria->datakeys = $this->getDataKeyHTML($fieldOptions, $hasKey, $datakey);
				$criteria->haskeys = $hasKey;

				if ($datakey) {
					$fieldOptions['fieldKey'] = $datakey;
				}

				$criteria->operator = $this->getOperatorHTML($fieldOptions, $operator);

				$fieldOptions['fieldOperator'] = $operator;
				$criteria->condition = $this->getConditionHTML($fieldOptions, $value);
				$criteria->selected = $field;

				$criterias[] = $criteria;
			}
		} else {

			// Create a new default criteria if there wasn't any values
			$criterias[] = $this->createCriteria($fields);
		}

		$theme = ES::themes();
		$theme->set('criterias', $criterias);
		$theme->set('isTemplate', $isTemplate);

		$output = $theme->output('site/search/advanced/criteria');

		return $output;
	}

	public function getFields($elements = [])
	{
		static $fields = [];

		if (!$fields) {
			JFactory::getLanguage()->load('com_easysocial' , JPATH_ROOT . '/administrator');

			$adapter = $this->getAdapter();

			if (!$adapter) {
				return $fields;
			}

			$fields = $adapter->getFields($elements);
		}

		return $fields;
	}

	/**
	 * Determines if a field has a data key
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasDataKey($fieldType = '')
	{
		$fields = array('address', 'joomla_fullname', 'birthday');

		if (in_array($fieldType, $fields)) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the default datakeys
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getDataKeys( $fieldCode = null , $fieldType = null)
	{
		$return = array();

		if ($fieldType == 'address') {

			// Get the visible field and set the show parameters
			$default = array(
				'address1' => true,
				'address2' => true,
				'city' => true,
				'state' => true,
				'zip' => true,
				'country' => true
			);

			$show = array();

			$adapter = $this->getAdapter();
			$fields = $adapter->loadFields($fieldCode, $fieldType);

			if ($fields) {

				$jsonLib = ES::json();

				foreach ($fields as $field) {

					$params = array();

					if ($field->params) {
						// If it is a string (won't be empty at this point), then we try to decode it
						// Else just force type casting it to array
						if (is_string($field->params)) {
							$params = $jsonLib->isJsonString($field->params) ? (array) $jsonLib->decode($field->params) : array();
						} else {
							$params = (array) $field->params;
						}

						foreach ($default as $key => $val) {
							if (isset($show[$key]) && $show[$key]) {
								continue;
							}

							$paramKey = 'show_' . $key;

							if (isset($params[$paramKey])) {
								$show[$key] = $params[$paramKey];
							} else {
								$show[$key] = $val;
							}
						}
					}

				}
			}

			if (!$show) {
				$show = $default;
			}

			$return['address'] = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_ADDRESS');

			foreach ($show as $key => $val) {
				if ($val) {
					$return[$key] = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_' . strtoupper($key));
				}
			}

			$return['distance'] = JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_DISTANCE');


		} else if ($fieldType == 'joomla_fullname') {

			$return = array('name' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_NAME'), //full names
							'first' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_FIRST'),
							'middle' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_MIDDLE'),
							'last' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_LAST'));

		} else if ($fieldType == 'birthday') {

			$return = array('date' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_DATE'), //date search
							'age' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_KEY_LABEL_AGE')); // age search
		}

		return $return;
	}


	/**
	 * Retrieves the default operators
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOperators( $fieldCode = null , $fieldType = null, $fieldKey = null )
	{
		$config = ES::config();
		$searchUnit = $config->get('general.location.proximity.unit','mile');

		$common = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO'),
					'contain' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_CONTAINS'),
					'notcontain' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_DOES_NOT_CONTAIN'),
					'startwith' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_STARTS_WITH'),
					'endwith' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_ENDS_WITH'),
					'blank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_EMPTY'),
					'notblank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_NOT_EMPTY')
					);

		// for address - distance
		$distance = array(
					'lessequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_WITHIN_' . $searchUnit),
					'greater' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_FURTHER_' . $searchUnit),
					);

		$relationOption = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO')
					);

		// for radio buttons, country, gender
		$option = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO'),
					'blank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_EMPTY'),
					'notblank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_NOT_EMPTY')
					);

		// for checkbox, multilist, multidropdown
		$multioption = array(
					'contain' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notcontain' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO'),
					'blank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_EMPTY'),
					'notblank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_NOT_EMPTY')
					);

		// for datetime
		$date = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO'),
					'greater' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN'),
					'greaterequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN_OR_EQUAL_TO'),
					'less' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN'),
					'lessequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN_OR_EQUAL_TO'),
					'between' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_BETWEEN'),
					'blank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_EMPTY'),
					'notblank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_NOT_EMPTY')
					);

		$birthdate = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO'),
					'greater' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN'),
					'greaterequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN_OR_EQUAL_TO'),
					'less' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN'),
					'lessequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN_OR_EQUAL_TO'),
					'between' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_BETWEEN')
					);


		// for birthday - age
		$age = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'less' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN'), // when we searching for age greater than x, in date, it mean lesser than
					'lessequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN_OR_EQUAL_TO'), // when we searching for age greater than x, in date, it mean lesser than
					'greater' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN'),
					'greaterequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN_OR_EQUAL_TO'),
					'between' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_BETWEEN')
					);

		$numeric = array(
					'equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'),
					'notequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_NOT_EQUAL_TO'),
					'greater' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN'),
					'greaterequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_GREATER_THAN_OR_EQUAL_TO'),
					'less' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN'),
					'lessequal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_LESSER_THAN_OR_EQUAL_TO'),
					'between' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_BETWEEN'),
					'blank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_EMPTY'),
					'notblank' => JText::_('COM_ES_ADVANCED_SEARCH_OPERATOR_IS_NOT_EMPTY')
					);

		$operators = array();

		switch( $fieldType )
		{
			case 'checkbox':
			case 'multilist':
			case 'multidropdown':
				$operators = $multioption;
				break;
			case 'relationship':
				$operators = $relationOption;
				break;
			case 'country':
			case 'gender':
			case 'dropdown':
			case 'boolean':
			case 'allday':
			case 'radio':
			// case 'joomla_timezone':
			// case 'joomla_user_editor':
			// case 'joomla_language':
				$operators = $option;
				break;

			case 'numeric':
				$operators = $numeric;
				break;
			case 'price':
				$operators = $numeric;
				unset($operators['blank']);
				unset($operators['notblank']);
				break;
			case 'datetime':
			case 'joomla_lastlogin':
			case 'joomla_joindate':
			case 'startend':
				$operators = $date;
				break;

			case 'birthday':
				if ($fieldKey && $fieldKey == 'age') {
					$operators = $age;
				} else {
					$operators = $birthdate;
				}
				break;

			case 'address':
				if ($fieldKey && $fieldKey == 'distance') {
					$operators = $distance;
				} else {
					$operators = $common;
				}
				break;

			case 'joomla_username':
			case 'joomla_email':
			case 'email':
				$operators = array('equal' => JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_OPERATOR_IS_EQUAL_TO'));
				break;

			default:
				$operators = $common;
				break;
		}

		return $operators;
	}

	/**
	 * Retrieves a list of default search conditions
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCondition($operator = null, $fieldType = null)
	{
		$condition = new SocialAdvancedSearchCondition();
		$condition->type = 'text';
		$condition->value = '';
		$condition->range = false;
		$condition->list = '';
		$condition->operator = $operator;
		$condition->options = [];

		// Date inputs
		$dates = ['datetime', 'birthday', 'joomla_lastlogin', 'joomla_joindate', 'birthday.date', 'startend'];

		if (in_array($fieldType, $dates)) {
			$condition->type = 'date';

			if ($operator === 'between') {
				$condition->range = true;
			}

			return $condition;
		}

		// Birthday inputs
		if ($fieldType === 'birthday.age') {
			$condition->type = 'age';

			if ($operator === 'between') {
				$condition->range = true;
			}

			return $condition;
		}

		// Numeric inputs
		if ($fieldType === 'numeric') {
			$condition->type = 'numeric';

			if ($operator === 'between') {
				$condition->range = true;
			}

			return $condition;
		}

		// Numeric inputs
		if ($fieldType === 'price') {
			$condition->type = 'numeric';

			if ($operator == 'between') {
				$condition->range = true;
			}

			return $condition;
		}

		// Distance field
		if ($fieldType === 'address.distance') {
			$condition->type = 'distance';
			return $condition;
		}

		// Gender and relationships
		$genders = ['relationship', 'gender'];

		if (in_array($fieldType, $genders)) {
			$condition->type = 'gender';

			return $condition;
		}

		// Other known
		$others = ['checkbox', 'radio', 'dropdown', 'boolean', 'country', 'multilist', 'multidropdown', 'allday'];

		if (in_array($fieldType, $others)) {
			$condition->type = $fieldType;

			return $condition;
		}

		return $condition;
	}

	/**
	 * Performs the search on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function search($options = array())
	{
		$this->setDisplayOptions($options);

		$adapter = $this->getAdapter();

		return $adapter->search($options);
	}

	/**
	 * @since	2.0
	 * @access	public
	 */
	public function setDisplayOptions($options)
	{
		if (!isset($options['criterias'])) {
			return;
		}

		$criterias = is_string($options['criterias']) ? array($options['criterias']) : $options['criterias'];
		$datakeys = is_string($options['datakeys']) ? array($options['datakeys']) : $options['datakeys'];
		$conditions = is_string($options['conditions']) ? array($options['conditions']) : $options['conditions'];

		$totalC = count($criterias);

		for ($i = 0; $i < $totalC; $i++) {
			$field = $criterias[$i];
			$datakey = isset($datakeys[$i]) ? $datakeys[$i] : '';

			if (!isset($conditions[$i])) {
				continue;
			}

			$condition = $conditions[$i];

			// Since the values are stored in CODE|TYPE, we need to get the correct values
			$data = explode('|', $field);
			$fieldCode = isset($data[0]) ? $data[0] : '';
			$fieldType = isset($data[1]) ? $data[1] : '';

			// show gender
			if ($fieldType == 'gender') {
				$this->displayOptions['showGender'] = true;
				$this->displayOptions['GenderCode'] = $fieldCode;
			}

			// show last login date
			if ($fieldType == 'joomla_lastlogin') {
				$this->displayOptions['showLastLogin'] = true;
				$this->displayOptions['lastLoginCode'] = $fieldCode;
			}

			// show last login date
			if ($fieldType == 'joomla_joindate') {
				$this->displayOptions['showJoinDate'] = true;
				$this->displayOptions['joinDateCode'] = $fieldCode;
			}

			// show distance
			if ($fieldType == 'address' && $datakey == 'distance') {

				$inputdata = explode('|', $condition);

				$this->displayOptions['showDistance'] = true;
				$this->displayOptions['AddressCode'] = $fieldCode;

				$lat = isset($inputdata[1]) ? $inputdata[1] : 0;
				$lon = isset($inputdata[2]) ? $inputdata[2] : 0;

				if (!$lat && !$lon) {
					$my = ES::user();
					$address = $my->getFieldValue('ADDRESS');
					$lat = $address->value->latitude;
					$lon = $address->value->longitude;
				}

				$this->displayOptions['AddressLat'] = $lat;
				$this->displayOptions['AddressLon'] = $lon;
			}

		}
	}

	/**
	 * Formats the field data string into proper data
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFieldData($field)
	{
		list($code, $type) = explode('|', $field);

		$data = new stdClass();
		$data->code = $code;
		$data->type = $type;

		return $data;
	}

	/**
	 * @since	2.0
	 * @access	public
	 */
	public function getDisplayOptions()
	{
		return $this->displayOptions;
	}

	/**
	 * @since	2.0
	 * @access	public
	 */
	public function getTotal()
	{
		$adapter = $this->getAdapter();

		return $adapter->total;
	}

	/**
	 * @since	2.0
	 * @access	public
	 */
	public function getNextLimit()
	{
		$adapter = $this->getAdapter();

		return $adapter->nextlimit;
	}
}


class SocialAdvancedSearchField
{
	public $group = null;
	public $code = null;
	public $type = null;
	public $keys = null;

	public function __construct($group = 'user')
	{
		$this->group = $group;
	}
}

class SocialAdvancedSearchCondition
{
	public $field = null;
	public $type = null;
	public $value = null;
	public $range = null;
	public $list = null;
	public $options = null;
	public $theme = null;
	public $show = null;
	public $operator = null;

	public function __construct()
	{
		$this->show = false;
		$this->range = false;
		$this->options = array();
	}
}


