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

// Load parent table.
ES::import( 'admin:/tables/table' );

/**
 * Object mapping for `#__social_badges_history`.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableBadgeHistory extends SocialTable
{
	/**
	 * The unique id which is auto incremented.
	 * @var int
	 */
	public $id					= null;

	/**
	 * The unique badge id.
	 * @var int
	 */
	public $badge_id			= null;

	/**
	 * The actor for this action.
	 * @var int
	 */
	public $user_id 			= null;

	/**
	 * Determines if the user achieved the badge with this action.
	 * @var int
	 */
	public $achieved 			= null;


	/**
	 * Class construct
	 *
	 * @since	1.0
	 * @param	JDatabase
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__social_badges_history' , 'id' , $db );
	}
}
