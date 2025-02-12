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

class SocialPrivacyOption
{

	var $id = null;
	var $default = null;
	var $option = null;

	var $uid = null;
	var $type = null;
	var $user_id = null;
	var $value = null;

	var $custom = null;
	var $field = null;
	var $pid = null;

	var $editable = null;
	var $override = null;


	/**
	 * Class constructor
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function __construct()
	{
		$this->editable = false;

		return $this;
	}

	public static function factory()
	{
		$obj 	= new self();
		return $obj;
	}

}

