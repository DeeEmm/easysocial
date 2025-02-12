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

class SocialTableConversationParticipantHistory extends SocialTable
{
	public $id				= null;
	public $conversation_id	= null;
	public $created_by		= null;
	public $target			= null;
	public $type			= null;
	public $created			= null;
	public $state			= null;

	public function __construct( $db )
	{
		parent::__construct('#__social_conversations_participants_history', 'id' , $db);
	}

	public function exists( $nodeId , $conversationId )
	{
		return parent::exists( array( 'node_id' => $nodeId , 'conversation_id' => $conversationId ) );
	}

	public function getCreator()
	{
		static $nodes	= array();

		if( !isset( $nodes[ $this->created_by ] ) )
		{
			$nodes[ $this->created_by ]	= ES::get( 'People' , $this->created_by , SOCIAL_PEOPLE_NODE_ID );
		}
		return $nodes[ $this->created_by ];
	}

	public function getTarget()
	{
		static $nodes	= array();

		if( !isset( $nodes[ $this->target ] ) )
		{
			// @rule: Allow multiple targets in a single action.
			$target 	= ES::json()->decode( $this->target );
			$targets	= array();

			foreach( $target as $targetId )
			{
				$targets[]	= ES::get( 'People' , $targetId , SOCIAL_PEOPLE_NODE_ID );
			}

			$nodes[ $this->target ]	= $targets;
		}
		return $nodes[ $this->target ];
	}

	public function getType()
	{
		return SOCIAL_CONVERSATION_FEED_ITEM;
	}

	public function getHistoryType()
	{
		return $this->get( 'type' );
	}
}
