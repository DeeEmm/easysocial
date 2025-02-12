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

class SocialSidebar
{
	static $instance = null;

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Renders the sidebar items.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function render($view)
	{
		// Retrieve menu items from the model.
		$model = ES::model('Sidebar');
		$menus = $model->getItems($view);

		$theme = ES::themes();
		$theme->set('menus', $menus);
		$theme->set('view', $view);

		$output	= $theme->output('admin/structure/sidebar');

		return $output;
	}
}
