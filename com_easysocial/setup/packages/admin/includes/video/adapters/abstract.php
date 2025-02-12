<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

abstract class SocialVideoAdapter extends EasySocial
{
	public $table = null;
	public $uid = null;
	public $type = null;
	public $document = null;

	public function __construct($uid, $type, SocialTableVideo $table)
	{
		$this->uid = $uid;
		$this->type = $type;
		$this->table = $table;
		$this->document = ES::document(); 

		parent::__construct();
	}

	/**
	 * Determine whether hit has to be incremented.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public abstract function hit();
}
