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

// Include main view here.
ES::import( 'site:/views/views' );

class EasySocialViewUnity extends EasySocialSiteView
{
	/**
	 * Responsible to display the unity view
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function display( $tpl = null )
	{
		echo JText::_( 'The unity view has been deprecated. Please use the dashboard view instead.' );
	}
}
