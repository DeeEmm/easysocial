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
 * Object mapping for privacy table.
 *
 * @author	Sam <sam@stackideas.com>
 * @since	1.0
 */


class SocialTablePrivacyMap extends SocialTable
{
	/**
	 * The table row id
	 * @var int
	 */
	public $id	= null;

	/**
	 * id to social_privacy.id
	 * @var int
	 */
	public $privacy_id	= null;

	/**
	 * user id or profile id
	 * @var int
	 */
	public $uid	= null;

	/**
	 * The privacy type
	 * @var string max 64chars
	 */
	public $utype = null;

	/**
	 * The privacy value
	 * @var int
	 */
	public $value = 0;

	/**
	 * custom fields (json)
	 * @var int
	 */
	public $params = null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_privacy_map' , 'id' , $db );
	}


	public function toJSON()
	{
		return array('id' 			=> $this->id ,
					 'privacy_id' 	=> $this->privacy_id,
					 'uid' 			=> $this->uid,
					 'utype' 		=> $this->utype,
					 'value' 		=> $this->value,
					 'params' 		=> $this->params,
		 );
	}

}
