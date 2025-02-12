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

ES::import('admin:/includes/fields/dependencies');
ES::import('fields:/user/address/helper');

class SocialFieldsUserAddress extends SocialFieldItem
{
	public function getValue()
	{
		$container = $this->getValueContainer();
		$container->value = $this->getAddressValue($container->data);

		return $container;
	}

	public function getDisplayValue()
	{
		$address = $this->getValue();
		return $address->toString();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onRegister(&$post, &$registration)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$address = $this->getAddressValue($value);

		// Set the value.
		$this->set('value', $address);

		// Set custom required option
		$isRequired = false;

		if ($this->params->get('use_maps')) {
			$isRequired = $this->params->get('required_address');

			$this->set('required', (int) $isRequired);
		} else {

			// Get the countries list
			$countries = SocialFieldsUserAddressHelper::getCountries($this->params->get('data_source'));

			// Set the countries
			$this->set('countries', $countries);

			// If data source is using database (regions) and address has a country value, then we get the region object for the country, then subsequently the states.
			if ($this->params->get('data_source') === 'regions' && $this->params->get('show_state') && !empty($address->country)) {
				$states = SocialFieldsUserAddressHelper::getStates($address->country, $this->params->get('sort'));

				$this->set('states', $states);
			}

			// Get the requirements and set the required parameters
			$required = array(
				'address1' => $this->params->get('required_address1'),
				'address2' => $this->params->get('required_address2'),
				'city' => $this->params->get('required_city'),
				'state' => $this->params->get('required_state'),
				'zip' => $this->params->get('required_zip'),
				'country' => $this->params->get('required_country')
			);

			foreach ($required as $requireAddr => $requireVal) {
				if (!$requireVal) {
					$required[$requireAddr] = false;
				}
			}

			// Set the jsonencoded string for required data
			$this->set('required', ES::json()->encode($required));

			// Get the visible field and set the show parameters
			$show = array(
				'address1' => $this->params->get('show_address1'),
				'address2' => $this->params->get('show_address2'),
				'city' => $this->params->get('show_city'),
				'state' => $this->params->get('show_state'),
				'zip' => $this->params->get('show_zip'),
				'country' => $this->params->get('show_country')
			);

			// Set the jsonencoded string for required data
			$this->set('show', ES::json()->encode($show));

			foreach ($required as $key => $value) {

				if ($value) {
					$isRequired = true;
					break;
				}
			}
		}

		// Ensure that the value is 'false' instead of 0
		if (!$isRequired) {
			$isRequired = false;
		}

		$this->set('options', array('required' => $isRequired));

		// Detect if there's any errors
		$error  = $registration->getErrors($this->inputName);

		// Set the error
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onRegisterValidate(&$post, &$registration)
	{
		$data = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($data);
	}

	/**
	 * Validate mini registration
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onRegisterMiniValidate(&$post, &$registration)
	{
		$data = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		// Only validate the field if it set the field to be visible in mini registration
		if ($this->params->get('visible_mini_registration')) {
			return $this->validateInput($data);
		}
	}

	/**
	 * Executes before a user's registration is saved.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$address = $this->getAddressValue($value);

		if (!$this->params->get('use_maps') && $this->params->get('geocode')) {
			$address->geocode();
		}

		$post[$this->inputName] = $address->export();
	}

	/**
	 * Displays the field input for user when they edit their account.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		// Check if there is values in the post first
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $this->value;

		// Get the value
		$address = $this->getAddressValue($value);

		// Set the value.
		$this->set('value', $address);

		// Set custom required option
		$isRequired = false;

		if ($this->params->get('use_maps')) {
			$isRequired = $this->params->get('required_address');

			$this->set('required', (int) $isRequired);
		} else {

			// Get the countries list
			$countries  = SocialFieldsUserAddressHelper::getCountries($this->params->get('data_source'));

			// Set the countries
			$this->set('countries', $countries);

			// If data source is using database (regions) and address has a country value, then we get the region object for the country, then subsequently the states.
			if ($this->params->get('data_source') === 'regions' && $this->params->get('show_state') && !empty($address->country)) {
				$states = SocialFieldsUserAddressHelper::getStates($address->country, $this->params->get('sort'));

				$this->set('states', $states);
			}

			// Get the requirements and set the required parameters
			$required = array(
				'address1' => $this->params->get('required_address1'),
				'address2' => $this->params->get('required_address2'),
				'city' => $this->params->get('required_city'),
				'state' => $this->params->get('required_state'),
				'zip' => $this->params->get('required_zip'),
				'country' => $this->params->get('required_country')
			);

			foreach ($required as $requireAddr => $requireVal) {
				if (!$requireVal) {
					$required[$requireAddr] = false;
				}
			}

			// Set the jsonencoded string for required data
			$this->set('required', ES::json()->encode($required));

			// Get the visible field and set the show parameters
			$show = array(
				'address1' => $this->params->get('show_address1'),
				'address2' => $this->params->get('show_address2'),
				'city' => $this->params->get('show_city'),
				'state' => $this->params->get('show_state'),
				'zip' => $this->params->get('show_zip'),
				'country' => $this->params->get('show_country')
			);

			// Set the jsonencoded string for required data
			$this->set('show', ES::json()->encode($show));

			foreach ($required as $key => $value) {
				if ($value) {
					$isRequired = true;
					break;
				}
			}
		}

		// Ensure that the value is 'false' instead of 0
		if (!$isRequired) {
			$isRequired = false;
		}

		$this->set('options', array('required' => $isRequired));

		// Get field error
		$error = $this->getError($errors);

		// Set field error
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onEditValidate(&$post, &$user)
	{
		$data = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateInput($data);
	}

	public function onEditBeforeSave(&$post, &$user)
	{
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		$address = $this->getAddressValue($value);

		if (!$this->params->get('use_maps') && $this->params->get('geocode')) {
			$address->geocode();
		}

		$post[$this->inputName] = $address->export();

		return true;
	}

	/**
	 * Converts the data into a correct value representation.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getAddressValue($data = '')
	{
		$address = new SocialFieldsUserAddressObject($data);

		return $address;
	}

	/**
	 * format the value used in data export
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onExport($data, $user)
	{
		$field = $this->field;

		$formatted = array('address' => '',
							'address1' => '',
							'address2' => '',
							'city' => '',
							'state' => '',
							'zip' => '',
							'country' => '',
							'latitude' =>'',
							'longitude' =>''
						);

		if (isset($data[$field->id])) {
			$formatted['address'] = isset($data[$field->id]['address']) ? $data[$field->id]['address'] : '';
			$formatted['address1'] = isset($data[$field->id]['address1']) ? $data[$field->id]['address1'] : '';
			$formatted['address2'] = isset($data[$field->id]['address2']) ? $data[$field->id]['address2'] : '';
			$formatted['city'] = isset($data[$field->id]['city']) ? $data[$field->id]['city'] : '';
			$formatted['state'] = isset($data[$field->id]['state']) ? $data[$field->id]['state'] : '';
			$formatted['zip'] = isset($data[$field->id]['zip']) ? $data[$field->id]['zip'] : '';
			$formatted['country'] = isset($data[$field->id]['country']) ? $data[$field->id]['country'] : '';
			$formatted['latitude'] = isset($data[$field->id]['latitude']) ? $data[$field->id]['latitude'] : '';
			$formatted['longitude'] = isset($data[$field->id]['longitude']) ? $data[$field->id]['longitude'] : '';
		}

		return $formatted;
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when their profile is viewed.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onDisplay($user)
	{
		$address = $this->getAddressValue($this->value);

		if (!$this->params->get('use_maps') && $address->isEmpty()) {
			return;
		}

		if ($this->params->get('use_maps') && (empty($address->latitude) || empty($address->longitude))) {
			return;
		}

		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$field = $this->field;

		$advGroups = array(SOCIAL_FIELDS_GROUP_GROUP, SOCIAL_FIELDS_GROUP_USER, SOCIAL_FIELDS_GROUP_EVENT, SOCIAL_FIELDS_GROUP_PAGE);

		$advsearchLink = array();
		$addressKeys = array('city', 'state', 'zip', 'country');
		$mapsKeys = array('city', 'state', 'country');


		if (in_array($field->type, $advGroups) && $field->searchable) {

			$params = array( 'layout' => 'advanced' );

			if ($field->type != SOCIAL_FIELDS_GROUP_USER) {
				$params['type'] = $field->type;
				$params['uid'] = $field->uid;
			}

			if ($this->params->get('use_maps')) {

				$hasItem = false;
				$idx = 0;

				foreach ($mapsKeys as $key) {
					if (!empty($address->$key) && $this->params->get('show_' . $key)) {

						$params['criterias[' . $idx . ']'] = $field->unique_key . '|' . $field->element;
						$params['datakeys[' . $idx . ']'] = $key;
						$params['conditions[' . $idx . ']'] = $address->$key;
						$params['operators[' . $idx . ']'] = 'equal';

						$idx++;
						$hasItem = true;
					}
				}

				if ($hasItem) {
					$advsearchLink['map'] = FRoute::search($params);
				}

			} else {

				$params['criterias[]'] = $field->unique_key . '|' . $field->element;

				foreach ($addressKeys as $key) {
					if (!empty($address->$key)) {
						$params['datakeys[]'] = $key;
						$params['conditions[]'] = $address->$key;
						$params['operators[]'] = 'equal';

						$advsearchLink[$key] = FRoute::search($params);

						unset($params['datakeys[]']);
						unset($params['conditions[]']);
						unset($params['operators[]']);
					}
				}
			}
		}

		$this->set('advancedsearchlink', $advsearchLink);

		// Push vars to the theme
		$this->set('value', $address);

		// Get the visible field and set the show parameters
		$show = array(
			'address1' => $this->params->get('show_address1'),
			'address2' => $this->params->get('show_address2'),
			'city' => $this->params->get('show_city'),
			'state' => $this->params->get('show_state'),
			'zip' => $this->params->get('show_zip'),
			'country' => $this->params->get('show_country')
		);

		$this->set('show', $show);

		$url = 'https://maps.google.com/?q=' . $address->toString();

		if ($this->config->get('location.provider') == 'osm') {
			$url = 'https://www.openstreetmap.org/#map=16/' . $address->latitude . '/' . $address->longitude;
		}

		$this->set('url', $url);
		$this->set('locationProvider', $this->config->get('location.provider'));

		return $this->display();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$value = $this->getDisplayValue();

		if (!$value) {
			return '';
		}

		// retrieve field data
		$field = $this->field;

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = $value;

		return $data;
	}

	/**
	 * return formated string from the fields value
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onIndexer($userFieldData)
	{
		if (!$this->field->searchable) {
			return false;
		}

		$data = $this->getAddressValue($userFieldData);
		$content = $data->toString();

		if (!$content) {
			return false;
		}

		return $content;
	}

	/**
	 * return formated string from the fields value
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onIndexerSearch($itemCreatorId, $keywords, $userFieldData)
	{
		if (!$this->field->searchable) {
			return false;
		}

		$data = $this->getAddressValue($userFieldData);

		$content = '';

		if (ESJString::stristr($data->address1, $keywords) !== false) {
			$content = $data->address1;
		} elseif (ESJString::stristr($data->address2, $keywords) !== false) {
			$content = $data->address2;
		} elseif (ESJString::stristr($data->city, $keywords) !== false) {
			$content = $data->city;
		} elseif (ESJString::stristr($data->state, $keywords) !== false) {
			$content = $data->state;
		} elseif (ESJString::stristr($data->country, $keywords) !== false) {
			$content = $data->country;
		}

		if ($content) {
			$my = ES::user();
			$privacyLib = ES::privacy($my->id);

			if (! $privacyLib->validate('core.view', $this->field->id, SOCIAL_TYPE_FIELD, $itemCreatorId)) {
				return -1;
			} else {
				// okay this mean the user can view this fields. let hightlight the content.
				// building the pattern for regex replace
				$searchworda = preg_replace('#\xE3\x80\x80#s', ' ', $keywords);
				$searchwords = preg_split("/\s+/u", $searchworda);
				$needle = $searchwords[0];
				$searchwords = array_unique($searchwords);

				$pattern = '#(';
				$x = 0;

				foreach ($searchwords as $k => $hlword) {
					$pattern .= $x == 0 ? '' : '|';
					$pattern .= preg_quote($hlword, '#');
					$x++;
				}
				$pattern .= ')#iu';

				$content = preg_replace($pattern, '<span class="search-highlight">\0</span>', $content);
				$content = JText::sprintf('PLG_FIELDS_ADDRESS_SEARCH_RESULT', $content);
			}
		}

		if ($content)
			return $content;
		else
			return false;
	}


	/**
	 * return list of users which match the address data of current logged in user.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function onFriendSuggestSearch($user, $userFieldData)
	{
		// Get the address value
		$data = $this->getAddressValue($userFieldData);

		if (empty($data->city) || empty($data->state) || empty($data->country)) {
			return false;
		}

		$searchphase = '+' . $data->city . ' ' . $data->state . ' ' . $data->country;
		$searchphase = str_replace(' ', ' +', $searchphase);

		$db = ES::db();

		$query = 'select a.' . $db->nameQuote('uid') . ' as ' . $db->nameQuote('ffriend_id') . ', MATCH(a.' . $db->nameQuote('raw') . ') AGAINST (' . $db->Quote($searchphase) . ' IN BOOLEAN MODE) AS score';
		$query .= ' FROM ' . $db->nameQuote('#__social_fields_data') . 'as a';
		$query .= ' WHERE MATCH(a.' . $db->nameQuote('raw') . ') AGAINST (' . $db->Quote($searchphase) . ' IN BOOLEAN MODE)';
		$query .= ' and a.' . $db->nameQuote('field_id') . ' = ' . $db->Quote($this->field->id);
		$query .= ' and a.' . $db->nameQuote('uid') . ' != ' . $db->Quote($user->id);

		$query .= ' and not exists (';
		$query .= '     select if (b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($user->id) . ', b.' . $db->nameQuote('target_id') . ', b.' . $db->nameQuote('actor_id') . ') AS ' . $db->nameQuote('friend_id');
		$query .= '         FROM ' . $db->nameQuote('#__social_friends') . ' as b WHERE (b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($user->id) . ' or b.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($user->id) . ')';
		$query .= '         and b.' . $db->nameQuote('state') . ' != ' . $db->Quote(SOCIAL_FRIENDS_STATE_REJECTED);
		// $query .= '      and b.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_FRIENDS_STATE_FRIENDS);
		$query .= '         and if (b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($user->id) . ', b.' . $db->nameQuote('target_id') . ', b.' . $db->nameQuote('actor_id') . ') = a.' . $db->nameQuote('uid');
		$query .= ')';
		$query .= ' order by score desc';

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if (count($result) > 0) {
			// we need to reset the score because in friend mode, the score will be
			// use to show no. mutual friends.
			for ($i=0; $i < count($result); $i++) {
				$item =& $result[$i];
				$item->score = 0;
			}
		}

		return $result;
	}

	public function validateInput($data)
	{
		// Get the default value.
		$address = $this->getAddressValue($data);

		if ($this->params->get('use_maps')) {
			if ($this->params->get('required_address') && (empty($address->latitude) || empty($address->longitude))) {
				$this->setError(JText::_('PLG_FIELDS_ADDRESS_MAP_PLEASE_ENTER_LOCATION'));
				return false;
			}
		} else {
			$fields = array('address1', 'address2', 'city', 'state', 'zip', 'country');

			foreach ($fields as $field) {
				$value = trim($address->$field);

				if (empty($value) && $this->params->get('required_' . $field) && $this->params->get('show_' . $field)) {
					$this->setError(JText::_('PLG_FIELDS_ADDRESS_PLEASE_ENTER_' . strtoupper($field)));
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if this field is complete.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onFieldCheck($user)
	{
		return $this->validateInput($this->value);
	}

	/**
	 * Checks if this field is filled in.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function onProfileCompleteCheck($user)
	{
		$strict = ES::config()->get('user.completeprofile.strict');

		$address = $this->getAddressValue($this->value);

		if ($this->params->get('use_maps')) {
			if (!$strict && !$this->params->get('required_address')) {
				return true;
			}

			if(empty($address->latitude) || empty($address->longitude)) {
				return false;
			}
		} else {
			$fields = array('address1', 'address2', 'city', 'state', 'zip', 'country');

			if ($strict) {
				// If strict mode is on, then as long as the field is showing, we check
				foreach ($fields as $field) {
					if ($this->params->get('show_' . $field) && empty($address->$field)) {
						return false;
					}
				}
			} else {
				// If strict mode is off, then we only check against required fields
				foreach ($fields as $field) {
					if ($this->params->get('show_' . $field) && $this->params->get('required_' . $field) && empty($address->$field)) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function onGetValue($user)
	{
		return $this->getValue();
	}

	public function onOAuthGetUserPermission(&$permissions)
	{
		$permissions[] = 'user_location';
	}

	public function onOAuthGetMetaFields(&$fields)
	{
		$fields[] = 'location';
	}

	public function onRegisterOAuthBeforeSave(&$post, $client)
	{
		if (empty($post['location']) || empty($post['location']['name'])) {
			return;
		}

		$lib = ES::get('Geocode');

		$data = $lib->geocode($post['location']['name']);

		if (empty($data->address_components)) {
			return;
		}

		$components = array();

		foreach ($data->address_components as $index => $component) {
			if (!empty($component->types[0])) {
				$components[$component->types[0]] = $component->short_name;
			}
		}

		$mapping = array(
			'address1' => array('street_address', 'route'),
			'address2' => array('intersection', 'colloquial_area', 'neighborhood', 'premise', 'subpremise'),
			'city' => array('locality', 'sublocality', 'sublocality_level_1', 'sublocality_level_2', 'sublocality_level_3', 'sublocality_level_4', 'sublocality_level_5'),
			'state' => array('administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3'),
			'zip' => 'postal_code',
			'country' => 'country'
		);

		$legacy = array();

		foreach ($mapping as $key => $value) {
			$legacy[$key] = '';

			if (is_array($value)) {
				foreach ($value as $v) {
					if (!empty($components[$v])) {
						$legacy[$key] = $components[$v];

						break;
					}
				}
			} else {
				if (!empty($components[$value])) {
					$legacy[$key] = $components[$value];
				}
			}
		}

		$legacy['components'] = $components;
		$legacy['address'] = $data->formatted_address;
		$legacy['latitude'] = $data->geometry->location->lat;
		$legacy['longitude'] = $data->geometry->location->lng;

		$post[$this->inputName] = $legacy;
	}
}

class SocialFieldsUserAddressObject
{
	public $address1 = '';
	public $address2 = '';
	public $city = '';
	public $state = '';
	public $zip = '';
	public $country = '';

	// Geocode
	public $latitude = '';
	public $longitude = '';

	// Full address
	public $address = '';

	public function __construct($data = null)
	{
		$this->load($data);
	}

	public function load($data)
	{
		if (empty($data)) {
			return false;
		}

		$vars = (object) get_object_vars($this);

		$data = ES::makeObject($data);

		if (!$data) {
			return false;
		}

		foreach ($data as $key => $val) {
			if (isset($vars->$key) && !empty($val)) {
				$this->$key = $val;
			}
		}

		return true;
	}

	public function toArray()
	{
		return (array) $this;
	}

	public function toJson()
	{
		return ES::json()->encode($this);
	}

	public function toString($del = ' ')
	{
		if (!empty($this->address)) {
			return $this->address;
		}

		$components = array('address1', 'address2', 'city', 'state', 'zip', 'country');

		$address = array();

		foreach ($components as $key) {
			if (!empty($this->$key)) {
				$address[] = $this->$key;
			}
		}

		$address = trim(implode($del, $address));

		return $address;
	}

	public function isEmpty()
	{
		$components = array('address1', 'address2', 'city', 'state', 'zip', 'country');

		foreach ($components as $key) {
			if (!empty($this->$key)) {
				return false;
			}
		}

		return true;
	}

	public function geocode()
	{
		$lib = ES::get('GeoCode');
		$address = $this->toString(',');

		$data = $lib->geocode($address);

		if (!$data) {
			return;
		}

		// We only want the geometry data here
		$geometry = $data->geometry;

		$this->latitude = $geometry->location->lat;
		$this->longitude = $geometry->location->lng;
	}

	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Prepares this object to be save ready.
	 *
	 * @since  1.3
	 * @access public
	 * @return SocialFieldsUserAddressObject    The address object.
	 */
	public function export()
	{
		if (empty($this->address)) {
			$this->address = $this->toString();
		}

		return $this;
	}
}


class SocialFieldsUserAddressValue extends SocialFieldValue
{

