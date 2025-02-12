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

class EasySocialViewStream extends EasySocialAdminView
{
	public function confirmDelete()
	{
		$theme  = ES::themes();
		$output = $theme->output('admin/stream/dialogs/delete');

		return $this->ajax->resolve($output);
	}

	public function confirmTrash()
	{
		$theme  = ES::themes();

		$output = $theme->output('admin/stream/dialogs/trash');
		return $this->ajax->resolve($output);
	}

	public function confirmArchive()
	{
		$theme  = ES::themes();

		$output = $theme->output('admin/stream/dialogs/archive');
		return $this->ajax->resolve($output);
	}

	public function confirmRestore()
	{
		$theme  = ES::themes();

		$output = $theme->output('admin/stream/dialogs/restore');
		return $this->ajax->resolve($output);
	}

	/**
	 * Displaying dialog for publishing stream.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function confirmPublish()
	{
		$theme = ES::themes();

		$output = $theme->output('admin/stream/dialogs/publish');
		return $this->ajax->resolve($output);
	}
}
