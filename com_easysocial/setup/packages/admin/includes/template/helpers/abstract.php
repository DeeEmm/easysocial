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

/**
 * New abstract does not inherit from @EasySocial library since we
 * do not need all the unecessary properties that comes from it.
 *
 * @since	3.3.0
 */
class EasySocialHelperAbstract
{
	public $config = null;
	public $app = null;
	public $input = null;

	public function __construct()
	{
		$this->config = ES::config();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
	}
}

/**
 * Only rely on this for older classes
 */
class ThemesHelperAbstract extends EasySocial
{
}
