<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialDocumentHTML extends EasySocial
{
	static $loaded = false;
	static $scriptsLoaded = false;
	static $stylesheetsLoaded = false;
	static $options = array();

	/**
	 * This loads a list of javascripts that are dependent throughout the whole component.
	 *
	 * @access	public
	 * @param	null
	 */
	public function init($options = array())
	{
		// @task: Only load when necessary and in html mode.
		if (self::$loaded) {
			return;
		}

		self::$options = $options;

		$location = ES::isFromAdmin() ? 'admin' : 'site';

		// Initialize js & css dependencies
		ES::initialize($location);

		self::$loaded = true;
	}
}
