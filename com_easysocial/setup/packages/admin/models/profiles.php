<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelProfiles extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('profiles', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('state', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'ordering');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');

		$this->setState('state', $filter);


		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Saves the ordering of profiles
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function saveOrder($ids, $ordering)
	{
		$table = ES::table('Profile');
		$table->reorder();
	}

	public function updateOrdering($id, $order)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "update `#__social_profiles` set ordering = " . $db->Quote($order);
		$query .= " where id = " . $db->Quote($id);

		$sql->raw($query);


		$db->setQuery($sql);
		$state = $db->query();

		return $state;
	}

	/**
	 * Gets the default profile.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDefaultProfile()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_profiles');
		$sql->where('default', 1);

		$db->setQuery($sql);

		$row = $db->loadObject();

		$noDefaultSet = false;

		// If no default profile found then fetch the first one from the database
		if (!$row) {
			$sql->clear();
			$sql->select('#__social_profiles');
			$sql->limit(1);

			$db->setQuery($sql);

			$row = $db->loadObject();

			$noDefaultSet = true;
		}

		$profile = ES::table('Profile');
		$profile->bind($row);

		if ($noDefaultSet) {
			$profile->makeDefault();
		}

		return $profile;
	}

	/**
	 * Gets the profile field
	 *
	 * @since	1.2.1
	 * @access	public
	 */
	public function getProfileField($workflowId, $fieldCode)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->column('f.*');
		$sql->select('#__social_fields', 'f');
		$sql->innerjoin('#__social_fields_steps', 's');
		$sql->on('f.step_id', 's.id');
		$sql->where('f.unique_key', $fieldCode);
		$sql->where('s.workflow_id', $workflowId);
		$sql->where('s.type', 'profiles');

		$db->setQuery($sql);
		$data = $db->loadObject();

		return $data;
	}

	/**
	 * Retrieves the total number of profiles in the system.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalProfiles($options = array())
	{
		$db = ES::db();

		$query = array();

		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_profiles');
		$query[] = 'WHERE ' . $db->nameQuote('state') . '=' . $db->Quote(SOCIAL_STATE_PUBLISHED);

		if (isset($options['registration'])) {
			$query[] = 'AND ' . $db->nameQuote('registration') . '=' . $db->Quote(1);
		}

		if (isset($options['switchable_in'])) {
			$switchable_in = (int) $options['switchable_in'];
			$query[] = 'AND ' . $db->nameQuote('switchable_in') . '=' . $db->Quote($switchable_in);
		}

		$query = implode(' ', $query);
		$db->setQuery($query);
		$count = (int) $db->loadResult();

		return $count;
	}

	/**
	 * Retrieves a list of custom profiles from the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getItems($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_profiles');

		// Check for search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Check for ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		// Check for state
		$state = $this->getState('state');

		if ($state != 'all') {
			$sql->where('state', $state);
		}

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return false;
		}

		$profiles = array();
		$total = count($result);

		for ($i = 0; $i < $total; $i++) {
			$profile = ES::table('Profile');
			$profile->bind($result[$i]);

			$profiles[] = $profile;
		}

		return $profiles;
	}

	/**
	 * Retrieves a list of users not in any profiles.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOrphanMembersCount($publishedOnly = true)
	{
		$db = ES::db();
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__users') . ' AS a';
		$query[] = 'WHERE NOT EXISTS (select user_id from ' . $db->nameQuote('#__social_profiles_maps') . ' AS b';
		$query[] = 'where a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('user_id') . ')';

		if ($publishedOnly)
			$query[] = 'AND a.' . $db->nameQuote('block') . '=' . $db->Quote(0);

		$query = implode(' ', $query);

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteOrphanItems()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'delete from `#__social_profiles_maps` where not exists (select `id` from `#__social_profiles` where `profile_id` = `id`)';
		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Retrieves a list of users in this profile type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMembers($profileId, $options = array())
	{
		$config = ES::config();
		$db = ES::db();

		// Determine if we should randomize the result.
		$randomize = isset($options['randomize']) ? true : false;
		$limit = isset($options['limit']) ? (int) $options['limit'] : false;
		$includeAdmin = isset($options['includeAdmin']) ? $options['includeAdmin'] : true;

		$query = array();
		$query[] = 'SELECT b.' . $db->nameQuote('id') . ' FROM ' . $db->nameQuote('#__social_profiles_maps') . ' AS a';

		// Joins
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS b';
		$query[] = 'ON b.' . $db->nameQuote('id') . ' = a.' . $db->nameQuote('user_id');

		$excludeBlocked = isset($options['excludeblocked']) ? $options['excludeblocked'] : 0;
		if (ES::isBlockEnabled() && $excludeBlocked) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';

		}

		// Where
		$query[] = 'WHERE a.' . $db->nameQuote('profile_id') . '=' . $db->Quote($profileId);
		$query[] = 'AND b.' . $db->nameQuote('block') . ' = ' . $db->Quote(0);

		$excludeAdmins = array();

		if (!$includeAdmin) {
			// Retrieve all of the site admins
			$userModel = ES::model('Users');
			$excludeAdmins = $userModel->getSiteAdmins(true);
		}

		if ($excludeAdmins) {
			$query[] = ' AND a.' . $db->nameQuote('user_id') . ' NOT IN (' . implode(',', $excludeAdmins) . ')';
		}
		// user block continue here
		if (ES::isBlockEnabled() && $excludeBlocked) {
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		// Randomize the result if necessary
		if ($randomize) {
			$query[] = 'ORDER BY RAND()';
		}

		// If limit is set, we need to define the limit here.
		if ($limit) {
			$query[] = 'LIMIT 0,' . $limit;
		}

		// Merge queries back.
		$query = implode(' ', $query);

		// Debug
		// echo str_ireplace('#__', 'jos_', $query) . '<br />';
		// exit;

		$db->setQuery($query);

		// Load by column
		$result = $db->loadColumn();

		if (!$result) {
			return $result;
		}

		// Pre-load these users.
		$users = ES::user($result);

		// Ensure that $users is an array.
		$users = ES::makeArray($users);

		// Randomize the result if necessary.
		if ($randomize) {
			shuffle($users);
		}

		return $users;
	}


	/**
	 * Retrieves random albums
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getRandomAlbums($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_albums', 'a');
		$sql->column('a.*');

		// Filter by profile id
		$profileId = isset($options['profileId']) ? $options['profileId'] : '';
		$privacy = isset($options['privacy']) ? $options['privacy'] : true;

		if ($profileId) {
			$sql->join('#__social_profiles_maps', 'p');
			$sql->on('a.user_id', 'p.user_id');

			$sql->where('p.profile_id', (int) $profileId);
		}

		// Determine if we should include the core albums
		$coreAlbums = isset($options['core']) ? $options['core'] : true;

		if (!$coreAlbums) {
			$sql->where('a.core', 0);
		}

		$coreAlbumsOnly = isset($options['coreAlbumsOnly']) ? $options['coreAlbumsOnly'] : '';

		if ($coreAlbumsOnly) {
			$sql->where('a.core', 0, '>');
		}

		// Retrieve user's albums only. No cluster albums.
		$sql->where('a.type' , SOCIAL_TYPE_USER, '=');

		$withCoversOnly = isset($options['withCovers']) ? $options['withCovers'] : '';

		if ($withCoversOnly) {
			$sql->join('#__social_photos', 'b', 'INNER');
			$sql->on('a.cover_id', 'b.id');
		}

		$ordering = isset($options['order']) ? $options['order'] : '';

		if ($ordering) {
			$direction = isset($options['direction']) ? $options['direction'] : 'desc';

			$sql->order($ordering, $direction);
		}

		$pagination = isset($options['pagination']) ? $options['pagination'] : false;

		$result = array();

		if ($pagination) {
			// Set the total number of items.
			$totalSql = $sql->getTotalSql();
			$this->setTotal($totalSql);

			$result = $this->getData($sql);
		} else {
			$limit = isset($options['limit']) ? $options['limit'] : '';

			if ($limit) {
				$sql->limit($limit);
			}

			$db->setQuery($sql);
			$result = $db->loadObjectList();
		}

		if (!$result) {
			return $result;
		}

		$albums = array();

		$privacyLib = ES::privacy(ES::user()->id);

		foreach($result as $row) {
			$album = ES::table('Album');
			$album->bind($row);

			$add = true;

			if ($privacy) {
				$add = $privacyLib->validate('albums.view' , $album->id, SOCIAL_TYPE_ALBUM , $album->user_id);
			}

			if ($add) {
				$albums[] = $album;
			}
		}

		return $albums;
	}

	/**
	 * Removes user from existing profiles
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeUserFromProfiles($id)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->delete('#__social_profiles_maps');
		$sql->where('user_id', $id);

		$db->setQuery($sql);

		$db->Query();
	}

	/**
	 * Updates the user groups assigned
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function updateJoomlaGroup($userId, $profileId)
	{
		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Get the list of groups
		$gid = $profile->getJoomlaGroups();
		$options = array('gid' => $gid);

		// Get the current user object and assign it
		$user = ES::user($userId);

		// If selected user is truly #2968 super user, we should skip it.
		if ($user->authorise('core.admin') || $user->authorise('core.manage')) {
			return true;
		}

		$user->bind($options);

		// Save the user object
		$state = $user->save();

		return $state;
	}

	/**
	 * Update the fields that are associated to certain profile type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function updateFields($profileId, $fields)
	{
		$db = ES::db();

		// First in first out.
		$query  = 'DELETE FROM ' . $db->nameQuote('#__social_profile_types_fields') . ' '
				. 'WHERE ' . $db->nameQuote('profile_id') . '=' . $db->Quote($profileId);

		$db->setQuery($query);
		$db->Query();

		$query  = 'INSERT INTO ' . $db->nameQuote('#__social_profile_types_fields') . ' VALUES ';

		if (is_array($fields)) {
			$total  = count($fields);

			for ($i = 0; $i < $total; $i++) {
				$query  .= '(' . $db->Quote($profileId) . ',' . $db->Quote($fields[$i]) . ')';

				if (($i + 1) != $total) {
					$query  .= ',';
				}
			}
		}

		$db->setQuery($query);
		$db->Query();
	}

	/**
	 * Updates a user profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function updateUserProfile($uid, $profileId, $workflowId)
	{
		$map = ES::table('ProfileMap');
		$exists = $map->load(array('user_id' => $uid));

		if (!$exists) {
			$map->user_id = $uid;
			$map->state = SOCIAL_STATE_PUBLISHED;
		}

		$map->profile_id = $profileId;

		$state = $map->store();

		if (!$state) {
			$this->setError($map->getError());
			return $state;
		}

		$db = ES::db();
		$sql = $db->sql();

		// Get the lists of changed field id first before we update it
		$queryJoin = ' LEFT JOIN `#__social_fields` AS `b` ON a.`field_id` = b.`id`';
		$queryJoin .= ' LEFT JOIN `#__social_fields` AS `c` ON b.`unique_key` = c.`unique_key`';
		$queryJoin .= ' LEFT JOIN `#__social_fields_steps` AS `d` ON c.`step_id` = d.`id`';

		$queryWhere = ' a.`uid` = ' . $db->quote($uid);
		$queryWhere .= ' AND a.`type` = ' . $db->quote('user');
		$queryWhere .= ' AND d.`type` = ' . $db->quote('profiles');
		$queryWhere .= ' AND d.`workflow_id` = ' . $db->quote($workflowId);

		$query = 'SELECT a.`field_id` as `old_id`, c.`id` as `new_id` FROM `#__social_fields_data` AS `a`';
		$query .= $queryJoin;
		$query .= ' WHERE';
		$query .= $queryWhere;

		$db->setQuery($query);
		$fieldsMappingData = $db->loadObjectList();

		// Update the data now
		$query = 'UPDATE `#__social_fields_data` AS `a`';
		$query .= $queryJoin;
		$query .= 'SET `a`.`field_id` = c.id';
		$query .= ' WHERE';
		$query .= $queryWhere;

		$db->setQuery($query);

		$state = $db->query();

		if ($state) {

			// Update fields privacy according to the new profile
			$sql = $db->sql();

			$sql->update('#__social_privacy_items', 'a');
			$sql->leftjoin('#__social_fields', 'b');
			$sql->on('a.uid', 'b.id');
			$sql->leftjoin('#__social_fields', 'c');
			$sql->on('b.unique_key', 'c.unique_key');
			$sql->leftjoin('#__social_fields_steps', 'd');
			$sql->on('c.step_id', 'd.id');

			$sql->set('a.uid', 'c.id', false);

			$sql->where('a.user_id', $uid);
			$sql->where('(');
			$sql->where('a.type', 'field');
			$sql->where('a.type', 'year','=', 'OR');
			$sql->where(')');
			$sql->where('d.type', 'profiles');
			$sql->where('d.workflow_id', $workflowId) ;

			$db->setQuery($sql);

			$state = $db->query();

			// update new user field's the default value
			$this->updateUserFieldDefault($uid, $workflowId);

			// cleanup orphan records
			$this->cleanupOrphans($uid, $workflowId);

			// Update conditional fields data
			$this->syncConditionalData($uid, $fieldsMappingData);
		}

		return $state;
	}

	/**
	 * Update user's custom field default value when perform profile switching and
	 * the new custom field is not exists in old profile and the new custom field has default value.
	 *
	 * @since	2.1.10
	 * @access	public
	 */
	private function updateUserFieldDefault($userId, $workflowId)
	{
		// first get the custom fields that is not inside user's data
		// for now we only fix for multilist, checkbox, dropdown, multidropdown, radio
		// as these are the custom fields that crucial to privacy's custom field type.
		$db = ES::db();
		$hasMultiSelectionFields = array('multilist', 'checkbox', 'multidropdown', 'radio');

		$query = "select a.`id`, c.`element` from `#__social_fields` as a";
		$query .= " inner join `#__social_fields_steps` as b on a.`step_id` = b.`id`";
		$query .= " inner join `#__social_apps` as c on a.`app_id` = c.`id` and c.`type` = " . $db->Quote('fields');
		$query .= " where b.`workflow_id` = " . $db->Quote($workflowId);
		$query .= " and c.`element` in ('multilist', 'checkbox', 'dropdown', 'multidropdown', 'radio')";
		$query .= " and not exists (select `field_id` from `#__social_fields_data` where `field_id` = a.`id` and `uid` = " . $db->Quote($userId) . " and `type` = " . $db->Quote('user') . ")";

		$db->setQuery($query);

		$rows = $db->loadObjectList();

		if ($rows) {

			$insert = array();

			foreach ($rows as $item) {

				$field = ES::table('field');
				$field->load($item->id);

				// check if this field has options or not.
				$fieldOptions = $field->getOptions();

				$defaults = array();

				if ($fieldOptions) {
					foreach ($fieldOptions as $key => $options) {
						foreach ($options as $value) {
							if ($value->default) {
								$defaults[] = $value->value;
							}
						}
					}
				}

				if ($defaults && !in_array($item->element, $hasMultiSelectionFields)) {
					$defaults = isset($defaults[0]) ? $defaults[0] : '';
				}

				if ($defaults) {

					$val = $defaults;
					$raw = $defaults;

					if (is_array($defaults)) {
						$val = json_encode($defaults);
						$raw = implode(' ', $defaults);
					}

					// preparing insert statement
					$insert[] = "( " . $db->Quote($field->id) . ", " . $db->Quote($userId). ", " . $db->Quote(SOCIAL_FIELDS_GROUP_USER) . ", " . $db->Quote($val) . ", " . $db->Quote($raw) . ")";
				}

			}

			if ($insert) {

				$tmp = implode(',', $insert);

				$insertSql = "insert into `#__social_fields_data` (`field_id`, `uid`, `type`, `data`, `raw`) values ";
				$insertSql .= $tmp;

				$db->setQuery($insertSql);
				$db->query();

			}
		}

		return true;
	}

	/**
	 * After switch profile, we need to clean up orphan's data if there is any
	 *
	 * @since	2.1.10
	 * @access	public
	 */
	private function cleanupOrphans($userId, $workflowId)
	{
		$db = ES::db();

		$query = "delete a from `#__social_fields_data` as a";
		$query .= " where a.`uid` = " . $db->Quote($userId);
		$query .= " and a.`type` = 'user'";
		$query .= " and not exists (select b.id from `#__social_fields` as b";
		$query .= "						inner join `#__social_fields_steps` as c on b.step_id = c.id";
		$query .= "  					where b.`id` = a.`field_id` and c.`workflow_id` = " . $db->Quote($workflowId) . ")";

		$db->setQuery($query);
		$db->query();

		return true;
	}

	/**
	 * Retrieves a list of profile types throughout the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getProfiles($config = array())
	{
		$db = ES::db();
		$my = ES::user();

		$showCount = isset($config['showCount']) ? $config['showCount'] : true;

		// Ensure that the user's are published
		$validUsers = isset($config['validUser']) ? $config['validUser'] : null;

		// Determines if we should display admin's on this list.
		$includeAdmin = isset($config['includeAdmin']) ? $config['includeAdmin'] : null;

		// Determine if we should exclude moderator access profile type
		$excludeModeratorAccess = isset($config['excludeModeratorAccess']) ? $config['excludeModeratorAccess'] : false;

		// Determines if we should exclude this profile type user on the user listing page.
		$excludeUserListing = isset($config['excludeUserListing']) ? $config['excludeUserListing'] : false;

		$excludeIds = array();

		if ($my->id) {
			$excludeIds[] = $my->id;
		}

		// If caller doesn't want to include admin, we need to set the ignore list.
		if ($includeAdmin === false) {
			// Get a list of site administrators from the site.
			$userModel = ES::model('Users');
			$admins = $userModel->getSiteAdmins(true);

			if ($admins) {
				$excludeIds = array_merge($excludeIds, $admins);
				$excludeIds = array_unique($excludeIds);
			}
		}

		$queries = array();
		$wheres = array();

		// get profile 1st
		$queries[] = 'SELECT a.*';
		$queries[] = 'FROM ' . $db->nameQuote('#__social_profiles') . ' AS a';

		// Need to filter by state.
		if (isset($config['state'])) {
			$state = (int) $config['state'];

			$wheres[] = 'a.' . $db->nameQuote('state') . '=' . $db->Quote($state);
		}

		if ($excludeModeratorAccess) {
			$wheres[] = 'a.' . $db->nameQuote('moderator_access') . '=' . $db->Quote(0);
		}

		// Skip this if the current logged in user is site admin
		if ($excludeUserListing && !$my->isSiteAdmin()) {
			$wheres[] = 'a.' . $db->nameQuote('exclude_userlisting') . '=' . $db->Quote(0);
		}

		if (isset($config['excludeProfileIds']) && $config['excludeProfileIds']) {
			$exludeProfileId = implode(',', $config['excludeProfileIds']);
			$wheres[] = 'a.' . $db->nameQuote('id') . ' NOT IN (' . $exludeProfileId . ')';
		}

		// Need to filter by registration flag.
		if (isset($config['registration'])) {
			$registration = (int) $config['registration'];

			$wheres[] = 'a.' . $db->nameQuote('registration') . '=' . $db->Quote($registration);
		}

		// Need to filter by switchable_in flag.
		if (isset($config['switchable_in'])) {
			$switchable_in = (int) $config['switchable_in'];

			$wheres[] = 'a.' . $db->nameQuote('switchable_in') . '=' . $db->Quote($switchable_in);
		}

		// Only show which profile have the community access permission
		// if enable this allow admin view ESAD profile user and that user is superadmin then only can view.
		if (!($my->isSiteAdmin()) && isset($config['excludeESAD']) && $config['excludeESAD']) {
			$wheres[] = 'a.' . $db->nameQuote('community_access') . ' = 1';
		}

		$query = implode(' ', $queries);
		$where = '';
		if ($wheres) {
			$where = ' WHERE ';
			$where .= (count($wheres) > 1) ? implode(' AND ', $wheres) : $wheres[0];
		}

		$query .= $where;

		// Specify the ordering.
		if (isset($config['ordering'])) {
			$ordering = $config['ordering'];
			$query .= ' ORDER BY a.' . $db->nameQuote($ordering) . ' ASC';
		} else {
			$query .= ' ORDER BY a.' . $db->nameQuote('ordering') . ' ASC';
		}

		// Determine wheter or not to use pagination
		$paginate = isset($config['limit']) ? $config['limit'] : SOCIAL_PAGINATION_ENABLE;
		$paginate = $paginate == SOCIAL_PAGINATION_NO_LIMIT ? false : SOCIAL_PAGINATION_ENABLE;

		if (! $paginate) {
			// if no pagination required, lets set the limit to 0
			$this->setState('limit', 0);
		}

		$results = $this->getData($query);

		// to store profiles count.
		$counts = array();

		if ($showCount) {

			// now for each profiles, we need to get the user counts.
			$unions = array();
			foreach ($results as $row) {
				$query = "select count(1) as `cnt`, " . $db->Quote($row->id) . " as `profile_id`";
				$query .= " from `#__social_profiles_maps` as a";

				if ($validUsers) {
					$query .= ' INNER JOIN ' . $db->nameQuote('#__users') . ' AS c';
					$query .= ' ON c.' . $db->nameQuote('id') . '= a.' . $db->nameQuote('user_id');
					$query .= ' AND c.' . $db->nameQuote('block') . '=' . $db->Quote(0);
				}

				$query .= " where a.`profile_id` = " . $db->Quote($row->id);

				if ($excludeIds) {
					$query .= ' AND a.' . $db->nameQuote('user_id') . ' NOT IN (' . implode(',', $excludeIds) . ')';
				}


				$unions[] = $query;
			}

			$query = implode(' UNION ALL ', $unions);

			// echo $query;exit;

			$db->setQuery($query);

			$counts = $db->loadObjectList('profile_id');

		}

		$profiles = array();

		foreach ($results as $row) {
			$profile = ES::table('Profile');
			$profile->bind($row);

			// Assign temporary data.
			$profile->totalUsers = isset($counts[$row->id]) ? $counts[$row->id]->cnt : 0;

			// Set the profile object back.
			$profiles[] = $profile;
		}

		return $profiles;

		$query = array();
	}

	/**
	 * Retrieve the total number of users in this profile type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMembersCount($profileId, $publishedOnly = true, $excludeBlocked = false)
	{
		$config = ES::config();
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_profiles_maps') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS b';
		$query[] = 'ON b.' . $db->nameQuote('id') . ' = a.' . $db->nameQuote('user_id');

		if (ES::isBlockEnabled() && $excludeBlocked) {
			// user block
			$query[] = ' LEFT JOIN ' . $db->nameQuote('#__social_block_users') . ' as bus';

			$query[] = ' ON (';
			$query[] = ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote('user_id');
			$query[] = ' AND bus.' . $db->nameQuote('target_id') . ' = ' . $db->Quote(JFactory::getUser()->id);
			$query[] = ') OR (';
			$query[] = ' b.' . $db->nameQuote('id') . ' = bus.' . $db->nameQuote( 'target_id' ) ;
			$query[] = ' AND bus.' . $db->nameQuote('user_id') . ' = ' . $db->Quote(JFactory::getUser()->id) ;
			$query[] = ')';

		}

		$query[] = 'WHERE a.' . $db->nameQuote('profile_id') . '=' . $db->Quote($profileId);

		if ($publishedOnly) {
			$query[] = 'AND b.' . $db->nameQuote('block') . '=' . $db->Quote(0);
		}

		// Determines if we should display admin's on this list.
		$includeAdmin = $config->get('users.listings.admin') ? true : false;

		if (ES::isFromAdmin()) {
			$includeAdmin = true;
		}

		// If caller doesn't want to include admin, we need to set the ignore list.
		if ($includeAdmin === false) {
			// Get a list of site administrators from the site.
			$userModel = ES::model('Users');
			$admins = $userModel->getSiteAdmins(true);

			if ($admins) {
				$query[] = ' AND b.' . $db->nameQuote('id') . ' NOT IN (' . implode(',', $admins) . ')';
			}
		}

		if (ES::isBlockEnabled() && $excludeBlocked) {
			// user block continue here
			$query[] = ' AND bus.' . $db->nameQuote('id') . ' IS NULL';
		}

		$query = implode(' ', $query);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Retreive custom field groups based on a specific step.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFieldsGroups($stepId, $type = 'profiletype')
	{
		$db		= ES::db();

		$query  = 'SELECT a.* '
				. 'FROM ' . $db->nameQuote('#__social_fields_groups') . ' AS a '
				. 'WHERE a.' . $db->nameQuote('steps_id') . ' = ' . $db->Quote($stepId) . ' '
				. 'AND a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$groups = array();

		foreach ($result as $row) {
			$group  = ES::table('FieldGroup');
			$group->bind($row);
			$groups[]   = $group;
		}

		return $groups;
	}

	/**
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFields(&$groups, $filters = array())
	{
		$db = ES::db();

		foreach ($groups as $group) {
			$query  = 'SELECT a.*,b.title AS addon_title, b.element AS addon_element FROM ' . $db->nameQuote('#__social_fields') . ' AS a '
					. 'INNER JOIN ' . $db->nameQuote('#__social_apps') . ' AS b '
					. 'ON b.id=a.field_id '
					. 'WHERE a.`group_id`=' . $db->Quote($group->id);

			if ($filters) {
				$subquery = array();

				foreach ($filters as $key => $value) {
					$subquery[] = 'a.' . $db->nameQuote($key) . '=' . $db->Quote($value);
				}

				$query .= ' ' . count($subquery) == 1 ? ' AND ' . $subquery[0] : implode(' AND ', $subquery);
			}

			$db->setQuery($query);

			$fields	= $db->loadObjectList();
			$group->childs  = array();

			foreach ($fields as $field) {
				$table = ES::table('Field');
				$table->bind($field);
				$table->addon_title = $field->addon_title;

				$group->childs[] = $table;
			}
		}

		return $groups;
	}

	/**
	 * Creates the necessary core fields required in order for the system to work.
	 *
	 * @since	1.0
	 * @access	public
	 *
	 */
	public function createDefaultFields($stepId)
	{
		// Load apps model
		$model = ES::model('Apps');

		// Get a list of core and default apps
		$apps = $model->getDefaultApps(array('type' => SOCIAL_APPS_TYPE_FIELDS));

		// Get default data from the manifest files.
		$lib = ES::fields();
		$fields = $lib->getCoreManifest(SOCIAL_FIELDS_GROUP_USER, $apps);

		// Only get fields that doesn't exist for the profile type.
		if (!$fields) {
			return false;
		}

		foreach ($fields as $row) {
			$field = ES::table('Field');

			// Set the current profile's id.
			$field->bind($row);

			// If there is a params set in the defaults.json, we need to decode it back to a string.
			if ($row->params && is_object($row->params)) {
				$field->params 	= ES::json()->encode($row->params);
			}

			// Set the core identifier
			$field->core = SOCIAL_STATE_PUBLISHED;

			// Set the step id this field belongs to.
			$field->step_id = $stepId;

			// Let's try to store the custom field now.
			$field->store();
		}

		return true;
	}

	/**
	 * Retrieves a list of core fields from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCoreFields($profileId)
	{
		$db = ES::db();

		$query  = 'SELECT a.*, b.title AS addon_title '
				. 'FROM ' . $db->nameQuote('#__social_fields') . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote('#__social_apps') . ' AS b '
				. 'ON a.' . $db->nameQuote('app_id') . ' = b.' . $db->nameQuote('id') . ' '
				. 'WHERE b.' . $db->nameQuote('core') . ' = ' . $db->Quote(1);

		// @rule: We already know before hand which elements are the core fields for the profile types.
		$elements   = array($db->Quote('joomla_username'), $db->Quote('joomla_fullname'), $db->Quote('joomla_email'),
							$db->Quote('joomla_password'), $db->Quote('joomla_timezone'), $db->Quote('joomla_user_editor'), $db->Quote('joomla_password2'));

		$query  .= ' AND b.' . $db->nameQuote('element') . ' IN(' . implode(',', $elements) . ')';

		$db->setQuery($query);

		$result = $db->loadObjectList();
		$fields = array();

		foreach ($result as $row) {
			$field = ES::table('Field');
			$field->bind($row);
			$field->set('addon_title', $row->addon_title);

			// Manually push in profile_id
			$field->profile_id = $profileId;
			$fields[] = $field;
		}

		return $fields;
	}


	/**
	 * Retrieves the past 7 days statistics for new sign ups.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getRegistrationStats($profileId = null)
	{
		$db = ES::db();
		$dates = array();

		// Get the past 7 days
		$curDate 	= ES::date();
		for ($i = 0 ; $i < 7; $i++) {
			$obj = new stdClass();

			if ($i == 0) {
				$dates[] = $curDate->toMySQL();
			} else {
				$unixdate = $curDate->toUnix();
				$new_unixdate = $unixdate - ($i * 86400);
				$newdate = ES::date($new_unixdate);

				$dates[] = $newdate->toMySQL();
			}
		}

		// Reverse the dates
		$dates = array_reverse($dates);

		$result = new stdClass();
		$result->dates = $dates;

		$profiles = array();

		foreach ($dates as $date) {
			// Registration date should be Y, n, j
			$date = ES::date($date)->format('Y-m-d');

			$query = 'select a.' . $db->nameQuote('id') . ', a.' . $db->nameQuote('title') . ', count(b.' . $db->nameQuote('id') . ') as cnt';
			$query .= ' from ' . $db->nameQuote('#__social_profiles') . ' as a';
			$query .= '	left join ' . $db->nameQuote('#__social_profiles_maps') . ' as b';
			$query .= '		on a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('profile_id');
			$query .= '		and date_format(b.' . $db->nameQuote('created') . ', GET_FORMAT(DATE,' . $db->Quote('ISO') . ')) = ' . $db->Quote($date);

			// make sure these user exists in #__users table.
			$query .= '		and exists (select u.`id` from `#__users` as u where u.`id` = b.`user_id`)';

			if ($profileId) {
				$query .= ' WHERE a.' . $db->quoteName('id') . '=' . $db->Quote($profileId);
			}

			$query .= ' group by a.' . $db->nameQuote('id');

			$db->setQuery($query);

			$items = $db->loadObjectList();

			foreach ($items as $item) {

				if (!isset($profiles[$item->id])) {
					$profiles[$item->id] = new stdClass();
					$profiles[$item->id]->title = $item->title;

					$profiles[$item->id]->items = array();
				}

				$profiles[$item->id]->items[] = $item->cnt;
			}
		}

		// Reset the index.
		$profiles = array_values($profiles);

		$result->profiles = $profiles;

		return $result;
	}

	/**
	 * Check if the profile alias exists
	 *
	 * @since  1.2
	 * @access public
	 */
	public function aliasExists($alias, $exclude = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_profiles');
		$sql->where('alias', $alias);

		if (!empty($exclude)) {
			$sql->where('id', $exclude, '!=');
		}

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Gets all the profile row without state
	 *
	 * @since  1.2
	 * @access public
	 */
	public function getAllProfiles($filters = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_profiles');

		foreach ($filters as $key => $val) {
			$sql->where($key, $val);
		}

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		$profiles = array();

		foreach ($result as $row) {
			$table = ES::table('profile');
			$table->bind($row);

			$profiles[] = $table;
		}

		return $profiles;
	}

	/**
	 * Determines whether this current profile type is associated with
	 * a custom field.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isChild($fieldId, $profileId)
	{
		$db = ES::db();
		$query = 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__social_fields') . ' '
				. 'WHERE ' . $db->nameQuote('field_id') . '=' . $db->Quote($fieldId) . ' '
				. 'AND ' . $db->nameQuote('profile_id') . '=' . $db->Quote($profileId);

		$db->setQuery($query);

		return $db->loadResult() > 0 ? true : false;
	}


	/**
	 * remap the user profile type.
	 * NOTE: we canot use the function name with 'switch' as its a predefined keyword in PHP
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function switchProfile($userId, $oldId, $newId)
	{
		$db = ES::db();

		$query = "update `#__social_profiles_maps` set `profile_id` = " . $db->Quote($newId);
		$query .= " where `user_id` = " . $db->Quote($userId);
		$query .= " and `profile_id` = " . $db->Quote($oldId);

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * Retrieve profiles used in filters.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getProfileFilters($options = array())
	{
		$db = ES::db();

		$showCount = isset($options['showCount']) ? $options['showCount'] : true;

		$query = "select a.* from `#__social_profiles` as a";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		// get counts
		$counts = array();
		if ($showCount) {
			$queries = array();
			foreach ($results as $row) {
				$query = "select count(1) as `cnt`, " . $db->Quote($row->id) . " as `profile_id` from `#__social_profiles_maps` where profile_id = " . $db->Quote($row->id);
				$queries[] = $query;
			}

			$query = implode(' UNION ALL ', $queries);

			$db->setQuery($query);

			$counts = $db->loadObjectList('profile_id');
		}

		$profiles = array();
		foreach ($results as $row) {
			$row->count = isset($counts[$row->id]) ? $counts[$row->id]->cnt : 0;
			$profiles[] = $row;
		}

		return $profiles;
	}


	/**
	 * Check if a profile type has privacy mapped or not.
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function hasPrivacyMap($profileId)
	{
		$db = ES::db();

		$query = "select count(1) from `#__social_privacy_map`";
		$query .= " where `uid` = " . $db->Quote($profileId);
		$query .= " and `utype` = " . $db->Quote('profiles');

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result ? true : false;
	}

	/**
	 * Insert default privacy data into mapping table
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function insertDefaultPrivacy($profileId)
	{
		$db = ES::db();

		$query = "insert into `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`)";
		$query .= " select `id`, " . $db->Quote($profileId) . ", 'profiles', `value` from `#__social_privacy`";

		$db->setQuery($query);
		$state = $db->query();

		return $state;
	}

	/**
	 * Sync conditional data field id from the user params
	 *
	 * @since	4.0.12
	 * @access	public
	 */
	public function syncConditionalData($userId, $fieldsMappingData)
	{
		$user = ES::user($userId);
		$esParams = $user->getEsParams();

		$conditionalFields = $esParams->get('conditionalFields');

		if (!$conditionalFields) {
			return true;
		}

		$conditionalFields = json_decode($conditionalFields, true);

		foreach ($fieldsMappingData as $field) {
			if (isset($conditionalFields[$field->old_id])) {

				// Assign to a new id
				$conditionalFields[$field->new_id] = $conditionalFields[$field->old_id];
				unset($conditionalFields[$field->old_id]);
			}
		}

		$user->setUserParams('conditionalFields', json_encode($conditionalFields));

		return true;
	}
}
