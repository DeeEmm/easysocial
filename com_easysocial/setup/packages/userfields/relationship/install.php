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

class SocialFieldsRelationshipstatusInstaller implements SocialAppInstaller
{
	/*
	 * During the initial installation, EasySocial will
	 * automatically call up this function.
	 *
	 * @return	string	Return a string so that EasySocial
	 */
	public function install()
	{
		/*
		 * Run something here if necessary
		 */
		 return true;
	}

	/**
	 * This is executed during the uninstallation.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function uninstall()
	{
		/*
		 * Run something here if necessary
		 */
		 return true;
	}

	/**
	 * This is executed when user upgrades the application.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function upgrade()
	{
		/*
		 * Run something here if necessary
		 */
		 return true;
	}

	/**
	 * This is executed when there is an error during the installation.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function error()
	{
		/*
		 * Run something here if necessary
		 */
		 return true;
	}

	/**
	 * Upon successfull installation
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function success()
	{
		/*
		 * Run something here if necessary
		 */
		 return true;
	}
}
