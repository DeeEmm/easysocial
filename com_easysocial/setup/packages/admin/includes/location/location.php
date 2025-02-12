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

class SocialLocation extends EasySocial
{
	static $providers = array();
	protected $provider = null;
	private $baseProviderClassname = '';

	public $table = null;

	public function __construct($id = null, $type = null)
	{
		parent::__construct();

		// Initialize the location provider
		$this->provider = $this->initProvider();

		$this->table = ES::table('Location');

		if (!is_null($id) && !is_null($type)) {
			$this->table->load(array('uid' => $id, 'type' => $type));
		}
	}

	public static function factory($id = null, $type = null)
	{
		return new self($id, $type);
	}

	/**
	 * Get GMaps api key
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getGmapsApiKey($type = 'browser')
	{
		// Default key
		$key = $this->getApiKey();

		// Check for secure key
		if ($this->config->get('location.maps.secure.api')) {
			$key = $this->config->get('location.maps.secure.' . $type);
		}

		return $key;
	}

	/**
	 * Get location api key
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getApiKey($type = 'maps')
	{
		if ($type != 'foursquare') {
			$key = trim($this->config->get('location.' . $type . '.api'));

			if (!empty($key)) {
				return $key;
			}

			return false;
		}

		// If it reached here means it trying to get foursquare api key
		$clientId = trim($this->config->get('location.foursquare.clientid'));
		$clientSecret = trim($this->config->get('location.foursquare.clientsecret'));

		$obj = new stdClass();
		$obj->clientId = $clientId;
		$obj->clientSecret = $clientSecret;

		return $obj;
	}

	/**
	 * Determine if the result has address
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hasAddress()
	{
		return !empty($this->table->address);
	}

	/**
	 * Retrieves the longitude
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLongitude()
	{
		return $this->table->longitude;
	}

	/**
	 * Retrieves the latitude
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getLatitude()
	{
		return $this->table->latitude;
	}

	/**
	 * Retrieves the address of a location.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAddress()
	{
		$address = $this->table->address;
		return $address;
	}

	/**
	 * Retrieves the map url of a location.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getMapUrl()
	{
		return $this->table->getMapUrl();
	}

	/**
	 * Initialize the location provider
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function initProvider($provider = null)
	{
		// If no provider is given, then we load the default one from the settings
		if (!$provider) {
			$provider = $this->config->get('location.provider', 'maps');
		}

		if ($provider == 'foursquare') {
			$api = $this->getApiKey($provider);

			// fallback to use google maps
			if (!$api->clientId || !$api->clientSecret) {
				$provider = 'maps';
			}
		}

		if ($provider == 'places') {
			// fallback to use google maps
			if (empty($this->getApiKey($provider))) {
				$provider = 'maps';
			}
		}

		// Return loaded cache if there is any
		if (isset(self::$providers[$provider])) {
			return self::$providers[$provider];
		}

		$file = __DIR__ . '/providers/' . strtolower($provider) . '.php';

		require_once($file);

		$className = 'SocialLocationProviders' . ucfirst($provider);
		$obj = new $className;

		// Now we check if the provider's initialisation generated any errors
		if ($obj->hasErrors()) {
			return false;
		}

		self::$providers[$provider] = $obj;

		return self::$providers[$provider];
	}

	/**
	 * Magic method for method fallback across location providers
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->provider, $method), $arguments);
	}
}
