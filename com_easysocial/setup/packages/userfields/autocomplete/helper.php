<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');


ES::import( 'admin:/includes/fields/dependencies' );

jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class SocialFieldsUserAvatarHelper
{
	public static function getStoragePath( $inputName , $overwrite = true )
	{
		// Create a temporary folder for this session.
		$session	= JFactory::getSession();
		$uid		= md5( $session->getId() . $inputName );
		$path		= SOCIAL_MEDIA . '/tmp/' . $uid . '_avatar';

		if( $overwrite )
		{
			// If the folder exists, delete them first.
			if( JFolder::exists( $path ) )
			{
				JFolder::delete( $path );
			}

			// Create folder if necessary.
			ES::makeFolder( $path );
		}

		return $path;
	}

	public static function getStorageURI( $inputName )
	{
		$session	= JFactory::getSession();
		$uid		= md5( $session->getId() . $inputName );

		$uri		= rtrim( JURI::root() , '/' ) . '/media/com_easysocial/tmp/' . $uid . '_avatar';

		return $uri;
	}
}
