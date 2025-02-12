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

use Joomla\CMS\Encrypt\Aes;
use Joomla\Component\Users\Administrator\Helper\Mfa;

ES::import('admin:/tables/table');
ES::import('admin:/includes/indexer/indexer');

require_once(__DIR__ . '/helpers/joomla.php');

class SocialUser extends JUser
{
	public $id = '';
	public $name = null;
	public $username = '';
	public $email = null;
	public $password = null;
	public $usertype = null;
	public $block = null;
	public $sendEmail = null;
	public $registerDate = null;
	public $otpKey = null;
	public $otep = null;
	public $lastvisitDate = '';
	public $activation = null;
	public $params = '';
	public $privacy = null;
	public $connections = 0;
	public $param = null;
	public $state = null;
	public $verified = null;
	public $affiliation_id = null;
	public $robots = null;
	public $profile_id = null;

	/**
	 * User's avatar id (from gallery). Stored in `#__social_avatars` table.
	 * @var int
	 */
	public $avatar_id = null;

	/**
	 * User's avatar id (from uploaded photos). Stored in `#__social_avatars` table.
	 * @var int
	 */
	public $photo_id = null;

	/**
	 * User's permalink
	 * @var string
	 */
	public $permalink = null;

	/**
	 * User's online status. This isn't stored anywhere. It's just loaded
	 * initially, to let other's know of the user's online state.
	 * @var int
	 */
	public $online = null;

	/**
	 * User's alias.
	 *
	 * @var string
	 */
	public $alias = null;

	public $config = null;

	/**
	 * User's authentication code.
	 *
	 * @var string
	 */
	public $auth 		= null;

	/*
	 * Custom values
	 */
	public $password_clear   = null;

	public $reminder_sent = null;

	public $require_reset = null;

	public $block_period = null;

	public $block_date = null;

	public $social_params = null;

	public $es_params = null;


	// Default avatar sizes
	public $avatarSizes	= array('small' , 'medium' , 'large' , 'square');

	// Avatars
	public $avatars 		= array('small' 	=> '',
									 'medium' 	=> '',
									 'large'	=> '',
									 'square'	=> ''
									);

	// Cover Photo
	public $cover 			= null;

	/**
	 * Stores the default avatar property if exists.
	 * @var SocialTableDefaultAvatar
	 */
	public $defaultAvatar	= null;

	/**
	 * The user's points
	 * @var int
	 */
	public $points 		= 0;

	/**
	 * Stores the user type.
	 * @var	string
	 */
	public $type = 'joomla';

	/**
	 * Social goals.
	 * @var	array
	 */
	public $goalsList = array(
				'updateavatar' => 'hasAvatar',
				'completeprofile' => 'hasCompletedProfile',
				'poststatus' => 'hasStatusUpdate',
				'addfriend' => 'hasFriends',
				'postcomment' => 'hasCommentPost',
				'joincluster' => 'hasClusters'
			);

	/**
	 * User Config.
	 * @var	string
	 */
	private $userConfig = array();

	/**
	 * Keeps a list of users that are already loaded so we
	 * don't have to always reload the user again.
	 * @var Array
	 */
	static $userInstances = array();

	/**
	 * Keeps a list of super admin ids.
	 * @var Array
	 */
	static $admins = array();

	/**
	 * Stores user badges
	 * @var Array
	 */
	protected $badges = array();

	/**
	 * Helper object for various cms versions.
	 * @var	object
	 */
	protected $helper = null;

	/**
	 * Determines the storage type for the avatars
	 * @var string
	 */
	protected $avatarStorage = 'joomla';

	/**
	 * Determines the number of fields completed for this user in this profile.
	 * @var integer
	 */
	public $completed_fields = 0;

	public function __construct($params = array(), $debug = false)
	{
		// Initialize helper object.
		$this->helper = new SocialUserHelperJoomla($this);

		// // Create the user parameters object
		$this->_params = new JRegistry;

		// Initialize user's property locally.
		$this->initParams($params);

		// Initialize global config
		$this->config = ES::config();
		$this->guest = false;

		if (!$this->id) {
			$this->guest = true;
		}
	}

	/**
	 * Bans a user from the site
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function ban($period = 0, $reason = null)
	{
		$model = ES::model('Users');

		// Block the user from Joomla
		$state = $this->block();

		// we need to update our own block_period column.
		if ($state) {
			$model->updateBlockInterval(array($this->id), $period);
		}

		// Retrieve the current logged in user who ban this user account
		$user = ES::user();
		$bannedBy = $user->getName();
		$bannedByUserPermalink = $user->getPermalink();

		// Send notification to admin
		// Push arguments to template variables so users can use these arguments
		$params	= array(
						'name' => $this->getName(),
						'avatar' => $this->getAvatar(SOCIAL_AVATAR_MEDIUM),
						'profileLink' => JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=form&id=' . $this->id,
						'userProfileLink' => $this->getPermalink(),
						'date' => ES::date()->format(JText::_('COM_EASYSOCIAL_DATE_DMY')),
						'totalFriends' => $this->getTotalFriends(),
						'totalFollowers' => $this->getTotalFollowers(),
						'reason' => $reason,
						'profileType' => $this->getProfile()->title,
						'period' => $period,
						'bannedBy' => $bannedBy,
						'bannedByUserPermalink' => $bannedByUserPermalink
				);

		ES::language()->loadSite();

		// Get a list of super admins on the site.
		$admins = $model->getSystemEmailReceiver();
		$mailer = ES::mailer();

		// Email title for admin
		if ($period == 0) {
			$titleAdmin = JText::sprintf('COM_EASYSOCIAL_EMAILS_ADMIN_USER_BANNED_TITLE', $this->getName());
		} else {
			$titleAdmin = JText::sprintf('COM_EASYSOCIAL_EMAILS_ADMIN_USER_BANNED_FOR_X_PERIOD_TITLE', $this->getName(), $period);
		}

		if ($admins) {
			foreach ($admins as $admin) {
				$params['adminName'] = $admin->name;

				$template = $mailer->getTemplate();
				$template->setRecipient($admin->name, $admin->email);
				$template->setTitle($titleAdmin);
				$template->setTemplate('site/profile/account.blocked', $params);
				$template->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

				// Try to send out email to the admin now.
				$state = $mailer->create($template);
			}
		}

		// Email title for user
		if ($period == 0) {
			$titleUser = JText::sprintf('COM_EASYSOCIAL_EMAILS_USER_BEING_BANNED_PERMANENTLY_TITLE');
		} else {
			$titleUser = JText::sprintf('COM_EASYSOCIAL_EMAILS_USER_BEING_BANNED_TITLE', $period);
		}

		// Send the email to the user being banned as well
		$params['userName'] = $this->getName();
		$temp = $mailer->getTemplate();
		$temp->setRecipient($this->getName(), $this->email);
		$temp->setTitle($titleUser);
		$temp->setTemplate('site/profile/useraccount.blocked', $params);
		$temp->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

		$mailer->create($temp);
	}

	/**
	 * Unbans user from the site
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function unban()
	{
		// Unblock the user
		$this->unblock();

		$model = ES::model('Users');

		return $model->updateBlockInterval(array($this->id), '0');
	}

	/**
	 * Deteremine if this user is banned from the site
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	public function isBanned()
	{
		// Deteremine the state of the user
		if ($this->block == SOCIAL_JOOMLA_USER_BLOCKED && $this->state == SOCIAL_USER_STATE_DISABLED) {
			return true;
		}

		return false;
	}

	/**
	 * Blocks a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function block()
	{
		// Set juser data first
		$this->block = SOCIAL_JOOMLA_USER_BLOCKED;

		// Set our own state data
		$this->state = SOCIAL_USER_STATE_DISABLED;

		// Save the user after updating their blocked state
		$state = $this->save();

		// After blocking a user, synchronize with finder
		$this->syncIndex();

		// Log the user out
		$app = JFactory::getApplication();
		$app->logout($this->id, array('clientid' => 0));

		return $state;
	}

	/**
	 * Blocks a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function unblock()
	{
		// Set juser data first
		$this->block = SOCIAL_JOOMLA_USER_UNBLOCKED;

		// Set our own state data
		$this->state = SOCIAL_USER_STATE_ENABLED;

		// When user is unbanned, we also want to remove any block_period from the table
		$this->block_period = 0;
		$this->block_date = null;

		// onBeforeUnblock

		$state = $this->save();

		// After unblocking a user, we need to sync the index again
		$this->syncIndex();

		return $state;
	}

	/**
	 * Determines if this user is blocked
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isBlock()
	{
		return (bool) $this->block;
	}

	/**
	 * Determines if this user is blocked by another user
	 *
	 * @since	1.3
	 * @access	public
	 * @return	bool	True if user is blocked, false otherwise
	 */
	public function isBlockedBy($id, $twoWay = false)
	{
		if (!$this->config->get('users.blocking.enabled') || $this->isSiteAdmin()) {
			return false;
		}

		static $cache = array();

		// Index needs to be
		$index = $this->id . $id . (int) $twoWay;

		if (!isset($cache[$index])) {
			$model = ES::model('Blocks');

			$cache[$index] = (bool) $model->isBlocked($id, $this->id, $twoWay);
		}

		return $cache[$index];
	}

	/**
	 * Assign this user to a group
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function assign($gid)
	{
		$model = ES::model('Users');

		$model->assignToGroup($this->id , $gid);
	}

	/**
	 * Initializes the provided properties into the existing object. Instead of
	 * trying to query to fetch more info about the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function initParams(&$params, $debug = false)
	{
		// Get all properties of this object
		$properties = get_object_vars($this);

		// Bind parameters to the object
		foreach ($properties as $key => $val) {
			if (isset($params->$key)) {
				$this->$key = $params->$key;
			}
		}

		// we need to do a checking on the json before we pass to Joomla for decoding since
		// Joomla! 3.6.3 throw error if the json string is invalid.
		if ($this->params && !ES::json()->isJsonString($this->params)) {
			$this->params = '';
		}

		// // Bind params json object here
		$this->_params->loadString($this->params);

		// Bind user avatars here.
		foreach ($this->avatars as $size => $value) {
			if (isset($params->$size)) {
				$this->avatars[$size] = $params->$size;
			}
		}

		// set the list of user groups
		$this->groups = $this->helper->getUserGroups();

	}

	/**
	 * Object initialisation for the class to fetch the appropriate user
	 * object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory($ids = null, $debug = false, $cacheUser = null)
	{
		$items = self::loadUsers($ids, $debug, $cacheUser);

		return $items;
	}

	/**
	 * Removes a favourite story type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function removeFavouriteStory($plugin)
	{
		$preferences = $this->getUserPreferences();

		if (!in_array($plugin, $preferences) || !$preferences) {
			return true;
		}

		$key = array_search($plugin, $preferences);

		if ($key !== false) {
			unset($preferences[$key]);
			$preferences = array_values($preferences);
		}

		$params = $this->getEsParams();
		$params->set('story', $preferences);

		$table = ES::table('Users');
		$table->load(array('user_id' => $this->id));
		$table->params = $params->toString();

		return $table->store();
	}

	/**
	 * Inserts a new favourite story type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function addFavouriteStory($plugins)
	{
		$preferences = $this->getUserPreferences();
		$plugins = !is_array($plugins) ? array($plugins) : $plugins;

		if (in_array($plugins, $preferences)) {
			return true;
		}

		$preferences = array_merge($preferences, $plugins);

		// Ensure that they are unique
		$preferences = array_unique($preferences);

		$params = $this->getEsParams();
		$params->set('story', $preferences);

		$table = ES::table('Users');
		$table->load(array('user_id' => $this->id));
		$table->params = $params->toString();

		return $table->store();
	}

	/**
	 * Insert user profile search indexing value in user params
	 *
	 * @since	3.2.1
	 * @access	public
	 */
	public function addUserRobots($robots = 'index')
	{
		// $params = $this->getEsParams();
		// $params->set('robots', $robots);

		// $table = ES::table('Users');
		// $table->load(array('user_id' => $this->id));
		// $table->params = $params->toString();


		// update local attributes
		$this->robots = $robots;
		$table = ES::table('Users');
		$table->load(array('user_id' => $this->id));
		$table->robots = $robots;

		return $table->store();
	}

	/**
	 * Processes user related stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addStream($verb)
	{
		$config = ES::config();

		if ($verb == 'uploadAvatar') {
			// Add stream item when a new photo is uploaded.
			$stream				= ES::stream();
			$streamTemplate		= $stream->getTemplate();

			// Set the actor.
			$streamTemplate->setActor($this->id , SOCIAL_TYPE_USER);

			// Set the context.
			$streamTemplate->setContext($this->id , SOCIAL_TYPE_PHOTO);

			// Set the verb.
			$streamTemplate->setVerb('add');

			$streamTemplate->setAccess('photos.view');


			//
			$streamTemplate->setType('full');

			// Create the stream data.
			$stream->add($streamTemplate);
		}

		if ($verb == 'updateProfile') {
			// Add stream item when a new photo is uploaded.
			$stream				= ES::stream();
			$streamTemplate		= $stream->getTemplate();

			// Set the actor.
			$streamTemplate->setActor($this->id , SOCIAL_TYPE_USER);

			// Set the context.
			$streamTemplate->setContext($this->id , SOCIAL_TYPE_PROFILES);

			// Set the verb.
			$streamTemplate->setVerb('update');


			$streamTemplate->setAggregate(true);


			$streamTemplate->setAccess('core.view');


			// Set stream style
			$streamTemplate->setType('mini');

			// Create the stream data.
			$stream->add($streamTemplate);
		}
	}

	/**
	 * Retrieves a one time unique token to exchange for a valid mobile token
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function getOnetimeMobileToken($refresh = false)
	{
		$params = $this->getEsParams();
		$token = $params->get('onetime_mobile', '');

		if (!$token || $refresh) {
			// Generate the hash
			$token = md5($this->email . JFactory::getDate()->toSql() . $this->password);

			$params->set('onetime_mobile', $token);

			$this->updateParams($params);
		}

		return $token;
	}

	/**
	 * Add logging when user login via rest api.
	 *
	 * @since	4.0.1
	 * @access	public
	 */
	public function logRestApiLogin()
	{
		$session = JFactory::getSession();
		$sessId = $session->getId();

		$tbl = ES::table('Restapilogin ');
		$tbl->load(array('session_id' => $sessId, 'user_id' => $this->id));

		if (!$tbl->id) {
			$tbl->session_id = $sessId;
			$tbl->user_id = $this->id;
			$tbl->created = ES::date()->toSql();

			$tbl->store();
		}
	}


	public function removeRestSessionLogs()
	{
		$model = ES::model('Users');
		$model->removeRestSessionLogs($this->id);

		return true;
	}


	/**
	 * Retrieves a one time unique token to exchange for a valid mobile token
	 *
	 * @since	3.2.19
	 * @access	public
	 */
	public function getMobileQrcodeData()
	{
		$token = $this->getOnetimeMobileToken();
		$base = rtrim(JURI::root(), '/');
		$namespace = '?url=' . $base . '&username=' . $this->username . '&mobile_otp=' . $token;
		$namespace = base64_encode($namespace);

		$namespace = '?origin=easysocial&data=' . $namespace;
		$namespace .= '&' . ES::date()->toUnix() . '=1';

		return $namespace;
	}

