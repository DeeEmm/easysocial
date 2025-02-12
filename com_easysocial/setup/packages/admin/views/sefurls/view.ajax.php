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

class EasySocialViewSefUrls extends EasySocialAdminView
{
	/**
	 * Render delete confirmation dialog
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		$theme 	= ES::themes();
		$contents = $theme->output('admin/sefurls/dialogs/delete');

		$this->ajax->resolve($contents);
	}

	/**
	 * Render purge confirmation dialog
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function confirmPurge()
	{
		$theme 	= ES::themes();
		$contents = $theme->output('admin/sefurls/dialogs/purge');

		$this->ajax->resolve($contents);
	}

	/**
	 * Clear sef cache warning message
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public function hideWarning()
	{
		$config = ES::config();

		// clear the cache warning message
		$config->set('seo.cachefile.warning', '');

		// Convert the config object to a json string.
		$jsonString = $config->toString();

		$configTable = ES::table('Config');
		if (!$configTable->load('site')) {
			$configTable->type  = 'site';
		}

		$configTable->set('value' , $jsonString);
		$state = $configTable->store();

		$this->ajax->resolve($state);
	}

}
