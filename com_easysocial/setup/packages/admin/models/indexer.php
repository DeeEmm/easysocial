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

// Include main model file.
ES::import( 'admin:/includes/model' );

class EasySocialModelIndexer extends EasySocialModel
{
	private $data			= null;
	private $nextdate		= null;

	function __construct()
	{
		parent::__construct( 'indexer' );
		parent::initStates();
	}

	public function getIndexingItem( $type )
	{
		$db 	= ES::db();
		$limit	= 10;

		$query 		= '';
		$queryCnt 	= '';

		switch( $type )
		{
			case 'users':

				$query = 'select a.`id` as uid, ' . $db->Quote( 'users' ) . ' as utype, a.`name` as title, concat_ws(' . $db->Quote(' ') . ', a.`name`, a.`email` ) as content, a.`id` as `creatorid`, 0 as `refid`';
				$query .= ' from #__users as a';
				$query .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'users' ) . ')';
				$query .= ' order by a.`id` limit ' . $limit;

				$queryCnt = 'select count(1)';
				$queryCnt .= ' from #__users as a';
				$queryCnt .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'users' ) . ')';


				break;

			case 'albums':

				$query = 'select a.`id` as uid, ' . $db->Quote( 'albums' ) . ' as utype, a.`title` as title, concat_ws(' . $db->Quote(' ') . ', a.`title`, a.`caption` ) as content, a.`uid` as `creatorid`, 0 as refid';
				$query .= ' from #__social_albums as a';
				$query .= ' where not exists ( select b.`uid` from `#__social_indexer` as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'albums') . ')';
				$query .= ' and a.type = ' . $db->Quote( 'user' );
				$query .= ' order by a.`id` limit ' . $limit;

				$queryCnt = 'select count(1)';
				$queryCnt .= ' from #__social_albums as a';
				$queryCnt .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'albums' ) . ')';
				$queryCnt .= ' and a.type = ' . $db->Quote( 'user' );

				break;

			case 'photos':

				$query = 'select a.`id` as uid, ' . $db->Quote( 'photos' ) . ' as utype, a.`title` as title, concat_ws(' . $db->Quote(' ') . ', a.`title`, a.`caption` ) as content, a.`uid` as `creatorid`, a.album_id as refid';
				$query .= ' from #__social_photos as a';
				$query .= ' where not exists ( select b.`uid` from `#__social_indexer` as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'photos') . ')';
				$query .= ' and a.type = ' . $db->Quote( 'user' );
				$query .= ' order by a.`id` limit ' . $limit;

				$queryCnt = 'select count(1)';
				$queryCnt .= ' from #__social_photos as a';
				$queryCnt .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'photos' ) . ')';
				$queryCnt .= ' and a.type = ' . $db->Quote( 'user' );

				break;
			case 'lists':

				$query = 'select a.`id` as uid, ' . $db->Quote( 'lists' ) . ' as utype, a.`title` as title, concat_ws(' . $db->Quote(' ') . ', a.`title`, a.`description` ) as content, a.`user_id` as `creatorid`, 0 as `refid`';
				$query .= ' from #__social_lists as a';
				$query .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'lists' ) . ')';
				$query .= ' order by a.`id` limit ' . $limit;

				$queryCnt = 'select count(1)';
				$queryCnt .= ' from #__social_lists as a';
				$queryCnt .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'lists' ) . ')';

				break;

			case 'groups':

				$query = 'select a.`id` as uid, ' . $db->Quote( 'groups' ) . ' as utype, a.`title` as title, concat_ws(' . $db->Quote(' ') . ', a.`title`, a.`description` ) as content, a.`creator_uid` as `creatorid`, 0 as `refid`';
				$query .= ' from #__social_clusters as a';
				$query .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'groups' ) . ')';
				$query .= ' and a.`cluster_type` = ' . $db->Quote( SOCIAL_TYPE_GROUP );
				$query .= ' order by a.`id` limit ' . $limit;

				$queryCnt = 'select count(1)';
				$queryCnt .= ' from #__social_clusters as a';
				$queryCnt .= ' where not exists ( select b.`uid` from #__social_indexer as b where a.`id` = b.`uid` and b.`utype` = ' . $db->Quote( 'groups' ) . ')';
				$queryCnt .= ' and a.`cluster_type` = ' . $db->Quote( SOCIAL_TYPE_GROUP );

				break;

			default:
				break;
		}

		$result = array();


		$db->setQuery( $queryCnt );
		$cnt = $db->loadResult();

		if( $cnt )
		{
			$db->setQuery( $query );
			$result = $db->loadObjectList();
		}


		$obj = new stdClass();
		$obj->data 	= $result;
		$obj->count = $cnt;

		return $obj;

	}


	/* used in backend
	 *
	 *
	 */
	public function getItems( $cond = array() )
	{
		$filterType 		= ( isset( $cond['type'] ) ) ? $cond['type'] : '';
		$filterComponent 	= ( isset( $cond['component'] ) ) ? $cond['component'] : '';

		$db = ES::db();
		$sql = $db->sql();


		$queryCnt 	= 'select COUNT(1) from ' . $db->nameQuote( '#__social_indexer' );
		$query 		= 'select * from ' . $db->nameQuote( '#__social_indexer' );

		$wheres = array();

		if( $filterType )
		{
			$wheres[] = $db->nameQuote( 'utype' ) . ' = ' . $db->Quote( $filterType );
		}

		if( $filterComponent )
		{
			$wheres[] = $db->nameQuote( 'component' ) . ' = ' . $db->Quote( $filterComponent );
		}

		$where = '';
		if( count( $wheres ) > 0 )
		{
			$where = ' where ';
			$where .= ( count($wheres) == 1 ) ? $wheres[0] : implode( ' and ', $wheres );
		}

		$query .= $where;
		$query .= ' order by ' . $db->nameQuote( 'id' ) . ' desc';

		//sql for total count.
		$queryCnt .= $where;

		//echo $query;


		$this->setTotal( $queryCnt );

		// actual data retrival.
		$sql->raw($query);
		$result = $this->getData( $sql );

		return $result;
	}


	public function getFilters()
	{
		$filter = array();

		$db = ES::db();

		//get distinct components
		$query = 'select distinct ' . $db->nameQuote( 'component' ) . ' from ' . $db->nameQuote( '#__social_indexer' );
		$db->setQuery( $query );

		$result = $db->loadColumn();
		$filter['component'] = $result;


		//get distinct components
		$query = 'select distinct ' . $db->nameQuote( 'utype' ) . ' from ' . $db->nameQuote( '#__social_indexer' );
		$db->setQuery( $query );

		$result = $db->loadColumn();
		$filter['type'] = $result;

		return $filter;
	}

	public function getSupportedType()
	{
		$db = ES::db();

		//get distinct components
		$query = 'select distinct ' . $db->nameQuote( 'utype' ) . ' from ' . $db->nameQuote( '#__social_indexer' );
		$db->setQuery( $query );

		$result = $db->loadColumn();

		if( !$result )
		{
			$result = array( SOCIAL_INDEXER_TYPE_USERS, SOCIAL_INDEXER_TYPE_PHOTOS, SOCIAL_INDEXER_TYPE_LISTS );
		}

		return $result;
	}


	public function index( $item )
	{

		if( empty( $item->uid ) )
			return false;


		$tbl = ES::table( 'Indexer' );
		$tbl->load( $item->uid, $item->utype, $item->component );

		$date = ES::date();

		if( $tbl->id )
		{
			//update
			if( $item->title ) $tbl->title 	= $item->title;
			if( $item->content ) $tbl->content 	= $item->content;
			if( $item->ulink ) $tbl->link 	= $item->ulink;
			if( $item->uimage ) $tbl->image = $item->uimage;
		}
		else
		{
			//add new
			$tbl->uid 		= $item->uid;
			$tbl->utype 	= $item->utype;
			$tbl->ucreator 	= $item->ucreator;
			$tbl->link      = $item->ulink;
			$tbl->image 	= $item->uimage;
			$tbl->title 	= $item->title;
			$tbl->content 	= $item->content;
			$tbl->component = $item->component;
		}

		$tbl->last_update 	= $date->toMySQL();
		$state = $tbl->store();

		if(! $state )
		{
			return false;
		}

		$item->id = $tbl->id;

		return $item;
	}

	public function delete( $item )
	{
		if( empty( $item->uid ) || empty( $item->utype) )
			return false;

		$db = ES::db();

	 	$query = 'delete from ' . $db->nameQuote( '#__social_indexer' );
	 	$query .= ' where ' . $db->nameQuote( 'uid' ) . ' = ' . $db->Quote( $item->uid );
	 	$query .= ' and ' . $db->nameQuote( 'utype' ) . ' = ' . $db->Quote( $item->utype );
	 	$query .= ' and ' . $db->nameQuote( 'component' ) . ' = ' . $db->Quote( $item->component );

	 	$db->setQuery( $query );
	 	if( ! $db->query() )
	 	{
			return $db->getErrorMsg();
	 	}

	 	return true;
	}

	public function deleteById( $id )
	{
		$db = ES::db();

	 	$query = 'delete from ' . $db->nameQuote( '#__social_indexer' );
	 	$query .= ' where ' . $db->nameQuote( 'id' ) . ' = ' . $db->Quote( $id );

	 	$db->setQuery( $query );

	 	if( ! $db->query() )
	 	{
			return $db->getErrorMsg();
	 	}

	 	return true;
	}

	public function purge()
	{
		$db = ES::db();

	 	$query = 'delete from ' . $db->nameQuote( '#__social_indexer' );

	 	$db->setQuery( $query );

	 	if( ! $db->query() )
	 	{
			return $db->getErrorMsg();
	 	}

	 	return true;
	}



}