	/**
	 * Retrieves a list of apps for a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getApps($view)
	{
		static $apps = array();

		if (!isset($apps[$this->id][$view])) {
			$model = ES::model('Apps');
			$options = array('view' => $view , 'uid' => $this->id , 'key' => SOCIAL_TYPE_USER);
			$userApps = $model->getApps($options);

			$apps[$this->id][$view] = $userApps;
		}

		return $apps[$this->id][$view];
	}

	/**
	 * Creates a guest object and store them into the property as static instance.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function createGuestObject()
	{
		static $guest = null;

		if (is_null($guest)) {
			$table = ES::table('Users');
			$data = array();

			$guest = new self($table, $data);
			$guest->id = 0;
			$guest->name = JText::_('COM_EASYSOCIAL_GUEST_NAME');

			SocialUserStorage::$users[0] = $guest;
		}

		return $guest;
	}

	/**
	 * Reloads the cache for custom field values when the user profile changes.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function reloadFields()
	{
		if (!$this->id) {
			// to prevent any 3rd party app / plugin call this function on the 'onBeforeSave' trigger
			// which might lead to issues due to the user records might not created yet.
			// #4185

			return;
		}

		$model = ES::model('Users');
		SocialUserStorage::$fields[$this->id] = $model->initUserData($this->id);
	}

	/**
	 * Removes a user item from the cache
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function removeFromCache()
	{
		// Remove from user's storage cache
		unset(SocialUserStorage::$users[$this->id]);

		// Remove it from the model's cache too
		unset(EasySocialModelUsers::$loadedUsers[$this->id]);
	}

	/**
	 * Loads a given user id or an array of id's.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function loadUsers($ids = null, $debug = false, $cacheUser = null)
	{
		static $currentUserId = null;

		// Ensure that is null or 0, the caller might be want to retrieve the current logged in user
		// Like those user cookies still stored from their browser, need to load back those user data.
		if (is_null($currentUserId) || !$currentUserId) {
			$currentUserId = JFactory::getUser()->id;
		}

		// Determine if the argument is an array.
		$argumentIsArray = is_array($ids);

		// If it is null or 0, the caller wants to retrieve the current logged in user.
		if (is_null($ids) || (is_string($ids) && $ids == '')) {
			$ids = array($currentUserId);
		}

		// Cache the users if needed
		if ($cacheUser) {
			$currentUserId = $ids;
		}

		// Ensure that id's are always an array
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		// Reset the index of ids so we don't load multiple times from the same user.
		$ids = array_values($ids);

		// Always create the guest objects first.
		self::createGuestObject();

		// Total needs to be computed here before entering iteration as it might be affected by unset.
		$total = count($ids);

		// Placeholder for items that are already loaded.
		$loaded = array();

		// @task: We need to only load user's that aren't loaded yet.
		for ($i = 0; $i < $total; $i++) {

			if (empty($ids)) {
				break;
			}

			if (!isset($ids[$i]) && empty($ids[$i])) {
				continue;
			}

			$id = $ids[$i];

			// If id is null, we know we want the current user.
			if (is_null($id)) {
				$ids[$i] = $currentUserId;
			}

			// The parsed id's could be an object from the database query.
			if (is_object($id) && isset($id->id)) {
				$id = $id->id;

				// Replace the current value with the proper value.
				$ids[$i] = $id;
			}

			if (isset(SocialUserStorage::$users[$id])) {
				$loaded[] = $id;
				unset($ids[$i]);
			}

		}

		// Reset the ids after it was previously unset.
		$ids = array_values($ids);

		// Place holder for result items.
		$result	= array();

		foreach ($loaded as $id) {
			$result[] = SocialUserStorage::$users[$id];
		}

		if (!empty($ids)) {

			// Retrieve user's data
			$model = ES::model('Users');
			$users = $model->getUsersMeta($ids);

			// Iterate through the users list and add them into the static property.
			if ($users) {

				foreach ($users as $user) {
					// Get the user's cover photo
					$user->cover = self::getCoverObject($user);

					// Detect if the user has an avatar.
					$user->defaultAvatar = false;

					if ($user->avatar_id) {
						$defaultAvatar = ES::table('DefaultAvatar');
						$defaultAvatar->load($user->avatar_id);
						$user->defaultAvatar = $defaultAvatar;
					}

					// Try to load the user from `#__social_users`
					// If the user record doesn't exists in #__social_users we need to initialize it first.
					if (!$model->metaExists($user->id)) {
						$model->createMeta($user->id);
					}

					// Attach fields for this user.
					// SocialUserStorage::$fields[$user->id]	= $model->initUserData($user->id);

					// Get user's badges
					// SocialUserStorage::$badges[$user->id]	= ES::model('Badges')->getBadges($user->id);

					// Create an object of itself and store in the static object.
					$obj = new SocialUser($user);

					// Initialize user config
					$obj->loadConfig();

					SocialUserStorage::$users[$user->id] = $obj;

					$result[] = SocialUserStorage::$users[$user->id];
				}
			} else {

				foreach ($ids as $id) {
					// Since there are no such users, we just use the guest object.
					SocialUserStorage::$users[$id] = SocialUserStorage::$users[0];

					$result[] = SocialUserStorage::$users[$id];
				}
			}
		}

		// If the argument passed in is not an array, just return the proper value.
		if (!$argumentIsArray && count($result) == 1) {
			return $result[0];
		}

		return $result;
	}

	/**
	 * Bind the cover object
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function getCoverObject($user)
	{
		$cover = ES::table('Cover');

		if (!empty($user->cover_id)) {
			$coverData = new stdClass();
			$coverData->id = $user->cover_id;
			$coverData->uid = $user->cover_uid;
			$coverData->type = $user->cover_type;
			$coverData->photo_id = $user->cover_photo_id;
			$coverData->cover_id = $user->cover_cover_id;
			$coverData->x = $user->cover_x;
			$coverData->y = $user->cover_y;
			$coverData->modified = $user->cover_modified;

			$cover->bind($coverData);
		} else {
			// Type is always user for this object.
			$cover->type = SOCIAL_TYPE_USER;
		}

		return $cover;
	}

	/**
	 * Determines whether the current user is active or not.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isOnline($useCache = true)
	{
		static $states = array();

		if (!isset($states[$this->id]) || !$useCache) {
			$model = ES::model('Users');
			$states[$this->id] = $model->isOnline($this->id, $useCache);
		}

		return $states[$this->id];
	}

	/**
	 * Determines whether current active user logged in via mobile or not
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isOnlineMobile($useCache = true)
	{
		static $states = array();

		if (!$this->isOnline($useCache)) {
			return false;
		}

		if (!isset($states[$this->id]) || !$useCache) {
			$model = ES::model('Users');
			$states[$this->id] = $model->isOnlineMobile($this->id, $useCache);
		}

		return $states[$this->id];
	}


	/**
	 * Determines if the current logged in user is viewing this current page
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isViewer()
	{
		$my = ES::user();

		$viewer = $my->id == $this->id;

		return $viewer;
	}

	/**
	 * Determines if the user is verified
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function isVerified()
	{
		if (!$this->config->get('users.verification.enabled')) {
			return false;
		}

		return $this->verified ? true : false;
	}

	/**
	 * Determines if the user is logged in
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isLoggedIn()
	{
		return $this->id > 0;
	}

	/**
	 * Logs the user out from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function logout()
	{
		$app = JFactory::getApplication();

		// Try to logout the user.
		$error = $app->logout();

		return $error;
	}

	/**
	 * Update points for the current instance. Does not store this into the database.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function addPoints($point)
	{
		$this->points += $point;

		return $this;
	}

	/**
	 * Determines if the current user is a super administrator of the site or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isSiteAdmin()
	{
		static $_cache = array();

		$isSiteAdmin = false;

		if (isset($_cache[$this->id])) {
			$isSiteAdmin = $_cache[$this->id];
		} else {
			$isSiteAdmin = $this->authorise('core.admin') || $this->authorise('core.manage');

			// Check for moderator access in profile type
			if (!$isSiteAdmin) {
				$isSiteAdmin = $this->isModerator();
			}

			$_cache[$this->id] = $isSiteAdmin;
		}

		return ($isSiteAdmin) ? true : false ;
	}

	/**
	 * Determine if user is part of the moderator set from profile type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isModerator()
	{
		static $_moderator = array();

		if (!isset($_moderator[$this->id])) {
			$profileType = $this->getProfile();
			$_moderator[$this->id] = $profileType->isModerator();
		}

		return $_moderator[$this->id];
	}

	/**
	 * Determines if the user can request verification
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canRequestVerification()
	{
		$my = JFactory::getUser();

		if (!$this->id || !$my->id) {
			return false;
		}

		if ($my->id == $this->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user able to submit a review
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function canSubmitReview($userId = null, $allowSelfReview = false)
	{
		$user = ES::user($userId);

		if ($user->guest) {
			return false;
		}

		if ($user->isSiteAdmin()) {
			return true;
		}

		// We don't allow user add review for himself
		if ($this->isViewer() && !$allowSelfReview) {
			return false;
		}

		// check if the user has submitted review before
		$model = ES::model('Reviews');
		$hasVoted = $model->hasVoted($this->id, SOCIAL_TYPE_USER, $user->id);

		// User can only vote one time.
		if (!$hasVoted) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve a ratings for this user
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getAverageRatings()
	{
		$model = ES::model('Reviews');
		$ratings = $model->getAverageRatings($this->id, SOCIAL_TYPE_USER);

		return $ratings;
	}

	/**
	 * Get total reviews for the user
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getTotalReviews($options = array())
	{
		return count($this->getReviews($options));
	}

	/**
	 * Gets user's reviews
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getReviews($options = array())
	{
		$model = ES::model('Reviews');
		$reviews = $model->getReviews($this->id, SOCIAL_TYPE_USER, $options);

		return $reviews;
	}


	/**
	 * Determines if this user are allowed to compose a new message to target person
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canStartConversation($userId)
	{
		$config = ES::config();

		// Guest will never be allowed to compose messages.
		if (!$this->isLoggedIn()) {
			return false;
		}

		$conversation = ES::conversation();

		if (!$conversation->canCreate()) {
			return false;
		}

		// Always allow site admin to compose message
		if ($this->isSiteAdmin()) {
			return true;
		}

		// if friends system is disabled, then system should allow to send to any users on the site
		if (!$config->get('friends.enabled')) {
			return true;
		}

		// Allow user to message to non-friend
		if ($config->get('conversations.nonfriend')) {
			return true;
		}

		// Check if the user is friend with the target
		if ($this->isFriends($userId)) {
			return true;
		}

		return false;
	}

	/**
	 * Determins if this user can view target person's profile
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canView(SocialUser $target, $privacyRule = 'profiles.view')
	{
		// Get the current logged in user's privacy
		$privacy = $this->getPrivacy();
		$allowed = $privacy->validate($privacyRule, $target->id, SOCIAL_TYPE_USER);

		if ($this->id != $target->id && !$allowed) {
			$this->setError('COM_EASYSOCIAL_PROFILE_PRIVACY_NOT_ALLOWED');
			return false;
		}

		return true;
	}

	/**
	 * Determines if this user can view the target person's field
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canViewField(SocialUser $target, $fieldId, $privacyRule = 'core.view')
	{
		$privacy = $this->getPrivacy();
		$allowed = $privacy->validate($privacyRule, $fieldId, SOCIAL_TYPE_FIELD, $target->id);

		if (!$allowed) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is allowed to join groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canJoinGroups()
	{
		if (!$this->config->get('groups.enabled')) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		$access = $this->getAccess();
		$total = $this->getTotalGroups();

		if ($access->get('groups.allow.join') && $access->exceeded('groups.join', $total)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is allowed to attend events
	 *
	 * @since	4.0.1
	 * @access	public
	 */
	public function canAttendEvents()
	{
		if (!$this->config->get('events.enabled')) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		$access = $this->getAccess();
		$total = $this->getTotalEvents();

		if ($access->get('events.allow.join') && $access->exceeded('events.join', $total)) {
			return false;
		}

		return true;
	}

