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

class EasySocialControllerLocation extends EasySocialController
{
	/**
	 * Suggests a location to people
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function suggestLocations()
	{
		$address = $this->input->get('address', '', 'default');
		$location = ES::location();

		if ($location->hasErrors()) {
			return $this->ajax->reject($location->getError());
		}

		// Search for address
		$location->setSearch($address);

		$result = $location->getResult();

		return $this->ajax->resolve($result);
	}

	/**
	 * Retrieves a list of locations
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getLocations()
	{
		// Get the provided latitude and longitude
		$latitude = $this->input->get('latitude', '', 'string');
		$longitude = $this->input->get('longitude', '', 'string');
		$query = $this->input->get('query', '', 'string');

		$location = ES::location();

		if ($location->hasErrors()) {
			return $this->ajax->reject($location->getError());
		}

		if ($latitude && $longitude) {
			$location->setCoordinates($latitude, $longitude);
		}

		$location->setSearch($query);

		$result = $location->getResult();

		if ($location->hasErrors()) {
			return $this->ajax->reject($location->getError() ? $location->getError() : $result);
		}

		return $this->ajax->resolve($result);
	}

	/**
	 * Determines if the controller should be visible on lockdown mode
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isLockDown($task)
	{
		if ($this->config->get('general.site.lockdown.registration')) {
			return false;
		}

		return true;
	}
}
