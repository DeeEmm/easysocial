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

class EasySocialView extends JViewLegacy
{
	/**
	 * Stores the view's message queue.
	 * @var	stdClass
	 */
	protected $message = null;

	/**
	 * Stores the theme object.
	 * @var	SocialThemes
	 */
	protected $theme = null;

	/**
	 * Determines if there's any errors on this view.
	 * @var	boolean
	 */
	protected $errors = false;

	protected $breadcrumbs	= null;
	protected $app = null;

	public function __construct($config = array())
	{
		// Load Joomla's app
		$this->app = JFactory::getApplication();
		$this->doc = JFactory::getDocument();
		$this->theme = ES::themes();
		$this->config = ES::config();
		$this->jconfig = ES::jconfig();
		$this->my = ES::user();
		$this->info = ES::info();
		$this->page = ES::document();
		$this->string = ES::string();
		$this->meta = ES::meta();
		$this->json = ES::json();

		// If the request is an ajax request, we should prepare the ajax library for the caller
		if ($this->doc->getType() == 'ajax') {
			$this->ajax = ES::ajax();
		}

		// @task: Initialize the necessary javascript header's that will be used throughout the site
		parent::__construct($config);

		// Input needs to be overridden later incase the parent view is already assigning the input variable
		$this->input = ES::request();
	}

	/**
	 * Allows caller to invoke an exception
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function exception($message, $type = ES_ERROR, $previous = null)
	{
		$message = JText::_($message);

		if ($this->doc->getType() == 'ajax') {
			return $this->ajax->reject($message);
		}

		// Handle standard error messages here
		throw ES::exception($message, $type, $previous);

		return;
	}

	/**
	 * Allows caller to set some message to the info queue
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setMessage($message, $messageType = SOCIAL_MSG_SUCCESS)
	{
		// Accepts SocialResponse instance
		if ($message instanceof SocialResponse) {
			$messageType = $message->type;
			$message = $message->message;
		}

		$obj = new stdClass();
		$obj->message = JText::_($message);
		$obj->type = $messageType;

		if ($obj->type == ES_ERROR) {
			$this->errors = true;
		}

		$format = $this->input->get('format', 'html', 'cmd');

		if ($format == 'ajax') {
			$this->ajax->notify($obj->message, $obj->type);
		}

		$this->message = $obj;

		return true;
	}

	/**
	 * Returns the helper for the view
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getHelper($name)
	{
		$helper = ES::viewHelper($this->getName(), $name);

		return $helper;
	}

	/**
	 * Returns the message queue.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMessage()
	{
		if (!$this->message) {
			return false;
		}

		$message = $this->message;

		// After getting the message, we need to empty this to prevent duplicate messages
		$this->message = null;

		return $message;
	}

	/**
	 * Helper to get any "return" data from the request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getReturnUrl($default = '')
	{
		$returnUrl = $this->input->get('return', '', 'default');

		if (!$returnUrl) {
			return $default;
		}

		$returnUrl = base64_decode($returnUrl);

		// Ensure that &amp; is replaced with &
		$returnUrl = str_ireplace('&amp;', '&', $returnUrl);

		return $returnUrl;
	}

	/**
	 * Calls a specific method from the view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function call($method)
	{
		if (!method_exists($this, $method)) {
			return false;
		}

		// Get a list of arguments since we do not know
		// how many arguments are passed in here.
		$args = func_get_args();

		// Remove the first argument since the first argument is the method.
		array_shift($args);

		// Before calling the view's method, we try to detect if there are any error messages in the queue
		if ($this->hasErrors() && $this->doc->getType() == 'ajax') {
			return $this->ajax->reject($this->getMessage());
		}

		// Set the info message here if needed
		if ($this->doc->getType() == 'html') {
			$message = $this->getMessage();

			$this->info->set($message);
		}

		return call_user_func_array(array($this, $method), $args);
	}

	/**
	 * Allows overriden objects to redirect the current request only when in html mode.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function redirect($uri, $message = '', $messageType = '')
	{
		if (!is_bool($message) && $message) {
			$this->info->set(null, $message, $messageType);
		}

		ES::redirect($uri);
	}

	/**
	 * Redirects to a specific view
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function redirectToView($view, $layout = '')
	{
		$link = 'index.php?option=com_easysocial&view=' . $view;

		if ($layout) {
			$link .= '&layout=' . $layout;
		}

		return $this->redirect($link);
	}

	/**
	 * Determines if there's any errors on this view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasErrors()
	{
		return $this->errors;
	}

	/**
	 * Main method to output the contents
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		$format = $this->input->get('format', 'html', 'word');

		if ($format == 'embed') {
			header('Content-type: text/html; charset=utf-8');
			echo $this->theme->output($tpl);
			exit;
		}
		if ($format == 'oembed') {
			header('Content-type: application/json; UTF-8');
			echo $this->theme->toJSON();
			exit;
		}

		if ($format == 'json') {
			header('Content-type: application/json; UTF-8');
			echo $this->theme->toJSON();
			exit;
		}

		/**
		 * For 'raw' types of output, we need to exit it after that
		 * as we do not want to process anything apart from our codes only.
		 */
		if ($format == 'raw') {
			echo $this->theme->output($tpl);
			return;
		}

		if ($format == 'ajax') {
			return $this->theme->output($tpl);
		}

		if ($format == 'html') {
			echo $this->theme->output($tpl);
			return;
		}

		return parent::display($tpl);
	}

	public function set($key, $value = null)
	{
		return $this->theme->set($key, $value);
	}

	/**
	 * Generic redirection method
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function setRedirection($url)
	{
		$this->info->set($this->getMessage());

		return $this->redirect($url);
	}

	/**
	 * Method to get the view name
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name)) {
			if (!preg_match('/View(.*)/i', get_class($this), $matches)) {
				throw ES::exception("EasySocialView::getName() : Can't get or parse class name.", ES_ERROR);
			}

			$name = strtolower($matches[1]);
		}

		return $name;
	}
}
