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

class SocialRouter
{
	/**
	 * Stores itself to be used statically.
	 * @var SocialRouter
	 */
	public static $instances = array();

	private $adapter = null;

	/**
	 * Creates a copy of it self and return to the caller.
	 *
	 * @since   1.0
	 * @access  public
	 *
	 */
	public static function getInstance($view)
	{
		if (!isset(self::$instances[$view])) {
			self::$instances[$view] = new self($view);
		}

		return self::$instances[$view];
	}

	public function __construct($view)
	{
		$file = dirname(__FILE__) . '/adapters/' . $view . '.php';
		if (!JFile::exists($file)) {
			return false;
		}

		require_once($file);

		$className = 'SocialRouter' . ucfirst($view);
		$this->adapter = new $className($view);
	}

	/**
	 * Parses SEF urls by the respective adapters
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function parse(&$segments)
	{
		if (is_null($this->adapter)) {
			return array();
		}

		$vars = $this->adapter->parse($segments);

		return $vars;
	}

	/**
	 * Pass url construction to the respective adapters
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function build(&$menu, &$query)
	{
		if (is_null($this->adapter) || !method_exists($this->adapter, 'build')) {
			return array();
		}

		$segments = $this->adapter->build($menu, $query);

		return $segments;
	}

	/**
	 * Route urls in EasySocial
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function route()
	{
		$args = func_get_args();

		if (count($args) > 0) {
			$options = $args[0];

			$args[0]['ssl'] = isset($options['ssl']) ? $options['ssl'] : null;
			$args[0]['tokenize'] = isset($options['tokenize']) ? $options['tokenize'] : null;
			$args[0]['external'] = isset($options['external']) ? $options['external'] : null;
			$args[0]['tmpl'] = isset($options['tmpl']) ? $options['tmpl'] : null;
			$args[0]['controller'] = isset($options['controller']) ? $options['controller'] : null;
			$args[0]['sef'] = isset($options['sef']) ? $options['sef'] : null;
			$args[0]['adminSef'] = isset($options['adminSef']) ? $options['adminSef'] : null;
		} else {
			$args[0] = array();
			$args[0]['ssl'] = null;
			$args[0]['tokenize'] = null;
			$args[0]['external'] = null;
			$args[0]['tmpl'] = null;
			$args[0]['controller'] = '';
			$args[0]['sef'] = null;
			$args[0]['adminSef'] = null;
		}

		return call_user_func_array(array($this->adapter, __FUNCTION__), $args);
	}
}

abstract class SocialRouterAdapter
{
	public $config = null;
	public $name;

	static $base = 'index.php?option=com_easysocial';

	public function __construct($view)
	{
		ES::language()->loadSite();

		$this->config = ES::config();
		$this->name = $view;
	}

	/**
	 * Translates a url
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function translate($str)
	{
		ES::language()->loadSite();

		$str = ESJString::strtoupper($str);
		$text = 'COM_EASYSOCIAL_ROUTER_' . $str;

		return JText::_($text);
	}

	/**
	 * Normalizes an array
	 *
	 * @since   1.4
	 * @access  public
	 */
	public function normalize($arr, $index, $default = null)
	{
		if (isset($arr[$index])) {
			return $arr[$index];
		}

		return $default;
	}

