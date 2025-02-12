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

ES::import('admin:/includes/model');

class EasySocialModelModules extends EasySocialModel
{
	public function __construct($config = [])
	{
		parent::__construct('modules', $config);
	}

	public function initStates()
	{
		parent::initStates();

		$published = $this->getUserStateFromRequest('published', '');

		$this->setState('published', $published);
	}

	/**
	 * Determines if the module manifest has been populated before
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function initialized()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_packages');
		$sql->column('count(1)');
		$sql->where('type', 'modules');
		$db->setQuery($sql);

		$initialized = $db->loadResult() > 0;

		return $initialized;
	}

	/**
	 * Retrieves the full manifest from the server
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function discoverManifest($apikey)
	{
		$connector = ES::connector(SOCIAL_SERVICE_PACKAGES_DISCOVER);

		$contents = $connector
						->setMethod('POST')
						->addQuery('key', $apikey)
						->addQuery('product', 'easysocial')
						->addQuery('type', 'modules')
						->execute()
						->getResult();

		$manifest = json_decode($contents);

		if ($manifest->state != 200) {
			return false;
		}

		foreach ($manifest->items as $module) {
			$package = ES::table('Package');
			$package->load([
				'group' => 'modules',
				'element' => $module->element
			]);

			$package->type = 'modules';
			$package->group = 'modules';
			$package->element = $module->element;
			$package->title = $module->name;
			$package->description = $module->description;
			$package->version = $module->version;
			$package->updated = '0000-00-00 00:00:00';
			$package->params = '';

			// Check if this module is installed
			$jmodule = $this->getJoomlaModule($module->element);
			$package->state = $jmodule ? true : false;

			if ($jmodule) {
				$jmodule->manifest_cache = json_decode($jmodule->manifest_cache);
				$package->state = version_compare($jmodule->manifest_cache->version, $package->version) === -1 ? SOCIAL_PACKAGE_NEEDS_UPDATING : $package->state;
			}

			$package->store();
		}

		return $manifest;
	}

	/**
	 * Retrieves the record for #__extensions
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getJoomlaModule($element)
	{
		$db = ES::db();

		$query = [
			'select * from `#__extensions`',
			'where `type`=' . $db->Quote('module'),
			'and `element`=' . $db->Quote($element),
			'and `state` !=' . $db->Quote(-1) // Discovered extensions are not installed yet.
		];

		$db->setQuery($query);
		$module = $db->loadObject();

		return $module;
	}

	/**
	 * Retrieves the modules manifest list
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getModules()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_packages');
		$sql->where('group', 'modules');

		// Search
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
			$sql->where('element', '%' . $search . '%', 'LIKE', 'OR');
		}

		$published = $this->getState('published');

		if ($published) {
			if ($published == 'installed') {
				$sql->where('state', SOCIAL_PACKAGE_NEEDS_UPDATING, '=');
				$sql->where('state', SOCIAL_PACKAGE_INSTALLED, '=', 'OR');
			}

			if ($published == 'notinstalled') {
				$sql->where('state', SOCIAL_PACKAGE_NOT_INSTALLED);
			}

			if ($published == 'updating') {
				$sql->where('state', SOCIAL_PACKAGE_NEEDS_UPDATING, '=');
			}
		}

		$db->setQuery($sql);

		$limit = $this->getState('limit', 0);

		if ($limit > 0) {
			$this->setState('limit' , $limit);

			// Get the limitstart.
			$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

			$this->setState('limitstart' , $limitstart);

			// Set the total number of items.
			$this->setTotal($sql->getTotalSql());

			$packages = $this->getData($sql);
		} else {
			$db->setQuery($sql);
			$packages = $db->loadObjectList();
		}

		if (!$packages) {
			return $packages;
		}

		foreach ($packages as $package) {
			$package->installed = false;

			if ($this->isInstalled($package->element)) {
				$jmodule = $this->getJoomlaModule($package->element);

				$jmodule->manifest_cache = json_decode($jmodule->manifest_cache);

				$package->installed = $jmodule->manifest_cache->version;
				$package->state = version_compare($package->installed, $package->version) === -1 ? SOCIAL_PACKAGE_NEEDS_UPDATING : $package->state;
			}
		}

		return $packages;
	}

	/**
	 * Determines if a Joomla module is installed on the site given the element.
	 * ModuleHelper in Joomla does not seem to provide any helpers to detect this
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isInstalled($element)
	{
		$db = ES::db();

		$query = [
			'select count(1) from `#__extensions`',
			'where `type`=' . $db->Quote('module'),
			'and `element`=' . $db->Quote($element),
			'and `state` != ' . $db->Quote(-1)
		];

		$db->setQuery($query);
		$installed = $db->loadResult() > 0 ? SOCIAL_PACKAGE_INSTALLED : SOCIAL_PACKAGE_NOT_INSTALLED;

		return $installed;
	}

	/**
	 * Purges non installed languages
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function purge()
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->delete('#__social_languages');
		$sql->where('state', SOCIAL_LANGUAGES_NOT_INSTALLED);

		$db->setQuery($sql);

		return $db->Query();
	}
}
