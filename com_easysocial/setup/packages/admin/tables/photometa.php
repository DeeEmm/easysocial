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

class SocialTablePhotoMeta extends SocialTable
{
	/**
	 * The unique id for this record.
	 * @var int
	 */
	public $id			= null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $photo_id 	= null;

	/**
	 * The meta group
	 * @var	string
	 */
	public $group 		= null;

	/**
	 * The unique type string for this record.
	 * @var string
	 */
	public $property 	= null;

	/**
	 * The album id for this photo
	 * @var int
	 */
	public $value 	= null;

	/**
	 * Class Constructor
	 *
	 * @since	1.0
	 * @param	JDatabase
	 */
	public function __construct( $db )
	{
		parent::__construct('#__social_photos_meta', 'id', $db);
	}

}
