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

/**
 * Object mapping for Field table.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableLogger extends SocialTable
{
	/**
	 * The unique id of the field item.
	 * @var	int
	 */
	public $id			= null;

	/**
	 * The name of the file that is throwing errors.
	 * @var	string
	 */
	public $file		= null;

	/**
	 * The errors at line number.
	 * @var	string
	 */
	public $line 	= null;

	/**
	 * The error message.
	 * @var	string
	 */
	public $message 	= null;

	/**
	 * The date time of this error message.
	 * @var	int
	 */
	public $created 	= null;


	/**
	 * Class Constructor
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function __construct(& $db )
	{
		parent::__construct( '#__social_logger' , 'id' , $db );
	}

}
