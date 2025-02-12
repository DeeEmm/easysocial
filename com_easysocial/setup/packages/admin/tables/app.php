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

class SocialTableApp extends SocialTable
{
	/**
	 * The unique id of the application
	 * @var int
	 */
	public $id = null;

	/**
	 * The type of the application. E.g: fields, applications
	 * @var string
	 */
	public $type = null;

	/**
	 * Determines if the application is a core application.
	 * @var int
	 */
	public $core = null;

	/**
	 * Determines if the application is only used for processing only.
	 * @var int
	 */
	public $system		= null;

	/**
	 * Determines if the application is a unique application.
	 * @var int
	 */
	public $unique		= null;

	/**
	 * The unique element of the application.
	 * @var string
	 */
	public $element		= null;

	/**
	 * The group type of the application. E.g: people, groups , events etc.
	 * @var string
	 */
	public $group 		= null;

	/**
	 * The title of the application
	 * @var string
	 */
	public $title		= null;

	/**
	 * The permalink of the application
	 * @var string
	 */
	public $alias		= null;

	/**
	 * The state of the application
	 * @var int
	 */
	public $state		= null;

	/**
	 * The user visibility of the application
	 * @var int
	 */
	public $visible		= null;

	/**
	 * The creation date time.
	 * @var datetime
	 */
	public $created		= null;

	/**
	 * The ordering of the application
	 * @var int
	 */
	public $ordering	= null;

	/**
	 * Custom parameters for the application
	 * @var string
	 */
	public $params		= null;

	/**
	 * The version number of the application.
	 * @var string
	 */
	public $version			= null;

	/**
	 * The author of the application
	 * @var string
	 */
	public $author			= null;

	/**
	 * Determines if this app plans to load as widgets
	 * @var string
	 */
	public $widget			= null;

	/**
	 * Determines if this app would be installable
	 * @var string
	 */
	public $installable		= null;

	/**
	 * Determines if this app would be installable
	 * @var string
	 */
	public $default		= null;

	/**
	 * Used for caching internally.
	 * @var Array
	 */
	public $layouts 		= null;

	public function __construct(& $db )
	{
		parent::__construct( '#__social_apps' , 'id' , $db );
	}

	public function load($keys = null, $reset = true)
	{
		// Liase to Dbcache
		$cache = ES::dbcache('app');
		$data = $cache->get($keys);

		if (!$data) {
			return false;
		}

		$state = $this->bind($data);

		return $state;
	}

	public function bind($data, $ignore = array())
	{
		$state = parent::bind($data, $ignore);

		// Load the app's language
		$this->loadLanguage();

		return $state;
	}

	/**
	 * Deprecated. Use native load function with array keys instead.
	 * Loads the application given the `element`, `type` and `group`.
	 *
	 * @since	1.0
	 * @deprecated	1.2
	 * @access	public
	 */
	public function loadByElement($element, $group, $type)
	{
		return $this->load(array('element' => $element, 'group' => $group, 'type' => $type));
	}

	/**
	 * Deprecated. Use native load function with array keys instead.
	 * Loads an application by group
	 *
	 * @deprecated	1.2
	 * @return	boolean	True on success false otherwise
	 */
	public function loadByGroup($group, $element)
	{
		return $this->load(array('group' => $group, 'element' => $element));
	}

	/**
	 * Loads the app's css file.
	 *
	 * @param	string	$element	The element to look for.
	 * @return	boolean	True on success false otherwise
	 */
	public function loadCss()
	{
		$doc = JFactory::getDocument();

		$file = SOCIAL_APPS . '/' . $this->group . '/' . $this->element . '/assets/styles/style.css';

		jimport('joomla.filessytem.file');

		if (JFile::exists($file)) {
			$doc->addStyleSheet(rtrim(JURI::root(), '/') . '/media/com_easysocial/apps/' . $this->group . '/' . $this->element . '/assets/styles/style.css');
		}
	}

