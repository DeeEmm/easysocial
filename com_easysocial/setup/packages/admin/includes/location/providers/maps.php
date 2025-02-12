<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/location/provider');

class SocialLocationProvidersMaps extends SociallocationProviders
{
	protected $queries = array(
		'latlng' => '',
		'address' => '',
		'key' => '',
		'language' => ''
	);

	public $url = 'https://maps.googleapis.com/maps/api/geocode/json';

	public function __construct()
	{
		parent::__construct();

		$options = array(
			'key' => $this->config->get('location.maps.api'),
			'language' => ES::user()->getLocationLanguage(),
			'useSecureKey' => $this->config->get('location.maps.secure.api'),
			'browserkey' => $this->config->get('location.maps.secure.browser'),
			'serverKey' => $this->config->get('location.maps.secure.server')
		);

		// Initialize options
		foreach ($options as $key => $value) {
			$this->setQuery($key, $value);
		}
	}

	public function setCoordinates($lat, $lng)
	{
		return $this->setQuery('latlng', $lat . ',' . $lng);
	}

	public function setSearch($search = '')
	{
		return $this->setQuery('address', $search);
	}

	public function getResult($queries = array())
	{
		$this->setQueries($queries);

		$options = array();

		// General api key
		if (!empty($this->queries['key'])) {
			$options['key'] = $this->queries['key'];
		}

		// See if we should use server key as api key
		if ($this->queries['useSecureKey'] && !empty($this->queries['serverKey'])) {
			$options['key'] = $this->queries['serverKey'];
		}

		// Set language
		if (!empty($this->queries['language'])) {
			$options['language'] = $this->queries['language'];
		}

		if (!empty($this->queries['address'])) {
			$options['address'] = $this->queries['address'];
		} else {
			$options['latlng'] = $this->queries['latlng'];
		}

		$connector = ES::connector($this->url . '?' . http_build_query($options));
		$result = $connector
						->execute()
						->getResult();

		$result = json_decode($result);

		if (!isset($result->status) || $result->status != 'OK') {
			$error = isset($result->error_message) ? $result->error_message : JText::_('COM_EASYSOCIAL_LOCATION_PROVIDERS_MAPS_UNKNOWN_ERROR');

			$this->setError($error);
			return $error;
		}

		$venues = array();

		foreach ($result->results as $row) {
			$obj = new SocialLocationData;
			$obj->latitude = $row->geometry->location->lat;
			$obj->longitude = $row->geometry->location->lng;
			$obj->name = $row->address_components[0]->long_name;
			$obj->address = $row->formatted_address;
			$obj->fulladdress = $row->formatted_address;

			$venues[] = $obj;
		}

		return $venues;
	}
}
