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

jimport('joomla.application.component.model');

class EasySocialModelUsers extends EasySocialModel
{
	private $data = null;
	private $displayOptions = null;
	public static $loadedUsers = array();
	public $ordering = null;

	public $searchables = array('id', 'username', 'email', 'name');

	public function __construct($config = array())
	{
		$this->displayOptions = array();
		parent::__construct('users', $config);
	}

	/**
	 * Retrieves a list of countries user's are from
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getUniqueCountries()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select count(1) as total, x.`raw` as `country` from (";
		$query .= " select a.`raw` from `#__social_fields_data` as a";
		$query .= "	inner join `#__social_fields` as b on a.`field_id` = b.`id`";
		$query .= "	inner join `#__social_apps` as c on b.`app_id` = c.`id`";
		$query .= "	inner join `#__social_fields_steps` as d on b.`step_id` = d.`id`";
		$query .= " where c.`element` = 'address'";
		$query .= " and c.`group` = 'user'";
		$query .= " and a.`datakey` = 'country'";
		$query .= " and a.`raw` is not null";
		$query .= " and a.`raw` != ''";
		$query .= ") as x";
		$query .= " group by x.`raw`";
		$query .= " order by count(1) desc";

		$sql->raw($query);

		$db->setQuery($sql);

		$countries 	= $db->loadObjectList();

		return $countries;
	}

	/**
	 * Populates the state
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		$profile = $this->getUserStateFromRequest('profile');
		$group = $this->getUserStateFromRequest('group');
		$published = $this->getUserStateFromRequest('published' , 'all');
		$filter = $this->getUserStateFromRequest('filter');

		$this->setState('filter', $filter);
		$this->setState('published', $published);
		$this->setState('group', $group);
		$this->setState('profile', $profile);

		parent::initStates();
	}

	/**
	 * return the list of custom fields used in user exports.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getExportFields($profileId, $fieldIds = array(), $options = array())
	{
		$fieldsToExlude = array('header',
								'avatar',
								'cover',
								'file',
								'joomla_email',
								'joomla_username',
								'joomla_twofactor',
								'joomla_password',
								'separator');

		$fieldsCore = array('userid' => 'User ID',
			'username' => 'Username',
			'email' => 'Email',
			'join' => 'Register Date',
			'visit' => 'Last Visit Date',
			'points' => 'User Points'
		);

		$includeCoreFields = ES::normalize($options, 'core', false);

		$profile = ES::table('Profile');
		$profile->load($profileId);

		$db = ES::db();

		// first we need to get the the fields for a profile type.
		$fields = [];

		if ($includeCoreFields) {
			foreach ($fieldsCore as $id => $title) {
				$item = new stdClass();
				$item->id = $id;
				$item->title = $title;

				$fields[] = $item;
			}
		}

		// $query = "select a.`id`, a.`app_id`, a.`unique_key`, a.`step_id`, a.`title`, c.`element`";
		$query = "select a.*, c.`element`";
		$query .= " from `#__social_fields` as a";
		$query .= "	inner join `#__social_fields_steps` as b on a.`step_id` = b.`id`";
		$query .= "	inner join `#__social_apps` as c on a.`app_id` = c.`id`";

		if ($fieldIds) {
			// this means we only want to get certain fields based on the provided ids.
			$tmpIds = implode(',', $fieldIds);
			$query .= " WHERE a.`id` IN (" . $tmpIds . ")";
		}

		if (!$fieldIds && $fieldsToExlude) {

			$query .= " where b.`type` = 'profiles'";
			$query .= " and b.`workflow_id` = " . $db->Quote($profile->getWorkflow()->id);
			$query .= " and c.`type` = 'fields'";
			$query .= " and c.`group` = 'user'";

			$tmp = implode('\',\'', $fieldsToExlude);
			$query .= " and c.`element` not in ('" . $tmp . "')";
		}

		$db->setQuery($query);

		$results = $db->loadObjectList();

		if ($results) {
			foreach ($results as $item) {

				$item->title = JText::_($item->title);
				$fields[] = $item;
			}
		}

		return $fields;
	}

	/**
	 * Exports users from EasySocial with their custom fields data and display in table format
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function exportTable($profileId, $fieldIds, $options = array())
	{
		ES::language()->loadSite();
		ES::language()->loadAdmin();

		$db = ES::db();

		$limit = ES::normalize($options, 'limit', 20);
		$limitstart = ES::normalize($options, 'limitstart', 0);

		$coreFields = array();
		$ids = array();

		$header = array();
		$body = array();
		$fields = array();

		foreach ($fieldIds as $id) {

			if (!is_numeric($id)) {
				$coreFields[] = $id;
			} else {
				$ids[] = $id;
			}
		}


		// make sure user passed in non-core custom fields
		if ($ids) {
			$results = $this->getExportFields($profileId, $ids);

			if ($results) {
				foreach ($results as $item) {
					$field = ES::table('Field');
					$field->bind($item);

					$field->data = '';
					$field->profile_id = $profileId;
					$field->element = $item->element;

					$fields[$field->id] = $field;
				}
			}
		}


		// get user data
		$data = array();
		$query = "select a.`id` as `userid`, a.`username`, a.`name`, a.`email`, a.`registerDate`, a.`lastvisitDate`";
		if ($coreFields && in_array('points', $coreFields)) {
			$query .= ", (SELECT SUM(x.`points`) FROM `#__social_points_history` AS x WHERE x.`user_id` = a.`id`) AS `points`";
		} else {
			$query .= ", 0 AS `points`";
		}
		$query .= " from `#__users` as a";
		$query .= " inner join `#__social_profiles_maps` as c on a.`id` = c.`user_id`";
		$query .= " where c.`profile_id` = " . $db->Quote($profileId);
		$query .= " LIMIT $limitstart, $limit";


		$db->setQuery($query);
		$results = $db->loadObjectList('userid');

		if ($results) {

			$userIds = array_keys($results);

			foreach ($results as $row) {

				if (!isset($data[$row->userid])) {
					$data[$row->userid] = array();

					if ($this->hasCoreField($coreFields, 'userid')) {
						$data[$row->userid]['0']['userid'] = $row->userid;
					}

					if ($this->hasCoreField($coreFields, 'username')) {
						$data[$row->userid]['0']['username'] = $row->username;
					}


					if ($this->hasCoreField($coreFields, 'email')) {
						$data[$row->userid]['0']['email'] = $row->email;
					}

					if ($this->hasCoreField($coreFields, 'join')) {
						$data[$row->userid]['0']['join'] = $row->registerDate;
					}

					if ($this->hasCoreField($coreFields, 'visit')) {
						$data[$row->userid]['0']['visit'] = $row->lastvisitDate;
					}

					if ($this->hasCoreField($coreFields, 'points')) {
						$data[$row->userid]['0']['points'] = $row->points ? $row->points : 0;
					}

				}

				foreach ($fields as $id => $item) {
					$data[$row->userid][$id]['default'] = '';
				}
			}

			// get custom fields data
			if ($ids) {
				$query = "select a.`uid` as `userid`, a.`field_id`, a.`datakey`, a.`raw`";
				$query .= " from `#__social_fields_data` as a";
				$query .= " where a.`field_id` IN (" . implode(',', $ids) . ")";
				$query .= " and a.`uid` IN (" . implode(',', $userIds) . ")";
				$query .= " and a.`type` = " . $db->Quote(SOCIAL_TYPE_USER);

				$db->setQuery($query);
				$results = $db->loadObjectList();

				if ($results) {
					foreach ($results as $row) {
						$datakey = $row->datakey ? $row->datakey : 'default';
						$data[$row->userid][$row->field_id][$datakey] = $row->raw;
					}
				}
			}


			// lets format the data by triggering the onExport event.
			$fieldLib = ES::fields();

			foreach ($data as $userid => $fieldData) {
				$formatted = array();
				$args = array($fieldData, $userid);

				$formatted = $fieldLib->trigger('onExport', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

				foreach($fields as $fid => $value) {
					$data[$userid][$fid] = $formatted[$fid];
				}
			}

			// real work start here.

			foreach ($data as $userid => $fieldData) {

				if (!$header) {

					// prepare the header
					if ($this->hasCoreField($coreFields, 'userid')) {
						$header[] = JText::_('COM_ES_EXPORT_FIELD_USERID');
					}

					if ($this->hasCoreField($coreFields, 'username')) {
						$header[] = JText::_('COM_ES_EXPORT_FIELD_USERNAME');
					}


					if ($this->hasCoreField($coreFields, 'email')) {
						$header[] = JText::_('COM_ES_EXPORT_FIELD_EMAIL');
					}

					if ($this->hasCoreField($coreFields, 'join')) {
						$header[] = JText::_('COM_ES_EXPORT_FIELD_JOINDATE');
					}

					if ($this->hasCoreField($coreFields, 'visit')) {
						$header[] = JText::_('COM_ES_EXPORT_FIELD_VISITDATE');
					}

					if ($this->hasCoreField($coreFields, 'points')) {
						$header[] = JText::_('COM_ES_EXPORT_FIELD_POINTS');
					}


					foreach ($fields as $fid => $field) {

						$headerdata = $fieldData[$fid];

						$keys = array_keys($headerdata);

						if(count($keys) == 1 && $keys[0] == 'default') {
							$title = JText::_($field->title);
							$header[] = $title;
						} else {
							foreach($keys as $key) {
								$title = JText::_($field->title) . '::' . $key;
								$header[] = $title;
							}
						}
					}
				}

				// populat the body for core fields

				if ($this->hasCoreField($coreFields, 'userid')) {
					$body[$userid][] = $fieldData['0']['userid'];
				}

				if ($this->hasCoreField($coreFields, 'username')) {
					$body[$userid][] = $fieldData['0']['username'];
				}

				if ($this->hasCoreField($coreFields, 'email')) {
					$body[$userid][] = $fieldData['0']['email'];
				}

				if ($this->hasCoreField($coreFields, 'join')) {
					$body[$userid][] = $fieldData['0']['join'];
				}

				if ($this->hasCoreField($coreFields, 'visit')) {
					$body[$userid][] = $fieldData['0']['visit'];
				}

				if ($this->hasCoreField($coreFields, 'points')) {
					$body[$userid][] = $fieldData['0']['points'];
				}

				if ($fields) {
					// populat the body for non-core fields

					foreach ($fields as $fid => $field) {

						$itemdata = $fieldData[$fid];

						foreach($itemdata as $key => $value) {
							$body[$userid][] = $value;
						}
					}
				}
			}

			array_unshift($body, $header);
		}

		// get total user count used for Joomla pagination.
		$query = "select count(1)";
		$query .= " from `#__users` as a";
		$query .= " inner join `#__social_profiles_maps` as c on a.`id` = c.`user_id`";
		$query .= " where c.`profile_id` = " . $db->Quote($profileId);

		$db->setQuery($query);
		$totalUsers = $db->loadResult();

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->setTotalCount($totalUsers);

		return $body;
	}

	/**
	 * Method to determine if the key exists in fields array or not
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function hasCoreField($fields, $key)
	{
		return ($fields && in_array($key, $fields)) ? true : false;
	}

	/**
	 * Exports users from EasySocial with their custom fields data
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function export($profileId)
	{
		ES::language()->loadSite();
		ES::language()->loadAdmin();

		$profile = ES::table('Profile');
		$profile->load($profileId);

		$db = ES::db();

		$header = array();
		$body = array();

		// first we need to get the the fields for a profile type.
		$fields = array();

		$results = $this->getExportFields($profileId);

		if ($results) {
			foreach ($results as $item) {
				$field = ES::table('Field');
				$field->bind($item);

				$field->data = '';
				$field->profile_id = $profileId;
				$field->element = $item->element;

				$fields[$field->id] = $field;
			}
		}

		// get user data
		$data = array();
		$query = "select a.`id` as `userid`, a.`username`, a.`name`, a.`email`, a.`registerDate`, a.`lastvisitDate`, b.`field_id`, b.`datakey`, b.`raw`";
		$query .= ", (SELECT SUM(x.`points`) FROM `#__social_points_history` AS x WHERE x.`user_id` = a.`id`) AS `points`";
		$query .= " from `#__users` as a";
		$query .= " inner join `#__social_fields_data` as b on a.`id` = b.`uid` and b.`type` = " . $db->Quote(SOCIAL_TYPE_USER);
		$query .= " inner join `#__social_profiles_maps` as c on a.`id` = c.`user_id`";
		$query .= " where c.`profile_id` = " . $db->Quote($profileId);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if ($results) {

			foreach ($results as $row) {

				if (!isset($data[$row->userid])) {
					$data[$row->userid] = array();

					$data[$row->userid]['0']['userid'] = $row->userid;
					$data[$row->userid]['0']['username'] = $row->username;
					$data[$row->userid]['0']['email'] = $row->email;
					$data[$row->userid]['0']['join'] = $row->registerDate;
					$data[$row->userid]['0']['visit'] = $row->lastvisitDate;
					$data[$row->userid]['0']['points'] = $row->points ? $row->points : 0;
				}

				if (array_key_exists($row->field_id, $fields)) {
					$datakey = $row->datakey ? $row->datakey : 'default';
					$data[$row->userid][$row->field_id][$datakey] = $row->raw;
				}
			}

			// lets format the data by triggering the onExport event.
			$fieldLib = ES::fields();

			foreach ($data as $userid => $fieldData) {
				$formatted = array();
				$args 	= array($fieldData, $userid);

				$formatted = $fieldLib->trigger('onExport', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

				foreach($fields as $fid => $value) {
					$data[$userid][$fid] = $formatted[$fid];
				}
			}

			// real work start here.
			foreach ($data as $userid => $fieldData) {

				if (!$header) {

					$header = array('id','username','email', 'join', 'visit', 'points');

					foreach ($fields as $fid => $field) {

						$headerdata = $fieldData[$fid];

						$keys = array_keys($headerdata);

						if(count($keys) == 1 && $keys[0] == 'default') {
							$title = JText::_($field->title);
							$header[] = $title;
						} else {
							foreach($keys as $key) {
								$title = JText::_($field->title) . '::' . $key;
								$header[] = $title;
							}
						}
					}
				}

				foreach ($fields as $fid => $field) {

					if (!isset($body[$userid])) {
						$body[$userid][] = $fieldData['0']['userid'];
						$body[$userid][] = $fieldData['0']['username'];
						$body[$userid][] = $fieldData['0']['email'];
						$body[$userid][] = $fieldData['0']['join'];
						$body[$userid][] = $fieldData['0']['visit'];
						$body[$userid][] = $fieldData['0']['points'];
					}

					$itemdata = $fieldData[$fid];

					foreach($itemdata as $key => $value) {
						$body[$userid][] = $value;
					}
				}
			}

			// now we need to check if there is any users that do not have any data in fields_data.
			// if yes, we need to process these users too.

			// lets borrow the data keys from the last data elements
			$lastData = array_pop($data);

			$query = "select a.`id` as `userid`, a.`username`, a.`name`, a.`email`, a.`registerDate`, a.`lastvisitDate`";
			$query .= " from `#__users` as a";
			$query .= " inner join `#__social_profiles_maps` as c on a.`id` = c.`user_id`";
			$query .= " where c.`profile_id` = " . $db->Quote($profileId);
			$query .= " and not exists (select b.`uid` from `#__social_fields_data` as b where b.`type` = 'user' and b.`uid` = a.`id`)";

			$db->setQuery($query);
			$results = $db->loadObjectList();

			if ($results) {

				foreach ($results as $user) {

					$userid = $user->userid;

					foreach($fields as $fid => $field) {

						if (!isset($body[$userid])) {
							$body[$userid][] = $user->userid;
							$body[$userid][] = $user->username;
							$body[$userid][] = $user->email;
							$body[$userid][] = $user->registerDate;
							$body[$userid][] = $user->lastvisitDate;
						}

						$data = $lastData[$fid];

						foreach($data as $key => $value) {
							$body[$userid][] = "";
						}
					}

				}
			}

			// lets add the header into the body
			array_unshift($body, $header);
		}

		return $body;
	}

	/**
	 * Method to import user based on given data and fields
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function import($data, $fieldIds, $profileId, $importOptions = array())
	{
		if (is_string($fieldIds)) {
			$fieldIds = json_decode($fieldIds);
		}

		if (is_string($importOptions)) {
			$importOptions = json_decode($importOptions);
		}

		if (!is_array($importOptions)) {
			$importOptions = ES::makeArray($importOptions);
		}

		// Initialize user object
		$user = new SocialUser();

		$formattedData = array();
		$coreInputMapping = array(
			'joomla_email' => 'email',
			'joomla_fullname' => 'first_name',
			'joomla_password' => 'password',
			'joomla_username' => 'username'
		);

		$password = false;

		// Construct the data
		foreach ($fieldIds as $key => $fieldId) {

			// There might an instance where certain column is missing in the csv
			if (!isset($data[$key])) {
				continue;
			}

			// If this is not an integer, we know this is joomla column
			if ($fieldId && !(int) $fieldId) {
				$formattedData[$fieldId] = $data[$key];
				continue;
			}

			$field = ES::table('Field');
			$field->load($fieldId);

			if (!$field->id) {
				continue;
			}

			$fieldApp = $field->getApp();

			// Determine if this is mandatory field.
			if ($field->core) {
				$app = ES::table('App');
				$app->load($field->app_id);

				$inputName = isset($coreInputMapping[$app->element]) ? $coreInputMapping[$app->element] : SOCIAL_FIELDS_PREFIX . $field->id;

				if ($app->element == 'joomla_password') {
					$inputName = SOCIAL_FIELDS_PREFIX . $field->id . '-input';
					$password = $data[$key];
				}

				// to support avatar as non-core field in usr import.
				if ($app->element == 'avatar') {
					$inputName = 'avatar';
				}

				if ($app->element == 'joomla_fullname') {
					$types = ['first_name', 'middle_name', 'last_name'];
					$tmpNames = explode(',', $data[$key]);

					foreach ($types as $idx => $type) {
						$formattedData[$type] = isset($tmpNames[$idx]) ? $tmpNames[$idx] : '';
					}

					continue;
				}

				$formattedData[$inputName] = trim($data[$key]);
			} else {

				$fKey = SOCIAL_FIELDS_PREFIX . $field->id;

				$formattedData[$fKey] = trim($data[$key]);

				if ($fieldApp->element == 'multidropdown') {
					$tmp = explode(' ', $data[$key]);
					$formattedData[$fKey] = json_encode($tmp);
				}

				if ($fieldApp->element == 'birthday' || $fieldApp->element == 'datatime') {
					$tmp = new stdclass();
					$tmp->date = trim($data[$key]);

					$formattedData[$fKey] = json_encode($tmp);
				}

				if ($fieldApp->element == 'address') {
					$value = $data[$key];

					if ($value && !is_array($value) && !is_object($value)) {

						$tmpAddressData = array('address' => $value);

						$lib = ES::get('GeoCode');
						$geoData = $lib->geocode($value);

						if ($geoData) {

							foreach ($geoData->address_components as $ac) {

								// var_dump($ac);

								if ($ac->types[0] == 'route') {
									$tmpAddressData['address1'] = $ac->long_name;
								}

								if (count($ac->types) >= 2 && $ac->types[0] == 'political' && $ac->types[1] == 'sublocality') {
									// address2
									$tmpAddressData['address2'] = $ac->long_name;
								}

								if ($ac->types[0] == 'administrative_area_level_1') {
									// state
									$tmpAddressData['state'] = $ac->long_name;
								}

								if ($ac->types[0] == 'country') {
									// county
									$tmpAddressData['country'] = $ac->long_name;
								}

								if ($ac->types[0] == 'postal_code') {
									// zip
									$tmpAddressData['zip'] = $ac->long_name;
								}

								if ($ac->types[0] == 'postal_town') {
									$tmpAddressData['city'] = $ac->long_name;
								}
							}

							$geometry = $geoData->geometry;

							$tmpAddressData['latitude'] = $geometry->location->lat;
							$tmpAddressData['longitude'] = $geometry->location->lng;
						}

						$formattedData[$fKey] = $tmpAddressData;
					}
				}
			}
		}

		$profile = ES::table('Profile');
		$profile->load($profileId);

		$options = array();
		$options['workflow_id'] = $profile->getWorkflow()->id;
		$options['group'] = SOCIAL_FIELDS_GROUP_USER;

		// Get fields model
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields($options);

		// Get the fields lib
		$fieldsLib = ES::fields();

		// Get the general field trigger handler
		$handler = $fieldsLib->getHandler();

		$args = array(&$formattedData, 'conditionalRequired' => false, &$user);

		$errors = $fieldsLib->trigger('onAdminEditBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		if (is_array($errors) && count($errors) > 0) {
			$this->setError($errors);
			return false;
		}

		// var_dump('after', $formattedData);
		// exit;

		$user->bind($formattedData);

		$user = $this->create($formattedData, $user, $profile, $importOptions['autoapprove']);

		$args = array(&$formattedData, &$user);
		$errors = $fieldsLib->trigger('onAdminAfterImport', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Store the import history
		$userImport = ES::table('Userimporthistory');
		$userImport->user_id = $user ? $user->id : 0;
		$userImport->data = json_encode($data);
		$userImport->params = json_encode($importOptions);
		$userImport->created = ES::date()->toSql();
		$userImport->state = $user ? 1 : 0;

		$userImport->store();

		if (!$user) {
			return false;
		}

		// Determine if the password is joomla hash
		if ($importOptions['passwordtype'] != 'plain') {

			// We need to use joomla user to directly modify the password props
			$joomlaUser = JFactory::getUser($user->id);
			$joomlaUser->set('password', trim($password));

			$joomlaUser->save();

			// Reset the user
			$user = ES::user($joomlaUser->id);
		}

		// Reconstruct args
		$args = array(&$formattedData, &$user);
		$fieldsLib->trigger('onAdminEditAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Bind the custom fields for the user.
		$user->bindCustomFields($formattedData);

		// Reconstruct args
		$args = array(&$formattedData, &$user);
		$fieldsLib->trigger('onAdminEditAfterSaveFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);

		$args = array(&$user, &$fields, &$data);

		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserProfileUpdate', $args);

		return true;
	}

	/**
	 * Retrieves the last login
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLastLogin($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// getting joomla session lifetime config.
		$jConfig = JFactory::getConfig();
		$sessionLimit = $jConfig->get('lifetime', '0');
		$curDateTime = ES::date()->toMySQL();

		$query = 'select `time`, UNIX_TIMESTAMP(date_add(' . $db->Quote($curDateTime) . ' , INTERVAL -' . $sessionLimit . ' MINUTE)) as `limit`';
		$query .= ', count(1) as `count`';
		$query .= ' from `#__session` where `userid` = ' . $db->Quote($userId);
		$query .= ' group by `userid`, `time`';
		$query .= ' order by `time` desc limit 1';

		// echo $query;exit;

		$sql->raw($query);

		$db->setQuery($sql);
		$lastLogin = $db->loadObject();

		return $lastLogin;
	}

	/**
	 * Preload users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preloadUsers($ids)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select * from `#__social_users`";
		$query .= " where `user_id` IN (" . implode(",", $ids) . ")";

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Preload online users
	 *
	 * @since	2.1.10
	 * @access	public
	 */
	public function preloadIsOnline($ids)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the session life time so we can know who is really online.
		$jConfig = ES::jConfig();
		$lifespan = $jConfig->getValue('lifetime');
		$sharedSess = $jConfig->get('shared_session', 0);
		$sessionHandler = $jConfig->get('session_handler', '');