	/**
	 * Determine if the app has core settings
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCoreSettings()
	{
		$file = SOCIAL_APPS . '/' . $this->group . '/' . $this->element . '/config/core.json';

		jimport('joomla.filesystem.file');
		$exists = JFile::exists($file);

		// Fallback to default
		if (!$exists) {
			return false;
		}

		$core = ES::registry($file);

		return $core;
	}

	/**
	 * Determines if this app has user settings.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasUserSettings()
	{
		$file = SOCIAL_APPS . '/' . $this->group . '/' . $this->element . '/config/user.json';

		jimport('joomla.filesystem.file');
		$exists	= JFile::exists($file);

		return $exists;
	}

	/**
	 * Determines if this app has access settings
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function hasAccessSettings()
	{
		$coreSettings = $this->getCoreSettings();

		// Try to get the
		if ($coreSettings) {
			return $coreSettings->get('showAccessSettings', true);
		}

		return true;
	}

	/**
	 * Determines if the app should appear in the app listing
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function appListing($view, $uid = '', $type = '')
	{
		$apps = ES::apps();
		$hasListing = $apps->hasAppListing($this, $view, $uid, $type);

		return $hasListing;
	}

	/**
	 * Determines if specific object has access to the app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function hasAccess($profileId)
	{
		$access = $this->getAccess();

		// If nothing is selected, always allow by default
		if (!$access->id) {
			return true;
		}

		// return profile ids that has the access to this app.
		$allowed = $access->getAllowed();

		if (empty($allowed) || in_array($profileId, $allowed)) {
			return true;
		}

		return false;
	}


	/**
	 * Determines if specific object has access to the app
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function hasView($viewName)
	{
		// Ensure that they are all in lowercase
		$group = strtolower($this->group);
		$element = strtolower($this->element);

		// Build the path
		$path = SOCIAL_APPS . '/' . $group . '/' . $element . '/views/' . $viewName . '/view.html.php';

		jimport('joomla.filesystem.file');

		$state = JFile::exists($path);

		return $state;
	}


	/**
	 * Determines if the current application has a bookmark view.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 *
	 * @return	boolean		True if app contains a bookmark, false otherwise.
	 */
	public function hasDashboard()
	{
		// Ensure that they are all in lowercase
		$group = strtolower($this->group);
		$element = strtolower($this->element);

		// Build the path
		$path = SOCIAL_APPS . '/' . $group . '/' . $element . '/views/dashboard/view.html.php';

		jimport('joomla.filesystem.file');

		$state = JFile::exists($path);

		return $state;
	}

