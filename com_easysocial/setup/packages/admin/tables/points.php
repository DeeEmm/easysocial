<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTablePoints extends SocialTable
{
	public $id = null;
	public $command = null;
	public $extension = null;
	public $title = null;
	public $description = null;
	public $alias = null;
	public $created = null;
	public $threshold = null;
	public $interval = null;
	public $daily_interval = null;
	public $points = null;
	public $state = null;
	public $params = null;

	private $_totalAchievers = null;


	public function __construct(&$db)
	{
		parent::__construct('#__social_points', 'id', $db);
	}

	/**
	 * Retrieves the list of achievers for this point.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAchievers($options = [])
	{
		$model = ES::model('Points');
		$achievers = $model->getAchievers($this->id, $options);

		return $achievers;
	}

	/**
	 * Retrieves the extension translation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getExtensionTitle()
	{
		$extension	= 'COM_EASYSOCIAL';

		if ($this->extension != 'com_easysocial') {
			$extension 	= strtoupper($this->extension);

			// Load custom language
			ES::language()->load( $this->extension , JPATH_ROOT );
			ES::language()->load( $this->extension , JPATH_ADMINISTRATOR );
		}

		$text 	= $extension . '_POINTS_EXTENSION_' . strtoupper( $this->extension );

		return JText::_( $text );
	}

	/**
	 * Retrieves the list of achievers for this point.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalAchievers()
	{
		if (isset($this->_totalAchievers) && !is_null($this->_totalAchievers)) {
			return $this->_totalAchievers;
		}

		$model = ES::model('Points');
		return $model->getTotalAchievers( $this->id );
	}

	/**
	 * set total point achievers
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function setTotalAchievers($total)
	{
		$this->_totalAchievers = $total;
	}

	/**
	 * Checks if the target user belongs to a group that has access to this point.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isAllowed( $userId )
	{
		$model 	= ES::model( 'Points' );
		return $model->isAllowed( $this->id , $userId );
	}

	/**
	 * Determines if the points is negative
	 *
	 * @since	3.2.12
	 * @access	public
	 */
	public function isNegative()
	{
		$negative = $this->points < 0;

		return $negative;
	}

	/**
	 * Retrieve the points permalink
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermalink($xhtml = false)
	{
		$url 	= FRoute::points( array( 'id' => $this->getAlias() , 'layout' => 'item' ) , $xhtml );

		return $url;
	}

	/**
	 * Retrieves the alias for this point
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlias()
	{
		$alias 	= $this->id . ':' . $this->alias;

		return $alias;
	}

	/**
	 * Loads the points language based on the extension
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function loadLanguage()
	{
		if (empty($this->extension)) {
			return;
		}

		$lang = ES::language();

		$lang->load( $this->extension, JPATH_ROOT );
		$lang->load( $this->extension, JPATH_ADMINISTRATOR );
	}

	/**
	 * Exports points data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		ES::language()->loadSite();

		$data = new stdClass();
		$data->id = (int) $this->id;
		$data->title = JText::_($this->title);
		$data->desc = JText::_($this->description);
		$data->points = $this->points;
		$data->totalAchievers = $this->getTotalAchievers();

		return $data;
	}
}
