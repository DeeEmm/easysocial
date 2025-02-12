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

require_once(dirname(__DIR__) . '/abstracts/workflows.php');
require_once(dirname(__DIR__) . '/traits/verifications.php');

class EasySocialViewUsers extends EasySocialViewWorkflowsAbstract
{
	use EasySocialViewVerificationsTrait;

	protected $verificationType = SOCIAL_TYPE_USER;

	/**
	 * Renders the user's listing
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_USERS', 'COM_EASYSOCIAL_DESCRIPTION_USERS');

		// Add Joomla buttons
		JToolbarHelper::addNew();
		JToolbarHelper::publishList('publish', JText::_('COM_ES_UNBAN'));
		JToolbarHelper::unpublishList('unpublish', JText::_('COM_ES_BAN'));
		JToolbarHelper::custom('switchProfile', 'switchprofile', '', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SWITCH_PROFILE'));
		JToolbarHelper::deleteList();

		// Get the model
		$profilesModel = ES::model('Profiles');
		$model = ES::model('Users', array('initState' => true, 'namespace' => 'users.listing'));

		// perform some maintenance actions here
		$profilesModel->deleteOrphanItems();

		// Get filter states.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'default');
		$direction = $this->input->get('direction', $model->getState('direction'), 'default');

		$limit = $model->getState('limit');
		$published = $model->getState('published');

		$search = $model->getState('search');
		$group = $this->input->get('group', $model->getState('group'), 'int');
		$profile = $this->input->get('profile', $model->getState('profile'), 'int');

		// Checks if user listing that is retrieved requires multiple selection or not
		// Multiple is enabled by default, assuming that we are on normal user listing page
		// If tmpl = component, this means that other elements is retrieving user listing through ajax, in that case, we default it to false instead
		$multiple = true;
		$idOnly = true;

		$excludeClusterMembers = $this->input->get('excludeClusterMembers', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');

		if ($this->input->get('tmpl', '', 'string') === 'component') {
			$multiple = $this->input->get('multiple', false, 'bool');

			// Limit of user to be display according to joomla default list limit
			$limit = ES::getLimit('userslimit');
		}

		$this->set('multiple', $multiple);

		// used in member add dialog from backend.
		$this->set('excludeClusterMembers', $excludeClusterMembers);
		$this->set('clusterId', $clusterId);

		// Ensure that reset that idOnly value if that ordering is points
		if ($ordering == 'points') {
			$idOnly = false;
		}

		// Get users
		$users = $model->getUsersWithState(array('limit' => $limit, 'idonly' => $idOnly, 'excludeClusterMembers' => $excludeClusterMembers, 'clusterId' => $clusterId));

		if ($users) {
			// preload users
			ES::user($users);
		}

		// Get pagination from model
		$pagination = $model->getPagination();

		$callback = $this->input->get('callback', '', 'default');

		// Prepare usergroup array separately because the usergroup title is no longer in the user object
		$usergroupsData = ES::model('Users')->getUserGroups(array('showCount' => false));

		// Reformat the usergroup to what we want
		$usergroups = array();
		foreach ($usergroupsData as $row) {
			$usergroups[$row->id] = $row->title;
		}

		$this->set('usergroups', $usergroups);

		$this->set('profile', $profile);
		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('direction', $direction);
		$this->set('callback', $callback);
		$this->set('search', $search);
		$this->set('published', $published);
		$this->set('group', $group);
		$this->set('pagination', $pagination);
		$this->set('users', $users);

		echo parent::display('admin/users/default/default');
	}

	/**
	 * Displays the export user form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function export()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_EXPORT_USERS', 'COM_EASYSOCIAL_DESCRIPTION_EXPORT_USERS');

		// Get a list of profiles on the site
		$model = ES::model('Profiles');
		$profiles = $model->getProfiles();

		$this->set('profiles', $profiles);

		echo parent::display('admin/users/export');
	}

	/**
	 * Displays the import user form
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function import()
	{
		$this->setHeading('COM_ES_IMPORT_USERS_HEADING', 'COM_ES_IMPORT_USERS_DESC');

		// Get a list of profiles on the site
		$model = ES::model('Profiles');
		$profiles = $model->getProfiles();

		$this->set('profiles', $profiles);

		echo parent::display('admin/users/import/default');
	}

	/**
	 * Import user settings form after CSV file is uploaded
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function importSettings($data = false, $profileId = false)
	{
		if (!$data || !$profileId) {
			return $this->call('import');
		}

		$this->setHeading('COM_ES_IMPORT_USERS_HEADING', 'COM_ES_IMPORT_USERS_DESC');

		$previousData = $this->input->get('previousData', '', 'default');
		$selectedFields = false;

		if ($previousData && is_string($previousData)) {
			$previousData = ES::makeArray(json_decode($previousData));
			$selectedFields = $previousData['field_id'];
		}

		// Determine the total number of columns
		$item = $data[0];
		$totalColumn = count($item);

		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Get list of custom field available for selected profile
		$customFields = $profile->getCustomFields(null, array('exclusion' => array('cover')));

		// Get list of available column in joomla user table
		$model = ES::model('Users');
		$joomlaUserColumn = $model->getJoomlaUserColumn(true);

		$passwordFieldId = false;

		foreach ($customFields as $key => $field) {
			if ($field->element == 'header') {
				unset($customFields[$key]);
			}

			if ($field->element == 'joomla_password') {
				$passwordFieldId = $field->id;
			}
		}

		$this->set('data', $data);
		$this->set('profile', $profile);
		$this->set('customFields', $customFields);
		$this->set('totalColumn', $totalColumn);
		$this->set('passwordFieldId', $passwordFieldId);
		$this->set('joomlaUserColumn', $joomlaUserColumn);
		$this->set('previousData', $previousData);
		$this->set('selectedFields', $selectedFields);

		echo parent::display('admin/users/import/settings');
	}

	/**
	 * Overview of the user import
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function importOverview($fieldIds = false, $options = array())
	{
		$this->setHeading('COM_ES_IMPORT_USERS_HEADING', 'COM_ES_IMPORT_USERS_DESC');

		// Retrieve previous data
		$post = $this->input->getArray('post');

		// Get the csv file
		$path = SOCIAL_IMPORT_CSV_DIR . '/es-userimport.csv';
		$data = ES::parseCSV($path, false, false);

		if (!$data) {
			$this->info->set(false, 'COM_ES_INVALID_CSV_FILE', 'error');
			return $this->call('import');
		}

		$profileId = isset($options['profileId']) ? $options['profileId'] : false;
		$autoPassword = isset($options['autopassword']) ? $options['autopassword'] : false;
		$autoApprove = isset($options['autoapprove']) ? $options['autoapprove'] : false;
		$passwordType = isset($options['passwordtype']) ? $options['passwordtype'] : false;
		$passwordFieldId = isset($options['passwordFieldId']) ? $options['passwordFieldId'] : false;

		if (!$fieldIds || !$profileId) {
			$this->info->set(false, 'COM_ES_USER_IMPORT_PLEASE_SELECT_FIELDS', 'error');
			return $this->call('importSettings', $data, $profileId);
		}

		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Determine the total number of columns
		$item = $data[0];
		$totalColumn = count($item);
		$fields = array();

		foreach ($fieldIds as $fieldId) {

			// If this is not an integer, we know this is joomla column
			if ($fieldId && !(int) $fieldId) {
				$obj = new stdClass();
				$obj->id = $fieldId;
				$obj->title = $fieldId;

				$fields[] = $obj;
				continue;
			}

			$field = ES::table('Field');
			$field->load($fieldId);

			if (!$field->id) {
				$totalColumn--;
				continue;
			}

			$obj = new stdClass();
			$obj->id = $field->id;
			$obj->title = JText::_($field->getTitle());

			// Append password type to the password column title
			if ($field->id == $passwordFieldId) {
				$title = 'COM_ES_IMPORT_USERS_';
				$title .= $passwordType == 'plain' ? 'PLAIN_TEXT' : 'JOOMLA_ENCRYPTED';

				$obj->title .= ' (' . JText::_($title) . ')';
			}

			$fields[] = $obj;
		}

		if (empty($fields)) {
			$this->info->set(false, 'COM_ES_USER_IMPORT_PLEASE_SELECT_FIELDS', 'error');
			return $this->call('importSettings', $data, $profileId);
		}

		$this->set('data', $data);
		$this->set('fields', $fields);
		$this->set('fieldIds', $fieldIds);
		$this->set('totalColumn', $totalColumn);
		$this->set('profile', $profile);
		$this->set('total', count($data));
		$this->set('importOptions', $options);
		$this->set('previousData', $post);

		echo parent::display('admin/users/import/overview');
	}

	/**
	 * Renders a list of pending users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function pending()
	{
		JToolbarHelper::deleteList();
		JToolbarHelper::divider();
		JToolbarHelper::custom('approve', 'publish', 'social-publish-hover', JText::_('COM_EASYSOCIAL_APPROVE_BUTTON'), true);
		JToolbarHelper::custom('reject', 'unpublish', 'social-unpublish-hover', JText::_('COM_EASYSOCIAL_REJECT_BUTTON'), true);

		$this->setHeading('COM_EASYSOCIAL_HEADING_PENDING_APPROVALS', 'COM_EASYSOCIAL_DESCRIPTION_PENDING_APPROVALS');

		// Get the user's model.
		$model = ES::model('Users', array('initState' => true, 'namespace' => 'users.pending'));

		$search = $model->getState('search');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');
		$limit = $model->getState('limit');
		$published = $model->getState('published');
		$filter = $model->getState('filter');
		$profile = $model->getState('profile');

		$options = array('ignoreESAD' => true, 'limit' => $limit);
		$options['state'] = SOCIAL_REGISTER_APPROVALS;

		if ($filter == 'verify') {
			$options['state'] = SOCIAL_REGISTER_VERIFY;
		}

		if ($filter == 'confirmation_approval') {
			$options['state'] = SOCIAL_REGISTER_CONFIRMATION_APPROVAL;
		}

		if ($filter == 'all') {
			$options['state'] = 'pending';
		}

		$result = $model->getUsersWithState($options);

		$pagination = $model->getPagination();
		$users = array();

		if ($result) {
			foreach ($result as $row) {
				$users[] = ES::user($row->id);
			}
		}

		$this->set('search', $search);
		$this->set('profile', $profile);
		$this->set('limit', $limit);
		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('users', $users);
		$this->set('pagination', $pagination);
		$this->set('filter', $filter);

		parent::display('admin/users/pending/default');
	}

	/**
	 * Renders a list of banned users
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function banned($tpl = null)
	{
		$this->setHeading('COM_ES_BANNED_USERS');

		JToolbarHelper::custom('unban', 'publish', '', JText::_('COM_ES_UNBAN'));
		JToolbarHelper::custom('banPermanent', 'unpublish', '', JText::_('COM_ES_BAN_PERMANENTLY'));

		$model = ES::model('Users', array('initState' => true, 'namespace' => 'banned.listing'));

		// Get filter states.
		$ordering = $this->input->get('ordering', $model->getState('ordering'), 'default');
		$direction = $this->input->get('direction', $model->getState('direction'), 'default');


		$limit = $model->getState('limit');
		$search = $model->getState('search');

		// Get users
		$users = $model->getUsersWithState(array('limit' => $limit, 'state' => SOCIAL_USER_STATE_DISABLED));

		// Get pagination from model
		$pagination = $model->getPagination();

		$callback = $this->input->get('callback', '', 'default');

		// Prepare usergroup array separately because the usergroup title is no longer in the user object
		$usergroupsData = ES::model('Users')->getUserGroups();

		// Reformat the usergroup to what we want
		$usergroups = array();
		foreach ($usergroupsData as $row) {
			$usergroups[$row->id] = $row->title;
		}

		$this->set('ordering', $ordering);
		$this->set('limit', $limit);
		$this->set('direction', $direction);
		$this->set('search', $search);
		$this->set('pagination', $pagination);
		$this->set('users', $users);

		echo parent::display('admin/users/banned/default');
	}

	/**
	 * Allows viewer to download data
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function downloadData()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Download');
		$table->load($id);

		return $table->showArchiveDownload();
	}

	/**
	 * Renders a list of pending users
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function downloads()
	{
		$this->setHeading('COM_ES_DOWNLOAD_REQUESTS');

		JToolbarHelper::deleteList('', 'deleteDownload');
		JToolbarHelper::trash('purgeAll', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_PURGE_ALL'), false);

		// Get the user's model.
		$model = ES::model('Download', array('initState' => true, 'namespace' => 'users.downloads'));
		$requests = $model->getRequests();
		$pagination = $model->getPagination();

		$this->set('requests', $requests);
		$this->set('pagination', $pagination);

		parent::display('admin/users/downloads/default');
	}

	/**
	 * Post process after account is activated
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activate()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post processing after a user is blocked or unblocked
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function togglePublish()
	{
		// Disallow access
		if(!$this->authorise('easysocial.access.users'))
		{
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users');
		$this->close();
	}

	/**
	 * Post processing after a user profile has changed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function switchProfile()
	{
		// Disallow access
		if (!$this->authorise('easysocial.access.users')) {
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users');
		$this->close();
	}

	/**
	 * Displays user form
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function form($errors = null)
	{
		// Disallow access
		if (!$this->authorise('easysocial.access.users')) {
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Set any errors
		if ($this->hasErrors()) {
			$this->info->set($this->getMessage());
		}

		// Get the user from the request.
		$id = $this->input->get('id', 0, 'int');
		$active = $this->input->get('active', 'profile', 'word');

		$this->set('active', $active);

		JToolbarHelper::apply('apply', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE'), false, false);
		JToolbarHelper::save('save', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_CLOSE'));
		JToolbarHelper::save2new('savenew', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_SAVE_AND_NEW'));
		JToolbarHelper::cancel('cancel', JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_CANCEL'));

		// Set page heading
		if (!$id) {
			echo $this->newForm($errors);
		} else {
			echo $this->editForm($id, $errors);
		}
	}

	/**
	 * Displays the new user form
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function newForm($errors = null)
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_CREATE_USER', 'COM_EASYSOCIAL_DESCRIPTION_CREATE_USER');

		// Get the profile id
		$profileId = $this->input->get('profileId', 0, 'int');

		$model = ES::model('Profiles');
		$profiles = $model->getProfiles();

		$profile = ES::table('Profile');

		// Load front end's language file
		ES::language()->loadSite();

		// If profile id is already loaded, just display the form
		if ($profileId) {
			$profile->load($profileId);

			// Get the steps model
			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($profile->getWorkflow()->id, SOCIAL_TYPE_PROFILES);

			// Init fields library
			$fields = ES::fields();

			// New user doesn't need privacy here, hence we manually override ths privacy to be false
			$fields->init(array('privacy' => false));

			// Get custom fields model.
			$fieldsModel = ES::model('Fields');

			// Build the arguments
			$user = new SocialUser();
			$post = $this->input->post->getArray();
			$args = array(&$post, &$user, $errors);

			$conditionalFields = array();

			// Get the custom fields for each of the steps.
			foreach ($steps as &$step) {
				$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id));

				// Trigger onEdit for custom fields.
				if (!empty($step->fields)) {
					$fields->trigger('onAdminEdit', SOCIAL_FIELDS_GROUP_USER, $step->fields, $args);
				}

				foreach ($step->fields as $field) {
					if ($field->isConditional()) {
						$conditionalFields[$field->id] = false;
					}
				}
			}

			if ($conditionalFields) {
				$conditionalFields = json_encode($conditionalFields);
			} else {
				$conditionalFields = false;
			}

			$this->set('steps', $steps);
			$this->set('conditionalFields', $conditionalFields);
		}

		$this->set('profile'	, $profile);
		$this->set('profileId'	, $profileId);
		$this->set('profiles', $profiles);

		return parent::display('admin/users/form/new');
	}

	/**
	 * Displays the edit form of user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function editForm($id, $errors = null)
	{
		// Get the user object
		$user = ES::user($id);

		// Get the user's profile
		$profile = $user->getProfile();

		$this->setHeading($user->getName() . ' (' . $profile->get('title') . ')');
		$this->setDescription(JText::_('COM_EASYSOCIAL_DESCRIPTION_EDIT_USER'));

		// Load up language file from the front end.
		ES::language()->loadSite();

		// Get a list of access rules that are defined for this
		$accessModel = ES::model('Access');

		// Get user's privacy
		$privacyLib = $user->getPrivacy();
		$privacyData = $privacyLib->getData();
		$privacy = array();


		// Update the privacy data with proper properties.
		if ($privacyData) {

			foreach ($privacyData as $group => $items) {

				// We do not want to show field privacy rules here because it does not make sense for user to set a default value
				// Most of the fields only have 1 and it is set in Edit Profile page
				if ($group === 'field') {
					continue;
				}

				foreach ($items as &$item) {
					$rule = strtoupper(ESJString::str_ireplace('.', '_', $item->rule));
					$groupKey = strtoupper($group);

					$item->groupKey = $groupKey;
					$item->label = JText::_('COM_EASYSOCIAL_PRIVACY_LABEL_' . $groupKey . '_' . $rule);
					$item->tips = JText::_('COM_EASYSOCIAL_PRIVACY_TIPS_' . $groupKey . '_' . $rule);
				}

				$privacy[$group] = $items;
			}
		}


		// Get the steps model
		$stepsModel = ES::model('Steps');
		$steps = $stepsModel->getSteps($user->getWorkflow()->id, SOCIAL_TYPE_PROFILES);

		// Get custom fields model.
		$fieldsModel = ES::model('Fields');

		// Get custom fields library.
		$fields = ES::fields();

		// Manually set the user here because admin edit might be editing a different user
		$fields->setUser($user);

		$conditionalFields = array();

		// Get the custom fields for each of the steps.
		foreach ($steps as &$step) {

			$step->fields = $fieldsModel->getCustomFields(array('step_id' => $step->id, 'data' => true, 'dataId' => $user->id, 'dataType' => SOCIAL_TYPE_USER));

			// Trigger onEdit for custom fields.
			if (!empty($step->fields)) {
				$post = $this->input->getArray('post');
				$args = array(&$post, &$user, $errors);

				$fields->trigger('onAdminEdit', SOCIAL_FIELDS_GROUP_USER, $step->fields, $args);
			}

			foreach ($step->fields as $field) {
				if ($field->isConditional()) {
					$conditionalFields[$field->id] = false;
				}
			}
		}

		if ($conditionalFields) {
			$conditionalFields = json_encode($conditionalFields);
		} else {
			$conditionalFields = false;
		}

		// Get user badges
		$badges = $user->getBadges();

		// Get the user notification settings
		$alertLib = ES::alert();
		$alerts = $alertLib->getUserSettings($user->id);

		// Hardcode the groups
		$groups = array('system', 'others');

		// filter the alerts to remove the alerts for those disabled features. #717
		$filteredAlerts = array();

		if ($alerts) {
			foreach ($groups as $group) {

				$filteredAlerts[$group] = array();

				if (isset($alerts[$group])) {
					foreach ($alerts[$group] as $element => $alert) {

						if (($element == 'albums' || $element == 'photos') && !$this->config->get('photos.enabled')) {
							continue;
						}

						if ($element == 'broadcast' && !$this->config->get('notifications.broadcast.popup')) {
							continue;
						}

						if ($element == 'conversations' && !$this->config->get('conversations.enabled')) {
							continue;
						}

						if ($element == 'events' && !$this->config->get('events.enabled')) {
							continue;
						}

						if ($element == 'groups' && !$this->config->get('groups.enabled')) {
							continue;
						}

						if ($element == 'pages' && !$this->config->get('pages.enabled')) {
							continue;
						}

						if ($element == 'videos' && !$this->config->get('video.enabled')) {
							continue;
						}

						if (($element == 'badges') && !$this->config->get('badges.enabled')) {
							continue;
						}

						if ($element == 'friends' && !$this->config->get('friends.enabled')) {
							continue;
						}

						if ($element == 'polls' && !$this->config->get('polls.enabled')) {
							continue;
						}

						$filteredAlerts[$group][$element] = $alert;
					}
				}
			}
		}

		// Get user points history
		$pointsModel = ES::model('Points');
		$pointsHistory = $pointsModel->getHistory($user->id, array('limit' => 20));
		$pointsPagination = $pointsModel->getPagination();

		// Get user's groups
		$userGroups = array_keys($user->groups);

		// We need to hide the guest user group that is defined in com_users options.
		// Public group should also be hidden.
		$userOptions = JComponentHelper::getComponent('com_users')->params;

		$defaultRegistrationGroup = $userOptions->get('new_usertype');
		$guestGroup = array(1, $userOptions->get('guest_usergroup'));

		// Retrieve the current user profile robots indexing value
		$robots = $user->getUserRobots();

		$this->set('conditionalFields', $conditionalFields);
		$this->set('userGroups', $userGroups);
		$this->set('guestGroup', $guestGroup);
		$this->set('pointsHistory', $pointsHistory);
		$this->set('alerts', $filteredAlerts);
		$this->set('privacy', $privacy);
		$this->set('badges', $badges);
		$this->set('steps', $steps);
		$this->set('user', $user);
		$this->set('robots', $robots);

		return parent::display('admin/users/form/edit');
	}

	/**
	 * Gets triggered when the user is approved
	 *
	 * @param	SocialUser	The user objct.
	 */
	public function approve($user)
	{
		// Disallow access
		if(!$this->authorise('easysocial.access.users'))
		{
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users&layout=pending');
	}

	/**
	 * Gets triggered when the apply button is clicked.
	 *
	 * @param	Socialuser	The user objct.
	 */
	public function apply(&$user)
	{
		// Disallow access
		if(!$this->authorise('easysocial.access.users'))
		{
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$errors 	= $this->getErrors();

		if($errors)
		{

		}

		$this->redirect('index.php?option=com_easysocial&view=users&id=' . $user->id . '&layout=form');
	}

	/**
	 * Post process after saving a user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function store($task, $user)
	{
		// Disallow access
		if (!$this->authorise('easysocial.access.users')) {
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Enqueue the message
		$this->info->set($this->getMessage());

		if ($this->hasErrors()) {
			return $this->form($user);
		}

		$active = $this->input->get('active', '', 'word');
		$active = $active ? '&active=' . $active : '';

		$redirect = 'index.php?option=com_easysocial&view=users' . $active;

		if ($task == 'apply') {
			$redirect .= '&layout=form&id=' . $user->id;
		}

		if ($task == 'savenew') {
			$redirect = 'index.php?option=com_easysocial&view=users&layout=form&profileId=' . $user->getProfile()->id;
		}

		return $this->redirect($redirect);
	}

	/**
	 * Post process after a badge has been removed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeBadge()
	{
		$this->info->set($this->getMessage());

		$userId = $this->input->get('userid', 0, 'int');

		$this->redirect('index.php?option=com_easysocial&view=users&layout=form&id=' . $userId);
	}

	/**
	 * Reject a user's registration application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reject()
	{
		// Disallow access
		if(!$this->authorise('easysocial.access.users'))
		{
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users&layout=pending');
	}

	/**
	 * Post processing after user is assigned into a group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assign()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post processing after user is deleted
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		// Disallow access
		if(!$this->authorise('easysocial.access.users'))
		{
			$this->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post process after resending activation email
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function resendActivate()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post process after resetting points
	 *
	 * @since	1.4.7
	 * @access	public
	 */
	public function resetPoints()
	{
		$this->info->set($this->getMessage());

		return $this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post process after points has been inserted for user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function insertPoints()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post process after badge has been inserted for user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function insertBadge()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users');
	}

	/**
	 * Post process after unbanning user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function unban()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users&layout=banned');
	}

	/**
	 * Post process after banning user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function banPermanent()
	{
		$this->info->set($this->getMessage());

		$this->redirect('index.php?option=com_easysocial&view=users&layout=banned');
	}
}
