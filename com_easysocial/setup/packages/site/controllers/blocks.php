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

class EasySocialControllerBlocks extends EasySocialController
{
	/**
	 * Blocks a user
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function store()
	{
		ES::requireLogin();
		ES::checkToken();

		$target = $this->input->get('target', 0, 'int');
		$reason = $this->input->get('reason', '', 'default');

		if (!$target) {
			$this->view->setError('COM_EASYSOCIAL_INVALID_USER_ID_PROVIDED');
			return $this->view->call(__FUNCTION__, $target);
		}

		// Load up the block library
		$lib = ES::blocks();
		$lib->block($target, $reason);

		return $this->view->call(__FUNCTION__, $target);
	}

	/**
	 * Unblock a user
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function unblock()
	{
		ES::requireLogin();
		ES::checkToken();

		$target = $this->input->get('target', 0, 'int');

		if (!$target) {
			$this->view->setError('COM_EASYSOCIAL_INVALID_USER_ID_PROVIDED');
			return $this->view->call(__FUNCTION__, $target);
		}

		// Load up the block library
		$lib = ES::blocks();
		$lib->unblock($target);

		return $this->view->call(__FUNCTION__, $target);
	}
}
