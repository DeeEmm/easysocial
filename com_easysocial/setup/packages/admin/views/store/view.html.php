<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialViewStore extends EasySocialAdminView
{
	/**
	 * Post processing after user failed the order
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function fail($app)
	{
		$this->redirect('index.php?option=com_easysocial&view=apps&layout=fail&id=' . $app->id);
	}

	/**
	 * Post processing after generating apps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function refresh($layout = 'store')
	{
		$this->info->set($this->getMessage());

		if ($layout != 'store') {
			return $this->redirect('index.php?option=com_easysocial&view=apps');
		}

		$this->redirect('index.php?option=com_easysocial&view=apps&layout=store');
	}

	/**
	 * Post processing after installation is completed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function install()
	{
		$this->info->set($this->getMessage());

		// Determines if there is a return url provided
		$redirect = $this->getReturnUrl('index.php?option=com_easysocial&view=apps&layout=store');

		$this->redirect($redirect);
	}
}
