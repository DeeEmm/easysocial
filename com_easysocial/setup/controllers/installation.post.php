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

class EasySocialControllerInstallationPost extends EasySocialSetupController
{
	/**
	 * Post installation process
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();

		$results = array();

		// Update the api key on the server with the one from the bootstrap
		$this->updateConfig('general.key', SI_KEY);

		$this->installActionLogs();

		// Update existing email logo override to a new home
		$this->updateEmailLogoOverrides();

		// Update existing video logo and watermark override to a new path
		$this->updateVideoImagesOverride();

		// Here we update the config for automatic purge sent email
		$previous = $this->getPreviousVersion('scriptversion');

		// This is hardcoded for upgrades from 1.x to 2.x
		$parts = explode('.', $previous);

		if ($parts[0] == 1) {
			$this->renameTemplateOverrides();
		}

		// Setup site menu.
		$results[] = $this->createMenu('site');

		// Now we need to update the #__update_sites row to include the api key as well as the domain
		$this->updateJoomlaUpdater();

		// Update the manifest_cache in #__extensions table
		$this->updateManifestCache();

		// Install initial story backgrounds
		$this->installDefaultBackgrounds();

		// Delete the easysocial from the Updates table
		$this->deleteUpdateRecord();

		// Uninstall unused modules
		$this->uninstallModules();

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($results as $obj) {
			$class = $obj->state ? 'success' : 'error';

			$result->message .= '<div class="text-' . $class . '">' . $obj->message . '</div>';
		}

		// Cleanup temporary files from the tmp folder
		$tmp = dirname(dirname(__FILE__)) . '/tmp';
		$folders = JFolder::folders($tmp, '.', false, true);

		if ($folders) {
			foreach ($folders as $folder) {
				@JFolder::delete($folder);
			}
		}

		// Here we need to delete those files that has been removed in 2.0 to avoid possible error
		$this->removeLegacyFiles();

		// Update installation package to 'launcher'
		$this->updatePackage();

		// re-eable system plugins.
		$this->enableSystemPlugins();

		// check if sef cache is writable or not.
		$this->verifySefCacheWrite();

		return $this->output($result);
	}

	/**
	 * Uninstallation of modules on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function uninstallModules()
	{
		$this->engine();

		$modules = [
			'mod_easysocial_easyblog_posts',
			'mod_easysocial_registration_requester'
		];

		$result = new stdClass();
		$result->state = true;
		$result->message = '';

		foreach ($modules as $moduleName) {

			$modulePath = JPATH_ROOT . '/modules/' . $moduleName;

			$state = true;

			if (!JFolder::exists($modulePath)) {
				continue;
			}

			$state = JFolder::delete($modulePath);

			if ($state) {
				$db = ES::db();
				$sql = $db->sql();

				// Remove from extensions table
				$query = 'delete from `#__extensions` ';
				$query .= ' where `type` = ' . $db->Quote('module');
				$query .= ' and `element` = ' . $db->Quote($moduleName);

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);
				$db->query();

				// we need to check if this module record exists in module or not. if yes, delete it.
				$query = 'select `id` from #__modules';
				$query .= ' where `module` = ' . $db->Quote($moduleName);

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);

				$results = $db->loadObjectList();

				if ($results) {
					foreach ($results as $item) {
						// Remove from Modules table
						$query = 'delete from `#__modules` ';
						$query .= ' where `id` = ' . $db->Quote($item->id);

						$sql->clear();
						$sql->raw($query);
						$db->setQuery($sql);
						$db->query();

						// Remove from Module_menu table if any
						$query = 'delete from `#__modules_menu` ';
						$query .= ' where `moduleid` = ' . $db->Quote($item->id);

						$sql->clear();
						$sql->raw($query);
						$db->setQuery($sql);
						$db->query();
					}
				}
			}
		}
	}

	/**
	 * Retrieves the main menu item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMainMenuType()
	{
		$this->engine();

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__menu');
		$sql->column('menutype');
		$sql->where('home', '1');

		$db->setQuery($sql);
		$menuType = $db->loadResult();

		return $menuType;
	}

	/**
	 * Setup and installs initial backgrounds for story
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function installDefaultBackgrounds()
	{
		$this->engine();

		$db = ES::db();
		$query = 'SELECT COUNT(1) FROM `#__social_backgrounds`';
		$db->setQuery($query);

		$exists = $db->loadResult() > 0;

		// Don't do anything when there is already backgrounds installed
		if ($exists) {
			return;
		}

		$backgrounds = array(
			array(
				'type' => 'gradient',
				'first_color' => '#FFD1CD',
				'second_color' => '#D5FFFA',
				'text_color' => '#000000'
			),
			array(
				'type' => 'gradient',
				'first_color' => '#FFAFBC',
				'second_color' => '#FFC3A0',
				'text_color' => '#000000'
			),
			array(
				'type' => 'gradient',
				'first_color' => '#DFAFFD',
				'second_color' => '#4E6FFB',
				'text_color' => '#FFFFFF'
			),
			array(
				'type' => 'gradient',
				'first_color' => '#87FCC4',
				'second_color' => '#EBE7B3',
				'text_color' => '#000000'
			),
			array(
				'type' => 'gradient',
				'first_color' => '#ED9286',
				'second_color' => '#D73E68',
				'text_color' => '#FFFFFF'
			)
		);

		$i = 1;

		foreach ($backgrounds as $background) {

			$obj = ES::table('Background');
			$obj->title = JText::sprintf('Background %1$s', $i);
			$obj->state = true;

			$params = new JRegistry($background);
			$obj->params = $params->toString();
			$obj->store();

			$i++;
		}
	}

	/**
	 * Retrieves the extension id
	 *
	 * @since	2.0.10
	 * @access	public
	 */
	public function getExtensionId()
	{
		$this->engine();

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__extensions', 'id');
		$sql->where('element', 'com_easysocial');

		$db->setQuery($sql);

		// Get the extension id
		$extensionId = $db->loadResult();

		return $extensionId;
	}

