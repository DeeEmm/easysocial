<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

// Include the fields library
ES::import('admin:/includes/fields/dependencies');

// Include helper file.
ES::import('fields:/user/address/helper');

class SocialFieldsUserAddress extends SocialFieldItem
{
	public function getStates()
	{
		$country = ES::input()->getString('country');

		$region = ES::table('Region');
		$region->load(array('type' => SOCIAL_REGION_TYPE_COUNTRY, 'name' => $country, 'state' => SOCIAL_STATE_PUBLISHED));

		$states = $region->getChildren(array('ordering' => $this->params->get('sort')));

		$data = new stdClass();

		foreach ($states as $state) {
			$data->{$state->code} = $state->name;
		}

		ES::ajax()->resolve($data);
	}

	/**
	 * Location suggestions
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLocations()
	{

		$lat = $this->input->get('latitude', '', 'string');
		$lng = $this->input->get('longitude', '', 'string');
		$query = $this->input->get('query', '', 'string');

		// For now we only support Google Maps
		$provider = 'maps';

		$service = ES::location($provider);

		if ($service->hasErrors()) {
			return $this->ajax->reject($service->getError());
		}

		if ($lat && $lng) {
			$service->setCoordinates($lat, $lng);
		}

		if ($query) {
			$service->setSearch($query);
		}

		$venues = $service->getResult($query);

		if ($service->hasErrors()) {
			return $this->ajax->reject($service->getError());
		}

		return $this->ajax->resolve($venues);
	}
}
