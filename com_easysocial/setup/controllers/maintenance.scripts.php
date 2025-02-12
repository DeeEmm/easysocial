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

class EasySocialControllerMaintenanceScripts extends EasySocialSetupController
{
	public $limit = 100;

	public function __construct()
	{
		parent::__construct();
		$this->engine();
	}

	/**
	 * Retrieves the list of scripts that needs to be executed during the upgrade
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getScripts()
	{
		$maintenance = ES::maintenance();

		// Get previous version installed
		$previous = $this->getPreviousVersion('scriptversion');

		$files = array();

		// 1.3 UPDATE: No previous version means this is a fresh installation, then we only run the installed version script.
		if (empty($previous)) {
			$files = $maintenance->getScriptFiles($this->getInstalledVersion(), '==');
		} else {
			$files = $maintenance->getScriptFiles($previous);
		}

		if (empty($files)) {
			$msg = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_MAINTENANCE_NO_SCRIPTS_TO_EXECUTE');
		} else {
			$msg = JText::sprintf('COM_EASYSOCIAL_INSTALLATION_MAINTENANCE_TOTAL_FILES_TO_EXECUTE', count($files));
		}

		$result = array(
			'message' => $msg,
			'scripts' => $files
		);

		return $this->output($result);
	}

	/**
	 * Executes the maintenance script
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function run()
	{
		$this->checkDevelopmentMode();

		$script = $this->input->get('script', '', 'default');

		$maintenance = ES::maintenance();
		$state = $maintenance->runScript($script);

		if (!$state) {
			$message = $maintenance->getError();
			$result = $this->getResultObj($message, 0);
		} else {
			$title = $maintenance->getScriptTitle($script);
			$message = JText::sprintf('Executed script: %1s', $title);
			$result = $this->getResultObj($message, 1);
		}

		return $this->output($result);
	}

	/**
	 * Invoked when the script maintenance process is completed
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function complete()
	{
		$version = $this->getInstalledVersion();

		// Update the version in the database to the latest now
		$config = ES::table('Config');
		$exists = $config->load(array('type' => 'scriptversion'));
		$config->type = 'scriptversion';
		$config->value = $version;

		$config->store();

		$result = $this->getResultObj('Updated maintenance version.', 1, 'success');

		// Purge all old version files
		ES::purgeOldVersionScripts();

		return $this->output($result);
	}
}
