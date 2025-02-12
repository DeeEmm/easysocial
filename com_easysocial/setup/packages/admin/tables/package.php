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

ES::import('admin:/tables/table');

class SocialTablePackage extends SocialTable
{
	public $id = null;
	public $type = null;
	public $group = null;
	public $element = null;
	public $title = null;
	public $description = null;
	public $updated = null;
	public $state = null;
	public $params = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_packages', 'id', $db);
	}

	/**
	 * Downloads the package
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function download()
	{
		// Get the api key
		$config = ES::config();
		$key = $config->get('general.key');

		// Download the language file
		$connector = ES::connector(SOCIAL_SERVICE_PACKAGES_DOWNLOAD);
		$result = $connector
					->setMethod('POST')
					->addQuery('key', $key)
					->addQuery('domain', rtrim(JURI::root(), '/'))
					->addQuery('type', $this->type)
					->addQuery('group', $this->group)
					->addQuery('package', $this->element)
					->execute()
					->getResult();

		$md5 = md5(ES::date()->toSql());
		$state = json_decode($result);

		if (is_object($state) && $state->code == 400) {
			$this->setError($state->error);
			return false;
		}

		// Create a temporary storage for this file
		$storage = SOCIAL_TMP . '/' . $md5 . '.zip';

		$state = JFile::write($storage, $result);

		// Set the path for the extracted folder
		$extractedFolder = SOCIAL_TMP . '/' . $md5;

		// Extract the language's archive file
		$state = ESArchive::extract($storage, $extractedFolder);


		// Delete the zip
		JFile::delete($storage);

		return $extractedFolder;
	}

	/**
	 * Retrieve the extension id associated with this package
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getExtensionId()
	{
		$type = $this->type == 'modules' ? 'module' : 'plugin';

		$db = ES::db();
		$query = [
			'select `extension_id` from `#__extensions`',
			'where `type`=' . $db->Quote($type),
			'and `element`=' . $db->Quote($this->element)
		];

		$db->setQuery($query);
		$id = (int) $db->loadResult();

		return $id;
	}

	/**
	 * Installs a package
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function install()
	{
		$downloadedPath = $this->download();

		$app = JFactory::getApplication();

		// Load installer plugins for assistance if required:
		JPluginHelper::importPlugin('installer');

		$installType = $app->input->getWord('installtype');

		// Get an installer instance.
		$installer = JInstaller::getInstance();

		$state = $installer->install($downloadedPath);

		// Delete the extracted folder
		JFolder::delete($downloadedPath);

		return $state;
	}

	/**
	 * Uninstalls a package
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function uninstall()
	{
		$installer = JInstaller::getInstance();
		$row = JTable::getInstance('extension');

		$result = false;

		$extensionId = $this->getExtensionId();

		if ($extensionId) {
			$row->load($extensionId);

			if ($row->type) {
				$result = $installer->uninstall($row->type, $extensionId);

				// There was an error in uninstalling the package
				if ($result === false) {
					$this->setError(JText::sprintf('There was an error uninstalling the package %1$s', $this->title));
				}
			}

			ES::clearCache();
		}

		return $result;
	}
}
