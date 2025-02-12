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


require_once(__DIR__ . '/dependencies.php');
require_once(__DIR__ . '/compatibility.php');

class ES
{
	static private $models = [];
	static private $views = [];

	/**
	 * Include the autoload file
	 *
	 * @since	3.3.0
	 * @access	public
	 */

	public static function autoload()
	{
		require_once(__DIR__ . '/vendor/autoload.php');
	}

	/**
	 * Initialize the scripts and stylesheets on the site
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function initialize($location = 'site')
	{
		static $loaded = array();

		if (!isset($loaded[$location])) {
			$config = ES::config();

			$location = self::isFromAdmin() ? 'admin' : 'site';
			$theme = strtolower($config->get('theme.' . $location));

			// Attach the scripts
			$scripts = ES::scripts();
			$scripts->attach();

			// Attach css files
			$stylesheet = ES::stylesheet($location, $theme);
			$stylesheet->attach();

			$loaded[$location] = true;
		}

		return $loaded[$location];
	}

	/**
	 * Convert objects into an array for dropdown
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function convertToDropdownOptions($items, $valueKey, $textKey, $prepend = [])
	{
		$options = [];

		if ($prepend) {
			foreach ($prepend as $item) {
				$option = [
					'value' => $item['value'],
					'text' => JText::_($item['text'])
				];

				$options[] = $option;
			}
		}

		foreach ($items as $item) {
			$option = [
				'value' => $item->$valueKey,
				'text' => JText::_($item->$textKey)
			];

			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Allows caller to queue a message
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function setMessage($message, $type = 'info')
	{
		$session = JFactory::getSession();

		$msgObj = new stdClass();
		$msgObj->message = JText::_($message);
		$msgObj->type = strtolower($type);

		//save messsage into session
		$session->set('social.message.queue', $msgObj, 'SOCIAL.MESSAGE');
	}

	public static function getMessageQueue()
	{
		$session    = JFactory::getSession();
		$msgObj     = $session->get('social.message.queue', null, 'SOCIAL.MESSAGE');

		//clear messsage into session
		$session->set('social.message.queue', null, 'SOCIAL.MESSAGE');

		return $msgObj;
	}

	/**
	 * Alias to JText::_
	 *
	 * @since   1.3
	 * @access  public
	 */
	public static function _($string, $escape = false)
	{
		$string = JText::_($string);

		if ($escape) {
			$string = ES::string()->escape($string);
		}

		return $string;
	}

	/**
	 * Singleton for every other classes. It is responsible to return whatever necessary to perform a proper chaining
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getInstance($item = '')
	{
		static $objects = array();

		// We always want lowercased items.
		$item = strtolower($item);

		$path = SOCIAL_LIB . '/' . $item . '/' . $item . '.php';
		$objects[$item] = false;

		// We shouldn't add file checks here because it greatly slows down the script.
		// The caller should know what's it doing.
		include_once($path);
		$class = 'Social' . ucfirst($item);

		if (class_exists($class)) {
			$args = func_get_args();

			// We do array_shift instead of unset($args[0]) to prevent using array_values to reset the index of the array, and also to maintain the reference
			array_shift($args);

			if (method_exists($class, 'getInstance')) {
				$objects[$item] = call_user_func_array(array($class, 'getInstance'), $args);
			}
		}

		return $objects[$item];
	}

	/**
	 * Magic method to load static methods
	 *
	 * @since   1.4
	 * @access  public
	 */
	public static function __callStatic($name, $arguments)
	{
		// Load the library first
		ES::load($name);

		$className = 'Social' . ucfirst($name);

		if (method_exists($className, 'factory')) {
			$object = call_user_func_array(array($className, 'factory'), $arguments);

			return $object;
		}

		if (!class_exists($className)) {
			return false;
		}

		$object = new $className;

		return $object;
	}

	/**
	 * Loads a library
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function load($library)
	{
		static $loaded = array();

		if (!isset($loaded[$library])) {
			// We do not need to use ESJString here because files are not utf-8 anyway.
			$file = strtolower($library);

			// Normalize file name by removing any weird characters
			$file = str_ireplace(['"', '\'', ',', ':', '..', '/'], '', $file);

			$path = SOCIAL_LIB . '/' . $file . '/' . $file . '.php';

			if (!file_exists($path)) {
				$loaded[$library] = false;
				return;
			}

			include_once($path);

			$loaded[$library] = true;
		}

		return $loaded[$library];
	}

	/**
	 * Standard method to get limit
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function getLimit($settings = '', $default = 20)
	{
		$jConfig = ES::jConfig();
		$limit = (int) $jConfig->getValue('list_limit');

		// @TODO: Different sections might have different limit. We could apply this here in the future.

		return $limit;
	}

	/**
	 * Determines if the images has override
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function hasOverride($filename = 'email_logo')
	{
		$folder = explode('_', $filename);
		$foldersType = array('avatar', 'cover');

		if (in_array($folder[1], $foldersType)) {
			$override = JPATH_ROOT . '/images/easysocial_override/' . $folder[0] . '/' . $folder[1];

			jimport('joomla.filesystem.folder');
			$exists = JFolder::exists($override);
		} else {
			$override = JPATH_ROOT . '/images/easysocial_override/' . $filename . '.png';

			jimport('joomla.filesystem.file');
			$exists = JFile::exists($override);
		}

		if ($exists) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves mobile icon for 'Add to Homescreen' feature
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function getMobileIcon($defaultIcon = false)
	{
		static $icon = null;

		if (is_null($icon) || $defaultIcon) {
			$default = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/mobileicon.png';

			if ($defaultIcon) {
				return $default;
			}

			if (ES::hasOverride('mobile_icon')) {
				$icon = rtrim(JURI::root(), '/') . '/images/easysocial_override/mobile_icon.png';
				return $icon;
			}

			$icon = $default;
		}

		return $icon;
	}

	/**
	 * Get options for currency.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getCurrencyOptions()
	{
		static $options = null;

		if (is_null($options)) {
			$model = ES::model('Currencies');
			$options = $model->getCurrencyOptions();
		}

		return $options;
	}


	/**
	 * Get options for nearby radius for miles/km.
	 *
	 * @since	4.0.6
	 * @access	public
	 */
	public static function getNearbyRadiusOptions()
	{
		$config = self::config();
		$unit = $config->get('general.location.proximity.unit');

		// COM_ES_MILE_RADIUS
		// COM_ES_KM_RADIUS
		$string = 'COM_ES_' . strtoupper($unit) . '_RADIUS';

 		$options = [
			['value' => 1, 'text' => JText::sprintf($string, 1)],
			['value' => 5, 'text' => JText::sprintf($string, 5)],
			['value' => 10, 'text' => JText::sprintf($string, 10)],
			['value' => 25, 'text' => JText::sprintf($string, 25)],
			['value' => 50, 'text' => JText::sprintf($string, 50)],
			['value' => 100, 'text' => JText::sprintf($string, 100)],
			['value' => 200, 'text' => JText::sprintf($string, 200)],
			['value' => 300, 'text' => JText::sprintf($string, 300)],
			['value' => 400, 'text' => JText::sprintf($string, 400)],
			['value' => 500, 'text' => JText::sprintf($string, 500)]
		];

		return $options;
	}

	/**
	 * Retrieves the logo that should be used site wide
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function getLogo($defaultLogo = false, $preventCache = false)
	{
		static $logo = null;

		if (is_null($logo) || $defaultLogo) {
			$default = rtrim(JURI::root(), '/') . '/media/com_easysocial/images/logo.png';

			if ($defaultLogo) {
				return $default;
			}

			if (ES::hasOverride('email_logo')) {
				$logo = rtrim(JURI::root(), '/') . '/images/easysocial_override/email_logo.png';

				if ($preventCache) {
					$logo .= '?' . time();
				}

				return $logo;
			}

			$logo = $default;
		}

		return $logo;
	}

	/**
	 * This is a simple wrapper method to access a particular library in EasySocial. This method will always
	 * instantiate a new class based on the given class name.
	 *
	 * @param   string  $item       Defines what item this method should load
	 **/
	public static function get($lib = '')
	{
		// Try to load up the library
		self::load($lib);

		$class = 'Social' . ucfirst($lib);

		$args = func_get_args();

		// Remove the first argument because we know the first argument is always the library.
		if (isset($args[0])) {
			unset($args[0]);
		}

		return ES::factory($class, $args);
	}

	/**
	 * Creates a new object given the class.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function factory( $class , $args = array() )
	{
		// Reset the indexes
		$args       = array_values( $args );
		$numArgs    = count($args);

		// It's too bad that we have to write these cods but it's much faster compared to call_user_func_array
		if($numArgs < 1)
		{
			return new $class();
		}

		if($numArgs === 1)
		{
			return new $class($args[0]);
		}

		if($numArgs === 2)
		{
			return new $class($args[0], $args[1]);
		}

		if($numArgs === 3 )
		{
			return new $class($args[0], $args[1] , $args[ 2 ] );
		}

		if($numArgs === 4 )
		{
			return new $class($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] );
		}

		if($numArgs === 5 )
		{
			return new $class($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] , $args[ 4 ] );
		}

		if($numArgs === 6 )
		{
			return new $class($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] , $args[ 4 ] , $args[ 5 ] );
		}

		if($numArgs === 7 )
		{
			return new $class($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] , $args[ 4 ] , $args[ 5 ] , $args[ 6 ] );
		}

		if($numArgs === 8 )
		{
			return new $class($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] , $args[ 4 ] , $args[ 5 ] , $args[ 6 ] , $args[ 7 ]);
		}

		return call_user_func_array($fn, $args);
	}

	/**
	 * Single point of entry for static calls.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function call($library, $method, $args = [])
	{
		ES::load($library);

		$className = 'Social' . ucfirst($library);

		// Ensure that $args is an array.
		$args = ES::makeArray($args);

		return call_user_func_array([$className, $method], $args);
	}

	/**
	 * Retrieves EasySocial's configuration
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function config($key = 'site')
	{
		ES::load('Config');
		$config = SocialConfig::getInstance($key);

		return $config;
	}

	/**
	 * Retrieves EasySocial's configuration
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function colors($hex)
	{
		static $data = array();

		if (!isset($data[$hex])) {
			ES::load('Colors');

			$data[$hex] = new SocialColors($hex);
		}

		return $data[$hex];
	}

	/**
	 * An alias to ES::getInstance( 'Config' , 'joomla' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function jconfig()
	{
		// Load config library
		ES::load('config');

		$config = SocialConfig::getInstance('joomla');

		return $config;
	}

	/**
	 * An alias to ES::getInstance( 'Config' , 'joomla' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function storage($type = 'joomla')
	{
		return ES::get('Storage', $type);
	}

	/**
	 * An alias to ES::getInstance( 'Config' , 'joomla' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function streamFilter($type = SOCIAL_TYPE_USER, $canCreateFilter = false)
	{
		ES::load('streamFilter');

		$streamFilter = new SocialStreamFilter($type, $canCreateFilter);

		return $streamFilter;
	}

	/**
	 * An alias to ES::getInstance( 'Config' , 'joomla' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function tag($uid = null, $type = null)
	{
		$tag = ES::get('Tag', $uid, $type);

		return $tag;
	}

	/**
	 * An alias to ES::getInstance( 'Config' , 'joomla' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function fields($params = array())
	{
		ES::load('Fields');

		$fields = SocialFields::getInstance($params);

		return $fields;
	}

	/**
	 * An alias to ES::getInstance( 'Router' , 'profile' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function router($view)
	{
		ES::load('Router');

		$router = SocialRouter::getInstance($view);

		return $router;
	}


	/**
	 * An alias to ES::get( 'Migrators' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function migrators($extension)
	{
		return ES::get('Migrators', $extension);
	}

	/**
	 * Helper for checking valid tokens
	 *
	 * @since   3.3
	 * @access  public
	 */
	public static function checkToken()
	{
		if (!JSession::checkToken('request')) {
			$doc = JFactory::getDocument();
			$docType = $doc->getType();

			if ($docType === 'ajax') {
				$ajax = ES::ajax();

				ES::info()->set(JText::_('COM_ES_INVALID_TOKEN_REFRESH'));
				$ajax->script('window.location.reload();');

				return $ajax->send();
			}

			self::redirect(JURI::current(), 'COM_ES_INVALID_TOKEN');
		}
	}

