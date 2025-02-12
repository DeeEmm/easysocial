<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewLanguages extends EasySocialAdminView
{
	/**
	 * Post processing after updating languages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function update()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays a confirmation to delete a language
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();

		$ids = $this->input->get('cid', array(), 'array');

		$theme->set('ids', $ids);
		$content = $theme->output('admin/languages/dialog.delete');

		return $this->ajax->resolve($content);
	}
}
