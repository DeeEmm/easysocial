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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

abstract class SocialApplications
{
	private $theme		= null;
	public $data		= null;

	public function __construct()
	{
		$this->theme	= ES::get( 'Themes' );
	}

	public function install()
	{
	}

	public function uninstall()
	{
	}

	public function success()
	{
	}

	public function error()
	{
	}

	public function setParams( $params = null )
	{

	}

	public function getParams()
	{

	}

	public function setPosition( $position = null )
	{

	}

	public function getPosition()
	{
		return $this->position;
	}

	public function render()
	{
		return $this->html();
	}

	public function display( $theme = null )
	{
		if (is_null($theme))
		{
			$theme	= ES::get( 'Themes' );
		}

		return $this->theme->output( $theme );
	}


	public function setData( $name, $value )
	{
		$this->data[$name]	= $value;
	}

	public function getData()
	{
		return array();
	}

	public function getView( $viewPath, $key, $value )
	{
		$html	= '';

		if ( JFile::exists( $viewPath ))
		{
			// pass into the tempalte the matching variable name
			// and it's value set by each application.
			// SocialApplications::setData()
			$$key	= $value;

			ob_start();
			{
				include( $viewPath );
			}

			$html	= ob_get_clean();
		}

		return $html;
	}

}
