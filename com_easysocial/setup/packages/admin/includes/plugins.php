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

require_once(__DIR__ . '/easysocial.php');

class EasySocialPlugins extends JPlugin
{
	public $config = null;
	public $jConfig = null;
	public $app = null;
	public $input = null;
	public $my = null;
	public $doc = null;

	private $templateVariables = array();

	protected $group = null;
	protected $element = null;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app = JFactory::getApplication();
		$this->input = ES::request();
		$this->jConfig = ES::jConfig();
		$this->config = ES::config();

		if ($this->input->get('controller') != 'installation') {
			$this->my = ES::user();
		}
	}

	/**
	 * Attaches a script to the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function addScript($file)
	{
		$baseurl = JURI::root(true);
		$url = $baseurl . $this->getPath(true) . '/scripts/' . $file;

		ES::scripts()->addScript($url);
	}

	/**
	 * Allows child classes to assign variables to the template
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function assign($key, $value)
	{
		$this->templateVariables[$key] = $value;
	}

	/**
	 * Retrieve params from a particular app
	 *
	 * @since	2.0.3
	 * @access	public
	 */
	public function getAppParams($element, $group, $type = SOCIAL_TYPE_APPS)
	{
		static $params = array();

		$key = $element . $group . $type;

		if (!isset($params[$key])) {

			$options = array('type' => $type, 'group' => $group, 'element' => $element);

			$app = ES::table('App');
			$app->load($options);

			$params[$key] = false;

			if ($app->id) {
				$params[$key] = $app->getParams();
			}
		}

		return $params[$key];
	}

	/**
	 * Retrieves the current Joomla template
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function getCurrentJoomlaTemplate()
	{
		static $template = null;

		if (is_null($template)) {
			$model = ES::model('Themes');
			$template = $model->getCurrentTemplate();
		}

		return $template;
	}

	/**
	 * Retrieves the override path of the plugin
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function getOverridePath($relative = false)
	{
		$path = '';

		if (!$relative) {
			$path .= JPATH_ROOT;
		}

		$path .= '/templates/' . $this->getCurrentJoomlaTemplate() . '/html/plg_' . $this->group . '_' . $this->element;

		return $path;
	}

	/**
	 * Retrieves the plugin params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPluginParams()
	{
		static $params = false;

		if (!$params) {
			$plugin	= JPluginHelper::getPlugin('content', 'easysocial');
			$params = ES::registry($plugin->params);
		}

		return $params;
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

		$path .= '/plugins/' . $this->group . '/' . $this->element;

		return $path;
	}

	/**
	 * Determines if the plugin has template override
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function hasOverride($namespace)
	{
		$file = $this->getOverridePath() . '/' . $namespace . '.php';

		if (JFile::exists($file)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this is on a mobile view
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMobile()
	{
		$isMobile = ES::responsive()->isMobile();

		return $isMobile;
	}

	/**
	 * Determines if this is on a tablet view
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isTablet()
	{
		$isTablet = ES::responsive()->isTablet();

		return $isTablet;
	}

	/**
	 * Determines if this is on a tablet view
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isMacintosh()
	{
		$isMacintosh = ES::responsive()->isMacintosh();

		return $isMacintosh;
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
	 * Retrieves the output of the template file for a plugin
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function output($namespace)
	{
		$hasOverride = $this->hasOverride($namespace);
		$file = $this->getPath() . '/themes/' . $namespace . '.php';

		if ($hasOverride) {
			$file = $this->getOverridePath() . '/' . $namespace . '.php';
		}

		extract($this->templateVariables);

		ob_start();
		include($file);
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
