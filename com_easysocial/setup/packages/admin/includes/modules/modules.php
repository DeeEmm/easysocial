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

jimport('joomla.filesystem.file');

class SocialModules extends EasySocial
{
	// Contains the name of the module
	private $name = '';
	private $module = null;
	private $params = null;
	private $baseurl = null;

	public function __construct($module, $requireCSS = true, $loadLanguage = true)
	{
		parent::__construct();

		$this->module = $module;
		$this->name = $this->module->module;
		$this->params = new JRegistry($this->module->params);
		$this->baseurl = JURI::root(true);

		if (ES::isFromAdmin()) {
			$this->baseurl .= '/administrator';
		}

		if ($requireCSS) {
			$this->requireCSS();
		}

		if ($loadLanguage) {
			ES::language()->loadSite();
		}
	}

	/**
	 * Factory pattern to ensure that this object is not an instance
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function factory($module, $requireCSS = true)
	{
		$instance = new self($name, $requireCSS);

		return $instance;
	}

	/**
	 * Legacy method for 3rd party apps / modules. This module is now deprecated.
	 *
	 * @deprecated	2.0
	 */
	public static function loadComponentScripts()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			// Attach the scripts
			$scripts = ES::scripts();
			$scripts->attach();

			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Renders the javascript library of the component as there are cases where modules appear on a page
	 * without EasySocial
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderComponentScripts()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			// Attach the scripts
			$scripts = ES::scripts();
			$scripts->attach();

			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Allows module to attach script files on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addScript($file)
	{
		static $items = array();

		$key = md5($this->name . $file);

		if (!isset($items[$key])) {

			// Since a script is added on the site, we'll render component's dependencies
			$this->renderComponentScripts();

			$uri = $this->baseurl . '/modules/' . $this->name . '/scripts/' . $file;

			$scripts = ES::scripts();
			$scripts->addScript($uri);

			$items[$key] = true;
		}

		return $items[$key];
	}

