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

class SocialConfig
{
	private $configs = null;
	private $key = null;
	private $val = '';

	static $instances = array();

	public function __construct($key = 'site')
	{
		if (!isset($this->configs[$key])) {
			$this->load($key);
		}

		return $this->configs[$key];
	}

	/**
	 * Creates an instance of the config object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance($key = 'site')
	{
		if (!isset(self::$instances[$key])) {
			$config = new self($key);
			self::$instances[$key] = $config->toParam($key);
		}

		return self::$instances[$key];
	}

	/**
	 * Reload a set of configuration given the type
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reload($key = 'site')
	{
		$this->load($key);

		$config = new self($key);
		self::$instances[$key] = $config->toParam($key);
	}

	/**
	 * Loads a set of configuration given the type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($key)
	{
		// Specifically if the key is 'joomla' , we only want to use JConfig.
		if ($key == 'joomla') {
			$codeName = ES::version()->getCodeName();
			$helper = dirname(__FILE__) . '/helpers/' . $codeName . '.php';

			require_once($helper);

			$className = 'SocialConfig' . ucfirst($codeName);
			$config = new $className();

			$this->configs[$key] = $config;

			return $config;
		}

		// EasySocial configuration
		$default = SOCIAL_ADMIN . '/defaults/' . $key . '.json';
		$defaultData = '';

		// Read the default data.
		$defaultData = file_get_contents($default);

		// Load a new copy of Registry
		$config = json_decode($defaultData);

		if (is_null($config)) {
			throw new Exception('Invalid json syntax in site.json');
		}

		$config = ES::registry($config);

		// @task: Now we need to get the user defined configuration that is stored in the database.
		if (!defined('SOCIAL_COMPONENT_CLI')) {
			$model = ES::model('Config');
			$storedConfig = $model->getConfig($key);

			// Get stored config
			if ($storedConfig) {

				$storedConfig = ES::registry($storedConfig->value);

				// Merge configurations
				$config->mergeObjects($storedConfig->getRegistry());
			}
		}

		$this->configs[$key] = $config;
	}

	/**
	 * Returns a JRegistry object
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toParam($key)
	{
		return $this->configs[$key];
	}
}
