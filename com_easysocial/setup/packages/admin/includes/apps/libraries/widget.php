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

require_once(__DIR__ . '/abstract.php');

class SocialAppsWidgets extends SocialAppsAbstract
{
	// The current view's name.
	protected $viewName	= '';

	public function __construct($options = array())
	{
		parent::__construct($options);
	}

	/**
	 * Main method to help caller to display contents from their theme files.
	 * The method automatically searches for {%APP_NAME%/themes/%CURRENT_THEME%/%FILE_NAME%}
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The template file name.
	 * @return
	 */
	public function display($tpl = null , $docType = null)
	{
		$format = $this->input->get('format' , 'html', 'word');

		// Since the $tpl now only contains the name of the file, we need to be smart enough to determine the full location.
		$template = 'themes:/apps/' . $this->app->group . '/' . $this->app->element . '/' . $tpl;

		return $this->theme->output($template);
	}

	/**
	 * Retrieves the user params
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getUserParams($userId)
	{
		static $_cache = array();

		$idx = $this->app->id . '-' . $userId;

		if (!isset($_cache[$idx])) {

			$map = ES::table('AppsMap');
			$map->load(array('app_id' => $this->app->id , 'uid' => $userId));

			$registry = ES::registry($map->params);

			$_cache[$idx] = $registry;
		}

		return $_cache[$idx];
	}

	public function set($key , $value = null)
	{
		return $this->theme->set($key , $value);
	}
}
