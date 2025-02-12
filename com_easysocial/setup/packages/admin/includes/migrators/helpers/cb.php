<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(SOCIAL_LIB . '/migrators/helpers/info.php');
require_once(dirname(__FILE__) . '/helper.php');

class SocialMigratorHelperCB extends SocialMigratorHelper
{
	/**
	 * The unique name of this extension
	 * @var string
	 */
	public $name = 'com_comprofiler';

	/**
	 * The steps available for this migrator.
	 * @var Array
	 */
	public $steps = null;


	/**
	 * Custom fields mapping against EasySocial fields.
	 * @var Array
	 */
	public $mapping = array(
								'checkbox'		=> 'checkbox',
								'multicheckbox'	=> 'checkbox',
								'radio'			=> 'checkbox',
								'date' 			=> 'datetime',
								'datetime'		=> 'datetime',
								'select' 		=> 'dropdown',
								'multiselect'	=> 'multilist',
								'emailaddress'	=> 'email',
								'primaryemailaddress' => 'email',
								'editorta'		=> 'textarea',
								'textarea' 		=> 'textarea',
								'text'			=> 'textbox',
								'integer'		=> 'textbox',
								'password'		=> 'textbox',
								'webaddress'	=> 'url',
								'image'			=> 'file',
								'hidden'		=> 'text',
								'delimiter' 	=> 'separator'
								);

	public $accessMapping 	= null;


	public function __construct()
	{
		parent::__construct();

		$this->steps[] = 'profile';
		$this->steps[] = 'users';
		$this->steps[] = 'connection';
		$this->steps[] = 'avatars';
	}


	public function cbCoreFieldType()
	{
		$cbCoreFields = array('checkbox',
							'multicheckbox',
							'date',
							'datetime',
							'select',
							'multiselect',
							'emailaddress',
							'primaryemailaddress',
							'editorta',
							'textarea',
							'text',
							'integer',
							'radio',
							'webaddress',
							'image',
							'password',
							'hidden',
							'delimiter');

		return $cbCoreFields;
	}

	/**
	 * Retrieves a list of custom fields that is created within JomSocial
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getCustomFields($includeESFieldId = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*, a.`fieldid` as `id`';

		if ($includeESFieldId) {
			$query .= ', d.`uid` as `esFieldId`';
		}

		$query .= ' from `#__comprofiler_fields` as a';
		$query .= ' 	inner join `#__comprofiler_tabs` as b on a.`tabid` = b.`tabid`';
		$query .= ' 	inner join `#__comprofiler_plugin` as c on a.`pluginid` = c.`id`';


		if ($includeESFieldId) {
			$query .= '		inner join `#__social_migrators` as d on a.`fieldid` = d.`oid` and d.`element` = ' . $db->Quote('fields') . ' and d.`component` = ' . $db->Quote($this->name);
		}

		$query .= ' where a.`sys` != ' . $db->Quote('1');
		$query .= ' and b.`fields` = 1';
		$query .= ' and c.`element` = ' . $db->Quote('cb.core');
		// $query .= ' and b.`displaytype` = ' . $db->Quote('tab');
		$query .= ' and a.`table` = ' . $db->Quote('#__comprofiler');

		//
		$cbFields = $this->cbCoreFieldType();
		$tmpString = '';
		foreach($cbFields as $cbf) {
			$tmpString .= ($tmpString) ? ',' . $db->Quote($cbf) : $db->Quote($cbf);
		}

		$query .= ' and a.`type` IN (' . $tmpString . ')';

		$sql->raw($query);

		$db->setQuery($sql);

		$fields	= $db->loadObjectList();
		return $fields;
	}

	/**
	 * Retrieve the version of Community Builder
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getVersion()
	{
		$exists = $this->isComponentExist();

		if (!$exists->isvalid) {
			return false;
		}

		// check JomSocial version.
		$xml = JPATH_ROOT . '/administrator/components/com_comprofiler/comprofiler.xml';

		$parser = ES::get('Parser');
		$parser->load($xml);

		$version = $parser->xpath('version');
		$version = (float) $version[0];

		return $version;
	}

	/**
	 * Determines if Community Builder is installed
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isInstalled()
	{
		$file 	= JPATH_ROOT . '/components/com_comprofiler/comprofiler.php';

		if (! JFile::exists($file)) {
			return false;
		}

		return true;
	}

	/*
	 * return object with :
	 *     isvalid  : true or false
	 *     messsage : string.
	 *     count    : integer. item count to be processed.
	 */
	public function isComponentExist()
	{
		$obj = new stdClass();
		$obj->isvalid = false;
		$obj->count = 0;
		$obj->message = '';

		if (!$this->isInstalled()) {
			$obj->message = JText::_('COM_EASYSOCIAL_MIGRATORS_CB_NOT_INSTALLED');
			return $obj;
		}

		// If the file exists, we assume that CB is installed.
		$obj->isvalid = true;
		$obj->count = $this->getItemCount();

		return $obj;
	}