	/**
	 * Creates the default menu for EasySocial
	 *
	 * @since	2.0.10
	 * @access	public
	 */
	public function createMenu()
	{
		$this->engine();

		$db = ES::db();

		// Get the extension id
		$extensionId = $this->getExtensionId();

		// Get the main menu that is used on the site.
		$menuType = $this->getMainMenuType();

		if (!$menuType) {
			return false;
		}

		$sql = $db->sql();

		$sql->select('#__menu');
		$sql->column('COUNT(1)');
		$sql->where('link', '%index.php?option=com_easysocial%', 'LIKE');
		$sql->where('type', 'component');
		$sql->where('client_id', 0);

		$db->setQuery($sql);

		$exists	= $db->loadResult();

		if ($exists) {
			// we need to update all easysocial menu item with this new component id.
			$query = 'update `#__menu` set component_id = ' . $db->Quote( $extensionId );
			$query .= ' where `link` like ' . $db->Quote( '%index.php?option=com_easysocial%' );
			$query .= ' and `type` = ' . $db->Quote( 'component' );
			$query .= ' and `client_id` = ' . $db->Quote( '0' );

			$sql->clear();
			$sql->raw( $query );
			$db->setQuery( $sql );
			$this->query($db);

			return $this->getResultObj(JText::_('COM_EASYSOCIAL_INSTALLATION_SITE_MENU_UPDATED'), true);
		}

		$menu = JTable::getInstance('Menu');
		$menu->menutype = $menuType;
		$menu->title = JText::_('COM_EASYSOCIAL_INSTALLATION_DEFAULT_MENU_COMMUNITY');
		$menu->alias = 'community';
		$menu->note = '';
		$menu->path = 'easysocial';
		$menu->link = 'index.php?option=com_easysocial&view=dashboard';
		$menu->type = 'component';
		$menu->published = 1;
		$menu->parent_id = 1;
		$menu->component_id = $extensionId;
		$menu->client_id = 0;
		$menu->language = '*';
		$menu->img = '';
		$menu->params = '';

		$menu->setLocation( '1' , 'last-child' );

		$state = $menu->store();

		// Assign modules to dashboard menu
		$this->installModulesMenu($menu->id);

		return $this->getResultObj(JText::_('COM_EASYSOCIAL_INSTALLATION_SITE_MENU_CREATED'), true);
	}