	/**
	 * Builds the URLs for apps view
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function route($options = array(), $xhtml = true)
	{
		$url = self::$base . '&view=' . $this->name;

		// Custom options
		$ssl = $options['ssl'];
		$tokenize = $options['tokenize'];
		$external = $options['external'];
		$tmpl = $options['tmpl'];
		$sef = $options['sef'];
		$adminSef = isset($options['adminSef']) ? $options['adminSef'] : false;
		$layout = isset($options['layout']) ? $options['layout'] : '';
		$type = isset($options['type']) ? $options['type'] : '';

		$lang = isset($options['lang']) ? $options['lang'] : '';

		// check if the current request is from feed page or not.
		// if yes, let set the external to always true.
		$pageFormat = ES::input()->get('format', '', 'var');

		if (! $external && $pageFormat == 'feed') {
			$external = true;
		}

		// Determines if this is a request to the controller
		$controller = $options['controller'];
		$data = array();

		unset($options['ssl'] , $options['tokenize'] , $options['external'] , $options['tmpl'] , $options['controller'], $options['sef'], $options['adminSef']);

		if ($options) {

			foreach ($options as $key => $value) {
				$data[] = $key . '=' . $value;
			}
		}

		$query = $options;
		$options = implode('&' , $data);
		$join = !empty($options) ? '&' : '';
		$url = $url . $join . $options;

		// Try to get the url from the adapter
		$overrideUrl = '';

		// Set temporary data
		$query['view'] = $this->name;
		$query['option'] = 'com_easysocial';

		// Ensure that all query values are lowercased
		$query = array_map(array('ESJString', 'strtolower'), $query);

		// Let's find for a suitable menu
		$view = $this->name;
		$xView = $this->name; // use for cluster redirection.
		$layout = isset($query['layout']) ? $query['layout'] : '';
		$id = isset($query['id']) ? (int) $query['id'] : '';
		$menuId = null;

		// this section is the handle albums / photos / videos for group
		$uId = isset($query['uid']) ? $query['uid'] : '';
		$uType = isset($query['type']) ? $query['type'] : '';


		if ($view == 'polls') {
			$uId = isset($query['clusterId']) ? $query['clusterId'] : $uId;
			$uType = isset($query['clusterType']) ? $query['clusterType'] : $uType;
		}

		if (($view == 'videos' || $view == 'albums' || $view == 'photos' || $view == 'apps' || $view == 'events' || $view == 'audios' || $view == 'marketplaces' || $view == 'polls')
			&& $uId && $uType && ($uType == 'group' || $uType == 'event' || $uType == 'page')) {

			if (($view == 'albums' || $view == 'videos' || $view == 'events' || $view == 'audios' || $view == 'marketplaces') && $id) {
				// check if the object already has it own menu item or not.
				$menuId = FRoute::getItemId($view, $layout, $id, $type, false, false, $lang);
			}

			if (! $menuId) {
				// here we try to get the cluster menu item
				$tempView = 'groups';
				if ($uType == 'event') {
					$tempView = 'events';
				} else if ($uType == 'page') {
					$tempView = 'pages';
				}

				$xmenuId = FRoute::getItemId($tempView, 'item', $uId, $type, true, false, $lang);

				if (! $xmenuId) {
					// try get any of the clusters menu item.
					$xmenuId = FRoute::getItemId($tempView, '', $uId, $type, true, false, $lang);
				}

				if ($xmenuId) {
					$menuId = $xmenuId;
				}
			}
		}


		// For users photos, we want to fetch menu from "All Albums"
		if (!$menuId && $view == 'photos' && $uId && $uType == 'user') {
			// $view = 'albums';
			// $layout = 'all';
			// $id = '';
			$menuId = FRoute::getItemId('albums', 'all', '', '', true, false, $lang);
		}

		// user albums
		if (!$menuId && $view == 'albums' && !$layout && $uId && $uType == 'user') {

			$menuId = FRoute::getItemId($view, $layout, $uId, $uType, true, true, $lang);

			if (!$menuId) {
				if (! $menuId) {
					$menuId = FRoute::getItemId($view, 'all', '', '', true, false, $lang);
				}
			}

			// Ensure that the alias is not 'photos' or it will caused conflict. #2887
			if ($menuId) {
				$menu = JFactory::getApplication()->getMenu('site')->getItem($menuId);

				// Reset the menu
				if ($menu->alias == 'photos') {
					$menuId = false;
				}
			}
		}

		// single user albums
		if (!$menuId && $view == 'albums' && $layout && $uId && $uType == 'user') {

			$menuId = FRoute::getItemId($view, $layout, $id, '', false, true, $lang);

			if (! $menuId) {
				$menuId = FRoute::getItemId($view, 'all', '', '', true, false, $lang);
			}
		}

		// normal albums
		if (!$menuId && $view == 'albums' && !$layout && !$id && !$uId && !$uType) {
			// lets try to get all albums layout.
			$layout = 'all';

			$menuId = FRoute::getItemId($view, $layout, '', '', true, false, $lang);
		}


		// videos category
		if (!$menuId && $view == 'videos') {

			$categeryId = isset($query['categoryId']) ? (int) $query['categoryId'] : '';
			if ($categeryId) {
				$menuId = FRoute::getItemId($view, '', $categeryId, 'categoryId', true, false, $lang);
			}
		}

		// Audio Genre
		if (!$menuId && $view == 'audios') {

			$categeryId = isset($query['genreid']) ? (int) $query['genreid'] : '';
			if ($categeryId) {
				$menuId = FRoute::getItemId($view, '', $categeryId, 'genreid', true, false, $lang);
			}
		}

		// registration with profile id #553
		// registraton menu item is abit special as the menu item do not have layouts.
		// apart from the layouts, when going through the registraion steps, the profil_id will be missing from the link,
		// thus we canot rely on the profile_id to retrieve the correct menu item id.
		// if the current active menu item is belong to registrion, we will just use it.
		if (!$menuId && $view == 'registration') {

			$activeMenu = JFactory::getApplication()->getMenu('site')->getActive();

			if (isset($query['profile_id']) && $query['profile_id'] != '') {
				$menuId = FRoute::getItemId($view, '', $query['profile_id'], 'profile_id', true);
			} else {
				if ($activeMenu && isset($activeMenu->query['view']) && $activeMenu->query['view'] == 'registration') {

					$menuId = $activeMenu->id;
				}
			}

		}

		if (! $menuId) {
			$menuId = FRoute::getItemId($view, $layout, $id, $type, false, false, $lang);
		}

		if ($menuId) {

			// $menu = JFactory::getApplication()->getMenu('site')->getItem($menuId);
			$menu = ES::getSiteMenu()->getItem($menuId);

			if ($menu) {

				$current = $menu->query;
				$tmpQuery = $query;

				if (isset($current['layout']) && $current['layout']) {
					$current['layout'] = strtolower($current['layout']);
				}

				if (isset($tmpQuery['layout']) && $tmpQuery['layout']) {
					$tmpQuery['layout'] = strtolower($tmpQuery['layout']);
				}

				// special handle for albums.
				// sometime the query has the uid and utype but the menuitem->query do not store this information thus the diff no longer bcome accurate.
				if (isset($tmpQuery['view']) && ($tmpQuery['view'] == 'albums' || $tmpQuery['view'] == 'photos' || $tmpQuery['view'] == 'videos' || $tmpQuery['view'] == 'audios' || $tmpQuery['view'] == 'marketplaces') && isset($tmpQuery['uid']) && isset($tmpQuery['id'])) {
					unset($tmpQuery['uid']);

					if (isset($tmpQuery['type'])) {
						unset($tmpQuery['type']);
					}
				}

				if (isset($current['id']) && !empty($current['id'])) {
					$current['id'] = (int) $current['id'];
				}

				if (isset($tmpQuery['id'])) {
					$tmpQuery['id'] = (int) $tmpQuery['id'];
				}

				if (isset($current['categoryId']) && !empty($current['categoryId'])) {
					$current['categoryId'] = (int) $current['categoryId'];
				}

				if (isset($tmpQuery['categoryId'])) {
					$tmpQuery['categoryId'] = (int) $tmpQuery['categoryId'];
				}

				if (isset($tmpQuery['lang'])) {
					unset($tmpQuery['lang']);
				}

				$hasDiff = array_diff($tmpQuery, $current);

				// // If there's no difference in both sets of query, we can safely assume that there's already
				// // a menu for this link
				if (empty($hasDiff)) {
					$overrideUrl = 'index.php?Itemid=' . $menuId;

					if ($lang) {
						$overrideUrl .= '&lang=' . $lang;
					}
				}

			}
		}

		// If there are no overriden url's, we append our own item id.
		if ($overrideUrl) {
			$url = $overrideUrl;
		} else {
			// If there is no getUrl method, we want to get the default item id.
			if ($menuId){
				$url .= '&Itemid=' . $menuId;
			} else {
				$url .= '&Itemid=' . ESR::getItemId($view, $layout, $id, $type, true, false, $lang);
			}
		}

		return ESR::_($url, $xhtml, array(), $ssl, $tokenize, $external, $tmpl, $controller, $sef, $adminSef);
	}

	/**
	 * Retrieves the user id
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getUserId($permalink)
	{
		static $loaded  = array();

		// Joomla always replaces the first : with a -
		$permalink = str_ireplace(':' , '-' , $permalink);

		if (!isset($loaded[$permalink])) {
			$config = ES::config();

			// Always test for the user's stored permalink first.
			$model = ES::model('Users');
			$id = $model->getUserFromPermalink($permalink);

			if ($id) {
				$loaded[$permalink] = $id;

				return $loaded[$permalink];
			}

			// Always test for the user's stored permalink first.
			$id = $model->getUserFromAlias($permalink);

			if ($id) {
				$loaded[$permalink] = $id;

				return $loaded[$permalink];
			}

			// If there's no permalink or alias found for the user, we know the syntax
			// by default would be ID:Username or ID:Full Name
			$loaded[$permalink] = $this->getIdFromPermalink($permalink);

			return $loaded[$permalink];
		}

		return $loaded[$permalink];
	}

	/**
	 * Returns the user's permalink
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getUserPermalink($fragment)
	{
		static $users = array();

		if (!isset($users[$fragment])) {
			$config = ES::config();

			// Since id is always in ID:alias format.
			$id = explode(':', $fragment);

			$segment = '';

			if (count($id) == 1) {
				$segment = $id[0];
			} else {
				// Check whether this is a user alias.
				$permalink = $id[1];

				// If this is an alias that the user set, just use it as is
				$model  = ES::model('Users');
				if ($config->get('users.aliasName') == 'username' || $model->isValidUserPermalink($permalink)) {
					$segment = $permalink;
				} else {
					// Otherwise, this is a real name and we have to always prepend the id.
					$segment = $id[0] . ':' . $permalink;
				}
			}

			$users[$fragment] = $segment;
		}

		return $users[$fragment];
	}

	/**
	 * Retrieves the app id
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAppId($alias)
	{
		$parts = explode('-' , $alias);

		if (count($parts) > 1) {
			return $parts[0];
		}

		$app = ES::table('App');
		$app->load(array('alias' => $alias));

		return $app->id;
	}

	/**
	 * Retrieves the group id
	 *
	 * @since   3.2
	 * @access  public
	 */
	public function getClusterId($alias, $type)
	{
		$id = $alias;

		// need to respect the ID sef setting
		if (!$this->config->get('seo.useid')) {
			$table = ES::table('Cluster');
			$table->load(array('alias' => $alias, 'cluster_type' => $type));

			$id = $table->id;
		} else {
			if (strpos($alias , ':') !== false) {
				$parts = explode(':', $alias , 2);

				$id = $parts[0];
			}
		}

		return $id;
	}

	/**
	 * Retrieves the id based on the permalink
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getIdFromPermalink($permalink, $type = '')
	{
		$id = $permalink;

		if (!empty($type)) {
			if ($type == SOCIAL_TYPE_USER) {
				$id = $this->getUserId($permalink);

				return $id;
			}

			if ($type == SOCIAL_TYPE_APPS) {
				$id = $this->getAppId($permalink);

				return $id;
			}

			$clusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT);

			if (in_array($type, $clusters)) {
				$id = $this->getClusterId($permalink, $type);

				return $id;
			}
		}

		if (strpos($permalink , ':') !== false) {
			$parts = explode(':', $permalink , 2);

			$id = $parts[0];
		}

		return $id;
	}

	/**
	 * Retrieves a list of layouts from a particular view
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getAvailableLayouts($viewName)
	{
		$viewName = (string) $viewName;
		$file = SOCIAL_SITE . '/views/' . strtolower($viewName) . '/view.html.php';

		jimport('joomla.filesystem.file');

		if (!JFile::exists($file)) {
			return array();
		}

		require_once($file);

		$layouts = get_class_methods('EasySocialView' . $viewName);

		return $layouts;

	}

}
