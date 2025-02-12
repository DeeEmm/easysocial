<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

interface ISocialOAuth
{
	/**
	 * Allows caller to revoke the access of the oauth client.
	 *
	 * @since	1.0
	 */
	public function revoke();

	/**
	 * Allows caller to render a login button
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string		The callback URL.
	 * @param	Array		An array of permissions
	 * @param	string		The display mode
	 * @param	string		The text to appear in the button
	 * @return
	 */
	public function getLoginButton( $callback , $permissions = array() , $display = 'popup' , $text = '' );

	/**
	 * Get's the external id.
	 *
	 * @since	1.0
	 * @access	public
	 * @return	int
	 */
	public function getUserId();

	/**
	 * Determines if the current user is already a registered user on site.
	 *
	 * @since	1.0
	 * @access	public
	 * @return	bool	True if registered, false otherwise.
	 */
	public function isRegistered();

	/**
	 * Retrieve Joomla User id
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getUid();

	/**
	 * Retrieves the user's information.
	 *
	 * @since	1.0
	 * @access	public
	 * @return
	 */
	public function getUserMeta();

	/**
	 * Sets the access token
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setAccess( $key , $secret = '' );

	/**
	 * Retrieves the access token.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getAccess();

	/**
	 * Retrieve the authorization url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getAuthorizeURL( $params );

	/**
	 * Gets the type of client.
	 *
	 * @since	1.0
	 * @access	public
	 * @return	string	The type of the oauth client.
	 */
	public function getType();
}