	/**
	 * Includes a file given a particular namespace in POSIX format.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function import($namespace)
	{
		static $locations = [];

		if (!isset($locations[$namespace])) {
			// Explode the parts to know exactly what to lookup for
			$parts = explode(':', $namespace);

			// Non POSIX standard.
			if (count($parts) <= 1) {
				return false;
			}

			$base = $parts[0];
			$basePath = SOCIAL_SITE;

			if ($base == 'admin') {
				$basePath   = SOCIAL_ADMIN;
			}

			if ($base == 'themes') {
				$basePath   = SOCIAL_THEMES;
			}

			if ($base == 'apps') {
				$basePath = SOCIAL_APPS;
			}

			if ($base == 'fields') {
				$basePath = SOCIAL_FIELDS;
			}

			// Replace / with proper directory structure.
			$path = str_ireplace( '/', DIRECTORY_SEPARATOR, $parts[1]);

			// Get the absolute path now.
			$path = $basePath . $path . '.php';

			$locations[$namespace] = true;

			if (!file_exists($path)) {
				return false;
			}

			// Include the file now.
			include_once($path);
		}

		return true;
	}

	/**
	 * Alias for ES::getInstance( 'Apps' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function apps()
	{
		return ES::getInstance( 'apps' );
	}

	/**
	 * Retrieves the CDN URL
	 *
	 * @since   1.2
	 * @access  public
	 */
	public static function getCdnUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$config = ES::config();
			$url = trim($config->get('general.cdn.url'));

			if (!defined('SOCIAL_COMPONENT_CLI')) {
				// We do not want to render cdn urls for admins
				if (self::isFromAdmin()) {
					$url = false;
					return $url;
				}
			}

			if (!$url) {
				$url = false;

				return $url;
			}

