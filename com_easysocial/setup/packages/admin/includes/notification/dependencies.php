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

class SocialNotificationTemplate
{
	/**
	 * Stores the actor's unique id.
	 * @var int
	 */
	public $uid 	= null;

	/**
	 * Stores actor's unique type
	 * @var string
	 */
	public $type 		= null;

	/**
	 * Stores context type of this notification item.
	 * @var string
	 */
	public $context_type 		= null;

	/**
	 * Stores context ids of this notification item.
	 * @var string
	 */
	public $context_ids 		= null;

	/**
	 * Stores the actor id.
	 * @var int
	 */
	public $actor_id 	= null;

	/**
	 * Stores the actor type.
	 * @var int
	 */
	public $actor_type = null;

	/**
	 * Stores the target user id.
	 * @var int
	 */
	public $target_id 	= null;

	/**
	 * Stores the unique id.
	 * @var int
	 */
	public $target_type = null;

	/**
	 * Stores the title of the notification
	 * @var string
	 */
	public $title		= null;

	/**
	 * Stores the content of the notification item.
	 * @var string
	 */
	public $content		= null;

	/**
	 * Stores the image of the notification item.
	 * @var string
	 */
	public $image		= null;

	/**
	 * Stores the command of the notification
	 * @var string
	 */
	public $cmd			= null;

	/**
	 * Stores the params for the notification title and content.
	 * @var string
	 */
	public $params	= null;

	/**
	 * Stores the url to the unique item.
	 * @var string
	 */
	public $url 	= null;

	/**
	 * Determines if aggregation should occur.
	 * @var bool
	 */
	public $aggregate	= null;

	/**
	 * Allows caller to set the target attributes.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The object id.
	 * @param	string	The object type.
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setObject( $uid , $type , $cmd = '' )
	{
		$this->uid 		= $uid;
		$this->type 	= $type;

		if( !empty( $cmd ) )
		{
			$this->setCommand( $cmd );
		}

		return $this;
	}

	/**
	 * If caller wants to aggregate this notification themselves, they can do it.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setAggregation()
	{
		$this->aggregate 	= true;
	}

	/**
	 * Sets the context type.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setContextType( $context )
	{
		$this->context_type 	= $context;
	}

	/**
	 * Sets the context type.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setContextId( $ids )
	{
		if( is_array( $ids ) )
		{
			$ids 	= ES::json()->encode( $ids );
		}

		$this->context_ids 	= $ids;
	}

	/**
	 * Allows caller to set the command
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The command for this action
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setCommand( $cmd )
	{
		$this->cmd 		= $cmd;

		return $this;
	}

	/**
	 * Allows caller to set the url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The url to the item
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setUrl( $url )
	{
		$this->url 	= $url;

		return $this;
	}


	/**
	 * Allows caller to set the target attributes.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The actor id.
	 * @param	string	The actor type.
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setActor( $actorId , $actorType = SOCIAL_TYPE_USER )
	{
		$this->actor_id 	= $actorId;
		$this->actor_type 	= $actorType;

		return $this;
	}

	/**
	 * Allows caller to set the target attributes.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The target id.
	 * @param	string	The target type.
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setTarget( $targetId , $targetType = SOCIAL_TYPE_USER )
	{
		$this->target_id 	= $targetId;
		$this->target_type 	= $targetType;

		return $this;
	}

	/**
	 * Allows caller to set the title to this notification item.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The title of the notification.
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setTitle( $title  )
	{
		$this->title 	= $title;

		return $this;
	}

	/**
	 * Allows caller to set the content for this notification item.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The content of the notification item (Optional)
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setContent( $content = '' )
	{
		$this->content 	= $content;

		return $this;
	}

	/**
	 * Allows caller to set the image for this notification
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The url to the image
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setImage( $url )
	{
		$this->image 	= $url;

		return $this;
	}

	/**
	 * Allows caller to set the params for this notification item
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The permalink url.
	 * @return	SocialNotificationTemplate	An instance of itself for chaining
	 */
	public function setParams( $params = '' )
	{
		if( is_object( $params ) )
		{
			$params 	= ES::json()->encode( $params );
		}

		$this->params 	= $params;

		return $this;
	}
}
