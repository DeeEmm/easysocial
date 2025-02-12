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

class SocialSeo
{
	public function init()
	{
		return $this;
	}

	public function debug(){ }

	public function setTitle( $default )
	{

		// 1. Use the default message first.
		$title	= $default;

		// 2. Test if there's any custom title set in SEO section
		if( true )
		{

		}

		// 3. Test if there's any title set in the Joomla menu item
		if( true )
		{
			// If registered, just redefine the $message

			// If it's not registered, create a new record so that the admin can customize this.
		}

		// option=com_easysocial&view=conversations&
		$document		= JFactory::getDocument();
		$document->setTitle( $title );
	}

	private function getOption()
	{
		$option = ES::request()->get('option', 'com_easysocial', 'string');

		return $option;
	}

	private function getView()
	{
		$view = ES::request()->get('view' , '', 'string');

		return $view;
	}

	private function getLayout()
	{
		$layout = ES::request()->get('layout' , '', 'string');
	}
	/**
	 * Registers all url that hasn't been registered yet.
	 * This allows admin to customize the title of their pages easily.
	 */
	private function register()
	{

	}
}
