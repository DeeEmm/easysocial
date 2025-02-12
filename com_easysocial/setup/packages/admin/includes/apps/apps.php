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

require_once(__DIR__ . '/libraries/abstract.php');
require_once(__DIR__ . '/libraries/controller.php');
require_once(__DIR__ . '/libraries/item.php');
require_once(__DIR__ . '/libraries/view.php');
require_once(__DIR__ . '/libraries/widget.php');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SocialApps
{
	/**
	 * Static variable for caching.
	 * @var	SocialApps
	 */
	private static $instance = null;

	/**
	 * Cached stored apps on this object for easy access
	 * @var	SocialApps
	 */
	private static $cachedApps = array();

	// Stores a list of widgets
	private static $widgets = array();

	// Store apps locally.
	private $apps = array();

	/**
	 * Cache a list of widget apps
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getWidgets($uid, $clusterId, $view, $group)
	{
		$key = $uid . $clusterId . $view . $group;

		if (!isset(self::$widgets[$key])) {

			self::$widgets[$key] = array();

			$options = array('uid' => $uid);

			if ($group == SOCIAL_TYPE_USER) {
				$options['key'] = $group;
			}

			$model = ES::model('Apps');
			$apps = $model->getWidgetApps($group, $options);

			// Set the initial path of the apps
			$folder = SOCIAL_APPS . '/' . $group;

			foreach ($apps as $app) {

				$file = $folder . '/' . $app->element . '/widgets/' . $view . '/view.html.php';
				$exists = JFile::exists($file);

				if (!$exists) {
					continue;
				}

				require_once($file);

				$className 	= ucfirst($app->element) . 'Widgets' . ucfirst($view);

				// Check if the class exists in this context.
				if (!class_exists($className)) {
					continue;
				}

				// Ensure that the object has access to this app
				if ($clusterId) {
					$cluster = ES::cluster($app->group, $clusterId);

					if (!$app->hasAccess($cluster->category_id)) {
						continue;
					}
				}

				// Pass in the app's config
				$options = array('app' => $app, 'viewName' => $view, 'group' => $app->group, 'element' => $app->element);
				$widget = new $className($options);

				$app->widgetObject = $widget;

				self::$widgets[$key][] = $app;
			}
		}

		return self::$widgets[$key];
	}

	/**
	 * Object initialisation for the class. Apps should be initialized using
	 * ES::getInstance( 'Apps' )
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Loads all app language files.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadAllLanguages()
	{
		// Get list of apps that should be loaded.
		$model = ES::model('Apps');

		// MUST FIX THIS TO NOT USE ANY LIMITS
		$options = array('state' => SOCIAL_STATE_PUBLISHED);
		$apps = $model->setLimit(10000)->getApps($options);

		if (!$apps) {
			return;
		}

		foreach ($apps as $app) {
			$app->loadLanguage();
		}
	}

	/**
	 * Load a list of applications.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function load($group, $inclusion = array(), $debug = false)
	{
		static $loaded = array();

		// Singleton pattern where we should only load necessary items.
		if (!isset($loaded[$group])) {

			// Get a list of applications that should be rendered for this app type.
			$model = ES::model('Apps');

			// Get a list of apps
			$options = array('type' => 'apps' , 'group' => $group , 'state' => SOCIAL_STATE_PUBLISHED);
			$apps = $model->getApps($options);

			if (!$apps) {
				$loaded[$group] = false;

				return $loaded[$group];
			}

			// Store them locally in the group
			$this->apps[$group] = $apps;

			foreach ($apps as $app) {
				$this->loadApp($app, $debug);

				$loaded[$group] = true;
			}
		}

		return $loaded[$group];
	}

	/**
	 * Responsible to render the widget on specific profile
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderWidgets($group, $view, $position, $args = array(), $wrapper = '')
	{
		$clusterId = null;

		// Determine if the uid is provided
		if (isset($args['uid'])) {
			$uid = $args['uid'];
			$clusterId = $args['uid'];
		}

		if (isset($args[0]) && $args[0] instanceof SocialCluster) {
			$clusterId = $args[0]->id;
		}

		// Uid not provided, we need to determine the appropriate uid
		if (!isset($args['uid'])) {
			$user = isset($args[0]) ? $args[0] : ES::user();
			$uid = $user->id;
		}

		// Initialize default contents
		$contents = '';

		// Go through each of these apps that are widgetable and see if there is a .widget file.
		$apps = $this->getWidgets($uid, $clusterId, $view, $group);

		// Arguments should be converted into a flat array to prevent named parameters in PHP8 from thinking that we are using named arguments
		$args = array_values($args);

		foreach ($apps as $app) {

			// Check if the method exists in this context.
			if (!method_exists($app->widgetObject, $position)) {
				continue;
			}

			ob_start();
			call_user_func_array([$app->widgetObject, $position], $args);
			$output = ob_get_contents();
			ob_end_clean();

			$contents .= $output;
		}

		// If nothing to display, just return false.
		if (empty($contents)) {
			return false;
		}

		return $contents;
	}

	/**
	 * Render's an app controller
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderController( $controllerName , $controllerTask , SocialTableApp $app )
	{
		// If application id is not provided, stop execution here.
		if (!$app->id) {
			return false;
		}

		// Construct the app's controller path.
		$controllerName = strtolower($controllerName);
		$file = SOCIAL_APPS . '/' . $app->group . '/' . $app->element . '/controllers/' . $controllerName . '.php';

		// Check if the controller file exists
		jimport('joomla.filesystem.file');

		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);

		// Construct the class name.
		$className 	= ucfirst($app->element) . 'Controller' . ucfirst($controllerName);

		// If despite loading the file, the class doesn't exist, don't proceed.
		if (!class_exists($className)) {
			return false;
		}

		// Instantiate the new class since we need to render it.
		$controller = new $className($app->group, $app->element);

		// Get the contents.
		$controller->$controllerTask();
	}

	/**
	 * Responsible to render an application's contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function renderView($viewType, $viewName, SocialTableApp $app, $args = array())
	{
		// If application id is not provided, stop execution here.
		if (!$app->id) {
			return JText::_('COM_EASYSOCIAL_APPS_INVALID_ID_PROVIDED');
		}

		// Construct the apps path.
		$path = SOCIAL_APPS . '/' . $app->group . '/' . $app->element;

		// Construct the relative file path based on the current view request.
		$file = 'views/' . $viewName . '/view.html.php';

		// Construct the absolute path now.
		$absolutePath = $path . '/' . $file;

		// Check if the view really exists.
		$exists = JFile::exists($absolutePath);

		if (!$exists) {
			return JText::sprintf('COM_EASYSOCIAL_APPS_VIEW_DOES_NOT_EXIST', $viewName);
		}

		require_once($absolutePath);

		// Construct the class name for this view.
		$className = ucfirst($app->element) . 'View' . ucfirst($viewName);

		if (!class_exists($className)) {
			return JText::sprintf('COM_EASYSOCIAL_APPS_CLASS_DOES_NOT_EXIST', $className);
		}

		// lets load backend language as well.
		ES::language()->loadAdmin();

		// Instantiate the new class since we need to render it.
		$options = array('app' => $app, 'group' => $app->group, 'element' => $app->element, 'viewName' => $viewName);
		$view = new $className($options);

		$clusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);

		if (in_array($app->group, $clusters)) {
			$id = array_values($args);
			$id = array_shift($id);

			$cluster = ES::cluster($app->group, $id);

			if (!$app->hasAccess($cluster->category_id)) {
				return;
			}
		}

		// Get the contents.
		ob_start();
		call_user_func_array(array($view, 'display'), array_values($args));
		$contents = ob_get_contents();
		ob_end_clean();

		// We need to wrap the app contents with our own wrapper.
		$namespace = 'site/apps/' . strtolower($viewType) . '/wrapper';

		// Title to display
		$title = $view->getTitle();

		$theme = ES::themes();

		// Bad implementation but it's much easier as we only rely on a single theme file
		if ($viewType == SOCIAL_APPS_VIEW_TYPE_CANVAS) {
			if ($app->group == SOCIAL_TYPE_USER) {
				$object = ES::user($args['uid']);
			} else {
				$object = ES::cluster($app->group, $args['uid']);
			}

			$theme->set('object', $object);
		}

		$theme->set('title', $title);
		$theme->set('app', $app);
		$theme->set('contents', $contents);
		$contents = $theme->output($namespace);

		return $contents;
	}

	/**
	 * Responsible to render an application's sidebar contents.
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function renderModuleSidebar($viewType, $viewName, SocialTableApp $app, $args = array())
	{
		// If application id is not provided, stop execution here.
		if (!$app->id) {
			return JText::_('COM_EASYSOCIAL_APPS_INVALID_ID_PROVIDED');
		}

		// Construct the apps path.
		$path = SOCIAL_APPS . '/' . $app->group . '/' . $app->element;

		// Construct the relative file path based on the current view request.
		$file = 'views/' . $viewName . '/view.html.php';

		// Construct the absolute path now.
		$absolutePath = $path . '/' . $file;

		// Check if the view really exists.
		$exists = JFile::exists($absolutePath);

		if (!$exists) {
			return JText::sprintf('COM_EASYSOCIAL_APPS_VIEW_DOES_NOT_EXIST', $viewName);
		}

		require_once($absolutePath);

		// Construct the class name for this view.
		$className = ucfirst($app->element) . 'View' . ucfirst($viewName);

		if (!class_exists($className)) {
			return JText::sprintf('COM_EASYSOCIAL_APPS_CLASS_DOES_NOT_EXIST', $className);
		}

		// lets load backend language as well.
		ES::language()->loadAdmin();

		// Instantiate the new class since we need to render it.
		$options = array('app' => $app, 'group' => $app->group, 'element' => $app->element, 'viewName' => $viewName);
		$view = new $className($options);

		$contents = '';
		if (method_exists($view, 'sidebar')) {
			// Get the contents.
			ob_start();
			call_user_func_array(array($view, 'sidebar'), $args);
			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}


	/**
	 * Allows caller to retrieve the app object
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getApp(SocialTableApp &$app)
	{
		// Load the app first as we need the app to be added to the dispatcher.
		$this->loadApp($app);

		$group = strtolower($app->group);
		$element = strtolower($app->element);

		// Check if the app exists
		if (!isset(self::$cachedApps[$group][$element])) {
			return false;
		}

		// Once the app is loaded, return the data
		$app = self::$cachedApps[$group][$element];

		return $app;
	}

	/**
	 * Responsible to attach the application into the SocialDispatcher object.
	 * In short, it does the requiring of files here.
	 *
	 * @since	1.0
	 * @access	private
	 */
	private function loadApp(SocialTableApp &$app, $debug = false)
	{
		static $items = array();

		// Application type and element should always be in lowercase.
		$group = strtolower($app->group);
		$element = strtolower($app->element);

		$index = $group . $element;

		if (!isset($items[$index])) {

			// Get dispatcher object.
			$dispatcher = ES::dispatcher();

			// Application trigger file paths.
			$file = SOCIAL_APPS . '/' . $group . '/' . $element . '/' . $element . '.php';
			$exists	= JFile::exists($file);

			// If file doesn't exist, skip the entire block.
			if (!$exists) {
				$items[$index] = false;
				return $items[$index];
			}

			// Assuming that the file exists here (It should)
			require_once($file);

			// Construct the class name
			$className = $this->getClassName($group, $element);

			// If the class doesn't exist in this context,
			// the application might be using a different class. Ignore this.
			if (!class_exists($className)) {
				$items[$index] = false;
				return $items[$index];
			}

			// Pass in the initialization options for the app
			$options = array('element' => $element, 'group' => $group);

			$obj = new $className($options);

			// Cache the app
			self::$cachedApps[$group][$element]	= $obj;

			// Attach the application into the observer list.
			$dispatcher->attach($group, $element, $obj);

			// Add a state for this because we know it has already been loaded.
			$items[$index] = true;
		}

		return $items[$index];
	}

	/**
	 * Generates the class name for an app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getClassName($group, $element)
	{
		$name = 'Social' . $group . 'App' . $element;

		return $name;
	}

	public function getCallable($namespace)
	{
		$path = '';

		$class = null;

		$className = '';

		$parts = explode( '/', $namespace );

		$location = array_shift( $parts );

		$method = array_pop( $parts );

		if( $location == 'site' || $location == 'admin' )
		{
			list( $type, $file ) = $parts;

			$path = $location == 'admin' ? SOCIAL_ADMIN : SOCIAL_SITE;

			$path .=  '/' . $type . '/' . $file;

			switch( $type )
			{
				case 'controllers':
				case 'models':
					$path .= '.php';
				break;
				case 'views':
					$path .= '/view.html.php';
				break;
			}

			$className = 'EasySocial' . ucfirst( rtrim( $type, 's' ) ) . ucfirst( $file );
		}

		if( $location == 'apps' )
		{
			list( $group, $element, $type, $file ) = $parts;

			$path = SOCIAL_APPS . '/' . $group . '/' . $element . '/' . $type . '/';

			switch( $type )
			{
				case 'controllers':
				case 'models':
					$path .= $file . '.php';
				break;
				case 'views':
					$path .=  'view.html.php';
			}

			$className = ucfirst( $element ) . ucfirst( trim( $type, 's' ) ) . ucfirst( $file );
		}

		if( $location == 'fields' )
		{
			list( $group, $element ) = $parts;

			$path = SOCIAL_FIELDS . '/' . $group . '/' . $element . '/' . $element . '.php';

			$className = 'SocialFields' . ucfirst( $group ) . ucfirst( $element );
		}

		if( !JFile::exists( $path ) )
		{
			return false;
		}

		include_once( $path );

		if( !class_exists( $className ) )
		{
			return false;
		}

		if( $location == 'admin' || $location == 'site' )
		{
			$class = new $className();
		}

		if( $location == 'apps' )
		{
			$class = new $className( $parts[0], $parts[1] );
		}

		if( $location == 'fields' )
		{
			$config = array( 'group' => $parts[0], 'element' => $parts[1] );

			$class = new $className( $config );
		}

		$callable = array( $class, $method );

		if (!is_callable($callable)) {
			return false;
		}


		return $callable;
	}

	/**
	 * Determines if the app should appear on the app listings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasActivityLog( SocialTableApp $table )
	{
		$file 	= SOCIAL_APPS . '/' . $table->group . '/' . $table->element . '/' . $table->element . '.php';

		jimport( 'joomla.filesystem.file' );

		if( !JFile::exists( $file ) )
		{
			return true;
		}

		require_once( $file );

		$appClass 	= 'Social' . ucfirst( $table->group ) . 'App' . ucfirst( $table->element );

		if( !class_exists( $appClass ) )
		{
			return true;
		}

		$app 			= new $appClass();
		$app->element	= $table->element;
		$app->group 	= $table->group;

		// Always return true unless explicitly disabled
		if( !method_exists( $app , 'hasActivityLog' ) )
		{
			return true;
		}

		$appear 	= $app->hasActivityLog();


		return $appear;
	}

	/**
	 * Determines if the app should appear on the app listings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasAppListing(SocialTableApp $table, $view, $uid = '' , $type = '')
	{
		$file = SOCIAL_APPS . '/' . $table->group . '/' . $table->element . '/' . $table->element . '.php';

		jimport('joomla.filesystem.file');

		if (!JFile::exists($file)) {
			return true;
		}

		require_once($file);

		$appClass = 'Social' . ucfirst($table->group) . 'App' . ucfirst($table->element);

		if (!class_exists($appClass)) {
			return true;
		}

		$app = new $appClass();
		$app->element = $table->element;
		$app->group = $table->group;

		// Properties based
		if (isset($app->appListing)) {
			return $app->appListing;
		}

		if (!method_exists($app, 'appListing')) {
			return true;
		}

		$display = $app->appListing($view, $uid, $type);
		return $display;
	}
}