	/**
	 * install module and assign to unity view
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installModulesMenu( $dashboardMenuId = null )
	{
		$this->engine();

		$db 	= ES::db();
		$sql 	= $db->sql();

		$modulesToInstall = array();

		// register modules here.

		// online user
		$modSetting = new stdClass();
		$modSetting->title 		= 'Online Users';
		$modSetting->name 		= 'mod_easysocial_users';
		$modSetting->position 	= 'es-dashboard-sidebar-bottom';
		$modSetting->config 	= array('filter' 	=> 'online',
										'total' 	=> '5',
										'ordering' 	=> 'name',
										'direction' => 'asc' );
		$modulesToInstall[] 	= $modSetting;

		// Recent user
		$modSetting = new stdClass();
		$modSetting->title 		= 'Recent Users';
		$modSetting->name 		= 'mod_easysocial_users';
		$modSetting->position 	= 'es-dashboard-sidebar-bottom';
		$modSetting->config 	= array('filter' 	=> 'recent',
										'total' 	=> '5',
										'ordering' 	=> 'registerDate',
										'direction' => 'desc' );
		$modulesToInstall[] 	= $modSetting;

		// Recent albums
		$modSetting = new stdClass();
		$modSetting->title 		= 'Recent Albums';
		$modSetting->name 		= 'mod_easysocial_albums';
		$modSetting->position 	= 'es-dashboard-sidebar-bottom';
		$modSetting->config 	= array();
		$modulesToInstall[] 	= $modSetting;

		// leaderboard
		$modSetting = new stdClass();
		$modSetting->title 		= 'Leaderboard';
		$modSetting->name 		= 'mod_easysocial_leaderboard';
		$modSetting->position 	= 'es-dashboard-sidebar-bottom';
		$modSetting->config 	= array('total' => '5');
		$modulesToInstall[] 	= $modSetting;

		// Dating Search
		$modSetting = new stdClass();
		$modSetting->title = 'Search For People';
		$modSetting->name = 'mod_easysocial_dating_search';
		$modSetting->position = 'es-users-sidebar-bottom';
		$modSetting->config = array('searchname' 	=> '1',
										'searchgender' 	=> '1',
										'searchage' 	=> '1',
										'searchdistance' => '1' );
		$modulesToInstall[] 	= $modSetting;


		// real work here.
		foreach( $modulesToInstall as $module )
		{
			$jMod	= JTable::getInstance( 'Module' );

			$jMod->title 		= $module->title;
			$jMod->ordering 	= $this->getModuleOrdering( $module->position );
			$jMod->position 	= $module->position;
			$jMod->published 	= 1;
			$jMod->module 		= $module->name;
			$jMod->access 		= 1;

			if( $module->config )
			{
				$jMod->params 		= ES::json()->encode( $module->config );
			}
			else
			{
				$jMod->params 		= '';
			}

			$jMod->client_id 	= 0;
			$jMod->language 	= '*';

			$state = $jMod->store();

			if( $state && $dashboardMenuId )
			{
				// lets add into module menu.
				$modMenu = new stdClass();
				$modMenu->moduleid 	= $jMod->id;
				$modMenu->menuid 	= $dashboardMenuId;

				$state	= $db->insertObject( '#__modules_menu' , $modMenu );
			}

		}

	}


	/**
	 * get ordering based on the module position.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getModuleOrdering($position)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select `ordering` from `#__modules` where `position` = ' . $db->Quote( $position );
		$query .= ' order by `ordering` desc limit 1';
		$sql->raw( $query );

		$db->setQuery( $sql );

		$result = $db->loadResult();

		return ( $result ) ? $result + 1 : 1;

	}

	/**
	 * Verify if system has the write permision on sef cache folder
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public function verifySefCacheWrite()
	{
		$cacheLib = ES::Filecache();

		$fileFolder = SOCIAL_FILE_CACHE_DIR;
		$filepath = $cacheLib->getFilePath();
		$hasError = false;

		// default warning message on folder
		ES::language()->loadSite();
		$warning = JText::sprintf('COM_ES_SEF_CACHE_WARNING_NO_FOLDER_PERMISSION', '/media/com_easysocial/cache');

		// check if folder exists or not.
		if (!JFolder::exists($fileFolder)) {
			$canWrite = @JFolder::create($fileFolder);
			$hasError = $canWrite ? false : true;
		}

		// check if folder is writable or not.
		if (!$hasError) {
			// check if can write into the folder.
			$testFile = str_replace('-cache.php', '-test.php', $filepath);
			$content = '';
			$canWrite = JFile::write($testFile, $content);
			$hasError = !$canWrite;

			if ($canWrite) {
				// delete the test file.
				JFile::delete($testFile);
			}
		}

		if (!$hasError && JFile::exists($filepath)) {

			// warning message on file.
			$relativePath = str_replace(JPATH_ROOT, '', $filepath);
			$warning = JText::sprintf('COM_ES_SEF_CACHE_WARNING_NO_FILE_PERMISSION', $relativePath);

			// can write into this file?
			$content = file_get_contents($filepath);
			$canWrite = JFile::write($filepath, $content);
			$hasError = !$canWrite;
		}

		if ($hasError) {
			// has error. we will disable the sef cache setting.
			$this->disableSEFCache($warning);
		}

		return true;
	}

	/**
	 * Method to disable the sef caching
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	private function disableSEFCache($warningMsg = '')
	{
		$config = ES::config();

		// append user id disabled. let update the seo use id to false.
		$config->set('seo.cachefile.enabled', "0");
		$config->set('seo.cachefile.warning', $warningMsg);

		// Convert the config object to a json string.
		$jsonString = $config->toString();

		$configTable = ES::table('Config');
		if (!$configTable->load('site')) {
			$configTable->type  = 'site';
		}

		$configTable->set('value' , $jsonString);
		$state = $configTable->store();

		return $state;
	}


	/**
	 * Update installation package to launcher package to update issue via update button
	 *
	 * @since	2.1.3
	 * @access	public
	 */
	public function updatePackage()
	{
		// now we need to update the ES_INSTALLER to launcher to that the update button will
		// work correctly. #1558
		$path = JPATH_ADMINISTRATOR . '/components/com_easysocial/setup/bootstrap.php';

		// Read the contents
		$contents = file_get_contents($path);

		$contents = str_ireplace("define('SI_INSTALLER', 'full');", "define('SI_INSTALLER', 'launcher');", $contents);
		$contents = preg_replace('/define\(\'SI_PACKAGE\', \'.*\'\);/i', "define('SI_PACKAGE', '');", $contents);

		JFile::write($path, $contents);
	}


