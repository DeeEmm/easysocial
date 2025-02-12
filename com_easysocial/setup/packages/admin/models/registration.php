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

ES::import('admin:/includes/model');

class EasySocialModelRegistration extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('registration');
	}

	/**
	 * Rejects a user from the whole registration process
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reject( $id )
	{
		// Load user's object.
		$user 	= ES::user( $id );

		// Try to delete the user.
		$user->delete();

		// @rule: Delete node from profile maps
		$member	= ES::table( 'ProfileMap' );
		$member->loadByUser( $user->id );
		$member->delete();

		return $state;
	}

	/**
	 * Retrieve a list of related field id's.
	 *
	 * @param	int		$fieldId	The field id.
	 * @return	Array	An array of field id's.
	 */
	public function getRelatedFieldIds( $uid , $match , $fieldId )
	{
		$db		= ES::db();

		$query	= 'SELECT c.' . $db->nameQuote( 'field_id' ) . ' '
				. 'FROM ' . $db->nameQuote( '#__social_fields' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__social_fields_groups' ) . ' AS b '
				. 'ON a.' . $db->nameQuote( 'group_id' ) . ' = b.' . $db->nameQuote( 'id' ) . ' '
				. 'INNER JOIN ' . $db->nameQuote( '#__social_fields_rules' ) . ' AS c '
				. 'ON a.' . $db->nameQuote( 'id' ) . ' = c.' . $db->nameQuote( 'parent_id' ) . ' '
				. 'WHERE a.' . $db->nameQuote( 'id' ) . ' = ' . $db->Quote( $fieldId ) . ' '
				. 'AND b.' . $db->nameQuote( 'uid' ) . ' = ' . $db->Quote( $uid ) . ' '
				. 'AND a.' . $db->nameQuote( 'state' ) . ' = ' . $db->Quote( SOCIAL_STATE_PUBLISHED ) . ' '
				. 'AND c.' . $db->nameQuote( 'match_text' ) . ' = ' . $db->Quote( $match );
		$db->setQuery( $query );
		$ids	= $db->loadColumn();

		return $ids;
	}

	/**
	 * Retrives a list of custom field groups given the work flow id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFieldGroups( $workflowId )
	{
		$db		= ES::db();

		$query		= array();
		$query[]	= 'SELECT a.* FROM ' . $db->nameQuote( '#__social_fields_groups' ) . ' AS a';
		$query[]	= 'WHERE a.' . $db->nameQuote( 'workflow_id' ) . '=' . $db->Quote( $workflowId );
		$query[]	= 'AND a.' . $db->nameQuote( 'state' ) . '=' . $db->Quote( SOCIAL_STATE_PUBLISHED );

		$query 		= implode( ' ' , $query );
		$db->setQuery( $query );

		$result		= $db->loadObjectList();

		// If there's nothing, just return false.
		if( !$result )
		{
			return $result;
		}

		$groups = array();

		foreach( $result as $row )
		{
			$group  = ES::table( 'FieldGroup' );
			$group->bind( $row );

			$groups[]   = $group;
		}
		return $groups;
	}

	/**
	 * Deprecated.
	 * Retrieves a list of fields which should be displayed during the registration process.
	 * This should not be called elsewhere apart from the registration since it uses different steps, for processes.
	 *
	 * @since	1.0
	 * @deprecated Deprecated since 1.2. Used SocialModelFields::getCustomFields instead.
	 */
	public function getCustomFieldsForProfiles( $profileId )
	{
		$db     	= ES::db();
		$fields 	= array();

		$query 		= array();
		$query[]	= 'SELECT b.*, c.' . $db->nameQuote( 'element' ) . ' AS element,d.' . $db->nameQuote( 'field_id' ) . ' as smartfield';

		$query[]	= 'FROM ' . $db->nameQuote( '#__social_fields_steps' ) . ' AS a';

		// Only want fields from the steps associated to the profile.
		$query[]	= 'INNER JOIN ' . $db->nameQuote( '#__social_fields' ) . ' AS b';
		$query[]	= 'ON a.' . $db->nameQuote( 'id' ) . ' = b.' . $db->nameQuote( 'step_id' );

		// Join with apps table to obtain the element
		$query[]	= 'INNER JOIN ' . $db->nameQuote( '#__social_apps' ) . ' AS c';
		$query[]	= 'ON c.' . $db->nameQuote( 'id' ) . ' = b.' . $db->nameQuote( 'app_id' );

		// Join with rules table.
		$query[]	= 'LEFT JOIN ' . $db->nameQuote( '#__social_fields_rules' ) . ' AS d';
		$query[]	= 'ON d.' . $db->nameQuote( 'parent_id' ) . ' = b.' . $db->nameQuote( 'id' );

		// Core fields should not be dependent on the state because it can never be unpublished.
		$query[]	= 'WHERE(';
		$query[]	= 'b.' . $db->nameQuote( 'core' ) . '=' . $db->Quote( 1 );
		$query[]	= 'OR';
		$query[]	= 'b.' . $db->nameQuote( 'state' ) . '=' . $db->Quote( SOCIAL_STATE_PUBLISHED );
		$query[]	= ')';

		// Registration field should not select dependant fields by default unless it is selected.
		$query[]	= 'AND b.' . $db->nameQuote( 'id' ) . ' NOT IN (';
		$query[]	= 'SELECT ' . $db->nameQuote( 'field_id' ) . ' FROM ' . $db->nameQuote( '#__social_fields_rules' );
		$query[]	= 'WHERE ' . $db->nameQuote( 'field_id' ) . ' = b.' . $db->nameQuote( 'id' );
		$query[]	= ')';

		// Make sure that the field is set to be visible during registrations.
		$query[]	= 'AND b.' . $db->nameQuote( 'visible_registration' ) . '=' . $db->Quote( 1 );
		// $query[]	= 'AND b.' . $db->nameQuote( 'core' ) . '=' . $db->Quote( 1 );

		// Make sure that only visible_registration is enabled only.


		// Make sure to load fields that are in the current step only.
		$query[]	= 'AND a.' . $db->nameQuote( 'uid' ) . '=' . $db->Quote( $profileId );
		$query[]	= 'AND a.' . $db->nameQuote( 'type' ) . '=' . $db->Quote( SOCIAL_TYPE_PROFILES );

		// Join back the queries.
		$query 		= implode( ' ' , $query );

		// echo str_ireplace( '#__' , 'jos_' , $query );
		// exit;

		$db->setQuery( $query );

		$rows	= $db->loadObjectList();

		// If there's no fields at all, just skip this whole block.
		if( !$rows )
		{
			return false;
		}

		$fields 	= array();

		// We need to bind the fields with SocialTableField
		foreach( $rows as $row )
		{
			$field 	= ES::table( 'Field' );
			$field->bind( $row );

			// Manually push profile_id into the field
			$field->profile_id = $profileId;

			$fields[]	= $field;
		}

		return $fields;
	}


	/**
	 * Retrieves a list of core custom fields.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	The step id.
	 * @param	array 	Some additional data.
	 */
	public function getCoreFields( $stepId , $post = array() )
	{
		$db     	= ES::db();

		$query 		= array();

		$query[]	= 'SELECT a.*, b.' . $db->nameQuote( 'element' ) . ' AS ' . $db->nameQuote( 'element' ) . ', c.uid AS ' . $db->nameQuote( 'profile_id' );
		$query[]	= 'FROM ' . $db->nameQuote( '#__social_fields' ) . ' AS a';
		$query[]	= 'INNER JOIN ' . $db->nameQuote( '#__social_apps' ) . ' AS b';
		$query[]	= 'ON b.' . $db->nameQuote( 'id' ) . ' = a.' . $db->nameQuote( 'app_id' );
		$query[]	= 'INNER JOIN ' . $db->nameQuote( '#__social_fields_steps' ) . ' AS c';
		$query[]	= 'ON c.' . $db->nameQuote( 'id' ) . ' = a.' . $db->nameQuote( 'step_id' );
		$query[]	= 'WHERE b.' . $db->nameQuote( 'core' ) . '=' . $db->Quote( 1 );


		// @rule: We already know before hand which elements are the core fields for the profile types.
		$elements   = array( $db->Quote( 'joomla_username' ) , $db->Quote( 'joomla_fullname' ) , $db->Quote( 'joomla_email' ) ,
							$db->Quote( 'joomla_password' ), $db->Quote( 'joomla_timezone' ) , $db->Quote('joomla_user_editor' ) );

		$query[]	= 'AND b.' . $db->nameQuote( 'element' ) . ' IN(' . implode( ',' , $elements ) . ')';

		// Only select from specific steps.
		$query[]	= 'AND a.' . $db->nameQuote( 'step_id' ) . '=' . $db->Quote( $stepId );

		// The fields should be ordered correctly.
		$query[]	= 'ORDER BY a.' . $db->nameQuote( 'ordering' ) . ' ASC';

		// Let's merge the queries.
		$query 		= implode( ' ' , $query );

		// @TODO: There should be some checking here to check for fields that are not added into any steps.

		$db->setQuery( $query );

		$result		= $db->loadObjectList();

		// If all the core fields have already been mapped, just ignore this.
		if( !$result )
		{
			return $result;
		}

		$fields     = array();

		foreach( $result as $row )
		{
			$field      = ES::table( 'Field' );
			$field->bind( $row );

			// Manually push in profile id
			$field->profileId = $row->profile_id;

			$fields[]   = $field;
		}

		return $fields;
	}

	/**
	 * Allows purging of expired registration data.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purgeExpired()
	{
		$db 	= ES::db();

		$date 		= ES::get( 'Date' );

		$query[]	= 'DELETE FROM ' . $db->nameQuote( '#__social_registrations' );

		// @TODO: Configurable interval period
		$query[]	= 'WHERE ' . $db->nameQuote( 'created' ) . ' <= DATE_SUB( ' . $db->Quote( $date->toMySQL() ) . ' , INTERVAL 12 HOUR)';

		$db->setQuery( implode( ' ' , $query ) );
		$state 		= $db->Query();

		return $state;
	}

	/**
	 * Links a user account with an oauth client.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function linkOAuthUser(SocialOAuth $client, SocialUser $user, $pull = true, $push = true)
	{
		// We need to update with the new access token here.
		$session = JFactory::getSession();
		$accessToken = $session->get($client->getType() . '.access', '', SOCIAL_SESSION_NAMESPACE);

		$client->setAccess($accessToken->token, $accessToken->secret);

		$oauthId = $client->getUserId();

		if ($client->getType() == 'apple') {
			$oauthId = $accessToken->user->id;
		}

		$oauth = ES::table('OAuth');
		$oauth->uid = $user->id;
		$oauth->type = SOCIAL_TYPE_USER;
		$oauth->client = $client->getType();
		$oauth->oauth_id = $oauthId;
		$oauth->token = $accessToken->token;
		$oauth->secret = $accessToken->secret;
		$oauth->expires = $accessToken->expires;
		$oauth->pull = $pull;
		$oauth->push = $push;

		// Store the user's meta here.
		try {
			$meta = $client->getUserMeta();
		} catch (Exception $e) {
			$app = JFactory::getApplication();

			// Use dashboard here instead of login because api error calls might come from after user have successfully logged in
			$url = ESR::dashboard(array(), false);

			$message = (object) [
				'message' => JText::sprintf('COM_EASYSOCIAL_OAUTH_FACEBOOK_ERROR_MESSAGE', $e->getMessage()),
				'type' => ES_ERROR
			];

			ES::info()->set($message);
			ES::redirect($url);
		}

		$params = ES::registry();
		$params->bind($meta);

		// Store the permissions
		$oauth->permissions	= ES::makeJSON($client->getPermissions());

		// Set the params
		$oauth->params = $params->toString();

		// Store oauth record
		$state = $oauth->store();

		if (!$state) {
			$this->setError($oauth->getError());
			return false;
		}

		// update the user type from joomla to oauth client name once authenticated successfully.
		$oauthModel = ES::model('Oauth');
		$oauthModel->updateUserType($user->id, $client->getType());

		// Trigger fields to do necessary linking
		// Load profile type.

		// Get all published fields apps.
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields(array('workflow_id' => $user->getWorkflow()->id, 'state' => SOCIAL_STATE_PUBLISHED));

		// Prepare the arguments
		$args = array(&$meta, &$client, &$user);

		// Get the fields library
		$lib = ES::fields();

		// Get the trigger handler
		$handler = $lib->getHandler();

		// Trigger onRegisterOAuthBeforeSave
		$errors = $lib->trigger('onLinkOAuthAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// @TODO: Send email notification to admin that a user linked their social account with an existing account

		// @TODO: Send email notification to the account owner that they have successfully associated their social account.

		return $state;
	}

	/**
	 * Creates a user in the system for users who logged in via oauth
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function createOAuthUser($accessToken, $data, $client, $import = true, $sync = true)
	{
		$config = ES::config();

		// Registrations needs to be enabled.
		if (!$config->get('registrations.enabled')) {
			$this->setError('COM_EASYSOCIAL_REGISTRATIONS_DISABLED');
			return false;
		}

		// Load profile type.
		$profile = ES::table('Profile');
		$profile->load($data['profileId']);

		// Get all published fields apps.
		$fieldsModel = ES::model('Fields');
		$fields = $fieldsModel->getCustomFields(array('workflow_id' => $profile->getWorkflow()->id, 'state' => SOCIAL_STATE_PUBLISHED));
		$args = array(&$data, &$client);

		// Get the fields library
		$lib = ES::fields();

		// Get the trigger handler
		$handler = $lib->getHandler();

		// Trigger onRegisterOAuthBeforeSave
		$errors = $lib->trigger('onRegisterOAuthBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'beforeSave'));

		// Get a list of user groups this profile is assigned to
		$groups = json_decode($profile->gid);

		// Need to bind the groups under the `gid` column from Joomla.
		$data['gid'] = $groups;

		// Bind the posted data for the user.
		$user = ES::user();
		$user->bind($data, SOCIAL_POSTED_DATA);

		// Detect the profile type's registration type.
		$type = $profile->getRegistrationType(false, true);

		// We need to generate an activation code for the user.
		if ($type == 'verify' || $type == 'confirmation_approval') {
			$user->activation = ES::getHash(JUserHelper::genRandomPassword());
		}

		// If the registration type requires approval or requires verification or both, the user account need to be blocked first.
		if ($type == 'approvals' || $type == 'verify' || $type == 'confirmation_approval') {
			$user->block = 1;
		}

		// Get registration type and set the user's state accordingly.
		$user->set('state', constant('SOCIAL_REGISTER_' . strtoupper($type)));
		$user->set('type', $client->getType() );

		// Let's try to save the user now.
		$state = $user->save();

		// If there's a problem saving the user object, set error message.
		if (!$state) {
			$this->setError($user->getError());
			return false;
		}

		// Set the user with proper `profile_id`
		$user->profile_id = $profile->id;

		// Once the user is saved successfully, add them into the profile mapping.
		$profile->addUser($user->id);

		// Assign user object back into the data.
		$data['user'] = $user;

		// Determine if we should import avatar and cover
		if (!$import) {
			unset($data['avatar']);
			unset($data['cover']);
		}

		// Bind custom fields for this user.
		$user->bindCustomFields($data);

		// Allow field applications to manipulate custom fields data
		$args = array(&$data, &$client, &$user, $import);

		// Allow fields app to make necessary changes if necessary. At this point, we wouldn't want to allow
		// the field to stop the registration process already.
		// @trigger onRegisterAfterSave
		$lib->trigger('onRegisterOAuthAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Create a new oauth record on the `#__social_oauth` table so we can simulate the user.
		$oauth = ES::table('OAuth');
		$oauth->uid = $user->id;
		$oauth->type = SOCIAL_TYPE_USER;
		$oauth->client = $client->getType();
		$oauth->oauth_id = $data['oauth_id'];
		$oauth->token = $accessToken->token;
		$oauth->secret = $accessToken->secret;
		$oauth->expires = $accessToken->expires;
		$oauth->pull = $sync;
		$oauth->push = $sync;

		// We need to store the user params
		$oauthParams = json_encode($client->getUserMeta());
		$oauth->params = $oauthParams;

		// Store oauth record
		$oauth->store();

		// Add activity
		if ($type == 'auto' || $type == 'login') {
			$this->logRegistrationActivity($user);
		}

		return $user;
	}

	/**
	 * Generates a username until it no longer exists on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function generateUsername($username, $min = 1, $max = 500)
	{
		$postfix	= rand( $min , $max );
		$original 	= $username;

		while( $this->isUsernameExists( $username ) )
		{
			$username 	= $original . '_' . $postfix;
		}

		return $username;
	}

	/**
	 * Determines if a username exists on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isUsernameExists($username)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select( '#__users' );
		$sql->column( 'COUNT(1)' );
		$sql->where( 'username' , $username );

		$db->setQuery( $sql );

		$exists 	= $db->loadResult() > 0;

		return $exists;
	}

	/**
	 * Determines if an email exists on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isEmailExists($email)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__users');
		$sql->column('COUNT(1)');
		$sql->where('email', $email);

		$db->setQuery($sql);

		$exists = $db->loadResult() > 0;

		return $exists;
	}

	/**
	 * Creates a user in the system given it's registration data.
	 *
	 * @since	2.0.11
	 * @access	public
	 */
	public function createUser(SocialTableRegistration &$registration, $quickRegister = false, $inviteId = '', $referral = null)
	{
		$config = ES::config();

		// Registrations needs to be enabled.
		if (!$config->get('registrations.enabled')) {
			$this->setError(JText::_('COM_EASYSOCIAL_REGISTRATIONS_DISABLED'));
			return false;
		}


		$user = ES::user();


		$param = ES::get('Registry');
		$param->bind($registration->values);

		// Convert the data into an array of result.
		$data = $param->toArray();

		$fieldsModel = ES::model('fields');

		// Load profile type.
		$profile = ES::table('Profile');
		$profile->load($registration->profile_id);

		$visibility = $quickRegister ? 'mini_registration' : 'registration';

		// Get all published fields
		$fields = $fieldsModel->getCustomFields(array('workflow_id' => $profile->getWorkflow()->id, 'visible' => $visibility));

		// Pass in data and new user object by reference for fields to manipulate
		$args = array(&$data, &$user);

		// Perform field validations here. Validation should only trigger apps that are loaded on the form
		// @trigger onRegisterBeforeSave
		$lib = ES::getInstance('Fields');

		// Set the user's profile id
		$user->profile_id = $registration->profile_id;

		// Get the trigger handler
		$handler = $lib->getHandler();

		// Trigger onRegisterBeforeSave
		$errors = $lib->trigger('onRegisterBeforeSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args, array($handler, 'beforeSave'));

		// We need to know the password of the user because they might need to login after registrations.
		$data['password_clear'] = $data['password'];

		// If there are any errors, throw them on screen.
		if (is_array($errors) && in_array(false, $errors, true)) {
			$this->setError($errors);
			return $user;
		}

		// Get a list of user groups this profile is assigned to
		$groups = json_decode($profile->gid);

		// Need to bind the groups under the `gid` column from Joomla.
		$data['gid'] = $groups;

		// Bind the posted data for the user.
		$user->bind($data, SOCIAL_POSTED_DATA);

		// Detect the profile type's registration type.
		$type = $profile->getRegistrationType();

		// We need to generate an activation code for the user.
		if ($type == 'verify' || $type == 'confirmation_approval') {
			$user->activation = ES::getHash(JUserHelper::genRandomPassword());
		}

		// If the registration type requires approval or requires verification or both, the user account need to be blocked first.
		if ($type == 'approvals' || $type == 'verify' || $type == 'confirmation_approval') {
			$user->block = 1;
		}

		// Get registration type and set the user's state accordingly.
		$user->set('state', constant('SOCIAL_REGISTER_' . strtoupper($type)));

		// Trigger user apps before we create the new user account
		$args = array(&$data, &$user);
		ES::apps()->load(SOCIAL_TYPE_USER);
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserBeforeCreate', $args);

		// Determines if user should be set as verified automatically
		$profileParams = $profile->getParams();

		if ($profileParams->get('verified', false)) {
			$user->verified = 1;
		}

		// Let's try to save the user now.
		$state = $user->save();

		// If there's a problem saving the user object, set error message.
		// Added another check because $user->save() triggers Joomla's user plugin that although sometimes throws an error, the user actually got created anyway
		if (!$state && empty($user->id)) {
			$this->setError($user->getError());
			return $user;
		}

		// Set the user with proper `profile_id`
		$user->profile_id = $profile->id;

		// Once the user is saved successfully, add them into the profile mapping.
		$profile->addUser($user->id);

		// Allow field applications to manipulate custom fields data
		$args = array(&$data, &$user);

		// Allow fields app to make necessary changes if necessary. At this point, we wouldn't want to allow
		// the field to stop the registration process already.
		// @trigger onRegisterAfterSave
		$lib->trigger('onRegisterAfterSave', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Bind custom fields for this user.
		$user->bindCustomFields($data);

		// Reform the args with the binded custom field data in the user object
		$args = array(&$data, &$user);

		// @trigger onRegisterAfterSaveFields
		$lib->trigger('onRegisterAfterSaveFields' , SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// We need to set the "data" back to the registration table
		$newData = json_encode($data);
		$registration->values = $newData;

		// Trigger user apps after user creation
		$args = array(&$data, &$user);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserAfterCreate', $args);

		// Need to create all the privacy records for each field
		$privacyModel = ES::model('Privacy');
		$privacyModel->createFieldPrivacyItemsForUser($user->id);
		$privacyModel->createFieldPrivacyMapsForUser($user->id);

		// Invited by another user from the site
		if ($referral) {
			// We store the referral in params for future use
			$user->setUserParams('referral', $referral);

			// Only process this if the user doesnt have to verify email
			if ($type === 'auto' || $type === 'login') {
				$invites = ES::invites();
				$invites->registered($referral, $user->id);
			}
		}

		// If this user invited from site user, need to record this new user id into friend invite table
		// Determine that this user already registered on the site
		if ($inviteId && !$referral) {
			$inviteTable = ES::table('FriendInvite');
			$inviteTable->load($inviteId);

			if ($inviteId && $inviteTable->id) {
				$inviteTable->registered_id = $user->id;
				$inviteTable->store();

				// Make them both friends
				$inviteTable->makeFriends();
			}
		}

		// Add related activity after user has successfully registered. #3172
		if ($profile->getRegistrationType() == 'auto' || $profile->getRegistrationType() == 'login') {
			$this->logRegistrationActivity($user);
		}

		return $user;
	}

	/**
	 * Notify users and administrator when they create an account on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function notifyAdmins($data, SocialUser $user, SocialTableProfile $profile, $oauth = false)
	{
		// Skip this if the user haven't confirm their email account yet
		if ($user->state == SOCIAL_USER_STATE_CONFIRMATION) {
			return true;
		}


		// Get the application data.
		$jConfig = ES::jConfig();

		// Generate a key for the admin's actions.
		$key = md5($user->password . $user->email . $user->name . $user->username);

		$config = ES::config();

		if ($config->get('registrations.emailasusername')) {
			$data['username'] = $user->email;
		}

		// Get the user profile link
		$profileLink = $user->getPermalink(true, true);

		// If the registration requires approval, we will use the backend user link
		if ($profile->getRegistrationType() == 'login' || $profile->getRegistrationType() == 'auto') {
			$profileLink = JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id;
		}

		// Push arguments to template variables so users can use these arguments
		$params = array(
							'site' => $jConfig->getValue('sitename'),
							'username' => $data['username'],
							'password' => $data['password'],
							'firstName' => !empty($data['first_name']) ? $data['first_name'] : '',
							'middleName' => !empty($data['middle_name']) ? $data['middle_name'] : '',
							'lastName' => !empty($data['last_name']) ? $data['last_name'] : '',
							'name' => $user->getName(),
							'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
							'profileLink' => $profileLink,
							'email' => $user->email,
							'activation' => FRoute::controller('registration' , array('external' => true , 'task' => 'activate' , 'activation' => $user->activation)),
							'reject' => FRoute::controller('registration' , array('external' => true , 'task' => 'rejectUser' , 'id' => $user->id , 'key' => $key)),
							'approve' => FRoute::controller('registration' , array('external' => true , 'task' => 'approveUser' , 'id' => $user->id , 'key' => $key)),
							'manageAlerts' => false,
							'profileType' => $profile->get('title')
						);

		// Get the email title.
		$title = $profile->getModeratorEmailTitle();

		// Get the email format.
		$format = $profile->getEmailFormat();

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		foreach ($admins as $admin) {
			// Immediately send out emails
			$mailer = ES::mailer();

			// Set the admin's name.
			$params['adminName'] = $admin->name;

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($admin->name, $admin->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the template
			$template = $profile->getModeratorEmailTemplate('', $oauth);

			$mailTemplate->setTemplate($template, $params, $format);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Notify custom email addresses
	 *
	 * @since	4.0.014
	 * @access	public
	 */
	public function notifyCustom($data, SocialUser $user, SocialTableProfile $profile, $oauth = false)
	{
		// Skip this if the user haven't confirm their email account yet
		if ($user->state == SOCIAL_USER_STATE_CONFIRMATION) {
			return true;
		}


		// Get the application data.
		$jConfig = ES::jConfig();

		// Generate a key for the admin's actions.
		$key = md5($user->password . $user->email . $user->name . $user->username);

		$config = ES::config();

		if ($config->get('registrations.emailasusername')) {
			$data['username'] = $user->email;
		}

		// Get the user profile link
		$profileLink = $user->getPermalink(true, true);

		// If the registration requires approval, we will use the backend user link
		if ($profile->getRegistrationType() == 'login' || $profile->getRegistrationType() == 'auto') {
			$profileLink = JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=form&id=' . $user->id;
		}

		// Push arguments to template variables so users can use these arguments
		$params = [
			'site' => $jConfig->getValue('sitename'),
			'username' => $data['username'],
			'password' => $data['password'],
			'firstName' => !empty($data['first_name']) ? $data['first_name'] : '',
			'middleName' => !empty($data['middle_name']) ? $data['middle_name'] : '',
			'lastName' => !empty($data['last_name']) ? $data['last_name'] : '',
			'name' => $user->getName(),
			'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
			'profileLink' => $profileLink,
			'email' => $user->email,
			'activation' => FRoute::controller('registration' , array('external' => true , 'task' => 'activate' , 'activation' => $user->activation)),
			'reject' => FRoute::controller('registration' , array('external' => true , 'task' => 'rejectUser' , 'id' => $user->id , 'key' => $key)),
			'approve' => FRoute::controller('registration' , array('external' => true , 'task' => 'approveUser' , 'id' => $user->id , 'key' => $key)),
			'manageAlerts' => false,
			'profileType' => $profile->get('title')
		];

		// Get the email title.
		$title = $profile->getModeratorEmailTitle();

		// Get the email format.
		$format = $profile->getEmailFormat();

		// Get a list of super admins on the site.
		$emails = $profile->getParams()->get('custom.emails', '');
		$emails = trim($emails);
		$emails = explode(',', $emails);

		if (!$emails) {
			return false;
		}

		foreach ($emails as $email) {
			$mailer = ES::mailer();
			$mailTemplate = $mailer->getTemplate();
			$mailTemplate->setRecipient('', $email);
			$mailTemplate->setTitle($title);

			$template = $profile->getCustomEmailTemplate('', $oauth);
			$mailTemplate->setTemplate($template, $params, $format);
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email to the admin now.
			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Notify users and administrator when they create an account on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function notify($data, SocialUser $user, SocialTableProfile $profile, $oauth = false, $sendWelcomeMail = false)
	{
		// Get the application data.
		$jConfig = ES::jConfig();
		$config = ES::config();

		if ($config->get('registrations.emailasusername')) {
			$data['username'] = $user->email;
		}

		$activation = ESR::registration(array('external' => true, 'task' => 'activate', 'controller' => 'registration', 'token' => $user->activation));

		if ($profile->getRegistrationType() == 'confirmation_approval') {
			$activation = ESR::registration(array('external' => true, 'task' => 'confirmationUserEmail', 'controller' => 'registration', 'token' => $user->activation));
		}

		$adminSef = false;
		if (ES::isFromAdmin()) {
			$adminSef = true;
		}

		$data['name'] = $user->getName();

		if ($config->get('users.displayName') == 'username') {
			$data['name'] = $data['username'];
		}

		$password = $config->get('registrations.email.password') ? $user->password_clear : false;

		// Push arguments to template variables so users can use these arguments
		$params = array(
							'site' => $jConfig->getValue('sitename'),
							'username' => $data['username'],
							'password' => $password,
							'firstName' => !empty($data['first_name']) ? $data['first_name'] : '',
							'middleName' => !empty($data['middle_name']) ? $data['middle_name'] : '',
							'lastName' => !empty($data['last_name']) ? $data['last_name'] : '',
							'name' => $data['name'],
							'id' => $user->id,
							'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
							'profileLink' => $user->getPermalink(true, true, true, $adminSef),
							'email' => $user->email,
							'activation' => $activation,
							'token' => $user->activation,
							'manageAlerts' => false,
							'profileType' => $profile->get('title')
						);

		// Get the user preferred language
		$language = $user->getParam('language', '');

		// Get the email title.
		$title = $profile->getEmailTitle('', $language);

		// Get the email format.
		$format = $profile->getEmailFormat();

		// Immediately send out emails
		$mailer = ES::mailer();

		// Get the email template.
		$mailTemplate = $mailer->getTemplate();

		// Set recipient
		$mailTemplate->setRecipient($user->name , $user->email);

		// Set title
		$mailTemplate->setTitle($title);

		// Set the contents
		$mailTemplate->setTemplate($profile->getEmailTemplate('', $oauth, $sendWelcomeMail), $params, $format);

		// Set the priority. We need it to be sent out immediately since this is user registrations.
		$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

		// Set the language. We need the email to be sent out with the correct language.
		$mailTemplate->setLanguage($language);

		// Try to send out email now.
		$state = $mailer->create($mailTemplate);

		return $state;
	}

	/**
	 * Resends activation emails to the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function resendActivation(SocialUser $user)
	{
		// Get the application data.
		$jConfig = ES::jConfig();
		$config = ES::config();

		// Push arguments to template variables so users can use these arguments
		$params = array(
							'site' => $jConfig->getValue('sitename'),
							'username' => $user->username,
							'password' => $user->password_clear,
							'name' => $user->getName(),
							'id' => $user->id,
							'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
							'profileLink' => $user->getPermalink(true, true),
							'email' => $user->email,
							'activation' => FRoute::registration(array('external' => true, 'task' => 'activate', 'controller' => 'registration', 'token' => $user->activation)),
							'token' => $user->activation,
							'manageAlerts' => false
						);

		// Get the email title.
		$title = JText::_('COM_EASYSOCIAL_REGISTRATION_ACTIVATION_REMINDER');

		// Immediately send out emails
		$mailer = ES::mailer();

		// Get the email template.
		$mailTemplate = $mailer->getTemplate();

		// Set recipient
		$mailTemplate->setRecipient($user->name , $user->email);

		// Set title
		$mailTemplate->setTitle($title);

		// Set the contents
		$mailTemplate->setTemplate('site/registration/reactivate', $params);

		// Set the priority. We need it to be sent out immediately since this is user registrations.
		$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

		// Try to send out email now.
		$state = $mailer->create($mailTemplate);
		return $state;
	}

	/**
	 * Activates user account
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activate($token)
	{
		$db	= ES::db();
		$query = [];

		$query[] = 'SELECT `id` FROM `#__users`';
		$query[] = 'WHERE `activation` = ' . $db->Quote($token);
		$query[] = 'AND `block` = 1';

		$lastvisitDate = '= ' . $db->Quote($db->getNullDate());

		if (ES::isJoomla4()) {
			$lastvisitDate = 'IS NULL';
		}

		$query[] = 'AND `lastvisitDate` ' . $lastvisitDate;

		$db->setQuery($query);
		$id = (int) $db->loadResult();

		// If user id cannot be located, throw an error.
		if (!$id) {
			$this->setError(JText::_('COM_EASYSOCIAL_REGISTRATION_ACTIVATION_TOKEN_NOT_FOUND'));
			return false;
		}

		// Activate the user.
		$user = ES::user($id);
		$state = $user->activate($token);

		if (!$state) {
			$this->setError($user->getError());
			return false;
		}

		// Add related activity after user has successfully registered
		$this->logRegistrationActivity($user);

		return $user;
	}

	/**
	 * Method to log all the necessary activity after user has registered
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function logRegistrationActivity(SocialUser $user)
	{
		// Retrieve current profile data
		$profile = $user->getProfile();

		// Retrieve current profile registration type
		$type = $profile->getRegistrationType();

		// Add activity logging when a uer registers on the site.
		// Get the application params
		$app = ES::table('App');
		$app->load(array('element' => 'profiles', 'group' => SOCIAL_TYPE_USER));
		$params = $app->getParams();

		// Registration stream must always be first.
		if ($params->get('stream_register', true)) {

			// Upon new registration, check whether this registration new stream item added yet
			// maybe somewhere already added this e.g. payplans, so we need to check it here
			$stream = ES::stream();
			$exist = $stream->exists($user->id, 'profiles', 'register', $user->id);

			if (!$exist) {
				$streamTemplate = $stream->getTemplate();

				// Set the actor
				$streamTemplate->setActor($user->id, SOCIAL_TYPE_USER);

				// Set the context
				$streamTemplate->setContext($user->id, SOCIAL_TYPE_PROFILES);

				// Set the verb
				$streamTemplate->setVerb('register');

				// set sitewide
				$streamTemplate->setSiteWide();

				$streamTemplate->setAccess('core.view');

				// Add stream template.
				$stream->add($streamTemplate);
			}
		}

		// Need to pause the time so that the register and badge assigned stream
		// won't be on the same second
		usleep(500000);

		// @points: user.register
		// Assign points when user registers on the site.
		$points = ES::points();
		$points->assign('user.registration', 'com_easysocial', $user->id);

		// @badge: registration.create
		// Assign badge for the person that initiated the friend request.
		$badge = ES::badges();
		$badge->log('com_easysocial', 'registration.create', $user->id, JText::_( 'COM_EASYSOCIAL_REGISTRATION_BADGE_REGISTERED'));

		// Additional pause to ensure that any related stream generated beyond this path will always after the above stream. #3172
		usleep(500000);

		$model = ES::model('Clusters');

		$invitedClusters = $model->isInvited($user->id);

		// Assign users into the EasySocial groups
		$defaultGroups = $profile->getDefaultClusters('groups');

		// Assign users into the EasySocial pages
		$defaultPages = $profile->getDefaultClusters('pages');

		// Currently do not have any assign default event during registration
		$defaultEvents = array();

		foreach ($invitedClusters as $id) {
			$cluster = ES::cluster($id);

			if ($cluster->getType() == SOCIAL_TYPE_GROUP) {
				$defaultGroups[] = $cluster;
			}

			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				$defaultPages[] = $cluster;
			}

			if ($cluster->getType() == SOCIAL_TYPE_EVENT) {
				$defaultEvents[] = $cluster;
			}
		}

		if ($defaultGroups) {
			foreach ($defaultGroups as $group) {
				$group->createMember($user->id, true, $type);
			}
		}

		if ($defaultPages) {
			foreach ($defaultPages as $page) {
				$page->createMember($user->id, true, $type);
			}
		}

		if ($defaultEvents) {
			foreach ($defaultEvents as $event) {
				$event->createMember($user->id, true, $type);
			}
		}

		// Install default apps to user
		$defaultApps = $profile->getDefaultApps();

		if ($defaultApps) {
			$profile->assignUsersApps($defaultApps);
		}
	}

	/**
	 * Confirmation user registration email
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function confirmationUserEmail($token)
	{
		$db	= ES::db();
		$query = array();

		$query[] = 'SELECT `id` FROM ' . $db->nameQuote('#__users');
		$query[] = 'WHERE ' . $db->nameQuote('activation') . ' = ' . $db->Quote($token);
		$query[] = 'AND ' . $db->nameQuote('block') . ' = ' . $db->Quote(1);
		$query[] = 'AND (' . $db->nameQuote('lastvisitDate') . ' = ' . $db->Quote($db->getNullDate());
		// Joomla 4 store this as NULL value by default
		$query[] = 'OR ' . $db->nameQuote('lastvisitDate') . ' IS NULL)';

		$query = implode(' ', $query);
		$db->setQuery($query);
		$id	= (int) $db->loadResult();

		// If user id cannot be located, throw an error.
		if (!$id) {
			$this->setError(JText::_('COM_ES_REGISTRATION_CONFIRMATION_USER_EMAIL_AUTHENTICATION_FAILED'));
			return false;
		}

		// Set user to require approval state
		$user = ES::user($id);
		$state = $user->setRequireApproval();

		if (!$state) {
			$this->setError($user->getError());
			return false;
		}

		return $user;
	}
}
