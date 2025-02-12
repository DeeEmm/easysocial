<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

abstract class SocialSharingVendor
{
	public $name = '';
	public $base = '';
	public $params = array();
	public $map = array(
		'url'		=> 'url',
		'title'		=> 'title',
		'summary'	=> 'summary'
	);

	public $popup = array(
		'menubar'		=> 0,
		'resizble'		=> 0,
		'scrollbars'	=> 0,
		'width'			=> 660,
		'height'		=> 320
	);

	public $link = '';
	public $isFirst = null;
	public $token = '&';
	public $defaultTemplate = 'site/sharing/vendor';

	public function __construct($name, $options = array())
	{
		$this->name = $name;

		$this->setParams($options);
	}

	/**
	 * Generates the html codes for each vendor links
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getHTML()
	{
		$theme = ES::themes();

		$name = $this->name;
		$link = $this->getLink();
		$icon = $this->getIcon();
		$title = $this->getTitle();
		$popup = $this->getPopup();


		$theme->set('name', $name);
		$theme->set('link', $link);
		$theme->set('icon', $icon);
		$theme->set('title', $title);
		$theme->set('popup', $popup);

		$namespace = $this->getThemeFile();

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Returns the default vendor theme file
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getThemeFile()
	{
		return $this->defaultTemplate;
	}

	/**
	 * Retrieves the icon for the vendor
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getIcon()
	{
		return '<i class="icon-es-24 icon-es-' . $this->name . '"></i>';
	}

	public function getTitle()
	{
		return JText::_('COM_EASYSOCIAL_SHARING_' . ESJString::strtoupper( $this->name ) );
	}

	/**
	 * Generates the link to the social site
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getLink()
	{
		if ($this->link) {
			return $this->link;
		}

		$this->link = $this->base;

		foreach ($this->map as $key => $paramkey) {

			// Get the value of the mapping key
			$value = $this->getParam($key);

			if ($value !== false) {
				$this->addParam($paramkey, $value);
			}
		}

		return $this->link;
	}

	public function getPopup()
	{
		$optionString = array();

		foreach( $this->popup as $key => $value )
		{
			$optionString[] = $key . '=' . $value;
		}

		return implode( ',', $optionString );
	}

	/**
	 * Set the params
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setParams($params = array(), $force = false)
	{
		foreach ($params as $key => $value) {

			if (array_key_exists($key, $this->map) || $force) {
				$this->params[$this->map[$key]] = $value;
			}
		}
	}

	public function addParam( $key, $value )
	{
		$token = $this->token;

		if( $this->isFirst === null )
		{
			$this->checkFirst();
		}

		if( $this->isFirst === true )
		{
			$token = '?';
			$this->isFirst = false;
		}

		$this->link .= $token . $key . '=' . $value;
	}

	/**
	 * Retrieves the param value
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getParam($key)
	{
		$method = 'getParam' . ucfirst($key);

		if (method_exists($this, $method)) {
			return $this->$method();
		}

		if (!$this->map[$key] || !$this->params[$this->map[$key]]) {
			return false;
		}

		return $this->params[$this->map[$key]];
	}

	public function getParamUrl()
	{
		if (!$this->map['url'] || !$this->params[$this->map['url']]) {
			return false;
		}

		return $this->params[$this->map['url']];
	}

	public function getParamTitle()
	{
		if( empty( $this->map['title'] ) || empty( $this->params[$this->map['title']] ) )
		{
			return false;
		}

		return $this->params[$this->map['title']];
	}

	public function getParamSummary()
	{
		if( empty( $this->map['summary'] ) || empty( $this->params[$this->map['summary']] ) )
		{
			return false;
		}

		return $this->params[$this->map['summary']];
	}

	public function checkFirst()
	{
		$this->isFirst = ( ESJString::strpos( $this->link, '?' ) ) === false ? true : false;
	}
}
