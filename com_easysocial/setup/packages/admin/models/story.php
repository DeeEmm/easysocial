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

class EasySocialModelStory extends EasySocialModel
{
	private $data			= null;

	function __construct()
	{
		parent::__construct( 'story' );
	}

	/**
	 * Gets node's latest story.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getStory( $uid , $type )
	{
		$db 		= ES::db();

		$query		= array();
		$query[]	= 'SELECT * FROM ' . $db->nameQuote( '#__social_stories' ) . ' AS a';
		$query[]	= 'WHERE ' . $db->nameQuote( 'uid' ) . '=' . $db->Quote( $uid );
		$query[]	= 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( $type );

		$db->setQuery( $query );

		$obj 		= $db->loadObject();

		if( !$obj )
		{
			return false;
		}

		$story 	= ES::table( 'Story' );
		$story->bind( $obj );

		return $story;
	}

}