	public function toDisplay($options = '', $linkToAdvancedSearch = false)
	{
		if (! isset($this->value)) {
			return '';
		}

		$display = $options['display'];

		$config = ES::config();

		$params = array( 'layout' => 'advanced' );

		$params['datakeys[]'] = 'state';
		$params['conditions[]'] = $this->value->state;

		$value = $this->value->address;
		$icontype = 'compass';
		$icon = '<i class="fa fa-small mr-5 fa-' . $icontype . '"></i>';

		$searchUnit = $config->get('general.location.proximity.unit','mile');
		$searchText = $value;

		if ($display == 'distance') {

			// from input
			$lat = $options['lat'];
			$lon = $options['lon'];

			// from user data
			$uLat = $this->value->latitude;
			$uLon = $this->value->longitude;

			$dist = $this->distance($lat, $lon, $uLat, $uLon, $searchUnit);
			$value = round($dist, 1);


			$params['datakeys[]'] = 'distance';

			$cValue = ceil($value) . '|' . $uLat . '|' . $uLon . '|' . $this->value->address;
			$params['conditions[]'] = $cValue;

			$searchText = JText::sprintf('PLG_FIELDS_ADDRESS_DISTANCE_IN_' . strtoupper($searchUnit), $value );
		}

		if ($linkToAdvancedSearch) {

			$params['criterias[]'] = $this->unique_key . '|' . $this->element;
			$params['operators[]'] = 'equal';

			$advsearchLink = FRoute::search($params);

			$value = '<a href="'.$advsearchLink.'">' . $icon . $searchText . '</a>';
		}



		return $value;

	}

	private function distance($lat1, $lon1, $lat2, $lon2, $unit)
	{

		$lat1 = ($lat1 == '') ? '0.0' : $lat1;
		$lat2 = ($lat2 == '') ? '0.0' : $lat2;

		$delta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($delta));
		$dist = acos($dist);
		$dist = rad2deg($dist);

		$miles = $dist * 60 * 1.1515;

		if ($unit == 'km') {
			$miles = $miles * 1.609344;
		}

		return $miles;
	}
}
