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

/**
 * Object mapping for prvicay custom table.
 *
 * @author	Sam <sam@stackideas.com>
 * @since	1.0
 */

class SocialTablePrivacyCustom extends SocialTable
{
	/**
	 * The table row id
	 * @var int
	 */
	public $id			= null;

	/**
	 * The object id
	 * @var string
	 */
	public $uid			= null;

	/**
	 * The privacy type
	 * @var string max 64chars
	 */
	public $utype 		= null;

	/**
	 * The userid
	 * @var int
	 */
	public $user_id     = 0;


	public function __construct(& $db )
	{
		parent::__construct( '#__social_privacy_customize' , 'id' , $db );
	}


	public function toJSON()
	{
		return array('id' 		=> $this->id ,
					 'uid' 		=> $this->uid ,
					 'utype' 	=> $this->type,
					 'user_id' 	=> $this->user_id
		 );
	}

}
