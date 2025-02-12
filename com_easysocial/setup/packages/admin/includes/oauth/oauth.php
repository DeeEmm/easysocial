<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/dependencies.php');

class SocialOauth
{
	static $clients = array();

	/**
	 * The current oauth client.
	 * @var	SocialConsumer
	 */
	private $client = null;

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * Example:
	 * <code>
	 * <?php
	 * ES::get( 'OAuth' , 'facebook' );
	 * </code>
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The oauth client's name. E.g: facebook , twitter.
	 * @param	string	A valid callback url.
	 */
	public static function getInstance($client, $callback = '')
	{
		if (!isset(self::$clients[$client])) {
			self::$clients[$client]	= new self($client, $callback);
		}

		return self::$clients[$client];
	}

	public function __construct($client, $callback = '')
	{
		// Get the path to the consumer file.
		$file 	= dirname( __FILE__ ) . '/clients/' . strtolower( $client ) . '/consumer.php';

		jimport( 'joomla.filesystem.file' );

		// If file doesn't exist, just quit.
		if (!JFile::exists($file)) {
			return false;
		}

		if (!$callback) {
			$callback = rtrim(JURI::root(), '/') . JRoute::_('index.php?option=com_easysocial&controller=oauth&task=grant&client=' . $client , false);
		}

		require_once( $file );

		// All adapters classes should have the same naming convention
		$consumerClass  = 'SocialConsumer' . ucfirst($client);

		if (!class_exists($consumerClass)) {
			return false;
		}

		$config	= ES::config();

		// All oauth clients should have a key and secret.
		$key 	= $config->get( 'oauth.' . strtolower( $client ) . '.app' );
		$secret = $config->get( 'oauth.' . strtolower( $client ) . '.secret' );

		// Let's try to create instance of consumer.
		$this->client 	= new $consumerClass( $key , $secret , $callback );
	}

	/**
	 * Maps back the call method functions to the helper.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {

			foreach ($args as &$arg) {
				$refArray[]	=& $arg;
			}
		}
		return call_user_func_array([$this->client, $method], $refArray);
	}

	/**
	 * Determines if a specific oauth client is enabled
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isEnabled($client)
	{
		// If registrations is disabled, we shouldn't show anything
		if (!$this->config->get('registrations.enabled')) {
			return false;
		}

		$key = 'oauth.' . strtolower($client) . '.registration.enabled';

		if (!$this->config->get($key)) {
			return false;
		}

		return true;
	}

	/**
	 * Logs the user into the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function login()
	{
		$app = JFactory::getApplication();
		$credentials = $this->getLoginCredentials();

		return $app->login($credentials);
	}

	/**
	 * Method to show oauth redirect URI for frontend
	 *
	 * @since   2.2.3
	 * @access  public
	 */
	public function getOauthRedirectURI($type = 'facebook')
	{
		$callbackUri = array();

		if ($type == 'facebook') {
			$callbackUri[] = rtrim(JURI::root(), '/') . '/index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=facebook';
			$callbackUri[] = rtrim(JURI::root(), '/') . '/index.php?option=com_easysocial&view=registration&layout=oauthLogin&client=facebook';
			$callbackUri[] = rtrim(JURI::root(), '/') . '/index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=facebook&sharer=1';
		}

		if ($type == 'twitter') {

			$callbackOptions = array();
			$callbackOptions['layout'] = 'oauthDialog';
			$callbackOptions['client'] = 'twitter';

			$callback = ESR::registration($callbackOptions, false);
			$callback = str_replace('/administrator/', '/', $callback);

			$callback = ltrim($callback, '/');

			$callback = FRoute::external($callback, false, null, false, true, true);

			$callbackUri[] = $callback;
		}

		if ($type == 'linkedin' || $type == 'apple' || $type == 'google') {
			$callbackUri[] = rtrim(JURI::root(), '/') . '/index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=' . $type;
		}

		return $callbackUri;
	}

	/**
	 * Get the default assigned social profile type e.g. oauth.facebook.profile
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getDefaultProfile($type = 'facebook')
	{
		// Load internal configuration
		$config = ES::config();

		$oauthType = 'oauth.' . $type . '.profile';

		// Get the profile id the mapping is configured to
		$profileId = $config->get($oauthType);

		$profile = ES::table('profile');
		$state = $profile->load($profileId);

		// Test if profile id is set
		if (!$state) {

			// Try to get the default profile on the site.
			$profile = ES::table('Profile');
			$state = $profile->load(array('default' => 1));

			// If the profile id still cannot be found, just fetch the first item from the database
			if (!$state) {
				$model = ES::model('Profiles');
				$profile = $model->setLimit(1)->getProfiles();
			}
		}

		return $profile;
	}
}
