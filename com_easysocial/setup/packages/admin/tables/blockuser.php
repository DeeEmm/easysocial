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

ES::import('admin:/tables/table');

class SocialTableBlockUser extends SocialTable
{
	/**
	 * The primary key for this table
	 * @var string
	 */
	public $id = null;

	/**
	 * This stores the creator that initiated the block
	 * @var string
	 */
	public $user_id = null;

	/**
	 * This stores the location type that the item is being tagged on
	 * @var string
	 */
	public $target_id = null;

	/**
	 * Reason for blocking the user
	 * @var string
	 */
	public $reason 	= null;

	/**
	 * The creation date for the block
	 * @var string
	 */
	public $created = null;

	public function __construct( $db )
	{
		parent::__construct('#__social_block_users' , 'id' , $db);
	}
}
