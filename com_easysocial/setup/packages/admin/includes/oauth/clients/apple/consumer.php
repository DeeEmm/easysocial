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

require_once(dirname(__FILE__) . '/apple.php');

class SocialConsumerApple extends SocialApple
{
	public $clientUser = null;
	public $identityToken = null;
	public $auth_code = null;
	public $userObj = null;

	public function __construct($client, $token, $callback)
	{
		$this->doc = JFactory::getDocument();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->config = ES::config();

		$this->clientUser = $client;
		$this->identityToken = $token;
		$this->redirect = JURI::root() . 'index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=apple';

		$options = array('appKey' => $this->clientUser, 'appSecret' => $this->identityToken, 'callbackUrl' => $this->redirect);

		parent::__construct($options);
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
	 * @since	2.1
	 * @access	public
	 */
	public function getType()
	{
		return 'apple';
	}

	/**
	 * Renders the revoke access button
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getRevokeButton($callback)
	{
		$theme = ES::themes();
		$theme->set('callback', $callback);
		$output = $theme->output('site/linkedin/revoke');

		return $output;
	}

	/**
	 * Returns the verifier option. Since Facebook does not have oauth_verifier,
	 * The only way to validate this is through the 'code' query
	 *
	 * @since	2.1.0
	 * @access	public
	 **/
	public function getVerifier()
	{
		$verifier = $this->input->get('oauth_verifier' , '', 'default');
		return $verifier;
	}

	public function getAuthorizationURL($callback = '')
	{
		$redirect_uri = !empty($callback) ? $callback : $this->redirect;

		$url = parent::_URL_AUTH;
		$url .= '?response_type=code';
		$url .= '&response_mode=form_post';
		$url .= '&client_id=' . $this->clientUser;
		$url .= '&redirect_uri=' . urlencode($redirect_uri);
		$url .= '&state=' . $this->constructUserIdInState();
		$url .= '&scope=' . urlencode('name email');

		return $url;
	}

	private function constructUserIdInState()
	{
		$user = ES::user();
		$state = parent::_USER_CONSTANT . $user->id;

		return $state;
	}

	public function getUserIdFromState($state)
	{
		$id = str_replace(parent::_USER_CONSTANT, '', $state);

		return $id;
	}

	/**
	 * Determines if the current twitter user is already registered
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isRegistered()
	{
		$table = ES::table('OAuth');
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'apple');
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
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'apple');
		$table->load($options);

		return $table->uid;
	}

	/**
	 * Retrieves the user's unique id on Twitter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUserId()
	{
		$user = $this->getUser();

		return $user->id;
	}

	/**
	 * Sets the request token
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setRequestToken($token, $secret)
	{
		$this->request_token = $token;
		$this->request_secret = $secret;
	}

	/**
	 * Set the authorization code
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setAuthCode($code)
	{
		$this->auth_code = $code;
	}

	/**
	 * Set the user object
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function setUserObj($obj)
	{
		$this->userObj = $obj;
	}

	/**
	 * Set the access token
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function setAccess($access)
	{
		return parent::setAccessToken($access);
	}

	/**
	 * Refreshes the stored token
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateToken()
	{
		return true;
	}

	/**
	 * Once the user has already granted access, we can now exchange the token with the access token
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAccessLegacy($verifier = '')
	{
		$token = $this->input->get('oauth_token', '', 'default');
		$session = JFactory::getSession();
		$secret = $session->get('linkedin.oauth_secret', '', SOCIAL_SESSION_NAMESPACE);

		// Try to retrieve the access token now
		$access = parent::retrieveTokenAccess($token, $secret, $verifier);

		$obj = new stdClass();
		$obj->token = $access['linkedin']['oauth_token'];
		$obj->secret = $access['linkedin']['oauth_token_secret'];
		$obj->expires = $access['linkedin']['oauth_expires_in'];

		return $obj;
	}

	/**
	 * Exchanges the request token with the access token
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function getAccess($verifier = '', $code = '')
	{
		// Set the authorization code from the response
		$this->setAuthCode($code);

		$params = array('grant_type' => 'authorization_code',
			'code' => $this->auth_code,
			'redirect_uri' => $this->redirect,
			'client_id' => $this->clientUser,
			'client_secret' => $this->identityToken
		);

		$access = parent::retrieveTokenAccess($params);

		if (!$access) {
			return false;
		}

		$claims = explode('.', $access->id_token)[1];
		$claims = json_decode(base64_decode($claims));

		$this->userObj->id = $claims->sub;

		if (!isset($this->userObj->email)) {
			$this->userObj->email = $claims->email;
		}

		$obj = new stdClass();

		$obj->token = $access->access_token;
		$obj->secret = '';
		$obj->params = '';
		$obj->expires = ES::date();
		$obj->user = $this->userObj;

		// If the expiry date is given
		if (isset($access->expires_in)) {
			$expires = $access->expires_in;

			// Set the expiry date with proper date data
			$obj->expires = ES::date(strtotime('now') + $expires)->toSql();
		}

		return $obj;
	}

	/**
	 * Retrieves the person's profile picture
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAvatar($meta = array())
	{
		$avatar = false;

		if (isset($meta->profilePicture)) {
			$profilePicture = $meta->profilePicture;
			$displayImage = $profilePicture->{'displayImage~'};
			$imageElements = $displayImage->elements;

			$totalImageVariations = count($imageElements);

			// We want to get the highest quality image
			// The last array always store the highest image variation.
			$image = $imageElements[$totalImageVariations - 1];
			$identifiers = $image->identifiers[0];
			$avatar = $identifiers->identifier;
		}

		return $avatar;
	}

	/**
	 * Method to retrieve user email
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	public function getUserEmail()
	{
		$details = parent::emailAddress();
		$result = json_decode($details['linkedin']);

		$email = '';

		// Decorate the data
		if ($result) {
			$elements = $result->elements;
			$elements = ES::makeArray($elements[0]);

			$email = $elements['handle~']['emailAddress'];
		}

		return $email;
	}

	/**
	 * Retrieves user's linkedin profile
	 *
	 * @since	3.0.4
	 * @access	public
	 */
	private function getUser()
	{
		return $this->userObj;
	}

	/**
	 * Retrieve details of user from LinkedIn
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getUserMeta()
	{
		$session = JFactory::getSession();

		$access = $session->get('apple.access', '', SOCIAL_SESSION_NAMESPACE);

		$userObj = $access->user;

		$this->setUserObj($userObj);

		// Get the default profile
		$profile = ES::oauth()->getDefaultProfile('apple');

		$details = array();
		$details['profileId'] = $profile->id;
		$details['oauth_id'] = $userObj->id;
		$details['email'] = $userObj->email;
		$details['name'] = isset($userObj->fullname) ? $userObj->fullname : '';
		$details['first_name'] = isset($userObj->firstName) ? $userObj->name->firstName : '';
		$details['last_name'] = isset($userObj->lastName) ? $userObj->name->lastName : '';

		// We let field decide which fields they want from facebook
		$fields = $profile->getCustomFields();
		$fieldsLib = ES::fields();

		// Give fields the ability to decorate user meta as well
		// This way fields can do extended api calls if the fields need it
		$args = array(&$details, &$this);
		$fieldsLib->trigger('onOAuthGetUserMeta', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		$details['password'] = JUserHelper::genRandomPassword();

		return $details;
	}

	/**
	 * Gets the login credentials for the Joomla site.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getLoginCredentials()
	{
		$table = ES::table("OAuth");
		$user = $this->getUser();

		$state = $table->load(array('oauth_id' => $user->id, 'client' => $this->getType()));

		if (!$state) {
			return false;
		}

		// Get the user object.
		$user = ES::user($table->uid);
		$credentials = array('username' => $user->username, 'password' => JUserHelper::genRandomPassword());

		return $credentials;
	}

	/**
	 * Renders the login button for LinkedIn
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getLoginButton($callback , $permissions = array() , $display = 'popup', $text = '', $size = 'btn-sm btn-block')
	{
		$config = ES::config();

		if (!$config->get('oauth.apple.registration.enabled')) {
			return;
		}

		if (!$config->get('oauth.apple.app') || !$config->get('oauth.apple.secret')) {
			return;
		}

		$theme = ES::themes();

		// Load front end language file.
		ES::language()->loadSite();

		if (empty($text)) {
			$text = 'COM_ES_SIGN_IN_WITH_APPLE';
		}

		// only display icon without text
		if ($text == 'icon') {
			$text = '';
		}

		if (ES::responsive()->isSafari()) {
			$this->doc->addScript('//appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js');

			// Prevent script loaded 2 times.
			static $cache = [];

			if (!isset($cache['apple.auth.init'])) {
				$this->doc->addScriptDeclaration('AppleID.auth.init({
						clientId : "' . $this->clientUser .'",
						scope : "name email",
						redirectURI : "' . $this->redirect . '",
						state : "' . $this->constructUserIdInState() . '"
					});'
				);

				$cache['apple.auth.init'] = true;
			}
			
		}

		$authorizeURL = $this->getAuthorizationURL();

		$theme = ES::themes();
		$theme->set('url', $authorizeURL);
		$theme->set('size', $size);
		$theme->set('text', $text);
		$theme->set('script', [
			'clientId' => $this->clientUser,
			'scope' => 'name email',
			'redirectURI' => $this->redirect,
			'state' => $this->constructUserIdInState(),
			'usePopup' => true
		]);

		$output = $theme->output('site/apple/button');

		return $output;
	}

	/**
	 * Pushes data to LinkedIn
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function push($message, $placeId = null, $photo = null, $link = null)
	{
		$options = array(
					'text' => $message,
					'visibility' => 'PUBLIC',
					'submitted-url' => $link->get('link'),
					'submitted-url-title' => $link->get('title'),
					'submitted-url-desc' => $link->get('content'),
					'userId' => $this->getUserId()
				);

		if ($photo) {
			$options['submitted-image'] = $photo;
		}

		// Satisfy linkedin's criteria
		// Linkedin now restricts the message and text size.
		// To be safe, we'll use 380 characters instead of 400.
		$options['text'] = trim(htmlspecialchars(strip_tags(stripslashes($options['text']))));
		$options['text'] = trim(ESJString::substr($options['text'], 0, 380));

		// Share to their account now
		$response = parent::share('new', $options, true, false);
		$state = isset($response['success']) && $response['success'] ? true : false;

		if (!$state) {
			return false;
		}

		return $state;
	}
}
