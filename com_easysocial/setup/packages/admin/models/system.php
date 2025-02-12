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

jimport('joomla.application.component.model');

class EasySocialModelSystem extends EasySocialModel
{
	private $data = null;

	public function __construct($config = [])
	{
		parent::__construct('system', $config);
	}

	public function getMenus()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__menu_types');
		$sql->column('menutype');
		$sql->column('title');

		$db->setQuery($sql);

		$menus = $db->loadObjectList();

		return $menus;
	}

	/**
	 * Upgrades component to the latest version
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function update()
	{
		$config = ES::config();

		// Get the updater URL
		$uri = $this->getUpdateUrl();
		$key = $config->get('general.key');

		$domain = str_ireplace(array('http://', 'https://'), '', rtrim(JURI::root(), '/'));

		$uri->setVar('from', ES::getLocalVersion());
		$uri->setVar('key', $key);
		$uri->setVar('domain', $domain);
		$url = $uri->toString();

		// Download the package
		$file = JInstallerHelper::downloadPackage($url);

		// Error downloading the package
		if (!$file) {
			$this->setError('Error downloading zip file. Please try again. If the problem still persists, please get in touch with our support team.');
			return false;
		}

		$jConfig = ES::jconfig();
		$temporaryPath = $jConfig->getValue('tmp_path');

		// Ensure that the temporary path exists as some site owners
		// may migrate their site into a different environment
		if (!JFolder::exists($temporaryPath)) {
			$this->setError('Temporary folder set in Joomla does not exists. Please check the temporary folder path in your Joomla Global Configuration section.');
			return false;
		}

		// Unpack the downloaded zip into the temporary location
		$temporaryFile = $temporaryPath . '/' . $file;
		$package = JInstallerHelper::unpack($temporaryFile);

		if (!$package) {
			$contents = file_get_contents($temporaryFile);

			$result = json_decode($contents);

			if ($result && $result->code === 400) {
				$this->setError($result->error);
				return false;
			}
		}

		$installer = JInstaller::getInstance();
		$state = $installer->update($package['dir']);

		if (!$state) {
			$this->setError('Error updating component when using the API from Joomla. Please try again.');
			return false;
		}

		// Clean up the installer
		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return true;
	}

	/**
	 * Retrieves the latest installable version
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUpdateUrl()
	{
		$adapter = ES::connector(SOCIAL_SERVICE_JUPDATE);
		$result = $adapter
						->execute()
						->getResult();

		if (!$result) {
			throw new Exception('Unable to connect to remote service to obtain package. Please contact our support team');
		}

		$parser = ES::parser();
		$parser->load($result);

		$update = $parser->xpath('update/downloads/downloadurl');
		$url = (string) $update[0];

		$uri = new JURI($url);
		return $uri;
	}
}