	/**
	 * Determinise if the user is allowed to like a page
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function canLikePages()
	{
		if (!$this->config->get('pages.enabled')) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		$access = $this->getAccess();
		$total = $this->getTotalPages();

		if ($access->get('pages.allow.like') && $access->exceeded('pages.like', $total)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is allowed to create polls
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreatePolls()
	{
		if (!$this->config->get('polls.enabled')) {
			return;
		}

		if ($this->guest) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		$access = $this->getAccess();

		if ($access->get('polls.create')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to create audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canCreateAudios()
	{
		if (!$this->config->get('audio.enabled')) {
			return false;
		}

		if ($this->guest) {
			return false;
		}

		$access = $this->getAccess();

		if ($access->get('audios.upload') || $access->get('audios.link')) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to create advertisements
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function canCreateAds()
	{
		if (!$this->config->get('ads.enabled')) {
			return false;
		}

		static $cache = [];

		if (!isset($cache[$this->id])) {
			$access = $this->getAccess();
			$cache[$this->id] = $access->get('ads.create') || ES::isSiteAdmin();
		}

		return $cache[$this->id];
	}

	/**
	 * Determines if the user is allowed to create videos
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreateVideos()
	{
		if (!$this->config->get('video.enabled')) {
			return false;
		}

		if ($this->guest) {
			return false;
		}

		$access = $this->getAccess();

		if ($access->get('videos.create')) {
			return true;
		}

		return false;
	}

	/**
	 * Normalize the method available in this object
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canCreateMarketplaces()
	{
		return $this->canCreateListing();
	}

	/**
	 * Determines if the user is allowed to create marketplace listing
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function canCreateListing()
	{
		if (!$this->config->get('marketplaces.enabled')) {
			return false;
		}

		if ($this->guest) {
			return false;
		}

		$access = $this->getAccess();

		if ($access->get('marketplaces.sell')) {
			return true;
		}

		return false;
	}



	/**
	 * Determines if the user is allowed to create albums
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreateAlbums()
	{
		if (!$this->config->get('photos.enabled')) {
			return false;
		}

		if ($this->guest) {
			return false;
		}

		$access = $this->getAccess();

		if ($access->get('albums.create')) {
			return true;
		}

		return false;
	}


	/**
	 * Determines if the user is allowed to create events
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreateEvents()
	{
		if ($this->guest) {
			return false;
		}

		$model = ES::model('EventCategories');
		$categories = $model->getCreatableCategories($this->getProfile()->id);

		if (empty($categories)) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		// Get the user's access
		$access = $this->getAccess();

		if ($access->allowed('events.create') && !$access->intervalExceeded('events.limit', $this->id)) {
			return true;
		}

		return false;
	}


	/**
	 * Determines if the user is allowed to create groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreateGroups()
	{
		if ($this->guest) {
			return false;
		}

		$model = ES::model('Groups');
		$categories = $model->getCreatableCategories($this->getProfile()->id);

		if (empty($categories)) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		// Get the user's access
		$access = $this->getAccess();

		if ($access->allowed('groups.create') && !$access->intervalExceeded('groups.limit', $this->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user is allowed to create pages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canCreatePages()
	{
		if ($this->guest) {
			return false;
		}

		$model = ES::model('Pages');
		$categories = $model->getCreatableCategories($this->getProfile()->id);

		if (empty($categories)) {
			return false;
		}

		if ($this->isSiteAdmin()) {
			return true;
		}

		// Get the user's access
		$access = $this->getAccess();

		if ($access->allowed('pages.create') && !$access->intervalExceeded('pages.limit', $this->id)) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if user is allowed to upload files on the site
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canCreateFiles($app = null)
	{
		$access = $this->getAccess();

		// Determine if the user can use this feature
		if (!$access->get('files.upload')) {
			return false;
		}

		// Check for access from apps
		if (!is_null($app) && $app instanceof SocialAppsAbstract) {

			// Only check for the app access if that is user file app
			// For those app allow upload file setting should check into the app
			if ($app->element == 'files' && !$app->getApp()->hasAccess($this->profile_id)) {
				return false;
			}
		}

		return true;
	}

	/*
	 * Determine if user are allow to post scheduled story via story form.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function canPostScheduledStory($storyType = SOCIAL_TYPE_USER)
	{
		// If this user cannot post story at all, we'll just return false.
		if (!$this->canPostStory()) {
			return false;
		}

		// Global scheduled is turned off.
		if (!$this->config->get('stream.scheduled.enabled')) {
			return false;
		}

		// User's permission(acl).
		if (!$this->getAccess()->get('scheduler.' . $storyType . '.scheduled') && !$this->isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * determine if the current user can delete a specific user or not
	 *
	 * @since	1.4
	 * @access	public
	 * @param	EasySocialUser $user
	 *
	 * @return	boolean 		True if success, false otherwise.
	 */
	public function canDeleteUser($user)
	{
		if (! $this->isSiteAdmin()) {
			return false;
		}

		// Ensure that the user cannot delete themselves
		if ($user->id == $this->id) {
			return false;
		}

		$isUserSuper = $this->authorise('core.admin');
		$isTargetSuper = $user->authorise('core.admin');

		$isUserAdmin = !$isUserSuper && $this->authorise('core.manage');
		$isTargetAdmin = !$isTargetSuper && $user->authorise('core.manage');

		// if currrent user is a superadmin and target user also a super admin, we dont allow to delete.
		if ($isUserSuper && $isTargetSuper) {
			return false;
		}

		// if current user is a standard admin and the target is either super or admin, we dont allow to delete target.
		if ($isUserAdmin && ($isTargetSuper || $isTargetAdmin)) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if the current user can ban a specific user or not
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canBanUser($user)
	{
		if (!$this->isSiteAdmin()) {
			return false;
		}

		// Ensure that the user cannot ban themselves
		if ($user->id == $this->id) {
			return false;
		}

		$isUserSuper = $this->authorise('core.admin');
		$isTargetSuper = $user->authorise('core.admin');

		$isUserAdmin = !$isUserSuper && $this->authorise('core.manage');
		$isTargetAdmin = !$isTargetSuper && $user->authorise('core.manage');

		// if currrent user is a superadmin and target user also a super admin, we dont allow to ban.
		if ($isUserSuper && $isTargetSuper) {
			return false;
		}

		// if current user is a standard admin and the target is a superadmin, we dont allow to ban target.
		if ($isUserAdmin && $isTargetSuper) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the current user is followed by the target id
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	$id 	The target user id.
	 *
	 * @return	boolean 		True if success, false otherwise.
	 */
	public function isFollowed($id)
	{
		static $items = null;

		if (!isset($items[$this->id][$id])) {

			$subscriptions = ES::subscriptions();
			$items[$this->id][$id] = $subscriptions->isSubscribed($this->id, SOCIAL_TYPE_USER, SOCIAL_APPS_GROUP_USER, $id);
		}

		return $items[$this->id][$id];
	}

	/**
	 * Determines if the current user is friends with the specified user id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	$id 	The target user id.
	 *
	 * @return	boolean 		True if success, false otherwise.
	 */
	public function isFriends($id)
	{
		static $isFriends	= null;

		if(!isset($isFriends[ $this->id ][ $id ]))
		{
			$model 	= ES::model('Friends');

			$isFriends[ $this->id ][ $id ]	= $model->isFriends($this->id , $id);
		}

		return $isFriends[ $this->id ][ $id ];
	}

	/**
	 * Determine if the provided field should be visible on the site
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isFieldVisible(SocialTableField $field)
	{
		// Check for conditional field
		if (!$field->isConditional()) {
			return true;
		}

		// Get user params
		$params = $this->getEsParams();
		$conditionalFields = $params->get('conditionalFields');

		if (!$conditionalFields) {
			return true;
		}

		$conditionalFields = json_decode($conditionalFields, true);

		if (isset($conditionalFields[$field->id]) && $conditionalFields[$field->id]) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user is friends with the specified user id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int 	$id 	The target user id.
	 *
	 * @return	boolean 		True if success, false otherwise.
	 */
	public function getFriend($id)
	{
		$friend = ES::table('Friend');
		$friend->loadByUser($this->id, $id);

		return $friend;
	}

	/**
	 * Determines if the person is a registered member or not.
	 *
	 * @param	null
	 * @return	boolean		True if registered, false otherwise.
	 */
	public function isRegistered()
	{
		return $this->id > 0;
	}

	/**
	 * Determines if the current user record is a new user or not.
	 *
	 * @access	private
	 * @param	null
	 * @return	boolean	True on success false otherwise.
	 */
	private function isNew()
	{
		return $this->id < 1;
	}

	/**
	 * Determines if the person is pending approval or not.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	boolean		True if still pending, false otherwise.
	 */
	public function isPending()
	{
		if($this->status == SOCIAL_REGISTER_APPROVAL)
		{
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user has access to the community area
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function hasCommunityAccess()
	{
		static $items = array();

		if (!isset($items[$this->id])) {
			$profile = $this->getProfile();

			$items[$this->id] = (bool) $profile->community_access;
		}

		return $items[$this->id];
	}


	/**
	 * Determines if the user has ability to switch to other profile type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canSwitchProfile()
	{
		static $items = array();

		if (!isset($items[$this->id])) {
			$profile = $this->getProfile();

			$items[$this->id] = (bool) $profile->switchable;
		}

		return $items[$this->id];
	}

	/**
	 * Determines if the user has an advertisement account
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function hasAdvertiserAccount()
	{
		static $cache = [];

		if (!isset($cache[$this->id])) {
			$cache[$this->id] = false;

			$advertiser = $this->getAdvertiserAccount();

			if ($advertiser && $advertiser->isPublished()) {
				$cache[$this->id] = true;
			}
		}

		return $cache[$this->id];
	}

	/**
	 * Determines if the user has an avatar
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function hasAvatar()
	{
		return !empty($this->avatar_id) || !empty($this->photo_id);
	}


	/**
	 * Get available avatar sizes
	 *
	 * @since	1.4.6
	 * @access	public
	 */
	public function getAvatarSizes()
	{
		return $this->avatarSizes;
	}

	/**
	 * Retrieves named based avatars
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getAvatarName()
	{
		static $items = array();

		if (!isset($items[$this->id])) {
			$name = $this->getName();

			$initials = new stdClass();
			$initials->text = '';
			$initials->code = '';

			$text = '';

			$isAscii = ES::string()->isAscii($name);

			if ($isAscii) {
				$segments = explode(' ', $name);

				if (count($segments) >= 2) {
					$tmp = array();
					$tmp[] = substr($segments[0], 0, 1);
					$tmp[] = substr($segments[count($segments) - 1], 0, 1);

					$text = implode('', $tmp);
					$initials->text = $text;
				} else {
					$initials->text = substr($name, 0, 1);
				}

				$initials->text = strtoupper($initials->text);
				$text = $initials->text;

			} else {
				$initials->text = ESJString::substr($name, 0, 1);

				// We need to get the color
				$text = $this->email;
			}

			// Render the color code
			$initials->code = $this->getAvatarNameCode($text);

			$items[$this->id] = $initials;
		}

		return $items[$this->id];
	}

	/**
	 * Generates the color code for avatar
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function getAvatarNameCode($text)
	{
		$char = substr($text, 0, 1);
		$codes = array(1 => array('A','B','C','D','E'),
					   2 => array('F','G','H','I','J'),
					   3 => array('K','L','M','N','O'),
					   4 => array('P','Q','R','S','T'),
					   5 => array('U','V','W','X','Y','Z'));


		foreach($codes as $key => $sets) {
			if (in_array($char, $sets)) {
				return $key;
			}
		}

		// if nothing found, just return 1
		return '1';
	}

	/**
	 * Retrieves the user's avatar location
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAvatar($size = SOCIAL_AVATAR_MEDIUM)
	{
		// If avatar id is being set, we need to get the avatar source
		if ($this->defaultAvatar && isset($this->defaultAvatar->id) && $this->defaultAvatar->id) {
			$default = $this->defaultAvatar->getSource($size);

			return $default;
		}

		// If the avatar size that is being requested is invalid, return default avatar.
		$default = $this->getDefaultAvatar($size);

		if ($this->config->get('users.avatarUseName') && (!$this->avatars[$size] || empty($this->avatars[$size]))) {
			$textAvatar = ES::textavatar();

			// Try to get the first and last name if exists
			$fullName = $this->getName();
			$firstName = $this->getFirstName();
			$lastName = $this->getLastName();

			// We need to get the correct initials
			if ($this->config->get('users.displayName') == 'realname' && $firstName && $lastName) {
				$firstName = explode(' ', $firstName);
				$lastName = explode(' ', $lastName);
				$format = $this->getNameFormat();

				$fullName = $firstName[0] . ' ' . $lastName[0];

				// Respect the name format if it is last_middle_first or last_first
				if ($format == 2 || $format == 5) {
					$fullName = $lastName[0] . ' ' . $firstName[0];
				}
			}

			return $textAvatar->getAvatar($fullName);
		}

		if (!$this->avatars[$size] || empty($this->avatars[$size])) {
			return $default;
		}

		// Get the path to the avatar storage.
		$avatarLocation = ES::cleanPath($this->config->get('avatars.storage.container'));
		$usersAvatarLocation = ES::cleanPath($this->config->get('avatars.storage.user'));

		// Build the path now.
		$path = $avatarLocation . '/' . $usersAvatarLocation . '/' . $this->id . '/' . $this->avatars[ $size ];

		// Build final storage path.
		if ($this->avatarStorage == SOCIAL_STORAGE_JOOMLA) {

			$absolutePath = JPATH_ROOT . '/' . $path;

			// Detect if this file really exists.
			if (!JFile::exists($absolutePath)) {
				return $default;
			}

			$uri = ES::getUrl($path);
		} else {
			$storage = ES::storage($this->avatarStorage);
			$uri = $storage->getPermalink($path);
		}

		return $uri;
	}

	/**
	 * Retrieves the default cover location as it might have template overrides.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getDefaultCover()
	{
		static $default = null;

		if (!$default) {
			$config = ES::config();
			$overriden = JPATH_ROOT . '/images/easysocial_override/user/cover/default.png';
			$uri = ES::getUrl('/images/easysocial_override/user/cover/default.png');

			if (JFile::exists($overriden)) {
				$default = $uri;
			} else {
				$default = ES::getUrl($config->get('covers.default.user.default'));
			}
		}

		return $default;
	}

	/**
	 * Retrieves the default avatar location as it might have template overrides.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDefaultAvatar($size)
	{
		static $default = null;

		$key = $this->id . '.' . $size;

		if (!isset($default[$key])) {

			$config = ES::config();
			$overriden = JPATH_ROOT . '/images/easysocial_override/user/avatar/' . $size . '.png';

			$uri = ES::getUrl('/images/easysocial_override/user/avatar/' . $size . '.png');

			// Default avatar path
			$default[$key] = ES::getUrl($config->get('avatars.default.user.' . $size));

			if (JFile::exists($overriden)) {
				$default[$key] = $uri;
			}

			// See if profile is set and see if there is a default avatar in the profile or not
			$model = ES::model('Avatars');
			$avatars = $model->getDefaultAvatars($this->profile_id);

			foreach($avatars as $avatar) {
				if ($avatar->default) {
					$default[$key] = $avatar->getSource($size);
					break;
				}
			}
		}

		return $default[$key];
	}

	/**
	 * Retrieves the photo table for the user's avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAvatarPhoto()
	{
		static $photos = array();

		if (!isset($photos[$this->id])) {

			$model = ES::model('Avatars');
			$photo = $model->getPhoto($this->id);

			$photos[$this->id] = is_null($photo) ? false : $photo;
		}

		return $photos[$this->id];
	}

	public function hasCover()
	{
		return !(empty($this->cover) || empty($this->cover->id));
	}

	/**
	 * Retrieves the user's cover data
	 *
	 * @since 	1.2
	 * @access	public
	 *
	 */
	public function getCoverData()
	{
		return $this->cover;
	}

	/**
	 * Retrieves the user's cover location
	 *
	 * @since 	1.0
	 * @access	public
	 */
	public function getCover()
	{
		static $covers = [];

		$key = md5($this->id);

		if (!isset($covers[$key])) {
			if (!$this->cover) {
				$covers[$key] = $this->getDefaultCover();

				return $covers[$key];
			}

			$covers[$key] = $this->cover->getSource(SOCIAL_AVATAR_LARGE, true, true);
		}

		return $covers[$key];
	}

	/**
	 * Retrieves the user's cover position
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getCoverPosition()
	{
		if(!$this->cover)
		{
			return 0;
		}

		return $this->cover->getPosition();
	}

	/**
	 * Retrieves the user badges
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getBadges()
	{
		if (!isset(SocialUserStorage::$badges[$this->id])) {

			$model 	= ES::model('Badges');

			SocialUserStorage::$badges[$this->id] = $model->getBadges($this->id);
		}

		// Returns a list of badges earned by the user.
		return SocialUserStorage::$badges[$this->id];
	}

	/**
	 * Retrieves the user's username
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserName()
	{
		return $this->username;
	}


	/**
	 * Retrieves the user's real name dependent on the system configurations.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getName($useFormat = '')
	{
		$config = ES::config();
		$name = $this->username;

		if ($this->guest) {
			return JText::_($this->name);
		}

		if ($useFormat) {
			if ($useFormat == 'realname') {
				$name = $this->name;
			}
		} else {
			if ($config->get('users.displayName') == 'realname') {
				$name = $this->name;
			}
		}

		return $name;
	}

	/**
	 * Retrieves the user's name format
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getNameFormat($key = 'JOOMLA_FULLNAME')
	{
		static $formats = array();

		if (!isset($formats[$this->id])) {
			$data = $this->getFieldValue($key);

			$formats[$this->id] = $data->format;
		}

		return $formats[$this->id];
	}

	/**
	 * Retrieves the user's first name
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getFirstName($key = 'JOOMLA_FULLNAME')
	{
		static $firstNames = array();

		if (!isset($firstNames[$this->id])) {
			$fullName = $this->getFieldValue($key);

			$firstNames[$this->id] = '';

			if (isset($fullName->first)) {
				$firstNames[$this->id] = $fullName->first;
			}
		}

		return $firstNames[$this->id];
	}

	/**
	 * Retrieves the user's middle name
	 *
	 * @since	3.2.24
	 * @access	public
	 */
	public function getMiddleName($key = 'JOOMLA_FULLNAME')
	{
		static $middleName = array();

		if (!isset($middleName[$this->id])) {
			$fullName = $this->getFieldValue('JOOMLA_FULLNAME');

			$middleName[$this->id] = '';

			if (isset($fullName->middle)) {
				$middleName[$this->id] = $fullName->middle;
			}
		}

		return $middleName[$this->id];
	}

	/**
	 * Retrieves the user's last name
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getLastName($key = 'JOOMLA_FULLNAME')
	{
		static $lastNames = array();

		if (!isset($lastNames[$this->id])) {
			$fullName = $this->getFieldValue($key);

			$lastNames[$this->id] = '';

			if (isset($fullName->last)) {
				$lastNames[$this->id] = $fullName->last;
			}
		}

		return $lastNames[$this->id];
	}

	/**
	 * Retrieves the user's single name
	 *
	 * @since	3.2.24
	 * @access	public
	 */
	public function getSingleName($key = 'JOOMLA_FULLNAME')
	{
		static $name = array();

		if (!isset($name[$this->id])) {
			$fullName = $this->getFieldValue($key);

			$name[$this->id] = '';

			if (isset($fullName->name)) {
				$name[$this->id] = $fullName->name;
			}
		}

		return $name[$this->id];
	}

	/**
	 * Get's a user stream name. If the current logged in user is him/her self, use "You" instead.
	 * This can be applied to anyone that is trying to apply stream like-ish contents.
	 *
	 * @access	public
	 * @return	string
	 */
	public function getStreamName($uppercase = true)
	{
		$my = ES::user();

		if ($my->id == $this->id) {
			$uppercase 	= $uppercase ? '' : '_LOWERCASE';

			return JText::_('COM_EASYSOCIAL_YOU' . $uppercase);
		}

		return $this->getName();
	}

	/**
	 * Retrieves the user's connection.
	 *
	 * @param   null
	 * @return  string  The current user's connection.
	 */
	public function getConnections()
	{
		return $this->connections;
	}

	/**
	 * Retrieves the user's points
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * Returns the last visited date from a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLastVisitDate($type = null, $local = false)
	{
		// If user wants a lapsed type.
		if ($type == 'lapsed') {
			$date = ES::date($this->lastvisitDate);

			return $date->toLapsed();
		}

		// And the rest of the checking also respect this empty time format 0000-00-00 00:00:00
		// So we need to set this empty time format as long as the last visit date return empty
		if ($this->lastvisitDate === '') {
			$this->lastvisitDate = '0000-00-00 00:00:00';
		}

		// show the last visit date time base on the timezone
		if ($this->lastvisitDate != '0000-00-00 00:00:00' && $local) {

			$date = ES::date($this->lastvisitDate);
			$lastVisitDate = $date->toMySQL($local);

			return $lastVisitDate;
		}

		return $this->lastvisitDate;
	}

	/**
	 * Returns the Joomla user params column data
	 *
	 * @since	4.0.12
	 * @access	public
	 */
	public function getUserActivationParams()
	{
		// Retrieve the Joomla user params
		$params = $this->params;

		$obj = ES::makeObject($this->params);

		// Get the activation state from the user
		if (isset($obj->activate) && $obj->activate) {
			return $obj->activate;
		}

		return false;
	}

	/**
	 * Returns the user's user group that they belong to.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserGroups($gids = false)
	{
		$groups = $this->helper->getUserGroups();

		if ($gids) {
			return array_keys($groups);
		}

		return $groups;
	}

	/**
	 * Generates a redirection login url if needed to
	 *
	 * @since	2.2.5
	 * @access	public
	 */
	public function getLoginRedirectionLink()
	{
		$profile = $this->getProfile();
		$params = $profile->getParams();

		$customLink = false;
		$menuId = $params->get('login_success', false);

		if ($this->hasNeverLoggedIn()) {
			$menuId = $params->get('login_success_first', false);
		}

		if ($menuId && $menuId != 'null') {
			$customLink = ESR::getMenuLink($menuId);
		}

		return $customLink;
	}

	/**
	 * Returns the last visited date from a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getRegistrationDate()
	{
		$date = ES::get('Date', $this->registerDate);

		return $date;
	}

	/**
	 * Retrieves the profile type of the current user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getProfile()
	{
		static $profiles = array();

		if ($this->id && $this->username && is_null($this->profile_id)) {
			// this is unlikely to happen. User might be created by query injection. Lets
			// assign default profile type to this user.
			$model = ES::model('Users');
			$newId = $model->assignDefaultProfile($this->id);

			if ($newId !== false) {
				$this->profile_id = $newId;
			}
		}

		if (!isset($profiles[$this->profile_id])) {

			$profile = ES::table('Profile');
			$profile->load($this->profile_id);

			$profiles[$this->profile_id] = $profile;
		}

		return $profiles[$this->profile_id];
	}

	/**
	 * Retrieves the privacy object of the current user.
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function getPrivacy()
	{
		$privacy = ES::privacy($this->id);

		return $privacy;
	}

	/**
	 * Get the alias of the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlias($withId = true, $forceId = false)
	{
		$config = ES::config();
		$jConfig = ES::jconfig();

		// Default permalink to use.
		$name = $config->get('users.aliasName') == 'realname' ? $this->name : $this->username;

		$withId = ($withId || $forceId) ? true : $withId;

		// If sef is not enabled or running SH404, just return the ID-USERNAME prefix.
		jimport('joomla.filesystem.file');
		$mijoSef = JFile::exists(JPATH_ADMINISTRATOR . '/components/com_mijosef/mijosef.php');

		// Check if the permalink is set
		if ($this->permalink && !empty($this->permalink)) {
			$name = $this->permalink;
		}

		// If alias exists and permalink doesn't we use the alias
		if ($this->alias && !empty($this->alias) && !$this->permalink) {
			$name = $this->alias;
		}

		// if sh404sef enabled, we stop here. Pririoty given to sh404sef vs mijosef.
		if (!$jConfig->getValue('sef') || ES::isSh404EasySocialEnabled()) {

			// Always return id regardless of the settings when sh404 is enabled. #2826
			return ($withId || ES::isSh404EasySocialEnabled() ? $this->id . ':' : '') . JFilterOutput::stringURLSafe($name);
		}

		// if mijosef enabled, we stop here.
		if ($mijoSef) {
			return ($withId ? $this->id . ':' : '') . JFilterOutput::stringURLSafe($name);
		}

		// If the name is in the form of an e-mail address, due to security concern, we will use fullname intead and with the format of ID:permalink
		// further check if the alias is in a form of 'formatted' emails, if yes, let use fullname instead.
		if (JMailHelper::isEmailAddress($this->username) && $name == JFilterOutput::stringURLSafe($this->username)) {

			$name = $this->name;
			return ($withId ? $this->id . ':' : '') . JFilterOutput::stringURLSafe($name);
		}

		// Ensure that the name is a safe url.
		$name = ($withId ? $this->id . ':' : '') . JFilterOutput::stringURLSafe($name);
		$name = JFilterOutput::stringURLSafe($name);

		return $name;
	}

	/**
	 * Centralized method to retrieve a person's profile link.
	 * This is where all the magic happens.
	 *
	 * @access	public
	 * @param	null
	 */
	public function getPermalink($xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		$my = ES::user();

		// If user is blocked, just use a dummy link
		if (!$my->isSiteAdmin() && ($this->isBlock() || !$this->hasCommunityAccess()) || !$this->id) {
			return 'javascript:void(0);';
		}

		// When simple urls are enabled, we just hardcode the url
		$config = ES::config();
		$jConfig = ES::jConfig();


		// Check if the easysocialurl system plugin is enabled or not.
		if (JPluginHelper::isEnabled('system', 'easysocialurl')) {
			if (!ES::isSh404Installed() && $jConfig->getValue('sef') && $sef) {

				$rootUri = rtrim(JURI::root(), '/');

				$alias = JFilterOutput::stringURLSafe($this->getAlias());

				$alias = ESR::normalizePermalink($alias);

				$url = $rootUri . '/' . $alias;

				// Retrieve current site language code
				$langCode = ES::getCurrentLanguageCode();

				// Append language code from the simple url
				if (!empty($langCode)) {
					$url = $rootUri . '/' . $langCode . '/' . $alias;
				}

				if ($jConfig->getValue('sef_suffix') && !(substr($url, -9) == 'index.php' || substr($url, -1) == '/')) {

					// $uri = JURI::getInstance(ES::getURI(true));
					// $format = $uri->getVar('format', 'html');

					$format = 'html';
					$url .= '.' . $format;

				}

				return $url;
			}
		}

		$options = array('id' => $this->getAlias());

		if ($external) {
			$options['external'] = true;
		}

		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$url = FRoute::profile($options , $xhtml);

		return $url;
	}

	/**
	 * Sets a user as a verified user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function setVerified()
	{
		$table = ES::table('Users');
		$table->load(array('user_id' => $this->id));

		$table->verified = true;

		$state = $table->store();

		// @TODO: Notify the user that their profile has been set as verified

		return $state;
	}

	/**
	 * Sets a user as a verified user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function removeVerified()
	{
		$table = ES::table('Users');
		$table->load(array('user_id' => $this->id));

		$table->verified = false;

		$state = $table->store();

		// We need to remove requested row as well
		$request = ES::table('Verification');
		$exists = $request->load([
				'uid' => $this->id,
				'type' => SOCIAL_TYPE_USER
		]);

		if ($exists) {
			$request->delete();
		}
		// @TODO: Notify the user that their profile has been set as verified

		return $state;
	}

	/**
	 * Allows caller to set a field value given the unique key
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function setFieldValue($key, $value, $requireValidation = true)
	{
		$data = $this->getProfile()->getCustomFields();

		if (!$data) {
			return false;
		}

		$fields = array();

		foreach ($data as $field) {
			$fields[$field->unique_key]	= $field;
		}

		if (!isset($fields[$key])) {
			return false;
		}

		// Get the field
		$field = $fields[$key];

		$fieldTable = ES::table('Field');

		// If field doesn't exist, just skip this.
		if (!$fieldTable->load($field->id)) {
			return false;
		}

		// Format the data for onEditValidate trigger
		$inputName = SOCIAL_CUSTOM_FIELD_PREFIX . '-' . $field->id;
		$data = array($inputName => $value);

		if ($requireValidation) {
			$fields = ES::fields();
			$handler = $fields->getHandler();
			$args = array(&$data, &$this);
			$fieldArray = array($field);

			$errors = $fields->trigger('onEditValidate', SOCIAL_FIELDS_GROUP_USER, $fieldArray, $args, array($handler, 'validate'));

			if (is_array($errors) && count($errors) > 0) {
				return $errors;
			}
		}

		$state = $fieldTable->saveData($value, $this->id, SOCIAL_TYPE_USER);

		return $state;
	}

	/**
	 * Retrieves the custom field formatted value from this user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFieldValue($key)
	{
		static $processed = array();

		if (!isset($processed[$this->id])) {
			$processed[$this->id] = array();
		}

		if (!isset($processed[$this->id][$key])) {

			// Get the field
			if (!isset(SocialUserStorage::$fields[$this->id][$key])) {
				$model = ES::model('Fields');

				// isFieldKey is needed so that the model will cache the result
				$options = array('group' => SOCIAL_TYPE_USER, 'workflow_id' => $this->getWorkflow()->id, 'data' => true , 'dataId' => $this->id , 'dataType' => SOCIAL_TYPE_USER, 'key' => $key, 'isFieldKey' => true);
				$result = $model->getCustomFields($options);

				SocialUserStorage::$fields[$this->id][$key] = isset($result[0]) ? $result[0] : false;
			}

			$field = SocialUserStorage::$fields[$this->id][$key];

			// Initialize a default property
			$processed[$this->id][$key] = '';

			// Trigger the getFieldValue to obtain data from the field.
			if ($field) {
				$value = ES::fields()->getValue($field);

				$processed[$this->id][$key] = $value;
			}
		}


		return $processed[$this->id][$key];
	}

	/**
	 * Retrieves the custom field raw data from this user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getFieldData($key , $default = '')
	{
		static $processed = array();

		if (!isset($processed[$this->id])) {
			$processed[$this->id] = array();
		}

		if (!isset($processed[$this->id][$key])) {
			if (!isset(SocialUserStorage::$fields[$this->id][$key])) {
				$result = ES::model('Fields')->getCustomFields(array('group' => SOCIAL_TYPE_USER, 'workflow_id' => $this->getWorkflow()->id, 'data' => true , 'dataId' => $this->id , 'dataType' => SOCIAL_TYPE_USER, 'key' => $key));

				SocialUserStorage::$fields[$this->id][$key] = isset($result[0]) ? $result[0] : false;
			}

			$field = SocialUserStorage::$fields[$this->id][$key];

			// Initialize a default property
			$processed[$this->id][$key]	= '';

			if ($field) {
				// Trigger the getFieldValue to obtain data from the field.
				$value = ES::fields()->getData($field);

				$processed[$this->id][$key] = $value;
			}
		}

		return $processed[$this->id][$key];
	}

	/**
	 * Returns the total number of groups the user created.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalCreatedGroups()
	{
		static $total = array();

		if(!isset($total[ $this->id ])) {
			$model = ES::model('Groups');

			$total[$this->id] = $model->getTotalCreated($this->id, SOCIAL_TYPE_USER);
		}

		return $total[$this->id];
	}

	/**
	 * Retrieves authentication token for a user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAuthToken($createIfEmpty = false)
	{
		$token = $this->auth;

		if (!$token && $createIfEmpty) {
			$this->auth = $this->generateAuthToken();
			$token = $this->auth;

			$this->store();
		}

		return $token;
	}

	/**
	 * Generates an affiliation id for the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function generateAuthToken()
	{
		$hash = md5($this->password . JFactory::getDate()->toSql());

		return $hash;
	}

	/**
	 * Retrieves user affiliation id. This should be used for any tracking purposes to avoid
	 * user's id being exposed
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAffiliationId($createIfEmpty = false)
	{
		$id = $this->affiliation_id;

		if (!$id && $createIfEmpty) {
			$this->affiliation_id = $this->generateAffiliationId();
			$id = $this->affiliation_id;

			$this->store();
		}

		return $id;
	}

	/**
	 * Generates an affiliation id for the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function generateAffiliationId()
	{
		$key = $this->email . $this->id . $this->name;
		$hash = md5($key);

		return $hash;
	}

	/**
	 * Retrieves user porums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getEsParams()
	{
		$params = new JRegistry($this->es_params);

		return $params;
	}

	/**
	 * Retrieves the user dashboard preferences
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getDashboardFilterPreferences()
	{
		$config = ES::config();
		$defaultFilter = $config->get('users.dashboard.start');
		$params = $this->getEsParams();

		// Checks for the user's ACL whether allowed to schedule a post.
		$access = $this->canPostScheduledStory(SOCIAL_TYPE_USER);

		$userFilter = new stdClass();
		$userFilter->filter = $defaultFilter;
		$userFilter->id = null;

		if (!$config->get('users.dashboard.startsession')) {
			return $userFilter;
		}

		$userFilter->filter = $params->get('dashboard_filter', $defaultFilter);
		$userFilter->id = $params->get('dashboard_filter_id', null);

		// Check whether the user has the permission(acl) to scheduled a post. We'll need to reset this preference back to default filter.
		if (!$access && $userFilter->filter == 'scheduled') {
			$this->saveDashboardFilterPreferences($defaultFilter);

			$userFilter->filter = $defaultFilter;
		}

		// Check whether the user default default start item was set to everyone, but the everyone filter was turned off.
		// We'll need to reset this preference back to default filter. #4560
		if ($config->get('users.dashboard.everyone') == '0' && $userFilter->filter = 'everyone') {
			$this->saveDashboardFilterPreferences($defaultFilter);

			$userFilter->filter = $defaultFilter;
		}

		return $userFilter;
	}

	/**
	 * Determine if story preferences already exists for the user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isStoryPreferencesExists()
	{
		$params = $this->getEsParams();
		$preferences = $params->get('story', false);

		if ($preferences === false) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves user porums
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUserPreferences($element = 'story')
	{
		$params = $this->getEsParams();
		$preferences = $params->get($element, false);

		if (!$preferences) {
			return array();
		}

		$preferences = (array) $preferences;

		// Ensure that they are always unique
		$preferences = array_unique($preferences);

		return $preferences;
	}

	/**
	 * Retrieves user porums
	 *
	 * @since	3.2.1
	 * @access	public
	 */
	public function getUserRobots()
	{
		// $robots = SOCIAL_PROFILE_ROBOTS_INHERIT;
		// $preferences = $this->getUserPreferences('robots');

		// if (isset($preferences[0]) && $preferences) {
		// 	$robots = $preferences[0];
		// }

		$robots = $this->robots ? $this->robots : SOCIAL_PROFILE_ROBOTS_INHERIT;
		return $robots;
	}

	/**
	 * Retrieves the advertiser account associated with this user
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getAdvertiserAccount()
	{
		static $cache = [];

		if (!isset($cache[$this->id])) {
			$model = ES::model('Advertisers');

			$cache[$this->id] = $model->getAdvertiserAccount($this->id);
		}

		return $cache[$this->id];
	}

	/**
	 * Normalize the method available in this object so other users know what node is this
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getType()
	{
		return SOCIAL_TYPE_USER;
	}

	/**
	 * Normalize the method available in this object so other users know what node is this
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function getTypePlural()
	{
		return SOCIAL_TYPE_USERS;
	}

	/**
	 * Returns the total number of groups the user participated.
	 *
	 * @since	2.0.6
	 * @access	public
	 */
	public function getTotalGroups($filter = array())
	{
		static $total = array();

		if (! $filter) {
			// default to search groups that the user participated
			$filter['types'] = 'participated';
		}

		$key = $this->id . md5(implode('', $filter));

		if (!isset($total[$key])) {
			$model = ES::model('Groups');

			$total[$key] = $model->getTotalParticipatedGroups($this->id, $filter);
		}

		return $total[$key];
	}

	/**
	 * Returns the total number of items the user required to review
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPendingReview($type)
	{
		static $_cache = array();

		$idx = $this->id . '-' . $type;

		if (!isset($_cache[$idx])) {

			$model = ES::model('Clusters');
			$count = $model->getTotalPendingReview($this->id, $type);

			$_cache[$idx] = $count;
		}

		return $_cache[$idx];
	}


	/**
	 * Returns the total number of pages the user participated.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPages($filter = array())
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Pages');

			$total[$this->id] = $model->getTotalParticipatedPages($this->id, $filter);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number of pages the user has created.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPagesCreated()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Pages');

			$total[$this->id] = $model->getTotalCreatedPages($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number unpublished of pages the user has created.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalPagesUnpublished()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Pages');

			$total[$this->id] = $model->getTotalUnpublishedPages($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number of groups the user has created.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalGroupsCreated()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Groups');

			$total[$this->id] = $model->getTotalCreatedGroups($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number of groups the user has created.
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getTotalGroupsUnpublished()
	{
		static $total = array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Groups');

			$total[$this->id] = $model->getTotalUnpublishedGroups($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number of followers the user has
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalFollowers()
	{
		static $data = [];

		$key = $this->id;

		if (!isset($data[$key])) {
			$model = ES::model('Followers');

			$data[$key] = $model->getTotalFollowers($this->id);
		}

		return $data[$key];
	}

	/**
	 * Retrieves the total albums the user has
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalAlbums($excludeCore = false)
	{
		static $total 	= array();

		if(!isset($total[ $this->id ]))
		{
			$model 		= ES::model('Albums');
			$options 	= array('uid' => $this->id , 'type' => SOCIAL_TYPE_USER);

			if($excludeCore)
			{
				$options[ 'excludeCore' ]	= $excludeCore;
			}

			$total[ $this->id ] = $model->getTotalAlbums($options);
		}

		return $total[ $this->id ];
	}

	/**
	 * Retrieves the total number of videos the user has
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function getTotalVideos($daily = false, $includeUnpublished = false, $skipVideoIds = array())
	{
		static $total 	= array();

		$sid = $this->id . (int) $daily . (int) $includeUnpublished;

		if (!isset($total[$sid]) || $skipVideoIds) {

			$model = ES::model('Videos');
			$options = array('userid' => $this->id);

			if ($includeUnpublished) {
				$options['state'] = 'all';
			}

			if ($daily) {
				$today = ES::date()->toMySQL();
				$date = explode(' ', $today);

				$options['day'] = $date[0];
			}

			if ($skipVideoIds) {
				$options['skipVideos'] = $skipVideoIds;
			}

			$total[$sid] = $model->getTotalVideos($options);
		}

		return $total[$sid];
	}

	/**
	 * Retrieves the total number of items on sale the user has
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function getTotalItemSell($daily = false, $includeUnpublished = false, $skipItemIds = array())
	{
		static $total 	= array();

		$sid = $this->id . (int) $daily . (int) $includeUnpublished;

		if (!isset($total[$sid]) || $skipItemIds) {

			$model = ES::model('Marketplaces');
			$options = array('userid' => $this->id);

			if ($includeUnpublished) {
				$options['state'] = 'all';
			}

			if ($daily) {
				$today = ES::date()->toMySQL();
				$date = explode(' ', $today);

				$options['day'] = $date[0];
			}

			if ($skipItemIds) {
				$options['skipItems'] = $skipItemIds;
			}

			$total[$sid] = $model->getTotalItems($options);
		}

		return $total[$sid];
	}

	/**
	 * Retrieves the total number of audios the user has
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalAudios($daily = false, $includeUnpublished = false)
	{
		static $total 	= array();

		$sid = $this->id . (int) $daily . (int) $includeUnpublished;

		if (!isset($total[$sid])) {

			$model = ES::model('Audios');
			$options = array('userid' => $this->id);

			if ($includeUnpublished) {
				$options['state'] = 'all';
			}

			if ($daily) {
				$today = ES::date()->toMySQL();
				$date = explode(' ', $today);

				$options['day'] = $date[0];
			}

			$total[$sid] = $model->getTotalAudios($options);
		}

		return $total[$sid];
	}


	/**
	 * Retrieves the total photos the user has
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalPhotos($daily = false, $includeUnpublished = false)
	{
		static $total = array();

		$sid = $this->id . (int) $daily . (int) $includeUnpublished;

		if (!isset($total[$sid])) {

			$model = ES::model('Photos');
			$options = array('uid' => $this->id, 'type' => SOCIAL_TYPE_USER);

			if ($includeUnpublished) {
				$options['state'] = 'all';
			}

			if ($daily) {
				$today 	= ES::date()->toMySQL();
				$date 	= explode(' ', $today);

				$options['day'] = $date[0];
			}

			$total[$sid] = $model->getTotalPhotos($options);
		}

		return $total[$sid];
	}


	/**
	 * Returns the total number of badges the user has
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalBadges()
	{
		static $total 	= array();

		if (!isset($total[$this->id])) {
			$model = ES::model('Badges');
			$total[$this->id] = $model->getTotalBadges($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Returns the total number of users this user follows.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalFollowing()
	{
		static $total 	= array();

		if(!isset($total[ $this->id ]))
		{
			$model	= ES::model('Followers');

			$total[ $this->id ]	= $model->getTotalFollowing($this->id);
		}

		return $total[ $this->id ];
	}

	/**
	 * Returns the total number of users this user follows.
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getTotalPolls()
	{
		static $total = array();

		if (!isset($total[ $this->id])) {
			$model = ES::model('Polls');

			$total[$this->id] = $model->getTotalPolls($this->id);
		}

		return $total[$this->id];
	}

	/**
	 * Retrieves the default friend list for this user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getDefaultFriendList()
	{
		static $lists 	= array();

		if(!isset($lists[ $this->id ]))
		{
			$list 	= ES::table('List');
			$exists	= $list->load(array('default' => 1 , 'user_id' => $this->id));

			if(!$exists)
			{
				$lists[ $this->id ]	= false;
			}
			else
			{
				$lists[ $this->id ]	= $list;
			}
		}


		return $lists[ $this->id ];
	}

	/**
	 * Returns the total number of friends list the current user has.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	null
	 */
	public function getTotalFriendsList()
	{
		static $total	= array();

		if(! isset($total[ $this->id ]))
		{
			$model					= ES::model('Lists');
			$total[ $this->id ] 	= $model->getTotalLists($this->id);
		}

		return $total[ $this->id ];
	}

	/**
	 * Returns the total number of friends the current user has.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalFriends()
	{
		static $data = [];

		if (!isset($data[$this->id])) {
			$model = ES::model('Friends');
			$data[$this->id] = $model->getTotalFriends($this->id);
		}

		return $data[$this->id];
	}

	/**
	 * To determine if the user already has oauth linked
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function hasOAuth($client = '')
	{
		return $this->getOAuth($client) ? true : false;
	}

	/**
	 * Retrieves the oauth token
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getOAuth($client = '')
	{
		$oauth = ES::table('OAuth');

		$state = $oauth->load(array('client' => $client , 'uid' => $this->id , 'type' => SOCIAL_TYPE_USER));

		if (!$state) {
			return false;
		}

		return $oauth;
	}

	/**
	 * Retrieves the oauth token
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getOAuthToken($client = '')
	{
		$oauth 	= ES::table('OAuth');

		$oauth->load(array('client' => $client , 'uid' => $this->id , 'type' => SOCIAL_TYPE_USER));

		return $oauth->token;
	}

	/**
	 * Determines if the user is associated with an oauth client
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isAssociated($clientType = '')
	{
		static $results = array();

		$key = $this->id . $clientType;

		if (!isset($results[$key])) {

			$options = array('uid' => $this->id, 'type' => SOCIAL_TYPE_USER);

			if ($clientType) {
				$options['client'] = $clientType;
			}

			$table = ES::table('OAuth');
			$exists = $table->load($options);

			$results[$key] = $exists;
		}

		return $results[$key];
	}

	/**
	 * Retrieves the total number of mutual friends.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTotalMutualFriends($targetId)
	{
		static $data = array();

		if (!isset($data[$this->id])) {

			$model = ES::model('Friends');
			$total = $model->getMutualFriendCount($this->id , $targetId);

			$data[$this->id] = $total;
		}

		return $data[$this->id];
	}

	/**
	 * Retrieves the access of a user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAccess()
	{
		static $cache = [];

		if (!isset($cache[$this->id])) {
			$access = ES::access($this->id, SOCIAL_TYPE_USER);

			$cache[$this->id] = $access;
		}

		return $cache[$this->id];
	}

	/**
	 * Returns the total number of new notifications for this user.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalNewNotifications()
	{
		static $items = array();

		if (!isset($items[$this->id])) {
			$model = ES::model('Notifications');
			$options = array('unread' => 1, 'target' => array('id' => $this->id, 'type' => SOCIAL_TYPE_USER));

			$items[$this->id] = $model->getCount($options);
		}

		return $items[$this->id];
	}

	/**
	 * Returns the total number of new conversations this user has not yet read.
	 *
	 * @param	null
	 * @return	int 	The total new conversations
	 */
	public function getTotalNewConversations()
	{
		static $results	= array();

		if(!isset($results[ $this->id ]))
		{
			$model	= ES::model('Conversations');
			$total 	= $model->getConversations($this->id , array('count' => true , 'filter' => 'unread'));

			$results[ $this->id ]	= $total;
		}

		return $results[ $this->id ];
	}

	/**
	 * Returns the total number of new friend requests the user has.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	int 		The total number of requests.
	 */
	public function getTotalFriendRequests()
	{
		static $results 	= array();

		if(!isset($results[ $this->id ]))
		{
			$model 	= ES::model('Friends');
			$total 	= $model->getTotalRequests($this->id);

			$results[ $this->id ]	= $total;
		}

		return $results[ $this->id ];
	}

	/**
	 * Returns the total number of friend request a user made.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	int 		The total number of requests.
	 */
	public function getTotalFriendRequestsSent()
	{
		static $results 	= array();

		if (!isset($results[$this->id])) {
			$model = ES::model('Friends');
			$total = $model->getTotalRequestSent($this->id);

			$results[$this->id] = $total;
		}

		return $results[$this->id];
	}

	/**
	 * Loads the user's session
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 */
	public function loadSession()
	{
		$user 	= ES::user();

		$this->helper->loadSession($this , $user);
	}

	/**
	 * Allows caller to update a specific field item given it's unique id and value.
	 *
	 * @since	2.0.0
	 * @access	public
	 */
	public function updateField($fieldId , $value)
	{
		$data   = ES::table('FieldData');
		$data->loadByField($fieldId , $this->node_id);

		$data->node_id  = $this->node_id;
		$data->field_id = $fieldId;
		$data->data     = $value;
		$data->data_binary  = $value;

		return $data->store();
	}

	/**
	 * Updates the params for the user
	 *
	 * @since	3.2.18
	 * @access	public
	 */
	public function updateParams($params)
	{
		$table = ES::table('Users');
		$table->load(array('user_id' => $this->id));
		$table->params = $params->toString();

		$table->store();

		// we need to update this cached copy too.
		$this->es_params = $table->params;

		return $table;
	}

	/**
	 * Determines if this user account can be deleted.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteable()
	{
		if ($this->isSiteAdmin()) {
			return false;
		}

		// Check if this user's profile allows deletion.
		$profile = $this->getProfile();
		$params = $profile->getParams();

		if ($params->get('delete_account')) {
			return true;
		}

		return false;
	}

	/**
	 * Allows caller to delete user GDPR download request
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function deleteGdprDownload()
	{
		$download = ES::table('Download');
		$exists = $download->load(array('userid' => $this->id));

		if ($exists) {
			$download->delete();
		}

		return;
	}

	/**
	 * Allows caller to delete user's clusters subscription
	 *
	 * @since	3.2.0
	 * @access	public
	 */
	public function deleteClustersSubscriptions()
	{
		$model = ES::model('Subscriptions');
		$model->deleteClustersSubscriptions($this->id);

		return;
	}

	/**
	 * Allows caller to delete a cover photo for a user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteCover()
	{
		$state 	= $this->cover->delete();

		// Reset this user's cover
		$this->cover 	= ES::table('Cover');

		// Prepare the dispatcher
		ES::apps()->load(SOCIAL_TYPE_USER);
		$dispatcher		= ES::dispatcher();
		$args 			= array(&$this , &$this->cover);

		// @trigger: onUserCoverRemove
		$dispatcher->trigger(SOCIAL_TYPE_USER , 'onUserCoverRemove' , $args);

		return $state;
	}

	/**
	 * Override parent's delete implementation if necessary.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		$state = parent::delete();

		// NOTE: At this point, we don't need to delete the user's record from #__social_users because we have a user plugin that already captures this.

		// Once the user is deleted, clear it from the indexer.
		if ($state) {
			JPluginHelper::importPlugin('finder');

			if(ESUtility::isJoomla5()) {
				$dispatcher = ESFactory::getApplication()->getDispatcher();

				$data = new Joomla\CMS\Event\Finder\AfterDeleteEvent('onFinderAfterDelete', ['easysocial.users', $this]);
				$result = $dispatcher->dispatch('onFinderAfterDelete', $data);

            	return !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];
			} else {
				ESDispatcher::trigger('onFinderAfterDelete', array('easysocial.users', $this));
			}

			// We also need to remove his/her avatar from the server
			$this->removeAvatar();

			// We also need to remove their gdpr download request
			$this->deleteGdprDownload();

			// We need to delete clusters subscription for this user as well
			$this->deleteClustersSubscriptions();
		}

		return $state;
	}

	/**
	 * Alternative to store if we just want to save the user's details in #__social_users
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function store()
	{
		$user = ES::table('Users');
		$user->loadByUser($this->id);

		$user->user_id = $this->id;
		$user->state = $this->state;
		$user->type = $this->type;
		$user->alias = $this->alias;
		$user->auth = $this->auth;
		$user->verified = $this->verified;
		$user->affiliation_id = $this->affiliation_id;

		$state = $user->store();

		return $state;
	}

	/**
	 * Override parent's implementation when save so we could run some pre / post rendering.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function save($updateOnly = false)
	{
		// Determine if this record is a new user by identifying the id.
		$isNew		= $this->isNew();

		// Request parent to store data.
		// $state 		= parent::save($updateOnly);

		// Joomla 3.3.0 sets the JUser object into the session when user login, and if we do parent::save, then SocialUser object will get updated into the session, and causes instance mismatch that leads up to user getting logged out.
		// Hence here we recreate a JUser object, bind it, and save it.
		// In order to make sure that getProperties() gets the correct properties (especially password), SocialUser::bind no longer binds to the to parent class.
		// This is partly because, JUser::bind encrypts password, and calling parent::bind will cause SocialUser->password to no longer have the original clear password, and calling getProperties from here will get you the encrypted password to rebind again.
		if ($isNew) {
			$user = new JUser();
		} else {
			$user = JFactory::getUser($this->id);
		}

		// We only want to bind data that JUser needs
		$vars = get_object_vars($user);
		$data = array();

		foreach ($vars as $key => $val) {
			if (isset($this->$key)) {
				$data[$key]	= $this->$key;
			}
		}

		// Need a custom check for password2
		if (isset($this->password2)) {
			$data['password'] = $this->password;
			$data['password2'] = $this->password2;
		} else {

			// This is to prevent Joomla from throwing PHP notice error because bind actions expects both password and password2 to exist.
			if (!$isNew) {
				unset($data['password']);
			}
		}

		// lets re-arrange the 'groups' so that other user plugins can facilidate the user data
		if (isset($data['groups']) && $data['groups']) {

			$newG = array();
			foreach($data['groups'] as $key => $val) {
				if (is_int($val)) {
					$newG[] = $val;
				} else {
					$newG[] = $key;
				}
			}
			// now we reassgn the groups back to the data.
			$data['groups'] = $newG;
		}

		if (isset($this->require_reset)) {
			$data['requireReset'] = $this->require_reset;
		}

		$resetRequireReset = false;

		// Reset the require reset since there is password in the form. #1851
		// but we must let joomla receive the correct require reset value so it can be processed correctly.
		if (isset($data['password']) && $data['password']) {
			$resetRequireReset = true;
		}

		if (isset($data['params'])) {
			// This must be in array when it goes to JUser's loadArray() during binding in Joomla 4
			$data['params'] = ES::makeArray($data['params']);
		}

		$user->bind($data);
		$state = $user->save($updateOnly);

		$this->setProperties($user->getProperties());

		// Once the #__users table is updated, we need to update ours as well.
		if ($state) {

			// Joomla user save succeeded. Let's reset this value for ES user table.
			if ($resetRequireReset) {
				$this->require_reset = 0;
			}

			$userTable = ES::table('Users');
			$userTable->loadByUser($this->id);

			$userTable->user_id	= $this->id;
			$userTable->state = $this->state;
			$userTable->type = $this->type;
			$userTable->alias = $this->alias;
			$userTable->auth = $this->auth;
			$userTable->completed_fields = $this->completed_fields;
			$userTable->require_reset = $this->require_reset;
			$userTable->verified = $this->verified;

			// store the user robot default value
			if ($isNew) {

				$profile = $this->getProfile();

				if ($profile->params) {

					$params = new JRegistry($profile->params);
					$robots = $params->get('robots', SOCIAL_PROFILE_ROBOTS_INHERIT);

					$robots = array('robots' => $robots);
					$robots = json_encode($robots);

					$userTable->params = $robots;
				}
			}

			$state = $userTable->store();

			// @TODO: Set the default parameters and connections?
			// $this->params = $this->param->toString();
			// $user->set('params'		, $params);
			// $user->set('connections'	, $connections);

			//Generate "new user just registered on the site" on the stream
			if ($isNew) {
				// Get the application params
				$app = ES::table('App');
				$options = array('element' => 'profiles', 'group' => SOCIAL_TYPE_USER);

				$app->load($options);
				$params = $app->getParams();

				// We should greet newbie user upon login with welcome message
				$user = ES::user($this->id);
				$user->setConfig('showwelcome', 1);
				$user->storeConfig();

			}

		} else {
			$this->setError($user->getError());
		}

		return $state;
	}

	/**
	 * Saves the user preferences for dashboard filters
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function saveDashboardFilterPreferences($filter, $id = null)
	{
		$params = $this->getEsParams();

		$params->set('dashboard_filter', $filter);
		$params->set('dashboard_filter_id', $id);

		return $this->updateParams($params);
	}

	/**
	 * Activates a user account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function activate($sendEmail = true)
	{
		// Load Joomla users plugin for triggers.
		JPluginHelper::importPlugin('user');

		// Set joomla parameters
		$this->activation = '';
		$this->block = 0;

		// Update the current state property.
		$this->state = SOCIAL_USER_STATE_ENABLED;

		// Try to save the user.
		$state = $this->save();

		// Save the user.
		if (!$state) {
			$this->setError($this->getError());
			return false;
		}

		//index user into com_finder
		$this->syncIndex();

		return true;
	}

	/**
	 * Assign this user to clusters
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function assignCluster()
	{
		$profile = $this->getProfile();

		// We need to see if this user was invited to join any group/pages or not
		$model = ES::model('Clusters');
		$invitedClusters = $model->isInvited($this->id);

		$defaultGroups = $profile->getDefaultClusters('groups');
		$defaultPages = $profile->getDefaultClusters('pages');

		foreach ($invitedClusters as $id) {

			$cluster = ES::cluster($id);

			if ($cluster->getType() == SOCIAL_TYPE_GROUP) {
				$defaultGroups[] = $cluster;
			}

			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				$defaultPages[] = $cluster;
			}
		}

		$processed = array();

		// Assign users into the EasySocial groups
		if ($defaultGroups) {
			foreach ($defaultGroups as $group) {
				if (in_array($group->id, $processed)) {
					continue;
				}

				$state = $group->createMemberViaAutoJoinGroups($this->id);

				if ($state) {
					$processed[] = $group->id;
				}
			}
		}

		// Assign users into the EasySocial pages
		if ($defaultPages) {
			foreach ($defaultPages as $page) {
				if (in_array($page->id, $processed)) {
					continue;
				}

				$state = $page->createMemberViaAutoLikePages($this->id);

				if ($state) {
					$processed[] = $page->id;
				}
			}
		}
	}

	/**
	 * Approves a user's registration application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function approve($sendEmail = true)
	{
		// Check if the user already approved or not.
		if ($this->block == 0 && $this->state == SOCIAL_USER_STATE_ENABLED) {
			//already approved.
			return true;
		}

		// Update the JUser object.
		$this->block = 0;

		// Update the current state property.
		$this->state = SOCIAL_USER_STATE_ENABLED;

		// If set to admin approve, user should be activated regardless of whether user activates or not.
		$this->activation = 0;

		// Store the block status
		$this->save();

		// Activity logging.
		$registration = ES::model('Registration');
		$registration->logRegistrationActivity($this);

		// add user into com_finder index
		$this->syncIndex();

		// If we need to send email to the user, we need to process this here.
		if ($sendEmail) {

			// Get the application data.
			$jConfig = ES::jConfig();

			// Get the current profile this user has registered on.
			$profile = $this->getProfile();

			$adminSef = false;
			if (ES::isFromAdmin()) {
				$adminSef = true;
			}

			// Push arguments to template variables so users can use these arguments
			$params = array(
							'site' => $jConfig->getValue('sitename'),
							'username' => $this->username,
							'name' => $this->getName(),
							'avatar' => $this->getAvatar(SOCIAL_AVATAR_LARGE),
							'email' => $this->email,
							'profileType' => $profile->get('title'),
							'manageAlerts' => false,
							'loginLink' => ESR::external('index.php?option=com_easysocial&view=login', false, null, false, true, $adminSef)
							);

			JFactory::getLanguage()->load('com_easysocial', JPATH_ROOT);

			// Get the email title.
			$title = JText::_('COM_EASYSOCIAL_EMAILS_REGISTRATION_APPLICATION_APPROVED');

			// Immediately send out emails
			$mailer = ES::mailer();

			$mailTemplate = $mailer->getTemplate();

			$mailTemplate->setTitle($title);
			$mailTemplate->setRecipient($this->getName(), $this->email);
			$mailTemplate->setTemplate('site/registration/approved', $params);
			$mailTemplate->setLanguage($this->getLanguage());

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email now.
			$mailer->create($mailTemplate);
		}

		return true;
	}

	/**
	 * Reject's a user's registration application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function reject($reason = '' , $sendEmail = true , $deleteUser = false)
	{
		// Announce to the world when a new user registered on the site.
		$config = ES::config();

		// If we need to send email to the user, we need to process this here.
		if ($sendEmail) {
			// Get the application data.
			$jConfig = ES::jConfig();

			$profile = $this->getProfile();
			$params = array(
							'site'			=> $jConfig->getValue('sitename'),
							'username'		=> $this->username,
							'name'			=> $this->getName(),
							'email'			=> $this->email,
							'reason'		=> $reason,
							'profileType'	=> $profile->get('title'),
							'manageAlerts'	=> false
					);

			JFactory::getLanguage()->load('com_easysocial' , JPATH_ROOT);

			// Get the email title.
			$title      = JText::_('COM_EASYSOCIAL_EMAILS_REGISTRATION_REJECTED_EMAIL_TITLE');

			// Immediately send out emails
			$mailer 	= ES::mailer();

			// Get the email template.
			$mailTemplate	= $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($this->getName() , $this->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the contents
			$mailTemplate->setTemplate('site/registration/rejected' , $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email now.
			$mailer->create($mailTemplate);
		}

		// If required, delete the user from the site.
		if($deleteUser)
		{
			$this->delete();
		}else{
			// else we need to 'expire' the activation token
			$this->activation = 1;

			// incase the user already activated, we need to block the user again.
			$this->block = 1;

			// now save juser
			$this->save();
		}

		return true;
	}

	/**
	 * Bind an array of data to the current user.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	The object's properties.
	 * @param	bool	Determines whether the data is from $_POST method.
	 *
	 * @return 	bool	True if success false otherwise.
	 */
	public function bind(&$data, $post = false)
	{
		// Request the helper to bind specific additional details
		$this->helper->bind($this, $data);

		$this->setProperties($data);
	}

	/**
	 * Deprecated. Binds a single custom field data based on the given field element
	 *
	 * @since	1.2
	 * @deprecated Deprecated since 1.3.
	 * @access	public
	 * @param	Array	An array of data that is being posted.
	 * @return	bool	True on success, false otherwise.
	 */
	public function bindCustomField($field)
	{
		SocialUserStorage::$fields[$this->id][$field->unique_key] = $field;
	}

	/**
	 * Binds the user custom fields.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	Array	An array of data that is being posted.
	 * @return	bool	True on success, false otherwise.
	 */
	public function bindCustomFields($data, $useProfileId = 0)
	{
		// Get the registration model.
		$model = ES::model('Fields');

		$profileId = ($useProfileId) ? $useProfileId : $this->profile_id;

		$profile = ES::table('Profile');
		$profile->load($profileId);

		// Get the field id's that this profile is allowed to store data on.
		$fields	= $model->getStorableFields($profile->getWorkflow()->id, SOCIAL_TYPE_PROFILES);

		// If there's nothing to process, just ignore.
		if (!$fields) {
			return false;
		}

		$availableFields = array();

		// Let's go through all the storable fields and store them.
		foreach ($fields as $fieldId) {
			$availableFields[$fieldId] = $fieldId;

			$key = SOCIAL_FIELDS_PREFIX . $fieldId;

			if (!isset($data[$key])) {
				continue;
			}

			// Get the value
			$value = isset($data[$key]) ? $data[$key] : '';

			// Test if field really exists to avoid any unwanted input
			$field = ES::table('Field');

			// If field doesn't exist, just skip this.
			if (!$field->load($fieldId)) {
				continue;
			}

			$field->saveData($value, $this->id, SOCIAL_TYPE_USER);
		}

		// Store conditional fields in user params so it can be use in other places
		if (isset($data['conditionalRequired']) && $data['conditionalRequired']) {
			$params = $this->getEsParams();

			$conditionalFields = ES::registry($data['conditionalRequired']);
			$storedConditionalFields = ES::registry($params->get('conditionalFields'));

			$storedConditionalFields->mergeObjects($conditionalFields->getRegistry());

			// Remove any unused fields
			$conditionalFieldsArray = $storedConditionalFields->toArray();
			$obj = new stdClass();

			foreach ($conditionalFieldsArray as $key => $value) {
				if (isset($availableFields[$key])) {
					$obj->$key = $value;
				}
			}

			$newConditionalFields = ES::registry($obj);

			$params->set('conditionalFields', $newConditionalFields->toString());

			$table = ES::table('Users');
			$table->load(array('user_id' => $this->id));
			$table->params = $params->toString();

			$table->store();
		}
	}

	/**
	 * Binds the privacy object for the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bindPrivacy($privacy , $privacyIds , $customIds, $privacyOld, $resetPrivacy = false)
	{
		$privacyLib = ES::privacy();
		//$resetMap 	= call_user_func_array(array($privacyLib , 'getResetMap'));
		$resetMap 	= $privacyLib->getResetMap();

		$result 	= array();

		if(empty($privacy))
		{
			return false;
		}

		foreach($privacy as $group => $items)
		{
			foreach($items as $rule => $value)
			{
				$id		= $privacyIds[ $group ][ $rule ];
				$id 	= explode('_' , $id);

				$custom			= $customIds[ $group ][ $rule ];
				$customUsers	= array();
				$curVal 	 	= $privacyOld[ $group ][ $rule ];

				// Break down custom user rules
				if(!empty($custom))
				{
					$tmp 	= explode(',' , $custom);

					foreach($tmp as $userId)
					{
						if(!empty($userId))
						{
							$customUsers[]	= $userId;
						}
					}
				}

				$obj 			= new stdClass();
				$obj->id		= $id[ 0 ];
				$obj->mapid		= $id[ 1 ];
				$obj->value		= $value;
				$obj->custom	= $customUsers;

				$obj->reset  = false;

				//check if require to reset or not.
				$gr = strtolower($group . '.' . $rule);
				if($resetPrivacy && in_array($gr,  $resetMap))
				{
					$obj->reset = true;
				}

				$result[]	= $obj;
			}
		}

		$model 		= ES::model('Privacy');
		$state 		= $model->updatePrivacy($this->id , $result , SOCIAL_PRIVACY_TYPE_USER);

		if ($state) {
			//index user access in finder
			$this->syncIndex();
		}

		return $state;
	}

	/**
	 * Sync's the user record with Joomla smart search
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function syncIndex()
	{
		// Determines if this is a new account
		$isNew = $this->isNew();

		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterSave', array('easysocial.users', &$this, $isNew));
	}

	/**
	 * Determines if the user exceeded their friend request limit
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function exceededFriendLimit()
	{
		$access 	= $this->getAccess();
		$limit 		= $access->get('friends.limit');

		//TODO: Should get this in one query only.
		$total = $this->getTotalFriends() + $this->getTotalFriendRequestsSent();

		// Site admin should never be bound to this rule.
		if ($this->isSiteAdmin()) {
			return false;
		}

		if ($limit != 0 && $access->exceeded('friends.limit', $total)) {
			return true;
		}

		return false;
	}

	/**
	 * Allows caller to remove the user's avatar
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function removeAvatar()
	{
		$avatar = ES::table('Avatar');
		$state = $avatar->load(array('uid' => $this->id, 'type' => SOCIAL_TYPE_USER));

		if ($state) {
			$state = $avatar->delete();

			// Prepare the dispatcher
			ES::apps()->load(SOCIAL_TYPE_USER);
			$dispatcher = ES::dispatcher();
			$args = array(&$this, &$avatar);

			// @trigger: onUserAvatarRemove
			$dispatcher->trigger(SOCIAL_TYPE_USER, 'onUserAvatarRemove', $args);
		}

		return $state;
	}

	/**
	 * Function to verify user password
	 *
	 * @since  1.2
	 * @access public
	 */
	public function verifyUserPassword($password)
	{
		$model = ES::model('Users');

		return $model->verifyUserPassword($this->id, $password);
	}

	/**
	 * Deprecated. Used to support <1.1 legacy language strings.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function getGenderTerm()
	{
		$gender = $this->getFieldData('GENDER');

		$term = JText::_('COM_EASYSOCIAL_THEIR');

		if($gender == 1)
		{
			$term = JText::_('COM_EASYSOCIAL_HIS');
		}

		if($gender == 2)
		{
			$term = JText::_('COM_EASYSOCIAL_HER');
		}

		return $term;
	}

	/**
	 * Retrieve gender for this user
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function getGender()
	{
		// 1 - male, 2 - female, 0 - no gender
		return $this->getFieldData('GENDER');
	}

	/**
	 * Used to construct a part of language strings to form a gender specific language strings
	 *
	 * @since  1.2
	 * @access public
	 */
	public function getGenderLang()
	{
		$gender = $this->getFieldData('GENDER');

		$term = '_NOGENDER';

		if ($gender == 1) {
			$term = '_MALE';
		}

		if ($gender == 2) {
			$term = '_FEMALE';
		}

		return $term;
	}

	/**
	 * Retrieves the language that the user is currently using
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getLanguage()
	{
		static $params = array();

		if (!isset($params[$this->id])) {

			$obj = ES::makeObject($this->params);

			// Get the locale the user is using
			$locale = !empty($obj->language) ? $obj->language : '';

			// If the user configures to use the site language, get the default language of the site.
			if (empty($locale)) {
				$jConfig = ES::jConfig();
				$locale = $jConfig->getValue('language');
			}

			$params[$this->id] = $locale;
		}

		return $params[$this->id];
	}

	/**
	 * Method to retrieve user's location langauge code
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLocationLanguage()
	{
		static $langCode = array();

		if (!isset($langCode[$this->id])) {
			// $code = $this->config->get('general.location.language');

			$code = $this->getLanguage();
			$langCode[$this->id] = $code;
		}

		return $langCode[$this->id];
	}

	/**
	 * Function to verify current user the badges is viewable by the userId that passed in.
	 *
	 * @since  1.2
	 * @access public
	 */
	public function badgesViewable($userId)
	{
		if($this->id != $userId)
		{
			$privacy 	= ES::privacy($userId);

			if(!$privacy->validate('achievements.view' , $this->id , SOCIAL_TYPE_USER))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the total number of events that this user is invited to.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getTotalEvents()
	{
		return ES::model('Events')->getTotalEvents(array('guestuid' => $this->id, 'types' => 'all', 'gueststate' => SOCIAL_EVENT_GUEST_GOING, 'state' => SOCIAL_STATE_PUBLISHED));
	}

	/**
	 * Returns the total number of events that this user created.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getTotalCreatedEvents($customOptions = array())
	{
		$baseOptions = array('creator_uid' => $this->id, 'creator_type' => SOCIAL_TYPE_USER, 'types' => 'all');

		$options = array_merge($baseOptions, $customOptions);

		return ES::model('Events')->getTotalEvents($options);
	}

	/**
	 * Returns the total number of events that this user created or joined.
	 * @since  2.0
	 * @access public
	 */
	public function getTotalCreatedJoinedEvents($customOptions = array())
	{
		$baseOptions = array('creator_uid' => $this->id, 'creator_type' => SOCIAL_TYPE_USER, 'types' => 'all', 'creator_join' => true, 'ongoing' => true, 'upcoming' => true);

		$options = array_merge($baseOptions, $customOptions);

		return ES::model('Events')->getTotalEvents($options);
	}

	/**
	 * Sets the OTP settings for the user. This technique is borrowed from totp plugin
	 *
	 * @access public
	 * @since 1.3
	 */
	public function setOtpConfig($otpConfig)
	{
		// Create the encryptor class
		$key = ES::jConfig()->getValue('secret');

		if (ES::isJoomla4()) {
			$aes = new Aes($key, 256);
		} else {
			$aes = new FOFEncryptAes($key, 256);
		}

		// Create the encrypted option strings
		if (!empty($otpConfig->method) && ($otpConfig->method != 'none')) {

			$decryptedConfig = json_encode($otpConfig->config);
			$decryptedOtep   = json_encode($otpConfig->otep);

			// Bind the values to this user
			$this->otpKey = $otpConfig->method . ':' . $decryptedConfig;
			$this->otep = $aes->encryptString($decryptedOtep);
		}
	}

	/**
	 * Retrieves the user's one time password settings
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getOtpConfig()
	{
		static $cache = array();

		if (!isset($cache[$this->id])) {
			$otpConfig = new stdClass();
			$otpConfig->method = 'none';
			$otpConfig->config = array();
			$otpConfig->otep   = array();

			// Ensure the user has an otp set
			if (!$this->otpKey) {
				$cache[$this->id] = $otpConfig;

				return $cache[$this->id];
			}

			// Get the encrypted data
			list($method, $encryptedConfig) = explode(':', $this->otpKey, 2);
			$encryptedOtep = $this->otep;

			// Create an encryptor class
			$key = ES::jConfig()->getValue('secret');

			if (ES::isJoomla4()) {
				$aes = new Aes($key, 256);
			} else {
				$aes = new FOFEncryptAes($key, 256);
			}

			// Do not need to decrypt the config data which contain the code
			// Because the config itself already JSON data
			$decryptedConfig = $encryptedConfig;

			// Decrypt the data
			$decryptedOtep = $aes->decryptString($encryptedOtep);

			// Remove the null padding added during encryption
			$decryptedConfig = rtrim($decryptedConfig, "\0");
			$decryptedOtep = rtrim($decryptedOtep, "\0");

			// Update the configuration object
			$otpConfig->method = $method;
			$otpConfig->config = @json_decode($decryptedConfig);
			$otpConfig->otep   = @json_decode($decryptedOtep);

			/*
			 * If the decryption failed for any reason we essentially disable the
			 * two-factor authentication. This prevents impossible to log in sites
			 * if the site admin changes the site secret for any reason.
			 */
			if (is_null($otpConfig->config)) {
				$otpConfig->config = array();
			}

			if (is_object($otpConfig->config)) {
				$otpConfig->config = (array) $otpConfig->config;
			}

			if (is_null($otpConfig->otep)) {
				$otpConfig->otep = array();
			}

			if (is_object($otpConfig->otep)) {
				$otpConfig->otep = (array) $otpConfig->otep;
			}

			$cache[$this->id] = $otpConfig;
		}

		return $cache[$this->id];
	}

	/**
	 * Determines if the person can view target person's points history.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canViewPointsHistory($user)
	{
		return $this->canView($user, 'points.view.history');
	}

	/**
	 * Determine if user can receive system emails
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function canReceiveSystemEmails()
	{
		if ($this->isModerator() || $this->isSiteAdmin()) {

			// Ensure that user is allowed to receive system email
			if ($this->sendEmail) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get lists of custom fields that are associated with the user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCustomFields($key = null)
	{
		$user = $this;
		$model = ES::model('Fields');

		$options = [
			'key' => $key,
			'workflow_id' => $user->getWorkflow()->id,
			'data' => true,
			'dataId' => $user->id,
			'dataType' => SOCIAL_TYPE_USER
		];

		$fields = $model->getCustomFields($options);

		if (!isset($fields)) {
			return false;
		}

		return $fields;
	}

	/**
	 * Get lists of custom fields that are associated with the user
	 * in the form of json string
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFieldsApi($key = null)
	{
		$fields = $this->getCustomFields($key);

		if (!$fields) {
			return false;
		}

		$fieldsApi = array();

		foreach ($fields as $field) {
			$obj = new stdClass();

			$obj->key = $field->unique_key;
			$obj->data = $field->data;

			$fieldsApi[] = $obj;
		}

		$json = ES::json();

		$fieldsApi = $json->encode($fieldsApi);
		return $fieldsApi;
	}

	/**
	 * Get user's profile headline descriptions
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getDescription($truncate = false, $key = 'HEADLINE')
	{
		// Get the data from headline custom fields
		$description = $this->getFieldValue($key);

		if (!$description || !$description->value) {
			return false;
		}

		if ($truncate) {
			$description->value = ESJString::substr($description->value, 0, 300) . JText::_('COM_EASYSOCIAL_ELLIPSES');
		}

		return $description->value;
	}

	/**
	 * Render metadata of the user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderHeaders()
	{
		$obj = new stdClass();

		$name = ES::string()->escape($this->getName());

		// Set meta data
		$obj->title = JText::sprintf('COM_EASYSOCIAL_PROFILE_META_DESCRIPTION', $name);
		$obj->description = $this->getDescription(true);

		if (!$obj->description) {
			$obj->description = $obj->title;
		}

		$obj->type = 'Profile';
		$obj->keywords = JText::sprintf('COM_EASYSOCIAL_PROFILE_META_KEYWORDS', $this->getProfile()->title, $name);
		$obj->author = $this->getName();
		$obj->image = $this->getCover();
		$obj->url = $this->getPermalink(true, true);

		// Set meta data
		ES::meta()->setMetaObj($obj);
	}

	/**
	 * Determine if we should display welcome message to the user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function showWelcomeMessage()
	{
		$config = $this->getConfig();

		$welcome = $config->get('showwelcome');

		return $welcome;
	}

	/**
	 * Method to delete all fields_data for existing profile type.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFieldData($workflowId)
	{
		if (!$workflowId) {
			$workflowId = $this->getWorkflow()->id;
		}

		$model = ES::model('Fields');
		$state = $model->deleteFieldsData($this->id, $workflowId);

		return $state;
	}

	/**
	 * Get user's total cluster joined
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalClusters()
	{
		$totalGroups = $this->getTotalGroups();
		$totalEvents = $this->getTotalEvents();
		$totalPages = $this->getTotalPages();

		$totalClusters = $totalGroups + $totalEvents + $totalPages;

		return $totalClusters;
	}

	/**
	 * Check if user already completed their profile
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getProfileCompleteness()
	{
		$total = $this->getProfile()->getTotalFields(SOCIAL_PROFILES_VIEW_EDIT);

		// There are no fields to be check.
		if ($total == 0) {
			return (int) 100;
		}

		// Avoid using maintenance script to do this because it is possible that a site might have >1000 users
		// Using this method instead so that every user will at least get executed once during login
		// Won't happen on subsequent logins
		if (empty($this->completed_fields)) {
			$this->calculateProfileCompleteness();
		}

		$percentage = (int) (($this->completed_fields / $total) * 100);

		// If somehow the percentage is above 100%, it means some of the fields get deleted from the workflow. #2799
		// Hence we need to recalculate it back.
		if ($percentage > 100) {
			$this->calculateProfileCompleteness();
			$percentage = (int) (($this->completed_fields / $total) * 100);
		}

		return $percentage;
	}

	/**
	 * Method to recalculate profile completeness percentage
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function calculateProfileCompleteness()
	{
		$options = [
			'workflow_id' => $this->getWorkflow()->id,
			'data' => true,
			'dataId' => $this->id,
			'dataType' => SOCIAL_TYPE_USER,
			'visible' => SOCIAL_PROFILES_VIEW_EDIT,
			'group' => SOCIAL_FIELDS_GROUP_USER
		];

		$model = ES::model('Fields');
		$fields = $model->getCustomFields($options);

		$args = [&$this];
		$completedFields = ES::fields()->trigger('onProfileCompleteCheck', SOCIAL_FIELDS_GROUP_USER, $fields, $args);
		$table = ES::table('Users');
		$table->load([
			'user_id' => $this->id
		]);

		$table->completed_fields = count($completedFields);
		$table->store();

		$this->completed_fields = $table->completed_fields;
	}

	/**
	 * Reload user config
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function reloadConfig()
	{
		return $this->loadConfig(true);
	}

	/**
	 * Initialize user config
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadConfig($forceReload = null)
	{
		if (!isset($this->userConfig[$this->id]) || $forceReload) {

			// Get the config file
			$default = SOCIAL_CONFIG_DEFAULTS . '/users/social.params.json';

			// Read the default data
			$defaultData = file_get_contents($default);

			// Load a new copy of registry
			$originalConfig = ES::registry($defaultData);

			// Get stored config from database
			$storedConfig = $this->social_params;

			// If the config already exists, we need to merge the value with the default
			if ($storedConfig) {

				if (!$storedConfig instanceof SocialRegistry) {
					$storedConfig = ES::registry($storedConfig);
				}

				// Merge the configs
				$originalConfig->mergeObjects($storedConfig->getRegistry());
			}

			$this->userConfig[$this->id] = $originalConfig;
		}

		return $this->userConfig[$this->id];
	}

	/**
	 * Get user config
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getConfig($key = null)
	{
		if ($key) {
			return $this->userConfig[$this->id]->get($key);
		}

		return $this->userConfig[$this->id];
	}

	/**
	 * Set user config
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setConfig($key, $value)
	{
		return $this->getConfig()->set($key, $value);
	}

	/**
	 * Allows caller to set the params for #__social_users.params
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function setUserParams($key, $value)
	{
		$params = $this->getEsParams();
		$params->set($key, $value);

		return $this->updateParams($params);
	}

	/**
	 * Store user config
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function storeConfig()
	{
		// Get user config
		$config = $this->getConfig();

		// Convert the config object to a json string.
		$jsonString = $config->toString();

		// Load user table
		$user = ES::table('Users');
		$user->load(array('user_id' => $this->id));

		$user->social_params = $jsonString;
		$user->store();

		return $config;
	}

	/**
	 * Get user's Social Goals
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSocialGoals()
	{
		$config = $this->getConfig();
		$socialGoals = $config->get('socialgoals');

		// bind goals permalink
		foreach ($socialGoals as $goal => $data) {

			if (!is_object($data)) {
				continue;
			}

			if ($goal == 'joincluster' && (!$this->canAttendEvents() && !$this->canLikePages() && !$this->canJoinGroups())) {
				unset($socialGoals->$goal);
			}

			$permalink = $this->getGoalsPermalink($goal);
			$data->permalink = $permalink;
		}

		return $socialGoals;
	}

	/**
	 * Generate goals permalink
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getGoalsPermalink($key)
	{
		// go to edit profile page
		if ($key == 'updateavatar' || $key == 'completeprofile') {
			return ESR::profile(array('layout' => 'edit'));
		}

		// Browse user page
		if ($key == 'addfriend') {
			return ESR::users();
		}

		// redirect to group
		if ($key == 'joincluster') {
			return ESR::groups();
		}

		// default redirection
		return ESR::dashboard();
	}

	/**
	 * Method to determine user has post a status update before
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasStatusUpdate()
	{
		$model = ES::model('Stream');

		$totalStream = $model->getTotalStreamBy($this->id);

		if ($totalStream > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Method to determine user has post a comment before
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasCommentPost()
	{
		$model = ES::model('Comments');
		$totalComment = $model->getTotalCommentsBy($this->id);

		if ($totalComment > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether user has friend
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasFriends()
	{
		$totalFriends = $this->getTotalFriends();

		if ($totalFriends > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Determines whether user has joined a cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasClusters()
	{
		$totalClusters = $this->getTotalClusters();

		if ($totalClusters > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this user has never logged in before
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function hasNeverLoggedIn()
	{
		$user = JFactory::getUser($this->id);

		if ($user->lastvisitDate == '0000-00-00 00:00:00') {
			return true;
		}

		return false;
	}

	/**
	 * Determines whether user already completed their profile
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hasCompletedProfile()
	{
		$completion = $this->getProfileCompleteness();

		if ($completion >= 100) {
			return true;
		}

		return false;
	}

	/**
	 * Initialize social goals value
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function loadSocialGoals($options = array())
	{
		$goals = $this->getSocialGoals();

		// List of goals and function
		$allowed = $this->goalsList;

		$total = 0;
		$completed = 0;

		foreach ($allowed as $key => $functionName) {

			if (isset($goals->$key)) {
				$total++;

				// We only let user to achieve the goal for one time only
				if ($goals->$key->value) {
					$completed++;
					continue;
				}

				$configName = 'socialgoals.' . $key . '.value';
				$configValue = isset($options[$key]) ? $options[$key] : $this->$functionName();

				$this->setConfig($configName, $configValue);

				if ($configValue) {
					$completed++;
				}
			}
		}

		// Calculate Completion percentage
		$percentage = (int) (($completed / $total) * 100);

		$this->setConfig('socialgoals.percentagecomplete', $percentage);
		$this->setConfig('socialgoals.initialized', true);

		// Store the progress
		$newConfig = $this->storeConfig();

		// Return new goals progress
		return $newConfig->get('socialgoals');
	}

	/**
	 * Check for user's goals completion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function checkGoalsCompletion($percentageOnly = false)
	{
		$goals = $this->getSocialGoals();

		if (!isset($goals->initialized) || !$goals->initialized) {

			// Initialize social goals progress
			$goals = $this->loadSocialGoals();
		}

		if ($percentageOnly) {
			return $goals->percentagecomplete;
		}

		return $goals;
	}

	/**
	 * Update Social Goals Progress
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function updateGoals($key)
	{
		if (!$key) {
			return false;
		}

		$goals = $this->checkGoalsCompletion();

		// Do not proceed if user already achieved all the goals
		if ($goals->percentagecomplete >= 100) {
			return true;
		}

		// Do not proceed if user already achieved this before
		if ($goals->$key->value) {
			return true;
		}

		// The trick here is to change the `initialized` value from true to false,
		// so that the system will automatically re-update the data next time user refresh the page.
		$this->setConfig('socialgoals.initialized', false);
		$this->storeConfig();

		return true;
	}

	/**
	 * Update user's last story posting datetime
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function updateLastStoryTime()
	{
		$now = ES::date()->toSql();

		$this->setConfig('last.story.time', $now);
		$this->storeConfig();

		return true;
	}

	/**
	 * get user's last story posting datetime
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getLastStoryTime()
	{
		$lastDateTime = $this->getConfig('last.story.time', '');
		return $lastDateTime;
	}

	/**
	 * Determine if user are allow to post story via story form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canPostStory()
	{
		$access = $this->getAccess();

		if (!$access->get('story.user.post') && !$this->isSiteAdmin()) {
			return false;
		}

		return true;
	}


	/**
	 * Determine if story flood control enabled and user are no withing the flood interval.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function canSubmitStory()
	{
		if ($this->isSiteAdmin()) {
			return true;
		}

		$access = $this->getAccess();

		if ($access->get('story.flood.user')) {

			$floodInterval = (int) $access->get('story.flood.interval');
			$lastPosting = $this->getLastStoryTime();

			if ($floodInterval && $lastPosting) {
				$check = ES::date('- ' . $floodInterval . ' seconds')->toSql();
				$now = ES::date()->toSql();

				if ($lastPosting > $check) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determine whether user are allow to post story update based on cluster type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canPostClusterStory($clusterType, $clusterId)
	{
		$access = $this->getAccess();
		$model = ES::model('Fields');

		if ($clusterType == SOCIAL_TYPE_GROUP) {

			// Check for profile type access
			if (!$access->get('story.group.post') && !$this->isSiteAdmin()) {
				return false;
			}

			$group = ES::group($clusterId);

			if (!$group->canViewStoryForm($this)) {
				return false;
			}

			// Get the group params
			$params = $group->getParams();

			// Ensure that the user has permissions to see the story form
			$permissions = $params->get('stream_permissions', null);

			$hasPermissionsField = $model->getCustomField($group->getWorkflow()->id, 'permissions', SOCIAL_TYPE_CLUSTERS);

			if ($hasPermissionsField && !is_null($permissions) && !$this->isSiteAdmin()) {

				// If the user is not an admin, ensure that permissions has member
				if ($group->isMember() && !in_array('member', $permissions) && !$group->isOwner() && !$group->isAdmin()) {
					return false;
				}

				// If the user is an admin, ensure that permissions has admin
				if ($group->isAdmin() && !in_array('admin', $permissions) && !$group->isOwner()) {
					return false;
				}

				$type = $group->getParams()->get('permission_type', null);

				// Check for profile type
				if (!is_null($type) && in_array('selected', $type) && !$group->isAdmin() && !$group->isOwner()) {

					$profileType = $group->getParams()->get('permission_profiles', null);

					// Get profile type from current logged in user
					$profile = ES::user()->getProfile();

					if (!is_null($profileType) && !in_array($profile->id, $profileType)) {
						return false;
					}
				}

				// Check for selected members
				if (!is_null($type) && in_array('selectedUsers', $type) && !$group->isAdmin() && !$group->isOwner()) {
					$users = $group->getparams()->get('permission_users', null);

					// Get current logged in user
					$user = ES::user();

					if (!is_null($user->id) && !in_array($user->id, $users)) {
						return false;
					}
				}
			}
		}

		if ($clusterType == SOCIAL_TYPE_EVENT) {

			$event = ES::event($clusterId);

			if (!$event->canPostUpdates()) {
				return false;
			}

			$params = $event->getParams();

			$permissions = $params->get('stream_permissions', null);

			$hasPermissionsField = $model->getCustomField($event->getWorkflow()->id, 'permissions', SOCIAL_TYPE_CLUSTERS);

			if ($hasPermissionsField && !is_null($permissions) && !$this->isSiteAdmin()) {

				// If the user is not an admin, ensure that permissions has member
				if ($event->isMember() && !in_array('member', $permissions) && !$event->isOwner() && !$event->isAdmin()) {
					return false;
				}

				// If the user is an admin, ensure that permissions has admin
				if ($event->isAdmin() && !in_array('admin', $permissions) && !$event->isOwner()) {
					return false;
				}

				$type = $event->getParams()->get('permission_type', null);

				// Check for profile type
				if (!is_null($type) && in_array('selected', $type) && !$event->isAdmin() && !$event->isOwner()) {

					$profileType = $event->getParams()->get('permission_profiles', null);

					// Get profile type from current logged in user
					$profile = ES::user()->getProfile();

					if (!is_null($profileType) && !in_array($profile->id, $profileType)) {
						return false;
					}
				}
			}
		}

		if ($clusterType == SOCIAL_TYPE_PAGE) {

			$page = ES::page($clusterId);

			if (!$page->canViewStoryForm($this)) {
				return false;
			}

			// Get the page params
			$params = $page->getParams();

			// Ensure that the user has permissions to see the story form
			$permissions = $params->get('stream_permissions', null);

			$hasPermissionsField = $model->getCustomField($page->getWorkflow()->id, 'permissions', SOCIAL_TYPE_CLUSTERS);

			if ($hasPermissionsField && !is_null($permissions) && !$this->isSiteAdmin()) {

				// If the user is not an admin, ensure that permissions has member
				if ($page->isMember() && !in_array('member', $permissions) && !$page->isOwner() && !$page->isAdmin()) {
					return false;
				}

				// If the user is an admin, ensure that permissions has admin
				if ($page->isAdmin() && !in_array('admin', $permissions) && !$page->isOwner()) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to send reset password token email
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function remindPassword($email = null)
	{
		// Load backend language file.
		ES::language()->loadAdmin();

		if (!$email) {
			$email = $this->email;
		}

		$model = ES::model('Users');

		// Get user id
		$id = $model->getUserId('email', $email);

		if(!$id) {
			$this->setError(JText::_('COM_EASYSOCIAL_USERS_NO_SUCH_USER_WITH_EMAIL'));
			return false;
		}

		// Reload user
		$user = ES::user($id);

		// Ensure that the user is not blocked
		if ($user->block) {
			$this->setError(JText::_('COM_EASYSOCIAL_USERS_USER_BLOCKED'));
			return false;
		}

		// Super administrator is not allowed to reset passwords.
		if ($user->authorise('core.admin')) {
			$this->setError( JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_SUPER_ADMIN'));
			return false;
		}

		// Make sure the user has not exceeded the reset limit
		if (!$this->checkResetLimit($user)) {
			$resetLimit = (int) JFactory::getApplication()->getParams()->get('reset_time');
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_EXCEEDED', $resetLimit));
			return false;
		}

		$activation = ESUserHelper::generateActivationToken();

		// Set the new activation
		$user->activation = $activation->hashedToken;

		// Save the user to the database.
		if (!$user->save(true)) {
			$this->setError(JText::_('COM_EASYSOCIAL_PROFILE_REMIND_PASSWORD_SAVE_ERROR'));
			return false;
		}

		// Get the application data.
		$jConfig = ES::jConfig();

		// Default username
		$username = $user->username;

		// Use email as username if system only allow to login with emails. #508
		if ($this->config->get('registrations.emailasusername') && $this->config->get('general.site.loginemail')) {
			$username = $email;
		}

		// Push arguments to template variables so users can use these arguments
		$params = array(
				'site' => $jConfig->getValue('sitename'),
				'username' => $username,
				'name' => $user->getName(),
				'id' => $user->id,
				'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
				'profileLink' => $user->getPermalink(true, true),
				'email' => $email,
				'token' => $activation->token
			);

		// Get the email title.
		$title = JText::_('COM_EASYSOCIAL_EMAILS_REMIND_PASSWORD_TITLE');

		// Immediately send out emails
		$mailer = ES::mailer();

		// Get the email template.
		$mailTemplate = $mailer->getTemplate();

		// Set recipient
		$mailTemplate->setRecipient($user->name, $user->email);

		// Set title
		$mailTemplate->setTitle($title);

		// Set the contents
		$mailTemplate->setTemplate('site/user/remind.password', $params);

		// Set the priority. We need it to be sent out immediately since this is user registrations.
		$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

		// Try to send out email now.
		$state = $mailer->create($mailTemplate);

		return $state;
	}

	/**
	 * Remind username
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function remindUsername($email = null)
	{
		// Load backend language file.
		ES::language()->loadAdmin();

		if (!$email) {
			$email = $this->email;
		}

		$model = ES::model('Users');

		// Get user id
		$id = $model->getUserId('email', $email);

		if (!$id) {
			$this->setError(JText::_('COM_EASYSOCIAL_USERS_NO_SUCH_USER_WITH_EMAIL'));
			return false;
		}

		// Reload user
		$user = ES::user($id);

		// Ensure that the user is not blocked
		if ($user->block) {
			$this->setError(JText::_('COM_EASYSOCIAL_USERS_USER_BLOCKED'));
			return false;
		}

		// Get the application data.
		$jConfig = ES::jConfig();

		// Push arguments to template variables so users can use these arguments
		$params = array(
				'site' => $jConfig->getValue('sitename'),
				'username' => $user->username,
				'name' => $user->getName(),
				'id' => $user->id,
				'avatar' => $user->getAvatar(SOCIAL_AVATAR_LARGE),
				'profileLink' => $user->getPermalink(true, true),
				'email' => $email
			);

		// Get the email title.
		$title = JText::sprintf('COM_EASYSOCIAL_EMAILS_REMIND_USERNAME_TITLE', $jConfig->getValue('sitename'));

		// Immediately send out emails
		$mailer = ES::mailer();

		// Get the email template.
		$mailTemplate = $mailer->getTemplate();

		// Set recipient
		$mailTemplate->setRecipient($user->name, $user->email);

		// Set title
		$mailTemplate->setTitle($title);

		// Set the contents
		$mailTemplate->setTemplate('site/user/remind.username', $params);

		// Set the priority. We need it to be sent out immediately since this is user registrations.
		$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

		// Try to send out email now.
		$state = $mailer->create($mailTemplate);

		return $state;
	}

	/**
	 * Method to check if user reset limit has been exceeded within the allowed time period.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function checkResetLimit($user = null)
	{
		if (!$user) {
			$user = ES::user();
		}

		$params = JFactory::getApplication()->getParams();
		$maxCount = (int) $params->get('reset_count');
		$resetHours = (int) $params->get('reset_time');
		$result = true;

		$lastResetTime = strtotime($user->lastResetTime) ? strtotime($user->lastResetTime) : 0;
		$hoursSinceLastReset = (strtotime(JFactory::getDate()->toSql()) - $lastResetTime) / 3600;

		// If it's been long enough, start a new reset count
		if ($hoursSinceLastReset > $resetHours) {
			$user->lastResetTime = JFactory::getDate()->toSql();
			$user->resetCount = 1;
		} elseif ($user->resetCount < $maxCount) {
			// If we are under the max count, just increment the counter
			$user->resetCount;
		} else {
			// At this point, we know we have exceeded the maximum resets for the time period
			$result = false;
		}

		return $result;
	}

	/**
	 * Get workflow for this user
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getWorkflow()
	{
		$profile = $this->getProfile();
		return $profile->getWorkflow();
	}

	/**
	 * Set user to require approval state
	 *
	 * @since	2.2.3
	 * @access	public
	 */
	public function setRequireApproval()
	{
		// Set joomla parameters
		$this->activation = '';
		$this->block = 1;

		// Update the current state property.
		$this->state = SOCIAL_USER_STATE_PENDING;

		// Try to save the user.
		$state = $this->save();

		if (!$state) {
			$this->setError($this->getError());
			return false;
		}

		return true;
	}

	/**
	 * Set this user into Joomla session. Used in REST api
	 *
	 * @since   3.2.9
	 * @access  public
	 */
	public function setJoomlaSession()
	{
		// Set JUser into JSession so that in REST api if we do a
		// JFactory()->getUser() call, Joomla will return correct current logged-in user data.
		$app = JFactory::getApplication();

		$juser = JFactory::getUser($this->id);
		if ($juser->id) {
			$session = JFactory::getSession();
			$session->set('user', $juser);

			$app->checkSession();
		}
	}

	/**
	 * Get the lastseen of the user.
	 *
	 * @since   3.2.11
	 * @access  public
	 */
	public function getLastSeen()
	{
		if ($this->lastvisitDate == '0000-00-00 00:00:00') {
			return JText::_('COM_EASYSOCIAL_USER_NEVER_LOGGED_IN');
		}

		return JText::sprintf('COM_ES_USER_LAST_SEEN_LAPSED', $this->getLastVisitDate('lapsed'));
	}


	public function canConfigureMFA()
	{
		$juser = JFactory::getUser($this->id);

		$state = false;

		if(class_exists('Joomla\Component\Users\Administrator\Helper\Mfa')) {
			$state = Mfa::canShowConfigurationInterface($juser);
		}

		return $state;
	}


	public function getMFAInterfaceUI()
	{
		$juser = JFactory::getUser($this->id);

		$forms = '';

		if(class_exists('Joomla\Component\Users\Administrator\Helper\Mfa')) {

			JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);

			$document = JFactory::getApplication()->getDocument();

			// @var Joomla\CMS\WebAsset\WebAssetManager $wa
			$wa = $document->getWebAssetManager();

			// register com_users asset for the use in Mfa
			$wa->getRegistry()->addRegistryFile('media/com_users/joomla.asset.json');

			$forms = Mfa::getConfigurationInterface($juser);
		}

		return $forms;
	}

	/**
	 * Converts a user object into an array that can be exported
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function toExportData(SocialUser $viewer, $includeFields = false, $extended = false, $options = array())
	{
		static $cache = array();

		$key = $this->id . $viewer->id . (int) $includeFields;

		// force data reload.
		$reload = isset($options['reload']) && $options['reload'] ? $options['reload'] : false;
		$fieldVisibility = isset($options['fieldVisibility']) && $options['fieldVisibility'] ? $options['fieldVisibility'] : SOCIAL_PROFILES_VIEW_DISPLAY;

		if (isset($cache[$key]) && !$reload) {
			return $cache[$key];
		}

		$downloadRequest = isset($options['downloadRequest']) && $options['downloadRequest'] ? $options['downloadRequest'] : false;

		$minimalData = isset($options['minimalData']) && $options['minimalData'] ? $options['minimalData'] : false;


		$isFriend = '0';
		$respondFriend = 0;
		$canFriend = 1;
		$canMessage = 1;
		$canViewProfile = 1;
		$canPostStory = 1;
		$canViewFriend = 1;
		$canViewFollower = 1;
		$canViewPoints = 1;
		$canViewBadges = 1;

		$viewerPrivacy = $viewer->getPrivacy();

		if ($viewer->id != $this->id) {

			// check if both user are friend.
			$friend = $this->getFriend($viewer->id);
			if ($friend !== false && $friend->id) {
				// Determines if they are already friends
				$isFriend = $friend->state == SOCIAL_FRIENDS_STATE_REJECTED ? '0' : $friend->state;

				if ($friend->state == SOCIAL_FRIENDS_STATE_PENDING) {
					$respondFriend = ($friend->actor_id != $viewer->id) ? 1 : 0;
				}
			}

			// Ensure that the current viewer is really allowed to add the target as friends
			if (!$viewerPrivacy->validate('friends.request', $this->id)) {
				$canFriend = 0;
			}

			if (!$isFriend && !ES::config()->get('conversations.nonfriend') || (!$viewerPrivacy->validate('profiles.post.message', $this->id))) {
				$canMessage = 0;
			}

			if (!$viewerPrivacy->validate('profiles.view', $this->id)) {
				$canViewProfile = 0;
			}

			if (!$viewerPrivacy->validate('profiles.post.status', $this->id)) {
				$canPostStory = 0;
			}

			if (!$viewerPrivacy->validate('friends.view', $this->id)) {
				$canViewFriend = 0;
			}

			if (!$viewerPrivacy->validate('followers.view', $this->id)) {
				$canViewFollower = 0;
			}

			if (!$viewerPrivacy->validate('points.view.history', $this->id)) {
				$canViewPoints = 0;
			}

			if (!$viewerPrivacy->validate('achievements.view', $this->id)) {
				$canViewBadges = 0;
			}
		}

		$coverData = $this->getCoverData();
		$coverDetails = array();

		if (!is_null($coverData) && $coverData->id) {
			$coverDetails = $coverData->getCoverDetails();
		}

		$profile = $this->getProfile();
		$profileParams = $profile->getParams();

		$result = array(
			'id' => $this->id,
			'displayName' => $this->getName(),
			'username' => $this->getUserName(),
			'gender' => '',
			'age' => '',
			'email' => $this->email,
			'profileType' => $this->getProfile()->toExportData($viewer),
			'joinedDate' => $this->registerDate,
			'isVerified' => $this->isVerified(),
			'lastLoggedin' => $this->lastvisitDate,
			'lastseen' => $this->getLastSeen(),
			'isFollowed' => $this->isFollowed($viewer->id),
			'isBlocked' => $this->isBlockedBy($viewer->id),
			'isBanned' => $this->isBanned(),
			'viewer' => $this->id == $viewer->id,
			'avatar' => array(
				'thumbnail' => $this->getAvatar(),
				'large' => $this->getAvatar(SOCIAL_AVATAR_LARGE),
				'original' => $this->getAvatarPhoto() ? $this->getAvatarPhoto()->getSource('large') : false
			),
			'cover' => array(
				'large' => $this->getCover() // Deprecated. use cover_details in the future.
			),
			'cover_details' => $coverDetails,
			'objectType' => SOCIAL_TYPE_USER,
			'isSiteAdmin' => $this->isSiteAdmin(),
			'isFriend' => $isFriend,
			'isOnline' => $this->isOnline(),
			'canFriend' => $canFriend,
			'respondFriend' => $respondFriend,
			'canMessage' => $canMessage,
			'canViewProfile' => $canViewProfile,
			'permalink' => $this->getPermalink(true, true),
			'editPermalink' => ESR::profile(array('layout' => 'edit', 'external' => true)),
			'labelFontColour' => false,
			'labelBackgroundColour' => false
		);

		if ($profileParams->get('label_colour') && $profileParams->get('label_font_colour')) {
			$result['labelFontColour'] = $profileParams->get('label_font_colour');
		}

		if ($profileParams->get('label_background') && $profileParams->get('label_background_colour')) {
			$result['labelBackgroundColour'] = $profileParams->get('label_background_colour');
		}

		$conversation = ES::table('Conversation');
		$conversation->loadByRelation($this->id, $viewer->id, SOCIAL_CONVERSATION_SINGLE, true);
		$conversationId = $conversation->id ? $conversation->id : false;

		if ($conversationId) {
			$conversationModel = ES::model('conversations');
			$isParticipated = $conversationModel->hasParticipateBefore($conversationId, $viewer->id);

			if (!$isParticipated) {
				$conversationId = false;
			}
		}

		$result['conversationId'] = $conversationId;

		// if we only want the minimal data, we should stop here.
		if ($minimalData) {
			return $result;
		}


		$totalFriends = 0;
		$totalMutualFriends = 0;
		$totalFollowing = 0;
		$totalFollowers = 0;
		$totalPoints = 0;
		$totalBadges = 0;

		if ($canViewFriend) {
			$totalFriends = $this->getTotalFriends();
			$totalMutualFriends = $this->getTotalMutualFriends($viewer->id);
		}

		if ($canViewFollower) {
			$totalFollowing = $this->getTotalFollowing();
			$totalFollowers = $this->getTotalFollowers();
		}

		if ($canViewPoints) {
			$totalPoints = $this->getPoints();
		}

		if ($canViewBadges) {
			$totalBadges = $this->getTotalBadges();
		}

		$result['totalFriends'] = $totalFriends;
		$result['totalMutualFriends'] = $totalMutualFriends;
		$result['totalFollowing'] = $totalFollowing;
		$result['totalFollowers'] = $totalFollowers;
		$result['points'] = array(
								'total' => $totalPoints
							);
		$result['steps'] = array();
		$result['achievements'] = array('total' => $totalBadges);
		$result['canPostStory'] = $canPostStory;
		$result['canViewFriend'] = $canViewFriend;
		$result['canViewFollower'] = $canViewFollower;
		$result['canViewPoints'] = $canViewPoints;
		$result['canViewBadges'] = $canViewBadges;
		$result['infoDownload'] = false;

		if ($downloadRequest && $this->config->get('users.download.enabled')) {

			$download = ES::table('Download');
			$download->load(array('userid' => $this->id));

			$obj = new stdClass();
			$obj->canRequest = $download->id ? false : true;
			$obj->isProcessing = false;
			$obj->isReady = false;
			$obj->downloadUrl = false;

			if ($download->id) {
				$obj->isProcessing = $download->isProcessing() || $download->isNew() ? true : false;
				$obj->isReady = $download->isReady() ? true : false;

				if ($obj->isReady) {
					$obj->downloadUrl = $download->getDownloadLink();
				}
			}
			$result['infoDownload'] = $obj;
		}

		if ($extended) {
			// Get Friends
			$model = ES::model('friends');
			$friends = $model->getFriends($this->id, array('state' => SOCIAL_FRIENDS_STATE_FRIENDS, 'limit' => 20));
			$result['friends'] = array();

			if ($friends) {
				foreach ($friends as $friend) {
					$result['friends'][] = $friend->toExportData($viewer);
				}
			}

			// Get Followers
			$model = ES::model('Followers');
			$followers = $model->getFollowers($viewer->id, array('limit' => 20));

			$result['followers'] = array();

			if ($followers) {
				foreach ($followers as $follower) {
					$result['followers'][] = $follower->toExportData($viewer);
				}
			}

			// Get following
			$following = $model->getFollowing($viewer->id, array('limit' => 20));

			$result['following'] = array();

			if ($following) {
				foreach ($following as $user) {
					$result['following'][] = $user->toExportData($viewer);
				}
			}

			// Get badges
			// $badges = $this->getBadges();

			// if ($badges) {
			// 	foreach ($badges as $badge) {
			// 		$result['achievements']['items'][] = $badge->toExportData($viewer);
			// 	}
			// }
		}

		// Get the user's age
		$birthday = $this->getFieldValue('BIRTHDAY');

		if ($birthday) {
			$result['age'] = $birthday->value->toAge();
		}

		// Get the user's gender
		$gender = $this->getFieldValue('GENDER');

		if ($gender) {
			$result['gender'] = $gender->data;
		}

		// Prepare DISPLAY custom fields
		if ($includeFields && $canViewProfile) {

			ES::language()->loadAdmin();

			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($this->getWorkflow()->id, SOCIAL_TYPE_PROFILES, SOCIAL_PROFILES_VIEW_DISPLAY);

			$library = ES::fields();
			$args = array(&$this);

			foreach ($steps as &$step) {

				$stepData = new stdClass();
				$stepData->id = $step->id;
				$stepData->title = JText::_($step->title);
				$stepData->description = JText::_($step->description);

				$fieldsModel = ES::model('Fields');
				$fieldOptions = array('step_id' => $step->id, 'data' => true, 'dataId' => $this->id, 'dataType' => SOCIAL_TYPE_USER, 'visible' => $fieldVisibility);
				$fields = $fieldsModel->getCustomFields($fieldOptions);

				$library->trigger('onGetValue', SOCIAL_FIELDS_GROUP_USER, $fields, $args);
				$validFields = array();

				foreach ($fields as $field) {
					$value = $field->value;

					if (empty($value)) {
						continue;
					}

					// check if this field privacy is viewable or not.
					$allowView = $viewerPrivacy->validate('field.' . $field->element, $field->id, 'field', $this->id);
					if (!$allowView) {
						continue;
					}

					$data = new stdClass();
					$data->id = $field->id;
					$data->type = $field->element;
					$data->name = JText::_($field->title);
					$data->value = !is_array($value) ? JText::_((string) $value) : $value;

					if ($field->element == 'birthday' || $field->element == 'datetime') {
						$fieldParams = $field->getParams();
						$datePrivacyType = $field->element == 'birthday' ? 'birthday.year' : 'year';

						$onlyAllowYear = false;
						$allowYear = true;

						if ($fieldParams->get('year_privacy')) {
							$allowYear = $viewerPrivacy->validate('field.' . $datePrivacyType, $field->id, $datePrivacyType, $this->id);
						}

						$format = $allowYear ? 'd M Y' : 'd M';

						switch($fieldParams->get('date_format')) {
							case 2:
							case '2':
								$format = $allowYear ? 'M d Y' : 'M d';
								break;
							case 3:
							case '3':
								$format = $allowYear ? 'Y d M' : 'd M';
								break;
							case 4:
							case '4':
								$format = $allowYear ? 'Y M d' : 'M d';
								break;
						}

						if ($onlyAllowYear) {
							$format = 'Y';
						}

						if ($value->data instanceof SocialFieldsUserDateTimeObject) {
							$data->value = $value->data->toFormat($format);
						} else {
							$data->value = ES::date($value->data)->toFormat($format);
						}
					}

					$data->rawValue = $field->element == 'joomla_fullname' ? $value->value : $value;
					$data->params = $field->getParams()->toObject();

					$validFields[] = $data;
				}

				$stepData->fields = $validFields;
				$step = $stepData;
			}

			$result['steps'] = $steps;
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}

/**
 * This class would be used to store all user objects
 *
 */
class SocialUserStorage
{
	static $users = array();
	static $fields = array();
	static $badges = array();
}
