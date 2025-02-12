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

ES::import( 'admin:/includes/fields/dependencies' );

class SocialFieldModelUserRelations extends SocialFieldModel
{
	public function getRelationship( $uid, $filter = array() )
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select( '#__social_relationship_status' )
			->where( '(' )
			->where( 'actor', $uid )
			->where( 'target', $uid, '=', 'or' )
			->where( ')' );

		foreach( $filter as $key => $val )
		{
			$sql->where( $key, $val );
		}

		$db->setQuery( $sql );

		$result = $db->loadObjectList();

		if( !$result )
		{
			return false;
		}

		$relations = array();

		foreach( $result as $row )
		{
			$table = $this->table( 'relations' );
			$table->bind( $row );

			$relations[] = $table;
		}

		return $relations;
	}

	public function getActorRelationship( $uid = null, $filter = array() )
	{
		if( is_null( $uid ) )
		{
			$uid = ES::user()->id;
		}

		$db = ES::db();

		$sql = $db->sql();

		$sql->select( '#__social_relationship_status' )
			->where( 'actor', $uid );

		foreach( $filter as $k => $v )
		{
			$sql->where( $k, $v );
		}

		$db->setQuery( $sql );

		$result = $db->loadObject();

		if( !$result )
		{
			return false;
		}

		$relation = $this->table( 'relations' );
		$relation->bind( $result );

		return $relation;
	}

	public function getTargetRelationship( $uid = null, $filter = array() )
	{
		if( is_null( $uid ) )
		{
			$uid = ES::user()->id;
		}

		$db = ES::db();

		$sql = $db->sql();
		$sql->select( '#__social_relationship_status' )
			->where( 'target', $uid );

		foreach( $filter as $k => $v )
		{
			$sql->where( $k, $v );
		}

		$db->setQuery( $sql );

		$result = $db->loadObjectList();

		$relations = array();

		foreach( $result as $row )
		{
			if( $row->actor != $uid )
			{
				$relation = $this->table( 'relations' );
				$relation->bind( $row );

				$relations[] = $relation;
			}
		}

		return $relations;
	}
}
