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

require_once(__DIR__ . '/twitteroauth.php');

class SocialConsumerTwitter extends SocialTwitterOAuth
{
	public $callback = '';
	public $_access_token = '';

	private $uid = '';

	private $oauth_appId = '';
	private $oauth_appSecret = '';

	public function __construct($key, $secret, $callback)
	{
		$this->oauth_appId = $key;
		$this->oauth_appSecret = $secret;

		parent::__construct($key, $secret );

		$this->callback	= $callback;

		$this->input = ES::request();
	}

	/**
	 * Retrieves the person's profile picture
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAvatar($meta = array(), $size = 'normal')
	{
		$avatar = $meta['profile_image_url_https'];

		if ($size != 'normal') {

			if ($size == 'original') {
				$avatar = str_ireplace('_normal', '', $avatar);
			} else {
				$avatar = str_ireplace('_normal', '_' . $size, $avatar);
			}

		}

		return $avatar;
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
	 * @since	1.0
	 * @access	public
	 */
	public function getType()
	{
		return 'twitter';
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
		$accessToken = $session->get('twitter.access', '', SOCIAL_SESSION_NAMESPACE);

		$user = $this->getUser();
		$userId = $this->getUserId();

		$table = ES::table('OAuth');
		$exists = $table->load(array('oauth_id' => $userId, 'client' => 'twitter'));

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
	 * @since	2.0
	 * @access	public
	 */
	public function revoke()
	{
		// Twitter doesn't have a method to revoke access
		return true;
	}

	/**
	 * Determines if the current twitter user is already registered
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isRegistered()
	{
		$table = ES::table('OAuth');
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'twitter');
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
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'twitter');
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
		$output = $theme->output('site/twitter/revoke');

		return $output;
	}

	/**
	 * Gets the login credentials for the Joomla site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLoginCredentials()
	{
		$table = ES::table("OAuth");
		$user = $this->getUser();
		$userId = $this->getUserId();

		$state = $table->load(array('oauth_id' => $userId, 'client' => 'twitter'));

		if (!$state) {
			return false;
		}

		// Get the user object.
		$user = ES::user($table->uid);
		$credentials = array('username' => $user->username, 'password' => JUserHelper::genRandomPassword());

		return $credentials;
	}

	/**
	 * Retrieves user's twitter details
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUserMeta()
	{
		// Empty user meta data
		$data = array();

		// Load internal configuration
		$config = ES::config();

		// Get the default profile
		$profile = ES::oauth()->getDefaultProfile('twitter');

		// Assign the profileId first
		$data['profileId'] = $profile->id;

		// We need the basic id from Twitter
		$twitterFields = array('id');

		// We let field decide which fields they want from facebook
		$fields = $profile->getCustomFields();
		$args = array(&$twitterFields, &$this);
		$fieldsLib = ES::fields();
		$fieldsLib->trigger('onOAuthGetMetaFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Unique it to prevent multiple same fields request
		$twitterFields = array_unique((array) $twitterFields);

		// Implode it into a string for request
		$twitterFields = implode(',', $twitterFields);

		// Let's try to query facebook for more details.
		$details = (array) $this->getUser();

		// Since twitter never produces the user's email address, we need to produce it differently
		if (!isset($details['email']) || (isset($details['email']) && !$details['email'])) {
			$details['email'] = $details['screen_name'];
		}

		// Give fields the ability to decorate user meta as well
		// This way fields can do extended api calls if the fields need it
		$args = array(&$details, &$this);
		$fieldsLib->trigger( 'onOAuthGetUserMeta', SOCIAL_FIELDS_GROUP_USER, $fields, $args );

		// We remap the id to oauth_id key
		$details['oauth_id'] = $details['id_str'];
		unset($details['id']);

		// Merge Facebook details into data array
		$data = array_merge($data, $details);

		// Generate a random password for the user.
		$data['password'] = JUserHelper::genRandomPassword();

		return $data;
	}

	/**
	 * Returns the verifier option. Since Facebook does not have oauth_verifier,
	 * The only way to validate this is through the 'code' query
	 *
	 * @return string	$verifier	Any string representation that we can verify it isn't empty.
	 **/
	public function getVerifier()
	{
		$verifier = $this->input->get('oauth_verifier' , '', 'default');
		return $verifier;
	}

	/**
	 * Retrieves the authorization url for Twitter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAuthorizationURL($callback = '')
	{
		$temporary = parent::getRequestToken($callback);

		$token = isset($temporary['oauth_token']) ? $temporary['oauth_token'] : '';

		return parent::getAuthorizeURL($token);
	}

	/**
	 * Retrieves the RequestToken for Twitter
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getRequestToken($callback = '')
	{
		$request = parent::getRequestToken($callback);

		$obj = new stdClass();
		$obj->token = $request['oauth_token'];
		$obj->secret = $request['oauth_token_secret'];

		return $obj;
	}

	/**
	 * Exchanges the verifier code with the access token.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getAccess($verifier = '')
	{
		// Get the temporary token from the query string
		$temporaryToken = $this->input->get('oauth_token', '', 'default');

		// Set temporary token
		$this->token = new SocialOAuthConsumer($temporaryToken, '');

		// Now let's try to get the access token.
		$accessToken = $this->getAccessToken($verifier);

		$obj = new stdClass();
		$obj->token = $accessToken['oauth_token'];
		$obj->secret = $accessToken['oauth_token_secret'];

		// Twitter sessions never expires (for now...)
		$obj->expires = 0;

		$params = ES::registry();
		$params->set('user_id', $accessToken[ 'user_id' ] );
		$params->set('screen_name', $accessToken[ 'screen_name' ] );

		$obj->params = $params->toString();

		return $obj;
	}

	/**
	 * Retrieves the user object from twitter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getUser()
	{
		static $user = null;

		if (is_null($user)) {
			// Get the access
			$user = parent::get('/account/verify_credentials', array('include_email' => 'true'));

			if (isset($user->errors)) {
				throw ES::exception($user->errors[0]->message, 500);
			}
		}

		return $user;
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

		return $user->id_str;
	}

	/**
	 * Allows caller to set the access
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setAccess($access, $secret)
	{
		$this->token = new SocialOAuthConsumer($access, $secret);
	}

	/**
	 * Allows caller to manually set the request token (used in rest api)
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function setOauthRequestToken($access, $secret)
	{
		parent::setRequestToken($access, $secret);
	}

	/**
	 * Posts a status update on Twitter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function push($message, $photo = null, $link = null)
	{
		// Check for image
		if ($photo) {

			// Absolute path of the image
			$photoPath = $photo->getPath('original');

			// Check for amazon storage
			if (!JFile::exists($photoPath)) {
				$amazonPath = $photo->getSource('large');

				// Crawl the image from amazon.
				$connector = ES::connector($amazonPath);
				$photoBinary = $connector
									->execute()
									->getResult();
			} else {
				$photoBinary = file_get_contents($photoPath);
			}

			// Only proceed if the image file is valid
			if (!$photoBinary) {
				return true;
			}

			$options = array(
						'media' => base64_encode($photoBinary)
					);

			// Upload the image to twitter
			$status = $this->post('https://upload.twitter.com/1.1/media/upload.json', $options);

			// Get media id of the image from twitter response
			if (isset($status->media_id_string) && $status->media_id_string) {
				$photo = $status->media_id_string;
			}
		}

		$message = $this->formatMessage($message, $link);

		$params = array('status' => $message);

		// Attach media id for available image
		if ($photo) {
			$params['media_ids'] = $photo;
		}

		$status = $this->post('statuses/update', $params);

		return true;
	}

	/**
	 * Format the message to avoid maximum tweet limit
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	public function formatMessage($message, $link = '')
	{
		// Current maximum tweet is 280 characters (23rd January 2018)
		$max = 275;

		// Count total characters of the link
		if ($link) {
			$max = $max - strlen($link);
		}

		$message = ESJString::substr($message, 0, $max);
		$message .= JText::_('COM_EASYSOCIAL_ELLIPSES');

		// Append the link at the end of message
		if ($link) {
			$message .= ' ' . $link;
		}


		return $message;
	}

	/**
	 * Renders a logout button
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getLogoutButton($callback)
	{
		// Check if the user has already authenticated.
		$table 	= ES::table( 'OAuth' );
		$exists	= $table->load( array( 'uid' => $uid , 'type' => $type ) );

		$theme->set( 'logoutCallback'	, $callback );
		$output 	= $theme->output( 'site/login/facebook.authenticated' );
	}

	/**
	 * Renders twitter login button
	 *
	 * @since	2.0
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

		if (!$config->get('oauth.twitter.registration.enabled')) {
			return;
		}

		$callbackOptions = array();
		$callbackOptions['layout'] = 'oauthRequestToken';
		$callbackOptions['client'] = 'twitter';
		$callbackOptions['callback'] = base64_encode($callback);

		$url = ESR::registration($callbackOptions, false);

		if (!$text) {
			$text = 'COM_EASYSOCIAL_SIGN_IN_WITH_TWITTER';
		}

		// only display icon without text
		if ($text == 'icon') {
			$text = '';
		}

		$theme = ES::themes();
		$theme->set('url', $url);
		$theme->set('size', $size);
		$theme->set('text', $text);

		$output = $theme->output('site/twitter/button');

		return $output;
	}
}
