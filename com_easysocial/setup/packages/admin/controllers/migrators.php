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

class EasySocialControllerMigrators extends EasySocialController
{
	// Used for registering action logs
	protected $logContext = 'migrators';

	public function __construct()
	{
		parent::__construct();

		$this->registerTask('jomsocialevent', 'purgeHistory');
		$this->registerTask('jomsocialgroup', 'purgeHistory');
		$this->registerTask('joomla', 'purgeHistory');
		$this->registerTask('jomsocial', 'purgeHistory');
		$this->registerTask('cb', 'purgeHistory');
		$this->registerTask('kunena', 'purgeHistory');
		$this->registerTask('easyblog', 'purgeHistory');
	}

	/**
	 * Runs the checking of the extension
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function check()
	{
		ES::checkToken();

		$component = $this->input->get('component', '', 'cmd');

		$migrator = ES::migrators($component);
		$obj = $migrator->isComponentExist();

		$this->logAction('COM_ES_ACTION_LOG_STARTED_MIGRATION_PROCESS', [
			'type' => ucfirst($component)
		]);

		return $this->view->call(__FUNCTION__, $obj);
	}

	/**
	 * Processes the migration item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function process()
	{
		ES::checkToken();

		$component = $this->input->get('component', '', 'cmd');
		$item = $this->input->get('item', '', 'default');
		$mapping = $this->input->get('mapping', '', 'default');
		$updateconfig = $this->input->get('updateconfig', false, 'bool');

		$migrator = ES::migrators($component);
		$migrator->setUserMapping($mapping);

		// We need to check if we need to update config or not.
		if (empty($item)) {
			$configTable = ES::table('Config');
			$config = ES::registry();

			if ($configTable->load('site')) {

				$config->load($configTable->value);

				if ($config->get('points.enabled') == 1) {
					$config->set('points.enabled', 0);

					// Convert the config object to a json string.
					$jsonString = $config->toString();
					$configTable->set('value', $jsonString);

					// Try to store the configuration.
					if ($configTable->store()) {
						$updateconfig = true;

						// we need to reload the config
						$esConfig = new SocialConfig();
						$esConfig->reload();
					}
				}
			}
		}

		// Process the migration
		$obj = $migrator->process($item);

		// now we need to re-enable back the points setting.
		if ($obj->continue == false && $updateconfig == true) {
			$configTable = ES::table('Config');
			$config = ES::registry();

			if ($configTable->load('site')) {
				$config->load($configTable->value);
				$config->set('points.enabled', 1);

				// Convert the config object to a json string.
				$jsonString = $config->toString();
				$configTable->set('value', $jsonString);

				// Try to store the configuration.
				$configTable->store();
				$updateconfig = false;
			}
		}

		// Return the data back to the view.
		return $this->view->call(__FUNCTION__, $obj, $updateconfig);
	}

	/**
	 * Scans for rules throughout the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function scan()
	{
		// Check for request forgeries
		ES::checkToken();

		// Get the allowed rule scan sections
		$config = ES::config();

		// Retrieve info lib.
		$info = ES::info();

		// Retrieve the view.
		$view = ES::view('Privacy', true);

		// Get the current path that we should be searching for.
		$file = $this->input->files->get('file', []);

		// Retrieve the points model to scan for the path
		$model 	= ES::model('Privacy');

		$obj = new stdClass();

		// Format the output to display the relative path.
		$obj->file = str_ireplace( JPATH_ROOT , '' , $file );
		$obj->rules = $model->install($file);

		return $view->call(__FUNCTION__, $obj);
	}

	/**
	 * Responsible to purge migration history
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function purgeHistory()
	{
		ES::checkToken();

		$type = $this->getTask();
		$type = strtolower($type);

		$model = ES::model('Migrators');
		$model->purgeHistory($type);

		$this->logAction('COM_ES_ACTION_LOG_PURGED_MIGRATION_HISTORY', [
			'type' => ucfirst($type)
		]);

		$this->view->setMessage('COM_EASYSOCIAL_MIGRATOR_PURGE_SUCCESSFULLY');
		return $this->view->call('purgeHistory', $type);
	}
}
