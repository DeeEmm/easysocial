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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationSync extends EasySocialSetupController
{
	/**
	 * Synchronizes necessary data during installation / upgrade
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();

		// Get this installations version
		$version = $this->getInstalledVersion();

		// Get previous version installed
		$previous = $this->getPreviousVersion('dbversion');

		// Get total tables affected
		$affected = ES::syncDB($previous);

		// If the previous version is empty, we can skip this altogether as we know this is a fresh installation
		if (!empty($affected)) {
			$result = $this->getResultObj(JText::sprintf('COM_EASYSOCIAL_INSTALLATION_MAINTENANCE_DB_SYNCED', $version), 1, 'Success');
		} else {
			$result = $this->getResultObj(JText::sprintf('COM_EASYSOCIAL_INSTALLATION_MAINTENANCE_DB_NOTHING_TO_SYNC', $version), 1, 'Success');
		}

		// @TODO: In the future synchronize database table indexes here.

		// Update the version in the database to the latest now
		$config = ES::table('Config');
		$exists = $config->load(array('type' => 'dbversion'));
		$config->type = 'dbversion';
		$config->value = $version;

		$config->store();

		return $this->output($result);
	}

}
