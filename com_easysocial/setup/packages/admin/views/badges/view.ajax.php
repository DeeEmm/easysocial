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

class EasySocialViewBadges extends EasySocialAdminView
{
	/**
	 * Assign users into group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function browse()
	{
		$callback = $this->input->get('jscallback', '', 'word');

		$theme = ES::themes();
		$theme->set('callback', $callback);

		$output = $theme->output('admin/badges/dialog.browse');

		return $this->ajax->resolve($output);
	}

	/**
	 * Displays confirmation dialog before deleting badges
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/badges/dialog.delete');

		return $this->ajax->resolve($output);
	}

	/**
	 * Sends back the list of files to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discoverFiles($files = [])
	{
		$message = JText::sprintf('COM_EASYSOCIAL_DISCOVER_FOUND_FILES', count($files));

		return $this->ajax->resolve($files, $message);
	}

	/**
	 * Processes ajax calls to scan rules.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan($obj)
	{
		$message = JText::sprintf('COM_EASYSOCIAL_DISCOVER_CHECKED_OUT', $obj->file, count($obj->rules));
		
		return $this->ajax->resolve($message);
	}
}