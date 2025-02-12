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

class EasySocialViewAlerts extends EasySocialAdminView
{
	/**
	 * Sends back the list of files to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discoverFiles($files = array())
	{
		$message = JText::sprintf('COM_EASYSOCIAL_DISCOVER_FOUND_FILES', count($files));

		return $this->ajax->resolve($files, $message);
	}

	/**
	 * Post process after scanning files
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan($obj)
	{
		$message = JText::sprintf('COM_EASYSOCIAL_DISCOVER_CHECKED_OUT', $obj->file, count($obj->rules));

		return $this->ajax->resolve($message);
	}

	/**
	 * Displays the dialog for user's setting reset confirmation
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function resetUserConfirmation()
	{
		// Get the id's of the user that we are trying to modify
		$ids = $this->input->get('ids', array(), 'array');
		$task = $this->input->get('task', '', 'default');

		$ids = ES::makeArray($ids);

		$theme = ES::themes();
		$theme->set('ids', $ids);
		$theme->set('task', $task);

		$output = $theme->output('admin/alerts/dialogs/reset.users');

		return $this->ajax->resolve($output);
	}
}