	/**
	 * Once the installation is completed, we need to update Joomla's update site table with the appropriate data
	 *
	 * @since	2.0.10
	 * @access	public
	 */
	public function updateJoomlaUpdater()
	{
		$this->engine();

		$extensionId = $this->getExtensionId();

		$db = JFactory::getDBO();
		$query = array();
		$query[] = 'SELECT ' . $db->quoteName('update_site_id') . ' FROM ' . $db->quoteName('#__update_sites_extensions');
		$query[] = 'WHERE ' . $db->quoteName('extension_id') . '=' . $db->Quote($extensionId);

		$query = implode(' ', $query);
		$db->setQuery($query);

		$updateSiteId = $db->loadResult();

		$defaultLocation = 'https://stackideas.com/jupdates/manifest/easysocial';

		// For some Joomla versions, there is no tables/updatesite.php
		// Hence, the JTable::getInstance('UpdateSite') will return null
		$table = JTable::getInstance('UpdateSite');

		if ($table) {
			// Now we need to update the url
			$exists = $table->load($updateSiteId);

			if (!$exists) {
				return false;
			}

			$table->location = $defaultLocation;
			$table->store();

		} else {
			$query	= 'UPDATE '. $db->quoteName('#__update_sites')
					. ' SET ' . $db->quoteName('location') . ' = ' . $db->Quote($defaultLocation)
					. ' WHERE ' . $db->quoteName('update_site_id') . ' = ' . $db->Quote($updateSiteId);
			$db->setQuery($query);
			$this->query($db);
		}

		return true;
	}

