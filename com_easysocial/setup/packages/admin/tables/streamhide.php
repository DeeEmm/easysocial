<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( 'JPATH_BASE' ) or die( 'Unauthorized Access' );

ES::import( 'admin:/tables/table' );

class SocialTableStreamHide extends SocialTable
{
	public $id				= null;
	public $user_id			= null;
	public $uid				= null;
	public $type			= null;
	public $context 		= null;

	public function __construct( $db )
	{
		parent::__construct('#__social_stream_hide', 'id', $db);
	}

	public function load( $uid = null, $userId = null, $type = SOCIAL_STREAM_HIDE_TYPE_STREAM )
	{
		if( empty( $uid ) || empty( $userId ) )
			return false;

		$db = ES::db();

		$query = 'select `id` from `#__social_stream_hide`';
		$query .= ' where `user_id` = ' . $db->Quote( $userId );
		$query .= ' and `uid` = ' . $db->Quote( $uid );
		$query .= ' and `type` = ' . $db->Quote( $type );

		$db->setQuery( $query );
		$id = $db->loadResult();

		return parent::load($id);
	}

	public function toJSON()
	{
		return array('id' => $this->id,
					 'user_id' => $this->user_id,
					 'uid' => $this->uid,
					 'type' => $this->type,
					 'context' => $this->context
		 );
	}
}
