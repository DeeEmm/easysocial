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

require_once( SOCIAL_LIB . '/migrators/helpers/info.php' );

/**
 * DB layer for EasySocial.
 *
 * @since	1.1
 * @author	Sam <sam@stackideas.com>
 */
class SocialMigratorHelperJoomla
{
	// component name, e.g. com_kunena
	var $name  			= null;

	// migtration steps
	var $steps 			= null;

	var $info  			= null;

	var $mapping 		= null;

	var $accessMapping 	= null;

	var $limit 		 	= null;

	var $userMapping  	= null;

	public function __construct()
	{
		$this->info     = new SocialMigratorHelperInfo();
		$this->name  	= 'joomla';

		$this->limit 	= 10; //10 items per cycle

		$this->steps[] 	= 'users';

		$this->accessMapping = array(
			'0' 	=> SOCIAL_PRIVACY_PUBLIC,
			'1'		=> SOCIAL_PRIVACY_MEMBER,
			'10'	=> SOCIAL_PRIVACY_MEMBER,
			'20'	=> SOCIAL_PRIVACY_MEMBER,
			'30'	=> SOCIAL_PRIVACY_FRIEND,
			'40'	=> SOCIAL_PRIVACY_ONLY_ME
			);
	}

	public function getVersion()
	{
		$version 	= JVERSION;

		return $version;
	}

	public function isInstalled()
	{
		return true;
	}

	/*
	 * return object with :
	 *     isvalid  : true or false
	 *     messsage : string.
	 *     count    : integer. item count to be processed.
	 */
	public function isComponentExist()
	{
		$obj = new stdClass();
		$obj->isvalid = true;
		$obj->count   = $this->getItemCount();
		$obj->message = '';

		return $obj;
	}

	public function setUserMapping( $maps )
	{
		// do nothing.
	}

	public function getItemCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$total = count( $this->steps );

		// kunena topics count
		$query = 'select count(1) as `total`';
		$query .= ' from `#__users` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`oid` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'userreg' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`block` = ' . $db->Quote( '0' );


		$sql->clear();
		$sql->raw( $query );
		$db->setQuery( $sql );
		$numTotal = $db->loadResult();
		$numTotal = ( $numTotal > 0 ) ? ceil( $numTotal / $this->limit ) : 0;
		$total = $total + $numTotal;

		return $total;
	}

	public function process( $item )
	{
		// @debug
		$obj = new stdClass();

		if( empty( $item ) )
		{
			$item = $this->steps[0];
		}

		$result = '';

		switch( $item )
		{
			case 'users':
				$result = $this->processUsers();
				break;


			default:
				break;
		}

		// this is the ending part to determine if the process is already ended or not.
		if( is_null( $result ) )
		{
			$keys 		= array_keys( $this->steps, $item);
			$curSteps 	= $keys[0];

			if( isset( $this->steps[ $curSteps + 1] ) )
			{
				$item = $this->steps[ $curSteps + 1];
			}
			else
			{
				$item = null;
			}

			$obj->continue = ( is_null( $item ) ) ? false : true ;
			$obj->item 	   = $item;
			$obj->message  = ( $obj->continue ) ? 'Checking for next item to migrate....' : 'No more item found.';

			return $obj;
		}


		$obj->continue = true;
		$obj->item 	   = $item;
		$obj->message  = implode( '<br />', $result->message );

		return $obj;
	}


	private function processUsers()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$query	= 'SELECT a.* from `#__users` as a';
		$query .= ' where not exists ( ';
		$query .= '		select b.`oid` from `#__social_migrators` as b';
		$query .= ' 			where a.`id` = b.`oid` and b.`element` = ' . $db->Quote( 'userreg' ) . ' and b.`component` = ' . $db->Quote( $this->name );
		$query .= ' )';
		$query .= ' and a.`block` = ' . $db->Quote( '0' );

		$query .= ' ORDER BY a.`id` ASC';
		$query .= ' LIMIT ' . $this->limit;


		$sql->raw( $query );
		$db->setQuery( $sql );


		$kUsers = $db->loadObjectList();

		if( count( $kUsers ) <= 0 )
		{
			return null;
		}

		foreach( $kUsers as $user )
		{
			// add stream.
			$this->addUserRegStream( $user );

			// add log
			$this->log( 'userreg', $user->id, $user->id );

			$this->info->setInfo( 'Joomla user with id \'' . $user->id . '\' processed succussfully.' );
		}

		return $this->info;
	}



	private function addUserRegStream( $user )
	{
		$stream				= ES::stream();
		$streamTemplate		= $stream->getTemplate();

		// Set the actors.
		$streamTemplate->setActor( $user->id , SOCIAL_TYPE_USER );

		// Set the context for the stream.
		$streamTemplate->setContext( $user->id , SOCIAL_TYPE_PROFILES );

		// Set the verb for this action as this is some sort of identifier.
		$streamTemplate->setVerb( 'register' );

		$streamTemplate->setSiteWide();

		$streamTemplate->setAccess( 'core.view' );

		// set the stream creation date
		$streamTemplate->setDate( $user->registerDate );

		// Create the stream data.
		$stream->add( $streamTemplate );
	}


	public function log( $element, $oriId, $newId )
	{
		$tbl = ES::table( 'Migrators' );

		$tbl->oid 		= $oriId;
		$tbl->element 	= $element;
		$tbl->component = $this->name;
		$tbl->uid 		= $newId;
		$tbl->created 	= ES::date()->toMySQL();

		$tbl->store();
	}

}
