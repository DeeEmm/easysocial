<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelPrivacy extends EasySocialModel
{
	private $data = null;

	static $_privacyitems = array();

	public function __construct($config = array())
	{
		parent::__construct('privacy', $config);
	}

	/**
	 * Get privacy id based on the type and rules
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPrivacyId($type, $rule, $useDefault = false)
	{
		$db = ES::db();

		$query = 'select ' . $db->nameQuote('id') . ' from ' . $db->nameQuote('#__social_privacy');
		$query .= ' where ' . $db->nameQuote('type') . ' = ' . $db->Quote($type);
		$query .= ' and ' . $db->nameQuote('rule') . ' = ' . $db->Quote($rule);

		$db->setQuery($query);
		$result = $db->loadResult();

		if (empty($result) && $useDefault) {
			$query = 'select ' . $db->nameQuote('id') . ' from ' . $db->nameQuote('#__social_privacy');
			$query .= ' where ' . $db->nameQuote('type') . ' = ' . $db->Quote('core');
			$query .= ' and ' . $db->nameQuote('rule') . ' = ' . $db->Quote('view');

			$db->setQuery($query);
			$result = $db->loadResult();
		}

		return $result;
	}


	/**
	 * Updates the privacy of an object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function updatePrivacy($uid, $data, $type = SOCIAL_PRIVACY_TYPE_USER)
	{
		$db  = ES::db();
		$sql = ES::sql();

		if (count($data) <= 0) {
			return false;
		}

		foreach ($data as $item) {
			$tbl = ES::table('PrivacyMap');

			$valueInInt = '';

			if ($item->mapid) {
				$tbl->load($item->mapid);
			}

			$tbl->privacy_id = $item->id;
			$tbl->uid = $uid;
			$tbl->utype = $type;
			$tbl->value = ES::privacy()->toValue($item->value);
			$valueInInt  = $tbl->value;

			// we only want to update the params for profile's privacy
			if ($type == SOCIAL_PRIVACY_TYPE_PROFILES && isset($item->params) && $item->params) {
				$tbl->params = $item->params;
			}

			$state = $tbl->store();

			if (!$state) {
				return $tbl->getError();
			}


			// reset sql object.
			$sql->clear();

			//clear the existing customized privacy data.
			$sql->delete('#__social_privacy_customize');
			$sql->where('uid', $tbl->id);
			$sql->where('utype', SOCIAL_PRIVACY_TYPE_USER);

			$db->setQuery($sql);
			$db->query();

			// save custom users here.
			if ($tbl->value == SOCIAL_PRIVACY_CUSTOM && count($item->custom) > 0) {
				foreach ($item->custom as $customUserId) {
					if (empty($customUserId)) {
						continue;
					}

					$tblCustom = ES::table('PrivacyCustom');
					$tblCustom->uid = $tbl->id;
					$tblCustom->utype = SOCIAL_PRIVACY_TYPE_USER;
					$tblCustom->user_id = $customUserId;
					$tblCustom->store();
				}
			}

			// lets check if we need to reset the privacy_items or not.
			// we can do either delete or updates. delete seems more clean.
			if (isset($item->reset) && $item->reset && $type == SOCIAL_PRIVACY_TYPE_USER) {
				// delete user's  non-fields privacy item. e.g. photos, story updates and etc
				$query = 'delete from `#__social_privacy_items`';
				$query .= ' where `privacy_id` = ' . $db->Quote($item->id);
				$query .= ' and `user_id` = ' . $db->Quote($uid);
				$query .= ' and `type` != ' . $db->Quote(SOCIAL_TYPE_FIELD);

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();

				// now we need to update user's fields privacy.
				$updateQuery = "update `#__social_privacy_items` set `value` = " . $db->Quote($valueInInt);
				$updateQuery .= ' where `privacy_id` = ' . $db->Quote($item->id);
				$updateQuery .= ' and `user_id` = ' . $db->Quote($uid);
				$updateQuery .= ' and `type` = ' . $db->Quote(SOCIAL_TYPE_FIELD);
				$sql->clear();
				$sql->raw($updateQuery);
				$db->setQuery($sql);
				$db->query();


				// need to update stream for related privacy items.
				$isPublic 	= ($valueInInt == SOCIAL_PRIVACY_PUBLIC) ? 1 : 0;

				$updateQuery = 'update `#__social_stream` set `ispublic` = ' . $db->Quote($isPublic);
				$updateQuery .= ' ,`access` = ' . $db->Quote($valueInInt);
				$updateQuery .= ' where `actor_id` = ' . $db->Quote($uid) . ' and `privacy_id` = ' . $db->Quote($item->id) ;

				$sql->clear();
				$sql->raw($updateQuery);
				$db->setQuery($sql);
				$db->query();

			} else if(isset($item->reset) && $item->reset && $type == SOCIAL_PRIVACY_TYPE_PROFILES) {

				$commandSQL = 'select `user_id` from `#__social_profiles_maps` where `profile_id` = ' . $db->Quote($uid);

				// uid == profile id.
				// we need to update user's privacy setting as well for this profile.
				$updateQuery = 'update `#__social_privacy_map` set `value` = ' . $db->Quote($valueInInt);
				$updateQuery .= ' where `privacy_id` = ' . $db->Quote($item->id);
				$updateQuery .= ' and `uid` IN ('. $commandSQL .')';
				$updateQuery .= ' and `utype` = ' . $db->Quote('user');

				// echo $updateQuery;
				// echo '<br>';

				$sql->clear();
				$sql->raw($updateQuery);
				$db->setQuery($sql);
				$db->query();


				// now lets clear the privacy for items.
				$query = 'delete from `#__social_privacy_items`';
				$query .= ' where `privacy_id` = ' . $db->Quote($item->id);
				$query .= ' and `user_id` IN (' . $commandSQL . ')';
				$query .= ' and `type` != ' . $db->Quote(SOCIAL_TYPE_FIELD);

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();


				// now we need to update user's fields privacy.
				$updateQuery = "update `#__social_privacy_items` set `value` = " . $db->Quote($valueInInt);
				$updateQuery .= ' where `privacy_id` = ' . $db->Quote($item->id);
				$updateQuery .= ' and `user_id` IN (' . $commandSQL . ')';
				$updateQuery .= ' and `type` = ' . $db->Quote(SOCIAL_TYPE_FIELD);
				$sql->clear();
				$sql->raw($updateQuery);
				$db->setQuery($sql);
				$db->query();

				// need to update stream for related privacy items.
				$isPublic 	= ($valueInInt == SOCIAL_PRIVACY_PUBLIC) ? 1 : 0;

				$updateQuery = 'update `#__social_stream` set `ispublic` = ' . $db->Quote($isPublic);
				$updateQuery .= ' ,`access` = ' . $db->Quote($valueInInt);
				$updateQuery .= ' where `actor_id` IN (' . $commandSQL . ')';
				$updateQuery .= ' and `privacy_id` = ' . $db->Quote($item->id);

				$sql->clear();
				$sql->raw($updateQuery);

				$db->setQuery($sql);
				$db->query();
			}


			$mediaAccesses = $this->getSupportedMediaAccess();

			if (isset($item->reset) && $item->reset && array_key_exists($item->id, $mediaAccesses)) {
				$mediaPrivacy = $mediaAccesses[$item->id];
				$PrivacyType = $mediaPrivacy->type;

				$tableName = '#__social_' . $PrivacyType;

				$customs = array();
				foreach ($item->custom as $customUserId) {
					if (empty($customUserId)) {
						continue;
					}
					$customs[] = $customUserId;
				}

				$customPrivacy = $customs ? ',' . implode($customs) . ',' : '';

				$query = "update " . $db->nameQuote($tableName) . " as a";

				if ($type == SOCIAL_PRIVACY_TYPE_PROFILES) {
					$query .= " INNER JOIN `#__social_profiles_maps` as b on a.`user_id` = b.`user_id`";
				}

				$query .= " SET a.`access` = " . $db->Quote($valueInInt);
				$query .= ", a.`field_access` = 0";
				$query .= ", a.`custom_access` = " . $db->Quote($customPrivacy);
				$query .= " where a.`type` = " . $db->Quote('user');

				if ($type == SOCIAL_PRIVACY_TYPE_USER) {
					$query .= " and a.`user_id` = " . $uid;
				}

				if ($type == SOCIAL_PRIVACY_TYPE_PROFILES) {
					$query .= " and b.`profile_id` = " . $uid;
				}

				if ($PrivacyType == 'albums') {
					$query .= " and a.`core` IN (0, 3)";
				}

				$db->setQuery($query);
				$db->query();

			}


		}

		return true;
	}

	/**
	 * Preload user privacy for later access
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function getSupportedMediaAccess()
	{
		static $privacies = null;

		if (is_null($privacies)) {

			$db = ES::db();

			$query = "select * from `#__social_privacy` where `type` IN ('photos', 'albums', 'videos', 'audios') and `rule` = 'view'";

			$db->setQuery($query);

			$privacies = $db->loadObjectList('id');
		}

		return $privacies;
	}

	/**
	 * Preload user privacy for later access
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preloadUserPrivacy($userIds)
	{
		$db = ES::db();

		$type = SOCIAL_PRIVACY_TYPE_USER;

		// Render items that are stored in the database.
		$query = 'select a.' . $db->nameQuote('type') . ', a.' . $db->nameQuote('rule') . ', b.' . $db->nameQuote('value') . ',';
		$query .= ' a.' . $db->nameQuote('id') . ', b.' . $db->nameQuote('id') . ' as ' . $db->nameQuote('mapid') . ', b.' . $db->nameQuote('uid');
		$query .= ' from ' . $db->nameQuote('#__social_privacy') . ' as a';
		$query .= '	inner join ' . $db->nameQuote('#__social_privacy_map') . ' as b on a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('privacy_id');
		$query .= ' where b.' . $db->nameQuote('uid') . ' IN (' . implode(",", $userIds) . ')';
		$query .= ' and b.' . $db->nameQuote('utype') . ' = ' . $db->Quote($type);
		$query .= ' and a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query .= ' order by a.' . $db->nameQuote('type');

		$db->setQuery($query);

		$results = $db->loadObjectList();

		$items = array();

		// prefill default array.
		foreach($userIds as $uid) {
			$items[$uid] = array();
		}

		if ($results) {
			foreach($results as $item) {
				$items[$item->uid][] = $item;
			}
		}

		return $items;
	}


	/**
	 * Preload fields privacy for later access
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function preloadFieldsPrivacy($userId, $fieldIds)
	{
		$db = ES::db();

		$type = SOCIAL_PRIVACY_TYPE_USER;

		$query = "select a.`id`, a.`value` as `default`, a.`options`, b.`user_id`, b.`uid`, b.`type`, b.`value`,b.`id` as `pid`";
		$query .= " from `#__social_privacy` as a";
		$query .= "  inner join `#__social_privacy_items` as b";
		$query .= "  on a.`id` = b.`privacy_id`";
		$query .= "  where b.`user_id` = " . $db->Quote($userId);
		$query .= " and b.`uid` IN (" . implode(",", $fieldIds) . ")";
		$query .= " and b.`type` IN ('field', 'year', 'birthday.year')";
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$db->setQuery($query);

		$results = $db->loadObjectList();

		$items = array();

		// prefill default array.
		foreach($fieldIds as $uid) {
			$items[$uid]['field'] = array();
			$items[$uid]['year'] = array();
			$items[$uid]['birthday.year'] = array();
		}

		if ($results) {
			foreach($results as $item) {
				$items[$item->uid][$item->type] = $item;
			}
		}

		return $items;
	}


	/**
	 * Responsible to retrieve the data for a privacy item.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getData($id, $type = SOCIAL_PRIVACY_TYPE_USER)
	{
		static $_cache = array();

		$cacheIdx = $id . $type;

		if (isset($_cache[$cacheIdx])) {
			return $_cache[$cacheIdx];
		}


		$db = ES::db();

		$item = array();

		// Render default acl items from manifest file.
		$defaultItems = $this->getDefaultPrivacy($id, $type);
		$loadDB = true;

		$result = array();

		if ($type == SOCIAL_PRIVACY_TYPE_USER) {
			if (ES::cache()->exists('user.privacy.' . $id)) {
				$loadDB = false;
				$result = ES::cache()->get('user.privacy.' . $id);
			}
		}

		if ($loadDB) {
			// Render items that are stored in the database.
			$query = 'select a.' . $db->nameQuote('type') . ', a.' . $db->nameQuote('rule') . ', b.' . $db->nameQuote('value') . ',';
			$query .= ' a.' . $db->nameQuote('id') . ', b.' . $db->nameQuote('id') . ' as ' . $db->nameQuote('mapid');
			$query .= ' , b.' . $db->nameQuote('params');
			$query .= ' from ' . $db->nameQuote('#__social_privacy') . ' as a';
			$query .= '	inner join ' . $db->nameQuote('#__social_privacy_map') . ' as b on a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('privacy_id');
			$query .= ' where b.' . $db->nameQuote('uid') . ' = ' . $db->Quote($id);
			$query .= ' and b.' . $db->nameQuote('utype') . ' = ' . $db->Quote($type);
			$query .= ' and a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
			$query .= ' order by a.' . $db->nameQuote('type');

			// echo $query;exit;

			$db->setQuery($query);

			$result = $db->loadObjectList();
		}

		// If there's nothing stored into the database, we just return the default values.
		if (!$result) {
			$_cache[$cacheIdx] = $defaultItems;
			return $defaultItems;
		}

		// If there's values stored in the database, map the values back.
		foreach ($result as $row) {
			$row->type  = strtolower($row->type);
			$group 		= $row->type;

			$obj = new stdClass();

			$obj->type = (string) $row->type;
			$obj->rule = (string) $row->rule;
			$obj->id = $row->id;
			$obj->mapid = $row->mapid;
			$obj->default = $row->value;

			if (isset($defaultItems[$group])) {
				$defaultGroup = $defaultItems[$group];

				foreach ($defaultGroup as $rule) {
					if ($rule->type == $row->type && $rule->rule == $row->rule) {
						$rule->options = is_array($rule->options) ? $rule->options : (array) $rule->options;
						$optionKeys 	= array_keys($rule->options);
						$defaultOptions = array_fill_keys($optionKeys, '0');

						$key = constant('SOCIAL_PRIVACY_' . $row->value);

						$defaultOptions[$key] = '1';

						$obj->options = $defaultOptions;

						break;
					}
				}
			}

			$obj->field = '';
			$obj->custom = '';

			//get the customized user listing if there is any
			if ($row->value == SOCIAL_PRIVACY_CUSTOM) {
				$obj->custom = $this->getPrivacyCustom($row->mapid, SOCIAL_PRIVACY_TYPE_USER);
			}

			if (isset($row->params) && $row->params) {
				$obj->field = ES::json()->decode($row->params);
			}


			$defaultItems[$group][$obj->rule] = $obj;
		}

		$_cache[$cacheIdx] = $defaultItems;

		return $defaultItems;
	}


	/**
	 * Get default privacy
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDefaultPrivacy($id, $type = SOCIAL_PRIVACY_TYPE_USER)
	{
		static $_cache = array();

		$db = ES::db();
		$sql = $db->sql();

		$result = array();

		if ($type == SOCIAL_PRIVACY_TYPE_USER) {
			// lets try to get from user profile privacy 1st.
			$user = ES::user($id);
			$profile_id = $user->get('profile_id');

			$result = array();
			if (isset($_cache[$profile_id])) {

				$result =  $_cache[$profile_id];

			} else {

				$query = 'select a.`id`, a.`type`, a.`rule`, a.`options`, b.`value`, b.`params`';
				$query .= ' from ' . $db->nameQuote('#__social_privacy') . ' as a';
				$query .= '	inner join ' . $db->nameQuote('#__social_privacy_map') . ' as b on a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('privacy_id');
				$query .= ' where b.' . $db->nameQuote('uid') . ' = ' . $db->Quote($profile_id);
				$query .= ' and b.' . $db->nameQuote('utype') . ' = ' . $db->Quote(SOCIAL_PRIVACY_TYPE_PROFILES);
				$query .= ' and a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
				$query .= ' order by a.' . $db->nameQuote('type');

				$sql->raw($query);

				$db->setQuery($sql);
				$result = $db->loadObjectList();

				$_cache[$profile_id] = $result;
			}
		}

		if (!$result) {
			$query = 'select * from ' . $db->nameQuote('#__social_privacy');
			$query .= ' where ' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
			$query .= ' order by ' . $db->nameQuote('type');
			$db->setQuery($query);

			$result = $db->loadObjectList();
		}

		$items = array();

		foreach ($result as $item) {

			$obj = new stdClass();
			$obj->id = $item->id;
			$obj->mapid = '0';
			$obj->type = $item->type;
			$obj->rule = $item->rule;
			$obj->default = $item->value;
			$obj->options = array();

			$default = ES::call('Privacy', 'toKey', $item->value);
			$options = ES::json()->decode($item->options);

			foreach($options->options as $key => $option) {
				$obj->options[$option] = ($default == $option) ? '1' : '0';
			}

			$obj->custom = null;
			$obj->field = null;

			if (isset($item->params) && $item->params) {
				$obj->field = ES::json()->decode($item->params);
			}

			$items[$item->type][$item->rule]	= $obj;
		}

		// Sort the items
		krsort($items);

		return $items;
	}

	/**
	 * Responsible to add / upate user privacy on an object
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function update($userId, $pid, $uId, $uType, $value, $custom = '', $field = '')
	{
		// lets check if this user already has the record or not.
		// if not, we will add it here.
		// if exists, we will update the record.

		$db = ES::db();

		// check if user selected custom but there is no userids, then we do not do anything.
		if ($value == 'custom' && empty($custom)) {
			return false;
		}

		// check if user selected custom fields but there is no values, then we do not do anything.
		if ($value == 'field' && empty($field)) {
			return false;
		}

		$query = 'select * from `#__social_privacy_items`';
		// $query .= ' where `user_id` = ' . $db->Quote($userId);
		$query .= ' where `uid` = ' . $db->Quote($uId);
		$query .= ' and `type` = ' . $db->Quote($uType);

		if ($uType == 'field' || $uType == 'birthday.year') {
			// when the utyoe is field OR birthday.year, we can always assume only the onwer is updating the item privacy.
			$query .= ' and `user_id` = ' . $db->Quote($userId);
		}

		$db->setQuery($query);

		$result = $db->loadResult();

		$tbl = ES::table('PrivacyItems');

		if ($result) {
			$tbl->load($result);

			// make sure the current user is the onwer of this privacy item.
			if ($tbl->user_id != $userId) {
				$nUser = ES::user($userId);
				if (!$nUser->isSiteAdmin()) {
					return false;
				}
			}
		}

		$privacy = ES::privacy($userId);
		$valueInInt = $privacy->toValue($value);

		if ($result) {
			// record exist. update here.
			// $tbl->load($result);

			$tbl->value = $valueInInt;

		} else {
			// record not found. add new here.
			$tbl->user_id = $userId;
			$tbl->privacy_id = $pid;
			$tbl->uid = $uId;
			$tbl->type = $uType;
			$tbl->value = $valueInInt;
		}

		if (!$tbl->store()) {
			return false;
		}

		//clear the existing customized privacy data.
		$sql = ES::sql();

		$sql->delete('#__social_privacy_customize');
		$sql->where('uid', $tbl->id);
		$sql->where('utype', SOCIAL_PRIVACY_TYPE_ITEM);

		$db->setQuery($sql);
		$db->query();

		// if there is custom userids.
		if ($value == 'custom' && !empty($custom)) {
			$customList = explode(',', $custom);

			for ($i = 0; $i < count($customList); $i++) {
				$customUserId = $customList[$i];

				if (empty($customUserId)) {
					continue;
				}

				$tblCustom = ES::table('PrivacyCustom');

				$tblCustom->uid = $tbl->id;
				$tblCustom->utype = SOCIAL_PRIVACY_TYPE_ITEM;
				$tblCustom->user_id = $customUserId;
				$tblCustom->store();
			}
		}

		// clear the existing custom fields privacy data.
		$sql->clear();
		$sql->delete('#__social_privacy_field');
		$sql->where('uid', $tbl->id);
		$sql->where('utype', SOCIAL_PRIVACY_TYPE_ITEM);

		$db->setQuery($sql);
		$db->query();

		// clear the existing custom fields in privacy items.
		$sql->clear();
		$sql->delete('#__social_privacy_items_field');
		$sql->where('uid', $tbl->id);
		$sql->where('utype', SOCIAL_PRIVACY_TYPE_ITEM);

		$db->setQuery($sql);
		$db->query();

		// if there is custom field values.
		if ($value == 'field' && $field) {
			$customfields = explode(';', $field);

			$totalFieldCount = 0;
			for ($i = 0; $i < count($customfields); $i++) {
				$item = $customfields[$i];

				if (empty($item)) {
					continue;
				}

				// lets further explode based on '|'
				$data = explode('|', $item);

				if (!isset($data[2]) || !$data[2]) {
					// empty value. do not do anything.
					continue;
				}

				$totalFieldCount++;

				$fieldvalue = array_pop($data);
				$fieldkey = implode('|', $data);

				// for privacy items field table
				$uniquekey = $data[1];
				$fieldValues = explode(',', $fieldvalue);

				// insert into privacy field table
				$tblCustom = ES::table('PrivacyField');
				$tblCustom->uid = $tbl->id;
				$tblCustom->utype = SOCIAL_PRIVACY_TYPE_ITEM;
				$tblCustom->field_key = $fieldkey;
				$tblCustom->field_value = $fieldvalue;
				$tblCustom->store();

				// insert into privacy items field table
				foreach ($fieldValues as $val) {
					$itemField = ES::table('PrivacyItemsField');
					$itemField->uid = $tbl->id;
					$itemField->utype = SOCIAL_PRIVACY_TYPE_ITEM;
					$itemField->unique_key = $uniquekey;
					$itemField->value = $val;
					$itemField->store();
				}
			}

			// update field_access
			$tbl->field_access = $totalFieldCount;
			$tbl->store();
		}

		// need to update the stream's ispublic flag.
		if ($uType != SOCIAL_TYPE_FIELD) {
			$context = $uType;
			$column = 'context_id';
			$updateId = $uId;
			$isPublic = ($valueInInt == SOCIAL_PRIVACY_PUBLIC) ? 1 : 0;

			$updateQuery = 'update #__social_stream set ispublic = ' . $db->Quote($isPublic);


			switch ($context) {
				case SOCIAL_TYPE_ACTIVITY:
					$updateQuery .= ' where `id` = (select `uid` from `#__social_stream_item` where `id` = ' . $db->Quote($uId) . ')';
					break;
				case SOCIAL_TYPE_STORY:
				case SOCIAL_TYPE_LINKS:
					$updateQuery .= ' where `id` = ' . $db->Quote($uId);
					break;

				default:
					$updateQuery .= ' where `id` IN (select `uid` from `#__social_stream_item` where `context_type` = ' . $db->Quote($context) . ' and `context_id` = ' . $db->Quote($uId) . ')';
					break;
			}

			$sql->clear();
			$sql->raw($updateQuery);
			$db->setQuery($sql);
			$db->query();
		}

		// lets trigger the onPrivacyChange event here so that apps can handle their items accordingly.
		$obj = new stdClass();
		$obj->user_id = $userId;
		$obj->privacy_id = $pid;
		$obj->uid = $uId;
		$obj->utype = $uType;
		$obj->value = $valueInInt;
		$obj->custom = $custom;
		$obj->field = $field;

		// Get apps library.
		$apps = ES::getInstance('Apps');

		// Try to load user apps
		$state = $apps->load(SOCIAL_APPS_GROUP_USER);
		if ($state) {
			// Only go through dispatcher when there is some apps loaded, otherwise it's pointless.
			$dispatcher = ES::dispatcher();

			// Pass arguments by reference.
			$args = array($obj);

			// @trigger: onPrepareStream for the specific context
			$result = $dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onPrivacyChange', $args, $uType);
		}

		return true;
	}

	/**
	 * Get custom privacy if available
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPrivacyCustom($pItemId, $type = SOCIAL_PRIVACY_TYPE_ITEM)
	{
		$db = ES::db();
		$sql = ES::sql();

		$sql->select('#__social_privacy_customize');
		$sql->column('user_id');
		$sql->where('uid', $pItemId, '=');
		$sql->where('utype', $type, '=');

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get item's privacy
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItem($uid, $type)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select * from `#__social_privacy_items`";
		$query .= " where `uid` = " . $db->Quote($uid);
		$query .= " and `type` = " . $db->Quote($type);
		$sql->raw($query);

		$db->setQuery($sql);
		$result = $db->loadObject();

		return $result;
	}


	/**
	 * Retrieves the privacy object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPrivacyItem($uid, $type, $ownerId, $command)
	{
		static $cached = array();

		// Build the index for cached item
		$index = $uid . $type . $ownerId . $command;
		$key = $uid . '.' . $type;

		if (isset($cached[$index])) {
			return $cached[$index];
		}

		$db = ES::db();

		// Default value
		$result = array();

		// Check if we already have a copy of the objects privacy
		if (!isset(self::$_privacyitems[$key]) && $uid) {

			$exists = false;
			$loadDB = true;

			$cacheTypes = array('field', 'year', 'birthday.year');
			if (in_array($type, $cacheTypes) && $ownerId) {

				if (ES::cache()->exists('field.privacy.' . $ownerId . '.' . $uid . '.' . $type)) {
					self::$_privacyitems[$key] = ES::cache()->get('field.privacy.' . $ownerId . '.' . $uid . '.' . $type);
					$loadDB = false;
				}
			}

			if ($loadDB) {

				$query = 'select a.' . $db->nameQuote('id') . ', a.' . $db->nameQuote('value') . ' as ' . $db->nameQuote('default') . ', a.' . $db->nameQuote('options') . ', ';
				$query .= 'b.' . $db->nameQuote('user_id') . ', b.' . $db->nameQuote('uid') . ', b.' . $db->nameQuote('type') . ', b.' . $db->nameQuote('value') . ',';
				$query .= 'b.' . $db->nameQuote('id') . ' as ' . $db->nameQuote('pid');
				$query .= ' from ' . $db->nameQuote('#__social_privacy') . ' as a';
				$query .= '		inner join ' . $db->nameQuote('#__social_privacy_items') . ' as b on a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('privacy_id');
				$query .= ' where b.' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid);
				$query .= ' and b.' . $db->nameQuote('type') . ' = ' . $db->Quote($type);
				$query .= ' and a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);

				if ($ownerId) {
					$query .= ' and b.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($ownerId);
				}

				$db->setQuery($query);

				self::$_privacyitems[$key] = $db->loadObject();
			}
		}

		if (isset(self::$_privacyitems[$key]) && self::$_privacyitems[$key]) {
			$result = clone(self::$_privacyitems[$key]);
		}

		// If we still can't find a result, then we need to load from the default items
		if (!$result || !isset($result->id)) {

			// Retrieve the core values
			$defaultValue = $this->getPrivacyDefaultValues($command, $ownerId);

			$result = clone($defaultValue);

			$result->uid = $uid;
			$result->type = $type;
			$result->user_id = $ownerId;
			$result->value = isset($result->default) ? $result->default : '';
			$result->pid = '0';

			self::$_privacyitems[$key] = $result;
		}

		// Normalize the options property.
		if (!isset($result->options)) {
			$result->options = '';
		}

		$default = ES::call('Privacy', 'toKey', $result->value);
		$options = json_decode($result->options);

		$result->option	= array();


		$hasFieldOption = false;

		// Set the default values
		if ($options) {
			foreach ($options->options as $key => $option) {
				$result->option[$option] = ($default == $option) ? '1' : '0';

				if ($option == SOCIAL_PRIVACY_200) {
					$hasFieldOption = true;
				}
			}
		}

		// Get the custom user id.
		$result->custom = array();

		if ($result->value == SOCIAL_PRIVACY_CUSTOM) {

			if ($result->pid) {
				$result->custom = $this->getPrivacyCustom($result->pid);

			} else if($result->mapid) {
				$result->custom = $this->getPrivacyCustom($result->mapid, SOCIAL_PRIVACY_TYPE_USER);
			}
		}

		// get the default custom fields list for this privacy rule.
		$result->field = array();

		if ($hasFieldOption) {
			$result->field = $this->getCustomFields($command, $result->user_id, $result->pid);

			// if there is no fields defined, remove the custom field selection.
			if (! $result->field) {
				unset($result->option['field']);
			}
		}

		// This is where we define whether the privacy item is editable or not.
		$my = ES::user();

		$result->editable = false;

		if ($result->user_id && $result->user_id == $my->id) {
			$result->editable = true;
		}

		$cached[$index]	= $result;

		return $cached[$index];
	}


	/**
	 * Retrieves the default values for the privacy item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPrivacyDefaultValues($command = null, $userId = null)
	{
		static $core 	= array();

		$command = !$command ? 'core.view' : $command;
		$index = $command . $userId;

		$data = explode('.', $command);
		$isFieldCommand = ($data[0] == 'field') ? true : false;

		$element = array_shift($data);
		$rule = implode('.', $data);

		if (isset($core[$index])) {
			return $core[$index];
		}

		$default = null;

		// If owner id is provided, try to get the owner's privacy object
		if ($userId) {
			$userPrivacy = $this->getPrivacyUserDefaultValues($userId, $isFieldCommand);

			if ($userPrivacy) {
				foreach ($userPrivacy as $item) {
					if ($item->type == $element && $item->rule == $rule) {
						$default = $item;
						break;
					}
				}
			}
		}

		$systemPrivacy = $this->getPrivacySystemDefaultValues();

		// If the default value is still null, try to search for default values from our own table
		if (!$default) {
			foreach ($systemPrivacy as $item) {
				if ($item->type == $element && $item->rule == $rule) {
					$default 	= $item;
					break;
				}
			}
		}

		// If we still can't find the default, then we just revert to the core.view privacy here.
		if (!$default) {
			foreach ($systemPrivacy as $defaultItem) {
				if ($defaultItem->type == 'core' && $defaultItem->rule == 'view') {
					$default = $defaultItem;
					break;
				}
			}


		}

		$core[$index]	= $default;

		return $core[$index];
	}

	/**
	 * Get default system privacy
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPrivacySystemDefaultValues()
	{
		static $system	= null;

		if ($system) {
			return $system;
		}

		$db = ES::db();
		$sql = $db->sql();

		// Try to get the privacy from the master table
		$query = array();
		$query[] = 'SELECT a.' . $db->nameQuote('type') . ', a.' . $db->nameQuote('rule') . ', a.' . $db->nameQuote('id') . ', a.' . $db->nameQuote('value') . ' AS ' . $db->nameQuote('default') . ', a.' . $db->nameQuote('options');
		$query[] = ', 0 as ' . $db->nameQuote('mapid');
		$query[] = 'FROM ' . $db->nameQuote('#__social_privacy') . ' AS a';
		$query[] = 'where a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$query = implode(' ', $query);

		$sql->raw($query);
		$db->setQuery($sql);

		$system = $db->loadObjectList();

		return $system;
	}

	/**
	 * Get default privacy for users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPrivacyUserDefaultValues($userId, $isFieldCommand = false)
	{
		static $users = array();

		$indexKey = $userId . '-' . $isFieldCommand;

		if (isset($users[$indexKey])) {
			return $users[$indexKey];
		}

		$db = ES::db();

		$query = 'select a.'.$db->nameQuote('type') . ', a.' . $db->nameQuote('rule') . ', a.' . $db->nameQuote('id') . ', b.' . $db->nameQuote('value') . ' as ' . $db->nameQuote('default') . ', a.' . $db->nameQuote('options');
		$query .= ', b.' . $db->nameQuote('id') . ' as ' . $db->nameQuote('mapid');
		$query .= ' from ' . $db->nameQuote('#__social_privacy') . ' as a';
		$query .= ' inner join ' . $db->nameQuote('#__social_privacy_map') . ' as b';
		$query .= ' ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('privacy_id');
		$query .= ' where b.' . $db->nameQuote('uid') . ' = ' . $db->Quote($userId);
		$query .= ' and b.' . $db->nameQuote('utype') . ' = ' . $db->Quote(SOCIAL_PRIVACY_TYPE_USER);
		$query .= ' and a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		if ($isFieldCommand) {
			$query .= ' and a.' . $db->nameQuote('type') . ' = ' . $db->Quote('field');
		} else {
			$query .= ' and a.' . $db->nameQuote('type') . ' != ' . $db->Quote('field');
		}

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			$currentUser = ES::user($userId);
			$profile_id = $currentUser->get('profile_id');

			$query = 'select a.'.$db->nameQuote('type') . ', a.' . $db->nameQuote('rule') . ', a.' . $db->nameQuote('id') . ', b.' . $db->nameQuote('value') . ' as ' . $db->nameQuote('default') . ', a.' . $db->nameQuote('options');
			$query .= ', 0 as ' . $db->nameQuote('mapid');
			$query .= ' from ' . $db->nameQuote('#__social_privacy') . ' as a';
			$query .= ' inner join ' . $db->nameQuote('#__social_privacy_map') . ' as b';
			$query .= ' ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('privacy_id');
			$query .= ' where b.' . $db->nameQuote('uid') . ' = ' . $db->Quote($profile_id);
			$query .= ' and b.' . $db->nameQuote('utype') . ' = ' . $db->Quote(SOCIAL_PRIVACY_TYPE_PROFILES);
			$query .= ' and a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);

			$db->setQuery($query);

			$result = $db->loadObjectList();
		}

		$users[$indexKey] = $result;

		return $users[$indexKey];
	}

	/**
	 * method used in backend to list down all the privacy items.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getList()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_privacy');

		$filter = $this->getUserStateFromRequest('filter', 'all');

		if ($filter && $filter != 'all') {
			if ($filter == 'field') {
				$sql->where('type', 'field');
			} else {
				$sql->where('type', 'field', '!=');
			}
		}

		// Determines if user wants to search for something
		$search = $this->getState('search');

		if ($search) {
			// $sql->where('type', $search, 'LIKE', 'OR');
			$sql->where('rule', '%'.$search.'%', 'LIKE', 'OR');
			$sql->where('description', '%'.$search.'%', 'LIKE', 'OR');
		}

		$ordering 	= $this->getState('ordering');

		if ($ordering) {
			$direction 	= $this->getState('direction');

			$sql->order($ordering, $direction);
		}

		$this->setTotal($sql->getTotalSql());

		$rows 	= parent::getData($sql->getSql());

		if (!$rows) {
			return false;
		}

		// We want to pass back a list of PointsTable object.
		$data 	= array();

		// Load the admin language file whenever there's points.
		JFactory::getLanguage()->load('com_easysocial', JPATH_ROOT . '/administrator');

		foreach ($rows as $row) {
			$privacy 	= ES::table('Privacy');
			$privacy->bind($row);

			$data[]	= $privacy;
		}

		return $data;

	}

	/**
	 * Scans through the given path and see if there are any privacy's rule files.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan($path)
	{
		jimport('joomla.filesystem.folder');

		$data = array();

		$directory = JPATH_ROOT . $path;
		$directories = JFolder::folders($directory, '.', true, true);

		foreach ($directories as $folder) {
			// just need to get one level folder.
			$files = JFolder::files($folder, '.privacy$', false, true);
			$data = array_merge($data, $files);
		}

		return $data;
	}

	/**
	 * Given a path to the file, install the privacy rules.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install($path)
	{
		// Import platform's file library.
		jimport('joomla.filesystem.file');

		// Read the contents
		$contents = file_get_contents($path);

		// If contents is empty, throw an error.
		if (empty($contents)) {
			$this->setError(JText::_('COM_EASYSOCIAL_PRIVACY_UNABLE_TO_READ_PRIVACY_RULE_FILE'));
			return false;
		}

		$json = ES::json();
		$data = $json->decode($contents);

		if (!is_array($data)) {
			$data = array($data);
		}

		// Let's test if there's data.
		if (empty($data)) {
			$this->setError(JText::_('COM_EASYSOCIAL_PRIVACY_UNABLE_TO_READ_PRIVACY_RULE_FILE'));
			return false;
		}

		$privLib = ES::privacy();
		$result = array();

		foreach ($data as $row) {
			$type = $row->type;
			$rules = $row->rules;

			if (count($rules) > 0) {
				foreach ($rules as $rule) {
					$command = $rule->command;
					$description = $rule->description;
					$default = $rule->default;
					$options = $rule->options;

					$optionsArr = array();
					foreach ($options as $option) {
						$optionsArr[] = $option->name;
					}

					$ruleOptions = array('options' => $optionsArr);
					$optionString = $json->encode($ruleOptions);

					// Load the tables
					$privacy = ES::table('Privacy');

					// If this already exists, we need to skip this.
					$state = $privacy->load(array('type' => $type, 'rule' => $command));

					if ($state) {
						continue;
					}

					$privacy->core = isset($rule->core) && $rule->core ? true : false;
					$privacy->state = SOCIAL_STATE_PUBLISHED;
					$privacy->type = $type;
					$privacy->rule = $command;
					$privacy->description = $description;
					$privacy->value = $privLib->toValue($default);
					$privacy->options = $optionString;

					$addState = $privacy->store();

					if ($addState) {
						// now we need to add this new privacy rule into all the profile types.
						$this->addRuleProfileTypes($privacy->id, $privacy->value);
					}

					$result[] = $type . '.' . $command;

				}
			}
		}

		return $result;
	}

	/**
	 * method used in backend to add rules for profile type
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function addRuleProfileTypes($privacyId, $default) {
		$db = ES::db();
		$sql = $db->sql();

		$query = "insert into `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`)";
		$query .= " select '$privacyId', `id`, 'profiles', '$default' from `#__social_profiles` as p where `id` not in (";
		$query .= " 	select distinct `uid` from `#__social_privacy_map` where `utype` = 'profiles' and `privacy_id` = $privacyId)";
		$query .= " and exists (select pm.`uid` from `#__social_privacy_map` as pm where pm.`uid` = p.`id` and pm.`utype` = 'profiles')";

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

	}

	/**
	 * batch processing on stream privacy id
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setStreamPrivacyItemBatch($data)
	{
		$db = ES::db();
		$sql = $db->sql();

		// _privacyitems
		$streamModel = ES::model('Stream');

		$dataset = array();
		foreach ($data as $item) {
			$relatedData = $streamModel->getBatchRalatedItem($item->id);

			// If there are no related data, skip this.
			if (!$relatedData) {
				continue;
			}

			$element = $item->context_type;

			$streamItem = $relatedData[0];
			$uid = $streamItem->context_id;

			if ($element == 'photos' && count($relatedData) > 1) {

				if ($streamItem->target_id) {
					$key = $streamItem->target_id . '.albums';

					if (!isset(self::$_privacyitems[$key])) {
						$dataset['albums'][] = $streamItem->target_id;
					}
				}

				foreach ($relatedData as $itemData) {
					$key = $itemData->context_id . '.photos';

					if (!isset(self::$_privacyitems[$key])) {
						$dataset['photos'][] = $itemData->context_id;
					}
				}

				// go to next item
				continue;
			}

			if ($element == 'story' || $element == 'links') {
				$uid = $streamItem->uid;
			}

			if ($element == 'badges' || $element == 'shares') {
				$uid 	 = $streamItem->id;
				$element = SOCIAL_TYPE_ACTIVITY;
			}

			if (!$uid) {
				continue;
			}

			$key = $uid . '.' . $element;

			if (!isset(self::$_privacyitems[$key])) {
				$dataset[$element][] = $uid;
			}
		}

		// lets build the sql now.
		if ($dataset) {

			$mainSQL = '';
			foreach ($dataset as $element => $uids) {
				$ids = implode(',', $uids);

				foreach ($uids as $uid) {
					$key = $uid . '.' . $element;
					self::$_privacyitems[$key] = array();
				}

				$query = 'select a.`id`, a.`value` as `default`, a.`options`, ';
				$query .= 'b.`user_id`, b.`uid`, b.`type`, b.`value`,';
				$query .= 'b.`id`  as `pid`';
				$query .= ' from `#__social_privacy` as a';
				$query .= '		inner join `#__social_privacy_items` as b on a.`id` = b.`privacy_id`';
				$query .= ' where b.uid IN (' . $ids . ')';
				$query .= ' and b.type = ' . $db->Quote($element);

				$mainSQL .= (empty($mainSQL)) ? $query : ' UNION ' . $query;
			}

			$sql->raw($mainSQL);
			$db->setQuery($sql);

			$result = $db->loadObjectList();

			if ($result) {
				foreach ($result as $rItem) {
					$key = $rItem->uid . '.' . $rItem->type;
					self::$_privacyitems[$key] = $rItem;
				}
			}

		}

	}

	/**
	 * Retrieve all privacy commands.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAllRulesCommand()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select concat(`type`, ' . $db->Quote('.') . ',  `rule`) as `commands` from `#__social_privacy`';
		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadColumn();

		return $result;
	}

	/**
	 * Retrieve privacy's value which is a custom field type.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getFieldValue($key, $userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$keys = explode('.', $key);
		$pType = array_shift($keys);
		$pRule = implode('.', $keys);

		$query = "select a.`value` from `#__social_privacy_items` as a";
		$query .= "		inner join `#__social_privacy` as b on a.`privacy_id` = b.`id`";
		$query .= " where a.`user_id` = $userId and a.`type` = 'field'";
		$query .= " and b.`type` = '$pType' and b.`rule` = '$pRule'";

		$query .= " union all";

		$query .= " select a.`value` from `#__social_privacy_map` as a";
		$query .= " 	inner join `#__social_privacy` as b on a.privacy_id = b.id";
		$query .= " where a.`utype` = 'user'";
		$query .= " and a.`uid` = $userId";
		$query .= " and b.`type` = '$pType' and b.`rule` = '$pRule'";

		$query .= " union all";

		$query .= " select a.`value` from `#__social_privacy` as a where a.`type` = '$pType' and a.`rule` = '$pRule'";
		$query .= " limit 1";

		$sql->raw($query);
		$db->setQuery($sql);

		$value = $db->loadResult();

		return $value;
	}

	/**
	 * Get custom fields used in privacy configuration
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getConfigFields()
	{
		$db = ES::db();

		// only fields that has options are supported
		$supported = array($db->Quote('checkbox'), $db->Quote('dropdown'), $db->Quote('multidropdown'), $db->Quote('multilist'));

		$query = 'select a.`title`, a.`unique_key`, b.`element`';
		$query .= ' from `#__social_fields` as a';
		$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('profiles');
		$query .= ' inner join `#__social_workflows_maps` as wm on wm.`workflow_id` = fs.`workflow_id` and wm.`type` = ' . $db->Quote('user');
		$query .= ' inner join `#__social_profiles` as p on wm.`uid` = p.`id`';
		$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id` and b.`group` = ' . $db->Quote('user');
		$query .= ' where a.`searchable` = ' . $db->Quote('1');
		$query .= ' and a.`state` = ' . $db->Quote('1');
		$query .= ' and b.`element` in (' . implode(',', $supported) .')';
		$query .= ' and p.`state` = ' . $db->Quote('1');

		$db->setQuery($query);
		$results = $db->loadObjectList();

		$fields = array();

		// manual grouping here.
		if ($results) {
			foreach ($results as $item) {
				$fields[$item->unique_key] = $item;
			}
		}

		return $fields;
	}

	/**
	 * Get profile privacy's fields
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function getProfilePrivacyFields($command, $profileId, $workflowId)
	{

		static $profiles = array();

		$db = ES::db();

		$rule = explode('.', $command);

		$type = array_shift($rule);
		$rule = implode('.', $rule);

		$query = "select a.`params` from `#__social_privacy_map` as a";
		$query .= " inner join `#__social_privacy` as b on a.privacy_id = b.id";
		$query .= " where b.`type` = " . $db->Quote($type);
		$query .= " and b.`rule` = " . $db->Quote($rule);
		$query .= " and a.`uid` = " . $db->Quote($profileId);
		$query .= " and a.`utype` = " . $db->Quote('profiles');

		$db->setQuery($query);
		$result = $db->loadResult();

		if (! $result) {

			// look like admin did not configure fields on this privacy rule. lets get from
			// profile's global fields.

			if (!isset($profiles[$profileId])) {
				$tbl = ES::table('profile');
				$tbl->load($profileId);

				$profiles[$profileId] = $tbl;
			}

			$profile = $profiles[$profileId];
			$result = $profile->privacy_fields;
		}

		$fields = array();

		if ($result) {
			$data = ES::json()->decode($result);

			foreach ($data as $item) {
				$arr = explode('|', $item);

				$obj = new stdClass();
				$obj->unique_key = $arr[0];
				$obj->element = $arr[1];

				$fields[] = $obj;
			}
		}

		return $fields;
	}

	/**
	 * Get custom fields used in privacy
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function getCustomFields($command, $userId, $pid = '0', $ptype = SOCIAL_PRIVACY_TYPE_ITEM)
	{
		$db = ES::db();

		static $_cache = array();

		$user = ES::user($userId);

		$idx = $command . '.' . $user->profile_id;

		if (! isset($_cache[$idx])) {

			$userProfileId = $user->profile_id;
			$_cache[$idx] = $this->getProfilePrivacyFields($command, $userProfileId, $user->getWorkflow()->id);
		}

		$customfields = $_cache[$idx];

		if (! $customfields) {
			return array();
		}

		// read from json file for now.
		// if (is_null($customfields)) {
		// 	$datafile = SOCIAL_LIB . '/privacy/customfields.json';
		// 	if (! JFile::exists($datafile)) {
		// 		return array();
		// 	}
		// 	$customfields = ES::json()->decode(file_get_contents($datafile));
		// }

		$fields = array();

		$tmpKeys = array();
		foreach ($customfields as $item) {
			$tmpKeys[] = $db->Quote($item->unique_key);
		}

		$selected = array();

		if ($pid) {
			$query = "select * from `#__social_privacy_field`";
			$query .= " where `uid` = " . $db->Quote($pid);
			$query .= " and `utype` = " . $db->Quote($ptype);

			$db->setQuery($query);
			$results = $db->loadObjectList();

			if ($results) {
				foreach ($results as $row) {
					$selected[$row->field_key] = $row;
				}
			}
		}


		// $tmpKeys = array($db->Quote('TESTPRIVACYA'), $db->Quote('TESTPRIVACYB'), $db->Quote('TESTPRIVACYC'));

		$query = 'select distinct a.`unique_key`, a.`title`, b.`element`, fo.`title` as `opt_title`, fo.`value` as `opt_value`';
		$query .= ' from `#__social_fields` as a';
		$query .= ' inner join `#__social_fields_steps` as fs on a.`step_id` = fs.`id` and fs.`type` = ' . $db->Quote('profiles');
		$query .= ' inner join `#__social_workflows_maps` as wm on wm.`workflow_id` = fs.`workflow_id` and wm.`type` = ' . $db->Quote('user');
		$query .= ' inner join `#__social_profiles` as p on wm.`uid` = p.`id`';
		$query .= ' inner join `#__social_apps` as b on a.`app_id` = b.`id` and b.`group` = ' . $db->Quote('user');
		$query .= ' left join `#__social_fields_options` as fo on a.`id` = fo.`parent_id`';
		$query .= ' where a.`searchable` = ' . $db->Quote('1');
		$query .= ' and a.`state` = ' . $db->Quote('1');
		$query .= ' and a.`unique_key` IN (' . implode(',', $tmpKeys) . ')';
		$query .= ' and p.`state` = ' . $db->Quote('1');
		$query .= ' order by a.`id`, fo.`id`';

		// echo $query;
		// echo '<br><br>';

		$db->setQuery($query);

		$results = $db->loadObjectList();
		$data = array();
		$options = array();

		if ($results) {

			// manual grouping of custom fields
			foreach ($results as $item) {
				$obj = new stdClass();

				$obj->unique_key = $item->unique_key;
				$obj->title = $item->title;
				$obj->element = $item->element;
				$obj->options = array();

				$data[$item->unique_key] = $obj;

				$itemKey = $item->element . '|' . $item->unique_key;
				// $itemKey = $item->unique_key;

				if ($item->title) {
					$opt = new stdClass();

					$opt->title = $item->opt_title;
					$opt->value = $item->opt_value;
					$opt->selected = false;

					if ($selected && isset($selected[$itemKey])) {
						$selectedValue = $selected[$itemKey]->field_value;
						$selectedValue = $selectedValue . ','; // this last comma is to ease the checking later.

						if (ESJString::strpos($selectedValue, $item->opt_value .',') !== false) {
							$opt->selected = true;
						}
					}

					$options[$item->unique_key][] = $opt;
				}
			}

			foreach ($data as $key => $item) {
				if (isset($options[$key])) {
					$item->options = $options[$key];
				}

				$fields[] = $item;
			}

		}

		return $fields;
	}

	/**
	 * Retrieve privacy's value which is a custom field type.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function createFieldPrivacyItemsForUser($userId)
	{
		$user = ES::user($userId);
		$profileId = $user->profile_id;
		$workflowId = $user->getWorkflow()->id;

		$db = ES::db();
		$sql = $db->sql();

		$query = array();

		// this $privacyTable is the sql to retrieve the privacy value from profile if exists, and if no, then it will return from privacy master.
		$privacyTable = "select pi.`id`, pi.type, pi.rule, ifnull(pim.`value`, pi.value) as `value`";
		$privacyTable .= " from `#__social_privacy` as pi";
		$privacyTable .= " left join `#__social_privacy_map` as pim on pi.`id` = pim.`privacy_id` and pim.`utype` = 'profiles' and pim.`uid` = '$profileId'";
		$privacyTable .= " where pi.`type` = 'field'";

		$query[] = "INSERT INTO `#__social_privacy_items` (`privacy_id`, `user_id`, `uid`, `type`, `value`)";
		$query[] = "SELECT `d`.`id` AS `privacy_id`, '$userId' AS `user_id`, `a`.`id` AS `uid`, `d`.`type`, `d`.`value` FROM `#__social_fields` AS `a`";
		$query[] = "LEFT JOIN `#__social_fields_steps` AS `b`";
		$query[] = "ON `b`.`id` = `a`.`step_id`";
		$query[] = "LEFT JOIN `#__social_apps` AS `c`";
		$query[] = "ON `c`.`id` = `a`.`app_id`";
		$query[] = "LEFT JOIN (" . $privacyTable . ") AS `d`";
		$query[] = "ON `d`.`rule` = `c`.`element`";
		$query[] = "WHERE `d`.`type` = 'field'";
		$query[] = "AND `b`.`type` = 'profiles'";
		$query[] = "AND `b`.`workflow_id` = '$workflowId'";
		$query[] = "AND `a`.`id` NOT IN (SELECT `e`.`uid` FROM `#__social_privacy_items` AS `e` WHERE `e`.`type` = 'field' AND `e`.`user_id` = '$userId')";

		$sql->raw(implode(' ', $query));

		$db->setQuery($sql);

		return $db->query();
	}

	/**
	 * Add privacy's value which is a custom field type.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function createFieldPrivacyMapsForUser($userId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "INSERT INTO `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`)";
		$query .= " select pi.`id` as `privacy_id`, '$userId' AS `uid`, 'user' AS `utype`, ifnull(pim.`value`, pi.value) as `value`";
		$query .= " from #__social_privacy as pi";
		$query .= "		left join #__social_privacy_map as pim on pi.`id` = pim.`privacy_id`";
		$query .= "						and pim.`utype` = 'profiles' and pim.`uid` = (select prm.`profile_id` from `#__social_profiles_maps` as prm where prm.`user_id` = '$userId')";
		$query .= " where pi.`type` = 'field'";
		$query .= " AND pi.`id` NOT IN (SELECT b.`privacy_id` FROM `#__social_privacy_map` AS `b` WHERE `b`.`uid` = '$userId' AND `b`.`utype` = 'user')";

		$sql->raw($query);

		$db->setQuery($sql);

		return $db->query();
	}


	/**
	 * Function to determine if privacy contain custom fields or not.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasPrivacyCustomFieldValue($fields, $targetId)
	{
		$db = ES::db();


		$query = "select count(1) from (";

		$conditions = array();
		foreach ($fields as $key => $values) {

			$tmp = "select fd.`id` from `#__social_fields_data` as fd";
			$tmp .= " inner join `#__social_fields` as f on fd.`field_id` = f.`id`";
			$tmp .= " where fd.`uid` = " . $db->Quote($targetId);
			$tmp .= " and fd.`type` = " . $db->Quote('user');
			$tmp .= " and f.`unique_key` = " . $db->Quote($key);

			if (count($values) > 1) {

				$tmp .= " and (";

				$OR = "";
				foreach ($values as $value) {
					$string = "(fd.`raw` LIKE " . $db->Quote('%' . $value . '%') . ")";
					$OR .= $OR ? " OR " . $string : $string;
				}
				$tmp .= $OR;
				$tmp .= ")";

			} else {
				$tmp .= " and fd.`raw` LIKE " . $db->Quote('%' . $values[0] . '%');
			}

			$conditions[] = $tmp;
		}

		$query .= implode(' UNION ALL ', $conditions);
		$query .= ") as x";

		$db->setQuery($query);

		$result = $db->loadResult();

		$numConditions = count($fields);

		return $result >= $numConditions;
	}

	/**
	 * update photo privacy access
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateMediaAccess($utype, $ids, $privacy, $custom = null, $field = null)
	{
		$db = ES::db();

		$supportedMedia = array('photos', 'albums', 'audios', 'videos');
		$tableMap = array('photos' => '#__social_photos',
						'albums' => '#__social_albums',
						'audios' => '#__social_audios',
						'videos' => '#__social_videos'
					);

		if (!in_array($utype, $supportedMedia)) {
			return false;
		}

		$privacyLib = ES::privacy();
		$customPrivacy = '';

		if ($privacy == SOCIAL_PRIVACY_CUSTOM && $custom) {
			$customPrivacy = $privacyLib->convertCustomPrivacyDataToString($custom);
		}

		$fieldPrivacy = array();

		if ($privacy == SOCIAL_PRIVACY_FIELD && $field) {
			$fieldPrivacy = $privacyLib->convertFieldPrivacyStringToData($field);
		}

		$totalField = count($fieldPrivacy);

		$ids = ES::makeArray($ids);

		$tableName = $tableMap[$utype];

		$query = 'update ' . $db->nameQuote($tableName);
		$query .= ' set `access` = ' . $db->Quote($privacy);
		$query .= ', `custom_access` = ' . $db->Quote($customPrivacy);
		$query .= ', `field_access` = ' . $db->Quote($totalField);
		$query .= ' where `id` IN (' . implode(',', $ids) . ')';

		// echo $query;
		// echo '<br><br>';

		$db->setQuery($query);

		$state = $db->query();

		return $state;
	}

	/**
	 * Updates the access column for the activity stream
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function updateStreamAccess($type, $objectId, $privacy, $custom = null, $field = null, $verb = 'create')
	{
		$db = ES::db();

		$supportedTypes = array('photos', 'albums', 'audios', 'videos');

		if (!in_array($type, $supportedTypes)) {
			return false;
		}

		// Get the stream item
		$query = array();
		$query[] = 'SELECT * FROM `#__social_stream_item` AS a';
		$query[] = 'WHERE `context_type` = ' . $db->Quote($type);
		$query[] = 'AND `context_id` = ' . $db->Quote($objectId);
		$query[] = 'AND `verb` = ' . $db->Quote($verb);

		$db->setQuery($query);
		$streamItem = $db->loadObject();

		// If we can't find any stream item, possibly it has already been removed
		if (!$streamItem) {
			return;
		}

		$stream = ES::table('Stream');
		$stream->load($streamItem->uid);

		$privacyLib = ES::privacy();
		$customPrivacy = '';

		if ($privacy == SOCIAL_PRIVACY_CUSTOM && $custom) {
			$customPrivacy = $privacyLib->convertCustomPrivacyDataToString($custom);
		}

		$fieldPrivacy = array();

		if ($privacy == SOCIAL_PRIVACY_FIELD && $field) {
			$fieldPrivacy = $privacyLib->convertFieldPrivacyStringToData($field);
		}

		$stream->access = $privacy;
		$stream->custom_access = $customPrivacy;
		$stream->field_access = count($fieldPrivacy);

		return $stream->store();
	}


	/**
	 * get stream items count based on contexts info from privacy.
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function getStreamItemCount($contextType, $contextId)
	{
		$db = ES::db();

		$query = "select count(a.`id`) as `total`, a.`uid`";
		$query .= " from `#__social_stream_item` as a";
		$query .= " where a.`uid` = (select `uid` from `#__social_stream_item`";
		$query .= " 				where `context_type` = " . $db->Quote($contextType);
		$query .= "						and `context_id` = " . $db->Quote($contextId) . ")";

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}
}
