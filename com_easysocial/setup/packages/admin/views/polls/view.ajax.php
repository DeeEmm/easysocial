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

class EasySocialViewPolls extends EasySocialAdminView
{
	/**
	 * Confirmation to delete poll
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function confirmDelete()
	{
		$ids = $this->input->get('id', '', 'default');

		// Ensure that it is in an array form
		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);

		$contents = $theme->output('admin/polls/dialogs/delete');

		return $this->ajax->resolve($contents);
	}	
}