	/**
	 * Retrieves the total items needed to be migrated.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getItemCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the total steps needed
		$total = count($this->steps);

		// users
		$query = 'select count(1) as `total`';
		$query .= ' 	from `#__comprofiler` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`user_id` = b.`oid` and b.`element` = ' . $db->Quote('users') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// connection
		$query = 'select count(1) as `total`';
		$query .= ' from `#__comprofiler_members` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`referenceid` = b.`oid` and b.`element` = ' . $db->Quote('connection') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`accepted` = ' . $db->Quote('1');

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		// avatar
		$query = 'select count(1) as `total`';
		$query .= ' from `#__comprofiler` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`user_id` = b.`oid` and b.`element` = ' . $db->Quote('avatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`avatar` is not null';
		$query .= ' and a.`avatarapproved` = 1';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$numTotal = $db->loadResult();
		$numTotal = ($numTotal > 0) ? ceil($numTotal / $this->limit) : 0;
		$total = $total + $numTotal;

		return $total;
	}

	/**
	 * Performs the migration process
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function process($item)
	{
		$obj = new stdClass();

		if (empty($item)) {
			$item = $this->steps[0];
		}

		$result = null;

		switch ($item)
		{
			case 'profile':
				$result	= $this->processProfile();
				break;

			case 'users':
				$result	= $this->processUsers();
				break;

			case 'connection':
				$result = $this->processConnection();
				break;

			case 'avatars':
				$result = $this->processAvatar();
				break;

			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if (is_null($result)) {
			$keys 		= array_keys($this->steps, $item);
			$curSteps 	= $keys[0];

			if (isset($this->steps[ $curSteps + 1])) {
				$item = $this->steps[ $curSteps + 1];
			} else {
				$item = null;
			}

			$obj->continue = (is_null($item)) ? false : true ;
			$obj->item = $item;
			$obj->message = ($obj->continue) ? 'Checking for next item to migrate....' : 'No more item found.';

			return $obj;
		}

		$obj->continue = true;
		$obj->item = $item;
		$obj->message = implode('<br />', $result->message);

		return $obj;
	}


	private function processProfile()
	{
		$db = ES::db();
		$sql = $db->sql();

		// check if cb default profile already processed or not.
		$query = 'select b.`id` from `#__social_migrators` as b';
		$query .= ' where b.`element` = ' . $db->Quote('profiles') . ' and b.`component` = ' . $db->Quote($this->name);

		$sql->clear();
		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadResult();

		if ($result) {
			//already process. dont process again.
			return null;
		}

		// create new profile without steps.
		$newProfileId = $this->createProfileItem();

		//now we need to install the profiles steps.
		$newSteps = $this->createDefaultItems($newProfileId, 'Community Builder Default', SOCIAL_TYPE_USER, $this->name);

		// now we need to add the tab from cb as step in es.
		$cbFields = $this->getCustomFields();

		$cbTabFields = array();
		foreach ($cbFields as $cbField) {
			$cbTabFields[ $cbField->tabid ][] = $cbField;
		}

		$nextSeq = count($newSteps) + 1;

		$this->processStepFields($nextSeq, $cbTabFields, $newProfileId);

		$this->addPrivacyMap($newProfileId, 'profile');

		$this->info->setInfo('Custom fields from Community Builder has migrated succefully into EasySocial.');
		return $this->info;

	}


	private function processStepFields($nextSeq, $cbTabFields, $esProfileId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// we need to create workflow data as well.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->load(array('uid' => $esProfileId, 'type' => SOCIAL_TYPE_USER));

		$workflowId = 0;

		if ($workflowMap->id) {
			$workflowId = $workflowMap->workflow_id;
		} else {
			// there is no existing workflow for this profile.
			// lets create one.
			$workflowId = $this->createWorkflow($esProfileId, 'Community Builder Default Workflow', SOCIAL_TYPE_USER);
		}

		foreach ($cbTabFields as $cbTabId => $cbFields) {
			// let add the tab as step in es

			$cbTab = $this->getCBTab($cbTabId);

			$stepTable = ES::table('FieldStep');

			$stepTable->title = $cbTab->title;
			$stepTable->description = $cbTab->description;
			$stepTable->sequence = $nextSeq;
			$stepTable->state = $cbTab->enabled;
			$stepTable->visible_registration = true;
			$stepTable->visible_edit = true;
			$stepTable->visible_display = true;
			$stepTable->uid = $esProfileId;
			$stepTable->type = SOCIAL_TYPE_PROFILES;
			$stepTable->workflow_id = $workflowId;

			$stepTable->store();

			$this->log('steps', $cbTabId, $stepTable->id);

			//now we associate the fields into this new step
			foreach ($cbFields as $cbField) {
				$this->processField($stepTable->id, $cbField, $esProfileId);
			}

			$nextSeq++;
		}


	}

	private function processField($stepId, $cbField, $esProfileId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Load profile
		$profile = ES::table('Profile');
		$workflow = $profile->getWorkflow();

		if ($cbField->type == 'text' && (strpos(strtolower($cbField->name) , 'givenname') !== false || strpos(strtolower($cbField->name) , 'familyname') !== false)) {

			// this means this is a givenname / familyname mapping to default joomla_fullname field.
			// there is no need to create this field.

			$query = 'select `id` from `#__social_fields`';
			$query .= ' where `unique_key` = ' . $db->Quote('JOOMLA_FULLNAME');
			$query .= ' and `step_id` = (select `id` from `#__social_fields_steps` where `workflow_id` = ' . $db->Quote($workflow->id) ;
			$query .= '                             and `type` = ' . $db->Quote(SOCIAL_TYPE_PROFILES);
			$query .= '                     order by `id` ASC limit 1)';

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);

			$jfullnameField = $db->loadResult();

			if ($jfullnameField) {
				// log into migrator table
				$this->log('fields', $cbField->id, $jfullnameField);
				return $jfullnameField;
			}
		}


		//map address
		if ((($cbField->type == 'text' || $cbField->type == 'select') && strtolower($cbField->name) == 'cb_state')
		||  ($cbField->type == 'text' && strtolower($cbField->name) == 'cb_city')
		||  ($cbField->type == 'text' && strtolower($cbField->name) == 'cb_zip')
		||  (($cbField->type == 'textarea' || $cbField->type == 'text') && strtolower($cbField->name) == 'cb_address')
		||  (($cbField->type == 'textarea' || $cbField->type == 'text') && strtolower($cbField->name) == 'cb_street1')
		||  (($cbField->type == 'textarea' || $cbField->type == 'text') && strtolower($cbField->name) == 'cb_street2')
		||  (($cbField->type == 'text' || $cbField->type == 'select') && strtolower($cbField->name) == 'cb_country')) {
			// this means this is a givenname / familyname mapping to default joomla_fullname field.
			// there is no need to create this field.

			$query = 'select `id` from `#__social_fields`';
			$query .= ' where (`unique_key` LIKE ' . $db->Quote('%ADDRESS%');
			$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CB_STATE');
			$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CB_CITY');
			$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CB_ZIP');
			$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CB_COUNTRY');
			$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CB_STREET1');
			$query .= ' 	OR `unique_key` = ' . $db->Quote('FIELD_CB_STREET2') . ')';
			$query .= ' and `step_id` IN (select `id` from `#__social_fields_steps` where `workflow_id` = ' . $db->Quote($workflow->id) ;
			$query .= '							and `type` = ' . $db->Quote(SOCIAL_TYPE_PROFILES) . ')';
			$query .= ' order by `id` DESC limit 1';

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);

			$addressField = $db->loadResult();

			if ($addressField) {
				// log into migrator table
				$this->log('fields', $cbField->id, $addressField);
				return $addressField;
			}
		}


		$esFieldApp = $this->getESFieldApp($cbField);

		$fieldElement = $esFieldApp->element;
		$appId = $esFieldApp->appid;


		$fieldTable = ES::table('Field');


		$uniqueKey = str_replace(' ', '_', $cbField->name);
		$uniqueKey = 'FIELD_' . $uniqueKey;
		$uniqueKey = strtoupper($uniqueKey);

		$title = $cbField->title;
		if (ESJString::strpos($title, '_UE_') !== false) {
			$title = ESJString::str_ireplace('_UE_', '', $title);
		}

		$fieldTable->title = $title;
		$fieldTable->description = $cbField->description;
		$fieldTable->default = $cbField->default;
		$fieldTable->unique_key = $uniqueKey;

		// Set the app id.
		$fieldTable->app_id = $appId;
		$fieldTable->step_id = $stepId;
		$fieldTable->state = $cbField->published;
		$fieldTable->searchable = $cbField->searchable;
		$fieldTable->required = $cbField->required;

		$fieldTable->visible_registration = ($cbField->registration) ? 1 : 0 ;
		$fieldTable->visible_edit = $cbField->readonly ? 0 : 1;
		$fieldTable->visible_display = $cbField->profile ? 1 : 0;
		$fieldTable->ordering = $cbField->ordering;

		$fieldTable->display_title = 1;
		$fieldTable->display_description = 1; // if title display, then we display the description as well

		// need to get the default params from ES fields.
		$params = array();
		switch ($fieldElement)
		{
			case 'country':
				$params['select_type'] = 'dropdown';
				break;

			case 'multilist':
				$params['multiple'] = 1;
				break;

			case 'textbox':

				$params['min'] = 0;
				$params['max'] = 0;

				if ($cbField->maxlength) {
					$params['min'] = 1;
					$params['max'] = $cbField->maxlength;
				}

				break;

			case 'textarea':

				$params['min'] = 0;
				$params['max'] = 0;

				if ($cbField->maxlength) {
					$params['min'] = 1;
					$params['max'] = $cbField->maxlength;
				}

				$params['placeholder'] = $cbField->description;

				break;

			case 'birthday':

				$params['calendar'] = 1;
				$params['form_format'] = 1;

				break;

			case 'url':

				$params['placeholder'] = $cbField->description;
				$params['linkable'] = 1;
				$params['nofollow'] = 1;
				$params['target'] = "_blank";

				break;

			case 'email':

				$params['disallowed'] = "";
				$params['forbidden'] = "";

				break;

			case 'datetime':

				$params['calendar'] = 1;
				$params['form_format'] = 1;
				break;

			default:
				break;

		}

		$fieldTable->params = ($params) ? ES::json()->encode($params) : '';

		// Store the field item.
		$state = $fieldTable->store();

		if (! $state) {
			return false;
		}

		$newFieldId = $fieldTable->id;

		//lets check if this field is a dropdown list / multilist/ checkbox or radio button.
		$hasOptions = array(
						'dropdown',
						'checkbox',
						'multilist',
						'gender',
						'country'
						);

		if (in_array($fieldElement, $hasOptions)) {
			$query = 'select * from `#__comprofiler_field_values` where `fieldid` = ' . $db->Quote($cbField->id);
			$query .= ' order by `ordering`';

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);

			$jsOptions = $db->loadObjectList();

			$valueKey  = 'items';

			if ($jsOptions) {
				$insert = 'insert into `#__social_fields_options` (`parent_id`, `key`, `title`, `value`) values ';

				$count = 1;
				foreach ($jsOptions as $jsOption) {
					$fieldTitle = $jsOption->fieldtitle;
					$fieldTitle = ESJString::str_ireplace('_UE_', '', $fieldTitle);
					// $fieldTitleVal = strtolower($fieldTitle);
					$fieldTitleVal = $fieldTitle;
					$fieldTitle = ucfirst($fieldTitleVal);

					$insert .= '(' . $db->Quote($newFieldId) . ',' . $db->Quote($valueKey) . ',' . $db->Quote($fieldTitle) . ',' . $db->Quote($fieldTitleVal) . ')';
					if ($count < count($jsOptions)) {
						$insert .= ',';
						$count++;
					}
				}

				// now let do the insert
				$sql->clear();
				$sql->raw($insert);

				$db->setQuery($sql);
				$db->query();
			}
		}


		// log into migrator table
		$this->log('fields', $cbField->id, $fieldTable->id);

		return $fieldTable->id;
	}

	private function addItemPrivacy($command, $esUid, $esUType, $cbUserId)
	{
		static $defaultESPrivacy = array();

		$db = ES::db();
		$sql = $db->sql();


		if (! isset($defaultESPrivacy[ $command ])) {
			$db 	= ES::db();
			$sql 	= $db->sql();

			$commands = explode('.', $command);

			$element = $commands[0];
			$rule  	 = $commands[1];

			$query = 'select `id`, `value` from `#__social_privacy`';
			$query .= ' where `type` = ' . $db->Quote($element);
			$query .= ' and `rule` = ' . $db->Quote($rule);

			$sql->raw($query);
			$db->setQuery($sql);

			$defaultESPrivacy[ $command ] = $db->loadObject();
		}

		$defaultPrivacy = $defaultESPrivacy[ $command ];
		$privacyValue 	= $defaultPrivacy->value;


		$esPrivacyItem = ES::table('PrivacyItems');

		$esPrivacyItem->privacy_id = $defaultPrivacy->id;
		$esPrivacyItem->user_id = $cbUserId;
		$esPrivacyItem->uid = $esUid;
		$esPrivacyItem->type = $esUType;
		$esPrivacyItem->value = $privacyValue;

		$esPrivacyItem->store();
	}

	private function getESFieldApp($cbField)
	{
		static $_cache = array();

		if (isset($_cache[ $cbField->id ])) {
			return $_cache[ $cbField->id ];
		}


		// let try to get from user mapping
		$appTable = ES::table('App');

		$esFid = isset($this->userMapping[ $cbField->id ]) ? $this->userMapping[ $cbField->id ] : 0;

		if ($esFid) {
			if (!isset($_cache[ $cbField->id ])) {
				$appTable->load($esFid);

				if ($appTable->element && $appTable->id) {
					$obj = new stdClass();
					$obj->element = $appTable->element;
					$obj->appid = $appTable->id;

					$_cache[ $cbField->id ] = $obj;
				}
			}
		}

		return $_cache[ $cbField->id ];
	}



	private function getCBTab($tabid)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select * from `#__comprofiler_tabs` where `tabid` = ' . $db->Quote($tabid);
		$sql->raw($query);

		$db->setQuery($sql);
		$result = $db->loadObject();

		$title = str_replace('_UE_', '', $result->title);
		$title = str_replace('_', ' ', $title);

		$result->title = $title;

		return $result;
	}


	private function createProfileItem()
	{
		$db = ES::db();

		$newProfile = new stdClass();

		// default setting
		$data = array();
		$data['delete_account'] = "0";
		$data['theme'] = "wireframe";
		$data['registration'] = "approvals";
		$data['email'] = array("users" => "1", "moderators" => "1");

		$newProfile->title = 'Community Builder';
		$newProfile->alias = '';
		$newProfile->description = 'Default profile for community builder migration.';
		$newProfile->gid = ES::json()->encode(array("2"));
		$newProfile->default = '';
		$newProfile->default_avatar = '';
		$newProfile->created = ES::date()->toMySQL();
		$newProfile->state = "1";
		$newProfile->params = ES::json()->encode($data);
		$newProfile->registration = 0; // for profiles that migrated from JS, we dont want to appear in registration form.
		$newProfile->ordering = $this->profileLastOrdering() + 1;

		$db->insertObject('#__social_profiles', $newProfile, 'id');
		$newId = $db->insertID();

		$tbl = ES::table('Profile');
		$tbl->load($newId);

		// Filter the name to ensure that it is a valid permalink
		$permalink 	= JFilterOutput::stringURLSafe($newProfile->title);
		$tbl->alias = $tbl->id . ':' . $permalink;
		$tbl->store();

		// log records
		$this->log('profiles', '0', $newId);

		return $newId;
	}



	private function processAvatar()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__comprofiler` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`user_id` = b.`oid` and b.`element` = ' . $db->Quote('avatar') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`avatar` is not null';
		$query .= ' and a.`avatarapproved` = 1';

		$query .= ' ORDER BY a.`user_id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);


		$cbAvatars = $db->loadObjectList();

		if (count($cbAvatars) <= 0) {
			return null;
		}


		foreach ($cbAvatars as $cbAvatar) {
			if (!$cbAvatar->avatar) {
				// no need to process further.
				$this->log('avatar', $cbAvatar->user_id , $cbAvatar->user_id);

				$this->info->setInfo('User ' . $cbAvatar->user_id . ' is using default avatar. no migration is needed.');
				continue;
			}

			$userid = $cbAvatar->user_id;

			// images/avatar/c7a88f6daec02aea3fd3bc4e.jpg
			$imagePath = JPATH_ROOT . '/images/comprofiler/' . $cbAvatar->avatar;

			$tmp 		= explode('/', $imagePath);
			$filename 	= $tmp[ count($tmp) - 1 ];

			if (!JFile::exists($imagePath)) {
				$this->log('avatar', $cbAvatar->user_id , $cbAvatar->user_id);

				$this->info->setInfo('User ' . $cbAvatar->user_id . ' the avatar image file is not found from the server. Process aborted.');
				continue;
			}

			// lets copy this file to tmp folder 1st.
			$tmp 			= JFactory::getConfig()->get('tmp_path');
			$tmpImageFile 	= $tmp . '/' . md5(JFactory::getDate()->toSql());
			JFile::copy($imagePath , $tmpImageFile);

			$image = ES::image();
			$image->load($tmpImageFile);

			$avatar	= ES::avatar($image, $userid, SOCIAL_TYPE_USER);

			// Check if there's a profile photos album that already exists.
			$albumModel	= ES::model('Albums');

			// Retrieve the user's default album
			$album 	= $albumModel->getDefaultAlbum($userid , SOCIAL_TYPE_USER , SOCIAL_ALBUM_PROFILE_PHOTOS);

			// we need to update the album user_id to this current user.
			$album->user_id = $userid;
			$album->store();

			$photo = ES::table('Photo');
			$photo->uid = $userid;
			$photo->type = SOCIAL_TYPE_USER;
			$photo->user_id = $userid;
			$photo->album_id = $album->id;
			$photo->title = $filename;
			$photo->caption = '';
			$photo->ordering = 0;

			// We need to set the photo state to "SOCIAL_PHOTOS_STATE_TMP"
			$photo->state = SOCIAL_PHOTOS_STATE_TMP;

			// Try to store the photo first
			$state = $photo->store();

			// Push all the ordering of the photo down
			$photosModel = ES::model('photos');
			$photosModel->pushPhotosOrdering($album->id , $photo->id);

			// Render photos library
			$photoLib = ES::get('Photos' , $image);
			$storage = $photoLib->getStoragePath($album->id, $photo->id);
			$paths = $photoLib->create($storage);

			// Create metadata about the photos
			foreach ($paths as $type => $fileName) {
				$meta = ES::table('PhotoMeta');
				$meta->photo_id = $photo->id;
				$meta->group = SOCIAL_PHOTOS_META_PATH;
				$meta->property = $type;
				$meta->value = $storage . '/' . $fileName;

				$meta->store();
			}

			// Create the avatars now, but we do not want the store function to create stream.
			// so we pass in the option. we will create the stream ourown.
			$options = array('addstream' => false);
			$avatar->store($photo, $options);

			// add photo privacy
			$this->addItemPrivacy('photos.view', $photo->id, SOCIAL_TYPE_PHOTO, $cbAvatar->user_id, '0');

			// add photo stream
			$photo->addPhotosStream('uploadAvatar', $cbAvatar->lastupdatedate);

			// log into mgirator
			$this->log('avatar', $cbAvatar->user_id , $cbAvatar->user_id);

			$this->info->setInfo('User avatar ' . $cbAvatar->user_id . ' is now migrated into EasySocial.');

		}//end foreach


		return $this->info;

	}

	private function processConnection()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.*';
		$query .= ' from `#__comprofiler_members` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`referenceid` = b.`oid` and b.`element` = ' . $db->Quote('connection') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';
		$query .= ' and a.`accepted` = ' . $db->Quote('1');
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$cbConns = $db->loadObjectList();

		if (count($cbConns) <= 0) {
			return null;
		}

		foreach ($cbConns as $cbConn) {
			//let check if this two user already a fren in ES or not.
			$actor 	= $cbConn->referenceid;
			$target = $cbConn->memberid;

			$query = 'select a.`id` from `#__social_friends` as a';
			$query .= ' where (a.`actor_id` = ' . $db->Quote($actor) . ' and a.`target_id` = ' . $db->Quote($target);
			$query .= ' OR a.`target_id` = ' . $db->Quote($actor) . ' and a.`actor_id` = ' . $db->Quote($target) . ')';

			$sql->clear();
			$sql->raw($query);

			$db->setQuery($sql);
			$esFriends = $db->loadObjectList();

			if (count($esFriends) > 0) {
				// this mean they are already have connection in ES. lets log into mgirator table.
				foreach ($esFriends as $friend) {
					$this->log('connection', $cbConn->referenceid, $friend->id);
				}

				$this->info->setInfo('User \'' . $actor . '\' already connected with user \'' . $target . '\' so no migration need for these two users.');
				continue;
				// return $this->info;
			}

			// lets add into social friend table
			$esFriendTbl = ES::table('Friend');

			$esFriendTbl->actor_id = $actor;
			$esFriendTbl->target_id = $target;
			$esFriendTbl->state = ($cbConn->pending == '1') ? SOCIAL_FRIENDS_STATE_PENDING : SOCIAL_FRIENDS_STATE_FRIENDS ;
			$esFriendTbl->created = $cbConn->membersince;

			$esFriendTbl->store();
			$newFriendId = $esFriendTbl->id;

			// done. lets add into log table.
			$this->log('connection', $cbConn->referenceid, $newFriendId);

			if ($esFriendTbl->state == SOCIAL_FRIENDS_STATE_FRIENDS) {
				$stream = ES::stream();
				$streamTemplate = $stream->getTemplate();

				// Set the actor.
				$streamTemplate->setActor($actor, SOCIAL_TYPE_USER);

				// Set the context.
				$streamTemplate->setContext($esFriendTbl->id , SOCIAL_TYPE_FRIEND);

				// Set the actor.
				$streamTemplate->setTarget($target);

				// Set the verb.
				$streamTemplate->setVerb('add');

				// set to aggreate friend
				$streamTemplate->setAggregate(true);

				// set the stream creation date
				$streamTemplate->setDate($cbConn->membersince);

				$streamTemplate->setAccess('core.view');

				// Create the stream data.
				$stream->add($streamTemplate);
			}

			$this->info->setInfo('User \'' . $actor . '\' is now connected with user \'' . $target . '\' in your EasySocial.');

		}// end foreach

		return $this->info;
	}


	/**
	 * Migrates the user data from Community Builder into EasySocial
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function processUsers()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select a.*';
		$query .= ' 	from `#__comprofiler` as a';
		$query .= ' where not exists (';
		$query .= '		select b.`id` from `#__social_migrators` as b';
		$query .= ' 			where a.`user_id` = b.`oid` and b.`element` = ' . $db->Quote('users') . ' and b.`component` = ' . $db->Quote($this->name);
		$query .= ')';

		$query .= ' ORDER BY a.`user_id` ASC';
		$query .= ' LIMIT ' . $this->limit;

		$sql->raw($query);
		$db->setQuery($sql);

		$cbUsers = $db->loadObjectList();

		if (count($cbUsers) <= 0) {
			return null;
		}

		// okay lets get the only migrated profile from CB into ES.
		$esProfileId = $this->getDefaultMigratedProfileId();

		if (! $esProfileId) {
			// something not right here. abort the process.
			return null;
		}

		//json lib
		$json = ES::json();

		$cbFields = $this->getCustomFields(true);

		foreach ($cbUsers as $cbUser) {
			// we need to manually group the data first
			$dataContainer = array();

			foreach ($cbFields as $cbField) {
				$esFieldApp = $this->getESFieldApp($cbField);
				$fieldElement = $esFieldApp->element;

				if (! isset($dataContainer[$cbField->esFieldId])) {
					$esFieldData = new stdClass();
					$esFieldData->field_id = $cbField->esFieldId;
					$esFieldData->uid = $cbUser->user_id;
					$esFieldData->type = SOCIAL_TYPE_USER;
					$esFieldData->element = $fieldElement;
					$esFieldData->access = '0';
					$esFieldData->data = array();
				} else {
					$esFieldData = $dataContainer[$cbField->esFieldId];
				}


				switch($fieldElement)
				{
					case 'gender':

						$gender = 0;

						$val = $cbUser->{$cbField->tablecolumns};
						$val = ESJString::str_ireplace('_UE_', '', $val);

						if (strtolower($val) == 'male') {
							$gender = 1;
						} else if(strtolower($val) == 'female') {
							$gender = 2;
						}

						$valueObj = new stdClass();
						$valueObj->datakey = '';
						$valueObj->data = $gender;
						$valueObj->raw = $gender;

						$esFieldData->data['default'] = $valueObj;

						break;

					case 'country':
						// CB is storing the full country name. lets just take this value.
						$cbCountryValue = $cbUser->{$cbField->tablecolumns};

						$valueObj = new stdClass();
						$valueObj->datakey = '0';
						$valueObj->data = $cbCountryValue;
						$valueObj->raw 	= $cbCountryValue;

						$esFieldData->data['default'] = $valueObj;

						break;

					case 'dropdown':
					case 'textbox':
					case 'textarea':
					case 'email':
					case 'url':

						$valueObj = new stdClass();
						$valueObj->datakey = '';
						$valueObj->data = $cbUser->{$cbField->tablecolumns};
						$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

						$esFieldData->data['default'] = $valueObj;

						break;

					case 'multilist':
					case 'checkbox':

						$cbArrValues = explode("|*|", $cbUser->{$cbField->tablecolumns});

						// remove empty value elements.
						$cbArrValues = array_diff($cbArrValues, array(''));

						$valueObj = new stdClass();
						$valueObj->datakey = '';
						$valueObj->data = $json->encode($cbArrValues);
						$valueObj->raw = implode(', ', $cbArrValues);

						$esFieldData->data['default'] = $valueObj;

						break;

					case 'birthday':
					case 'datetime':

						// lets check if the value is a valid datetime or not.
						if (strtotime($cbUser->{$cbField->tablecolumns}) !== false && strtotime($cbUser->{$cbField->tablecolumns}) != -1) {
							$date = ES::date($cbUser->{$cbField->tablecolumns});

							$valueObj = new stdClass();
							$valueObj->datakey = 'date';
							$valueObj->data = $date->toSql();
							$valueObj->raw = $date->toSql();

							$esFieldData->data['date'] = $valueObj;
						}

						break;

					case 'joomla_fullname':

						if (strpos(strtolower($cbField->tablecolumns) , 'givenname') !== false) {
							$valueObj = new stdClass();
							$valueObj->datakey = 'first';
							$valueObj->data = $cbUser->{$cbField->tablecolumns};
							$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

							$esFieldData->data['first'] = $valueObj;
						}

						if (strpos(strtolower($cbField->tablecolumns) , 'familyname') !== false) {
							$valueObj = new stdClass();
							$valueObj->datakey = 'last';
							$valueObj->data = $cbUser->{$cbField->tablecolumns};
							$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

							$esFieldData->data['last'] = $valueObj;
						}

						break;

					case 'address':

						if (strtolower($cbField->tablecolumns) == 'cb_address') {
							//let split the address based on the 1st comma.
							$data = explode(',', $cbUser->{$cbField->tablecolumns});

							$add1 = array_shift($data);

							$add2 = (!empty($data)) ? implode('',$data) : '';
							// remove new line feed.
							$add2 = trim(preg_replace('/\s\s+/', ' ', $add2));

							//address 1
							$valueObj = new stdClass();
							$valueObj->datakey = 'address1';
							$valueObj->data = $add1;
							$valueObj->raw 	= $add1;
							$esFieldData->data['address1'] = $valueObj;

							//address 2
							$valueObj = new stdClass();
							$valueObj->datakey = 'address2';
							$valueObj->data = $add2;
							$valueObj->raw 	= $add2;

							$esFieldData->data['address2'] = $valueObj;
						}

						if (strtolower($cbField->tablecolumns) == 'cb_street1' || strtolower($cbField->tablecolumns) == 'cb_street2') {
							//let split the address based on the 1st comma.
							$add1 = '';
							$add2 = '';

							if (strtolower($cbField->tablecolumns) == 'cb_street1') {
								$add1 = $cbUser->{$cbField->tablecolumns};

								//address 1
								$valueObj = new stdClass();
								$valueObj->datakey = 'address1';
								$valueObj->data = $add1;
								$valueObj->raw 	= $add1;
								$esFieldData->data['address1'] = $valueObj;
							}

							if (strtolower($cbField->tablecolumns) == 'cb_street2') {
								$add2 = $cbUser->{$cbField->tablecolumns};

								//address 2
								$valueObj = new stdClass();
								$valueObj->datakey = 'address2';
								$valueObj->data = $add2;
								$valueObj->raw 	= $add2;

								$esFieldData->data['address2'] = $valueObj;
							}

						}

						if (strtolower($cbField->tablecolumns) == 'cb_state') {
							$valueObj = new stdClass();
							$valueObj->datakey = 'state';
							$valueObj->data = $cbUser->{$cbField->tablecolumns};
							$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

							$esFieldData->data['state'] = $valueObj;
						}

						if (strtolower($cbField->tablecolumns) == 'cb_city') {
							$valueObj = new stdClass();
							$valueObj->datakey = 'city';
							$valueObj->data = $cbUser->{$cbField->tablecolumns};
							$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

							$esFieldData->data['city'] = $valueObj;
						}

						if (strtolower($cbField->tablecolumns) == 'cb_zip') {
							$valueObj = new stdClass();
							$valueObj->datakey = 'zip';
							$valueObj->data = $cbUser->{$cbField->tablecolumns};
							$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

							$esFieldData->data['zip'] = $valueObj;
						}

						if (strtolower($cbField->tablecolumns) == 'cb_country') {
							// we will store country as long name.
							$valueObj = new stdClass();
							$valueObj->datakey = 'country';
							$valueObj->data = $cbUser->{$cbField->tablecolumns};
							$valueObj->raw 	= $cbUser->{$cbField->tablecolumns};

							$esFieldData->data['country'] = $valueObj;
						}

						break;

					default:
						break;

				}

				$dataContainer[$cbField->esFieldId] = $esFieldData;

			}

			// now we will do the actual saving here
			if ($dataContainer) {
				foreach($dataContainer as $dataItem) {
					$datas = $dataItem->data;
					$itemCount = count($datas);

					if ($itemCount > 1) {

						if ($dataItem->element == 'address') {
							if (! isset($datas['zip'])) {
								$valueObj = new stdClass();
								$valueObj->datakey = 'zip';
								$valueObj->data = '';
								$valueObj->raw 	= '';
								$datas['zip'] = $valueObj;
							}

							$fullAddress = $datas['address1']->raw . ' '
										. $datas['address2']->raw . ' '
										. $datas['city']->raw . ' '
										. $datas['zip']->raw . ' '
										. $datas['state']->raw . ' '
										. $datas['country']->raw;

							$valueObj = new stdClass();
							$valueObj->datakey = 'address';
							$valueObj->data = $fullAddress;
							$valueObj->raw 	= $fullAddress;
							$datas['address'] = $valueObj;


							$valueObj = new stdClass();
							$valueObj->datakey = 'latitude';
							$valueObj->data = '';
							$valueObj->raw 	= '';
							$datas['latitude'] = $valueObj;


							$valueObj = new stdClass();
							$valueObj->datakey = 'longitude';
							$valueObj->data = '';
							$valueObj->raw 	= '';
							$datas['longitude'] = $valueObj;
						}

						if ($dataItem->element == 'joomla_fullname') {
							$fullName = $datas['first']->raw . ' '
									. $datas['last']->raw;

							$valueObj = new stdClass();
							$valueObj->datakey = 'middle';
							$valueObj->data = '';
							$valueObj->raw 	= '';
							$datas['middle'] = $valueObj;

							$valueObj = new stdClass();
							$valueObj->datakey = 'name';
							$valueObj->data = $fullName;
							$valueObj->raw 	= $fullName;
							$datas['name'] = $valueObj;
						}

						foreach ($datas as $data) {
							$this->addFieldData($dataItem, $data);
						}

					} else {
						$data = array_pop($datas);
						$this->addFieldData($dataItem, $data);
					}

					// add privacy to this field
					$this->addItemPrivacy('field.' . $dataItem->element , $dataItem->field_id, 'field', $dataItem->uid, $dataItem->access);
				} // end foreach

			} //end if dataContainer

			// add user into profile map
			$this->addProfileMapping($cbUser->user_id, $esProfileId);


			// log user into migrator
			$this->log('users', $cbUser->user_id, $cbUser->user_id);
		}



		return $this->info;

	}

	private function addFieldData($dataItem, $data)
	{
		$fieldData = ES::table('FieldData');

		$fieldData->field_id = $dataItem->field_id;
		$fieldData->uid = $dataItem->uid;
		$fieldData->type = $dataItem->type;
		$fieldData->datakey = ($data->datakey == 'default') ? '' : $data->datakey;
		$fieldData->data = $data->data;
		$fieldData->raw = $data->raw;

		$fieldData->store();
	}

	private function getDefaultMigratedProfileId()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query = 'select `uid` from `#__social_migrators` where `element` = ' . $db->Quote('profiles') . ' and `component` = ' . $db->Quote($this->name);
		$sql->raw($query);

		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}


	/**
	 * Migrates the user custom fields data from CB to EasySocial
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function migrateUser($userId , $profileId)
	{
		dump($this->userMapping);
	}


	protected function log($element, $oriId, $newId)
	{
		$tbl = ES::table('Migrators');

		$tbl->oid = $oriId;
		$tbl->element = $element;
		$tbl->component = $this->name;
		$tbl->uid = $newId;
		$tbl->created = ES::date()->toMySQL();

		$tbl->store();
	}
}