		// we need to get the sql tiem with the locale as the time stored into session table include the locale as well.
		$now = ES::date()->toSql(true);

		$online = time() - ($lifespan * 60);

		$query = array();
		$query[] = 'SELECT ' . $db->qn('session_id') . ', ' . $db->qn('userid') . ' FROM ' . $db->nameQuote('#__session');
		$query[] = 'WHERE ' . $db->nameQuote('userid') . ' IN(' . implode(",", $ids) . ')';

		if ($sessionHandler == 'database') {
			// $query[] = 'AND ' . $db->nameQuote('time') . ' >= UNIX_TIMESTAMP(DATE_SUB(' . $db->Quote($now) . ', INTERVAL ' . $lifespan . ' MINUTE))' ;
			$query[] = 'AND ' . $db->nameQuote('time') . ' >= ' . $db->Quote($online);
		} else {
			$query[] = 'AND ' . $db->nameQuote('guest') . ' = 0';
		}

		if (!$sharedSess) {
			$query[] ='AND ' . $db->nameQuote('client_id') . ' <> ' . $db->Quote(1);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		$onlineUsers = array();
		if ($results) {
			foreach($results as $item) {
				$onlineUsers[$item->userid] = 1;
			}
		}

		return $onlineUsers;
	}

	/**
	 * Determines if the user exists in #__social_users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function metaExists($id)
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		if (ES::cache()->exists('user.meta.'.$id)) {
			$value = ES::cache()->get('user.meta.'.$id);
			$exists = ($value) ? true : false;

		} else {
			$sql->select('#__social_users');
			$sql->column('COUNT(1)' , 'count');
			$sql->where('user_id' , $id);

			$db->setQuery($sql);

			$exists	= $db->loadResult() > 0 ? true : false;
		}

		return $exists;
	}

	/**
	 * Creates a new user meta
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createMeta($id)
	{
		$db = ES::db();

		$query = "select count(1) from `#__social_users` where user_id = " . $db->Quote($id);
		$db->setQuery($query);

		$count = $db->loadResult();

		if (! $count) {
			$obj = new stdClass();
			$obj->user_id = $id;
			$obj->alias = '';
			$obj->params = '';
			$obj->permalink = '';
			$obj->auth = '';
			$obj->affiliation_id = '';
			$obj->connections = 0;
			$obj->social_params = '';

			// If user is created on the site but doesn't have a record, we should treat it as published.
			$obj->state = SOCIAL_STATE_PUBLISHED;

			return $db->insertObject('#__social_users' , $obj);
		}

		return true;
	}

	/**
	 * Search a username given the email
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUsernameByEmail($email)
	{
		$db	= ES::db();
		$sql = $db->sql();

		$sql->select('#__users' , 'username');
		$sql->column('username');
		$sql->where('email' , $email);

		$db->setQuery($sql);

		$username = $db->loadResult();

		return $username;
	}

	/**
	 * Assigns user to a particular user group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assignToGroup($id , $gid)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Check if the user is already assigned to this group
		$sql->select('#__user_usergroup_map');
		$sql->column('COUNT(1)');
		$sql->where('group_id' , $gid);
		$sql->where('user_id'	, $id);

		$db->setQuery($sql);

		$exists = $db->loadResult();

		if (!$exists) {
			$sql->clear();
			$sql->insert('#__user_usergroup_map');
			$sql->values('user_id' , $id);
			$sql->values('group_id' , $gid);

			$db->setQuery($sql);
			$db->Query();
		}

	}

	/**
	 * Retrieve a user group from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserGroup($id)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__usergroups');
		$sql->where('id' , $id);

		$db->setQuery($sql);

		$result = $db->loadObject();

		if (!$result) {
			return $result;
		}

		$sql->clear();

		$sql->select('#__user_usergroup_map');
		$sql->where('group_id' , $id);

		$db->setQuery($sql->getTotalSql());

		$result->total 	= $db->loadResult();

		return $result;
	}

	/**
	 * Retrieve a list of user groups from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserGroups($options = array())
	{
		$db	= ES::db();

		$showCount = (isset($options['showCount'])) ? $options['showCount'] : true;

		$sql = $db->sql();

		$sql->select('#__usergroups', 'a');
		$sql->column('a.*');
		$sql->column('b.id', 'level', 'count distinct');
		$sql->join('#__usergroups' , 'b');
		$sql->on('a.lft', 'b.lft', '>');
		$sql->on('a.rgt', 'b.rgt', '<');
		$sql->group('a.id' , 'a.title' , 'a.lft' , 'a.rgt' , 'a.parent_id');
		$sql->order('a.lft' , 'ASC');

		$db->setQuery($sql);

		$result 	= $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		foreach ($result as &$row) {

			$row->total = 0;

			if ($showCount) {

				$sql->clear();

				$sql->select('#__user_usergroup_map');
				$sql->where('group_id' , $row->id);

				$db->setQuery($sql->getTotalSql());

				$row->total = $db->loadResult();

			}
		}

		return $result;
	}

	/**
	 * Retrieves the "about" information of a user.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getAbout($user, $activeStep = 0)
	{
		// Load admin language files
		ES::language()->loadAdmin();

		// Get a list of steps
		$model = ES::model('Steps');
		$steps = $model->getSteps($user->getProfile()->getWorkflow()->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_DISPLAY);

		// Load up the fields library
		$fieldsLib = ES::fields();
		$fieldsModel = ES::model('Fields');

		// Initial step
		$index = 1;
		$hasActive = false;

		foreach ($steps as $step) {

			// Get a list of fields from the current tab
			$options = array('step_id' => $step->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => SOCIAL_PROFILES_VIEW_DISPLAY);
			$step->fields = $fieldsModel->getCustomFields($options);

			// Trigger each fields available on the step
			if (!empty($step->fields)) {
				$args = array($user);

				$fieldsLib->trigger('onDisplay', SOCIAL_FIELDS_GROUP_USER, $step->fields, $args);
			}

			// By default hide the step
			$step->hide = true;

			// As long as one of the field in the step has an output, then this step shouldn't be hidden
			// If step has been marked false, then no point marking it as false again
			// We don't break from the loop here because there is other checking going on
			foreach ($step->fields as $field) {

				// We do not want to consider "header" field as a valid output
				if ($field->element == 'header') {
					continue;
				}

				// Ensure that the field has an output
				if (!empty($field->output) && $step->hide === true) {
					$step->hide = false;
				}
			}

			// Default step url
			$step->url = FRoute::profile(array('id' => $user->getAlias(), 'layout' => 'about'), false);

			if ($index !== 1) {
				$step->url = FRoute::profile(array('id' => $user->getAlias(), 'layout' => 'about', 'step' => $index), false);
			}

			$step->title = $step->get('title');
			$step->active = !$step->hide && $index == 1 && !$activeStep;

			// If there is an activeStep set, we should respect that
			if ($activeStep && $activeStep == $step->sequence) {
				$step->active = true;
				$hasActive = true;
			}

			// If the step is not hidden and there isn't any active set previously
			// Also, it should be the first item on the list.
			if (!$activeStep && !$step->hide && !$hasActive && $index == 1) {
				$step->active = true;
				$hasActive = true;
			}

			// If this is not the first step, and there is no active step previously
			if ($index != 1 && !$hasActive && !$step->hide && $step->fields && !$activeStep) {
				$step->active = true;
				$hasActive = true;
			}

			// If this is active, and there is no fields, we should skip it
			if ($step->active && !$step->fields) {
				$step->active = false;
				$hasActive = false;
			}

			$step->index = $index;

			$index++;
		}

		return $steps;
	}

	/**
	 * Retrieves a list of apps for the user's dashboard.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDashboardApps($userId)
	{
		$model = ES::model('Apps');
		$options = array('uid' => $userId , 'key' => SOCIAL_TYPE_USER);
		$apps = $model->getApps($options);

		// If there's nothing to process, just exit block.
		if (!$apps) {
			return $apps;
		}

		// Format the result as we only want to
		// return the caller apps that should appear on dashboard.
		$result = array();

		foreach ($apps as $app) {
			if ($app->hasDashboard()) {
				$result[]	= $app;
			}
		}

		return $result;
	}

	/**
	 * Retrieves a list of data for a type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initUserData($id)
	{
		$fieldsModel = ES::model('Fields');
		$data = $fieldsModel->getFieldsData(array('uid' => $id, 'type' => SOCIAL_TYPE_USER));

		// We need to attach all positions for this field
		$fields	= array();

		if (!$data) {
			return false;
		}

		foreach ($data as &$row) {
			// Manually assign the uid and type
			$row->uid = $id;
			$row->type = SOCIAL_TYPE_USER;

			$fields[$row->unique_key]	= $row;
		}

		return $fields;
	}

	/**
	 * Retrieves a list of user data based on the given ids.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUsersMeta($ids = array())
	{
		$loaded = array();
		$new = array();

		static $selectBigLoaded = false;

		if (!empty($ids)) {

			foreach ($ids as $id) {

				if (is_numeric($id)) {

					if (isset(self::$loadedUsers[$id])) {
						$loaded[] = self::$loadedUsers[$id];
					} else {
						$new[] = $id;
					}
				}
			}
		}

		// Only fetch for new items that isn't stored on the cache
		if ($new) {

			foreach ($new as $id) {
				self::$loadedUsers[$id] = false;
			}

			$db = ES::db();
			$sql = $db->sql();

			if (!$selectBigLoaded) {
				// set the SQL_BIG_SELECTS here to avoid possible MAX_JOIN_SIZE error.
				$query = "SET SQL_BIG_SELECTS=1";
				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();

				$selectBigLoaded = true;
			}

			$sql->clear();
			$sql->select('#__users', 'a');
			$sql->column('a.*');
			$sql->column('b.small');
			$sql->column('b.medium');
			$sql->column('b.large');
			$sql->column('b.square');
			$sql->column('b.avatar_id');
			$sql->column('b.photo_id');
			$sql->column('b.storage' , 'avatarStorage');
			$sql->column('d.profile_id');
			$sql->column('e.state');
			$sql->column('e.type');
			$sql->column('e.alias');
			$sql->column('e.params', 'es_params');
			$sql->column('e.completed_fields');
			$sql->column('e.permalink');
			$sql->column('e.reminder_sent');
			$sql->column('e.require_reset');
			$sql->column('e.block_period');
			$sql->column('e.block_date');
			$sql->column('e.social_params');
			$sql->column('e.verified');
			$sql->column('e.affiliation_id');
			$sql->column('e.robots');
			$sql->column('e.auth');
			$sql->column('f.id' , 'cover_id');
			$sql->column('f.uid' , 'cover_uid');
			$sql->column('f.type' , 'cover_type');
			$sql->column('f.photo_id' , 'cover_photo_id');
			$sql->column('f.cover_id'	, 'cover_cover_id');
			$sql->column('f.x' , 'cover_x');
			$sql->column('f.y' , 'cover_y');
			$sql->column('f.modified' , 'cover_modified');
			$sql->column('g.points' , 'points' , 'sum');
			$sql->join('#__social_avatars' , 'b');
			$sql->on('b.uid' , 'a.id');
			$sql->on('b.type' , SOCIAL_TYPE_USER);
			$sql->join('#__social_profiles_maps' , 'd');
			$sql->on('d.user_id' , 'a.id');
			$sql->join('#__social_users' , 'e');
			$sql->on('e.user_id' , 'a.id');
			$sql->join('#__social_covers' , 'f');
			$sql->on('f.uid' , 'a.id');
			$sql->on('f.type' , SOCIAL_TYPE_USER);

			$sql->join('#__social_points_history' , 'g');
			$sql->on('g.user_id' , 'a.id');

			if (count($new) > 1) {
				$sql->where('a.id' , $new , 'IN');
			} else {
				$sql->where('a.id' , $new[0]);
			}

			if (!empty($this->ordering)) {
				$sql->order($this->ordering['ordering'], $this->ordering['direction']);
			}

			// to compatible with aggregation function the 'ONLY_FULL_GROUP_BY' standard.
			$sql->group('a.id');
			$db->setQuery($sql);

			$users = $db->loadObjectList();

			if ($users) {

				// cache user metas
				ES::cache()->cacheUsersMeta($users);

				foreach ($users as $user) {
					$loaded[] = $user;
					self::$loadedUsers[$user->id] = $user;
				}
			}
		}

		$return = array();

		if ($loaded) {

			foreach ($loaded as $user) {
				if (isset($user->id)) {
					$return[] = $user;
				}
			}
		}

		return $return;
	}

	/**
	 * Retrieves a list of super administrator's on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getSiteAdmins($idOnly = false)
	{
		// Instead of using 'static $cache = null;' because trying to use values of type null, bool, int, float or resource as an array will always generate a notice in PHP 7.4. #3727
		// source: https://www.php.net/manual/de/migration74.incompatible.php#migration74.incompatible.core.non-array-access
		static $cache = array();

		$idx = (int) $idOnly;

		if (!isset($cache[$idx]) || empty($cache[$idx])) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__usergroups', 'a')
				->column('a.id')
				->column('a.title')
				->leftjoin('#__usergroups', 'b')
				->on('a.lft', 'b.lft', '>')
				->on('a.rgt', 'b.rgt', '<')
				->group('a.id')
				->order('a.lft', 'asc');

			$db->setQuery($sql);

			$result = $db->loadObjectList();

			// Get list of super admin groups.
			$superAdminGroups = array();

			foreach ($result as $group) {
				if (JAccess::checkGroup($group->id, 'core.admin')) {
					$superAdminGroups[]	= $group;
				}
			}

			$superAdmins = array();

			foreach ($superAdminGroups as $superAdminGroup) {
				$users = JAccess::getUsersByGroup($superAdminGroup->id);

				foreach ($users as $id) {
					$user = JFactory::getUser($id);

					if (!$user->block) {
						$superAdmins[] = $id;
					}
				}
			}

			// Experimenting features #2053
			$moderators = array();

			$query = "select u.`id`, u.`block`";
			$query .= " from `#__users` as u";
			$query .= " inner join `#__social_profiles_maps` as b on u.`id` = b.`user_id`";
			$query .= " inner join `#__social_profiles` as a on b.`profile_id` = a.`id`";
			$query .= " where a.`moderator_access` = 1";

			// somehow if we filter with block and sendEmail column,
			// the query become slow. let use php to filter. #3359
			// $query .= " and u.`block` = 0";

			$db->setQuery($query);
			$results = $db->loadObjectList();

			if ($results) {
				foreach ($results as $item) {
					if (!$item->block) {
						$moderators[] = $item->id;
					}
				}
			}

			$users = array_merge($superAdmins, $moderators);
			$users = array_unique($users);

			if ($idOnly) {
				$cache[$idx] = $users;
				return $cache[$idx];
			}

			// preload users
			ES::user($users);

			$admins = array();
			foreach ($users as $id) {
				$user = ES::user($id);
				$admins[] = $user;
			}

			$cache[$idx] = $admins;
		}

		return $cache[$idx];
	}

	/**
	 * Retrieve lists of site admins that can receive system notifications
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getSystemEmailReceiver()
	{
		static $receiver = null;

		if (is_null($receiver)) {

			// Refactor the way we retrieve admins / moderator
			// for system email sending without
			// preload users with ESUser object.
			// # 3359

			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__usergroups', 'a')
				->column('a.id')
				->column('a.title')
				->leftjoin('#__usergroups', 'b')
				->on('a.lft', 'b.lft', '>')
				->on('a.rgt', 'b.rgt', '<')
				->group('a.id')
				->order('a.lft', 'asc');

			$db->setQuery($sql);

			$result = $db->loadObjectList();

			// Get list of super admin groups.
			$superAdminGroups = array();

			foreach ($result as $group) {
				if (JAccess::checkGroup($group->id, 'core.admin')) {
					$superAdminGroups[]	= $group;
				}
			}

			$admins = array();

			foreach ($superAdminGroups as $superAdminGroup) {
				$users = JAccess::getUsersByGroup($superAdminGroup->id);

				foreach ($users as $id) {
					$user = JFactory::getUser($id);

					if (!$user->block && $user->sendEmail) {

						$obj = new stdClass();
						$obj->id = $id;
						$obj->name = $user->name;
						$obj->email = $user->email;

						$admins[$id] = $obj;
					}
				}
			}

			// manually get the user's name
			$query = "select u.`id`, u.`name`, u.`email`, u.`block`, u.`sendEmail`";
			$query .= " from `#__users` as u";
			$query .= " inner join `#__social_profiles_maps` as b on u.`id` = b.`user_id`";
			$query .= " inner join `#__social_profiles` as a on b.`profile_id` = a.`id`";
			$query .= " where a.`moderator_access` = 1";


			// somehow if we filter with block and sendEmail column,
			// the query become slow. let use php to filter. #3359

			// $query .= " and u.`block` = 0";
			// $query .= " and u.`sendEmail` = 1";

			$db->setQuery($query);
			$moderators = $db->loadObjectList();

			if ($moderators) {
				foreach ($moderators as $mod) {
					if (!$mod->block && $mod->sendEmail) {
						$admins[$mod->id] = $mod;
					}
				}
			}

			$receiver = $admins;
		}

		return $receiver;
	}

	/**
	 * Approves a user's registration application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approve($id)
	{
		$user 	= ES::user($id);

		return $user->approve();
	}

	/**
	 * Retrieves a list of online users from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOnlineUsers()
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the session life time so we can know who is really online.
		$jConfig 	= ES::jConfig();
		$lifespan 	= $jConfig->getValue('lifetime');
		$online 	= time() - ($lifespan * 60);

		$sql->select('#__session' , 'a');
		$sql->column('b.id');
		$sql->join('#__users' , 'b' , 'INNER');
		$sql->on('a.userid' , 'b.id');

		// exclude esad users
		$sql->innerjoin('#__social_profiles_maps', 'upm');
		$sql->on('b.id', 'upm.user_id');

		$sql->innerjoin('#__social_profiles', 'up');
		$sql->on('upm.profile_id', 'up.id');
		$sql->on('up.community_access', '1');

		if (ES::isBlockEnabled()) {
			$sql->leftjoin('#__social_block_users' , 'bus');

			$sql->on('(');
			$sql->on( 'b.id' , 'bus.user_id' );
			$sql->on( 'bus.target_id', JFactory::getUser()->id);
			$sql->on(')');

			$sql->on('(', '', '', 'OR');
			$sql->on( 'b.id' , 'bus.target_id' );
			$sql->on( 'bus.user_id', JFactory::getUser()->id );
			$sql->on(')');

			$sql->isnull('bus.id');
		}

		$sql->where('a.time' , $online , '>=');
		$sql->where('b.block' , 0);
		$sql->group('a.userid');

		$db->setQuery($sql);

		$result = $db->loadColumn();

		if (!$result) {
			return array();
		}

		$users	= ES::user($result);

		return $users;
	}

	/**
	 * Method to retrieve the required joomla user column for user registration
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getJoomlaUserColumn($essentialOnly = false)
	{
		$db = ES::db();

		$query = 'SHOW COLUMNS FROM `#__users`';

		$db->setQuery($query);

		$columns = $db->loadColumn();

		if ($essentialOnly) {
			$allowed = array('registerDate', 'lastvisitDate', 'params');
			foreach ($columns as $key => $column) {
				if (!in_array($column, $allowed)) {
					unset($columns[$key]);
				}
			}
		}

		return $columns;
	}

	/**
	 * Retrieves the total number of users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalUsers()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users');

		$db->setQuery($sql->getTotalSql());

		$total = $db->loadResult();

		return $total;
	}
	/**
	 * Retrieves all user ids on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAllUsers($excludeSelf = null)
	{
		$db = ES::db();
		$sql = $db->sql();
		$my = ES::user();

		$query = 'SELECT `user_id`, `alias` FROM ' . $db->nameQuote('#__social_users');

		if ($excludeSelf) {
			$query .= ' WHERE user_id != ' . $db->quote($my->id);
		}

		$db->setQuery($query);

		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Retrieves all user ids on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAllUserIds($excludeSelf = null, $limit = null, $limitstart = 0)
	{
		$db = ES::db();
		$sql = $db->sql();
		$my = ES::user();

		$query = 'SELECT `id` FROM ' . $db->nameQuote('#__users');
		$query .= ' WHERE `block` = 0';

		if ($excludeSelf) {
			$query .= ' AND `id` != ' . $db->quote($my->id);
		}

		if ($limit) {
			$query .= ' LIMIT ' . $limitstart . ',' . $limit;
		}

		$db->setQuery($query);
		$results = $db->loadColumn();

		return $results;
	}
	/**
	 * Retrieves all user ids on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAllUserNames($userId)
	{
		$db = ES::db();
		$sql = $db->sql();
		$my = ES::user();

		$query = 'SELECT `alias` FROM ' . $db->nameQuote('#__social_users');
		$query .= ' WHERE user_id = ' . $db->quote($userId);

		$db->setQuery($query);

		$results = $db->loadColumn();

		return $results;
	}

	/**
	 * Retrieves the total number of pending users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPending()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_users' , 'a');
		$sql->column('COUNT(1)' , 'count');
		$sql->join('#__users' , 'b');
		$sql->on('b.id' , 'a.user_id');
		$sql->where('a.state' , SOCIAL_REGISTER_APPROVALS);

		$db->setQuery($sql);

		$total 	= $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total number of pending users form the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPendingUsersCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users', 'a');
		$sql->column('COUNT(1)', 'count');
		$sql->join('#__social_users', 'b', 'INNER');
		$sql->on('a.id', 'b.user_id');
		$sql->where('b.state', SOCIAL_REGISTER_APPROVALS);
		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of pending users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPendingUsers($options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.* FROM ' . $db->nameQuote('#__users') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_users') . ' AS b';
		$query[] = 'ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('user_id');
		$query[] = 'WHERE b.' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_REGISTER_APPROVALS);
		$query[] = 'ORDER BY a.' . $db->nameQuote('registerDate');

		if (isset($options['limit'])) {
			$query[] = 'LIMIT ' . $options['limit'];
		}

		$query = implode(' ' , $query);
		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Prepare the user object.
		$users = array();

		foreach ($result as $row) {
			$user = ES::user($row->id);
			$users[] = $user;
		}

		return $users;
	}


	/**
	 * Retrieves the total online users on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalOnlineUsers()
	{
		$db = ES::db();
		$sql = $db->sql();

		$jConfig = ES::jconfig();
		$lifespan = $jConfig->getValue('lifetime');
		$online = time() - ($lifespan * 60);

		// Get total backend users
		$sql->select('#__session');
		$sql->column('COUNT(session_id)');
		$sql->where('guest', 0);
		$sql->where('client_id', 1);
		$sql->where('time', $online, '>=');

		$db->setQuery($sql);

		$totalBackend 	= $db->loadResult();

		// Get total online users on the front end
		$sql->clear();
		$sql->select('#__session');
		$sql->column('COUNT(session_id)');
		$sql->where('guest', 0);
		$sql->where('client_id', 0);
		$sql->where('time', $online, '>=');

		$db->setQuery($sql);

		$totalSite 	= $db->loadResult();

		$total 	= $totalSite + $totalBackend;

		return $total;
	}

	/**
	 * Retrieves a list of user data based on the given ids.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUsersWithState($options = array())
	{

		$idOnly = isset($options['idonly']) ? $options['idonly'] : false;


		$excludeClusterMembers = isset($options['excludeClusterMembers']) ? $options['excludeClusterMembers'] : 0;
		$clusterId = isset($options['clusterId']) ? $options['clusterId'] : 0;

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users' , 'a');

		if (!$idOnly) {
			$sql->column('a.*');
			$sql->column('b.type');
			$sql->column('p.points', 'points' , 'sum');
			$sql->column('b.block_period', 'period');
			$sql->column('b.block_date', 'block_date');

			// Join with points table.
			$sql->join('#__social_points_history', 'p');
			$sql->on('p.user_id', 'a.id');
			$sql->group('a.id');

		} else {
			$sql->column('a.id');
		}

		$sql->join('#__social_users' , 'b');
		$sql->on('a.id', 'b.user_id');

		// Determines if there's a group filter.
		$group = $this->getState('group');

		if ($group && $group != -1) {
			$sql->join('#__user_usergroup_map' , 'c');
			$sql->on('a.id' , 'c.user_id');

			$sql->where('c.group_id' , $group);
		}

		// Join with the social profiles table
		$sql->join('#__social_profiles_maps' , 'e');
		$sql->on('e.user_id' , 'a.id');

		// Determines if there's a search filter.
		$search = $this->getState('search');

		if ($search) {
			// If there is a : in the search query
			$column = $this->getSearchableItems($search);

			if ($column) {
				$sql->where('a.' . $column->column, '%' . $column->query . '%', 'LIKE');
			} else {
				$sql->where('(');
				$sql->where('name' , '%' . $search . '%' , 'LIKE' , 'OR');
				$sql->where('username' , '%' . $search . '%' , 'LIKE' , 'OR');
				$sql->where('email' , '%' . $search . '%' , 'LIKE' , 'OR');
				$sql->where(')');
			}
		}

		// Determines if registration state
		$userState = $this->normalize($options, 'state', '');

		if ($userState !== '') {
			$sql->where('b.state', $userState);
		}

		// Determines if state filter is provided
		$state = $this->getState('published');

		if ($state != 'all' && !is_null($state)) {
			$state = $state == 1 ? SOCIAL_JOOMLA_USER_UNBLOCKED : SOCIAL_JOOMLA_USER_BLOCKED;

			$sql->where('a.block', $state);
		}

		// Determines if we want to filter by logged in users.
		$login 	= isset($options['login']) ? $options['login'] : '';

		if ($login) {
			$tmp = 'EXISTS(SELECT ' . $db->nameQuote('userid') . ' FROM ' . $db->nameQuote('#__session') . ' AS f WHERE ' . $db->nameQuote('userid') . ' = a.' . $db->nameQuote('id') . ')';

			$sql->exists($tmp);
		}

		$picture 	= isset($options['picture']) ? $options['picture'] : '';

		// Determines if we should only pick users with picture
		if ($picture) {
			$sql->join('#__social_avatars' , 'g');
			$sql->on('a.id' , 'g.uid');

			$sql->where('g.small' , '' , '!=');
		}


		// Determines if there's filter by profile id.
		$profile 		= $this->getState('profile');

		if ($profile && $profile != -1 && $profile != -2) {
			$sql->where('e.profile_id' , $profile);
		} else if($profile == -2) {
			$sql->isnull('e.profile_id');
		}

		// Determines if we have an exclusion list.
		$exclusions = isset($options['exclusion']) ? $options['exclusion'] : '';

		if ($exclusions) {
			// Ensure that it's in an array
			$exclusions 	= ES::makeArray($exclusions);
			$sql->where('a.id' , implode(',' , $exclusions) , 'NOT IN');
		}

		// dump($excludeClusterMembers, $clusterId);

		// exclude members from clusters.
		if ($excludeClusterMembers && $clusterId) {

			$tmpSQL = 'NOT EXISTS (SELECT ' . $db->nameQuote('uid') . ' FROM ' . $db->nameQuote('#__social_clusters_nodes') .' WHERE ' . $db->nameQuote('uid') . ' = a.' . $db->nameQuote('id');
			$tmpSQL .= ' and ' . $db->nameQuote('cluster_id') . ' = ' . $db->Quote($clusterId) . ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote('user') . ')';

			$sql->exists($tmpSQL);

		}

		// echo $sql;exit;

		$counterSQL = $sql->getSql();

		// Determines if we need to order the items by column.
		$ordering 	= isset($options['ordering']) ? $options['ordering'] : '';

		// Ordering based on caller
		if ($ordering) {
			$direction 	= isset($options['direction']) ? $options['direction'] : '';

			$sql->order($ordering , $direction);
		}

		// Column ordering
		$ordering = $this->getState('ordering' , $ordering);

		if ($ordering) {
			$direction 	= $this->getState('direction');

			$sql->order($ordering , $direction);
		}

		$limit = $this->normalize($options, 'limit', '');
		$limitState = $this->getState('limit');

		if ($limit != 0 || $limitState) {

			if ($limit) {
				$this->setState('limit', $limit);
				// $sql->limit(0, $limit);
			} else if ($limitState) {
				$this->setState('limit', $limitState);
			}

			// Set the total number of items.
			$this->setTotal($counterSQL, true);

			// Get the list of users
			$users = $this->getData($sql, true);
		} else {

			$db->setQuery($sql);
			$users = $db->loadObjectList();
		}


		return $users;
	}

	/**
	 * Determines if the alias exists
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function aliasExists($alias , $exceptUserId)
	{
		$db = ES::db();

		$query = "SELECT COUNT(1) as `total`";
		$query .= " FROM `#__social_users`";
		$query .= " WHERE (";
		$query .= " `alias` = " . $db->Quote($alias);
		$query .= " OR `permalink` = " . $db->Quote($alias);
		$query .= ")";
		$query .= " AND `user_id` != " . $db->Quote($exceptUserId);

		$db->setQuery($query);

		$count = $db->loadResult();
		$exists	= $count >= 1 ? true : false;

		return $exists;
	}

	/**
	 * Retrieve's user id based on the alias
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserIdFromAlias($permalink)
	{
		static $loaded 	= array();

		if (!isset($loaded[$permalink])) {
			$config = ES::config();

			// Get the user form permalink field
			$id = $this->getUserFromPermalink($permalink);

			// If the user set's the permalink, we should respect that.
			if ($id) {
				$loaded[$permalink]	= $id;

				return $loaded[$permalink];
			}

			// Try to get the user id from the alias column
			$id = $this->getUserFromAlias($permalink);

			if ($id) {
				$loaded[$permalink] = $id;

				return $loaded[$permalink];
			}

			// We need to know which column should we be checking against.
			if ($config->get('users.aliasName') == 'realname') {

				if (strpos($permalink , ':') !== false) {
					$parts = explode(':', $permalink , 2);

					$id = $parts[0];
				}

				if (!$id) {
					// Replace : and - with spaces
					$tmp = str_ireplace(array(':', '-'), ' ', $permalink);
					$id = (int) $this->getUserIdWithNamePermalink($tmp);
				}

				$loaded[$permalink]	= $id;

				return $loaded[$permalink];
			}

			// If it reaches here, we know then that the alias is using username
			// First we need to replace : with -
			$tmp = str_replace(':', '-', $permalink);
			$id = $this->getUserIdWithUsernamePermalink($tmp);

			// If we still can't find '-' try '_' now.
			if (!$id) {
				$tmp = str_replace(':', '_' , $permalink);
				$id = $this->getUserIdWithUsernamePermalink($tmp);
			}

			// If we still can't find '_' , we replace it with spaces
			if (!$id) {
				$tmp = str_replace(':' , ' ' , $permalink);
				$id = $this->getUserIdWithUsernamePermalink($tmp);
			}

			// If we still can't find '-' try '_' now.
			if (!$id) {
				$tmp = str_replace('-', '_' , $permalink);
				$id = $this->getUserIdWithUsernamePermalink($tmp);
			}

			$loaded[$permalink] 	= $id;
		}

		return $loaded[$permalink];
	}

	/**
	 * Determines if the permalink is a valid permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isValidUserPermalink($permalink)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_users');
		$sql->column('COUNT(1)');
		$sql->where('permalink' , $permalink);

		$db->setQuery($sql);

		$exists	= $db->loadResult() > 0 ? true : false;

		return $exists;
	}

	/**
	 * Generates a unique alias given a string
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function generateAlias($username, $id)
	{
		// Filter the username so that it becomes a valid alias
		$alias = JFilterOutput::stringURLSafe($username);

		// check if the alias is empty or not. if yes, we will assign a dummy text along with user id.
		// #909
		if (! $alias) {
			// make sure the language file is loaded.
			ES::language()->loadSite();
			$alias = JText::sprintf('COM_EASYSOCIAL_DEFAULT_USER_ALIAS_PREFIX', $id);
		}

		// Keep the original state of the alias
		$tmp = $alias;

		while ($this->aliasExists($alias, $id)) {
			// Generate a new alias for the user.
			$alias = $tmp . '-' . rand(1, 150);
		}

		return $alias;
	}

	/**
	 * Retrieve user's id given the name permalink
	 *
	 * @since	2.0.7
	 * @access	public
	 */
	public function getUserIdWithNamePermalink($permalink)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users');
		$sql->column('id');
		$sql->where('name', $permalink);

