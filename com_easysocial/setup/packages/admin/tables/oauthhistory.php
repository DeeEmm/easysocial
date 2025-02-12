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

// Load parent's table
ES::import( 'admin:/tables/table' );

class SocialTableOauthHistory extends SocialTable
{
	/**
	 * The unique id for this record.
	 * @var int
	 */
	public $id		= null;

	/**
	 * The unique oauth id for this record.
	 * @var int
	 */
	public $oauth_id 	= null;

	/**
	 * The unique item id for this record.
	 * @var int
	 */
	public $remote_id 	= null;

	/**
	 * The unique item type for this record.
	 * @var string
	 */
	public $remote_type 	= null;

	/**
	 * The unique item id for this record.
	 * @var int
	 */
	public $local_id 	= null;

	/**
	 * The unique item type for this record.
	 * @var string
	 */
	public $local_type 	= null;

	/**
	 * The date the request has been granted
	 * @var datetime
	 */
	public $created = null;


	public function __construct( $db )
	{
		parent::__construct( '#__social_oauth_history' , 'id' , $db );
	}
}