			// If there are no url protocols set for the cdn, we should always prepend //
			if (stristr($url, 'http://') === false && stristr($url, 'https://') === false) {
				$url = '//' . $url;
			}
		}

		return $url;
	}

	public static function stylesheet($location, $name=null, $useOverride=false)
	{
		return ES::get('Stylesheet', $location, $name, $useOverride);
	}

	/**
	 * Alias for ES::getInstance( 'Dispatcher' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function dispatcher()
	{
		return ES::getInstance( 'Dispatcher' );
	}

	/**
	 * Alias for ES::uploader
	 *
	 * @since   3.2.8
	 * @access  public
	 */
	public static function uploader($options = array(), $type = null)
	{
		return ES::get('Uploader', $options, $type);
	}

	/**
	 * Alias for ES::getInstance( 'Ajax' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function ajax()
	{
		return ES::getInstance('Ajax');
	}

	/**
	 * Intelligent method to determine if the string uses plural or singular.
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function text($string, $count, $useCount = true)
	{
		$count = (int) $count;

		// @TODO: Make singular and plural configurable.
		if ($count <= 1) {
			$string .= '_SINGULAR';
		}

		if ($count > 1) {
			$string .= '_PLURAL';
		}

		if ($useCount) {
			return JText::sprintf($string, $count);
		}

		return JText::_($string);
	}

	/**
	 * Retrieves a JTable object. This simplifies the caller from manually adding include path all the time.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function table($name, $prefix = 'SocialTable')
	{
		ES::import('admin:/tables/table');

		$table = SocialTable::getInstance($name, $prefix);

		return $table;
	}

	/**
	 * Retrieves the view object.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function view($name, $backend = true)
	{
		$className = 'EasySocialView' . ucfirst($name);

		if (!isset(self::$views[$className]) || (!self::$views[$className] instanceof EasySocialView)) {

			if (!class_exists($className)) {
				$doc = JFactory::getDocument();

				$path = $backend ? SOCIAL_ADMIN : SOCIAL_SITE;
				$path .= '/views/' . strtolower($name) . '/view.' . $doc->getType() . '.php';

				if (!JFile::exists($path)) {
					return false;
				}

				require_once($path);
			}

			if (!class_exists($className)) {
				throw self::exception(JText::sprintf('View class not found: %1s', $className), 500);
			}

			self::$views[$className] = new $className([]);
		}

		return self::$views[$className];
	}

	/**
	 * Retrieves the view helper
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function viewHelper($viewName, $helperName)
	{
		static $helpers = array();

		$helperName = strtolower($helperName);
		$viewName = strtolower($viewName);

		$key = $viewName . $helperName;

		if (!isset($helpers[$key])) {
			$path = JPATH_ROOT . '/components/com_easysocial/views/' . $viewName . '/helpers/' . $helperName . '.php';

			require_once($path);

			$className = 'EasySocialView' . $viewName . $helperName . 'Helper';

			$helpers[$key] = new $className();
		}

		return $helpers[$key];
	}

	/**
	 * Retrieves a model from the models folder
	 *
	 * @since   2.0
	 * @access  public
	 **/
	public static function model($name, $config = array())
	{
		// Construct the cache id
		$cacheId = strtolower($name);
		$keys = array_keys($config);
		$values = array_values($config);

		$cacheId .= implode('.', $keys) . implode('.', $values);

		if (!isset(self::$models[$cacheId])) {

			ES::import('admin:/includes/model');

			$className = 'EasySocialModel' . ucfirst($name);

			// Include the model file. This is much quicker than doing JLoader::import
			if (!class_exists($className)) {
				$path = SOCIAL_MODELS . '/' . strtolower($name) . '.php';
				require_once($path);
			}

			// If the class still doesn't exist, let's just throw an error here.
			if (!class_exists($className)) {
				throw ES::exception(JText::sprintf('COM_EASYSOCIAL_MODEL_NOT_FOUND', $className), 500);
			}

			$model = new $className($config);

			self::$models[$cacheId] = $model;
		}

		// Forcefully run initState here instead of construct in the model because the same model might be used more than once in different states
		if (!empty($config['initState'])) {
			self::$models[$cacheId]->initStates();
		}

		return self::$models[$cacheId];
	}

	/**
	 * This should be triggered when certain pages are not found in the system.
	 * Particularly when certain id does not exist on the system.
	 *
	 */
	public static function show404()
	{
		// @TODO: Log some errors here.
		echo 'some errors here';
	}

	/**
	 * Shows a layout that the user has no access to the particular item.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function showNoAccess($message)
	{
		echo $message;
	}

	/**
	 * Sets some callback data into the current session
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function setCallback( $data )
	{
		$session        = JFactory::getSession();

		// Serialize the callback data.
		$data           = serialize( $data );

		// Store the profile type id into the session.
		$session->set( 'easysocial.callback' , $data , SOCIAL_SESSION_NAMESPACE );
	}

	/**
	 * Formats the callback url
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function formatCallback($url)
	{
		$url = str_ireplace('&amp;', '&', $url);

		return $url;
	}

	/**
	 * Shorten a given number into its format accordingly
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public static function formatNumbers($n, $precision = 1)
	{
		if ($n < 900) {
			// 0 - 900
			$n_format = number_format($n, $precision);
			$suffix = '';
		} else if ($n < 900000) {
			// 0.9k-850k
			$n_format = number_format($n / 1000, $precision);
			$suffix = 'K';
		} else if ($n < 900000000) {
			// 0.9m-850m
			$n_format = number_format($n / 1000000, $precision);
			$suffix = 'M';
		} else if ($n < 900000000000) {
			// 0.9b-850b
			$n_format = number_format($n / 1000000000, $precision);
			$suffix = 'B';
		} else {
			// 0.9t+
			$n_format = number_format($n / 1000000000000, $precision);
			$suffix = 'T';
		}

		// Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
		// Intentionally does not affect partials, eg "1.50" -> "1.50"
		if ($precision > 0) {
			$dotzero = '.' . str_repeat( '0', $precision );
			$n_format = str_replace( $dotzero, '', $n_format );
		}

		return $n_format . $suffix;
	}

	/**
	 * Retrieves stored callback data.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getCallback($default = '')
	{
		$session = JFactory::getSession();
		$data = $session->get('easysocial.callback', '', SOCIAL_SESSION_NAMESPACE);

		$data = unserialize($data);

		// Clear off the session once it's been picked up.
		$session->clear('easysocial.callback', SOCIAL_SESSION_NAMESPACE);

		if (!$data && $default) {
			return $default;
		}

		return $data;
	}

	/**
	 * Renders a login page if necessary. If this is called via an ajax method, it will trigger a dialog instead.
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function requireLogin()
	{
		$doc = JFactory::getDocument();
		$my = ES::user();

		// User is logged in, allow them to proceed
		if (!$my->guest) {
			return true;
		}

		$docType = $doc->getType();

		if ($docType == 'html') {

			$message = new stdClass();
			$message->message = JText::_('COM_EASYSOCIAL_PLEASE_LOGIN_FIRST');
			$message->type = SOCIAL_MSG_INFO;

			$info = ES::info();
			$info->set($message);

			// Set the current url as the callback
			$callback = FRoute::current();
			ES::setCallback($callback);

			// Create the login url
			$url = FRoute::login(array(), false);

			return self::redirect($url);
		}

		if ($docType == 'ajax') {
			$ajax = ES::ajax();

			// Get any referrer
			$callback = ESR::referer();

			if ($callback) {
				ES::setCallback($callback);
			}

			$ajax->script('EasySocial.login();');

			return $ajax->send();
		}
	}

	/**
	 * Converts an argument into an array.
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function makeArray($item, $delimeter = null)
	{
		// If this is already an array, we don't need to do anything here.
		if (is_array($item)) {
			return $item;
		}

		// Test if source is a SocialRegistry/JRegistry object
		if ($item instanceof ES\Registry || $item instanceof SocialRegistry || $item instanceof JRegistry) {
			return $item->toArray();
		}

		// Test if source is an object.
		if (is_object($item)) {
			return ESArrayHelper::fromObject($item);
		}

		if (is_integer($item)) {
			return [$item];
		}

		// Test if source is a string.
		if (is_string($item)) {
			if ($item == '') {
				return [];
			}

			// Test for comma separated values.
			if (!is_null($delimeter ) && stristr($item, $delimeter) !== false) {
				$data = explode($delimeter, $item);

				return $data;
			}

			// Test for JSON array string
			$pattern = '#^\s*//.+$#m';
			$item = trim(preg_replace($pattern, '', $item));
			if ((substr($item, 0, 1) === '[' && substr($item, -1, 1) === ']')) {
				return ES::json()->decode($item);
			}

			// Test for JSON object string, but convert it into array
			if ((substr($item, 0, 1) === '{' && substr($item, -1, 1) === '}')) {
				$result = ES::json()->decode($item);

				return ESArrayHelper::fromObject($result);
			}

			return [$item];
		}

		return false;
	}

	/**
	 * Converts an argument into an object
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function makeObject( $item, $debug = false )
	{
		// If this is already an object, skip this
		if( is_object( $item ) )
		{
			return $item;
		}

		if( is_array( $item ) )
		{
			return (object) $item;
		}

		if( strlen( $item ) < 1024 && is_file( $item ) )
		{
			jimport( 'joomla.filesystem.file' );

			$item   = file_get_contents( $item );
		}

		$json   = ES::json();

		// Test if source is a string.
		if( $json->isJsonString( $item ) )
		{

			if ($debug) {
				$obj    = $json->decode( $item );
				var_dump($item, $obj);
				exit;
			}

			// Trim the string first
			$item = trim( $item );

			$obj    = $json->decode( $item );

			if( !is_null( $obj ) )
			{
				return $obj;
			}

			$obj    = new stdClass();

			return $obj;
		}

		return false;
	}

	/**
	 * Converts an array to string
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function makeString( $val , $join = '' )
	{
		if( is_string( $val ) )
		{
			return $val;
		}

		return implode( $join , $val );
	}

	/**
	 * Converts an argument into a json string. If argument is a string, it wouldn't be processed.
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function makeJSON($item)
	{
		if (is_string($item)) {
			return $item;
		}

		return json_encode($item);
	}

	/**
	 * Retrieve homepage SEF URL
	 *
	 * @since   4.0.13
	 * @access  public
	 */
	public static function getUserDashboardURL()
	{
		$url = ESR::dashboard();

		// Just in case if the SEF URL contains a itemId without the menu item id
		// which mean the site already disabled the Easysocial dashboard menu item
		// so we need to get rid of the itemId query string to prevent 404 error in Joomla 4
		if (strpos($url, 'component/easysocial/?Itemid=') !== false) {
			$url = str_ireplace('/?Itemid=', '', $url);
		}

		$subfolder = JURI::root(true);
		$base = str_ireplace($subfolder, '', rtrim(JURI::root(), '/'));

		if (!empty($subfolder)) {
			$subfolder = $subfolder . '/';
			$base = str_ireplace($subfolder, '', rtrim(JURI::root()));
		}

		$url = $base . $url;

		return $url;
	}

	/**
	 * Retrieve shortcut manifect SEF URL
	 *
	 * @since   4.0.13
	 * @access  public
	 */
	public static function getShortcutManifestURL()
	{
		$join = ESRouter::getMode() == SOCIAL_ROUTER_MODE_SEF ? '?' : '&';
		$userDashboardURL = ES::getUserDashboardURL();
		$shortcutManifestURL = $userDashboardURL . $join . 'shortcutmanifest=true';

		return $shortcutManifestURL;
	}

	/**
	 * Retrieve manifest required for shortcut in android device
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function getShortcutManifest()
	{
		$config = ES::config();

		$manifest = new stdClass;
		$manifest->short_name = $config->get('mobileshortcut.shortname');
		$manifest->name = $config->get('mobileshortcut.name');
		$manifest->display = 'standalone';
		$manifest->theme_color = $config->get('mobileshortcut.theme');

		$userDashboardURL = self::getUserDashboardURL();
		$manifest->start_url = $userDashboardURL;

		$icon = new stdClass;
		$icon->src = ES::getMobileIcon();
		$icon->type = 'image/png';
		$icon->sizes = '192x192';

		$manifest->icons = array($icon);

		header('Content-type: application/json; UTF-8');
		echo json_encode($manifest);
		exit;

	}

	/**
	 * Parses a csv file to array of data
	 *
	 * @since   1.0.1
	 */
	public static function parseCSV( $file, $firstRowName = true, $firstColumnKey = true )
	{
		if( !JFile::exists( $file ) )
		{
			return array();
		}

		$handle = fopen( $file, 'r' );

		$line = 0;

		$columns = array();

		$data = array();

		while( ( $row = fgetcsv( $handle ) ) !== false )
		{
			if( $firstRowName && $line === 0 )
			{
				$columns = $row;
			}
			else
			{
				$tmp = array();

				if( $firstRowName )
				{
					foreach( $row as $i => $v )
					{
						$tmp[$columns[$i]] = $v;
					}
				}
				else
				{
					$tmp = $row;
				}

				if( $firstColumnKey )
				{
					if( $firstRowName )
					{
						$data[$tmp[$columns[0]]] = $tmp;
					}
					else
					{
						$data[$tmp[0]] = $tmp;
					}
				}
				else
				{
					$data[] = $tmp;
				}
			}

			$line++;
		}

		fclose( $handle );

		return $data;
	}

	/**
	 * Resolve a given POSIX path.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function resolve($path)
	{
		if (strpos($path, ':/') === false) {
			return false;
		}

		$parts = explode(':/', $path);

		// Get the protocol.
		$protocol = $parts[0];

		// Get the real path.
		$path = $parts[1];

		// Known core resolvers
		$core = ['fields', 'admin', 'apps', 'site'];

		if (in_array($protocol, $core)) {
			$key = 'SOCIAL_' . strtoupper($protocol);
			$basePath = constant($key);

			return $basePath . '/' . $path;
		}

		if ($protocol === 'emails') {
			return ES::call('Mailer', 'resolve', $path);
		}

		if ($protocol === 'ajax') {
			return ES::call('Ajax', 'resolveNamespace', $path);
		}

		if ($protocol === 'modules') {
			return ES::call('Modules', 'resolve', $path);
		}

		if ($protocol === 'themes') {
			return ES::call('Themes', 'resolve', $path);
		}

		return false;
	}

	/**
	 * This method has been deprecated in 2.1 and will be removed completely from 2.2
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function exists()
	{
		return true;
	}

	/**
	 * Simple implementation to extract keywords from a string
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public static function extractKeyWords($string)
	{
		mb_internal_encoding('UTF-8');

		$stopwords = array();
		$string = preg_replace('/[\pP]/u', '', trim(preg_replace('/\s\s+/iu', '', mb_strtolower($string))));
		$matchWords = array_filter(explode(' ',$string), function ($item) use ($stopwords) { return !($item == '' || in_array($item, $stopwords) || mb_strlen($item) <= 2 || is_numeric($item));});
		$wordCountArr = array_count_values($matchWords);

		arsort($wordCountArr);
		return array_keys(array_slice($wordCountArr, 0, 10));
	}

	/**
	 * Alias for ES::getInstance( 'Explorer' )
	 *
	 * @since   1.2
	 * @access  public
	 */
	public static function explorer( $uid , $type )
	{
		return ES::getInstance( 'Explorer' , $uid , $type );
	}

	/**
	 * Alias for ES::getInstance( 'Document' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function document()
	{
		return ES::getInstance( 'Document' );
	}

	/**
	 * Alias for ES::getInstance( 'Profiler' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function profiler()
	{
		return ES::getInstance( 'Profiler' );
	}

	/**
	 * Alias for ES::get( 'DB' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function privacy($target = '', $type = SOCIAL_TYPE_USER)
	{
		return ES::get('Privacy', $target, $type);
	}


	/**
	 * Retrieves a token generated by the platform.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function token()
	{
		return JFactory::getSession()->getFormToken();
	}

	/**
	 * Detects if the folder exist based on the path given. If it doesn't exist, create it.
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function makeFolder($path, $createIndex = true)
	{
		jimport('joomla.filesystem.folder');

		// If folder exists, we don't need to do anything
		if (JFolder::exists($path)) {
			return true;
		}

		// Folder doesn't exist, let's try to create it.
		$state = JFolder::create($path);

		if ($state && $createIndex) {
			ES::createIndex($path);
			return true;
		}

		return false;
	}

	/**
	 * Cleans a given string and replaces all /\ with proper directory structure DIRECTORY_SEPARATOR and removes any trailing or leading /
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function language()
	{
		static $language    = null;

		if (is_null($language)) {
			// Try to load up the library
			ES::load('Language');


			$language = new SocialLanguage();
		}

		return $language;
	}

	/**
	 * Clears the cache in the CMS
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function clearCache()
	{
		$arguments = func_get_args();

		$cache = JFactory::getCache();

		foreach ($arguments as $argument) {
			$cache->clean($argument);
		}

		return true;
	}

	/**
	 * Allows caller to pass in an array of data to normalize the data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public static function normalize($data, $key, $default = null)
	{
		if (!$data) {
			return $default;
		}

		// $key cannot be an array
		if (is_array($key)) {
			$key = $key[0];
		}

		// Object datatype
		if (is_object($data) && isset($data->$key)) {
			return $data->$key;
		}

		// Array datatype
		if (is_array($data) && isset($data[$key])) {
			return $data[$key];
		}

		return $default;
	}

	/**
	 * Ensures that the date string is a valid date string and doesn't contain any incorrect values
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public static function normalizeDateString($dateString)
	{
		static $emptyValue = '0000-00-00 00:00:00';

		if (!$dateString) {
			return $emptyValue;
		}

		return $dateString;
	}

	/**
	 * Return the last date of the current month
	 *
	 * @since	4.0.11
	 * @access	public
	 */
	public static function getLastDayOfCurrentMonth($dateString)
	{
		// Converting string to Unix timestamp
		$unixTimestamp = strtotime($dateString);

		// Last date of current month
		$lastDayOfCurrentMonth = date("Y-m-t", $unixTimestamp);

		return $lastDayOfCurrentMonth;
	}

	/**
	 * Normalize directory separator
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function normalizeSeparator($path)
	{
		$path = str_ireplace(array( '\\' ,'/' ) , '/' , $path);

		return $path;
	}

	/**
	 * Cleans a given string and replaces all /\ with proper directory structure DIRECTORY_SEPARATOR and removes any trailing or leading /
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function cleanPath($path)
	{
		$path = ltrim($path, '\/');
		$path = rtrim($path, '\/');
		$path = ES::normalizeSeparator($path);

		return $path;
	}

	/**
	 * Cleans up xml contents to ensure that it truly contains xml markups
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public static function cleanupXmlContents($contents)
	{
		// Ensure that there are no leading text before the <?xml> tag.
		$pattern = '/(.*?)<\?xml version/is';
		$replacement = '<?xml version';
		$contents = preg_replace($pattern, $replacement, $contents, 1);

		// If there's no xml text in the contents, we need to add them
		if (strpos($contents, '<?xml version' ) === false) {
			$contents = '<?xml version="1.0" encoding="utf-8"?>' . $contents;
		}

		return $contents;
	}

	/**
	 * Alias for ES::get( 'DB' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function db()
	{
		ES::load('DB');

		$db = SocialDB::getInstance();

		return $db;
	}

	/**
	 * Alias for ES::get( 'Date' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function date($current = 'now' , $withoffset = true)
	{
		if( is_object( $current ) && get_class( $current ) == 'SocialDate' )
		{
			return $current;
		}

		ES::load('Date');

		$date   = new SocialDate($current, $withoffset);

		return $date;
	}

	/**
	 * Alias for ES::get( 'User' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function user($ids = '', $debug = false, $cacheUser = null)
	{
		// Load the user library
		self::load('User');

		return SocialUser::factory($ids, $debug, $cacheUser);
	}

	/**
	 * Alias for ES::workflows()
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function workflows($id = null, $type = SOCIAL_TYPE_USER)
	{
		$lib = ES::get('Workflows', $id, $type);

		return $lib;
	}

	/**
	 * Alias for ES::get( 'Mailchimp' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function mailchimp( $apikey )
	{
		$lib    = ES::get( 'Mailchimp' , $apikey );

		return $lib;
	}

	/**
	 * Alias for ES::get( 'Group' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function group($ids = null, $reload = null, $debug = false)
	{
		// Load the group library
		ES::load('group');

		if (is_null($ids)) {
			return new SocialGroup();
		}

		$state = SocialGroup::factory($ids, $reload, $debug);

		if( $state === false )
		{
			return new SocialGroup();
		}

		return $state;
	}

	/**
	 * Alias for ES::get( 'Page' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function page($ids = null, $reload = null, $debug = false)
	{
		// Load the page library
		ES::load('page');

		if (is_null($ids)) {
			return new SocialPage();
		}

		$state = SocialPage::factory($ids, $reload, $debug);

		if( $state === false )
		{
			return new SocialPage();
		}

		return $state;
	}

	/**
	 * Alias for ES::get('Event')
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function event($ids = null, $reload = null, $debug = false)
	{
		// Load the group library
		ES::load('event');

		if (is_null($ids)) {
			return new SocialEvent();
		}

		$state = SocialEvent::factory($ids, $reload, $debug);

		if( $state === false )
		{
			return new SocialEvent();
		}

		return $state;
	}

	/**
	 * Alias for ES::get( 'User' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function version()
	{
		$version = ES::getInstance('Version');

		return $version;
	}

	/**
	 * Generates a blank index.html file into a specific target location.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function createIndex($targetLocation)
	{
		$targetLocation = $targetLocation . '/index.html';

		jimport('joomla.filesystem.file');

		$contents = "<html></html>";

		return JFile::write($targetLocation, $contents);
	}

	/**
	 * Alias to ES::getInstance( 'Notification' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function notification()
	{
		return ES::getInstance( 'Notification' );
	}

	/**
	 * Alias to ES::getInstance( 'Badges' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function badges()
	{
		return ES::getInstance( 'Badges' );
	}

	/**
	 * Alias to ES::getInstance( 'Points' );
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function points()
	{
		return ES::getInstance( 'Points' );
	}

	/**
	 * Alias method to load JSON library
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function json()
	{
		ES::load('JSON');

		$lib = SocialJSON::getInstance();

		return $lib;
	}

	/**
	 * Alias method to load info library
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function info()
	{
		$info = ES::getInstance('Info');

		return $info;
	}

	/**
	 * Determines if the site is on Joomla 3
	 *
	 * @since	3.3
	 * @access	public
	 */
	public static function isJoomla3()
	{
		static $isJoomla3 = null;

		if (is_null($isJoomla3)) {

			if (ESUtility::getJoomlaVersion() >= '3.0' && !ESUtility::isJoomla4()) {
				$isJoomla3 = true;
			}
		}

		return $isJoomla3;
	}

	/**
	 * Determines if the site is on Joomla 4
	 *
	 * @since	3.3
	 * @access	public
	 */
	public static function isJoomla4()
	{
		static $isJoomla4 = null;

		if (is_null($isJoomla4)) {
			$isJoomla4 =  ESUtility::isJoomla4();
		}

		return $isJoomla4;
	}

	/**
	 * Determines if the site is on Joomla 4
	 *
	 * @since	3.3
	 * @access	public
	 */
	public static function isJoomla42()
	{
		static $isJoomla42 = null;

		if (is_null($isJoomla42)) {
			$isJoomla42 = ESUtility::isJoomla42();
		}

		return $isJoomla42;
	}

	/**
	 * Shorthand method to check version
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function isJoomla31()
	{
		$version = ES::getInstance('version');
		return $version->getVersion() >= '3.1';
	}

	public static function isJoomla30()
	{
		$version = ES::getInstance('version');
		return $version->getVersion() >= '3.0';
	}

	public static function isJoomla25()
	{
		$version = ES::getInstance('version');
		return $version->getVersion() >= '1.6' && $version->getVersion() <= '2.5';
	}

	public static function isJoomla15()
	{
		$version = ES::getInstance('version');
		return $version->getVersion() <= '1.5';
	}

	/**
	 * Generates a hash on a string.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getHash($str)
	{
		return ESApplicationHelper::getHash($str);
	}

	public static function filelog()
	{
		$args = func_get_args();

		$now = ES::date()->toSql();

		$contents = '<h2>' . $now . '</h2><pre>';

		foreach ($args as $arg) {
			ob_start();
			var_export($arg);
			$contents .= ob_get_contents();
			ob_end_clean();
		}

		$contents .= '</pre>';

		$path = SOCIAL_TMP . '/debuglog.html';

		jimport('joomla.filesystem.file');

		if (JFile::exists($path)) {
			$original = file_get_contents($path);

			$contents = $original . $contents;
		}

		JFile::write($path, $contents);
	}

	/**
	 * Alias for ES::get( 'Image' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function avatar( SocialImage $image , $id = null , $type = null )
	{
		return ES::get( 'Avatar' , $image , $id , $type );
	}

	/**
	 * Retrieves the ad library
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public static function ad($id = null)
	{
		$ad = ES::get('Ad', $id);

		return $ad;
	}

	/**
	 * Retrieves the advertiser library
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public static function advertiser($id = null)
	{
		$advertiser = ES::get('Advertiser', $id);

		return $advertiser;
	}

	/**
	 * Alias for ES::get( 'Albums' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function albums($uid = null, $type = null, $id = null)
	{
		return ES::get('Albums', $uid, $type, $id);
	}

	/**
	 * Alias for ES::get( 'Photo' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function photo($uid = null, $type = null, $id = null)
	{
		return ES::get('Photo', $uid, $type, $id);
	}

	/**
	 * Retrieves the video library
	 *
	 * @since   1.4
	 * @access  public
	 */
	public static function video($uid = null, $type = null, $key = null)
	{
		$video = ES::get('Video', $uid, $type, $key);

		return $video;
	}

	/**
	 * Retrieves the audio library
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function audio($uid = null, $type = null, $key = null)
	{
		$audio = ES::get('Audio', $uid, $type, $key);

		return $audio;
	}

	/**
	 * Retrieves the ffmpeg library
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function ffmpeg($type = SOCIAL_TYPE_VIDEO)
	{
		$ffmpeg = ES::get('Ffmpeg', $type);

		return $ffmpeg;
	}

	/**
	 * Retrieves the conversation library
	 *
	 * @since   1.4
	 * @access  public
	 */
	public static function conversation($uid = null, $type = null, $key = null)
	{
		$conversation = ES::get('Conversation', $uid, $type, $key);

		return $conversation;
	}

	/**
	 * Generates a new exception
	 *
	 * @since   1.4.7
	 * @access  public
	 */
	public static function exception($message='', $type = ES_ERROR)
	{
		return ES::get('Exception', $message, $type);
	}

	public static function math()
	{
		return ES::getInstance('Math');
	}

	/**
	 * Alias for ES::get( 'Access' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function access( $userId = null, $type = SOCIAL_TYPE_USER )
	{
		// Load access library
		ES::load('Access');

		$access     = new SocialAccess($userId, $type);

		return $access;
	}

	/**
	 * Alias for ES::getInstance('Meta');
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function meta()
	{
		return ES::getInstance('Meta');
	}

	/**
	 * Alias for ES::getInstance( 'Opengraph' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function opengraph()
	{
		return ES::getInstance( 'Opengraph' );
	}

	/**
	 * Alias for ES::getInstance( 'OAuth' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function oauth( $client = '' , $callback = '' )
	{
		return ES::getInstance( 'OAuth' , $client , $callback );
	}

	/**
	 * Alias for ES::get( 'bbcode' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function bbcode()
	{
		ES::load('BBCode');

		$bbcode = new SocialBBCode();

		return $bbcode;
	}

	public static function callFunc( $obj , $fn , array $args = array() )
	{
		$numArgs = count($args);

		if($numArgs < 1)
		{
			return $obj->$fn();
		}

		if($numArgs === 1)
		{
			return $obj->$fn($args[0]);
		}

		if($numArgs === 2)
		{
			return $obj->$fn($args[0], $args[1]);
		}

		if($numArgs === 3 )
		{
			return $obj->$fn($args[0], $args[1] , $args[ 2 ] );
		}

		if($numArgs === 4 )
		{
			return $obj->$fn($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] );
		}

		if($numArgs === 5 )
		{
			return $obj->$fn($args[0], $args[1] , $args[ 2 ] , $args[ 3 ] , $args[ 4 ] );
		}

		return call_user_func_array($fn, $args);
	}

	/**
	 * Alias for ES::get( 'Likes' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function likes($uid = null , $type = null, $verb = null, $group = SOCIAL_APPS_GROUP_USER, $streamId = null, $options = array())
	{
		return ES::get('Likes', $uid, $type, $verb, $group, $streamId, $options);
	}

	/**
	 * Alias for ES::get( 'Story' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function story( $type = '' )
	{
		return ES::get( 'Story' , $type );
	}

	/**
	 * Alias for ES::get( 'Registry' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function registry($raw = '')
	{
		return ES::get('Registry', $raw);
	}

	/**
	 * Alias for ES::getInstance( 'Modules' )
	 *
	 * @since   1.0
	 * @access  public
	 * @return  SocialComments
	 */
	public static function modules( $name )
	{
		$modules    = ES::get( 'Modules' , $name );

		return $modules;
	}

	/**
	 * Alias for ES::getInstance( 'Comments' )
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function comments( $uid = null, $element = null, $verb = 'null', $group = SOCIAL_APPS_GROUP_USER, $options = array(), $useStreamId = false )
	{
		$comments = ES::getInstance( 'Comments' );

		if( !is_null( $uid ) && !is_null( $element ) )
		{
			return $comments->load( $uid, $element, $verb, $group, $options, $useStreamId );
		}

		return $comments;
	}

	public static function alert($element = null, $rulename = null)
	{
		$alert = ES::getInstance('Alert');

		if (is_null($element)) {
			return $alert;
		}

		$registry = $alert->getRegistry($element);

		if (is_null($rulename)) {
			return $registry;
		}

		return $registry->getRule($rulename);
	}

	/**
	 * Shorthand to send out notification
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function notify($rule, $participants, $emailOptions = [], $systemOptions = [], $type = SOCIAL_NOTIFICATION_TYPE_BOTH)
	{
		// Prior to 2.0, we no longer send notification to likes involved.
		if ($rule == 'likes.involved') {
			return false;
		}

		$segments = explode('.', $rule);
		$element = array_shift($segments);
		$rulename = implode('.', $segments);
		$alert = ES::alert($element, $rulename);

		$arg = new stdClass();
		$arg->rule = $rule;
		$arg->participant = $participants;
		$arg->email_options = $emailOptions;
		$arg->sys_options = $systemOptions;

		$args = [&$arg];

		$dispatcher = ES::getInstance('Dispatcher');

		// @trigger onNotificationBeforeCreate from user apps
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onNotificationBeforeCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onNotificationBeforeCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onNotificationBeforeCreate', $args);

		if (!$alert) {
			return false;
		}

		// There are 4 types of notification: Both = 1, Email = 2, Internal = 3, None = 4
		// We should check this setting before sending the notification.

		// If the type is None, don't send any notification
		if ($type == SOCIAL_NOTIFICATION_TYPE_NONE) {
			return false;
		}

		// If the notification type is Internal only, skip the email type
		if ($type == SOCIAL_NOTIFICATION_TYPE_INTERNAL) {
			$emailOptions = false;
		}

		// If the notification type is Email only, skip the system type
		if ($type == SOCIAL_NOTIFICATION_TYPE_EMAIL) {
			$systemOptions = false;
		}

		// Do not process notifications if params is empty, #417.
		if (empty($emailOptions)) {
			$emailOptions = false;
		}

		if (empty($systemOptions)) {
			$systemOptions = false;
		}

		// When e-mail notification is switched off, do not send any e-mails
		$config = ES::config();

		if (!$config->get('notifications.email.enabled')) {
			$emailOptions = false;
		}

		$state = $alert->send($participants, $emailOptions, $systemOptions);

		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onNotificationAfterCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP, 'onNotificationAfterCreate', $args);
		$dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT, 'onNotificationAfterCreate', $args);

		return $state;
	}

	/**
	 * Simple method of notifying admins
	 *
	 * @since	4.0.2
	 * @access	public
	 */
	public static function notifyAdmins($subject, $mailTemplateNamespace, $params)
	{
		$subject = JText::_($subject);

		// Get a list of super admins on the site.
		$model = ES::model('Users');
		$admins = $model->getSystemEmailReceiver();

		foreach ($admins as $admin) {
			$mailer = ES::mailer();
			$mailTemplate = $mailer->getTemplate();
			$mailTemplate->setRecipient($admin->name, $admin->email);
			$mailTemplate->setTitle($subject);
			$mailTemplate->setTemplate($mailTemplateNamespace, $params);
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			$state = $mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Used to send out notification to cluster's members in batch
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function notifyClusterMembers($rule, $clusterId, $emailOptions = array(), $systemOptions = array(), $exclude = array(), $type = SOCIAL_NOTIFICATION_TYPE_BOTH)
	{
		$segments = explode('.', $rule);
		$element = array_shift($segments);
		$rulename = implode('.', $segments);
		$alert = ES::alert($element, $rulename);

		if (!$alert) {
			return false;
		}

		// There are 4 types of notification: Both = 1, Email = 2, Internal = 3, None = 4
		// We should check this setting before sending the notification.

		// If the type is None, don't send any notification
		if ($type == SOCIAL_NOTIFICATION_TYPE_NONE) {
			return false;
		}

		// If the notification type is Internal only, skip the email type
		if ($type == SOCIAL_NOTIFICATION_TYPE_INTERNAL) {
			$emailOptions = false;
		}

		// If the notification type is Email only, skip the system type
		if ($type == SOCIAL_NOTIFICATION_TYPE_EMAIL) {
			$systemOptions = false;
		}

		$config = ES::config();
		if (!$config->get('notifications.email.enabled')) {
			$emailOptions = false;
		}

		$state = $alert->sendClusterMembers($clusterId, $emailOptions, $systemOptions, $exclude);
		return $state;
	}


	/**
	 * Used to send out notification to cluster's members in batch
	 *
	 * @since   2.1.8
	 * @access  public
	 */
	public static function notifyProfileMembers($rule, $profileIds, $emailOptions = array(), $systemOptions = array(), $exclude = array())
	{
		$segments = explode('.', $rule);
		$element = array_shift($segments);
		$rulename = implode('.', $segments);
		$alert = ES::alert($element, $rulename);

		if (!$alert) {
			return false;
		}

		$state = $alert->sendProfileMembers($profileIds, $emailOptions, $systemOptions, $exclude);
		return $state;
	}


	/**
	 * Retrieves the current version of EasySocial installed.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function getLocalVersion()
	{
		static $version = false;

		if ($version === false) {
			$file = SOCIAL_ADMIN . '/easysocial.xml';

			$contents = file_get_contents($file);
			$parser = simplexml_load_string($contents);

			$version = $parser->xpath('version');
			$version = (string) $version[0];
		}


		return $version;
	}

	/**
	 * Retrieves the latest version of EasySocial from the server
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getOnlineVersion()
	{
		$connector = ES::connector(SOCIAL_SERVICE_NEWS);
		$contents = $connector
						->execute()
						->getResult();

		$obj = ES::makeObject($contents);

		if (empty($obj->version)) {
			return '';
		}

		return $obj->version;
	}

	/**
	 * Generates a default cover link
	 *
	 * @since   1.4.8
	 * @access  public
	 */
	public static function getDefaultCover($group, $default = false)
	{
		static $covers = array();

		if (!isset($covers[$group]) || $default) {
			$config = ES::config();

			$covers[$group] = SOCIAL_JOOMLA_URI . $config->get('covers.default.' . $group . '.' . SOCIAL_COVER_DEFAULT);

			if ($default) {
				return $covers[$group];
			}

			$overridePath = JPATH_ROOT . '/images/easysocial_override/' . $group . '/cover/default.jpg';

			$exists = JFile::exists($overridePath);

			if ($exists) {
				$covers[$group] = rtrim(JURI::root(), '/') . '/images/easysocial_override/' . $group . '/cover/default.jpg';
			}
		}

		return $covers[$group];
	}

	/**
	 * Generates a default avatars link
	 *
	 * @since   1.4.9
	 * @access  public
	 */
	public static function getDefaultAvatar($groups, $size, $default = false)
	{
		static $avatars = array();

		$type = $groups . $size;

		if (!isset($avatars[$type]) || $default) {
			$config = ES::config();

			// Default storage /media/com_easysocial/defaults/avatars/user/
			$avatars[$type] = rtrim(JURI::root(), '/') . $config->get('avatars.default.' . $groups . '.' . $size);

			if ($default) {
				return $avatars[$type];
			}

			$overriden = JPATH_ROOT . '/images/easysocial_override/' . $groups . '/avatar/' . $size . '.png';
			$uri = rtrim(JURI::root(), '/') . '/images/easysocial_override/' . $groups . '/avatar/' . $size . '.png';

			if (JFile::exists($overriden)) {
				return $avatars[$type] = $uri;
			}
		}

		return $avatars[$type];
	}

	public static function getEditors($includeBBCode = false)
	{
		$bbcode = null;
		if ($includeBBCode) {
			$bbcode = new stdClass();
			$bbcode->value = 'bbcode';
			$bbcode->text = JText::_('COM_EASYSOCIAL_BBCODE_EDITOR');
		}


	   $db = ES::db();
		$query = 'SELECT `element` AS value, `name` AS text'
				.' FROM `#__extensions`'
				.' WHERE `folder` = "editors"'
				.' AND `type` = "plugin"'
				.' AND `enabled` = 1'
				.' ORDER BY ordering, name';

		$db->setQuery($query);
		$editors = $db->loadObjectList();

		if (!$editors) {
			if ($bbcode) {
				return array($bbcode);
			} else {
				return array();
			}
		}

		// We need to load the language file since we need to get the correct title
		$language = JFactory::getLanguage();

		foreach ($editors as $editor) {
			$language->load($editor->text . '.sys', JPATH_ADMINISTRATOR, null, false, false);
			$editor->text = JText::_($editor->text);
		}

		if ($bbcode) {
			array_unshift($editors, $bbcode);
		}

		return $editors;
	}

	/**
	 * Method to check if the editor is usable or not
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function getEditor($type = 'tinymce')
	{
		// Fall back to 'none' editor if the specified plugin is not enabled
		jimport('joomla.plugin.helper');
		$editorType = JPluginHelper::isEnabled('editors', $type) ? $type : 'none';

		$editor = ES::editor()->getEditor($editorType);

		return $editor;
	}

	public static function getEnvironment()
	{
		$config = ES::getInstance( 'Configuration' );
		return $config->environment;
	}

	public static function getMode()
	{
		$config = ES::getInstance( 'Configuration' );
		return $config->mode;
	}

	/**
	 * Loads the sharing library
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function sharing($options = array())
	{
		$sharing = ES::get('Sharing', $options);

		return $sharing;
	}

	/**
	 * Synchronizes the database table columns
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function syncDB( $from = '' )
	{
		$db     = ES::db();

		return $db->sync( $from );
	}

	/**
	 * Proxy to a target URL item.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public static function proxy($link, $type = 'image')
	{
		$link   = JURI::root() . 'index.php?option=com_easysocial&view=crawler&layout=proxy&tmpl=component&type=' . $type . '&url=' . urlencode($link);

		return $link;
	}

	/**
	 * Generates url for items on the site
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function getUrl($path, $storageType = '')
	{
		static $index = array();

		$hash = md5($path);

		if (!isset($index[$hash])) {
			$cdn = self::getCdnUrl();

			if (!$cdn) {
				$index[$hash] = rtrim(JURI::root(), '/') . '/' . ltrim($path, '/');
			} else {
				$index[$hash] = $cdn . '/' . ltrim($path, '/');
			}
		}

		return $index[$hash];
	}

	/**
	 * Helper to generate controller url for apps
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public static function getAppControllerUrl($appId, $controller, $task, $options = array(), $xhtml = true)
	{
		$options = array_merge(array(
			'appId' => $appId,
			'controller' => 'apps',
			'task' => 'controller',
			'appController' => $controller,
			'appTask' => $task,
			ES::token() => 1
		), $options);

		$url = ESR::apps($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the placeholder to be used for username
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public static function getUsernamePlaceholder()
	{
		static $placeholder = null;

		if (is_null($placeholder)) {
			$config = ES::config();
			$placeholder = $config->get('general.site.loginemail') ? 'COM_EASYSOCIAL_LOGIN_USERNAME_OR_EMAIL_PLACEHOLDER' : 'COM_EASYSOCIAL_LOGIN_USERNAME_PLACEHOLDER';

			if ($config->get('registrations.emailasusername')) {
				$placeholder = 'COM_EASYSOCIAL_LOGIN_EMAIL_PLACEHOLDER';
			}

			$placeholder = JText::_($placeholder);
		}

		return $placeholder;
	}

	/**
	 * Retrieves the base URL of the site
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getBaseUrl()
	{
		$baseUrl = rtrim( JURI::root() , '/' ) . '/index.php?option=com_easysocial';
		$input = self::request();
		$app = JFactory::getApplication();
		$config = ES::config();
		$jConfig = ES::jconfig();
		$uri = self::getURI();
		$language = $uri->getVar( 'lang' , 'none' );
		$router = $app->getRouter();
		$baseUrl = rtrim(JURI::base() , '/') . '/index.php?option=com_easysocial&lang=' . $language;
		$itemId = $input->get('Itemid', 0, 'int');
		$itemId = $itemId ? '&Itemid=' . $itemId : '';

		if (ESRouter::getMode() == SOCIAL_ROUTER_MODE_SEF && JPluginHelper::isEnabled("system" , "languagefilter")) {

			$sefs = JLanguageHelper::getLanguages('sef');
			$lang_codes   = JLanguageHelper::getLanguages('lang_code');

			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);
			$removeLangCode = is_null($params) ? 'null' : $params->get('remove_default_prefix', 'null');

			$rewrite = $jConfig->getValue('sef_rewrite');

			$path = $uri->getPath();
			$parts = explode('/', $path);


			if ($removeLangCode) {

				$defaultLang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				$currentLang = $app->input->cookie->getString(JApplicationHelper::getHash('language'), $defaultLang);

				$defaultSefLang = $lang_codes[$defaultLang]->sef;
				$currentSefLang = $lang_codes[$currentLang]->sef;

				if ($defaultSefLang == $currentSefLang) {
					$language = '';
				} else {
					$language = $currentSefLang;
				}

			} else {

				$base = str_ireplace(JURI::root(true), '', $uri->getPath());
				$path = $rewrite ? $base : ESJString::substr($base , 10);
				$path = trim( $path , '/' );
				$parts = explode( '/' , $path );

				if ($parts) {
					// First segment will always be the language filter.
					$language = reset( $parts );
				} else {
					$language = '';
				}

			}

			if ($language) {
				$language .= '/';
			}

			if ($rewrite) {
				$baseUrl = rtrim(JURI::base(), '/') . '/' . $language . '?option=com_easysocial';
			} else {
				$baseUrl = rtrim(JURI::base(), '/') . '/index.php/' . $language . '?option=com_easysocial';
			}
		}

		return $baseUrl . $itemId;
	}

	/**
	 * Alias for ES::getInstance('maintenance')
	 *
	 * @since  1.2
	 * @access public
	 */
	public static function maintenance()
	{
		return ES::getInstance('maintenance');
	}

	/**
	 * Checks for user's profile completion status
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function checkCompleteProfile()
	{
		$config = ES::config();
		$my = ES::user();
		$input = self::request();

		// If user is not registered, or no profile id, or settings is not enabled, we cannot do anything to check
		if (empty($my->id) || empty($my->profile_id) || !$config->get('user.completeprofile.required', false)) {
			return true;
		}

		$percentage = $my->getProfileCompleteness();

		if ($percentage < 100) {
			$action = $config->get('user.completeprofile.action', 'info');

			if ($action === 'redirect') {
				ES::info()->set(false , JText::sprintf('COM_EASYSOCIAL_PROFILE_YOUR_PROFILE_IS_INCOMPLETE_ENFORCED', $percentage) , SOCIAL_MSG_INFO);
				self::redirect(FRoute::profile(array('layout' => 'edit'), false));
			}

			$view = $input->get('view', '', 'string');
			$userProfilePageId = $input->get('id', '', 'int');
			$isLoggedInUser = $userProfilePageId == $my->id ? true : false;

			if ($action === 'info' || ($action === 'infoprofile' && $view === 'profile' && $isLoggedInUser)) {
				$incompleteMessage = JText::sprintf('COM_EASYSOCIAL_PROFILE_YOUR_PROFILE_IS_INCOMPLETE', $percentage, FRoute::profile(array('layout' => 'edit')));

				ES::info()->set(false, $incompleteMessage, SOCIAL_MSG_WARNING, 'easysocial.profilecompletecheck');
			}

			return false;
		}

		return true;
	}

	/**
	 * Check if there are any unsynced privacy access or not.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public static function hasUnsyncedPrivacy()
	{
		$model = ES::model('Maintenance');
		$count = $model->getMediaPrivacyCounts();
		return $count ? true : false;
	}

	/**
	 * Check if there is error message from Oauth dialog
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public static function checkOauthErrorMessage()
	{
		$session = JFactory::getSession();

		//save messsage into session
		$obj = $session->get('social.message.oauth', '', 'SOCIAL.MESSAGE.OAUTH');

		$jinput = JFactory::getApplication()->input;

		$view = $jinput->get('view', '');
		$layout = $jinput->get('layout', '');

		// we only display these errors when we are not in the registration page
		if ($obj && ($view != 'registration' && $layout != 'oauthDialog')) {
			ES::info()->set(false, $obj->message, $obj->type);
			// clear
			$session->set('social.message.oauth', null, 'SOCIAL.MESSAGE.OAUTH');
		}
	}

	/**
	 * Check if there is error translating language that uses in sef
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public static function checkSefLangTranslation()
	{
		$jconfig = ES::jconfig();

		$config = ES::config();

		$jUnicodeAlias = $jconfig->getValue('unicodeslugs');

		$checkLang = $config->get('seo.langcheck.enabled', 0);

		if ($checkLang && ES::isJoomlaSefEnabled() && !ES::isSh404Installed() && $jUnicodeAlias != '1') {
			// lets try to translate few translations

			$testPages = ['videos', 'audios', 'groups', 'pages'];

			$valid = true;

			foreach ($testPages as $item) {
				$val = SocialRouterAdapter::translate($item);

				$check = JFilterOutput::stringURLSafe($val);

				if (!$check) {
					// not able to translate
					$valid = false;
					break;
				}
			}

			if (!$valid) {
				// lets store into config

				// append user id disabled. let update the seo use id to false.
				$config->set('seo.langcheck.error', "1");

				// Convert the config object to a json string.
				$jsonString = $config->toString();

				$configTable = ES::table('Config');
				if (!$configTable->load('site')) {
					$configTable->type  = 'site';
				}

				$configTable->set('value' , $jsonString);
				$state = $configTable->store();

			}

		}
	}

	/**
	 * Determines if we should render sef cache warning message
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public static function checkSefLangTranslationMessage()
	{
		$config = ES::config();

		$hasError = $config->get('seo.langcheck.error');

		if ($hasError) {

			$theme = ES::themes();

			$warning = JText::_('COM_ES_LANGCHECK_WARNING_MSG');

			$theme->set('text', $warning);
			$message = $theme->output('admin/easysocial/warning/default');

			ES::info()->set(false, $message, SOCIAL_MSG_ERROR);
		}
	}


	/**
	 * Determines if we should render sef cache warning message
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public static function checkSEFCacheMessage()
	{
		$config = ES::config();

		$warning = $config->get('seo.cachefile.warning', '');

		if ($warning) {

			$theme = ES::themes();
			$theme->set('text', $warning);
			$message = $theme->output('admin/sefurls/warning/default');

			ES::info()->set(false, $message, SOCIAL_MSG_ERROR);
		}
	}

	/**
	 * Determines if we should turn off the 2FA settings or not
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public static function check2faSetting()
	{
		$config = ES::config();

		$twofa = $config->get('general.site.twofactor', false);

		if ($twofa && ES::isJoomla42()) {

			// since Joomla 4.2 and above no longer support 2fa, we need to silently turn of 2fa setting in EasySocial.
			$config->set('general.site.twofactor', false);

			// Convert the config object to a json string.
			$jsonString = $config->toString();

			$configTable = ES::table('Config');
			if (!$configTable->load('site')) {
				$configTable->type  = 'site';
			}

			$configTable->set('value' , $jsonString);
			$configTable->store();

			// now lets disable the 2fa custom field
			$field = ES::table('App');
			$field->load(['type' => 'fields', 'element' => 'joomla_twofactor']);
			if ($field->id && $field->state) {
				$field->state = 0;
				$field->store();
			}
		}
	}


	/**
	 * Method to disable the sef caching
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public static function disableSEFCache($warningMsg = '')
	{
		$config = ES::config();

		// append user id disabled. let update the seo use id to false.
		$config->set('seo.cachefile.enabled', "0");

		if ($warningMsg) {
			$config->set('seo.cachefile.warning', $warningMsg);
		}

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
	 * Verify if system has the write permision on sef cache folder
	 *
	 * @since	3.1.8
	 * @access	public
	 */
	public static function verifySefCacheWrite()
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

		// folder exists. check if folder is writable or not.
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

		// returning obj.
		$obj = new stdClass();
		$obj->hasError = $hasError;
		$obj->message = $warning;

		return $obj;
	}


	/**
	 * Determines if we should render welcome message
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public static function checkWelcomeMessage()
	{
		$config = ES::config();
		$my = ES::user();

		// If user is not registered, or no profile id, or settings is not enabled, we cannot do anything to check
		if (empty($my->id) || !$config->get('welcome.enabled', false)) {
			return true;
		}

		if (!$my->hasCommunityAccess()) {
			return true;
		}

		if ($my->showWelcomeMessage()) {
			$text = JText::_($config->get('welcome.text', 'COM_EASYSOCIAL_WELCOME_MESSAGE'));

			$theme = ES::themes();
			$theme->set('text', $text);
			$message = $theme->output('site/info/welcome');

			ES::info()->set(false, $message, SOCIAL_MSG_INFO);
		}
	}

	public static function dbcache($key, $options = array())
	{
		static $instances = array();

		if (!isset($instances[$key])) {
			$instances[$key] = ES::get('Dbcache', $key, $options);
		}

		return $instances[$key];
	}

	/**
	 * Alias method to return the appropriate cluster type
	 *
	 * @since  2.0.14
	 * @access public
	 */
	public static function cluster($type = '', $id = null, $reload = null, $debug = null)
	{
		static $mapping = array();

		// If cluster doesn't exists, we need to figure this out
		$total = func_num_args();

		// We need to figure out the type of the cluster
		if ($total == 1 || $id === null) {
			// Shift the arguments
			if (isset($mapping[$type])) {
				$id = $type;
				$type = $mapping[$type];
			} else {
				$model = ES::model('Clusters');
				$mapping[$type] = $model->getType($type);

				$id = $type;
				$type = $mapping[$type];
			}
		}

		return call_user_func(array('ES', $type), $id, $reload, $debug);
	}

	/**
	 * Remove older javascript files
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function purgeOldVersionScripts()
	{
		// Get the current installed version
		$version = ES::getLocalVersion();

		// Ignored files
		$ignored = array('.svn', 'CVS', '.DS_Store', '__MACOSX');
		$ignored[] = 'admin-' . $version . '.min.js';
		$ignored[] = 'admin-' . $version . '.js';
		$ignored[] = 'admin-' . $version . '-basic.js';
		$ignored[] = 'admin-' . $version . '-basic.min.js';
		$ignored[] = 'site-' . $version . '.min.js';
		$ignored[] = 'site-' . $version . '.js';
		$ignored[] = 'site-' . $version . '-basic.js';
		$ignored[] = 'site-' . $version . '-basic.min.js';
		$ignored[] = 'bootloader.js';
		$ignored[] = 'sharer.js';
		$ignored[] = 'template.php';

		$files = JFolder::files(JPATH_ROOT . '/media/com_easysocial/scripts', '.', false, true, $ignored);

		if ($files) {
			foreach ($files as $file) {
				JFile::delete($file);
			}
		}

		return true;
	}

	/**
	 * Alias method to return JFactory::getApplication()->input;
	 *
	 * @since  1.2.17
	 * @access public
	 */
	public static function input($hash = 'default')
	{
		// Possible $hash = 'default', 'get', 'post', 'server', 'files';

		$input = JFactory::getApplication()->input;

		$hash = strtolower($hash);

		if ($hash === 'default') {
			return $input;
		}

		return $input->$hash;
	}

	/**
	 * Determines if Joomla SEF enabled or not
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function isJoomlaSefEnabled()
	{
		$jConfig = ES::jconfig();
		return $jConfig->getValue('sef');
	}

	/**
	 * Determines if SH404 is installed
	 *
	 * @since   1.4
	 * @access  public
	 */
	public static function isSh404Installed()
	{
		$file = JPATH_ADMINISTRATOR . '/components/com_sh404sef/sh404sef.class.php';
		$enabled = false;

		if (defined('SH404SEF_AUTOLOADER_LOADED') && JFile::exists($file)) {
			require_once($file);

			if (class_exists('shRouter')) {
				$sh404Config = shRouter::shGetConfig();

				if ($sh404Config->Enabled) {
					$enabled = true;
				}
			}
		}

		return $enabled;
	}

	/**
	 * Determines if SH404 is installed and easysocial configured to use sh404sef sef_ext plugins
	 *
	 * @since   1.4
	 * @access  public
	 */
	public static function isSh404EasySocialEnabled()
	{
		if (! ES::isSh404Installed()) {
			return false;
		}

		$file = JPATH_ADMINISTRATOR . '/components/com_sh404sef/sh404sef.class.php';
		$enabled = true;

		if (defined('SH404SEF_AUTOLOADER_LOADED') && JFile::exists($file)) {
			require_once($file);

			if (class_exists('shRouter')) {
				$sh404Config = shRouter::shGetConfig();

				$shDoNotOverrideOwnSef = $sh404Config->shDoNotOverrideOwnSef;

				// dump($shDoNotOverrideOwnSef);

				if ($shDoNotOverrideOwnSef && in_array('easysocial', $shDoNotOverrideOwnSef)) {
					$enabled = false;
				}
			}
		}

		return $enabled;
	}


	/**
	 * Retrieve current site language code
	 *
	 * @since   1.4.12
	 * @access  public
	 */
	public static function getCurrentLanguageCode()
	{
		$langCode = '';

		// site default language
		$defaultLangCode = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		// Get the languagefilter params
		$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
		$params = new JRegistry();
		$params->loadString(empty($plugin) ? '' : $plugin->params);
		$langFilterRemoveLangCodeParams = is_null($params) ? 'null' : $params->get('remove_default_prefix', 'null');

		// current viewing language
		$lang = JFactory::getLanguage();
		$languages = JLanguageHelper::getLanguages('lang_code');

		// check if the languagefilter plugin enabled
		$pluginEnabled = JPluginHelper::isEnabled('system', 'languagefilter');

		// Check the 'Remove URL Language Code' option is disabled or not from the languagefilter plugin
		if ($pluginEnabled && !$langFilterRemoveLangCodeParams) {
			$langCode = $languages[$lang->getTag()]->sef;

		} else if ($pluginEnabled && $langFilterRemoveLangCodeParams) {
			if ($defaultLangCode != $lang->getTag()) {
				$langCode = $languages[$lang->getTag()]->sef;
			}
		}

		return $langCode;
	}

	/**
	 * Creates the request library
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function request()
	{
		return ES::get('Request');
	}

	/**
	 * Creates the cache library
	 *
	 * @since   2.1.0
	 * @access  public
	 */
	public static function cache()
	{
		ES::load('Cache');
		$cache = SocialCache::getInstance();

		return $cache;
	}

	/**
	 * Allow callers to set meta data
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function setMeta($userCustomRobots = false)
	{
		$doc = JFactory::getDocument();
		$active = JFactory::getApplication()->getMenu()->getActive();
		$meta = false;

		if ($active) {
			$params = $active->getParams();

			$description = $params->get('menu-meta_description', '');
			$keywords = $params->get('menu-meta_keywords', '');
			$robots = $params->get('robots', '');

			// override the menu item robots data if the user has set the profile indexing from edit privacy page.
			if ($userCustomRobots && $userCustomRobots != SOCIAL_PROFILE_ROBOTS_INHERIT) {
				$robots = $userCustomRobots;
			}

			if (!empty($description) || !empty($keywords) || !empty($robots)) {
				$meta = new stdClass();
				$meta->description = ES::string()->escape($description);
				$meta->keywords = $keywords;
				$meta->robots = $robots;
			}
		}

		if (!$meta) {
			return;
		}

		// override the menu item robots data if the user has set the profile indexing from edit privacy page.
		if ($meta && $userCustomRobots && $userCustomRobots != SOCIAL_PROFILE_ROBOTS_INHERIT) {
			$meta->robots = $userCustomRobots;
		}

		if ($meta->keywords) {
			$doc->setMetadata('keywords', $meta->keywords);
		}

		if ($meta->description) {
			$doc->setMetadata('description', $meta->description);
		}

		if ($meta->robots) {
			$doc->setMetadata('robots', $meta->robots);
		}

		// Set meta data
		ES::meta()->setMetaObj($meta);
	}

	public static function generateUniqueId($list = array())
	{
		$uniqueId = uniqid();

		// Self loop until the id is trully unique
		if (in_array($uniqueId, $list)) {
			return self::generateUniqueId($list);
		}

		return $uniqueId;
	}

	/**
	 * Get the upload message for Story Form
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function getUploadMessage($type, $storyForm = true)
	{
		$theme = ES::themes();

		$type = strtoupper($type);

		$message = 'COM_ES_' . $type . '_DROP_MESSAGE';

		if ($theme->isMobile()) {
			$message = $message . '_MOBILE';
		}

		return JText::_($message);
	}

	/**
	 * Populate full lists of cluster categories
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function populateCategoriesTree($type = SOCIAL_TYPE_GROUP, $exclusion = array(), $options = array())
	{
		if ($type == SOCIAL_TYPE_MARKETPLACE) {
			$model = ES::model('MarketplaceCategories');
			$parentCat = $model->getParentCategories($exclusion, $options);
		} else {
			$model = ES::model('ClusterCategory');
			$parentCat = $model->getParentCategories($exclusion, $type, $options);
		}

		$categories = array();

		if (!empty($parentCat)) {
			for ($i = 0; $i < count($parentCat); $i++) {
				$parent = ES::table('ClusterCategory');

				if ($type == SOCIAL_TYPE_MARKETPLACE) {
					$parent = ES::table('MarketplaceCategory');
				}

				$parent->bind($parentCat[$i]);

				$parent->childs = null;

				$parent->total = $parent->getTotalCluster($type, $options);

				$parent->title = JText::_($parent->title);

				ES::buildNestedCategories($parent->id, $parent, $exclusion, $type, $options);

				$categories[] = $parent;
			}
		}

		return $categories;
	}

	/**
	 * Populate parent categories selection for cluster
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function populateCategories($name, $default, $exclusion = array(), $type = SOCIAL_TYPE_GROUP, $attributes = '', $firstOption = true, $disableContainers = false)
	{
		$parentCat = ES::populateCategoriesTree($type, $exclusion);

		$selected = !$default ? ' selected="selected"' : '';
		$options = '';

		if ($firstOption) {
			$options .= '<option value="0"' . $selected . '>' . JText::_('COM_ES_SELECT_PARENT_CATEGORY') . '</option>';
		}

		if ($parentCat) {
			foreach ($parentCat as $category) {

				$style = '';
				$disabled = '';

				if ($disableContainers) {
					$disabled = $category->container ? ' disabled="disabled"' : '';
					$style = $disabled ? ' style="font-weight:700;"' : '';
				}

				$selected = ($category->id == $default) ? ' selected="selected"' : '';
				$options .= '<option value="' . $category->id . '" ' . $selected . $disabled . $style . '>' . JText::_($category->title) . '</option>';

				ES::accessNestedCategories($category, $options, '0', $default);
			}
		}

		$html = '<div class="o-select-group">';
		$html .= '<select name="' . $name . '" id="' . $name .'" class="o-form-control" ' . $attributes . '>';
		$html .= $options;
		$html .= '</select>';
		$html .= '<label for="' . $name . '" class="o-select-group__drop"></label>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Build a nested category for selection
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function buildNestedCategories($parentId, &$parent, $exclusion = array(), $type = SOCIAL_TYPE_GROUP, $options = array())
	{
		$buildNested = isset($options['buildNested']) ? $options['buildNested'] : true;

		$childs = array();

		//lets try to get from cache if there is any
		if ($type == SOCIAL_TYPE_MARKETPLACE) {
			$model = ES::model('MarketplaceCategories');
			$childs = $model->getChildCategories($parentId, $exclusion, $options);
		} else {
			$model = ES::model('ClusterCategory');
			$childs = $model->getChildCategories($parentId, $exclusion, $type, $options);
		}

		if (!$childs) {
			return false;
		}

		$items = array();
		$parent->childs = array();

		foreach ($childs as $child) {

			$table = ES::table('ClusterCategory');

			if ($type == SOCIAL_TYPE_MARKETPLACE) {
				$table = ES::table('MarketplaceCategory');
			}

			$table->bind($child);

			$table->total = $table->getTotalCluster($type, $options);

			if ($buildNested) {
				$items[$child->id] = $table;
			} else {
				$parent->childs[$child->id] = $table;
			}
		}

		// Load nested childs with respect to their parent
		if ($buildNested) {
			ES::addChilds($parent, $items);
		}

		return false;
	}

	/**
	 * Allow callers to assign child to the parent object
	 *
	 * @since   2.1
	 * @access  public
	 */
	public static function addChilds(&$parent, $items)
	{
		if (!$items) {
			return false;
		}

		foreach($items as $cItem) {
			if ($cItem->parent_id == $parent->id) {

				$tmpParent = $cItem;
				$tmpParent->childs = array();

				ES::addChilds($tmpParent, $items);

				$parent->childs[] = $tmpParent;
			}
		}

		return false;
	}

	public static function accessNestedCategories($arr, &$html, $deep = '0', $default = '0')
	{
		if (isset($arr->childs) && is_array($arr->childs)) {
			$sup = '<sup>|_</sup>';
			$space = '';

			$deep++;
			for ($d=0; $d < $deep; $d++) {
				$space .= '&nbsp;&nbsp;&nbsp;';
			}

			for ($j = 0; $j < count($arr->childs); $j++) {
				$child = $arr->childs[$j];

				$selected = ($child->id == $default) ? ' selected="selected"' : '';

				$html .= '<option value="' . $child->id . '" ' . $selected . '>' . $space . $sup . JText::_($child->title)  . '</option>';

				ES::accessNestedCategories($child, $html, $deep, $default);
			}
		}

		return false;
	}

	/**
	 * Determines if the given mime is an image
	 *
	 * @since	3.2.8
	 * @access	public
	 */
	public static function isImage($mime)
	{
		static $images = array('image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/apng', 'image/webp');

		if (ES::isImagickEnabled()) {
			$images[] = 'image/heic';
		}

		return in_array($mime, $images);
	}

	/**
	 * Determines if the user is viewing from the admin
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public static function isFromAdmin()
	{
		$isFromAdmin = null;

		if (is_null($isFromAdmin)) {
			$isFromAdmin = ESCompat::isFromAdmin();
		}

		return $isFromAdmin;
	}

	/**
	 * Determines if the user is a super admin on the site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public static function isSiteAdmin($id = null)
	{
		static $items = array();

		$user = ES::user($id);

		if (!isset($items[$user->id])) {
			$items[$user->id] = $user->isSiteAdmin();
		}

		return $items[$user->id] ? true : false;
	}

	/**
	 * If the current user is a super admin, allow them to change the environment via the query string
	 *
	 * @since	2.1
	 * @access	public
	 */
	public static function checkEnvironment()
	{
		if (!ES::isSiteAdmin()) {
			return;
		}

		$app = JFactory::getApplication();
		$environment = $app->input->get('es_env', '', 'word');
		$allowed = array('static', 'production', 'development');

		// Nothing has changed
		if (!$environment || !in_array($environment, $allowed)) {
			return;
		}

		// Standardize the value of environment
		if ($environment == 'production') {
			$environment = 'static';
		}

		$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/site.json';
		$contents = file_get_contents($file);
		$contents = preg_replace('/\"environment\": \"(.*)\"/', '"environment": "' . $environment . '"', $contents);

		JFile::write($file, $contents);

		// We also need to update the database value
		$configTable = ES::table('Config');
		$configTable->load('site');

		$config = ES::registry();
		$config->load($configTable->value);

		$config->set('general.environment', $environment);
		$configTable->set('value', $config->toString());
		$configTable->store();

		ES::info()->set('Updated system environment to <b>' . $environment . '</b> mode', 'success');
		return self::redirect('index.php?option=com_easysocial');
	}

	/**
	 * Show proper error page considerating the error redirection option.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public static function raiseError($code = '', $message = '')
	{
		$config = ES::config();
		$app = JFactory::getApplication();

		if (!$code) {
			$code = '404';
		}

		if (!$message) {
			$message = JText::_('COM_ES_404_PAGE_NOT_FOUND');
		}


		if ($config->get('general.error.redirection', false)) {
			// if user not login, redirect user to login page.
			ES::requireLogin();

			// show message and redirect user to frontpage.
			ES::info()->set(false, $message, SOCIAL_MSG_ERROR);

			// always redirect to user dashboard for now
			$redirectURL = ESR::dashboard(array(), false);

			return self::redirect($redirectURL);
		}

		throw ES::exception($message, $code);
	}

	/**
	 * Determine if the site is running on https
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public static function isHttps()
	{
		static $isHttps = null;

		if (is_null($isHttps)) {

			// Get url protocol
			$uri = JURI::getInstance();
			$protocol = $uri->toString(array('scheme'));

			$isHttps = false;

			if ($protocol === 'https://') {
				$isHttps = true;
			}
		}

		return $isHttps;
	}

	/**
	 * Alias for ES::get( 'FileCache' )
	 *
	 * @since   3.0
	 * @access  public
	 */
	public static function fileCache()
	{
		ES::load('FileCache');

		$cache = SocialFileCache::getInstance();

		return $cache;
	}

	/**
	 * Get the custom reactions
	 *
	 * @since	3.1.4
	 * @access	public
	 */
	public static function getCustomReactions()
	{
		static $file = null;

		if (is_null($file)) {

			// Default file to false
			$file = false;

			$app = JFactory::getApplication();

			// Get the template override path
			$path = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_easysocial/images/reactions/icon-reactions.svg';

			// Check if it exists
			$exists = JFile::exists($path);

			if ($exists) {
				$file = self::getUrl('/templates/' . $app->getTemplate() . '/html/com_easysocial/images/reactions/icon-reactions.svg');
			}
		}

		return $file;
	}

	/**
	 * Update link assets for image from re-crawl process
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function updateLinkAssets($streamItem, $crawlData, $previousCrawlData)
	{
		// Skip this if doesn't have the data from the crawl
		if (!$crawlData) {
			return;
		}

		// Get the link information from the re-crawl process
		$link = $crawlData->url;
		$title = isset($crawlData->title) && $crawlData->title ? $crawlData->title : '';
		$content = isset($crawlData->description) && $crawlData->description ? $crawlData->description : '';
		$image = isset($crawlData->images) && $crawlData->images ? $crawlData->images[0] : '';

		// if detected the re-crawl process doesn't return any image then delete the previous cached image.
		if (!$image) {

			// Need to delete the previous cache image if exist
			$linkImage = ES::table('LinkImage');
			$linkImage->load(array('internal_url' => $previousCrawlData->get('image')));

			// Retrieve the previous cache image path
			$cachedImagePath = $linkImage->getAbsolutePath();

			// Check if the file already exists
			$cachedImagePathExists = JFile::exists($cachedImagePath);

			// If the file is already cached before, delete it
			if ($cachedImagePathExists) {
				JFile::delete($cachedImagePath);

				// Delete the link image data for this as well.
				$linkImage->delete();
			}
		}

		$links = ES::links();
		$fileName = $links->cacheImage($image);

		$registry = ES::registry();
		$registry->set('title', $title);
		$registry->set('content', $content);
		$registry->set('image', $image);
		$registry->set('link', $link);
		$registry->set('cached', false);

		// Image link should only be modified when the file exists
		if ($fileName !== false) {
			$registry->set('cached', true);
			$registry->set('image', $fileName);
		}

		// Convert to JSON string
		$crawlData = $registry->toString();

		// Update the link object into the assets table for this stream item
		$assets = ES::table('StreamAsset');
		$state = $assets->updateAssetsData($streamItem->uid, $crawlData);

		return $state;
	}

	/**
	 * Method to determine if keep alive is required or not.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public static function keepAlive()
	{
		static $keepAlive = null;

		// 1. check if doctype is html and user is logged in.
		// 2. check if remember system plugin enabled or not.
		// 3. check if the session timeout is too low? if duration more than 10min, we will skip
		// 4. check if there is this remember_me cookies or not.

		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		// 1
		if ($doc->getType() != 'html') {
			return false;
		}

		// 1
		if (JFactory::getUser()->get('guest')) {
			return false;
		}

		if (is_null($keepAlive)) {

			// 2
			$pluginEnabled = JPluginHelper::isEnabled('system', 'remember');
			if (!$pluginEnabled) {
				$keepAlive = false;
				return false;
			}

			// 3
			$config = ES::jconfig();
			$lifetime = ($config->get('lifetime') * 60000);
			if ($lifetime > 600000) {
				$keepAlive = false;
				return false;
			}

			// 4
			// check for the cookies
			$cookieName = 'joomla_remember_me_' . JUserHelper::getShortHashedUserAgent();
			$cookieValue = $app->input->cookie->get($cookieName);

			// Try with old cookieName (pre 3.6.0) if not found
			if (!$cookieValue) {
				$cookieName = JUserHelper::getShortHashedUserAgent();
				$cookieValue = $app->input->cookie->get($cookieName);
			}

			if (!$cookieValue) {
				$keepAlive = false;
				return false;
			}

			$keepAlive = true;

			// now we need to add the behaviour.keepalive;
			JHtml::_('behavior.keepalive');
		}

		return $keepAlive;
	}

	/**
	 * Method to determine if block should be checked or not.
	 *
	 * @since	3.2
	 * @access	public
	 */
	public static function isBlockEnabled($userObj = false, $skipSiteAdmin = true)
	{
		$user = !$userObj ? JFactory::getUser() : $userObj;

		// We skip this for site admin because admins need to moderate users even if the user is blocked by the admin
		// plus, user wont be able to block admin. #3888
		// Unless the caller explicitly want to include site admin as part of the checking. #4113
		if ($skipSiteAdmin && ES::isSiteAdmin($user->id)) {
			return false;
		}

		$config = ES::config();

		if ($config->get('users.blocking.enabled') && !$user->guest) {
			return true;
		}

		return false;
	}

	/**
	 * Convert a permalink into an allowed permalink
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public static function generatePermalink($permalink)
	{
		$jConfig = ES::jConfig();

		if ($jConfig->get('unicodeslugs')) {
			$permalink = JFilterOutput::stringURLUnicodeSlug($permalink);

			return $permalink;
		}

		$permalink = JFilterOutput::stringURLSafe($permalink);

		if (empty($permalink)) {
			$permalink = JFilterOutput::stringURLSafe(JFactory::getDate()->toSql());
		}

		return $permalink;
	}

	/**
	 * Get JWT token from server
	 *
	 * @since   3.2
	 * @access  public
	 */
	public static function generateJWTToken()
	{
		$config = ES::config();

		$team_id =  $config->get('oauth.apple.team');
		$key_id = $config->get('oauth.apple.key');
		$client_id = $config->get('oauth.apple.app');
		$api_key = $config->get('general.key');
		$path = JPATH_ROOT . '/media/com_easysocial/tmp/' . $config->get('oauth.apple.keyfile');

		if (!$team_id || !$key_id || !$client_id || !JFile::exists($path)) {
			return false;
		}

		$post = array(
			'keyFile' => class_exists('CURLFile', false) ? new CURLFile($path, 'application/octet-stream') : "@" . $path,
			'teamId' => $team_id,
			'keyId' => $key_id,
			'clientId' => $client_id,
			'domain' => rtrim(JURI::root(), '/'),
			'key' => $api_key
		);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, SOCIAL_JWT_SERVER);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100000);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$result = json_decode($result);

		// We need to know the response status
		if ($result->code != 200) {
			return false;
		}

		// need to store last token generate.
		$configTable = ES::table('Config');
		$registry = ES::registry();

		if ($configTable->load('site')) {
			$registry->load($configTable->value);
		}

		$registry->set('oauth.apple.expired', ES::date('+6 month')->toUnix());
		$registry->set('oauth.apple.secret', $result->token);

		$configTable->set('value', $registry->toString());
		$configTable->store();

		return $result->token;
	}

	/**
	 * Retrieve the instance of JURI
	 *
	 * @since   3.3
	 * @access  public
	 */
	public static function getURI($requestPath = false)
	{
		$uri = JUri::getInstance();

		// Gets the full request path.
		if ($requestPath) {
			$uri = $uri->toString(['path', 'query']);
		}

		return $uri;
	}

	/**
	 * Redirects to a given link
	 *
	 * @since	3.3
	 * @access	public
	 */
	public static function redirect($link, $message = '', $class = '')
	{
		$app = JFactory::getApplication();

		if ($message) {
			$message = JText::_($message);
		}

		if (self::isJoomla4()) {
			if ($message) {
				$app->enqueueMessage($message, $class);
			}

			$app->redirect($link);
			return $app->close();
		}

		$app->redirect($link, $message, $class);
		return $app->close();
	}

	/**
	 * Generates a response object
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public static function response($message = '', $type = ES_ERROR)
	{
		ES::load('response');

		$response = new SocialResponse($message, $type);

		return $response;
	}

	/**
	 * Determine if imagick is enabled on the server
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public static function isImagickEnabled()
	{
		static $imagick = null;

		if (is_null($imagick)) {
			$imagick = false;
			$config = ES::config();

			if ($config->get('photos.uploader.driver') == 'imagick' && extension_loaded('imagick')) {
				$imagick = true;
			}
		}

		return $imagick;
	}

	/**
	 * Alias for ES::get('Marketplace')
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function marketplace($ids = null, $reload = null, $debug = false)
	{
		// Load the marketplace library
		ES::load('marketplace');

		if (is_null($ids)) {
			return new SocialMarketplace();
		}

		$state = SocialMarketplace::factory($ids, $reload, $debug);

		if ($state === false) {
			return new SocialMarketplace();
		}

		return $state;
	}

	/**
	 * Generate canonical options which contains the filter, ordering and limitstart...
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public static function normalizeCanonicalOptions($includeExternalOptions = false)
	{
		$input = self::request();
		$options = [];

		// default clusters
		$limitstart = $input->get('limitstart', 0, 'int');
		$filter = $input->get('filter', '', 'cmd');
		$ordering = $input->get('ordering', '', 'cmd');
		$sort = $input->get('sort', '', 'cmd');

		// event
		$includePastEvent = $input->get('includePast', '', 'int');
		$eventFilterDate = $input->get('date', '', 'default');

		// profile page
		$userAliasWithId = $input->get('userid', '', 'default');

		// People page
		$id = $input->get('id', '', 'default');

		// Determine the page shouldn't be index especially ordering and sorting.
		$noindex = false;

		// It is optional because other function parameter already check for this
		if ($includeExternalOptions) {
			$options['external'] = true;
		}

		if ($userAliasWithId) {
			$options['userid'] = $userAliasWithId;
		}

		if ($eventFilterDate) {
			$options['date'] = $eventFilterDate;
		}

		if ($filter) {
			$options['filter'] = $filter;
		}

		if ($ordering) {
			$options['ordering'] = $ordering;
			$noindex = true;
		}

		if ($sort) {
			$options['sort'] = $sort;
			$noindex = true;
		}

		if ($id) {
			$options['id'] = $id;
		}

		if ($includePastEvent) {
			$options['includePast'] = $includePastEvent;
		}

		if ($limitstart != 0) {
			$options['limitstart'] = $limitstart;
		}

		// Exclude these ordering and sorting page shouldn't get index by search engine advised by Google
		if ($noindex) {
			$doc = JFactory::getDocument();
			$doc->setMetadata('robots', 'noindex,follow');
		}

		return $options;
	}

	/**
	 * Method to generate the QR code for mobile login
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public static function getMobileQRLoginUrl()
	{
		$url = ESR::apps(['layout' => 'mobileAppQrcode', 'tmpl' => 'component', '1' => ES::date()->toUnix()]);

		// Use Foundry QR code if exists
		if (class_exists('FR') && method_exists('FR', 'getMobileQrcodeURL')) {
			$url = FR::user()->getMobileQRLoginUrl();
		}

		return $url;
	}


	/**
	 * Method to check if application is on cli mode or not.
	 *
	 * @since	4.0.9
	 * @access	public
	 */
	public static function isCli()
	{
		$app = JFactory::getApplication();

		if (method_exists($app, 'isCli')) {
			return $app->isCli();
		}

		return false;
	}


	public static function getSiteMenu()
	{
		if (self::isCli()) {
			if (self::isJoomla3()) {
				$app = \Joomla\CMS\Factory::getApplication('site');
				return $app->getMenu('site');
			}

			$container = \Joomla\CMS\Factory::getContainer();
			$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
			return $app->getMenu('site');
		}

		$app = JFactory::getApplication();
		return $app->getMenu('site');
	}
}

// Backward compatibility
class FD extends ES {}
class Foundry extends ES {}

