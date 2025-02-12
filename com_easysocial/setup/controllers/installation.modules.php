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

class EasySocialControllerInstallationModules extends EasySocialSetupController
{
	/**
	 * Installation of modules on the site
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
		$archivePath = $tmpPath . '/modules.zip';

		if (!JFile::exists($archivePath)) {
			return $this->output($this->getResultObj(JText::_('COM_EASYSOCIAL_INSTALLATION_NO_MODULES_AVAILABLE'), true));
		}
		// Where should the archive be extrated to
		$path = $tmpPath . '/modules';

		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			return $this->output($this->getResultObj('COM_EASYSOCIAL_INSTALLATION_ERROR_EXTRACT_MODULES', false));
		}

		// core modules must always enabled and published.
		$coreModules = array('mod_easysocial_sidebar');

		// Get a list of apps we should install.
		$modules = JFolder::folders( $path , '.' , false , true );

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($modules as $module) {
			$moduleName = basename($module);

			// Get Joomla's installer instance
			$installer = new JInstaller();

			// Allow overwriting existing plugins
			$installer->setOverwrite(true);
			$state = $installer->install($module);

			if($state) {
				$db = ES::db();
				$sql = $db->sql();

				$query = 'update `#__extensions` set `access` = 1';
				$query .= ' where `type` = ' . $db->Quote( 'module' );
				$query .= ' and `element` = ' . $db->Quote( $moduleName );
				$query .= ' and `access` = ' . $db->Quote( '0' );

				$sql->clear();
				$sql->raw( $query );
				$db->setQuery( $sql );
				$this->query($db);

				// we need to check if this module record already exists in module_menu or not. if not, lets create one for this module.
				$query = 'select a.`id`, b.`moduleid` from #__modules as a';
				$query .= ' left join `#__modules_menu` as b on a.`id` = b.`moduleid`';
				$query .= ' where a.`module` = ' . $db->Quote( $moduleName );
				$query .= ' and b.`moduleid` is null';

				$sql->clear();
				$sql->raw( $query );
				$db->setQuery( $sql );

				$results = $db->loadObjectList();

				$modId = 0;

				if( $results )
				{
					foreach( $results as $item )
					{
						// lets add into module menu.
						$modMenu = new stdClass();
						$modMenu->moduleid 	= $item->id;
						$modMenu->menuid 	= 0;

						$modId = $item->id;

						$db->insertObject( '#__modules_menu' , $modMenu );
					}
				}

				// check if this is core module or not.
				if ($modId && in_array($moduleName, $coreModules)) {

					$jMod = JTable::getInstance('Module');
					$jMod->load($modId);

					if (! $jMod->position) {
						$jMod->position = 'es-sidebar';
					}

					// hide module title.
					$jMod->showtitle = 0;
					$jMod->published = 1;
					$jMod->access = 1;
					$jMod->store();
				}
			}

			$message = $state ? JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_SUCCESS_MODULE' , $moduleName ) : JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_ERROR_MODULE' , $moduleName );

			$class = $state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $message . '</div>';
		}

		return $this->output($result);
	}
}
