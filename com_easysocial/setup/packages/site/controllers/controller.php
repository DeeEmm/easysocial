<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/controller');

class EasySocialController extends EasySocialControllerMain
{
	protected $app = null;
	protected $input = null;
	protected $my = null;
	protected $config = null;
	protected $jconfig = null;
	protected $actionlog = null;
	protected $doc = null;

	// This will notify the parent class that this is for the back end.
	protected $location = 'frontend';

	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->my = ES::user();
		$this->config = ES::config();
		$this->jconfig = ES::jconfig();
		$this->doc = JFactory::getDocument();

		if ($this->doc->getType() == 'ajax') {
			$this->ajax = ES::ajax();
		}

		parent::__construct();

		$this->input = ES::request();
	}

	/**
	 * Override's parent's execute behavior
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function execute($task)
	{
		$current = $this->input->get('controller', '', 'word');

		// Check and see if this view should be displayed
		// If private mode is enabled and user isn't logged in.
		if ($this->config->get('general.site.lockdown.enabled') && $this->my->guest) {

			if ($this->lockdown($task) && !empty($current)) {

				$url = ESR::login(array(), false);
				return ES::redirect($url);
			}
		}

		parent::execute($task);
	}

	/**
	 * Determines if the current view should be locked down.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function lockdown($task = '')
	{
		// Default, all views are locked down.
		$state = true;

		if (method_exists($this, 'isLockDown')) {
			$state 	= $this->isLockDown($task);
		}

		return $state;
	}

	/**
	 * Override parent controller's display behavior.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($params = array() , $urlparams = false)
	{
		// if we detect there is a format passed is what we recognise, then we need to reset on JDoc so that
		// the JDoc type will not be messed up due to other system plugins.
		$format = $this->input->get('format', '', 'cmd');
		if ($format && in_array($format, array('embed', 'oembed'))) {
			$this->doc->setType($format);
		}

		$type = $this->doc->getType();
		$name = $this->input->get('view', 'dashboard', 'cmd');
		$view = $this->getView($name, $type, '');

		// @task: Once we have the view, set the appropriate layout.
		$layout = $this->input->get('layout', 'default', 'cmd');
		$view->setLayout($layout);

		// Check and see if this view should be displayed
		// If private mode is enabled and user isn't logged in.
		if ($this->config->get('general.site.lockdown.enabled') && $this->my->guest && $view->lockdown()) {

			// Set the url callback
			$currentUrl = ESR::current();
			ES::setCallback($currentUrl);

			$url = ESR::login(array(), false);

			return ES::redirect($url);
		}

		// For ajax methods, we just load the view methods.
		if ($type == 'ajax') {

			if (!method_exists($view, $layout)) {
				$view->display();
			} else {
				$params = $this->input->get('params', '', 'default');
				$params = json_decode($params);

				call_user_func_array(array($view, $layout), $params);
			}
		} else {

			// Disable inline scripts in templates.
			SocialThemes::$_inlineScript = false;

			if ($layout != 'default') {

				if (!method_exists($view, $layout)) {
					$view->display();
				} else {
					call_user_func_array(array($view, $layout), $params);
				}
			} else {
				$view->display();
			}

			// Restore inline script in templates.
			SocialThemes::$_inlineScript = true;
		}
	}
}
