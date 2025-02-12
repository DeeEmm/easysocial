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

class SocialApp extends EasySocial
{
	public $data = null;

	public function __construct($appId = null, $debug = false)
	{
		parent::__construct();

		$this->data = ES::table('App');
		$this->data->bind($appId);
	}

	/**
	 * Object initialisation for the class to fetch the appropriate app
	 * object.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public static function factory($appId = null, $debug = false)
	{
		$items = self::loadApp($appId, $debug);

		return $items;
	}

	public static function loadApp($appId, $debug)
	{
		// The $data must always be a table.
		$data = ES::table('App');

		// If passed in argument is an integer, we load it
		if (is_numeric($appId)) {
			$data->load($appId);
		}

		// If passed in argument is already an app, table just assign it.
		if ($appId instanceof SocialTableApp) {
			$data = $appId;
		}

		if (is_object($appId)) {

			if (!$appId instanceof SocialTableApp) {
				$data = ES::table('App');
				$data->bind($appId);
			}
		}

		// Create an object
		// $data = new SocialApp($data);

		return $data;
	}
}
