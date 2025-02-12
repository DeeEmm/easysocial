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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.archive');
jimport('joomla.database.driver');
jimport('joomla.installer.helper');

class EasySocialSetupController
{
	private $result = array();

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
	}

	/**
	 * Checks for development mode
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	protected function checkDevelopmentMode()
	{
		if ($this->isDevelopment()) {
			return $this->outputDevelopment();
		}
	}

	/**
	 * Allows cleanup of installation files
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	protected function cleanupZipFiles($path)
	{
		$zipFiles = JFolder::files($path, '.zip', false, true);

		if ($zipFiles) {
			foreach ($zipFiles as $file) {
				@JFile::delete($file);
			}
		}

		return true;
	}

	protected function data($key, $value)
	{
		$obj = new stdClass();
		$obj->$key = $value;

		$this->result[] = $obj;
	}

	/**
	 * Renders a response with proper headers
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function output($data = array())
	{
		header('Content-Type: application/json; UTF-8');

		if (empty($data)) {
			$data = $this->result;
		}

		echo json_encode($data);
		exit;
	}

	/**
	 * Outputs development notice
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function outputDevelopment()
	{
		return $this->output($this->getResultObj('Develpment mode, nothing happened.', true));
	}

	/**
	 * Generates a result object that can be json encoded
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getResultObj($message, $state, $stateMessage = '')
	{
		$obj = new stdClass();
		$obj->state = $state;
		$obj->stateMessage = $stateMessage;
		$obj->message = JText::_($message);

		return $obj;
	}

	/**
	 * Get's the version of this launcher so we know which to install
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getVersion()
	{
		static $version = null;

		// Get the version from the manifest file
		if (is_null($version)) {
			$contents 	= file_get_contents( JPATH_ROOT . '/administrator/components/com_easysocial/easysocial.xml' );
			$parser 	= simplexml_load_string( $contents );
			$version 	= $parser->xpath( 'version' );
			$version 	= (string) $version[ 0 ];
		}

		return $version;
	}

	/**
	 * Retrieve the Joomla Version
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getJoomlaVersion()
	{
		$jVerArr = explode('.', JVERSION);
		$jVersion = $jVerArr[0] . '.' . $jVerArr[1];

		return $jVersion;
	}

	/**
	 * Retrieves the current site's domain information
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getDomain()
	{
		static $domain = null;

		if (is_null($domain)) {
			$domain = JURI::root();
			$domain = str_ireplace(array('http://', 'https://'), '', $domain);
			$domain = rtrim($domain, '/');
		}

		return $domain;
	}

	/**
	 * Retrieves the information about the latest version
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getInfo()
	{
		// Get the md5 hash from the server.
		$resource = curl_init();

		$version = $this->getVersion();

		// We need to pass the api keys to the server
		curl_setopt($resource, CURLOPT_URL, SI_MANIFEST);
		curl_setopt($resource, CURLOPT_POST, true);
		curl_setopt($resource, CURLOPT_POSTFIELDS, 'apikey=' . SI_KEY . '&from=' . $version);
		curl_setopt($resource, CURLOPT_TIMEOUT, 120);
		curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($resource);

		curl_close($resource);

		if (!$result) {
			return false;
		}

		$obj = json_decode($result);

		return $obj;
	}

	/**
	 * Requires the EasySocial core library
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function engine()
	{
		$lib = JPATH_ROOT . '/administrator/components/com_easysocial/includes/easysocial.php';

		if (!JFile::exists($lib)) {
			return false;
		}

		require_once($lib);
	}

	/**
	 * Loads the previous version that was installed
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getInstalledVersion()
	{
		$this->engine();

		$contents = file_get_contents(SI_ADMIN_MANIFEST);

		$parser = simplexml_load_string($contents);

		$version = $parser->xpath('version');
		$version = (string) $version[0];

		return $version;
	}

	/**
	 * get a configuration item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPreviousVersion($versionType)
	{
		$this->engine();

		$config = ES::table('Config');
		$config->load(array('type' => $versionType));

		return $config->value;
	}

	/**
	 * Determines if we are in development mode
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function isDevelopment()
	{
		$session = JFactory::getSession();
		$developer = $session->get('easysocial.developer');

		return $developer;
	}

	/**
	 * Verifies the api key
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function verifyApiKey($key)
	{
		$post = array('apikey' => $key, 'product' => 'easysocial');
		$resource = curl_init();

		curl_setopt($resource, CURLOPT_URL, SI_VERIFIER);
		curl_setopt($resource, CURLOPT_POST , true);
		curl_setopt($resource, CURLOPT_TIMEOUT, 120);
		curl_setopt($resource, CURLOPT_POSTFIELDS, $post);
		curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($resource);
		curl_close($resource);

		if (!$result) {
			return false;
		}

		$result = json_decode($result);

		return $result;
	}

	/**
	 * Determine if database is set to mysql or not.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isMySQL()
	{
		$jConfig = JFactory::getConfig();
		$dbType = $jConfig->get('dbtype');

		return $dbType == 'mysql' || $dbType == 'mysqli' || $dbType == 'pdomysql';
	}

	/**
	 * Determine if mysql can support utf8mb4 or not.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function hasUTF8mb4Support()
	{
		static $_cache = null;

		if (is_null($_cache)) {

			$db = JFactory::getDBO();

			if (method_exists($db, 'hasUTF8mb4Support')) {
				$_cache = $db->hasUTF8mb4Support();
				return $_cache;
			}

			// we check the server version 1st
			$server_version = $db->getVersion();
			if (version_compare($server_version, '5.5.3', '<')) {
				 $_cache = false;
				 return $_cache;
			}

			$client_version = '5.0.0';

			if (function_exists('mysqli_get_client_info')) {
				$client_version = mysqli_get_client_info();
			} else if (function_exists('mysql_get_client_info')) {
				$client_version = mysql_get_client_info();
			}

			if (strpos($client_version, 'mysqlnd') !== false) {
				$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);
				$_cache = version_compare($client_version, '5.0.9', '>=');
			} else {
				$_cache = version_compare($client_version, '5.5.3', '>=');
			}

		}

		return $_cache;
	}

	public function isMySQL56()
	{
		static $_cache = null;

		if (is_null($_cache)) {
			$db = JFactory::getDBO();
			// we check the server version 1st
			$server_version = $db->getVersion();
			$_cache = version_compare($server_version, '5.6.0', '>=');
		}

		return $_cache;
	}

	/**
	 * Convert utf8mb4 to utf8
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function convertUtf8mb4QueryToUtf8($query)
	{
		if ($this->hasUTF8mb4Support())
		{
			return $query;
		}

		// If it's not an ALTER TABLE or CREATE TABLE command there's nothing to convert
		$beginningOfQuery = substr($query, 0, 12);
		$beginningOfQuery = strtoupper($beginningOfQuery);

		if (!in_array($beginningOfQuery, array('ALTER TABLE ', 'CREATE TABLE')))
		{
			return $query;
		}

		// Replace utf8mb4 with utf8
		return str_replace('utf8mb4', 'utf8', $query);
	}

	/**
	 * convert CURRENT_TIMESTAMP to 0000-00-00 00:00:00
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function convertDefaultDateValue($query)
	{
		return str_replace('DEFAULT CURRENT_TIMESTAMP', 'DEFAULT \'0000-00-00 00:00:00\'', $query);
	}


	/**
	 * Return system plugin lock file path
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getPluginLockPath()
	{
		$file = JPATH_ROOT . '/tmp/easysocial.sys-plugins.lock';
		return $file;
	}

	/**
	 * Return system plugins that may cuase the installer to break
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getBadPlugins()
	{
		$badPlugins = array('easysocialmobile', 'easysocialtablet', 'conversekit');
		return $badPlugins;
	}

	/**
	 * Disable system plugins that might cause installer to break
	 * if there are any
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function disableSystemPlugins()
	{
		$file = $this->getPluginLockPath();

		$badPlugins = array();

		if (JFile::exists($file)) {
			// read the content
			$contents = file_get_contents($file);
			if ($contents) {
				$badPlugins = explode(',', $contents);
			}
		}

		$db = JFactory::getDBO();

		$disallowed = $this->getBadPlugins();
		$tmp = '';
		foreach ($disallowed as $plg) {
			$tmp .= ($tmp) ? ',' . $db->Quote($plg) : $db->Quote($plg);
		}

		$query = "select `element` from `#__extensions`";
		$query .= " where `type` = 'plugin'";
		$query .= " and `folder` = 'system'";
		$query .= " and `enabled` = 1";
		$query .= " and `element` in (" . $tmp . ")";

		$db->setQuery($query);

		$results = $db->loadColumn();

		if ($results) {
			$badPlugins = array_merge($badPlugins, $results);
			$badPlugins = array_unique($badPlugins);
		}

		if ($badPlugins) {

			// delete previous cached file
			if (JFile::exists($file)) {
				JFile::delete($file);
			}

			$tmp = '';
			foreach ($badPlugins as $plg) {
				$tmp .= ($tmp) ? ',' . $db->Quote($plg) : $db->Quote($plg);
			}

			// disable these plugins 1st
			$query = "update `#__extensions` set `enabled` = 0";
			$query .= " where `type` = 'plugin'";
			$query .= " and `folder` = 'system'";
			$query .= " and `enabled` = 1";
			$query .= " and `element` in (" . $tmp . ")";

			$db->setQuery($query);
			$this->query($db);

			// we need to lock these bad plugins into a file.
			$contents = implode(',', $badPlugins);

			// write into tmp folder
			JFile::write($file, $contents);
		}

	}

	/**
	 * Re-enable system plugins that might cause installer to break
	 * if there are any
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function enableSystemPlugins()
	{
		$file = $this->getPluginLockPath();

		$badPlugins = array();

		if (JFile::exists($file)) {
			// read the content
			$contents = file_get_contents($file);
			if ($contents) {
				$badPlugins = explode(',', $contents);
			}

			if ($badPlugins) {
				// now we need to re-enable these previouly disabled plugins.
				$db = JFactory::getDBO();

				$tmp = '';
				foreach ($badPlugins as $plg) {
					$tmp .= ($tmp) ? ',' . $db->Quote($plg) : $db->Quote($plg);
				}

				// disable these plugins 1st
				$query = "update `#__extensions` set `enabled` = 1";
				$query .= " where `type` = 'plugin'";
				$query .= " and `folder` = 'system'";
				$query .= " and `enabled` = 0";
				$query .= " and `element` in (" . $tmp . ")";

				$db->setQuery($query);
				$this->query($db);
			}

			// okay done updating.
			// lets remove the lock file
			JFile::delete($file);
		}

		return true;
	}

	/**
	 * Saves a configuration item
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function updateConfig($key, $value)
	{
		$this->engine();

		$config = ES::config();
		$config->set($key, $value);

		$jsonString = $config->toString();

		$configTable = ES::table('Config');

		if (!$configTable->load('site')) {
			$configTable->type = 'site';
		}

		$configTable->set('value', $jsonString);
		return $configTable->store();
	}

	/**
	 * Method to execute query
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function query($db)
	{
		if (JVERSION < 4.0) {
			$db->query();
		} else {
			$db->execute();
		}
	}

	/**
	 * Method to extract zip file in installation part
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function extractArchive($destination, $extracted)
	{
		if (JVERSION < 4.0) {
			$state = JArchive::extract($destination, $extracted);

			return $state;
		}

		// Method of extracting archive in Joomla 4
		$archive = new Joomla\Archive\Archive();
		$state = $archive->extract($destination, $extracted);

		return $state;
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function splitSql($contents)
	{
		if (JVERSION < 4.0) {
			$queries = JInstallerHelper::splitSql($contents);
			return $queries;
		}

		// Method of splitting the sql strings in Joomla 4
		$queries = JDatabaseDriver::splitSql($contents);

		return $queries;
	}
}
