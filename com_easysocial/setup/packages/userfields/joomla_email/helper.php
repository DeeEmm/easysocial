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

jimport('joomla.mail.helper');

class SocialFieldsUserJoomlaEmailHelper
{
	/**
	 * Determines if a domain name is allowed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function isAllowed( $email, &$params )
	{
		$domains 	= trim( $params->get( 'allowed' , '' ) );

		// If there's no domains set, return as false.
		if( empty( $domains ) )
		{
			return true;
		}

		// Ensure that it's an array.
		$domains 	= ES::makeArray( $domains, ',' );

		// Allowed checking is OR, which means for multiple allowed domains, if one of them is true, then it is considered as allowed
		$results = array();

		foreach( $domains as $domain )
		{
			$domain = trim( $domain );

			// Check if regex format is inserted
			if( substr( $domain, 0, 1 ) === '/' && substr( $domain, -1, 1 ) === '/' )
			{
				$result = preg_match( $domain . 'u', $email );

				// If result is empty, means no match, then we mark it as false result
				$results[] = !empty( $result );
			}
			else
			{
				$search 	= '@' . $domain;

				// If no match, then we mark it as false result
				$results[] = !( stristr( $email , $search ) === false );
			}
		}

		return in_array( true, $results );
	}

	/**
	 * Determines if a domain name is disallowed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function isDisallowed( $email , &$params )
	{
		// Detect for disallowed domain names.
		$domains 	= trim( $params->get( 'disallowed' , '' ) );

		// If there's no domains set, return as false.
		if( empty( $domains ) )
		{
			return false;
		}

		// Ensure that it's an array.
		$domains 	= ES::makeArray( $domains, ',' );

		// Disllowed checking is AND, which means for multiple allowed domains, if one of them is false, then it is considered as disallowed

		foreach( $domains as $domain )
		{
			$domain = trim( $domain );


			// Check if regex format is inserted
			if( substr( $domain, 0, 1 ) === '/' && substr( $domain, -1, 1 ) === '/' )
			{
				$result = preg_match( $domain . '/u', $email );

				// If there are matches, it means it is in the disallowed list
				if( !empty( $result ) )
				{
					return true;
				}
			}
			else
			{
				$search 	= '@' . $domain;

				if( stristr( $email , $search ) !== false )
				{
					return true;
				}
			}
		}

		// If nothing matched above, we just say it's invalid.
		return false;
	}

	/**
	 * Determines if an email is forbidden
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function isForbidden( $email , &$params )
	{
		// Detect forbidden words.
		$forbidden 	= trim( $params->get( 'forbidden' , '' ) );

		if( empty( $forbidden ) )
		{
			return false;
		}

		// Ensure that the text is in an array.
		$forbidden	= ES::makeArray( $forbidden, ',' );

		// Check for forbidden
		foreach( $forbidden as $word )
		{
			if( stristr( $email, $word ) !== false )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Validates a provided email address.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The email address to verify.
	 * @return	bool	True if validates, false otherwise.
	 *
	 */
	public static function isValid( $email )
	{
		if (empty($email)) {
			return false;
		}

		if (!JMailHelper::isEmailAddress($email)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if an email already exist in the system.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The email to check against.
	 * @return	bool	True if exists, false otherwise.
	 *
	 */
	public static function exists( $email , $currentEmail = '' )
	{
		// If current email is provided, they might not be setting any value for their email.
		if( $email === $currentEmail )
		{
			return false;
		}

		$db         = ES::db();
		$sql		= $db->sql();

		$sql->select( '#__users' )
			->where( 'email', $email );

		$db->setQuery( $sql->getTotalSql() );

		$exists		= $db->loadResult() > 0 ;

		return $exists;
	}
}
