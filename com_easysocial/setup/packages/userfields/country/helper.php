<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialFieldsUserCountryHelper
{
	/**
	 * Retrieves a list of countries from the manifest file.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getCountries($source = 'regions')
	{
		static $countries = array();

		if (!isset($countries[$source])) {
			$data = new stdClass();

			if ($source === 'file') {
				$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/countries.json';
				$contents = file_get_contents($file);

				$json = ES::json();
				$countries = $json->decode($contents);
				$countries = (array) $countries;

				// Sort by alphabet
				// asort($countries); #4619 #4964

				foreach ($countries as $code => $country) {
					$data->{$code} = ['code' => $code, 'title' => $country];
				}
			}

			if ($source === 'regions') {
				$countries = ES::model('Regions')->getRegions([
					'type' => SOCIAL_REGION_TYPE_COUNTRY,
					'state' => SOCIAL_STATE_PUBLISHED,
					'ordering' => 'name'
				]);

				foreach ($countries as $country) {
					$namespace = $country->code . ':' . $country->id;
					$data->{$namespace} = ['code' => $country->code, 'title' => $country->name];
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
	public static function getCountryName($code, $source = 'regions')
	{
		$countries = self::getCountries($source);

		$value = $code;

		if (isset($countries->$code)) {
			$value = $countries->{$code}['title'];
		} else {
			foreach ($countries as $uid => $name) {
				$uid = explode(':', $uid)[0];

				if (!is_array($code)) {
					$code = explode(':', $code);
				}

				if ($code[0] == $uid) {
					$value = is_array($name) ? $name['title'] : $name;

					break;
				}
			}
		}

		return $value;
	}

	public static function getCountryCode($name, $source = 'regions')
	{
		$countries = self::getCountries($source);

		foreach ($countries as $uid => $country) {
			if ($country['title'] == $name) {
				return $uid;
			}
		}

		return false;
	}

	public static function getHTMLContentCountries($source = 'regions')
	{
		$countries  = (array) self::getCountries($source);

		$data = array();

		foreach($countries as $key => $value) {
			$row = new stdClass();
			$row->id = $key;
			$row->title = is_array($value) ? $value['title'] : $value;
			$data[] = $row;
		}

		return $data;
	}
}
