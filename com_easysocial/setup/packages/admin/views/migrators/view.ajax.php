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

class EasySocialViewMigrators extends EasySocialAdminView
{
	/**
	 * Sends back the list of files to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function check($obj)
	{
		return $this->ajax->resolve($obj);
	}

	/**
	 * Sends back the list of files to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function process($obj, $updateConfig = 0)
	{
		$this->ajax->resolve($obj, $updateConfig);
	}

	/**
	 * Processes ajax calls to scan rules.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan($obj)
	{
		return $this->ajax->resolve($obj);
	}

	/**
	 * Renders confirmation to migrate
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmMigration()
	{
		$theme = ES::themes();
		$output = $theme->output('admin/migrators/dialog.confirm');

		return $this->ajax->resolve($output);
	}

	/**
	 * Renders confirmation to purge history
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function confirmPurge()
	{
		$type = $this->input->get('type', '', 'default');

		$theme = ES::themes();
		$contents = $theme->output('admin/migrators/dialog.confirm.purge');

		return $this->ajax->resolve($contents);
	}

}
