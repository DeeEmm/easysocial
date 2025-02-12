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

class SocialTableListMap extends SocialTable
{
	public $id			= null;
	public $list_id 	= null;
	public $target_id	= null;
	public $target_type	= null;
	public $created		= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__social_lists_maps' , 'id' , $db );
	}

	/**
	 * Allows caller to load a list map using a given id and type.
	 *
	 * Example:
	 * <code>
	 * <?php
	 * $table 	= ES::table();
	 * $table->loadByType( 42 , SOCIAL_TYPE_USER );
	 * ?>
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The list's id.
	 * @param	int 	The target id.
	 * @param	string	The target type.
	 * @return	boolean
	 */
	public function loadByType( $listId , $targetId , $targetType )
	{
		$db 	= ES::db();

		$query 	= 'SELECT * FROM ' . $db->nameQuote( $this->_tbl ) . ' '
				. 'WHERE ' . $db->nameQuote( 'target_id' ) . '=' . $db->Quote( $targetId ) . ' '
				. 'AND ' . $db->nameQuote( 'target_type' ) . '=' . $db->Quote( $targetType ) . ' '
				. 'AND ' . $db->nameQuote( 'list_id' ) . '=' . $db->Quote( $listId );

		$db->setQuery( $query );
		$result	= $db->loadObject();

		if( !$result )
		{
			return false;
		}

		return parent::bind( $result );
	}

	/**
	 * Override parent's store behavior
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function store( $updateNulls = false )
	{
		$state 	= parent::store();

		return $state;
	}
}
