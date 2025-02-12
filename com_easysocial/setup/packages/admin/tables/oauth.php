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

ES::import('admin:/tables/table');

class SocialTableOauth extends SocialTable
{
	public $id = null;
	public $oauth_id = null;
	public $uid = null;
	public $type = null;
	public $client = null;
	public $token = null;
	public $secret = null;
	public $created = null;
	public $expires	= null;
	public $pull = 0;
	public $push = 0;
	public $params = null;
	public $last_pulled = '0000-00-00 00:00:00';
	public $last_pushed = '0000-00-00 00:00:00';
	public $permissions = null;

	public function __construct($db)
	{
		parent::__construct('#__social_oauth', 'id', $db);
	}

	/*
	 * Loads a specific record given the client type and client's id.
	 *
	 * @param   string  $client     The client type. E.g: 'facebook' , 'twitter'
	 * @param   string  $clientId   The client's unique id.
	 *
	 * @return  boolean True on success, false otherwise.
	 */
	public function loadByClient( $client , $clientId )
	{
	    $db 	= ES::db();
	    $query  = 'SELECT * FROM ' . $db->nameQuote( $this->_tbl ) . ' '
	            . 'WHERE ' . $db->nameQuote( 'client' ) . '=' . $db->Quote( $client ) . ' '
	            . 'AND ' . $db->nameQuote( 'client_uid' ) . '=' . $db->Quote( $clientId ) . ' '
				. 'LIMIT 1';
		$db->setQuery( $query );
		$result	= $db->loadObject();

		return parent::bind( $result );
	}

	/**
	 * Tries to find the oauth record given a username
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadByUsername($username, $client = null)
	{
		$model = ES::model('OAuth');
		$options = array('username' => $username);

		if (!is_null($client)) {
			$options['client'] = $client;
		}

		$row = $model->getRow($options);

		if (!$row) {
			return false;
		}

		$state = parent::bind($row);

		return $state;
	}

	/*
	 * Generates a similar login credentials for login purposes.
	 *
	 */
	public function getLoginCredentials()
	{
		$db 	= ES::db();
		$query  = 'SELECT a.' . $db->nameQuote( 'username' ) . ', a.' . $db->nameQuote( 'password' ) . ' '
		        . 'FROM ' . $db->nameQuote( '#__users' ) . ' AS a '
		        . 'INNER JOIN ' . $db->nameQuote( '#__social_nodes' ) . ' AS b '
		        . 'ON a.' . $db->nameQuote( 'id' ) . ' = b.'. $db->nameQuote( 'uid' ) . ' '
		        . 'WHERE b.' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $this->node_id ) . ' '
				. 'LIMIT 1';
		$db->setQuery( $query );
		$credentials	= $db->loadAssoc();

		return $credentials;
	}

	/**
	 * Binds the token object to this table
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bindToken(SocialOauth $client)
	{
		$access = @$client->getAccess();

		$this->token	= $access->token;
		$this->secret 	= $access->secret;
	}

	/**
	 * Determines if the user has permissions
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function hasPermissions( $permission )
	{
		$data 	= ES::makeArray( $this->permissions );

		return in_array( $permission , $data );
	}

	/**
	 * Retrieves the client type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->client;
	}

	/**
	 * Retrieves the oauth client
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getClient()
	{
		$client = ES::oauth($this->getType());
		$client->setAccess($this->token, $this->secret);

		return $client;
	}

	/**
	 * Generates a title for this oauth type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTitle()
	{
		static $items = array();

		if (!isset($items[$this->client])) {
			$items[$this->client] = JText::_('COM_EASYSOCIAL_OAUTH_CLIENT_' . strtoupper($this->client));
		}

		return $items[$this->client];
	}

	/**
	 * Override parent's delete implementation
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function delete($pk = null)
	{
		// Ensure that is valid user id
		if (!$this->uid) {
			return false;
		}

		$state = parent::delete();

		// update the user type to joomla once revoke successfully.
		if ($state) {
			$model = ES::model('Oauth');
			$model->updateUserType($this->uid);
		}

		return $state;
	}
}