	/**
	 * Retrieve the local version of the application
	 *
	 * @since	1.0
	 * @access	public
	 * @param	null
	 * @return	string	The application version.
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Retrieves description of the app for the user.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserDesc()
	{
		$text = 'APP_' . strtoupper($this->element) . '_' . strtoupper($this->group) . '_DESC_USER';

		return JText::_($text);
	}

	/**
	 * Gets the application meta data from the manifest file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getMeta()
	{
		if ($this->type == 'fields') {
			$manifestFile 	= SOCIAL_APPS . '/' . $this->type . '/' . $this->group . '/' . $this->element . '/' . $this->element . '.xml';
		}

		if ($this->type == 'apps') {
			$manifestFile 	= SOCIAL_APPS . '/' . $this->group . '/' . $this->element . '/' . $this->element . '.xml';
		}

		$meta = new SocialAppMeta($manifestFile);

		return $meta;
	}

	/**
	 * Deletes any views that are related to the current view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function deleteExistingViews()
	{
		$model = ES::model('Apps');
		$state = $model->deleteExistingViews($this->id);

		return $state;
	}

	/**
	 * Retrieves the access table for the app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAccess()
	{
		static $items = array();

		if (!isset($items[$this->id])) {
			$access = ES::table('AppsAccess');
			$access->load(array('app_id' => $this->id));

			$items[$this->id] = $access;
		}

		return $items[$this->id];
	}

	/**
	 * Gets the acl type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAclType()
	{
		// Determines the acl type
		$aclType = 'profiles';

		if ($this->group == SOCIAL_TYPE_GROUP) {
			$aclType = 'groupcategories';
		}

		if ($this->group == SOCIAL_TYPE_EVENT) {
			$aclType = 'eventcategories';
		}

		if ($this->group == SOCIAL_TYPE_PAGE) {
			$aclType = 'pagecategories';
		}

		return $aclType;
	}

	/**
	 * Get's the application type in text.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTypeString()
	{
		$languageString = 'COM_EASYSOCIAL_APPS_TYPE_' . ESJString::strtoupper($this->type);

		return JText::_($languageString);
	}

	/**
	 * Gets a list of views that are assigned to this app.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getViews($viewName = '')
	{
		$model = ES::model('Apps');
		$appViews = $model->getViews($this->id);

		foreach ($appViews as $view) {
			if ($view->view == $viewName) {
				$view->title = trim($view->title);
				$view->description = trim($view->description);

				return $view;
			}
		}

		return false;
	}

	/**
	 * Gets a list of available layouts for this app.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLayout($layout = '')
	{
		static $layouts = array();

		if ($this->type !== SOCIAL_APPS_TYPE_APPS) {
			return false;
		}

		if (!$layouts[$this->id]) {

			// Build the path to the layouts file.
			$path = SOCIAL_APPS . '/' . $this->group . '/' . $this->element . '/config/layouts.json';

			jimport('joomla.filesystem.file');

			if (!JFile::exists($path)) {
				return false;
			}

			$contents = file_get_contents($path);
			$result = ES::json()->decode($contents);

			// Let's re-organize these layouts.
			$layouts = array();

			foreach ($result as $item) {
				$layouts[$item->view] = $item;
			}

			$this->layouts = $layouts;
		}

		return $this->layouts;
	}

	/**
	 * Determines if the app has activit log
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function hasActivityLog()
	{
		return ES::apps()->hasActivityLog($this);
	}

	/**
	 * Determines if the current app has already been installed by the user
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isInstalled($userId = null)
	{
		if (empty($userId)) {
			$userId = ES::user()->id;
		}

		$model = ES::model('Apps');
		$installed = $model->isInstalled($this->id, $userId);

		return $installed;
	}

	/**
	 * Determines if this app is available on the local directory
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isAvailableInStore()
	{
		$item = $this->getAppStoreItem();

		if (!$item->id) {
			return false;
		}

		return $item;
	}

	/**
	 * Determines if this app is outdated when compared with the app on the directory
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isOutdated()
	{
		// If the app is not available on the directory, there is nothing to compare and we wouldn't know the latest version.
		if (!$this->isAvailableInStore()) {
			return false;
		}

		// If the app is on the directory, we need to compare the versions.
		$store = $this->getAppStoreItem();

		$localVersion = $this->getVersion();
		$storeVersion = $store->version;

		$comparison = version_compare($localVersion, $storeVersion);

		// First version is lower than second version
		if ($comparison === -1) {
			return true;
		}

		return false;
	}

	/**
	 * Installs the app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function install($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$config = ES::config();
		$map = ES::table('appsmap');

		$map->app_id = $this->id;
		$map->uid = $userId;
		$map->type = SOCIAL_APPS_GROUP_USER;
		$map->created = ES::date()->toSql();

		$state = $map->store();

		if (!$state) {
			return false;
		}

		// @badge: apps.install
		// Assign a badge to the user when they install apps.
		$badge 	= ES::badges();
		$badge->log('com_easysocial', 'apps.install', $userId, JText::_('COM_EASYSOCIAL_APPS_BADGE_INSTALLED'));

		// Give points to the author when installing apps
		$points = ES::points();
		$points->assign('apps.install', 'com_easysocial', $userId);

		// Get the application settings
		$app = ES::table('App');
		$app->load( array('type' => SOCIAL_TYPE_APPS, 'group' => SOCIAL_TYPE_USER, 'element' => SOCIAL_TYPE_APPS));
		$params = $app->getParams();

		// If configured to publish on the stream, share this to the world.
		if ($app->id && $params->get('stream_install', true)) {

			// lets add a stream item here.
			$stream = ES::stream();
			$template = $stream->getTemplate();

			$template->setActor($user->id, SOCIAL_TYPE_USER);
			$template->setContext($this->id, SOCIAL_TYPE_APPS);
			$template->setVerb('install');
			$template->setType(SOCIAL_STREAM_DISPLAY_MINI);
			$template->setAggregate(false);
			$template->setParams($this);

			$template->setAccess('core.view');


			$stream->add($template);
		}

		return true;
	}

	/**
	 * Allows caller to uninstall this app from the site.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function uninstall()
	{
		$path = SOCIAL_APPS . '/' . $this->group . '/' . $this->element;
		$exists = JFolder::exists($path);

		// Check if the folder exists.
		if ($exists) {

			// Try to delete the folder
			// $state 	= JFolder::delete( $path );
		}

		// Delete app views
		$model = ES::model( 'Apps' );
		$model->deleteExistingViews( $this->id );

		// Just delete this record from the database.
		$state = $this->delete();

		// Remove the stream item as well.
		ES::stream()->delete($this->id, SOCIAL_TYPE_APPS);

		return $state;
	}

	/**
	 * Allows the caller to uninstall the app from the user.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function uninstallUserApp($userId = null)
	{
		$config = ES::config();
		$user = ES::user($userId);
		$userId = $user->id;

		// Delete user mapping
		$map = ES::table('appsmap');
		$map->load( array('app_id' => $this->id, 'uid' => $userId));

		$state = $map->delete();

		// Give points to the author when uninstalling apps
		if ($state) {
			$points = ES::points();
			$points->assign('apps.uninstall', 'com_easysocial', $userId);
		}

		// Delete any stream that's related to the user installing this app
		$stream	= ES::stream();
		$stream->delete($this->id, SOCIAL_TYPE_APPS, $userId);

		return $state;
	}

	/**
	 * Retrieves the new favicon style
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getFavIcon()
	{
		$app = ES::apps()->getApp($this);

		if (!$app) {
			return false;
		}

		if (!method_exists($app, __FUNCTION__ )) {
			return false;
		}

		return $app->getFavIcon();
	}

	/**
	 * Get's the icon's absolute path.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIcon($size = 'small')
	{
		$path = SOCIAL_APPS;

		if ($this->type == 'fields') {
			$path = $path . '/fields';
		}

		// If there's no icon provided for the app, we load our own default icons.
		$default = SOCIAL_DEFAULTS_URI . '/apps/' . $size . '.png';

		// Test for template overrides
		$app = JFactory::getApplication();
		$override = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_easysocial/apps/' . $this->group . '/' . $this->element . '/' . $size . '.png';

		if (JFile::exists($override)) {
			$url = JURI::root() . '/templates/' . $app->getTemplate() . '/html/com_easysocial/apps/' . $this->group . '/' . $this->element . '/' . $size . '.png';
			return $url;
		}


		$path = $path . '/' . $this->group . '/' . $this->element . '/assets/icons/' . $size . '.png';

		if (JFile::exists($path)) {
			$url = SOCIAL_APPS_URI;

			if ($this->type == 'fields') {
				$url = $url . '/fields';
			}

			$url = $url . '/' . $this->group . '/' . $this->element . '/assets/icons/' . $size . '.png';

			return $url;
		}

		return $default;
	}

	/**
	 * Retrieve user params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserParams($id = null)
	{
		$user = ES::user($id);
		$map = ES::table('AppsMap');
		$map->load(array('uid' => $user->id, 'app_id' => $this->id));

		$params = ES::registry($map->params);

		return $params;
	}

	/**
	 * Retrieve user params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getParams()
	{
		$params = ES::registry($this->params);

		return $params;
	}

	/**
	 * Render's application parameters form.
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function renderForm($type = 'form', $params = null, $prefix = '', $tabs = false)
	{
		// Get the manifest path.
		$file = $this->getManifestPath($type);

		if ($file === false) {
			return false;
		}

		$registry = ES::makeObject($file);

		$lib = ES::apps();

		// Check for custom callbacks
		foreach ($registry as &$section) {
			foreach ($section->fields as &$field) {

				if (isset($field->callback)) {

					$callable = $lib->getCallable($field->callback);

					if (!$callable) {
						continue;
					}

					$field->options = call_user_func_array($callable, array($this));
				}
			}
		}

		// Get the parameter object.
		$form = ES::form();
		$form->load($registry);

		if ($params) {
			$form->bind($params);
		} else {
			// Bind the stored data with the params.
			$form->bind($this->params);
		}

		// Get the HTML output.
		return $form->render($tabs, false, '', $prefix);
	}

	/**
	 * Returns the path of the manifest file for this application.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getManifestPath($type = 'config', $extension = 'json')
	{
		$name = strtolower($type === '' ? $this->element : $type);

		$path = SOCIAL_APPS;

		if ($this->type == 'fields') {
			$path = SOCIAL_FIELDS;
		}

		$path = $path . '/' . $this->group . '/' . $this->element . '/config/' . $type . '.' . $extension;

		jimport('joomla.filesystem.file');

		if (JFile::exists($path)) {
			return $path;
		}

		return false;
	}

	/**
	 * Returns the manifest of the application
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getManifest($manifestType = 'config')
	{
		// Get the path to the manifest file.
		$path = $this->getManifestPath($manifestType);

		if ($path === false) {
			return false;
		}

		$json = ES::json();

		// Get the json contents from the path.
		$raw = file_get_contents($path);

		// Let's decode the object now.
		$obj = $json->decode($raw);

		$this->getExtendedManifest($obj, $manifestType);

		return $obj;
	}

	private function getExtendedManifest(&$obj, $manifestType)
	{
		// It is possible that the manifest extends from another app/field
		if (!empty($obj->extends)) {

			// Extends should be in the format of:
			// "extends": "type/group/element"
			list($type, $group, $element) = explode('/', $obj->extends);
			$extendApp = ES::table('app');
			$state = $extendApp->load(array('type' => $type, 'group' => $group, 'element' => $element));

			if ($state) {
				$manifest = $extendApp->getManifest($manifestType);

				if ($manifest !== false) {

					// Manually perform a deep array merge
					foreach ($manifest as $name => $params) {

						// If this key does not exist in the parent object, then carry it over
						// Else, the child one should merge into parent
						if (!isset($obj->$name)) {
							$obj->$name = $params;
						} else {

							// Only do a merge if both parent and child is not a boolean
							// If either one is a boolean, then we just use the parent's data as is
							if (!is_bool($params) && !is_bool($obj->$name)) {
								$obj->$name = (object) array_merge((array) $params, (array) $obj->$name);
							}
						}
					}
				}
			}

			unset( $obj->extends );
		}
	}

	public function getElement()
	{
		return $this->element;
	}

	public function installAlerts()
	{
		$rules = $this->getManifest($this->element, 'alert');

		if (!$rules) {
			return false;
		}

		$alert = ES::alert($this->element);

		$options = array('core' => $this->core, 'app' => 1);

		foreach ($rules as $rulename => $values) {
			$alert->register($rulename, $values->email, $values->system, $options);
		}

		return true;
	}

	public function isCore()
	{
		return (bool) $this->core;
	}

	public function isUnique()
	{
		return (bool) $this->unique;
	}

	/**
	 * Returns the alias of the app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAlias()
	{
		$alias = $this->id . ':' . $this->alias;

		return $alias;
	}

	/**
	 * Retrieves the permalink of an app
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getPermalink($layout, $segments = array(), $xhtml = false)
	{
		$options = array('id' => $this->getAlias());

		$options['layout'] = $layout;

		// Merge the segments with the options
		$options = array_merge($options, $segments);

		if (isset($options['userId'])) {

			$user = ES::user($options['userId']);
			$options['uid'] = $user->getAlias();
			$options['type'] = SOCIAL_TYPE_USER;

			unset($options['userId']);
		}

		if (isset($options['groupId'])) {

			$group = ES::group($options['groupId']);
			$options['uid'] = $group->getAlias();
			$options['type'] = SOCIAL_TYPE_GROUP;

			unset($options['groupId']);
		}

		if (isset($options['eventId'])) {

			$event = ES::event($options['eventId']);
			$options['uid'] = $event->getAlias();
			$options['type'] = SOCIAL_TYPE_EVENT;

			unset($options['eventId']);
		}

		$url = FRoute::apps($options);

		return $url;
	}

	/**
	 * Gets the user permalink for the app
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getUserPermalink($userAlias)
	{
		// Get the profile view
		$view = $this->getViews('profile');
		$type = $view->type;

		// The app is embedded on the page
		if ($type == 'embed') {
			$url = FRoute::profile(array('id' => $userAlias, 'appId' => $this->getAlias()));

			return $url;
		}

		// If it's a canvas view
		$url = FRoute::apps(array('id' => $this->getAlias(), 'layout' => 'canvas', 'type' => 'user', 'uid' => $userAlias));

		return $url;
	}

	/**
	 * Retrieves the canvas url
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCanvasUrl($options = array(), $xhtml = true)
	{
		$default = array('layout' => 'canvas', 'id' => $this->getAlias());
		$options = array_merge($default, $options);

		$url = FRoute::apps($options, $xhtml);

		return $url;
	}

	/**
	 * Determines if the app is accessible by the provided user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function accessible($userId = null)
	{
		$user = ES::user($userId);

		if (!$this->hasAccess($user->profile_id)) {
			return false;
		}

		$installed = $this->isInstalled($user->id);

		if (!$installed && !$this->default) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the app store item for this app
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAppStoreItem()
	{
		static $items = array();

		$key = $this->type . $this->element . $this->group;

		if (!isset($items[$key])) {

			$store = ES::table('Store');
			$options = array('type' => $this->type, 'element' => $this->element, 'group' => $this->group);
			$store->load($options);

			$item = ES::store()->getApp($store);

			$items[$key] = $item;
		}

		return $items[$key];
	}

	public function getAppTitle($raw = false)
	{
		$element = strtoupper($this->element);
		$group = strtoupper($this->group);

		$title = 'APP_' . $element . '_' . $group . '_TITLE';

		if ($raw) {
			return $title;
		}

		return JText::_($title);
	}

	public function getTextAvatar()
	{
		$appTitle = $this->getAppTitle();

		// Return the first character of the title.
		return ESJString::substr($appTitle, 0, 1);
	}

	public function getAppClass()
	{
		static $classes = array();

		$className = '';

		if ($this->type == 'apps') {
			$className = 'Social' . ucfirst($this->group) . 'App' . ucfirst($this->element);
		}

		if ($this->type == 'fields') {
			$className = 'SocialFields' . ucfirst($this->group) . ucfirst($this->element);
		}

		if (empty($classes[$className])) {

			$root = $this->type == 'apps' ? SOCIAL_APPS : SOCIAL_FIELDS;

			$path = $root . '/' . $this->group . '/' . $this->element . '/' . $this->element . '.php';

			if (!JFile::exists($path)) {
				return false;
			}

			include_once($path);

			$args = array('group' => $this->group, 'element' => $this->element);

			$class = new $className($args);

			$classes[$className] = $class;
		}

		return $classes[$className];
	}


	/**
	 * Returns the page title
	 *
	 * @since	1.2.8
	 * @access	public
	 */
	public function getPageTitle()
	{
		$doc = ES::document();
		$title = $doc->getSiteTitle($this->get('title'));

		return $title;
	}

