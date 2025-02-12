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

class pkg_toolbarInstallerScript
{
	/**
	 * After the installation, we also want to enable the plugin and module
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function postflight($type, $parent)
	{
		// Install foundry
		$this->installFoundry($parent);

		// Activate foundry plugin
		$this->activatePlugin();

		// Only run this for is fresh installation
		if ($type === 'install') {
			$this->configureModule();
		}
	}

	public function installFoundry($parent)
	{
		// Get the temporary path from the server.
		$parent = $parent->getParent();
		$sourcePath = $parent->getPath('source');

		$tmpFoundryPath = $sourcePath . '/packages/foundry/pkg_foundry.zip';

		if (!JFile::exists($tmpFoundryPath)) {
			return;
		}

		$package = JInstallerHelper::unpack($tmpFoundryPath);
		$xmlFile = $package['dir'] . '/pkg_foundry.xml';

		$contents = file_get_contents($xmlFile);
		$parser = simplexml_load_string($contents);

		$version = $parser->xpath('version');
		$version = (string) $version[0];

		if (!$this->isLatestFoundry($version)) {
			return;
		}

		$installer = JInstaller::getInstance();
		$state = $installer->update($package['dir']);

		// Clean up the installer
		// JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		// need to rebuild because of the package installer conflict
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_installer/models', 'InstallerModel');
		$model = JModelLegacy::getInstance('Updatesites', 'InstallerModel');

		if (!$model) {
			return;
		}
		
		$model->rebuild();
	}
	
	public function activatePlugin()
	{
		$db = JFactory::getDBO();

		$query = [];

		$query[] = 'UPDATE ' . $db->quoteName('#__extensions');
		$query[] = 'SET ' . $db->quoteName('enabled') . ' = 1';
		$query[] = 'WHERE ' . $db->quoteName('element') . ' = ' . $db->Quote('foundry');
		$query[] = 'AND ' . $db->quoteName('type') . ' = ' . $db->Quote('plugin');

		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->execute();
	}

	public function configureModule()
	{
		$db = JFactory::getDBO();

		// Get the menu id
		$query = [];
		$query[] = 'SELECT a.id FROM ' . $db->quoteName('#__modules') . ' AS a';
		$query[] = 'LEFT JOIN ' . $db->quoteName('#__modules_menu') . ' AS b ON a.`id` = b.`moduleid`';
		$query[] = 'WHERE a.' . $db->quoteName('module') . ' = ' . $db->Quote('mod_stackideas_toolbar');
		$query[] = 'AND b.' . $db->quoteName('moduleid') . ' IS NULL';
		$query = implode(' ', $query);
		
		$db->setQuery($query);
		$moduleId = $db->loadResult();

		if (!$moduleId) {
			return true;
		}

		// Migrate setting from component
		$params = $this->migrateSettings();

		$jMod = JTable::getInstance('Module');
		$jMod->load($moduleId);
		$jMod->title = 'StackIdeas Toolbar';

		// Default it to stackideas-toolbar position.
		$jMod->position = 'stackideas-toolbar';

		$jMod->published = 1;
		$jMod->showtitle = 0;
		$jMod->params = $params;
		$jMod->access = 1;
		$jMod->client_id = 0;
		$jMod->language = '*';

		$state = $jMod->store();

		if (!$state) {
			return false;
		}

		// Adding this to module menu.
		$modMenu = new stdClass();
		$modMenu->moduleid = $jMod->id;
		$modMenu->menuid = 0;

		$db->insertObject('#__modules_menu', $modMenu);

		return true;
	}

	public function migrateSettings()
	{
		$db = JFactory::getDBO();

		// get the original params from #__extensions
		$query = 'SELECT ' . $db->quoteName('params') . ' FROM ' . $db->quoteName('#__extensions') . ' WHERE ' . $db->quoteName('element') . '=' . $db->Quote('mod_stackideas_toolbar');
		$db->setQuery($query);
		$oriParams = $db->loadResult();
		$oriParams = new JRegistry($oriParams);

		$components = [
			'payplans' => [
				'table' => 'payplans_config'
			],
			'easyblog' => [
				'table' => 'easyblog_configs',
				'row' => 'config',
				'column' => 'params',
				'mapping' => [
					'enable_toolbar' => 'layout_toolbar',
					'eb_layout_home' => 'layout_latest',
					'eb_layout_categories' => 'layout_categories',
					'eb_layout_tags' => 'layout_tags',
					'eb_layout_bloggers' => 'layout_bloggers',
					'eb_layout_teamblogs' => 'layout_teamblog',
					'eb_layout_archives' => 'layout_archives',
					'eb_layout_calendar' => 'layout_calendar',
					'eb_layout_search' => 'layout_search',
					'eb_layout_user_dropdown' => 'layout_showmoresettings',
					'eb_layout_login' => 'layout_login'
				]
			], 
			'easydiscuss' => [
				'table' => 'discuss_configs',
				'row' => 'config',
				'column' => 'params',
				'mapping' => [
					'enable_toolbar' => 'layout_enabletoolbar',
					'ed_layout_home' => 'layout_toolbarhome',
					'ed_layout_categories' => 'layout_toolbarcategories',
					'ed_layout_tags' => 'layout_toolbartags',
					'ed_layout_users' => 'layout_toolbarusers',
					'ed_layout_badges' => 'layout_toolbarbadges',
					'ed_layout_conversation' => 'layout_toolbar_conversation',
					'ed_layout_notification' => 'layout_toolbar_notification',
					'ed_layout_search' => 'layout_toolbar_searchbar',
					'ed_layout_subscribe' => 'main_rss',
					'ed_layout_user_dropdown' => 'layout_toolbarprofile',
					'ed_layout_login' => 'layout_toolbarlogin'
				]
			], 
			'easysocial' => [
				'table' => 'social_config',
				'row' => 'site',
				'column' => 'value',
				'mapping' => [
					'enable_toolbar' => 'general.layout.toolbar',
					'es_layout_search' => 'general.layout.toolbarsearch',
					'es_layout_friends' => 'general.layout.toolbarfriends',
					'es_layout_conversations' => 'general.layout.toolbarconversations',
					'es_layout_notifications' => 'general.layout.toolbarnotifications',
					'es_layout_mobileapp' => 'general.layout.toolbarmobileapp',
					'es_layout_guests' => 'general.layout.toolbarguests',
					'es_layout_searchguests' => 'general.layout.toolbarsearchguests'
				]
			]
		];

		$jConfig = JFactory::getConfig();
		$prefix = $jConfig->get('dbprefix');

		// Default to easysocial
		$globalMenuSetting = 'toolbardefault-easysocial';

		foreach ($components as $component => $data) {
			$query = "SHOW TABLES LIKE '" . $prefix . $data['table'] . "%'";
			$db->setQuery($query);
			$result = $db->loadResult();

			if (!$result) {
				continue;
			}

			$globalMenuSetting = 'toolbardefault-' . $component;

			// For payplans, we just need one config.
			if ($component === 'payplans') {
				$query = 'SELECT ' . $db->quoteName('value') . ' FROM ' . $db->quoteName('#__payplans_config') . ' WHERE ' . $db->quoteName('key') . '=' . $db->Quote('layout_toolbar');
				$db->setQuery($query);
				$ppToolbarEnabled = $db->loadResult();

				$paramValue = $ppToolbarEnabled ? 'toolbardefault-' . $component : 'disabled';
				$oriParams->set($component, $paramValue);
				continue;
			}

			$configColumn = $component === 'easysocial' ? 'type' : 'name';

			$query = 'SELECT ' . $db->quoteName($data['column']) . ' FROM ' . $db->quoteName('#__' . $data['table']) . ' WHERE ' . $db->quoteName($configColumn) . '=' . $db->Quote($data['row']);
			$db->setQuery($query);
			$configString = $db->loadResult();

			$params = new JRegistry($configString);

			foreach ($data['mapping'] as $key => $value) {
				$newValue = !is_null($params->get($value)) ? $params->get($value) : '1';

				if ($key === 'enable_toolbar') {
					$paramValue = $newValue ? 'toolbardefault-' . $component : 'disabled';
					$oriParams->set($component, $paramValue);
					continue;
				}

				$oriParams->set($key, $newValue);
			}
		}

		// set the globalMenu setting 
		$oriParams->set('globalMenu', $globalMenuSetting);
		return $oriParams->toString();
	}

	/**
	 * Determine which foundry is latest
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function isLatestFoundry($version)
	{
		$db = JFactory::getDBO();

		$query = 'SELECT ' . $db->quoteName('manifest_cache') . ' FROM ' . $db->quoteName('#__extensions') . ' WHERE ' . $db->quoteName('element') . '=' . $db->Quote('pkg_foundry');
		$db->setQuery($query);

		$manifestString = $db->loadResult();

		if (!$manifestString) {
			return true;
		}

		$manifestData = json_decode($manifestString);

		return version_compare($manifestData->version, $version) < 1;
	}
}