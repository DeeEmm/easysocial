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

require_once(SOCIAL_COMPOSER_AUTOLOAD);

class SocialConsumerGoogle
{

	public $callback = '';
	public $_access_token = '';

	private $uid = '';

	private $clientId = '';
	private $clientSecret = '';

	private $type = 'google';

	public function __construct($clientId, $clientSecret, $callback)
	{
		$this->input = ES::request();
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->callback	= $callback;
	}

	/**
	 * Generates the authorization url that Google requires
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getAuthorizationURL($callback = '')
	{
		$client = $this->getClient();

		$url = $client->createAuthUrl([
			'email',
			'profile'
		]);

		return $url;
	}

	/**
	 * Retrieves the person's profile picture
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getAvatar($meta = [], $size = 'normal')
	{
		$avatar = $meta['picture'];

		return $avatar;
	}

	/**
	 * Retrieves the client library
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getClient()
	{
		$client = new Google_Client();
		$client->setClientId($this->clientId);
		$client->setClientSecret($this->clientSecret);

		// We use non sef for the redirection
		$redirect = JURI::root() . 'index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=google';

		$client->setRedirectUri($redirect);

		return $client;
	}

	/**
	 * Not implemented in this oauth client
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getPermissions()
	{
		return [];
	}

	/**
	 * Return client type
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Refreshes the stored token
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function updateToken()
	{
		// We need to update with the new access token here.
		$session = JFactory::getSession();
		$accessToken = $session->get($this->type . '.access', '', SOCIAL_SESSION_NAMESPACE);

		$user = $this->getUser();
		$userId = $this->getUserId();

		$table = ES::table('OAuth');
		$exists = $table->load(array('oauth_id' => $userId, 'client' => $this->type));

		if (!$exists) {
			return false;
		}

		// Try to update with the new token
		$table->token = $accessToken->token;
		$table->secret = $accessToken->secret;

		$state = $table->store();

		return $state;
	}

	/**
	 * Allows caller to revoke twitter's access
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function revoke()
	{
		$client = $this->getClient();
		$client->setAccessToken($this->token['access']);

		$result = $client->revokeToken();

		return $result;
	}

	/**
	 * Determines if the current twitter user is already registered
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isRegistered()
	{
		$table = ES::table('OAuth');
		$options = [
			'oauth_id' => $this->getUserId(),
			'client' => $this->type
		];
		$state = $table->load($options);

		return $state;
	}

	/**
	 * Retrieve Joomla User id
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getUid()
	{
		$table = ES::table('OAuth');
		$options = array('oauth_id' => $this->getUserId(), 'client' => $this->type);
		$table->load($options);

		return $table->uid;
	}

	/**
	 * Renders the revoke access button
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRevokeButton($callback)
	{
		$theme = ES::themes();
		$theme->set('callback', $callback);
		$output = $theme->output('site/google/revoke');

		return $output;
	}

	/**
	 * Gets the login credentials for the Joomla site.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getLoginCredentials()
	{
		$table = ES::table("OAuth");
		$user = $this->getUser();
		$userId = $this->getUserId();

		$state = $table->load(array('oauth_id' => $userId, 'client' => $this->type));

		if (!$state) {
			return false;
		}

		// Get the user object.
		$user = ES::user($table->uid);
		$credentials = array('username' => $user->username, 'password' => JUserHelper::genRandomPassword());

		return $credentials;
	}

	/**
	 * Map user's details from Google
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getUserMeta()
	{
		// Load internal configuration
		$config = ES::config();

		// Get the default profile
		$profile = ES::oauth()->getDefaultProfile($this->type);

		// We let field decide which fields they want from facebook
		$fields = $profile->getCustomFields();

		$googleFields = ['id'];

		$args = array(&$googleFields, &$this);
		$fieldsLib = ES::fields();
		$fieldsLib->trigger('onOAuthGetMetaFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Unique it to prevent multiple same fields request
		$googleFields = array_unique((array) $googleFields);

		$details = (array) $this->getUser();

		$data = [
			'profileId' => $profile->id,
			'username' => $details['email']
		];

		// Give fields the ability to decorate user meta as well
		// This way fields can do extended api calls if the fields need it
		$args = array(&$details, &$this);
		$fieldsLib->trigger('onOAuthGetUserMeta', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// We remap the id to oauth_id key
		$details['oauth_id'] = $details['id'];
		unset($details['id']);

		// Merge Facebook details into data array
		$data = array_merge($data, $details);

		// Generate a random password for the user.
		$data['password'] = JUserHelper::genRandomPassword();

		return $data;
	}

	/**
	 * Retrieves the verifier params from the request string
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getVerifier()
	{
		$verifier = $this->input->get('code' , '', 'default');

		return $verifier;
	}

	/**
	 * Exchanges the verifier code with the access token.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getAccess($verifier = '')
	{
		$client = $this->getClient();

		// Google One Tap
		$credential = $this->input->get('credential', '', 'default');

		// Google single sign on
		$code = $this->input->get('code', '', 'default');

		if ($credential) {
			$payload = $client->verifyIdToken($credential);
			$token = [
				'access_token' => $credential,
				'id_token' => '',
				'expires_in' => '',
				'type' => 'onetap'
			];

			$googleAccount = new stdClass();
			$googleAccount->id = $payload['sub'];
		}

		if ($code) {
			$token = $client->fetchAccessTokenWithAuthCode($code);

			if (isset($token["error"]) && $token['error']) {
				throw new Exception($token['error_description']);
			}

			$client->setAccessToken($token['access_token']);

			$googleOauth = new Google_Service_Oauth2($client);
			$googleAccount = $googleOauth->userinfo->get();
		}

		$obj = new stdClass();
		$obj->token = $token['access_token'];
		$obj->secret = $token['id_token'];
		$obj->expires = $token['expires_in'];

		$params = ES::registry();
		$params->set('user_id', $googleAccount->id);

		$obj->params = $params->toString();

		return $obj;
	}

	/**
	 * Retrieves the user object from Google
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getUser()
	{
		static $user = null;

		if (is_null($user)) {
			$client = $this->getClient();

			if ($this->token['onetap']) {
				$user = $client->verifyIdToken($this->token['access']);
				$user['id'] = $user['sub'];
			}

			if (!$this->token['onetap']) {
				$client->setAccessToken($this->token['access']);

				$googleOauth = new Google_Service_Oauth2($client);
				$user = $googleOauth->userinfo->get();
				$user = get_object_vars($user);
			}
		}

		return $user;
	}

	/**
	 * Retrieves the user's unique id on Google
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getUserId()
	{
		$user = $this->getUser();

		return $user['id'];
	}

	/**
	 * Allows caller to set the access
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function setAccess($access, $secret, $accessTokenObject = null)
	{
		$this->token = [
			'access' => $access,
			'secret' => $secret,
			'onetap' => ($accessTokenObject) && isset($accessTokenObject->onetap) ? $accessTokenObject->onetap : false
		];
	}

	/**
	 * Renders a logout button
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getLogoutButton($callback)
	{
		// Check if the user has already authenticated.
		$table 	= ES::table( 'OAuth' );
		$exists	= $table->load( array( 'uid' => $uid , 'type' => $type ) );

		$theme->set( 'logoutCallback'	, $callback );
		$output = $theme->output( 'site/login/facebook.authenticated' );
	}

	/**
	 * Renders the login button for Google
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getLoginButton($callback, $permissions = array() , $display = 'popup', $text = '', $size = 'btn-sm btn-block')
	{
		$config = ES::config();

		if (!$config->get('oauth.google.registration.enabled')) {
			return;
		}

		if (!$text) {
			$text = 'COM_ES_SIGN_IN_WITH_GOOGLE';
		}

		// only display icon without text
		if ($text == 'icon') {
			$text = '';
		}

		$authorizeParts = parse_url($callback);

		// If the callback url query exist
		if (isset($authorizeParts['query']) && $authorizeParts['query']) {

			// Redirection url value
			$returnValue = $authorizeParts['query'];

			// Parse those existing key to array
			parse_str($authorizeParts['query'], $authorizeParts);

			// Ensure that is return key and value
			if (isset($authorizeParts['return']) && $authorizeParts['return']) {
				$returnCode = $authorizeParts['return'];

				// Set the redirection url on the session
				$session = JFactory::getSession();
				$session->set('oauth.login.redirection', $returnCode, SOCIAL_SESSION_NAMESPACE);
			}
		}

		$callbackOptions = [
			'layout' => 'oauthRequestToken',
			'client' => $this->type,
			'callback' => base64_encode($callback)
		];

		$url = ESR::registration($callbackOptions, false);

		$theme = ES::themes();
		$theme->set('url', $url);
		$theme->set('size', $size);
		$theme->set('text', $text);

		$output = $theme->output('site/google/button');

		return $output;
	}
}
