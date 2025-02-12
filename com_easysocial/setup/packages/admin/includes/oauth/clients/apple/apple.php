<?php
/**
* @package    EasySocial
* @copyright  Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license    GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(dirname(__FILE__)) . '/oauth.php');

class SocialApple extends EasySocial
{

	public $clientUser = null;
	public $identityToken = null;

	const _URL_ACCESS = 'https://appleid.apple.com/auth/token';
	const _URL_AUTH = 'https://appleid.apple.com/auth/authorize';
	const _USER_CONSTANT = 'APPLE_USER_ID_';

	protected $application_key = null;
	protected $application_secret = null;
	protected $callback = null;
	protected $access_token = null;

	public function __construct($config)
	{
		// bad data passed
		if (!is_array($config)) {
			throw new LinkedInException('Apple->__construct(): bad data passed, $config must be of type array.');
		}

		$this->setApplicationKey($config['appKey']);
		$this->setApplicationSecret($config['appSecret']);
		$this->setCallbackUrl($config['callbackUrl']);
	}

	/**
	 * Set application key
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setApplicationKey($key)
	{
		$this->application_key = $key;
	}

	/**
	 * Set application secret
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setApplicationSecret($secret)
	{
		$this->application_secret = $secret;
	}

	/**
	 * Set callback url after authentication
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setCallbackUrl($url)
	{
		$this->callback = $url;
	}

	public function retrieveTokenAccess($params)
	{
		$response = $this->fetch(self::_URL_ACCESS, $params);
		return $response;
	}

	public function fetch($url, $params)
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($params) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/json',
			'User-Agent: curl', # Apple requires a user agent header at the token endpoint
			]);
		}

		$response = curl_exec($ch);
		return json_decode($response);
	}

	/**
	 * Set access token required for API call
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setAccessToken($token)
	{
		// Ensure the data is array
		if (!is_null($token) && is_array($token)) {
			throw new LinkedInException('Apple->setToken(): bad data passed, $access_token should not be in array format.');
		}

		$this->access_token = $token;
	}

}
