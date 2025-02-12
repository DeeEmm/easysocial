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

class SocialLabels
{
	public static function getInstance()
	{
		static $instance = null;

		if( is_null( $instance ) )
		{
			$instance	= new self();
		}

		return $instance;
	}

	/**
	 * Displays a form to input the label.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getForm()
	{

	}

	/**
	 * Inserts a new label
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function create()
	{
	}

	/**
	 * Inserts a new label
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function delete()
	{
	}
}
