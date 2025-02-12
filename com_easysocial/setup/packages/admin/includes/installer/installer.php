<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialInstaller extends EasySocial
{
	protected $helper = null;

	public function __construct()
	{
		parent::__construct();

		$version = ES::version();
		$name = $version->getCodeName();
		$name = strtolower($name);

		require_once(__DIR__ . '/helpers/' . $name . '.php');

		$className = 'SocialInstallerHelper' . ucfirst($name);

		$this->helper = new $className();
	}

	public static function factory()
	{
		$obj = new self();

		return $obj;
	}

	/**
	 * Loads the target prior to installation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	$path	The path to the folder.
	 */
	public function load($path)
	{
		return $this->helper->load($path);
	}

	/**
	 * Initiates the installation process.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install()
	{
		return $this->helper->install();
	}

	/**
	 * Initiates the discover process.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function discover()
	{
		return $this->helper->discover();
	}

	/**
	 * Proxy method to load methods from helper.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __call( $method , $args )
	{
		return call_user_func_array( array( $this->helper , $method ) , $args );
	}

	/**
	 * Parses an uploaded file from a temporary path
	 *
	 * @access public
	 */
	public function upload($source, $destination)
	{
		return $this->helper->upload($source, $destination);
	}

	public function extract($destination)
	{
		return $this->helper->extract($destination);
	}

	public function cleanup( $path )
	{
		return $this->helper->cleanup( $path );
	}
}

interface SocialAppInstaller
{
	public function install();
	public function uninstall();
	public function success();
	public function error();
}