		$db->setQuery($sql);

		$id = $db->loadResult();

		return $id;
	}

	/**
	 * Retrieve user's id given the username permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserIdWithUsernamePermalink($permalink)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users');
		$sql->column('id');
		$sql->where('username' , $permalink);

		$db->setQuery($sql);

		$id 	= $db->loadResult();

		return $id;
	}

	/**
	 * Retrieve a user with the given permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserFromAlias($alias)
	{
		$db 	= ES::db();

		$sql 	= $db->sql();

		$sql->select('#__social_users');
		$sql->column('user_id');
		$sql->where('alias' , $alias , '=');

		$db->setQuery($sql);

		$id 	= (int) $db->loadResult();

		return $id;
	}

	/**
	 * Retrieve a user with the given permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserFromPermalink($permalink)
	{
		$db 	= ES::db();

		$sql 	= $db->sql();

		$variant 	= str_ireplace(':', '-' , $permalink);
		$underscore = str_ireplace(':' , '_' , $permalink);
		$underscore2 = str_ireplace('-' , '_' , $permalink);

		// $sql->select('#__social_users');
		// $sql->column('user_id');
		// $sql->where('permalink' , $permalink , '=' , 'OR');
		// $sql->where('permalink' , $variant , '=' , 'OR');
		// $sql->where('permalink' , $underscore , '=' , 'OR');
		// $sql->where('LOWER(`permalink`)' , $permalink , '=' , 'OR');
		// $sql->where('LOWER(`permalink`)' , $variant , '=' , 'OR');
		// $sql->where('LOWER(`permalink`)' , $underscore , '=' , 'OR');

		// // There are instances where the _ is converted into -
		// $sql->where('LOWER(`permalink`)', str_ireplace('-', '_', $permalink), '=', 'OR');

		$query = "select `user_id`";
		$query .= " from `#__social_users`";
		$query .= " where `permalink` IN (";
		$query .= $db->Quote($permalink) . ',' . $db->Quote($variant) . ',' . $db->Quote($underscore) . ',' . $db->Quote($underscore2);
		$query .= ")";

		$db->setQuery($query);

		$id 	= (int) $db->loadResult();

		return $id;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDisplayOptions()
	{
		return $this->displayOptions;
	}


	/**
	 * Retrieves a list of user based on the advanced search criterias
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUsersByFilter($fid, $settings = array(), $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		if ($fid) {
			// we need to load the data from db and do the search based on the saved filter.
			$filter = ES::table('SearchFilter');
			$filter->load($fid);

			if (!$filter->id) {
				return array();
			}

			// data saved as json format. so we need to decode it.
			$dataFilter = ES::json()->decode($filter->filter);

			// override with the one from db.
			$options['criterias'] = $dataFilter->{'criterias[]'};
			$options['datakeys'] = $dataFilter->{'datakeys[]'};
			$options['operators'] = $dataFilter->{'operators[]'};
			$options['conditions'] = $dataFilter->{'conditions[]'};
		}

		// we need check if the item passed in is array or not. if not, make it an array.
		if (! is_array($options['criterias'])) {
			$options['criterias'] = array($options['criterias']);
		}

		if (! is_array($options['datakeys'])) {
			$options['datakeys'] = array($options['datakeys']);
		}

		if (! is_array($options['operators'])) {
			$options['operators'] = array($options['operators']);
		}

		if (! is_array($options['conditions'])) {
			$options['conditions'] = array($options['conditions']);
		}

		$options['sort'] = isset($options['sort']) && $options['sort'] ? $options['sort'] : 'default';

		$sort = isset($dataFilter->sort) ? $dataFilter->sort : $options['sort'];

		$customOrdering = array();

		if (isset($settings['ordering']) && isset($settings['direction'])) {
			$customOrdering['ordering'] = $settings['ordering'];
			$customOrdering['direction'] = $settings['direction'];
		}

		if (isset($settings['excludeUserListing']) && $settings['excludeUserListing']) {
			$options['excludeUserListing'] = $settings['excludeUserListing'];
		}

		$options['match'] = isset($dataFilter->matchType) ? $dataFilter->matchType : 'all';

		$avatarOnly = isset($options['avatarOnly']) ? $options['avatarOnly'] : false;
		$options['avatarOnly'] = isset($dataFilter->avatarOnly) ? true : $avatarOnly;

		$onlineOnly = isset($options['onlineOnly']) ? $options['onlineOnly'] : false;
		$options['onlineOnly'] = isset($dataFilter->onlineOnly) ? true : $onlineOnly;

		//setup displayOptions
		$library = ES::get('AdvancedSearch');
		$library->setDisplayOptions($options);

		$this->displayOptions = $library->getDisplayOptions();

		$sModel = ES::model('search');

		$query = $sModel->buildAdvSearch($options['match'], $options);

		if (! $query) {
			return array();
		}

		$cntQuery = $query;

		if (empty($customOrdering) && ($sort && $sort != 'default')) {

			// Set always order alphabetical to the name.
			if ($sort == 'alphabetical') {
				$query .= ' ORDER BY ' . $db->nameQuote('u.name') . ' ASC';
			} else {
				$query .= ' ORDER BY ' . $db->nameQuote('u.' . $sort) . ' DESC';
			}

		} else if ($customOrdering) {
			$query .= ' ORDER BY ' . $db->nameQuote('u.' . $customOrdering['ordering']) . ' ' . $customOrdering['direction'];
		}

		$sql->raw($query);

		$limit 	= isset($settings['limit']) ? $settings['limit'] : '';

		if ($limit != 0) {
			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);

			// Set the total number of items.
			$this->setTotal($cntQuery, true);

			// Get the list of users
			$users 	= $this->getData($sql->getSql());

		} else {
			$db->setQuery($sql);
			$users 	= $db->loadObjectList();
		}

		return $users;
	}


	/**
	 * Retrieves a list of user data based on the given ids.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUsers($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get current user logged in user
		$my = ES::user();

		$followersOnly = $this->normalize($options, 'followersOnly', false);
		$followingOnly = $this->normalize($options, 'followingOnly', false);
		$friendOnly = isset($options['friendOnly']) ? $options['friendOnly'] : false;
		$ignoreESAD = isset($options['ignoreESAD']) ? $options['ignoreESAD'] : false;
		$isOrderConnection 	= isset($options['ordering']) && $options['ordering'] == 'connectionDate' ? true : false;

		// Determines if we should exclude this profile type user on the user listing page.
		$excludeUserListing = isset($options['excludeUserListing']) ? $options['excludeUserListing'] : false;

		// this flag used to determine if the caller is from module. If yes,
		// we might not require all information such as user's points and etc
		$isModule = isset($options['isModule']) ? $options['isModule'] : false;

		$targettedUserId = isset($options['targettedUserId']) ? $options['targettedUserId'] : $my->id;

		// flag to determine if we need to join with points table or not.
		$joinPoints = ($isModule) ? false : true;

		$profileTypeWithCommunityAccess = array();
		$blockedUserIds = array();

		$query = "select a.`id`, b.`type`, 0 as `points`";

		$query .= " FROM `#__users` as a";
		$query .= " INNER JOIN `#__social_users` as b on a.`id` = b.`user_id`";

		if ($friendOnly) {
			$query .= " INNER JOIN `#__social_friends` as ff on a.`id` = if(ff.`target_id` = " . $db->Quote($targettedUserId). ", ff.`actor_id`, ff.`target_id`)";
		} else if ($isOrderConnection) {
			$query .= " LEFT JOIN `#__social_friends` as ff on a.`id` = if(ff.`target_id` = " . $db->Quote($targettedUserId). ", ff.`actor_id`, ff.`target_id`)";
		}

		if ($followersOnly || $followingOnly) {
			if ($followersOnly) {
				$query .= " INNER JOIN `#__social_subscriptions` AS fl ON fl.`user_id` = a.`id`";
				$query .= " AND (fl.`type` = " . $db->Quote(SOCIAL_TYPE_USER . '.' . SOCIAL_TYPE_USER);
				$query .= " AND fl.`uid` = " . $db->Quote($targettedUserId) . ")";
			} else {
				$query .= " INNER JOIN `#__social_subscriptions` AS fl ON fl.`uid` = a.`id`";
				$query .= " AND (fl.`type` = " . $db->Quote(SOCIAL_TYPE_USER . '.' . SOCIAL_TYPE_USER);
				$query .= " AND fl.`user_id` = " . $db->Quote($targettedUserId) . ")";
			}
		}

		// Determines if there's filter by profile id.
		$profile = $this->normalize($options, 'profile');
		$hasProfileTypeId = false;

		if (is_null($profile)) {
			$profile = $this->getState('profile');
		}

		if ($profile && $profile != -1) {
			// Join with the social profiles table
			$query .= " INNER JOIN `#__social_profiles_maps` as e on e.`user_id` = a.`id`";

			// set the flag to true so that the table join on esad is not required anymore.
			$hasProfileTypeId = true;
		}


		if ((!$ignoreESAD || $excludeUserListing) && !$my->isSiteAdmin() && !$hasProfileTypeId) {

			// get the profile type id for later use.
			$profileTypeWithCommunityAccess = $this->getCommunityProfileTypeIds($ignoreESAD, $excludeUserListing);

			$query .= " INNER JOIN `#__social_profiles_maps` as upm on a.`id` = upm.`user_id`";
		}

		$excludeBlocked = $this->normalize($options, 'excludeblocked', 0);
		$blockedUser = $this->normalize($options, 'blocked', 0);


		if (!$blockedUser && ES::isBlockEnabled($my) && $excludeBlocked) {

			// get the blocked users ids for later use
			$blockedUserIds = $this->getBlockedUserIds($targettedUserId);
		}

		// get only users that blocked by current logged in user.
		if ($blockedUser && (ES::isBlockEnabled($my, false))) {
			$query .= " INNER JOIN `#__social_block_users` as bus2";
			$query .= " ON a.`id` = bus2.`target_id` AND bus2.`user_id` = " . $db->Quote($targettedUserId);
		}

		// Determines if we only get users with avatar photos
		$picture = isset($options['picture']) ? $options['picture'] : '';

		// Determines if we should only pick users with picture
		if ($picture) {

			// There is an instance where the user id is the same as cluster id. So, that is why we need to specify the avatar type.
			$query .= " INNER JOIN `#__social_avatars` as g ON g.`uid` = a.`id` and g.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and g.`small` != ''";
		}

		// wheres
		$wheres = array();


		// Determines if registration state
		$registrationState = $this->normalize($options, 'state');

		if ($registrationState) {
			if ($registrationState == 'pending') {
				$wheres[] = "(b.state = " . $db->Quote(SOCIAL_REGISTER_VERIFY) . " OR b.state = " . $db->Quote(SOCIAL_REGISTER_APPROVALS) . " OR b.state = " . $db->Quote(SOCIAL_REGISTER_CONFIRMATION_APPROVAL) . ")";
			} else {
				$wheres[] = "b.state = " . $db->Quote($registrationState);
			}
		}

		// Determines if we should display admin's on this list.
		$includeAdmin = isset($options['includeAdmin']) ? $options['includeAdmin'] : null;

		$excluseUserIds = array();

		// If caller doesn't want to include admin, we need to set the ignore list.
		if ($includeAdmin === false) {
			// Get a list of site administrators from the site.
			$excluseUserIds = $this->getSiteAdmins(true);
		}

		if ($blockedUserIds) {
			$excluseUserIds = array_merge($excluseUserIds, $blockedUserIds);
		}

		// Determines if we only get verified users
		$verified = $this->normalize($options, 'verified', null);

		if (!is_null($verified)) {
			$wheres[] = 'b.`verified` = ' . $db->Quote(1);
		}

		// Determines if state filter is provided
		$state 	= isset($options['published']) ? $options['published'] : '';

		if ($state !== '') {
			$state	= $state == 1 ? SOCIAL_JOOMLA_USER_UNBLOCKED : SOCIAL_JOOMLA_USER_BLOCKED;
			$wheres[] = "a.`block` = " . $db->Quote($state);
		}

		if ($friendOnly) {
			$wheres[] = "(ff.`actor_id` = " . $db->Quote($targettedUserId) . " and ff.`state` = 1) OR (ff.`target_id` = " . $db->Quote($targettedUserId) . " and ff.`state` = 1)";
		}

		if ($profile && $profile != -1) {
			$profile = ES::makeArray($profile);
			$wheres[] = "e.`profile_id` IN (" . implode(',', $profile) . ")";
		}

		// Determines if we have an exclusion list.
		$exclusions = isset($options['exclusion']) ? $options['exclusion'] : '';

		if ($exclusions) {
			// Ensure that it's in an array
			$exclusions = ES::makeArray($exclusions);
			$excluseUserIds = array_merge($excluseUserIds, $exclusions);
		}

		// ignore these users
		if ($excluseUserIds) {
			$excluseUserIds = array_unique($excluseUserIds);
			$wheres[] = "a.id NOT IN (" . implode(',', $excluseUserIds) . ")";
		}

		// Determines if we have an inclusion list.
		$inclusion = isset($options['inclusion'])? $options['inclusion'] : '';

		if ($inclusion) {
			// Ensure that it's in an array
			$inclusion = ES::makeArray($inclusion);
			$wheres[] = "a.id IN (" . implode(',', $inclusion) . ")";
		}


		// filter users who associated with profile type that has the community access
		if ($profileTypeWithCommunityAccess) {
			$wheres[] = "upm.`profile_id` IN (" . implode(',', $profileTypeWithCommunityAccess) . ")";
		}

		// lets check for the avatar validity here.
		if ($picture) {

			$tmp = "exists (select `photo_id` from `#__social_photos_meta` as pm where pm.`photo_id` = g.`photo_id`";
			$tmp .= " and pm.`group` = " . $db->Quote('path');
			$tmp .= " and pm.`property` = " . $db->Quote('original');
			$tmp .= ")";

			$wheres[] = $tmp;
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = " WHERE ";
			$where .= (count($wheres) > 1) ? implode(' AND ', $wheres) : $wheres[0];
		}

		// glue the main query with where conditions.
		$query .= $where;

		// prepare the query without ordering and limits
		$counterSQL = $query;

		// Determines if we need to order the items by column.
		$ordering 	= isset($options['ordering']) ? $options['ordering'] : '';

		// Determines if we want to filter by logged in users.
		$login = isset($options['login']) ? $options['login'] : '';

		// Ordering based on caller
		if ($ordering) {

			// order by last connected date.
			if ($isOrderConnection) {
				$ordering = 'ff.modified';
			}

			$direction 	= isset($options['direction']) ? $options['direction'] : '';

			if (! $isOrderConnection) {
				// if we order by last connected date, thats mean we are doing it from module.
				// if that is the case, do not store the ordering. if we do, it will
				// cause sql error in getUserMeta as this function using the same ordering.
				$this->ordering = array('ordering' => $ordering, 'direction' => $direction);
			}

			$query .= " ORDER BY " . $ordering . " " . $direction;
		}

		if ($login) {
			$tmp = 'SELECT * FROM (' . $query . ') AS friendx ';
			// Determine if only to fetch front end
			$frontend = isset($options['frontend']) ? $options['frontend'] : '';
			$tmp .= 'WHERE friendx.`id` IN (';
			$tmp .= 'SELECT ' . $db->nameQuote('userid') . ' FROM ' . $db->nameQuote('#__session') . ' AS f WHERE f.' . $db->nameQuote('userid') . ' = friendx.' . $db->nameQuote('id');

			if ($frontend) {
				$tmp .= ' AND f.`client_id` = ' . $db->Quote(0);
			}

			$tmp .= ')';

			$query = $tmp;

			$counterSQL = $query;
		}


		// echo $query;
		// echo '<br><br>';

		$limit 	= isset($options['limit']) ? $options['limit'] : '';

		if ($isModule && $limit) {
			// since we know module do not require paginatin, lets embed the limit into the sql.
			$query .= " LIMIT " . $limit;

			$db->setQuery($query);
			$users 	= $db->loadObjectList();

		} else {

			if ($limit != 0) {

				$this->setState('limit' , $limit);

				// Get the limitstart.
				$limitstart = isset($options['limitstart']) ? $options['limitstart'] : $this->getUserStateFromRequest('limitstart' , 0);
				$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

				$this->setState('limitstart' , $limitstart);

				// echo $counterSQL;

				// Set the total number of items.
				$this->setTotal($counterSQL, true);

				// Get the list of users
				$users 	= $this->getData($query);

			} else {
				$db->setQuery($query);
				$users 	= $db->loadObjectList();
			}
		}

		// we will now split the query to retrive user's points to speed up the query
		// performance. #3769
		if ($users && $joinPoints) {
			$ids = array();

			foreach ($users as $item) {
				$ids[] = $item->id;
			}

			$query = "select sum(`points`) as `points`, `user_id`";
			$query .= " from `#__social_points_history`";
			$query .= " where `user_id` in (" . implode(',', $ids) . ")";
			$query .= " group by `user_id`";

			$db->setQuery($query);
			$points = $db->loadObjectList('user_id');

			if ($points) {
				foreach ($users as &$item) {
					$item->points = isset($points[$item->id]) ? $points[$item->id]->points : 0;
				}
			}
		}

		return $users;
	}

	/**
	 * Get list of upcoming birhtdays
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUpcomingBirthdays($key, $userId, $limit = null, $page = null, $limitstart = null, $options = [])
	{
		$db = ES::db();
		$config = ES::config();
		$my = ES::user();

		$now = ES::date()->toSql();
		$showFriendOnlyWithoutPrivacy = $this->normalize($options, 'showFriendOnlyWithoutPrivacy', false);

		$query = array();
		$query[] = 'SELECT a.`id` as `uid`, DATE_FORMAT(fd.`raw`, ' . $db->Quote('%m%d') . ') AS `day`, fd.`field_id`, DATE_FORMAT(fd.`raw`, "%M %d %Y") as `displayday`';
		$query[] = ", (366 + DAYOFYEAR(DATE_ADD(fd.`raw`, INTERVAL (YEAR(" . $db->Quote($now) . ") - YEAR(fd.`raw`)) YEAR))";
		$query[] = " - DAYOFYEAR(" . $db->Quote($now) . ")) % 366 as `left_days`";

		$query[] = 'FROM `#__users` AS a';
		$query[] = "inner join `#__social_users` as u on a.`id` = u.`user_id`";
		$query[] = "inner join `#__social_fields_data` as fd on fd.`uid` = a.`id`";
		$query[] = "INNER JOIN `#__social_fields` as f on fd.`field_id` = f.`id`";
		$query[] = "INNER JOIN `#__social_privacy_items` as pi on fd.`field_id` = pi.`uid` and pi.`type` = " . $db->Quote('field') . "and pi.`user_id` = a.`id`";

		// exclude esad users
		$query[] = ' INNER JOIN `#__social_profiles_maps` as upm on a.`id` = upm.`user_id`';
		$query[] = ' INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id` and up.`community_access` = 1';

		if (ES::isBlockEnabled()) {
			$query[] = " LEFT JOIN `#__social_block_users` as bus";
			$query[] = " on (u.`user_id` = bus.`user_id` AND bus.`target_id` = " . $db->Quote($my->id);
			$query[] = " OR u.`user_id` = bus.`target_id` AND bus.`user_id` = " . $db->Quote($my->id) . ')';
		}

		$query[] = ' where a.`id` != ' . $db->Quote($userId);
		$query[] = ' and u.`state` != ' . $db->Quote('0');
		$query[] = ' and f.`unique_key` = ' . $db->Quote($key);
		$query[] = ' and fd.`datakey` = ' . $db->Quote('date');
		$query[] = ' and fd.`raw` != ' . $db->Quote('');

		// privacy here.
		$query[] = ' AND (';

		if ($showFriendOnlyWithoutPrivacy) {
			$query[] = '(' . $this->generateIsFriendSQL('pi.`user_id`', $my->id) . ') > 0)';

		} else {
			//public
			$query[] = '(pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

			//member
			$query[] = '((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $my->id . ' > 0)) OR ';

			if (ES::config()->get('friends.enabled')) {
				// friends
				// It will respect the user birthday custom field privacy
				$query[] = '((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $this->generateIsFriendSQL('pi.`user_id`', $my->id) . ') > 0)) OR ';
			} else {
				// fall back to 'member'
				$query[] = '((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $my->id . ' > 0)) OR ';
				$query[] = '((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $my->id . ' > 0)) OR ';
			}

			//only me
			$query[] = '((pi.`value` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (pi.`user_id` = ' . $my->id . ')) ) ';
		}

		if (ES::isBlockEnabled()) {
			$query[] = "and bus.`id` IS NULL";
		}

		// filter out user which is blocked.
		$query[] = ' AND a.`block`' . ' = ' . $db->Quote('0');
		$query[] = " AND ((366 + DAYOFYEAR(DATE_ADD(fd.`raw`, INTERVAL (YEAR(" . $db->Quote($now) . ") - YEAR(fd.`raw`)) YEAR))";
		$query[] = " - DAYOFYEAR(" . $db->Quote($now) . ")) % 366) <= 7";

		$query[] = " group by a.`id`";
		$query[] = " order by `left_days`";

		if ($limit) {
			$query = implode(' ', $query);

			$this->setState('limit', $limit);
			$this->setTotal($query, true);

			// if limitstart null get limiststart from request
			if (is_null($limitstart)) {
				// Get the limitstart.
				$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
				$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
			}

			$this->setState('limitstart' , $limitstart);

			$result = $this->getData($query);
		} else {
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}

		return $result;
	}

	/**
	 * Generate sql for determine if source and target is friend
	 * @since	3.1
	 * @access	public
	 */
	public function generateIsFriendSQL($source, $target)
	{
		$query = "select count(1) from `#__social_friends` where (`actor_id` = $source and `target_id` = $target) OR (`target_id` = $source and `actor_id` = $target) and `state` = 1";

		return $query;
	}

	/**
	 * Determines whether the current user is active or not.
	 *
	 * @since	2.1.10
	 * @access	public
	 */
	public function isOnline($id, $useCache = true)
	{
		$db	= ES::db();

		$onlineKey = 'user.online.' . $id;

		if ($useCache && ES::cache()->exists($onlineKey)) {
			return ES::cache()->get($onlineKey);
		}

		// Get the session life time so we can know who is really online.
		$jConfig = ES::jConfig();
		$lifespan = $jConfig->getValue('lifetime');
		$sharedSess = $jConfig->get('shared_session', 0);
		$sessionHandler = $jConfig->get('session_handler', '');

		$online = time() - ($lifespan * 60);

		// it seems like the unix time that stored into session table included the local.
		$now = ES::date()->toSql(true);

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__session');
		$query[] = 'WHERE ' . $db->nameQuote('userid') . '=' . $db->Quote($id);
		if ($sessionHandler == 'database') {
			// $query[] = 'AND ' . $db->nameQuote('time') . ' >= UNIX_TIMESTAMP(DATE_SUB(' . $db->Quote($now) . ', INTERVAL ' . $lifespan . ' MINUTE))' ;
			$query[] = 'AND ' . $db->nameQuote('time') . ' >= ' . $db->Quote($online) ;
		} else {
			$query[] = 'AND ' . $db->nameQuote('guest') . ' = 0';
		}

		if (!$sharedSess) {
			$query[] ='AND ' . $db->nameQuote('client_id') . ' <> ' . $db->Quote(1);
		}

		$query = implode(' ', $query);

		// echo $query;
		// exit;

		$db->setQuery($query);

		$online	= $db->loadResult() > 0;

		return $online;
	}

	/**
	 * Determines whether the current user is active or not.
	 *
	 * @since	2.1.10
	 * @access	public
	 */
	public function isOnlineMobile($id, $useCache = true)
	{
		static $_cache = [];

		$db	= ES::db();
		$onlineKey = 'user.onlinemobile.' . $id;

		if (!isset($_cache[$onlineKey]) || !$useCache) {

			$query = [];
			$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__session') . ' as a';
			$query[] = 'INNER JOIN ' . $db->nameQuote('#__social_restapi_login') . ' as b on a.`session_id` = b.`session_id`';
			$query[] = 'WHERE ' . $db->nameQuote('userid') . '=' . $db->Quote($id);
			$query[] = 'AND ' . $db->nameQuote('guest') . ' = 0';

			$query = implode(' ', $query);
			$db->setQuery($query);

			$online	= $db->loadResult() > 0;
			$_cache[$onlineKey] = $online;
		}

		return $online;
	}

	/**
	 * Perform necessary logics when a user is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($user)
	{
		$id = (int) $user['id'];
		$email = $user['email'];


		// Delete form #__social_oauth
		$this->deleteOAuth($id);

		// Delete user stream item
		$this->deleteStream($id);

		// Delete user photos
		$this->deletePhotos($id);

		// Delete user's photos that has been posted in the cluster #3828
		$this->deleteClusterPhotos($id);

		// Change the creator of cluster albums that created by this user to the cluster owner
		$this->updateClusterAlbumsCreator($id);

		// Delete user videos
		$this->deleteVideos($id);

		// Delete user audios
		$this->deleteAudios($id);

		// Delete user relations within a cluster
		$this->deleteClusterNodes($id);

		// Delete user marketplace item
		$this->deleteUserMarketplace($id);

		// Delete clusters that were created by this user
		$this->deleteUserClusters($id);

		// Delete user followers
		$this->deleteFollowers($id);

		// Delete user notifications
		$this->deleteNotifications($id, $email);

		// Delete user comments from the site
		$this->deleteComments($id);

		// Delete user likes from the site
		$this->deleteLikes($id);

		// Delete user friends from the site
		$this->deleteFriends($id);

		// Conversations should also be deleted from the site.
		$this->deleteConversations($id);

		// Delete storage files.
		$this->deleteStorageFiles($id);

		// Delete app relations
		$this->deleteAppRelations($id);

		// Delete verification request
		$this->deleteVerificationRequest($id);

		// Delete user points history
		$this->deletePoints($id);

		// delete user alert mapping.
		$this->deleteAlertMaps($id);

		// delete user's privacy #5028
		$this->deletePrivacyMaps($id);

		// delete user's bookmark #5028
		$this->deleteBookmarks($id);

		// delete user's polls #5028
		$this->deletePolls($id);

		// delete user's blocks #5028
		$this->deleteBlocks($id);

		// this has to be last step. #4153
		// Delete profile mapping
		$this->deleteProfile($id);

		return true;
	}

	/**
	 * Delete the user's blocked records
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function deleteBlocks($userId)
	{
		$db = ES::db();

		$query = "delete a from `#__social_block_users` as a where a.`user_id` = " . $db->Quote($userId) . " OR a.`target_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();

	}

	/**
	 * Delete the user's polls
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function deletePolls($userId)
	{
		$db = ES::db();

		// delete polls created by this user
		$query = "delete a, b, c from `#__social_polls` as a";
		$query .= " inner join `#__social_polls_items` as b on a.id = b.poll_id";
		$query .= " inner join `#__social_polls_users` as c on a.id = c.poll_id";
		$query .= " where a.`created_by` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();


		// delete poll's vote from this user.
		$query = "select * from `#__social_polls_users` where `user_id` = " . $db->Quote($userId);
		$db->setQuery($query);

		$results = $db->loadObjectList();

		if ($results) {
			foreach ($results as $row) {

				if ($row->state == SOCIAL_STATE_PUBLISHED) {
					// we need to minus the count in poll item
					$update = "update `#__social_polls_items` set `count` = `count` - 1";
					$update .= " where `id` = " . $db->Quote($row->poll_itemid);
					// we need to make sure after the update the count should not have negative value
					$update .= " and `count` > 0";

					$db->setQuery($update);
					$db->query();
				}
			}

			// now lets delete records in polls_users
			$query = "delete a from `#__social_polls_users` as a where a.`user_id` = " . $db->Quote($userId);
			$db->setQuery($query);
			$db->query();
		}

	}


	/**
	 * Delete the user's bookmark
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function deleteBookmarks($userId)
	{
		$db = ES::db();

		$query = "delete a from `#__social_bookmarks` as a where a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();

	}


	/**
	 * Delete the user's alert mapping
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function deleteAlertMaps($userId)
	{
		$db = ES::db();

		$query = "delete a from `#__social_alert_map` as a where a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();

	}


	/**
	 * Delete the user's privacy data
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function deletePrivacyMaps($userId)
	{
		$db = ES::db();

		// privacy custom
		$query = "delete a from `#__social_privacy_customize` as a where a.`utype` = " . $db->Quote(SOCIAL_TYPE_USER) . " and a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();


		// privacy items
		$query = "delete a from `#__social_privacy_items` as a where a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();


		// privacy map
		$query = "delete a from `#__social_privacy_map` as a where a.`uid` = " . $db->Quote($userId) . " and a.`utype` = " . $db->Quote(SOCIAL_TYPE_USER);
		$db->setQuery($query);
		$db->query();

	}


	/**
	 * Delete the user's storage files.
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function deleteStorageFiles($id)
	{
		$model = ES::model('Files');
		$model->deleteUserFiles($id);
	}


	/**
	 * Delete the user's photos that has been posted in the cluster
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function deleteClusterPhotos($id)
	{
		$model = ES::model('Photos');
		$model->deleteClusterPhotos($id);
	}

	/**
	 * Delete app relations for a user
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function deleteAppRelations($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_apps_map');
		$sql->where('uid', $id);
		$sql->where('type', SOCIAL_TYPE_USER);

		$db->setQuery($sql);
		return $db->query();
	}

	/**
	 * Remove all followers and following from this user
	 *
	 * @since	1.2.8
	 * @access	public
	 */
	public function deleteFollowers($id)
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		// Delete notifications generated for this user.
		$sql->delete('#__social_subscriptions');
		$sql->where('uid', $id);
		$sql->where('type', 'user.user');
		$db->setQuery($sql);
		$db->Query();

		// Delete notifications generated by this user.
		$sql->clear();
		$sql->delete('#__social_subscriptions');
		$sql->where('user_id', $id);
		$sql->where('type', 'user.user');
		$db->setQuery($sql);
		$db->Query();
	}

	/**
	 * Remove all notifications from a user.
	 *
	 * @since	1.2.8
	 * @access	public
	 */
	public function deleteNotifications($id, $email)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete notifications generated for this user.
		$sql->delete('#__social_notifications');
		$sql->where('target_id', $id);
		$db->setQuery($sql);
		$db->Query();

		// Delete notifications generated by this user.
		$sql->clear();
		$sql->delete('#__social_notifications');
		$sql->where('actor_id', $id);
		$db->setQuery($sql);
		$db->Query();

		// Get the user's email address
		$sql->clear();
		$sql->delete('#__social_mailer');
		$sql->where('recipient_email', $email);
		$db->setQuery($sql);
		$db->Query();
	}

	/**
	 * Remove a user from all cluster nodes
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function deleteClusterNodes($id)
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		$sql->delete('#__social_clusters_nodes');
		$sql->where('uid', $id);
		$sql->where('type', 'user');

		$db->setQuery($sql);

		$db->Query();
	}

	/**
	 * Remove marketplace item created by this user
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function deleteUserMarketplace($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_marketplaces');
		$sql->where('uid', $id);
		$sql->where('type', 'user');

		$db->setQuery($sql);

		$listings = $db->loadObjectList();

		if ($listings) {
			foreach ($listings as $row) {
				$listing = ES::marketplace($row->id);
				$listing->delete();
			}
		}
	}

	/**
	 * Remove clusters created by this user
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteUserClusters($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_clusters');
		$sql->where('creator_uid', $id);
		$sql->where('creator_type', 'user');

		$db->setQuery($sql);

		$clusters = $db->loadObjectList();

		if ($clusters) {
			foreach ($clusters as $row) {
				$cluster = ES::cluster($row->cluster_type, $row->id);
				$cluster->delete();
			}
		}
	}

	/**
	 * Deletes the user profile data.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteProfile($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete profile mapping of the user
		$sql->delete('#__social_profiles_maps');
		$sql->where('user_id' , $userId);
		$db->setQuery($sql);
		$db->Query();

		// Delete user custom fields.
		$sql->clear();
		$sql->delete('#__social_fields_data');
		$sql->where('uid' , $userId);
		$sql->where('type' , SOCIAL_TYPE_USER);
		$db->setQuery($sql);
		$db->Query();

		// Delete #__social_users
		// $sql->clear();
		// $sql->delete('#__social_users');
		// $sql->where('user_id' , $userId);
		// $db->setQuery($sql);
		// $db->Query();

		// for __social_users we need to use jtable to delete so that other triggers like deleting sef cache for this user will work correctly.
		// #4856
		$table = ES::table('Users');
		$table->load(array('user_id' => $userId));
		$table->delete();

		return true;
	}

	/**
	 * Delete user photos
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deletePhotos($userId)
	{
		$db = ES::db();

		// Delete user albums
		$query = "select * from `#__social_albums` where `uid` = " . $db->Quote($userId) . " and `type` = " . $db->Quote(SOCIAL_TYPE_USER);
		$db->setQuery($query);
		$albums	= $db->loadObjectList();

		if ($albums) {
			foreach ($albums as $row) {
				$album = ES::table('Album');
				$album->load($row->id);

				$album->delete();
			}
		}

		// now we need to remove the record in avatar / cover table.
		// #5028

		// avatar
		$this->deleteAvatar($userId);

		// cover
		$this->deleteCover($userId);

		return true;
	}

	/**
	 * Delete user videos
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function deleteVideos($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete user videos
		$sql->clear();
		$sql->select('#__social_videos');
		$sql->where('user_id', $userId);
		$db->setQuery($sql);

		$videos	= $db->loadObjectList();

		if ($videos) {
			foreach ($videos as $row) {
				$tbl = ES::table('video');
				$tbl->bind($row);

				$video = ES::video($tbl);
				$video->delete();
			}
		}

		return true;
	}


	/**
	 * Delete user audios
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function deleteAudios($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete user audios
		$sql->clear();
		$sql->select('#__social_audios');
		$sql->where('user_id', $userId);
		$db->setQuery($sql);

		$audios	= $db->loadObjectList();

		if ($audios) {
			foreach ($audios as $row) {
				$tbl = ES::table('audio');
				$tbl->bind($row);

				$audio = ES::audio($tbl);
				$audio->delete();
			}
		}

		return true;
	}


	/**
	 * Delete user's cover
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteCover($userId)
	{
		$cover 	= ES::table('Cover');
		$cover->load(['uid' => $userId , 'type' => SOCIAL_TYPE_USER]);

		if ($cover->id) {
			$cover->delete();
		}

		return true;
	}

	/**
	 * Delete user's avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteAvatar($userId)
	{
		$avatar = ES::table('Avatar');
		$avatar->load(['uid' => $userId , 'type' => SOCIAL_TYPE_USER]);

		if ($avatar->id) {
			$avatar->delete();
		}

		return true;
	}

	/**
	 * Deletes the conversations
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteConversations($userId)
	{
		// Get a list of conversations the user is participating in
		$model = ES::model('Conversations');

		return $model->deleteConversationsInvolvingUser($userId);
	}

	/**
	 * Deletes user likes
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteLikes($userId)
	{
		$db	= ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_likes');
		$sql->where('created_by' , $userId);

		$db->setQuery($sql);
		$db->Query();

		return true;
	}

	/**
	 * Deletes user comments
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteComments($userId)
	{
		$db	= ES::db();

		$query = "SELECT `id` FROM `#__social_comments` WHERE `created_by` = " . $db->Quote($userId);

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if (!$results) {
			return true;
		}

		$commentTbl = ES::table('Comments');

		foreach ($results as $comment) {

			$state = $commentTbl->load($comment->id);

			// some of the child comment might be deleted through the delete function
			// So we need to skip this at here.
			if (!$state) {
				continue;
			}

			// this delete function will delete the child comment as well.
			$commentTbl->delete();
		}

		return true;
	}

	/**
	 * Deletes the user point relations
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deletePoints($userId)
	{
		$db = ES::db();

		// delete user points history
		$query = "delete a from `#__social_points_history` as a WHERE a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();

		// delete user badges history #5028
		$query = "delete a from `#__social_badges_history` as a WHERE a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();

		// delete user badges mapping #5028
		$query = "delete a from `#__social_badges_maps` as a WHERE a.`user_id` = " . $db->Quote($userId);
		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Deletes the user verification request
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function deleteVerificationRequest($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "delete from `#__social_verification_requests`";
		$query .= " where `uid` = " . $db->Quote($userId);
		$query .= " and `type` = " . $db->Quote(SOCIAL_TYPE_USER);

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Deletes the user friend relations
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteFriends($userId)
	{
		// Delete friend list
		$db	= ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_lists');
		$sql->where('user_id' , $userId);

		$db->setQuery($sql);
		$db->Query();

		$sql->clear();

		// Delete friends
		$sql->delete('#__social_friends');
		$sql->where('actor_id' , $userId);
		$sql->where('target_id' , $userId, '=', 'or');

		$db->setQuery($sql);
		$db->Query();

		return true;
	}

	/**
	 * Deletes the user point relations
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteLabels($userId)
	{
		// Delete labels
		$db		= ES::db();
		$sql	= $db->sql();

		$query->delete('#__social_labels');
		$sql->where('created_by' , $userId);

		$db->setQuery($sql);
		$db->Query();

		return true;
	}


	/**
	 * Deletes stream of a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteStream($userId)
	{
		$db = ES::db();

		$query = "delete a, b from `#__social_stream` as a";
		$query .= " inner join `#__social_stream_item` as b";
		$query .= " 	on a.`id` = b.`uid`";
		$query .= " where a.`actor_id` = " . $db->Quote($userId);
		$query .= " and a.`actor_type` = " . $db->Quote(SOCIAL_TYPE_USER);

		$db->setQuery($query);
		$db->query();

		// need to delete story stream that is to this user.
		$query = "delete a, b from `#__social_stream` as a";
		$query .= " inner join `#__social_stream_item` as b";
		$query .= " 	on a.`id` = b.`uid`";
		$query .= " where a.`context_type` = " . $db->Quote('story');
		$query .= " and a.`verb` = " . $db->Quote('create');
		$query .= " and a.`target_id` = " . $db->Quote($userId);
		$query .= " and a.`cluster_id` = " . $db->Quote('0');

		$db->setQuery($query);
		$db->query();


		// now we need to delete friends stream who target is this current user.
		$query = "delete a from `#__social_stream_item` as a";
		$query .= " where a.`target_id` = " . $db->Quote($userId);
		$query .= " and a.`actor_type` = " . $db->Quote(SOCIAL_TYPE_USER);
		$query .= " and a.`context_type` = " . $db->Quote('friends');

		$db->setQuery($query);
		$db->query();

		// now we need to clean up the stream table incase there are any left over items.
		$query = "delete a from `#__social_stream` as a";
		$query .= " where not exists (select b.`uid` from `#__social_stream_item` as b where b.`uid` = a.`id`)";

		$db->setQuery($query);
		$db->query();


		// Delete any hidden stream by the user.
		$query = "delete a from `#__social_stream_hide` as a";
		$query .= " where a.`user_id` = " . $db->Quote($userId);

		$db->setQuery($query);
		$db->Query();

		// Delete any hidden stream from this user. #5028
		$query = "delete a from `#__social_stream_hide` as a";
		$query .= " where a.`actor_id` = " . $db->Quote($userId);
		$query .= " and a.`uid` = " . $db->Quote('0');

		$db->setQuery($query);
		$db->Query();


		// we need to remove user's items from stream history tables as well.
		// #5028
		$query = "delete a, b from `#__social_stream_history` as a";
		$query .= " inner join `#__social_stream_item_history` as b";
		$query .= " 	on a.`id` = b.`uid`";
		$query .= " where a.`actor_id` = " . $db->Quote($userId);
		$query .= " and a.`actor_type` = " . $db->Quote(SOCIAL_TYPE_USER);

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Retrieve the user's id given the authentication code for REST api
	 *
	 * @since	1.2.8
	 * @access	public
	 */
	public function getUserIdFromAuth($code)
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select('#__social_users');
		$sql->column('user_id');
		$sql->where('auth', $code);

		$db->setQuery($sql);

		$id		= (int) $db->loadResult();

		return $id;
	}

	/**
	 * Retrieves the user's id
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserId($key , $value)
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		$sql->select('#__users');
		$sql->column('id');
		$sql->where($key , $value);

		$db->setQuery($sql);

		$id 	= $db->loadResult();
		return $id;
	}

	/**
	 * Reset password confirmation
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function verifyResetPassword($username, $code)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users');
		$sql->column('activation');
		$sql->column('id');
		$sql->column('block');

		$config = ES::config();

		if ($config->get('registrations.emailasusername') && $config->get('general.site.loginemail')) {
			$sql->where('email', $username);
		} elseif ($config->get('general.site.loginemail')) {
			$sql->where('email', $username, '=', 'OR');
			$sql->where('username', $username, '=', 'OR');
		} else {
			$sql->where('username', $username);
		}

		$db->setQuery($sql);

		$obj = $db->loadObject();

		if (!$obj) {
			$this->setError(JText::_('COM_EASYSOCIAL_USERS_NO_SUCH_USER_WITH_EMAIL'));
			return false;
		}

		// Try to verify the token provided by the user
		try {
			ESUserHelper::verifyResetPassword($code, $obj->activation);
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}


		// Ensure that the user account is not blocked
		if ($obj->block) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_USER_BLOCKED'));
			return false;
		}

		// Push the user data into the session.
		$app = JFactory::getApplication();
		$app->setUserState('com_users.reset.token', $obj->activation);
		$app->setUserState('com_users.reset.user', $obj->id);

		return true;
	}

	/**
	 * Resets the user's password
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function resetPassword($password, $password2)
	{
		// Get the token and user id from the confirmation process.
		$app = JFactory::getApplication();
		$token = $app->getUserState('com_users.reset.token' , null);
		$userId = $app->getUserState('com_users.reset.user' , null);

		// Check for the token and the user's id.
		if (!$token || !$userId) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_TOKENS_MISSING'));
			return false;
		}

		// Retrieve the user object
		$user = JUser::getInstance($userId);

		// Check for a user and that the tokens match.
		if (empty($user) || $user->activation !== $token) {
			$this->setError(JText::_('COM_EASYSOCIAL_USERS_NO_SUCH_USER'));
			return false;
		}

		// Ensure that the user account is not blocked
		if ($user->block) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_USER_BLOCKED'));
			return false;
		}

		// Generates the new password hash
		$salt = JUserHelper::genRandomPassword(32);
		$password = ESCompat::getCryptedPasswordWithSalt($password, $salt);

		// Update user's object
		$user->password = $password;

		// Reset the activation
		$user->activation = '';

		// Set the clear password
		$user->password_clear = $password2;

		if (isset($user->requireReset)) {
			$user->requireReset	= 0;
		}

		// Save the user to the database.
		if (!$user->save(true)) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_SAVE_ERROR'));
			return false;
		}

		// we need to reset require_reset from social_users table.
		$userModel = ES::model('Users');
		$userModel->updateUserPasswordResetFlag($user->id, '0');

		// Flush the user data from the session.
		$app->setUserState('com_users.reset.token', null);
		$app->setUserState('com_users.reset.user', null);

		return true;
	}

	/**
	 * Perform a password reset based on the require password reset option
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function resetRequirePassword($password, $password2)
	{
		// Get the token and user id from the confirmation process.
		$app = JFactory::getApplication();

		// Retrieve the user object
		$user = JFactory::getUser();

		// Ensure that the user account is not blocked
		if ($user->block) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_USER_BLOCKED'));
			return false;
		}

		// Generates the new password hash
		$salt = JUserHelper::genRandomPassword(32);
		$crypted = ESCompat::getCryptedPasswordWithSalt($password, $salt);

		// Update user's object
		$user->password = $password;

		// Set the clear password
		$user->password_clear = $password2;

		// Do not
		if (isset($user->requireReset)) {
			$user->requireReset	= 0;
		}

		// Save the user to the database.
		if (!$user->save(true)) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_SAVE_ERROR'));
			return false;
		}

		// we need to reset require_reset from social_users table.
		$userModel = ES::model('Users');
		$userModel->updateUserPasswordResetFlag($user->id, '0');

		return true;
	}

	/**
	 * Delete any oauth related data here
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteOAuth($userId)
	{
		$db	= ES::db();
		$userId = (int) $userId;

		$query = [
			'SELECT * FROM `#__social_oauth`',
			'WHERE `uid` = ' . $db->Quote($userId),
			'AND `type`=' . $db->Quote(SOCIAL_TYPE_USER)
		];

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ($rows) {
			$query = [
				'DELETE FROM `#__social_oauth`',
				'WHERE `uid`=' . $db->Quote($userId),
				'AND `type`=' . $db->Quote(SOCIAL_TYPE_USER)
			];

			$db->setQuery($query);
			$db->Query();

			// Delete oauth histories as well
			if ($rows) {
				foreach ($rows as $row) {
					$query = 'DELETE FROM `#__social_oauth_history` WHERE `oauth_id`=' . $db->Quote($row->oauth_id);

					$db->setQuery($query);
					$db->Query();
				}
			}
		}

		return true;
	}

	/**
	 * Creates a user in the system
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function create($data, SocialUser $user, SocialTableProfile $profile, $forceApprove = false)
	{
		// Get a list of user groups this profile is assigned to
		$json = ES::json();
		$groups = $json->decode($profile->gid);

		// Need to bind the groups under the `gid` column from Joomla.
		$data['gid'] = $groups;

		// Bind the posted data
		$user->bind($data, SOCIAL_POSTED_DATA);

		// Detect the profile type's registration type.
		$type = $profile->getRegistrationType();

		// We need to generate an activation code for the user.
		if ($type == 'verify' || $type == 'confirmation_approval') {
			$user->activation = ES::getHash(JUserHelper::genRandomPassword());
		}

		// If the registration type requires approval or requires verification, the user account need to be blocked first.
		if (($type == 'approvals' || $type == 'verify' || $type == 'confirmation_approval') && !$forceApprove) {
			$user->block = 1;
		}

		// Get registration type and set the user's state accordingly.
		$userState = constant('SOCIAL_REGISTER_' . strtoupper($type));

		if ($forceApprove) {
			$userState = SOCIAL_USER_STATE_ENABLED;
		}

		$user->set('state', $userState);

		// Save the user object
		$state = $user->save();

		// If there's a problem saving the user object, set error message.
		if (!$state) {
			$this->setError($user->getError());
			return false;
		}

		// If this is autoApprove/forceApprove, we assign the badge
		if ($forceApprove) {
			// @badge: registration.create
			$badge = ES::badges();
			$badge->log('com_easysocial', 'registration.create', $user->id, JText::_('COM_EASYSOCIAL_REGISTRATION_BADGE_REGISTERED'));
		}

		// Set the user with proper `profile_id`
		$user->profile_id = $profile->id;

		// Once the user is saved successfully, add them into the profile mapping.
		$profile->addUser($user->id);

		$type = $profile->getRegistrationType();

		if ($forceApprove) {
			$type = 'auto';
		}

		// Assign users into the EasySocial groups
		$defaultGroups = $profile->getDefaultClusters('groups');

		if ($defaultGroups) {
			foreach ($defaultGroups as $group) {
				$group->createMember($user->id, true, $type);
			}
		}

		// Assign users into the EasySocial pages
		$defaultPages = $profile->getDefaultClusters('pages');

		if ($defaultPages) {
			foreach ($defaultPages as $page) {
				$page->createMember($user->id, true, $type);
			}
		}

		return $user;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setUserFieldsData($ids)
	{

		$db = ES::db();
		$sql = $db->sql();


		// if ids is empty, do not process at all.
		if (! $ids) {
			return;
		}

		// lets get the fields first.
		$query = "select a.*, b.`uid` as `profile_id`, c.`element`";
		$query .= " from `#__social_fields` as a";
		$query .= " inner join `#__social_fields_steps` as b on a.`step_id` = b.`id`";
		$query .= " inner join `#__social_apps` as c on a.`app_id` = c.`id`";
		$query .= " where b.`type` = " . $db->Quote(SOCIAL_TYPE_PROFILES);

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		// next we get the field_datas for the users.
		$query = "select a.* from `#__social_fields_data` as a";
		$query .= "	inner join `#__social_fields` as b on a.`field_id` = b.`id`";
		$query .= " where a.`type` = 'user'";
		$query .= " and a.`uid` IN (" . implode(',', $ids) . ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);

		$dresults = $db->loadObjectList();

		$fields = array();
		$data = array();

		// binding data into field jtable object
		if ($results) {

			// We need to bind the fields with SocialTableField
			$fieldIds = array();

			foreach($results as $row) {
				$field 	= ES::table('Field');
				$field->bind($row);

				$fieldIds[] = $field->id;

				$field->data = '';
				$field->profile_id = isset($row->profile_id) ? $row->profile_id : '';
				$fields[$field->id]	= $field;
			}

			// // set the field options in batch.
			ES::table('Field')->setBatchFieldOptions($fieldIds);
		}

		//groupping fields data for later processing.
		if ($dresults) {
			foreach($dresults as $item) {
				$data[$item->uid][$item->field_id][] = $item;
			}
		}


		$final = array();
		//now let combine the data with fields for each users
		if ($data) {
			foreach ($data as $uid => $items) {
				// foreach field data

				$xfield = null;

				foreach ($items as $fid => $fielddata) {

					if (!isset($fields[$fid]) || !$fields[$fid]) {
						continue;
					}

					$xfield = clone $fields[$fid];

					$xfield->bindData($uid, SOCIAL_TYPE_USER, $fielddata);
					$xfield->data = $xfield->getData($uid, SOCIAL_TYPE_USER);
					$xfield->uid = $uid;
					$xfield->type = SOCIAL_TYPE_USER;

					$user = ES::user($uid);
					$user->bindCustomField($xfield);
				}

			}//foreach
		}

	}

	/**
	 *
	 * @since  1.3
	 * @access public
	 */
	public function setUserGroupsBatch($ids)
	{
		// Get the path to the helper file.
		$file = SOCIAL_LIB . '/user/helpers/joomla.php';
		require_once($file);

		SocialUserHelperJoomla::setUserGroupsBatch($ids);
	}

	/**
	 * Given a user id and a password, ensure that the user is entering the correct password
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function verifyUserPassword($id, $password)
	{
		$db = Jfactory::getDbo();

		$query = $db->getQuery(true)
			->select('password')
			->from('#__users')
			->where('id=' . $db->quote($id));

		$db->setQuery($query);
		$passwordHash = $db->loadResult();

		if (!$passwordHash) {
			return false;
		}

		return ESUserHelper::verifyUserPassword($id, $passwordHash, $password);
	}

	/**
	 * Reset user's completed fields count in #__social_users based on profile id.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function resetCompletedFieldsByProfileId($profileId)
	{
		$query = "UPDATE `#__social_users` AS `a` LEFT JOIN `#__social_profiles_maps` AS `b` ON `a`.`user_id` = `b`.`user_id` SET `a`.`completed_fields` = 0 WHERE `b`.`profile_id` = '" . $profileId . "'";

		$db = ES::db();
		$sql = $db->sql();

		$sql->raw($query);

		$db->setQuery($sql);

		return $db->query();
	}

	/**
	 * get inactive users based on specify duration.
	 *
	 * @since  1.4
	 * @access public
	 */
	public function getInactiveUsers($duration, $limit = 20)
	{
		$db = ES::db();
		$sql = $db->sql();

		$now = ES::date();
		$today = $now->toMySQL();

		$query = "select a.`id`, a.`name`, a.`email`, a.`params` from `#__users` as a";
		$query .= " inner join `#__social_users` as b on a.`id` = b.`user_id`";
		$query .= " where a.`block` = 0";
		$query .= " and a.`lastvisitDate` != " . $db->Quote('00-00-00 00:00:00');
		$query .= " and a.`lastvisitDate` <= date_sub(" . $db->Quote($today) . ", INTERVAL $duration DAY)";
		$query .= " and b.`reminder_sent` = 0";
		$query .= " limit $limit";

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 *
	 * @since  1.4
	 * @access public
	 */
	public function updateReminderSentFlag($userId, $flag)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'update `#__social_users` set `reminder_sent` = ' . $db->Quote($flag);
		$query .= ' where user_id = ' . $db->Quote($userId);

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 *
	 * @since  1.4
	 * @access public
	 */
	public function updateActivationReminderSentFlag($userId, $flag)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'update `#__social_users` set `activation_reminder_sent` = ' . $db->Quote($flag);
		$query .= ' where user_id = ' . $db->Quote($userId);

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 *
	 * @since  1.4
	 * @access public
	 */
	public function updateJoomlaUserPasswordResetFlag($userId, $jFlag, $esFlag)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Joomla user
		$query = 'update `#__users` set `requireReset` = ' . $db->Quote($jFlag);
		$query .= ' where `id` = ' . $db->Quote($userId);

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		// EasySocial User
		$this->updateUserPasswordResetFlag($userId, $esFlag);

		return true;
	}

	/**
	 *
	 * @since  1.4
	 * @access public
	 */
	public function updateUserPasswordResetFlag($userId, $esFlag)
	{
		$db = ES::db();
		$sql = $db->sql();

		// EasySocial user
		$query = 'update `#__social_users` set `require_reset` = ' . $db->Quote($esFlag);
		$query .= ' where `user_id` = ' . $db->Quote($userId);

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Bans a user for a specific period
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function updateBlockInterval($users, $period)
	{
		$db = ES::db();
		$sql = $db->sql();

		if (!$users) {
			return false;
		}

		if (!is_array($users)) {
			$users = array($users);
		}

		$date = ES::date();

		$query = "update `#__social_users` set `block_period` = " . $db->Quote($period);

		if ($period == '0') {
			// clear the date
			$query .= ", `block_date` = null";
		} else {
			$query .= ", `block_date` = " . $db->Quote($date->toMySQL());
		}

		if (count($users) > 1) {
			$query .= " where `user_id` IN (" . implode(',', $users) . ")";
		} else {
			$query .= " where `user_id` = " . $db->Quote($users[0]);
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();

		return $state;
	}

	/**
	 *
	 * @since  1.4
	 * @access public
	 */
	public function getExpiredBannedUsers($userId = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		$now = ES::date();

		$query = "select `user_id` from `#__social_users`";
		$query .= " where `state` = " . $db->Quote(SOCIAL_USER_STATE_DISABLED);
		$query .= " and `block_period` > 0";
		$query .= " and DATE_ADD(`block_date`, INTERVAL `block_period` MINUTE) <= " . $db->Quote($now->toMySQL());
		if ($userId) {
			$query .= " and `user_id` = " . $db->Quote($userId);
		}

		// echo $query;exit;

		$sql->raw($query);
		$db->setQuery($sql);

		$users = $db->loadColumn();

		return $users;
	}


	/**
	 * send reminder to inactive user.
	 *
	 * @since  1.4
	 * @access public
	 */
	public function sendReminder($users)
	{
		$count = 0;
		$jConfig = ES::jConfig();
		$config = ES::config();

		if ($users) {

			// default language tag
			$languageTag = JFactory::getLanguage()->getTag();

			// Push arguments to template variables so users can use these arguments
			$params 	= array(
								'loginLink'	=> '',
								'duration'	=> $config->get('users.reminder.duration', '30'),
								'siteName'	=> $jConfig->getValue('sitename')
							);

			foreach ($users as $user) {

				// set language based on user lang setting. #1076
				$userLang = null;
				if (isset($user->params) && $user->params) {
					$userParams = ES::json()->decode($user->params);

					if (isset($userParams->language) && $userParams->language) {
						$userLang = $userParams->language;
					} else {
						$userLang = $languageTag;
					}
				}

				// Load site languages
				$lang = ES::language();
				$lang->loadSite($userLang, true, true);

				if ($userLang) {
					// update login link to build based on user language. #1076
					$option = array();
					$option['lang'] = $userLang;

					$loginLink = FRoute::login($option , false);
					$params['loginLink'] = $loginLink;
				}

				// Immediately send out emails
				$mailer 	= ES::mailer();

				// Set the user's name.
				$params['recipientName']	= $user->name;

				// Get the email template.
				$mailTemplate	= $mailer->getTemplate();

				// Set recipient
				$mailTemplate->setRecipient($user->name, $user->email);

				// set language;
				$mailTemplate->setLanguage($userLang);

				// Set title
				$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_INACTIVE_REMINDER_SUBJECT', $user->name);
				$mailTemplate->setTitle($title);

				// Set the template
				$mailTemplate->setTemplate('site/user/remind.inactive', $params);

				// Try to send out email to the admin now.
				$state = $mailer->create($mailTemplate);

				if ($state) {
					// need to update the reminder_sent flag
					$this->updateReminderSentFlag($user->id, '1');

					$count++;
				}

			}
		}

		return $count;
	}

	/**
	 * Send activation reminder to pending user.
	 *
	 * @since  1.4
	 * @access public
	 */
	public function sendActivationReminder($users)
	{
		$count = 0;
		$jConfig = ES::jConfig();
		$config = ES::config();

		if ($users) {

			$model = ES::model('Registration');
			$count = 0;

			foreach ($users as $user) {

				$id = (int) $user->id;
				$user = ES::user($id);

				// If the user is not blocked and doesn't have an activation, we shouldn't be doing anything.
				if (!$user->block || !$user->activation) {
					continue;
				}

				$state = $model->resendActivation($user);

				if ($state) {
					// need to update the activation_reminder_sent flag
					$this->updateActivationReminderSentFlag($user->id, '1');

					$count++;
				}
			}

		}

		return $count;
	}

	/**
	 * Retrieves the total number of users that submitted for verification requests
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getVerificationCount()
	{
		$db = $this->db;
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM `#__social_verification_requests`';
		$query[] = 'WHERE `type`=' . $db->Quote(SOCIAL_TYPE_USER);
		$query[] = 'AND `state` = '. $db->Quote(ES_VERIFICATION_REQUEST);

		$sql->raw($query);
		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}

	/**
	 * Method to assign default profile type to user.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function assignDefaultProfile($userId)
	{
		$db = $this->db;

		$pm = ES::table('ProfileMap');
		$pm->loadByUser($userId);

		if ($pm->profile_id) {
			return $pm->profile_id;
		}

		$query = "select `id` from `#__social_profiles`";
		$query .= " where `default` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= " and `state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$db->setQuery($query);
		$id = $db->loadResult();

		$state = false;
		if ($id) {

			$pm->profile_id = $id;
			$pm->user_id = $userId;
			$pm->state = SOCIAL_STATE_PUBLISHED;
			$pm->created = ES::date()->toSql();

			$state = $pm->store();
		}

		if (!$state) {
			return false;
		}

		return $id;
	}

	/**
	 * Retrieves user custom fields data for GDPR
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getProfileDataGDPR($user, $options = array())
	{
		// Load admin language files
		ES::language()->loadAdmin();

		// Get a list of steps
		$model = ES::model('Steps');
		$steps = $model->getSteps($user->getProfile()->getWorkflow()->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_DISPLAY, $options);

		// Load up the fields library
		$fieldsLib = ES::fields();
		$fieldsModel = ES::model('Fields');

		// exclude some of the fields do not need to display on the page
		$exclusionFields = $this->excludeGDPRProfileFields();

		foreach ($steps as $step) {

			// Get a list of fields from the current tab
			$stepOptions = array('step_id' => $step->id,
								 'data' => true,
								 'dataId' => $user->id,
								 'dataType' => SOCIAL_TYPE_USER,
								 'visible' => null,
								 'exclusion' => $exclusionFields
								);

			$step->fields = $fieldsModel->getCustomFields($stepOptions);

			// Trigger each fields available on the step
			if (!empty($step->fields)) {
				$args = array($user);

				// trigger the app who has this method
				$formattedResults = $fieldsLib->trigger('onGDPRExport', SOCIAL_FIELDS_GROUP_USER, $step->fields, $args);

				// Ensure this formatted value return an array
				if ($formattedResults !== false && count($formattedResults) > 0) {

					// retrieve the field id and value
					foreach ($formattedResults as $inputName => $formattedResult) {

						// retrieve the formatted value from the field
						$formattedDataId = isset($formattedResult->fieldId) && $formattedResult->fieldId ? $formattedResult->fieldId : '';
						$formattedDataValue = isset($formattedResult->value) && $formattedResult->value ? $formattedResult->value : '';

						// some of the fields itself already show the correct value without beautify it
						$fieldDataValue = !empty($formattedResult) ? $formattedResult : '';
						$fieldIdEmptyValue = '';

						// if either one also empty value, mean this field doesn't have formatted value
						if (!$fieldDataValue && (!$formattedDataId || !$formattedDataValue)) {

							// try to get the field id from the inputname e.g. es-fields-XXX
							$fieldIdEmptyValue = substr($inputName, 10);
						}

						foreach ($step->fields as $key => $fields) {

							// assign back formatted value into the raw data
							if ($fields->id == $formattedDataId) {
								$fields->data = $formattedDataValue;
							}

							// unset the field if the field doesn't have return any value
							if ($fieldIdEmptyValue && ($fields->id == $fieldIdEmptyValue)) {
								unset($step->fields[$key]);
							}
						}
					}
				}
			}
		}

		return $steps;
	}

	/**
	 * Retrieves user custom fields data for GDPR
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function excludeGDPRProfileFields()
	{
		$excludeFields = array(	'header',
								'headline',
								'separator',
								'acymailing',
								'file',
								'currency',
								'text',
								'mollom',
								'html',
								'vmvendor',
								'mailchimp',
								'code_generator',
								'terms',
								'joomla_username',
								'joomla_password',
								'joomla_user_editor',
								'joomla_language',
								'joomla_twofactor',
								'invitation_code',
								'recaptcha'
								);

		return $excludeFields;
	}

	/**
	 * Convert the creator of the cluster albums which created by this user to the cluster owner
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function updateClusterAlbumsCreator($userId)
	{
		$model = ES::model('Albums');
		$model->updateClusterAlbumsCreator($userId);
	}


	/**
	 * Method to update the user profile index robots data
	 *
	 * @since	3.2.2
	 * @access	public
	 */
	public function updateUserRobots($profileId, $robots)
	{
		// Skip this if the params do not have any robots data
		if (!$profileId || !$robots) {
			return;
		}

		$db = ES::db();

		$query = "UPDATE `#__social_users` as u";
		$query .= " INNER JOIN `#__social_profiles_maps` as pm on u.`user_id` = pm.`user_id`";
		$query .= " SET u.`robots` = " . $db->Quote($robots);
		$query .= " WHERE pm.`profile_id` = " . $db->Quote($profileId);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}


	/**
	 * Retrieves a list of user data used by user module.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getModuleUsers($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get current user logged in user
		$my = ES::user();

		$followersOnly = $this->normalize($options, 'followersOnly', false);
		$followingOnly = $this->normalize($options, 'followingOnly', false);
		$friendOnly = isset($options['friendOnly']) ? $options['friendOnly'] : false;
		$ignoreESAD = isset($options['ignoreESAD']) ? $options['ignoreESAD'] : false;
		$isOrderConnection 	= isset($options['ordering']) && $options['ordering'] == 'connectionDate' ? true : false;

		// Determines if we should exclude this profile type user on the user listing page.
		$excludeUserListing = isset($options['excludeUserListing']) ? $options['excludeUserListing'] : false;

		// this flag used to determine if the caller is from module. If yes,
		// we might not require all information such as user's points and etc
		$isModule = isset($options['isModule']) ? $options['isModule'] : false;

		$targettedUserId = isset($options['targettedUserId']) ? $options['targettedUserId'] : $my->id;


		// Determines if we want to filter by logged in users.
		$login = isset($options['login']) ? $options['login'] : '';

		// Determine if only to fetch front end
		$frontend = isset($options['frontend']) ? $options['frontend'] : '';

		// flag to determine if we need to join with points table or not.
		$joinPoints = ($isModule) ? false : true;

		$query = "select a.`id`";

		if ($login ) {
			$query = "select distinct a.`id`";
		}

		$query .= " FROM `#__users` as a";
		$query .= " INNER JOIN `#__social_users` as b on a.`id` = b.`user_id`";

		if ($login) {
			$query .= " INNER JOIN `#__session` AS f ON a.`id` = f.`userid`";

			if ($frontend) {
				$query .= " AND f.`client_id` = 0";

				// Check to retrieve only online users on the site.
				$lifespan = ES::jconfig()->getValue('lifetime');
				$online = time() - ($lifespan * 60);

				$query .= " AND f.`time` >= " . $db->Quote($online);
			}
		}


		if ($friendOnly) {
			$query .= " INNER JOIN `#__social_friends` as ff on a.`id` = if(ff.`target_id` = " . $db->Quote($targettedUserId). ", ff.`actor_id`, ff.`target_id`)";

		} else if ($isOrderConnection) {

			$query .= " LEFT JOIN `#__social_friends` as ff on a.`id` = if(ff.`target_id` = " . $db->Quote($targettedUserId). ", ff.`actor_id`, ff.`target_id`)";
		}


		if ((!$ignoreESAD || $excludeUserListing) && !$my->isSiteAdmin()) {
			$query .= " INNER JOIN `#__social_profiles_maps` as upm on a.`id` = upm.`user_id`";
			$query .= " INNER JOIN `#__social_profiles` as up on upm.`profile_id` = up.`id`";

			if (!$ignoreESAD) {
				$query .= " AND up.`community_access` = 1";
			}

			if ($excludeUserListing) {
				$query .= " AND up.`exclude_userlisting` = 0";
			}
		}

		// Determines if there's filter by profile id.
		$profile = $this->normalize($options, 'profile');

		if (is_null($profile)) {
			$profile = $this->getState('profile');
		}

		if ($profile && $profile != -1) {
			// Join with the social profiles table
			$query .= " INNER JOIN `#__social_profiles_maps` as e on e.`user_id` = a.`id`";
		}

		$excludeBlocked = $this->normalize($options, 'excludeblocked', 0);
		$blockedUser = $this->normalize($options, 'blocked', 0);


		if (!$blockedUser && ES::isBlockEnabled($my) && $excludeBlocked) {

			$query .= " LEFT JOIN `#__social_block_users` as bus";
			$query .= " on (a.`id` = bus.`user_id` AND bus.`target_id` = " . $db->Quote($targettedUserId);
			$query .= " OR a.`id` = bus.`target_id` AND bus.`user_id` = " . $db->Quote($targettedUserId) . ')';
		}

		// get only users that blocked by current logged in user.
		if ($blockedUser && ES::isBlockEnabled($my, false)) {
			$query .= " INNER JOIN `#__social_block_users` as bus2";
			$query .= " ON a.`id` = bus2.`target_id` AND bus2.`user_id` = " . $db->Quote($targettedUserId);
		}

		// Determines if we only get users with avatar photos
		$picture = isset($options['picture']) ? $options['picture'] : '';

		// Determines if we should only pick users with picture
		if ($picture) {

			// There is an instance where the user id is the same as cluster id. So, that is why we need to specify the avatar type.
			$query .= " INNER JOIN `#__social_avatars` as g ON g.`uid` = a.`id` and g.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and g.`small` != ''";
		}

		// wheres
		$wheres = array();

		if (!$blockedUser && ES::isBlockEnabled($my) && $excludeBlocked) {
			$wheres[] = "bus.`id` IS NULL";
		}

		// Determines if we should display admin's on this list.
		$includeAdmin 	= isset($options['includeAdmin']) ? $options['includeAdmin'] : null;

		// If caller doesn't want to include admin, we need to set the ignore list.
		if ($includeAdmin === false) {
			// Get a list of site administrators from the site.
			$admins = $this->getSiteAdmins(true);

			if ($admins) {
				$wheres[] = "a.id NOT IN (" . implode(',', $admins) . ")";
			}
		}

		// Determines if state filter is provided
		$state 	= isset($options['published']) ? $options['published'] : '';

		if ($state !== '') {
			$state	= $state == 1 ? SOCIAL_JOOMLA_USER_UNBLOCKED : SOCIAL_JOOMLA_USER_BLOCKED;
			$wheres[] = "a.`block` = " . $db->Quote($state);
		}

		if ($friendOnly) {
			$wheres[] = "(ff.`actor_id` = " . $db->Quote($targettedUserId) . " and ff.`state` = 1) OR (ff.`target_id` = " . $db->Quote($targettedUserId) . " and ff.`state` = 1)";
		}

		if ($profile && $profile != -1) {
			$profile = ES::makeArray($profile);
			$wheres[] = "e.`profile_id` IN (" . implode(',', $profile) . ")";
		}

		// Determines if we have an exclusion list.
		$exclusions = isset($options['exclusion']) ? $options['exclusion'] : '';

		if ($exclusions) {
			// Ensure that it's in an array
			$exclusions = ES::makeArray($exclusions);
			$wheres[] = "a.id NOT IN (" . implode(',', $exclusions) . ")";
		}

		// Determines if we have an inclusion list.
		$inclusion = isset($options['inclusion'])? $options['inclusion'] : '';

		if ($inclusion) {
			// Ensure that it's in an array
			$inclusion = ES::makeArray($inclusion);
			$wheres[] = "a.id IN (" . implode(',', $inclusion) . ")";
		}

		// lets check for the avatar validity here.
		if ($picture) {

			$tmp = "exists (select `photo_id` from `#__social_photos_meta` as pm where pm.`photo_id` = g.`photo_id`";
			$tmp .= " and pm.`group` = " . $db->Quote('path');
			$tmp .= " and pm.`property` = " . $db->Quote('original');
			$tmp .= ")";

			// $sql->exists($tmp);
			$wheres[] = $tmp;
		}

		$where = '';

		if (count($wheres) > 0) {
			$where = " WHERE ";
			$where .= (count($wheres) > 1) ? implode(' AND ', $wheres) : $wheres[0];
		}

		// glue the main query with where conditions.
		$query .= $where;


		// Determines if we need to order the items by column.
		$ordering 	= isset($options['ordering']) ? $options['ordering'] : '';

		// Ordering based on caller
		if ($ordering) {

			// order by last connected date.
			if ($isOrderConnection) {
				$ordering = 'ff.modified';
			}

			$direction 	= isset($options['direction']) ? $options['direction'] : '';

			if (! $isOrderConnection) {
				// if we order by last connected date, thats mean we are doing it from module.
				// if that is the case, do not store the ordering. if we do, it will
				// cause sql error in getUserMeta as this function using the same ordering.
				$this->ordering = array('ordering' => $ordering, 'direction' => $direction);
			}

			// check if ordering is on username. if yes, we need to add thr table prefix
			// to prevent ambiguous issue because we might join with session table.
			if ($ordering == 'username') {
				$ordering = 'a.'.$db->nameQuote($ordering);
			}

			$query .= " ORDER BY " . $ordering . " " . $direction;
		}

		$limit 	= isset($options['limit']) ? $options['limit'] : '';

		if ($limit) {
			// since we know module do not require paginatin, lets embed the limit into the sql.
			$query .= " LIMIT " . $limit;
		}

		$db->setQuery($query);
		$users = $db->loadObjectList();

		return $users;
	}

	/**
	 * Get Pending users based on specify duration.
	 *
	 * @since  1.4
	 * @access public
	 */
	public function getInactiveUsersByDuration($duration, $limit = 20)
	{
		$db = ES::db();
		$sql = $db->sql();

		$now = ES::date();

		$query = "select a.`id` from `#__users` as a";
		$query .= " inner join `#__social_users` as b on a.`id` = b.`user_id`";
		$query .= " where b.`state` = ". SOCIAL_REGISTER_VERIFY;
		$query .= " and a.`block` = 1";
		$query .= " and a.`registerDate` != " . $db->Quote('00-00-00 00:00:00');
		$query .= " and a.`registerDate` <= date_sub(" . $db->Quote($now->toMySQL()) . ", INTERVAL $duration DAY)";
		$query .= " and b.`activation_reminder_sent` = 0";
		$query .= " limit $limit";

		$sql->raw($query);
		$db->setQuery($sql);

		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Get Pending users based on specify duration.
	 *
	 * @since  1.4
	 * @access public
	 */
	public function removeRestSessionLogs($userId = null)
	{
		$db = ES::db();

		$q = [];

		$q[] = 'delete a from `#__social_restapi_login` as a';
		if ($userId) {
			$q[] = 'where a.`user_id` = ' . $db->Quote($userId);
		} else {
			$q[] = 'left join `#__session` as b on a.`session_id` = b.`session_id`';
			$q[] = 'where b.`session_id` is null';
		}

		$query = implode(' ', $q);

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
