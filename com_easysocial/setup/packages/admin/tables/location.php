<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialTableLocation extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $type = null;
	public $user_id = null;
	public $created = null;
	public $short_address = null;
	public $address = null;
	public $latitude = null;
	public $longitude = null;
	public $params = null;

	public function __construct($db)
	{
		parent::__construct('#__social_locations', 'id' , $db);
	}

	/**
	 * Loads a location result based on the given uid and type.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function loadByType($uid , $type)
	{
		$db = ES::db();
		$query = 'SELECT * FROM ' . $db->nameQuote($this->_tbl) . ' '
				. 'WHERE ' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid) . ' '
				. 'AND ' . $db->nameQuote('type') . ' = ' . $db->Quote($type);
		$db->setQuery($query);

		$result = $db->loadObject();

		if (!$result) {
			return false;
		}

		return parent::bind($result);
	}

	/**
	 * Retrieves the intro text portion of a message.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getAddress($overrideLength = null)
	{
		$config = ES::config();

		if (!is_null($overrideLength)) {
			// Get the maximum length.
			$maxLength = $overrideLength;

			$message = strip_tags($this->address);
			$message = ESJString::substr($message , 0 , $maxLength) . ' ' . JText::_('COM_EASYSOCIAL_ELLIPSIS');

			return $message;
		}

		return $this->address;
	}

	/**
	 * Override paren't implementation of store.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function store($updateNulls = false)
	{
		$state = parent::store($updateNulls);

		return $state;
	}

	/**
	 * Update the location
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function update($newData = array())
	{
		foreach($newData as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}

		$state = $this->store();

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the city value if available.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getCity()
	{
		$params = ES::makeObject($this->params);

		if (isset($params->address_components[2]) && $params->address_components[2]->types[0] == 'locality') {
			return $params->address_components[2]->short_name;
		}

		return false;
	}

	/**
	 * Retrieves the map url
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getMapUrl()
	{
		$config = ES::config();
		if ($config->get('location.provider') == 'osm') {
			return '//www.openstreetmap.org/#map=16/' . $this->latitude . '/' . $this->longitude;
		}

		return '//maps.google.com/?q=' . $this->latitude . ',' . $this->longitude;
	}

}
