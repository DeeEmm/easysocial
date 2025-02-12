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

class SocialLocationProvidersOsm extends SociallocationProviders
{
	protected $queries = array(
		'lat' => '',
		'lon' => '',
		'q' => '',
		'type' => ''
	);

	public $url = 'https://nominatim.openstreetmap.org';

	public function __construct()
	{
		parent::__construct();
	}

	public function setCoordinates($lat, $lng)
	{
		return $this->setQuery('lat', $lat) && $this->setQuery('lon', $lng);
	}

	public function setSearch($search = '')
	{
		return $this->setQuery('q', $search);
	}

	public function setType($type = '')
	{
		return $this->setQuery('type', $type);
	}

	public function getResult($queries = array())
	{
		$this->setQueries($queries);

		$options = array();

		$searchType = 'reverse';

		if (!empty($this->queries['q'])) {
			$options['q'] = $this->queries['q'];
			$searchType = 'search';
		} else {
			$options['lat'] = $this->queries['lat'];
			$options['lon'] = $this->queries['lon'];
		}

		if ($this->queries['type'] == 'dashboard') {
			return $this->getDashboardCountries();
		}

		return $this->getCountryData($searchType, $options);
	}

	public function getCountryData($searchType, $options)
	{
		$connector = ES::connector($this->url . '/' . $searchType . '?format=json&addressdetails=1&' . http_build_query($options));
		$result = $connector
						->execute()
						->getResult();

		$result = json_decode($result);

		if (empty($result) || isset($result->error)) {
			$error = isset($result->message) ? $result->message : JText::_('COM_EASYSOCIAL_LOCATION_PROVIDERS_OSM_UNKNOWN_ERROR');

			$this->setError($error);
			return false;
		}

		$result = is_array($result) ? $result : array($result);
		$venues = array();

		foreach ($result as $row) {
			$obj = new SocialLocationData;
			$obj->latitude = $row->lat;
			$obj->longitude = $row->lon;
			$obj->name = $row->display_name;
			$obj->address = $row->address;
			$obj->formatted_address = $row->display_name;

			$venues[] = $obj;
		}

		return $venues;
	}

	public function getDashboardCountries()
	{
		$file = SOCIAL_ADMIN_DEFAULTS . '/regions/country.json';
		$data = ES::makeObject($file);
		$countries = json_decode($this->queries['q']);

		$result = array_filter($data,
			function ($e) use (&$countries) {
				if (in_array($e->name, $countries)) {

					// this is to get country that not in the file
					$countries = array_diff($countries, [$e->name]);
					return true;
				}
			}
		);

		$venues = [];

		foreach ($result as $country) {
			$obj = new SocialLocationData;
			$obj->latitude = $country->latitude;
			$obj->longitude = $country->longitude;
			$obj->name = $country->name;
			$obj->formatted_address = $country->name;

			$venues[] = $obj;
		}

		if (!$countries) {
			return $venues;
		}

		// if there are still unlisted countries, we use the old way
		foreach ($countries as $country) {
			$options = array('country' => $country);
			$result = $this->getCountryData('search', $options);

			if (!$result) {
				continue;
			}

			$venues[] = $result[0];
		}

		return $venues;
	}
}
