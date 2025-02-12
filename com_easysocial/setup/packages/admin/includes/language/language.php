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

class SocialLanguage
{
	protected $adapter = null;
	private $string = null;
	private $count = true;
	private $number = 0;
	private $arguments = array();

	public static function factory()
	{
		$args = func_get_args();

		$string = array_shift($args);

		$obj 	= new self($string, $args);

		return $obj;
	}

	public function __construct($string = '', $args = array())
	{
		if (!empty($string)) {
			$this->string = $string;
		}

		if (!empty($args)) {
			$this->arguments = ES::makeArray($args);
		}
	}

	public static function getInstance()
	{
		static $instance;

		if (empty($instance)) {
			$instance = new self;
		}

		return $instance;
	}

	public function load($type = 'com_easysocial', $path = null, $lang = null, $reload = false, $default = true)
	{
		static $languages = array();

		$index 	= md5($type . $path . $lang);

		if (!isset($languages[$index]) || $reload) {
			$language = JFactory::getLanguage();

			// // Load user's preferred language file.
			$state = $language->load($type, $path, $lang, $reload, $default);

			$languages[$index]	= true;
		}

		return $languages[$index];
	}

	/**
	 * Loads a language file for an application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadApp($group, $element, $lang = null, $reload = false, $default = true)
	{
		if (empty($group) || empty($element)) {
			return;
		}

		$namespace = 'plg_app_' . $group . '_' . $element;

		return $this->load($namespace, SOCIAL_JOOMLA_ADMIN, $lang, $reload, $default);
	}

	/**
	 * Loads a language file for custom fields
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadField($group, $element, $lang = null, $reload = false, $default = true)
	{
		if (empty($group) || empty($element)) {
			return;
		}

		$namespace = 'plg_fields_' . $group . '_' . $element;

		return $this->load($namespace, SOCIAL_JOOMLA_ADMIN, $lang, $reload, $default);
	}

	/**
	 * Shorthand function to load backend language
	 *
	 * @since  1.2
	 * @access public
	 * @return boolean    State of the language loading
	 */
	public function loadAdmin($lang = null, $reload = false, $default = true)
	{
		static $loaded = array();

		$key = $lang . $default;

		if (!isset($loaded[$key]) || $reload) {
			$this->load(SOCIAL_COMPONENT_NAME, SOCIAL_JOOMLA_ADMIN, $lang, $reload, $default);

			$loaded[$key] = true;
		}

		return $loaded[$key];
	}

	/**
	 * Shorthand function to load frontend language
	 *
	 * @since  1.2
	 * @access public
	 * @return boolean    State of the language loading
	 */
	public function loadSite($lang = null, $reload = false, $default = true)
	{
		static $loaded = array();

		$key = $lang . $default;

		if (!isset($loaded[$key]) || $reload) {
			$this->load(SOCIAL_COMPONENT_NAME, SOCIAL_JOOMLA, $lang, $reload, $default);

			$loaded[$key] = true;
		}

		return $loaded[$key];
	}

	/**
	 * Pluralizes a language string
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function pluralize($count, $useCount = false)
	{
		$this->count = (boolean) $useCount;
		$this->number = (int) $count;

	    if ($this->count) {
			$this->string .= '_COUNT';
		}

		// 0 and > 1
		if ($this->number !== 1) {
			$this->string .= '_PLURAL';
	        return $this;
		}

		$this->string .= '_SINGULAR';

		return $this;
	}

	public function genderize($user = null)
	{
		if (empty($user)) {
			$user = ES::user();
		}

		if (!$user instanceof SocialUser) {
			$user = ES::user($user);
		}

		$this->string .= $user->getGenderLang();

		return $this;
	}

	public function getString()
	{
		return $this->string;
	}

	public function __toString()
	{
	    if ($this->count) {
	        return JText::sprintf($this->string, $this->number);
		}

	    return JText::_($this->string);
	}
}
