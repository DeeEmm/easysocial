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

ES::import('admin:/includes/model');

class EasySocialModelOAuth extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('people');
	}

	/**
	 * Loads a record given the unique item id
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getRow($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_oauth', 'a');
		$sql->column('a.*');
		$sql->join('#__users', 'b');
		$sql->on('b.id', 'a.uid');
		$sql->where('b.username', $options['username']);

		if (isset($options['client'])) {
			$sql->where('a.client', $options['client']);
		}

		$db->setQuery($sql);

		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Some desc
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPullableClients()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_oauth');
		$sql->where('pull', 1);

		$db->setQuery($sql);

		$items = $db->loadObjectList();

		return $items;
	}

	/**
	 * Gets a list of oauth clients a user is associated with
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getOauthClients($userId = null)
	{
		$user = ES::user($userId);
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__social_oauth');
		$query[] = 'WHERE ' . $db->qn('uid') . '=' . $db->Quote($user->id);
		$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote(SOCIAL_TYPE_USER);

		// the current oauth Twitch only for retrieve the Twitch video data instead of user login
		// so we need to exclude this.
		$query[] = 'AND ' . $db->qn('client') . '!=' . $db->Quote('twitch');

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return array();
		}

		$clients = array();

		foreach ($result as $row) {
			$table = ES::table('OAuth');
			$table->bind($row);

			$clients[] = $table;
		}

		return $clients;
	}

	/**
	 * Update to the default type after user revoke their oauth clients associated with
	 * Or update the rest of the oauth client he associated to because user can able to link to their social network more than 1
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateUserType($userId = null, $type = 'joomla')
	{
		$user = ES::user($userId);
		$db = ES::db();
		$hasOtherOauthClientAssociated = false;

		// Check for the user whether has associated with other social netowrk under this same user account or not.
		if ($type == 'joomla') {
			$hasOtherOauthClientAssociated = $this->getOauthClientAssociatedList($user->id, true);

			// Update the user type to the latest oauth client if this user has associated to multiple social profiles.
			if ($hasOtherOauthClientAssociated) {

				foreach ($hasOtherOauthClientAssociated as $oauthData) {
					$type = $oauthData->client;
				}
			}
		}

		$query = array();
		$query[] = 'UPDATE ' . $db->qn('#__social_users');
		$query[] = 'SET ' . $db->qn('type') . '=' . $db->Quote($type);
		$query[] = 'WHERE ' . $db->qn('user_id') . '=' . $db->Quote($user->id);

		$db->setQuery($query);
		$db->Query();

		return true;
	}

	/**
	 * Gets a list of oauth client data which associated with the same account
	 *
	 * @since	3.1.6
	 * @access	public
	 */
	public function getOauthClientAssociatedList($userId = null, $onlyRetrieveLastCreatedOauthClient = false)
	{
		$db = ES::db();

		$clientTypes = array(SOCIAL_TYPE_FACEBOOK, SOCIAL_TYPE_TWITTER, SOCIAL_TYPE_LINKEDIN, SOCIAL_TYPE_GOOGLE);

		foreach ($clientTypes as $clientType) {
			$OauthClientTypes[] = $db->Quote($clientType);
		}

		$OauthClientTypes = implode(',', $OauthClientTypes);

		$query = array();
		$query[] = 'SELECT * FROM ' . $db->qn('#__social_oauth');
		$query[] = 'WHERE ' . $db->qn('uid') . '=' . $db->Quote($userId);
		$query[] = 'AND ' . $db->qn('type') . '=' . $db->Quote(SOCIAL_TYPE_USER);
		$query[] = 'AND ' . $db->qn('client') . ' IN (' . $OauthClientTypes . ')';
		$query[] = 'ORDER BY ' . $db->qn('created') . ' DESC';

		// If require to check for update this user type which associated with their social account
		// then have to retrieve the last created associated with this oauth account
		if ($onlyRetrieveLastCreatedOauthClient) {
			$query[] = 'LIMIT 1';
		}

		$query = implode(' ', $query);
		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return false;
		}

		return $result;
	}

	/**
	 * Determines if a specific client has been associated with the system
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function isAssociated($type, $userId = '')
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT * FROM ' . $db->qn('#__social_oauth');
		$query[] = 'WHERE ' . $db->qn('type') . ' = ' . $db->Quote(SOCIAL_TYPE_USER);
		$query[] = 'AND ' . $db->qn('client') . ' = ' . $db->Quote($type);
		$query[] = 'AND ' . $db->qn('token') . ' != ""';

		if ($userId) {
			$query[] = 'AND ' . $db->qn('uid') . '=' . $db->Quote($userId);
		}

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result) {
			return false;
		}

		return $result;
	}
}
