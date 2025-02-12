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

class SocialAjax extends EasySocial
{
	private $commands = array();
	static $instance = null;

	public function addCommand($type, &$data)
	{
		$this->commands[] = array(
			'type' => $type,
			'data' =>& $data
		);

		return $this;
	}

	/**
	 * Creates a copy of it self and return to the caller.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Format our own ajax url
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getUrl()
	{
		static $url;

		if (isset($url)) {
			return $url;
		}

		$uri = ES::getURI();
		$language = $uri->getVar('lang', 'none');

		// Remove any ' or " from the language because language should only have -
		$app = JFactory::getApplication();
		$input = $app->input;

		$language = $input->get('lang', '', 'cmd');

		$jConfig = ES::jconfig();

		// Get the router
		$router = $app->getRouter();

		// It could be admin url or front end url
		$url = rtrim(JURI::base(), '/') . '/';

		// Determines if we should use index.php for the url
		$config = ES::config();

		if ($config->get('general.ajaxindex')) {
			$url .= 'index.php';
		}

		// Append the url with the extension
		$url = $url . '?option=com_easysocial&lang=' . $language;

		// During SEF mode, we need to ensure that the URL is correct.
		$languageFilterEnabled = JPluginHelper::isEnabled("system","languagefilter");

		if (ESRouter::getMode() == SOCIAL_ROUTER_MODE_SEF && !ES::isFromAdmin() && $languageFilterEnabled) {

			$sefs = JLanguageHelper::getLanguages('sef');
			$lang_codes   = JLanguageHelper::getLanguages('lang_code');

			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);
			$removeLangCode = is_null($params) ? 'null' : $params->get('remove_default_prefix', 'null');


			// Determines if the mod_rewrite is enabled on Joomla
			$rewrite = $jConfig->getValue('sef_rewrite');

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
				// Replace the path if it's on subfolders
				$base = str_ireplace(JURI::root(true), '', $uri->getPath());

				if ($rewrite) {
					$path = $base;
				} else {
					$path = ESJString::substr($base, 10);
				}

				// Remove trailing / from the url
				$path = ESJString::trim($path, '/');
				$parts = explode('/', $path);

				if ($parts) {
					// First segment will always be the language filter.
					$language = reset($parts);
				} else {
					$language = 'none';
				}
			}

			if ($language) {
				$language .= '/';
			}

			if ($rewrite) {
				$url = rtrim(JURI::root(), '/');

				if ($config->get('general.ajaxindex')) {
					$url .= '/index.php';
				}

				$url .= '/' . $language . '?option=com_easysocial';
			} else {
				$url = rtrim(JURI::root(), '/') . '/index.php/' . $language . '?option=com_easysocial';
			}
		}

		$menu = JFactory::getApplication()->getmenu();

		if (!empty($menu)) {
			$item = $menu->getActive();

			if (isset($item->id)) {
				$url .= '&Itemid=' . $item->id;
			}
		}

		// Some SEF components tries to do a 301 redirect from non-www prefix to www prefix. Need to sort them out here.
		$currentURL = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

		if (!empty($currentURL)) {

			// When the url contains www and the current accessed url does not contain www, fix it.
			if (stristr($currentURL, 'www') === false && stristr($url, 'www') !== false) {
				$url = str_ireplace('www.', '', $url);
			}

			// When the url does not contain www and the current accessed url contains www.
			if (stristr($currentURL, 'www') !== false && stristr($url, 'www') === false) {
				$url = str_ireplace('://', '://www.', $url);
			}
		}

		return $url;
	}

	/**
	 * Resolve a given POSIX path.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function resolveNamespace($namespace)
	{
		// Get the request
		$input = JFactory::getApplication()->input;

		$parts = explode('/', $namespace);

		// Determine the location of the namespace
		$location = $parts[0];

		// Remove the location from parts.
		array_shift($parts);

		// Remove the method from the namespace
		$method = array_pop($parts);

		// Get the absolute path of the initial location
		$path = $location == 'admin' ? SOCIAL_ADMIN : SOCIAL_SITE;

		// Determine if this is a view or controller.
		if ($location == 'site' || $location == 'admin') {

			$glued = implode('/', $parts);

			if ($parts[0] == 'controllers') {
				$path = $path . '/' . $glued . '.php';
			} else {
				$path = $path . '/' . $glued . '/view.ajax.php';

				$place = $location == 'site' ? 'site' : 'admin';

				// Import the base view
				ES::import($place . ':/views/views');
			}
		}

		// If the location is meant for apps, we need to determine the correct path now.
		if ($location == 'apps') {

			// Whether this is a "user", "group", "event" app.
			$group = $parts[0];

			// The element of the app.
			$element = $parts[1];

			// Whether this request is made for controllers or views
			$type = $parts[2];

			// Don't know what this is
			$typeFile = $parts[3];

			// E.g: apps:/user/tasks/views/viewName/functionName
			if ($type == 'views') {
				$path = SOCIAL_APPS . '/' . $group . '/' . $element . '/views/' . $typeFile . '/view.ajax.php';
			}

			// E.g: apps:/user/tasks/controllers/tasks/functionName
			if ($type == 'controllers') {
				// Import dependencies.
				ES::import('admin:/includes/apps/apps');

				$path = SOCIAL_APPS . '/' . $group . '/' . $element . '/controllers/' . $typeFile . '.php';
			}
		}

		// If the location is meant for custom fields, we need to determine the correct path
		if ($location == 'fields') {

			// This is the field group. E.g: users , groups etc.
			$group = $parts[0];

			// This is the field element.
			$element = $parts[1];

			$path = SOCIAL_FIELDS . '/' . $group . '/' . $element . '/ajax.php';
		}

		// Get the arguments from the query string if there is any.
		$args = $input->get('args', '', 'default');

		// Check that the file exists.
		jimport('joomla.filesystem.file');

		$ajax = ES::ajax();

		if (!JFile::exists($path)) {
			$ajax->reject(JText::sprintf('The file %1s does not exist.', $namespace));
			return $ajax->send();
		}

		// Include the path.
		include_once($path);

		// Get the adapter to process.
		$adapter = self::getAdapter($location);
		$adapter->execute($namespace, $parts, $args, $method);

		// Terminate the output.
		$ajax->send();

		return $path;
	}

	/**
	 * Retrieves an ajax adapter so that it knows how to resolve the calls
	 *
	 * @since	1.4
	 * @access	public
	 */
	public static function getAdapter($location)
	{
		static $adapters = [];

		if (!isset($adapters[$location])) {
			require_once(__DIR__ . '/adapters/abstract.php');

			$file = __DIR__ . '/adapters/' . strtolower($location) . '.php';

			require_once($file);

			$className = 'SocialAjaxAdapter' . ucfirst($location);
			$adapters[$location] = new $className();
		}

		return $adapters[$location];
	}

