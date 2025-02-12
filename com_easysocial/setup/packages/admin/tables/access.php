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

// Import main table.
ES::import( 'admin:/tables/table' );

/**
 * Object mapping for profile types table.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableAccess extends SocialTable
{
	/**
	 * The unique id for the profile type.
	 * @var int
	 */
	public $id			= null;

	/**
	 * The unique item id for this object.
	 * @var string
	 */
	public $uid		= null;

	/**
	 * The unique item type.
	 * @var string
	 */
	public $type		= null;

	/**
	 * The raw values in JSON string.
	 * @var string
	 */
	public $params = null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_access' , 'id' , $db );
	}
}
