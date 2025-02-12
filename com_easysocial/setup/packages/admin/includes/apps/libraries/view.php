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

require_once(__DIR__ . '/abstract.php');

class SocialAppsView extends SocialAppsAbstract
{
	public $title = null;

	public function __construct($options = array())
	{
		parent::__construct($options);

		$this->title = $this->app->_('title');
	}

	/**
	 * App views has the ability to change the title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * App views has the ability to change the title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setTitle($title)
	{
		$this->title = JText::_($title);
	}

	/**
	 * Retrieves the user params
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getUserParams( $userId )
	{
		$map 	= ES::table( 'AppsMap' );
		$map->load( array( 'app_id' => $this->app->id , 'uid' => $userId ) );

		$registry	= ES::registry( $map->params );

		return $registry;
	}
}
