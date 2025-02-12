<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialFieldsUserAddressHelper
{
	/**
	 * Retrieves a list of countries from the manifest file.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getCountries($source = 'file')
	{
		static $countries = array();

		if (!isset($countries[$source])) {
			$data = [];

			if ($source === 'file') {
				$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/countries.json';
				$contents = file_get_contents($file);

				$json = ES::json();
				$countries = $json->decode($contents);
				$countries = (array) $countries;

				// Sort by alphabet
				// asort($countries); #4619 #4964

				foreach ($countries as $code => $country) {
					$data[] = ['code' => $code, 'title' => $country];
				}
			}

			if ($source === 'regions') {
				$countries = ES::model('Regions')->getRegions(array(
					'type' => SOCIAL_REGION_TYPE_COUNTRY,
					'state' => SOCIAL_STATE_PUBLISHED,
					'ordering' => 'name'
				));

				foreach ($countries as $country) {
					$data[] = ['code' => $country->code, 'title' => $country->name];
				}
			}

			$countries[$source] = $data;
		}

		return $countries[$source];
	}

	/**
	 * Gets the country title given the code.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getCountryName($code, $source = 'file')
	{
		$countries = self::getCountries($source);

		$value = $code;

		if (isset($countries->$code)) {
			$value = $countries->$code;
		}

		return $value;
	}

	public static function getStates($countryName, $sort = 'name')
	{
		$country = ES::table('region');

		$country->load(array('type' => SOCIAL_REGION_TYPE_COUNTRY, 'name' => $countryName));

		$states = $country->getChildren(array('ordering' => $sort));

		$data = new stdClass();

		foreach ($states as $state) {
			$data->{$state->name} = $state->name;
		}

		return $data;
	}
}
