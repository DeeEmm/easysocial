<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewVideoCategories extends EasySocialAdminView
{
	/**
	 * Confirmation to delete a video category
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/videocategories/dialog.delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to render a browse video dialog
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'cmd');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$content = $theme->output('admin/videocategories/dialog.browse');

		return $this->ajax->resolve($content);
	}
}
