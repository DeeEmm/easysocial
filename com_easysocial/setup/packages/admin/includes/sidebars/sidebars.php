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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SocialSidebars extends EasySocial
{
	// Determines the current type request
	public $uid = null;
	public $utype = null;
	public $type = null;

	public function __construct($type, $uid = null, $utype = null)
	{
		parent::__construct();

		$this->type = $type;
		$this->uid = $uid;
		$this->utype = $utype;

		$this->adapter = $this->getAdapter();
	}

	/**
	 * Factory method to create a new access object.
	 *
	 * @access	public
	 */
	public static function factory($type, $uid = null, $utype = null)
	{
		$obj = new self($type, $uid, $utype);
		return $obj;
	}

	/**
	 * Magic method to route calls to adapter
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->adapter, $method), $arguments);
	}

	/**
	 * Retrieves the entity adapter
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAdapter()
	{
		if (!$this->type) {
			return false;
		}

		$file = __DIR__ . '/adapters/' . $this->type . '.php';

		require_once($file);

		$className = 'SocialSidebarsAdapter' . ucfirst($this->type);
		$obj = new $className($this->uid, $this->utype);

		return $obj;
	}
}