	/**
	 * Update the manifest cache
	 *
	 * @since   2.0.13
	 * @access  public
	 */
	public function updateManifestCache()
	{
		$extensionId = $this->getExtensionId();
		$manifest_details = JInstaller::parseXMLInstallFile(JPATH_ROOT. '/administrator/components/com_easysocial/easysocial.xml');
		$manifest = json_encode($manifest_details);

		// For some Joomla versions, there is no tables/Extension.php
		// Hence, the JTable::getInstance('Extension') will return null
		$table = JTable::getInstance('Extension');

		if ($table) {
			$exists = $table->load($extensionId);

			if (!$exists) {
				return false;
			}

			$table->manifest_cache = $manifest;
			$table->store();
		} else {
			$query	= 'UPDATE '. $db->quoteName('#__extensions')
					. ' SET ' . $db->quoteName('manifest_cache') . ' = ' . $db->Quote($manifest)
					. ' WHERE ' . $db->quoteName('extension_id') . ' = ' . $db->Quote($extensionId);
			$db->setQuery($query);
			$this->query($db);
		}
	}

	/**
	 * Delete record in updates table
	 *
	 * @since   2.0.13
	 * @access  public
	 */
	public function deleteUpdateRecord()
	{
		$db = ES::db();

		$query = 'DELETE FROM ' . $db->quoteName('#__updates') . ' WHERE ' . $db->quoteName('extension_id') . '=' . $db->Quote($this->getExtensionId());
		$db->setQuery($query);
		$this->query($db);
	}

