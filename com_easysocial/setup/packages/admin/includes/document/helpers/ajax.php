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

/**
 * HTML initialization here.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialDocumentAjax
{
	static $loaded 	= false;
	/**
	 * This loads a list of javascripts that are dependent throughout the whole component.
	 *
	 * @access	public
	 * @param	null
	 */
	public static function init()
	{
		// Anything that needs to be done on an ajax load should be done here. Triggers maybe?
	}

	/**
	 * method overriding.
	 *
	 * @access	public
	 * @param	null
	 */
	public function script()
	{
		// nothing to do for the moment.
	}
}
