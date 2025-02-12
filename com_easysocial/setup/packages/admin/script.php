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

class com_EasySocialInstallerScript
{
	/**
	 * Triggers before the installers are copied
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function postflight()
	{
		ob_start();
		include(__DIR__ . '/setup.html');

		$contents = ob_get_contents();
		ob_end_clean();

		echo $contents;
	}

	/**
	 * Triggers after the installers are copied
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preflight()
	{
		// During the preflight, we need to create a new installer file in the temporary folder
		$file = JPATH_ROOT . '/tmp/easysocial.installation';

		// Determines if the installation is a new installation or old installation.
		$obj = new stdClass();
		$obj->new = false;
		$obj->step = 1;
		$obj->status = 'installing';

		$contents = json_encode($obj);

		if (!JFile::exists($file)) {
			JFile::write($file, $contents);
		}
	}

	/**
	 * Responsible to perform the installation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
	}

	/**
	 * Responsible to perform the uninstallation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function uninstall()
	{
		// @TODO: Disable modules

		// @TODO: Disable plugins
	}

	/**
	 * Responsible to perform component updates
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function update()
	{

	}
}
