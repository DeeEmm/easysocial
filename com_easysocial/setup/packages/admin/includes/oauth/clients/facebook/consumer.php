<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/facebook.php');

class SocialConsumerFacebook extends SocialFacebook implements ISocialOAuth
{
	private $oauth_appId = '';
	private $oauth_appSecret = '';

	/**
	 * Determines the type of oauth client.
	 * @var string
	 */
	private $type = 'facebook';

	/**
	 * Stores the permissions mapping
	 * Permissions should not be premapped, it should be retrieved based on fields/apps
	 * See getAuthorizeURL and getUserMeta
	 * @var Array
	 */
	// private $permissions 		= array(
	// 	'registration' => array(
	// 		'user_about_me',
	// 		'email',
	// 		'publish_actions',
	// 		'publish_stream'
	// 		'create_note',
	// 		'photo_upload',
	// 		'read_stream',
	// 		'share_item',
	// 		'status_update',
	// 		'user_activities',
	// 		'user_birthday',
	// 		'user_friends',
	// 		'user_hometown',
	// 		'user_interests',
	// 		'user_location',
	// 		'user_photos',
	// 		'user_status',
	// 		'user_website',
	// 		'user_work_history',
	// 		'video_upload'
	// 	)
	// );

	/**
	 * Class constructor
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct($key, $secret, $callback)
	{
		// Initialize the parent object with appropriate data.
		parent::__construct(array('appId' => $key, 'secret' => $secret, 'cookie' => true));
	}

	/**
	 * Pulls user's stream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function pull($limit = null)
	{
		$options = array('limit' => 25);

		$result = $this->api('/me/feed', $options);
		$items = $result['data'];

		// We need to format the items to be our own format.
		$items = $this->format($items);

		return $items;
	}

	/**
	 * Some desc
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addPermission($permission)
	{
		// Get the user
		$oauthId = $this->getUserId();

		// Load the table as we need to update with the new permissions
		$oauthTable = ES::table('OAuth');
		$oauthTable->load(array('oauth_id' => $oauthId));

		$permissions = ES::makeArray($oauthTable->permissions);
		$permissions[] = $permission;

		// publish_actions scope permission was deprecated on April 24, 2018
		if ($permission == 'publish_actions') {
			$oauthTable->push = true;
		}

		$oauthTable->permissions = ES::makeJSON($permissions);

		// Store the permission here.
		$oauthTable->store();
	}

	/**
	 * Removes a permission
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removePermissions($scope)
	{
		$state = $this->api('/me/permissions/' . $scope, 'delete');

		// Update the permissions list.
		$oauthId = $this->getUserId();

		// Load the table as we need to update with the new permissions
		$oauthTable = ES::table('OAuth');
		$oauthTable->load(array('oauth_id' => $oauthId));

		$permissions = ES::json()->decode($oauthTable->permissions);
		$index = array_search($scope, $permissions, false );

		if ($index !== false) {
			unset($permissions[$index]);
		}

		$permissions = array_values($permissions);

		if ($scope == 'publish_actions') {
			$oauthTable->push = false;
		}

		$oauthTable->permissions = ES::makeJSON($permissions);

		// Store the permission here.
		$oauthTable->store();
	}

	/**
	 * Retrieves the person's profile picture
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAvatar()
	{
		$avatar = $this->api('me/picture', array('width' => '200', 'height' => '200', 'redirect' => false));
		$url = $avatar['data']['url'];

		return $url;
	}

	/**
	 * Retrieves permissions the user has
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPermissions()
	{
		$result = $this->api('/me/permissions');
		$permissions = array_keys($result['data'][0]);

		return $permissions;
	}

	/**
	 * Push an item to Facebook
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function push($message, $placeId = null, $photo = null, $link = null)
	{
		$options = array();

		if ($placeId) {
			$options['place'] = $placeId;
		}

		if (is_object($link)) {
			$options['link'] = $link->get('link');
			$options['title'] = $link->get('title');
			$options['content'] = $message;

			if ($photo) {
				$photoUrl = $photo->getSource('thumbnail');

				// If there is a cdn url, we need to replace it
				$cdn = ES::getCdnUrl();

				if ($cdn) {
					$photoUrl = str_ireplace($cdn, JURI::root(), $photoUrl);
				}

				$options['picture']	= $photoUrl;
			}
		}

		if (isset($options['picture']) && $options['picture']) {
			$photoUrl = ltrim($options['picture'], '//');

			// Normalize the url
			if (stristr($photoUrl, 'http://') === false && stristr($photoUrl, 'https://') === false) {
				$photoUrl = 'http://' . $photoUrl;
			}

			$options['picture'] = $photoUrl;
		}

		try {
			$result = $this->api('/me/feed', 'POST', $options);
		} catch (Exception $e) {
			$result = false;
		}

		if ($result) {
			return $result['id'];
		}

		return false;
	}


	/**
	 * Format Facebook stream items
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function format( $items )
	{
		$result	= array();

		$model 	= ES::model( 'OAuth' );

		// echo '<pre>';
		// print_r( $items );
		// echo '</pre>';
		// exit;

		foreach( $items as $item )
		{
			// Get the type
			$type 	= $item[ 'type' ];

			$obj 	= ES::registry();

			$obj->set( 'id'	, $item[ 'id' ] );
			$obj->set( 'with' , null );
			$obj->set( 'type' , $type );
			$obj->set( 'content' , '' );
			$obj->set( 'created' , $item[ 'created_time' ] );

			$file 	= dirname( __FILE__ ) . '/opengraph/' . $type . '.php';

			// Skip this item if the file doesn't exist.
			if( !JFile::exists( $file ) )
			{
				continue;
			}

			require_once( $file );

			$graphClass = 'SocialFacebook' . ucfirst( $type );
			$graphObj	= new $graphClass();

			// Process the graph item
			$state 		= $graphObj->process( $obj , $item , $this->getUserId() );

			if( $state === false )
			{
				continue;
			}

			// Replace names in the content if there are any story_tags
			if( isset( $item[ 'story_tags' ] ) )
			{
				$storyTags 	= $item[ 'story_tags' ];

				// Reverse the ordering
				$storyTags	= array_reverse( $storyTags );

				// Store data in temporary array
				$userStoryTags	= array();
				foreach( $storyTags as $tags => $users )
				{
					foreach( $users as $user )
					{
						$userName 	= $user[ 'name' ];
						$userId 	= $user[ 'id' ];
						$info 		= $this->api( '/' . $userId , array( 'fields' => 'id,name,link') );

						// Get the offset and length for the object.
						$offset 	= $user[ 'offset' ];
						$length 	= $user[ 'length' ];

						$userStoryTags[]	= array( 'link' => $info[ 'link' ] , 'name' => $info[ 'name' ] , 'offset' => $offset , 'length' => $length );
					}
				}

				$obj->set( 'story_tags' , $userStoryTags );
			}

			// If user specified that they are with certain users, we should update the data here.
			if( isset( $item[ 'with_tags' ] ) )
			{
				$withData 		= $item[ 'with_tags' ][ 'data' ];
				$userWithData	= array();

				foreach( $withData as $user )
				{
					// Find if there's an oauth user that is linked to an account.
					$oauthName	= $user[ 'name' ];
					$oauthId	= $user[ 'id' ];

					$info 			= $this->api( '/' . $oauthId , array( 'fields' => 'id,name,gender,email,username,picture,cover,timezone,education,location,website,work,link') );

					$userWithData[] = array( 'link' => $info[ 'link' ] , 'name' => $info[ 'name' ] );
				}
				$obj->set( 'with_data' , $userWithData );
			}

			$result[]	= $obj;

		}

		// echo '<pre>';
		// print_r( $result );
		// echo '</pre>';
		// exit;

		return $result;
	}

	/**
	 * Return client type
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Determines if the current logged in user on Facebook is already registered on the site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isRegistered()
	{
		// Try to check if external user id exists on the site.
		$oauthTable = ES::table('OAuth');

		// Get external user id.
		$userId = $this->getUserId();

		$state = $oauthTable->load(array('oauth_id' => $userId, 'client' => $this->getType()));

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
		$options = array('oauth_id' => $this->getUserId(), 'client' => 'facebook');
		$table->load($options);

		return $table->uid;
	}

	/**
	 * Retrieves the user id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserId()
	{
		$id = parent::getUser();

		return $id;
	}

	/**
	 * Gets the login credentials for the Joomla site.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLoginCredentials()
	{
		$table 	= ES::table( 'OAuth' );
		$state 	= $table->load( array( 'oauth_id' => $this->getUser() , 'client' => $this->getType() ) );

		if( !$state )
		{
			return false;
		}

		// Get the user object.
		$user 			= ES::user( $table->uid );
		$credentials 	= array( 'username' => $user->username , 'password' => $user->password );

		return $credentials;
	}

	/**
	 * Revokes the user's access
	 *
	 * @since	1.4.10
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function revoke()
	{
		// Try to revoke the access
		try {

			$result = parent::api('/me/permissions', 'delete');

		} catch(Exception $e) {

			// There are instances where the user's token has already expired and it doesn't make sense to throw an error.
			$result = true;
		}


		return $result;
	}

	/**
	 * Updates the access token
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function updateToken()
	{
		// We need to update with the new access token here.
		$session = JFactory::getSession();
		$accessToken = $session->get('facebook.access', '', SOCIAL_SESSION_NAMESPACE);

		$options = array('oauth_id' => $this->getUser() , 'client' => $this->getType());

		$table = ES::table('OAuth');
		$state = $table->load($options);

		if (!$state) {
			return false;
		}

		// Try to update with the new token
		$table->token = $accessToken->token;

		$state = $table->store();

		return $state;
	}

	/**
	 * Retrieves the access token from Facebook
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAccess($verified = '', $code = '')
	{
		// Get the access token for the user.
		$token = $this->getUserAccessToken();
		$config = ES::config();

		$redirect_uri = ESR::oauthRedirectUri();

		// if Facebook still can't generate the token to us, then we need to request again
		if ($token === false && $code) {

			$params = array( 'client_id' => $config->get('oauth.facebook.app'),
							 'redirect_uri'	=> $redirect_uri,
							 'client_secret'=> $config->get('oauth.facebook.secret'),
							 'code'	=> $code
							);

			$token = parent::_oauthRequest(parent::getUrl('graph', '/oauth/access_token'), $params);

			$token = json_decode($token);
			$access = $token->access_token;

			$obj = new stdClass();
			$obj->token = $access;
			$obj->expires = '';
			$obj->secret = '';

			return $obj;
		}

		$obj = new stdClass();
		$obj->token = $token;
		$obj->expires = '';
		$obj->secret = '';

		return $obj;
	}

	/**
	 * Given the access token and secret, set the access token to the parent.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setAccess($access, $secret = '')
	{
		return $this->setAccessToken( $access );
	}

	/**
	 * Retrieves user details
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getUserMeta()
	{
		// Empty user meta data
		$data = array();

		// Load internal configuration
		$config = ES::config();

		// Get the default profile
		$profile = ES::oauth()->getDefaultProfile('facebook');

		// Assign the profileId first
		$data['profileId'] = $profile->id;

		// We need the basic id from Facebook
		$fbFields = array( 'id' );

		// We let field decide which fields they want from facebook
		$fields = $profile->getCustomFields();
		$args = array( &$fbFields, &$this );
		$fieldsLib = ES::fields();
		$fieldsLib->trigger( 'onOAuthGetMetaFields', SOCIAL_FIELDS_GROUP_USER, $fields, $args );

		// Unique it to prevent multiple same fields request
		$fbFields = array_unique( (array) $fbFields );

		// Implode it into a string for request
		$fbFields = implode( ',', $fbFields );

		// Let's try to query facebook for more details.
		$details = $this->api( '/me' , array( 'fields' => $fbFields ) );

		// Give fields the ability to decorate user meta as well
		// This way fields can do extended api calls if the fields need it
		$args = array( &$details, &$this );
		$fieldsLib->trigger( 'onOAuthGetUserMeta', SOCIAL_FIELDS_GROUP_USER, $fields, $args );

		// We remap the id to oauth_id key
		$details['oauth_id'] = $details['id'];
		unset( $details['id'] );

		// Merge Facebook details into data array
		$data = array_merge( $data, $details );

		// Generate a random password for the user.
		$data['password']	= JUserHelper::genRandomPassword();

		return $data;
	}

	/**
	 * Retrieves the authorization url
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAuthorizeURL($params)
	{
		$config = ES::config();
		// Check if there are custom scope passed in
		$scopes = isset($params['scope']) ? $params['scope'] : array();
		$permissions = array();

		if ($scopes) {
			// Ensure that it's in an array
			$scopes	= ES::makeArray($scopes);

			$permissions = array_merge($permissions, $scopes);
		}

		// Story autoposting permission
		// publish_actions scope permission was deprecated on April 24, 2018
		$permissions[] = 'publish_actions';

		// We let fields add in permissions
		$args = array(&$permissions);
		$profile = ES::oauth()->getDefaultProfile('facebook');
		$fields = $profile->getCustomFields();

		$fieldsLib = ES::fields();
		$fieldsLib->trigger('onOAuthGetUserPermission', SOCIAL_FIELDS_GROUP_USER, $fields, $args);

		// Determine which scopes permission should use
		$hasScopesSelected = $config->get('oauth.facebook.scopes');

		if ($hasScopesSelected) {
			$hasScopesSelected = explode(',', $hasScopesSelected);
		}

		// returns an array containing all of the values whose values exist
		$permissions = array_intersect($hasScopesSelected, $permissions);

		// Reset the scope
		$params['scope'] = array_unique((array) $permissions);

		// if (!isset($params['display'])) {
		// 	$params['display'] = 'popup';
		// }

		// Encode the return_to if exists
		if (isset($params['return_to'])) {
			$params['return_to'] = base64_encode($params['return_to']);
		}

		// Determine and fix the redirect uri if necessary.
		if (isset($params['redirect_uri'])) {

			$uri = $params['redirect_uri'];

			// Check if there is http:// or https:// in the url.
			if (stristr($uri, 'http://') === false && stristr($uri , 'https://') === false ) {

				// If it doesn't exist, always pull from the site.
				$uri = JURI::root() . $uri;

				$params['redirect_uri']	= $uri;
			}
		}

		$url = $this->getLoginUrl($params);

		return $url;
	}

	/**
	 * Retrieves auto login scripts
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getAutologinScripts()
	{
		$script = ES::script();
		$output = $script->output('site/facebook/autologin');

		return $output;
	}

	/**
	 * Renders a logout button
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getRevokeButton($callback)
	{
		$theme = ES::themes();
		$theme->set('callback', $callback);
		$output = $theme->output('site/facebook/revoke');

		return $output;
	}

	/**
	 * Renders a login button.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLoginButton($callback, $permissions = array(), $display = 'popup', $text = '', $size = 'btn-sm btn-block', $isSharer = false)
	{
		// Test if the config has been created correctly.
		$config = ES::config();
		$jfbconnect = ES::jfbconnect();

		// Check if JFBConnect is enabled
		if ($jfbconnect->isEnabled()) {
			return $jfbconnect->getLoginButton();
		}

		if (!$config->get('oauth.facebook.registration.enabled')) {
			return;
		}

		if (!$config->get('oauth.facebook.app') || !$config->get('oauth.facebook.secret')) {
			return;
		}

		$theme = ES::themes();

		// Load front end language file.
		ES::language()->loadSite();

		if (empty($text)) {
			$text = 'COM_EASYSOCIAL_OAUTH_SIGN_IN_WITH_FACEBOOK';
		}

		// only display icon without text
		if ($text == 'icon') {
			$text = '';
		}

		// Normalise the authorize URL since Facebook API March 2018 no longer accept any extra parameters
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

		// the reason why we hardcoded this non-sef callback pass to Facebook is because if pass to SEF URL, it might be a lot of possibilities doesn't match the Facebook Oauth redirect URI
		$callback = 'index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=facebook';

		if ($isSharer) {
			$callback .= '&sharer=' . $isSharer;
		}

		$authorizeURL = $this->getAuthorizeURL(array('scope' => $permissions, 'redirect_uri' => $callback));

		$theme->set('text', $text);
		$theme->set('authorizeURL', $authorizeURL);
		$theme->set('appId', $this->appId);
		$theme->set('appSecret', $this->appSecret);
		$theme->set('callback', $callback);
		$theme->set('permissions', $permissions);
		$theme->set('size', $size);

		$output = $theme->output('site/facebook/button');

		return $output;
	}

	/**
	 * Generates the login url to facebook
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function getLoginRedirection($callback = '', $permissions = array(), $display = 'popup')
	{
		$config = ES::config();

		if (!$config->get('oauth.facebook.app') || !$config->get('oauth.facebook.secret')) {
			return;
		}

		if (!$callback) {
			$callback = 'index.php?option=com_easysocial&view=registration&layout=oauthLogin&client=facebook';
		}

		// Get default return url
		$returnURL = ESR::getMenuLink($config->get('general.site.login'));

		// If return value is empty, always redirect back to the current page
		if (!$returnURL || $returnURL == 'null') {
			$returnURL = ESR::getCurrentURI();
		}

		// Ensure that the return url is always encoded correctly.
		$returnCode = base64_encode($returnURL);

		// Set the redirection url on the session
		$session = JFactory::getSession();
		$session->set('oauth.autologin.redirection', $returnCode, SOCIAL_SESSION_NAMESPACE);

		$url = $this->getAuthorizeURL(array('scope' => $permissions, 'redirect_uri' => $callback));

		return $url;
	}
}
