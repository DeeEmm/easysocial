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

// Import main table.
ES::import( 'admin:/tables/table' );

/**
 * Object mapping for profile member table.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableProfileMap extends SocialTable
{
	/**
	 * The unique id for current record. Auto incremented.
	 * @var int
	 */
	public $id 		= null;

	/**
	 * The unique id for the profile type.
	 * @var int
	 */
	public $profile_id 	= null;

	/**
	 * The unique user id.
	 * @var int
	 */
	public $user_id 	= null;

	/**
	 * The state of the relationship.
	 * @var int
	 */
	public $state 		= null;

	/**
	 * The creation datetime
	 * @var datetime
	 */
	public $created     = null;

	/**
	 * Class construct.
	 *
	 */
	public function __construct(& $db )
	{
		parent::__construct( '#__social_profiles_maps' , 'id' , $db );
	}

	/**
	 * Load a particular record given the user's id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The user's id.
	 */
	public function loadByUser( $userId )
	{
	    $db 		= ES::db();
	    $query 		= array();
	    $query[]	= 'SELECT * FROM ' . $db->nameQuote( $this->_tbl );
	    $query[]	= 'WHERE ' . $db->nameQuote( 'user_id' ) . '=' . $db->Quote( $userId );

	    $query		= implode( ' ' , $query );
		$db->setQuery( $query );

		$result	= $db->loadObject();

		if( !$result )
		{
			return false;
		}

		return parent::bind( $result );
	}

}
