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

class plgInstallerEasySocial extends JPlugin
{
	/**
	 * Determines if EasySocial is installed
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function exists()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/easysocial.php';

		if (!JFile::exists($file) || !JComponentHelper::isInstalled('com_easysocial')) {
			return false;
		}

		require_once($file);
		return true;
	}

	/**
	 * Modifies update url
	 *
	 * @since	3.2.11
	 * @access	public
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$app = JFactory::getApplication();

		// If EasyBlog doesn't exist or it isn't enabled, there is no point updating it.
		if (!$this->exists() || stristr($url, 'https://services.stackideas.com/updater/easysocial') === false) {
			return true;
		}

		$config = ES::config();
		$key = $config->get('general.key');

		if (!$key) {
			$app->enqueueMessage('Your setup contains an invalid api key. EasySocial will not be updated now. If the problem still persists, please get in touch with the support team at https://stackideas.com/forums', 'error');

			return true;
		}

		$uri = new JURI($url);

		$domain = str_ireplace(array('http://', 'https://'), '', rtrim(JURI::root(), '/'));
		$localVersion = ES::getLocalVersion();
		$latestVersion = $uri->getVar('to');

		$uri->setVar('from', $localVersion);
		$uri->setVar('key', $key);
		$uri->setVar('domain', $domain);
		$url = $uri->toString();

		// Check to see if the single click updater can be used to update to this version
		$verifyUrl = 'https://services.stackideas.com/updater/easysocial?layout=upgradeable&from=' . $localVersion . '&to=' . $latestVersion . '&domain=' . $domain . '&key=' . $key;

		try {
			$response = JHttpFactory::getHttp()->get($verifyUrl);
		}  catch (\RuntimeException $exception) {

		}

		$result = json_decode($response->body);

		if ($result->code == 400) {
			$url = rtrim(JURI::root(), '/') . '/administrator/index.php?option=com_easysocial';

			ES::info()->set(null, $result->error, 'error');

			return $app->redirect($url);
		}



		return true;
	}
}
