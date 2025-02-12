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

require_once(dirname(__FILE__) . '/linkedin.php');

class SocialConsumerLinkedIn extends LinkedIn
{
	protected $apiKey = null;
	protected $apiSecret = null;
	protected $redirect = null;

	public function __construct($key, $secret, $callback)
	{
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->config = ES::config();

		$this->apiKey = $key;
		$this->apiSecret = $secret;
		$this->redirect = JURI::root() . 'index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=linkedin';

		$options = array('appKey' => $this->apiKey, 'appSecret' => $this->apiSecret, 'callbackUrl' => $this->redirect);

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
		return 'linkedin';
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

	public function getAuthorizationURL()
	{
		// default Linkedin scope permissions
		$scopes = array('r_liteprofile', 'r_emailaddress', 'w_member_social');
		$scopes = implode(',', $scopes);

		$url = parent::_URL_AUTH_V2;
		$url .= '&client_id=' . $this->apiKey;
		$url .= '&redirect_uri=' . urlencode($this->redirect);
		$url .= '&state=' . $this->constructUserIdInState();
		$url .= '&scope=' . urlencode($scopes);

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
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'linkedin');
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
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'linkedin');
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
		// We need to update with the new access token here.
		$session = JFactory::getSession();
		$accessToken = $session->get('linkedin.access', '', SOCIAL_SESSION_NAMESPACE);

		$user = $this->getUser();

		$table = ES::table('OAuth');
		$exists = $table->load(array('oauth_id' => $user->id, 'client' => 'linkedin'));

		if (!$exists) {
			return false;
		}

		// Try to update with the new token
		$table->token = $accessToken->token;
		$table->secret = $accessToken->secret;
		$table->expires = $accessToken->expires;
		$table->params = json_encode($this->getUserMeta());

		$state = $table->store();

		return $state;
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

		$access = parent::retrieveTokenAccess($this->auth_code);

		if (!$access) {
			return false;
		}

		$obj = new stdClass();

		// Convert to object
		if (is_string($access['linkedin'])) {
			$access['linkedin'] = json_decode($access['linkedin']);
		}

		$obj->token = $access['linkedin']->access_token;
		$obj->secret = true;
		$obj->params = '';
		$obj->expires = ES::date();

		// If the expiry date is given
		if (isset($access['linkedin']->expires_in)) {
			$expires = $access['linkedin']->expires_in;

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
		// Get the information needed from Linkedin
		$details = parent::me('?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))');
		$result = json_decode($details['linkedin']);

		// Format the output
		if ($result) {
			$email = $this->getUserEmail();
			$firstName = $result->firstName;
			$lastName = $result->lastName;

			// Get the preferred local
			$preferredLocale = $firstName->preferredLocale;
			$locale = $preferredLocale->language . '_' . $preferredLocale->country;

			$firstName = $firstName->localized->$locale;
			$lastName = $lastName->localized->$locale;
			$formattedName = $firstName . ' ' . $lastName;

			$obj = new stdClass();
			$obj->id = $result->id;
			$obj->locale = $locale;
			$obj->firstName = $firstName;
			$obj->lastName = $lastName;
			$obj->formattedName = $formattedName;
			$obj->email = $email;
			$obj->profilePicture = isset($result->profilePicture) ? $result->profilePicture : '';

			return $obj;
		}

		return $result;
	}

	/**
	 * Retrieve details of user from LinkedIn
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getUserMeta()
	{
		// Empty user meta data
		$data = [];

		// Get the default profile
		$profile = ES::oauth()->getDefaultProfile('linkedin');

		// Assign the profileId first
		$data['profileId'] = $profile->id;

		// We need the basic id from LinkedIn
		$linkedinFields = ['id'];

		// We let field decide which fields they want from facebook
		$fields = $profile->getCustomFields();
		$args = array(&$linkedinFields, &$this);
		$fieldsLib = ES::fields();
		$fieldsLib->trigger('onOAuthGetMetaFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Unique it to prevent multiple same fields request
		$linkedinFields = array_unique((array) $linkedinFields);

		// Implode it into a string for request
		$linkedinFields = implode(',', $linkedinFields);

		// Get the information needed from Linkedin
		$result = $this->getUser();

		$details = [];
		$details['email'] = $result->email;
		$details['username'] = $result->email;
		$details['name'] = $result->formattedName;
		$details['first_name'] = $result->firstName;
		$details['last_name'] = $result->lastName;

		if ($this->config->get('oauth.linkedin.registration.avatar')) {
			$details['avatar'] = $this->getAvatar($result);
		}

		// Give fields the ability to decorate user meta as well
		// This way fields can do extended api calls if the fields need it
		$args = array(&$details, &$this);
		$fieldsLib->trigger('onOAuthGetUserMeta', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// We remap the id to oauth_id key
		$details['oauth_id'] = $result->id;

		// Merge Facebook details into data array
		$data = array_merge($data, $details);

		// Generate a random password for the user.
		$data['password'] = JUserHelper::genRandomPassword();

		return $data;
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
	public function getLoginButton($callback, $permissions = array() , $display = 'popup', $text = '', $size = 'btn-sm btn-block')
	{
		$config = ES::config();
		$jfbconnect = ES::jfbconnect();

		// Check if JFBConnect is enabled
		if ($jfbconnect->isEnabled()) {

			// We only return false here since the button already created through facebook library
			return;
		}

		if (!$config->get('oauth.linkedin.registration.enabled')) {
			return;
		}

		$callbackOptions = array();
		$callbackOptions['layout'] = 'oauthRequestToken';
		$callbackOptions['client'] = 'linkedin';
		$callbackOptions['callback'] = base64_encode($callback);

		$url = ESR::registration($callbackOptions, false);

		if (!$text) {
			$text = 'COM_EASYSOCIAL_SIGN_IN_WITH_LINKEDIN';
		}

		// only display icon without text
		if ($text == 'icon') {
			$text = '';
		}

		$theme = ES::themes();
		$theme->set('url', $url);
		$theme->set('size', $size);
		$theme->set('text', $text);

		$output = $theme->output('site/linkedin/button');

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