	/**
	 * Determines if this is a mobile layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMobile()
	{
		$responsive = null;

		if (is_null($responsive)) {
			$responsive = ES::responsive()->isMobile();
		}

		return $responsive;
	}

	/**
	 * Determines if this is a mobile layout
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isTablet()
	{
		$responsive = null;

		if (is_null($responsive)) {
			$responsive = ES::responsive()->isTablet();
		}

		return $responsive;
	}

	/**
	 * This would ensure that we will render the module's stylesheet on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function requireCSS()
	{
		static $loaded = null;

		if (is_null($loaded)) {

			// Load site stylesheet since we will be sharing the css with the modules
			$location = ES::isFromAdmin() ? 'admin' : 'site';

			$theme = strtolower($this->config->get('theme.' . $location));
			$stylesheet = ES::stylesheet($location, $theme);
			$stylesheet->attach();

			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * EasyBlog Helper to render EasyBlog library if it exists
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function requireEasyBlog()
	{
		static $easyblog = null;

		if (is_null($easyblog)) {
			$easyblog = false;

			$file = JPATH_ROOT . '/administrator/components/com_easyblog/includes/easyblog.php';
			$exists = JFile::exists($file);

			if ($exists) {
				require_once($file);
				$easyblog = true;
			}
		}

		return $easyblog;
	}

	/**
	 * Render EasyDiscuss helper
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function requireEasyDiscuss()
	{
		static $easydiscuss = null;

		if (is_null($easydiscuss)) {
			$easydiscuss = false;

			$file = JPATH_ROOT . '/administrator/components/com_easydiscuss/includes/easydiscuss.php';
			$exists = JFile::exists($file);

			if ($exists) {
				require_once($file);
				$easydiscuss = true;
			}
		}

		return $easydiscuss;
	}

	/**
	 * Resolves a namespace path
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function resolve($name)
	{
		$path = JPATH_ROOT . '/modules/' . $name;

		return $path;
	}

	/**
	 * Retrieves the logout return url
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLogoutReturnUrl()
	{
		// Get the logout return value
		$menu = $this->config->get('general.site.logout');

		$url = ESR::getMenuLink($menu);
		$url = base64_encode($url);

		return $url;
	}

	/**
	 * Retrieves the signin redirection url
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLoginReturnUrl($menuId = '')
	{
		// Get the router
		$router = $this->app->getRouter();
		$url = null;

		if (empty($menuId)) {
			$loginMenu = $this->config->get('general.site.login');
			$menuId = $this->params->get('return', $loginMenu);
		}

		$url = ESR::getMenuLink($menuId);
		$url = base64_encode($url);

		return $url;

		// // var_dump($menuId);exit;

		// // Default url
		// $url = JUri::getInstance()->toString();

		// if ($menuId) {

		// 	// Get the menu item
		// 	$menu = $this->app->getMenu()->getItem($menuId);

		// 	if ($menu) {
		// 		$url = 'index.php?Itemid=' . $menu->id;
		// 	} else {
		// 		$url = 'index.php';
		// 	}

		// 	// adding lang segment here will cause language filter plugin to failed when the plugin attemp to get the associated menu item.
		// 	//
		// 	// if (JLanguageMultilang::isEnabled() && isset($menu->language) && $menu->language !== '*') {
		// 	// 	$url .= '&lang=' . $menu->language;
		// 	// }
		// }

		// return base64_encode($url);
	}

	/**
	 * Retrieves the default profile id on the site
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getDefaultProfileId()
	{
		$model = ES::model('profiles');
		$profile = $model->getDefaultProfile();

		return $profile->id;
	}

	/**
	 * Includes the helper for the module
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getHelper()
	{
		static $helpers = array();

		$key = $this->name;

		if (!isset($helpers[$key])) {
			$path = $this->getPath() . '/helper.php';
			$exists = JFile::exists($path);
			$helpers[$key] = false;

			if (!$exists) {
				return $helpers[$key];
			}

			require_once($path);
			$name = str_ireplace('mod_easysocial_', '', $key);
			$name = ucfirst($name);

			$className = 'EasySocialMod' . $name . 'Helper';

			if (!class_exists($className)) {
				return $helpers[$key];
			}

			$helpers[$key] = new $className();
		}

		return $helpers[$key];
	}

	/**
	 * Retrieves the layout set in the module
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getLayout($default = 'default')
	{
		$layout = $this->params->get('layout', $default);

		$output = JModuleHelper::getLayoutPath($this->name, $layout);

		return $output;
	}

	/**
	 * Retrieves the path of the plugin
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPath($relative = false)
	{
		$path = '';

		if (!$relative) {
			$path .= JPATH_ROOT;
		}

		$path .= '/modules/' . $this->name;

		return $path;
	}

	/**
	 * Retrieves the class suffix for the module
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSuffix($default = '')
	{
		$suffix = '';

		if ($this->isMobile()) {
			$suffix .= 'is-mobile ';
		}
		if ($this->isTablet()) {
			$suffix .= 'is-tablet ';
		}

		$moduleSuffix = $this->params->get('suffix', '');

		if ($moduleSuffix) {
			$suffix .= ' ' . $moduleSuffix;
		}

		// Standard suffix
		$standardSuffix = $this->params->get('moduleclass_sfx', '');

		if ($standardSuffix) {
			$suffix .= ' ' . $standardSuffix;
		}

		return $suffix;
	}

	/**
	 * Get apps for a particular cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getClusterApps($cluster, $respectView = true)
	{
		static $items = array();

		$type = $cluster->getType();
		$key = $cluster->id . '.' . $type;

		if (!isset($items[$key])) {
			$model = ES::model('Apps');
			$method = 'get' . ucfirst($type) . 'Apps';

			$items[$key] = $model->$method($cluster->id, $respectView);
		}

		return $items[$key];
	}

	/**
	 * Determines if secure url should be used
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function useSecureUrl()
	{
		$secure = $this->params->get('use_secure_url', false) ? 1 : 0;

		return $secure;
	}

	/**
	 * Load helpers for the module
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function html()
	{
		$theme = ES::themes();
		$args = func_get_args();

		$output = call_user_func_array(array($theme, 'html'), $args);

		return $output;
	}

	/**
	 * Renders the widget items
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function render()
	{
		$theme = ES::themes();

		$args = func_get_args();

		// Get the type of the widget from the first parameter.
		$type = array_shift($args);
		$method = 'render' . ucfirst($type);

		if (!method_exists($theme, $method)) {
			return;
		}

		return call_user_func_array(array($theme, $method), $args);
	}

	/**
	 * Render an output from the maint theme file
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function output()
	{
		$theme = ES::themes();
		$args = func_get_args();

		return call_user_func_array(array($theme, 'output'), $args);
	}
}
