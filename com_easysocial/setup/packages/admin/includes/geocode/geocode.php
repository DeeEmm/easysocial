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

class SocialGeoCode
{
	// API url
	const API_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

	// Methods
	const METHOD_REVERSE = '?latlng=';
	const METHOD_ADDRESS = '?address=';
	const METHOD_SENSOR = '&sensor=';
	const METHOD_KEY = '&key=';

	/**
	 * Class constructor
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct()
	{
	}

	/**
	 * Factory pattern
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function factory()
	{
		$geocode = new self();

		return $geocode;
	}

	/**
	 * Retrieves the proper url
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getURL($method, $value, $sensor = 'false')
	{
		$url = self::API_URL . $method . $value . self::METHOD_SENSOR . $sensor . self::METHOD_KEY . $this->getApiKey();

		return $url;
	}

	/**
	 * Retieve API key from setting
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getApiKey()
	{
		$config = ES::config();

		return $config->get('location.maps.api');
	}

	/**
	 * Retrieves the coordinates provided with the address
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function address($address)
	{
		$result = $this->geocode($address);

		if (!$result) {
			return false;
		}

		$coordinates = new stdClass();
		$coordinates->lat = $result->geometry->location->lat;
		$coordinates->lng = $result->geometry->location->lng;

		return $coordinates;
	}

	/**
	 * Returns a mode complete geocode data.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function geocode($address)
	{
		$url = $this->getURL(self::METHOD_ADDRESS, urlencode($address));

		$connector = ES::get('Connector');
		$connector->addUrl($url);
		$connector->connect();

		// Get the result
		$result = $connector->getResult($url);

		// Since the result is in json string, we need to decode it back to a proper php object.
		$obj = ES::makeObject($result);

		if (empty($obj->status) || $obj->status !== 'OK') {
			return false;
		}

		return $obj->results[0];
	}

	/**
	 * Reverse geocode the
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reverse($latitude, $longitude)
	{
		$url = $this->getURL(self::METHOD_REVERSE, $latitude . ',' . $longitude);

		$connector = ES::get('Connector');
		$connector->addUrl($url);
		$connector->connect();

		// Get the result
		$result = $connector->getResult($url);

		// Since the result is in json string, we need to decode it back to a proper php object.
		$obj = ES::makeObject($result);

		if ($obj->status !== 'OK') {
			return false;
		}

		return $obj->results[0]->formatted_address;
	}
}
