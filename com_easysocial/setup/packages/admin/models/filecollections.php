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

jimport('joomla.application.component.model');

ES::import( 'admin:/includes/model' );

class EasySocialModelFileCollections extends EasySocialModel
{
	private $data			= null;
	protected $pagination		= null;

	function __construct()
	{
		parent::__construct( 'filecollections' );
	}

	/**
	 * Retrieves the pagination object based on the current query.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	null
	 */
	public function getPagination($reload = null)
	{
		// Lets load the content if it doesn't already exist
		if ( empty( $this->pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination( $this->total , $this->getState('limitstart') , $this->getState('limit') );
		}

		return $this->pagination;
	}

	/**
	 * Retrieves a list of collections given the owner id and type.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int 		The unique id for the owner.
	 * @param	string		The unique type for the owner.
	 * @return
	 */
	public function getTotalFiles( $collectionId )
	{
		$db 		= ES::db();
		$sql 		= $db->sql();

		$sql->select( '#__social_files' );
		$sql->column( 'COUNT(1)' );
		$sql->where( 'collection_id' , $collectionId );
		$db->setQuery( $sql );

		$total		= (int) $db->loadResult();

		return $total;
	}

	/**
	 * Retrieves a list of collections given the owner id and type.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int 		The unique id for the owner.
	 * @param	string		The unique type for the owner.
	 * @return
	 */
	public function getCollections( $uid , $type , $options = array() )
	{
		$db 		= ES::db();
		$sql 		= $db->sql();

		$sql->select( '#__social_files_collections' );

		$sql->where( 'owner_id' , $uid );
		$sql->where( 'owner_type' , $type );

		$db->setQuery( $sql );

		$result		= $db->loadObjectList();

		if( !$result )
		{
			return $result;
		}

		$collections 	= array();

		foreach( $result as $row )
		{
			$collection 	= ES::table( 'FileCollection' );
			$collection->bind( $row );

			$collections[]	= $collection;
		}

		return $collections;
	}
}
