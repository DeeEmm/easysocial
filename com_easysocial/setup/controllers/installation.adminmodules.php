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

class EasySocialControllerInstallationAdminModules extends EasySocialSetupController
{
	/**
	 * Installation of admin modules on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();

		// Get the path to the current installer archive
		$tmpPath = $this->input->get('path', '', 'default');

		// Path to the archive
		$archivePath = $tmpPath . '/adminmodules.zip';

		if (!JFile::exists($archivePath)) {
			return $this->output($this->getResultObj(JText::_('COM_EASYSOCIAL_INSTALLATION_NO_MODULES_AVAILABLE'), true));
		}

		// Where should the archive be extrated to
		$path = $tmpPath . '/adminmodules';

		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			return $this->output($this->getResultObj('COM_EASYSOCIAL_INSTALLATION_ERROR_EXTRACT_MODULES', false));
		}

		// We need to exclude mod_easysocial_dummy since this module is added in the admin to satisfy phing's zip task.
		$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'mod_sample', 'mod_easysocial_dummy');
		$modules = JFolder::folders($path, '.', false, true, $exclude);

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		$db = ES::db();
		$sql = $db->sql();

		foreach ($modules as $module) {
			$moduleName = basename($module);

			// Get Joomla's installer instance
			$installer = new JInstaller();

			// Allow overwriting existing plugins
			$installer->setOverwrite(true);
			$state = $installer->install($module);

			if($state) {

				// We need to check if this module record already exists in module_menu or not. if not, lets create one for this module.
				$query = 'select a.`id`, b.`moduleid` from #__modules as a';
				$query .= ' left join `#__modules_menu` as b on a.`id` = b.`moduleid`';
				$query .= ' where a.`module` = ' . $db->Quote($moduleName);
				$query .= ' and b.`moduleid` is null';

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);

				$results = $db->loadObjectList();

				if ($results) {
					foreach ($results as $item) {
						$modMenu = new stdClass();
						$modMenu->moduleid = $item->id;
						$modMenu->menuid = 0;

						$db->insertObject('#__modules_menu', $modMenu);

						$jModule = JTable::getInstance('Module');
						$jModule->load($item->id);
						$jModule->position = 'cpanel';
						$jModule->published = 1;
						$jModule->store();
					}
				}
			}

			// Set the position of the module to cpanel
			$message = $state ? JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_SUCCESS_MODULE' , $moduleName ) : JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_ERROR_MODULE' , $moduleName );

			$class = $state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $message . '</div>';
		}

		return $this->output($result);
	}
}