	/* This will handle all ajax commands e.g. success/fail/script */
	public function __call($method, $args)
	{
		$this->addCommand($method, $args);

		return $this;
	}

	public function EasySocial($selector=null)
	{
		$chain = array();

		$this->addCommand('script', $chain);

		// Because we need to maintain the variable to be passed by reference,
		// we need to use an array instead as arguments.
		$js = ES::get( array( 'Javascript' , true ) , array( &$chain ) );

		if (isset($selector))
		{
			$js->EasySocial($selector);
		}
		else
		{
			$js->EasySocial;
		}

		return $js;
	}

	/**
	 * Terminates the output
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function send()
	{
		header('Content-type: application/json; UTF-8');

		$callback = $this->input->get('callback', '', 'default');

		// Isolate PHP errors and send it as a notify command.
		$error_reporting = ob_get_contents();
		if (strlen(trim($error_reporting))) {
			$this->notify($error_reporting, 'debug');
		}

		// check if there is any buffer before we calling ob_clean
		$buffer = ob_get_contents();
		if ($buffer) {
			ob_clean();
		}

		// Process jsonp requests if necessary.
		if ($callback) {
			header('Content-type: application/javascript; UTF-8');
			echo $callback . '(' . json_encode($this->commands) . ');';
			exit;
		}

		$transport = $this->input->get('transport', '', 'default');

		if ($transport == "iframe") {
			header('Content-type: text/html; UTF-8');
			echo '<textarea data-type="application/json" data-status="200" data-statusText="OK">' . json_encode($this->commands) . '</textarea>';
			exit;
		}

		echo json_encode($this->commands);
		exit;
	}

	/**
	 * Processes an ajax call that is passed to the server. It is smart enough to decide which
	 * file would be responsible to keep these codes.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function listen()
	{
		// Do not proceed if the request is not in ajax format.
		if ($this->doc->getType() != 'ajax') {
			return;
		}

		// Namespace format should be POSIX format.
		$namespace = $this->input->get('namespace', '', 'default');

		// Split the namespace
		$parts = explode(':/', $namespace);

		// Detect if the user passed in a protocol.
		$hasProtocol = count($parts) > 1;

		if (!$hasProtocol) {
			$namespace = 'ajax:/' . $namespace;
		}

		return ES::resolve($namespace);
	}
}
