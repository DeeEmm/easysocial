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

ES::import( 'admin:/tables/table' );


class SocialTableAlertMap extends SocialTable
{
	public $id				= null;
	public $alert_id		= null;
	public $user_id			= null;
	public $email			= null;
	public $system			= null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_alert_map' , 'id' , $db );
	}

	public function loadByAlertId( $alert_id, $user_id = null )
	{
		if( is_null( $user_id ) )
		{
			$user_id = ES::user()->id;
		}

		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__social_alert_map' );
		$sql->where( 'alert_id', $alert_id );
		$sql->where( 'user_id', $user_id );

		$db->setQuery( $sql );
		$result = $db->loadObject();

		if( !$result )
		{
			return false;
		}

		return parent::bind( $result );
	}
}