	/**
	 * Removed unused files from the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function removeLegacyFiles()
	{
		// Backend files
		$files = array();
		$files[] = '/components/com_easysocial/includes/crawler/hooks/images.php';
		$files[] = '/components/com_easysocial/themes/default/settings/form/pages/general/emails.php';
		$files[] = '/components/com_easysocial/themes/default/settings/form/pages/general/emails.js';
		$files[] = '/components/com_easysocial/defaults/sidebar/access.json';
		$files[] = '/components/com_easysocial/defaults/sidebar/maintenance.json';
		$files[] = '/components/com_easysocial/defaults/sidebar/reactions.json';
		$files[] = '/components/com_easysocial/themes/default/settings/form/pages/general/login.php';
		$files[] = '/components/com_easysocial/themes/default/settings/form/pages/general/login.js';
		$files[] = '/components/com_easysocial/themes/default/settings/form/pages/users/layout.php';

		foreach ($files as $file) {

			// Append administrator path
			$file = JPATH_ADMINISTRATOR . $file;

			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}

		// Frontend files
		$frontFiles = array();
		$frontFiles[] = '/components/com_easysocial/views/polls/metadata.xml';
		$frontFiles[] = '/components/com_easysocial/themes/wireframe/events/create/default.php';
		$frontFiles[] = '/components/com_easysocial/themes/wireframe/events/create/category.item.php';
		$frontFiles[] = '/components/com_easysocial/themes/wireframe/events/create/default.js';

		foreach ($frontFiles as $file) {

			// Append full path
			$file = JPATH_ROOT . $file;

			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}

		// Media files
		$mediaFiles = array();
		$mediaFiles[] = '/media/com_easysocial/apps/user/followers/themes/default/widgets/dashboard/suggestions.js';

		foreach ($mediaFiles as $file) {

			$file = JPATH_ROOT . $file;

			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}
	}

	/**
	 * Update old email logo override to new path
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateEmailLogoOverrides()
	{
		if (ES::hasOverride('email_logo')) {
			return;
		}

		$assets = ES::assets();

		// Set the logo for the generic email template
		$override = JPATH_ROOT . '/templates/' . $assets->getJoomlaTemplate() . '/html/com_easysocial/emails/logo.png';
		$exists = JFile::exists($override);

		// Copy the file over but retain original logo
		if ($exists) {
			$newOverride = JPATH_ROOT . '/images/easysocial_override/email_logo.png';

			// Normalize seprator
			$override = ES::normalizeSeparator($override);
			$newOverride = ES::normalizeSeparator($newOverride);

			$logo = file_get_contents($override);

			JFile::write($newOverride, $logo);
		}
	}

	/**
	 * Update old email logo override to new path
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateVideoImagesOverride()
	{
		$overrides = array('video_logo', 'video_watermark');

		foreach ($overrides as $file) {
			if (ES::hasOverride($file)) {
				continue;
			}

			$assets = ES::assets();

			$tmp = explode('_', $file);

			// Set the logo for the generic email template
			$override = JPATH_ROOT . '/templates/' . $assets->getJoomlaTemplate() . '/html/com_easysocial/videos/' . $tmp[1] . '.png';
			$exists = JFile::exists($override);

			// Copy the file over but retain original logo
			if ($exists) {
				$newOverride = JPATH_ROOT . '/images/easysocial_override/' . $file . '.png';

				// Normalize seprator
				$override = ES::normalizeSeparator($override);
				$newOverride = ES::normalizeSeparator($newOverride);

				$logo = file_get_contents($override);

				JFile::write($newOverride, $logo);
			}
		}
	}

	/**
	 * Rename template overrides folder
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renameTemplateOverrides()
	{
		$this->engine();

		// Get current site's template
		$model = ES::model('Themes');
		$template = $model->getCurrentTemplate();

		// Check if there is a template override for component
		$path = JPATH_ROOT . '/templates/' . $template . '/html/com_easysocial';

		$date = JFactory::getDate();
		$postfix = $date->format('j') . '-' . $date->format('n') . '-' . $date->format('Y');

		// Try to rename the folder
		if (JFolder::exists($path)) {
			$newPath = $path . '_' . $postfix;

			JFolder::move($path, $newPath);
		}

		// Now we need to rename module folders
		$path = JPATH_ROOT . '/templates/' . $template . '/html';
		$pattern = 'mod_easysocial_*';

		$folders = JFolder::folders($path, $pattern, false, true);

		if ($folders) {
			foreach ($folders as $folder) {

				// We need to rename it this way so that in the next update, the backup folder wont be renamed again
				$newPath = str_ireplace('mod_easysocial', 'backups_easysocial', $folder);
				$newPath = $newPath . '_' . $postfix;

				JFolder::move($folder, $newPath);
			}
		}
	}

	/**
	 * Method to insert query to support integration with Joomla Action Logs
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function installActionLogs()
	{
		$this->engine();

		// If the Joomla version is not starting from 3.9, do not need to proceed
		// Because Joomla User Actions log only available from version 3.9 onwards.
		$currentJVersion = ESUtility::getJoomlaVersion();
		$isSupported = version_compare('3.9', $currentJVersion) !== 1;

		if (!$isSupported) {
			return $this->getResultObj('COM_ES_INSTALLATION_USER_ACTIONS_LOGS_NOT_SUPPORTED', true);
		}

		$db = ES::db();

		$query = 'SELECT COUNT(1) FROM `#__action_logs_extensions` WHERE `extension`=' . $db->quote('com_easysocial');

		$db->setQuery($query);
		$exists = $db->loadResult() > 0;

		if (!$exists) {
			$query = 'INSERT INTO `#__action_logs_extensions` (`extension`) VALUES (' . $db->Quote('com_easysocial') . ')';

			$db->setQuery($query);
			$db->Query();
		}
	}
}
