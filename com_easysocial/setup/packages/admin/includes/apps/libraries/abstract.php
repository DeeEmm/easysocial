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

class SocialAppsAbstract extends EasySocial
{
	// Current logged in user
	public $my = null;

	// The apps element name
	public $element = null;

	// The apps' group
	public $group = null;

	public $info = null;

	// Stores system folder paths
	public $paths = array();

	// The theme library
	public $theme = null;

	// Stores a list of models
	public $models = array();

	// Stores a list of already initiated views.
	protected $views = array();

	public function __construct($options = array())
	{
		parent::__construct();

		// Initialize the options
		if (isset($options['app'])) {
			$this->app = $options['app'];
			$this->group = $this->app->group;
			$this->element = $this->app->element;
		}

		if (isset($options['viewName'])) {
			$this->viewName	= $options['viewName'];
		}

		if (isset($options['group'])) {
			$this->group = $options['group'];
		}

		if (isset($options['element'])) {
			$this->element = $options['element'];
		}

		// Initialize the theme object for the current app.
		$this->theme = ES::themes();

		// Make the app table accessible by theme files
		if (isset($options['app'])) {
			$this->set('app', $this->app);
		}

		// Set the input request object
		$this->input = ES::request();
		$this->info = ES::info();
		$this->page = ES::document();

		// Initialize the paths
		$path = SOCIAL_APPS . '/' . $this->group . '/' . $this->element;

		$this->paths['models'] = $path . '/models';
		$this->paths['tables'] = $path . '/tables';
		$this->paths['views'] = $path . '/views';
		$this->paths['config'] = $path . '/config';
		$this->paths['hooks'] = $path . '/hooks';
		$this->paths['streams'] = $path . '/streams';
		$this->paths['tables'] = $path . '/tables';

		if ($this->doc->getType() == 'ajax') {
			$this->ajax = ES::ajax();
		}
	}

	/**
	 * Responsible to help apps to output theme files.
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string 	The namespace of the file to look for
	 * @return 	string 	The contents of the fetched file
	 */
	public function display($file)
	{
		// Since this is a field item, we always want to prefix with the standard POSIX format.
		$namespace = 'themes:/apps/' . $this->group . '/' . $this->element . '/' . $file;

		// If there is a "protocol" such as site:/ or admin:/, we should just use it's own namespace
		if (stristr($file, ':/') !== false) {
			$namespace = $file;
		}

		return $this->theme->output($namespace);
	}

	/**
	 * Retrieves the app table row
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getApp()
	{
		static $apps = [];

		$key = $this->group . $this->element;

		if (!isset($apps[$key])) {

			$app = ES::table('App');
			$app->load([
				'element' => $this->element,
				'group' => $this->group,
				'type' => 'apps'
			]);

			$apps[$key] = $app;
		}

		return $apps[$key];
	}


	/**
	 * Retrieves the params for this app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getParams()
	{
		static $params = array();

		$index = $this->element . $this->group;

		if (isset($params[$index])) {
			return $params[$index];
		}

		$app = $this->getApp();
		$params[$index] = $app->getParams();

		return $params[$index];
	}


	/**
	 * Deprecated. Use SocialAppItem::table instead
	 *
	 * @deprecated	2.0
	 * @access	public
	 */
	public function getTable($name, $prefix = '')
	{
		return $this->table($name, $prefix);
	}

	/**
	 * Retrieves the table object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function table($name, $prefix = '')
	{
		$path = $this->paths['tables'];

		JTable::addIncludePath($path);

		$prefix	= empty($prefix) ? ucfirst($this->element) . 'Table' : $prefix;

		$table = JTable::getInstance($name, $prefix);

		return $table;
	}

	/**
	 * Deprecated. Use $this->model() instead.
	 *
	 * @deprecated	2.0
	 * @access	public
	 */
	public function getModel($name)
	{
		return $this->model($name);
	}

	/**
	 * Helper function to assist child classes to retrieve a model object.
	 *
	 * @since 	2.0
	 * @access	public
	 * @param 	string 	The name of the model.
	 **/
	public function model($name)
	{
		// If it already exists on the cache, return that model
		if (isset($this->models[$name])) {
			return $this->models[$name];
		}

		$className = $name . 'Model';

		// If it doesn't exist, load the model
		if (!class_exists($className)) {
			jimport('joomla.application.component.model');

			JLoader::import(strtolower($name), $this->paths['models']);
		}

		// If the class still doesn't exist, let's just throw an error here.
		if (!class_exists($className)) {
			ES::language()->loadAdmin();

			throw ES::exception(JText::sprintf('COM_EASYSOCIAL_MODEL_NOT_FOUND', $className), 500);
		}

		$model = new $className($name);
		$this->models[$name] = $model;

		return $this->models[$name];
	}

	/**
	 * Allows overriden objects to redirect the current request only when in html mode.
	 *
	 * @access	public
	 * @param	string	$uri 	The raw uri string.
	 * @param	boolean	$route	Whether or not the uri should be routed
	 */
	public function redirect($uri)
	{
		return ES::redirect($uri);
	}


	/**
	 * Sets a variable to the theme object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function set($key, $var)
	{
		$this->theme->set($key, $var);
	}

	/**
	 * Format the object name for the REST Api
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function formatRestObjectName($type, $id)
	{
		// E.g. {user:123}
		$name = '{' . $type . ':' . $id . '}';

		return $name;
	}
}
