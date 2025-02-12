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

class EasySocialControllerEasySocial extends EasySocialController
{
	/**
	 * Retrieves a list of unique countries for the dashboard country widget
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getCountries()
	{
		ES::checkToken();

		$model = ES::model('Users');
		$countries = $model->getUniqueCountries();

		return $this->view->call(__FUNCTION__, $countries);
	}

	/**
	 * Retrieves a list of locations
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getLocations()
	{
		// Get the provided latitude and longitude
		$latitude = $this->input->get('latitude', '', 'string');
		$longitude = $this->input->get('longitude', '', 'string');
		$query = $this->input->get('query', '', 'string');
		$type = $this->input->get('type', '', 'string');

		$location = ES::location();

		if ($location->hasErrors()) {
			return $this->ajax->reject($location->getError());
		}

		if ($latitude && $longitude) {
			$location->setCoordinates($latitude, $longitude);
		}

		$location->setSearch($query);

		if (!empty($type)) {
			$location->setType($type);
		}

		$result = $location->getResult();

		if ($location->hasErrors()) {
			return $this->ajax->reject($location->getError() ? $location->getError() : $result);
		}

		return $this->ajax->resolve($result);
	}
}