	/**
	 * Shorthand to load this app's language
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function loadLanguage($reload = false, $default = true)
	{
		if (empty($this->type) || empty($this->group) || empty($this->element)) {
			return false;
		}

		$method = '';

		if ($this->type == SOCIAL_APPS_TYPE_APPS) {
			$method = 'app';
		}

		if ($this->type == SOCIAL_APPS_TYPE_FIELDS) {
			$method = 'field';
		}

		if (!$method) {
			return false;
		}

		$method = 'load' . strtoupper($method);

		$lang = ES::language();

		$lang->$method($this->group, $this->element, $reload, $default);

		return true;
	}
}

class SocialAppMeta
{
	public $author = null;
	public $url = null;
	public $created = null;
	public $version = null;
	public $desc = null;

	/**
	 * This is the parser.
	 * @var SocialParser
	 */
	private $parser = null;

	public function __construct($path)
	{
		if (JFile::exists($path)) {
			$parser = ES::get('Parser');
			$parser->load($path);

			$this->parser = $parser;

			// Initialize variables.
			$this->init();
		}
	}

	/**
	 * Initializes all the properties.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function init()
	{
		// @TODO: Dynamically load all the methods that begins with "set"
		$this->setAuthor();
		$this->setURL();
		$this->setCreated();
		$this->setVersion();
		$this->setDescription();
	}

	/**
	 * Sets the app description
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function setDescription()
	{
		$desc = $this->parser->xpath('description');

		if (!empty($desc)) {
			$this->desc = (string) $desc[0];
			$this->desc = trim($this->desc);

			// Remove trailing whitespaces.
			$this->desc = JText::_($this->desc);
		}
	}

	/**
	 * Sets the author name.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function setAuthor()
	{
		$author = $this->parser->xpath('author');

		if (!empty($author)) {
			$this->author = (string) $author[0];
		}
	}

	/**
	 * Sets the author's url.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function setURL()
	{
		$url = $this->parser->xpath('url');

		if (!empty($url)) {
			$this->url = (string) $url[0];
		}
	}

	/**
	 * Sets the creation date
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function setCreated()
	{
		$created = $this->parser->xpath('created');

		if (!empty($created)) {
			$this->created = (string) $created[0];
		}
	}

	/**
	 * Sets the version
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function setVersion()
	{
		$version = $this->parser->xpath('version');

		if (!empty($version)) {
			$this->version = (string) $version[0];
		}
	}
}
