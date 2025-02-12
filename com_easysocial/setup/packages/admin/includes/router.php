<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/router/router');

class ESR
{
	static $base = 'index.php?option=com_easysocial';
	static $views = array(
							'account',
							'activities',
							'albums',
							'apps',
							'ads',
							'advertiser',
							'badges',
							'conversations',
							'events',
							'groups',
							'dashboard',
							'fields',
							'friends',
							'followers',
							'profile',
							'profiles',
							'unity',
							'users',
							'stream',
							'notifications',
							'leaderboard',
							'points',
							'photos',
							'registration',
							'search',
							'login',
							'audios',
							'videos',
							'polls',
							'pages',
							'manage',
							'download',
							'sharer'
						);

	/**
	 * Translates URL to SEF friendly
	 *
	 * External true SEF true
	 * http://solo.dev/joomla321/dashboard/registration/oauthDialog/facebook
	 * External true SEF false
	 * http://solo.dev/joomla321/index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=facebook&Itemid=135
	 * External false SEF true
	 * /joomla321/dashboard/registration/oauthDialog/facebook
	 * External false SEF false
	 * index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=facebook&Itemid=135
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function _($url , $xhtml = false , $view = array() , $ssl = null , $tokenize = false , $external = false , $tmpl = '' , $controller = '', $sef = true, $adminSef = false)
	{
		if ($tokenize) {
			$url .= '&' . ES::token() . '=1';
		}

		if (!empty($controller)) {
			$url = $url . '&controller=' . $controller;
		}

		// If this is an external URL, we want to fetch the full URL.
		if ($external) {
			return ESR::external($url , $xhtml , $ssl , $tmpl, $sef, $adminSef);
		}

		if (!empty($controller) && $sef) {
			$url = JRoute::_($url , $xhtml);
			return $url;
		}

		// We don't want to do any sef routing here.
		// Only external = false and sef = false will come here
		// IMPORTANT: handler needs to FRoute::_() the link
		if ($tmpl == 'component' || $sef === false) {
			return $url;
		}

		return JRoute::_($url , $xhtml , $ssl);
	}

	/**
	 * method to generate frontend sef links.
	 *
	 * @since   1.0
	 * @access  public
	 */
	private static function generateSEF($url, $xhtml = true, $ssl = null)
	{
		static $_router = null;

		// if Jroute already support link method, lets use it.
		// Joomla 3.9 and above should work with this Jroute::link.
		if (method_exists('JRoute', 'link')) {

			// In Easysocial, the url that passed in already has the Itemid,
			// thus we can directly pass the url to JRoute::link.

			$sef = JRoute::link('site', $url, $xhtml, $ssl);
			return $sef;
		}

		// look like JRoute::link not found.
		// lets manually generate the link.

		$client = 'site';

		if (is_null($_router)) {
			$app = JApplication::getInstance($client);
			$_router = $app->getRouter($client);
		}

		// If we cannot process this $url exit early.
		if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0)) {
			return $url;
		}

		// Make sure that we have our router
		if (is_null($_router) || !$_router) {
			return $url;
		}

		// Build route.
		$uri = $_router->build($url);


		$scheme = array('path', 'query', 'fragment');

		/*
		 * Get the secure/unsecure URLs.
		 *
		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		if ((int) $ssl || $uri->isSsl()) {
			static $host_port;

			if (!is_array($host_port)) {
				$uri2 = Uri::getInstance();
				$host_port = array($uri2->getHost(), $uri2->getPort());
			}

			// Determine which scheme we want.
			$uri->setScheme(((int) $ssl === 1 || $uri->isSsl()) ? 'https' : 'http');
			$uri->setHost($host_port[0]);
			$uri->setPort($host_port[1]);
			$scheme = array_merge($scheme, array('host', 'port', 'scheme'));
		}

		$url = $uri->toString($scheme);

		// just to make sure the url has no 'administrator' segment
		$url = str_replace('/administrator/', '/', $url);

		// Replace spaces.
		$url = preg_replace('/\s/u', '%20', $url);

		if ($xhtml) {
			$url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');
		}

		return $url;
	}


	/**
	 * Returns the raw url without going through any sef urls.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function raw($url)
	{
		$uri = rtrim(JURI::root() , '/') . '/' . $url;
		return $uri;
	}

	/**
	 * Builds an external URL that may be used in an email or other external apps
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function external($url , $xhtml = false , $ssl = null , $tmpl = false, $sef = true, $adminSef = false)
	{
		$uri = JURI::getInstance();

		// If this is an external URL, we will not want to xhtml it.
		$xhtml = false;

		// Send the URL for processing only if tmpl != component
		if ($tmpl !== 'component' && $sef !== false) {
			if (ES::isFromAdmin() && $adminSef) {
				$url = self::generateSEF($url, $xhtml, $ssl);
			} else {
				$url = FRoute::_($url , $xhtml , array(), $ssl, false, false, '', '', $sef);
			}
		}

		// Remove the /administrator/ part from the URL.
		$url = str_ireplace('/administrator/' , '/' , $url);
		$url = ltrim($url , '/');

		if ($sef === false || $tmpl === 'component') {
			// If we do not want sef, then we need to manually append the front part taking into account that this is not JRouted, hence we use JURI::root() to ensure subfolders
			$url = rtrim(JURI::root(), '/') . '/' .  $url;
		} else {
			// We need to use $uri->toString() because JURI::root() may contain a subfolder which will be duplicated
			// since $url already has the subfolder.
			$url = $uri->toString(array('scheme' , 'host' , 'port')) . '/' . $url;
		}

		return $url;
	}

	/**
	 * Adding token into url
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function tokenize($url , $xhtml = false , $ssl = null)
	{
		$url .= '&' . ES::token() . '=1';

		return FRoute::_($url , $xhtml , $ssl);
	}

	/**
	 * Retrieves the current url that is being accessed.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function current($isCallback = false)
	{
		$uri = self::getCurrentURI();

		if ($isCallback) {
			return '&callback=' . base64_encode($uri);
		}

		return $uri;
	}

	/**
	 * Retrieves the referer url that is being accessed.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public static function referer($isCallback = false)
	{
		$uri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

		if ($isCallback) {
			return '&callback=' . base64_encode($uri);
		}

		return $uri;
	}

	/**
	 * Retrieves the default menu id
	 *
	 * @since   1.2
	 * @access  public
	 */
	public static function getDefaultItemId($view)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Get the first public page available if there's any
		$sql->select('#__menu');
		$sql->where('link' , 'index.php?option=com_easysocial&view=dashboard%' , 'LIKE');
		$sql->where('published' , SOCIAL_STATE_PUBLISHED);

		$db->setQuery($sql);
		$id = $db->loadResult();

		// Check for more specificity
		if (!$id) {
			$sql->clear();
			$sql->select('#__menu');
			$sql->where('link' , 'index.php?option=com_easysocial&view=' . $view , '=');
			$sql->where('published' , SOCIAL_STATE_PUBLISHED);

			$db->setQuery($sql);
			$id = $db->loadResult();
		}

		// If the url doesn't exist, we use "LIKE" to search instead.
		if (!$id) {
			$sql->clear();
			$sql->select('#__menu');
			$sql->where('link' , 'index.php?option=com_easysocial%' , 'LIKE');
			$sql->where('published' , SOCIAL_STATE_PUBLISHED);

			$db->setQuery($sql);
			$id = $db->loadResult();
		}

		if (!$id) {
			// Try to get from the current Itemid in query string
			$id = $this->input->get('Itemid' , 0, 'int');
		}

		if (!$id) {
			// Try to get
			$id = false;
		}

		return $id;
	}

	/**
	 * Get the current url used for return segment in login form.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function getCurrentURI()
	{
		$url = ES::getURI(true);

		$app = JFactory::getApplication();
		$router = $app->getRouter();

		$pluginEnabled = JPluginHelper::isEnabled('system', 'languagefilter');

		if (ESRouter::getMode() == SOCIAL_ROUTER_MODE_SEF && $pluginEnabled) {
			// let get the non-sef url
			$router = JRouter::getInstance('site');
			$vars = $router->getVars();

			$isEasySocialUrlPluginInstalled = JPluginHelper::getPlugin('system', 'easysocialurl');
			$isEasySocialUrlPluginEnabled = JPluginHelper::isEnabled('system', 'easysocialurl');

			// $isEasySocialUrlPluginInstalled = false;

			if ($isEasySocialUrlPluginInstalled && $isEasySocialUrlPluginEnabled) {
				$uri = JUri::getInstance();

				JPluginHelper::importPlugin('system', 'easysocialurl');

				$args = array(&$router, &$uri);
				$results = ESDispatcher::trigger('getUriVars', $args);

				if ($results && isset($results[0]) && $results[0]) {
					$results = $results[0];
					if ((isset($results['option']) && $results['option'] == 'com_easysocial')
						&& (isset($results['view']) && $results['view'] == 'profile')
						&& (isset($results['id']) && $results['id']) ) {
						$vars = $results;
					}
				}
			}

			if ((isset($vars['option']) && $vars['option'] == 'com_easysocial')
				&& (isset($vars['view']) && $vars['view'] == 'profile')
				&& (isset($vars['id']) && $vars['id']) ) {

				$id = (int) $vars['id'];
				if ($id) {
					$user = ES::user($id);
					$url = $user->getPermalink();
					return $url;
				}
			}

			// build the the query as a string
			$url = 'index.php?' . JUri::buildQuery($vars);

			// lets sef it.
			$url = JRoute::_($url);
		}

		return $url;
	}

	/**
	 * Generates the login menu link
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public static function getLoginRedirectionLink()
	{
		static $url = null;

		if (is_null($url)) {
			$config = ES::config();
			$loginMenu = $config->get('general.site.login');

			if ($loginMenu == 'null') {
				$url = ESR::dashboard([], false);

				return $url;
			}

			$url = ESR::getMenuLink($loginMenu);
			$url = ESR::_($url, false);
		}

		return $url;
	}

	/**
	 * Given a menu item id, try to get the link
	 *
	 * @since   1.3
	 * @access  public
	 */
	public static function getMenuLink($menuId, $autologin = false)
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		if (!$menuId || $menuId == 'null') {
			// get the current url in non-sef if language fitler plugin enabled.
			$link = self::getCurrentURI();

			return $link;
		}

		$menuItem = $menu->getItem($menuId);

		$languageFilterEnabled = JPluginHelper::isEnabled('system', 'languagefilter');
		$langCode = false;

		// If language filter is enabled, we also need to cater for the language
		if ($languageFilterEnabled && $autologin) {

			$currentLanguage = $app->input->get('lang', '', 'cmd');

			$plugin = JPluginHelper::getPlugin('system', 'languagefilter');
			$params = new JRegistry();
			$params->loadString(empty($plugin) ? '' : $plugin->params);

			// if 'Automatic Language Change' is enabled, we should get the user's language as current language
			// Then redirect to menu item that has the same language as this
			if ($params->get('automatic_change', 1)) {
				$user = JFactory::getUser();
				$siteLanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				$currentLanguage = $user->getParam('language', $siteLanguage);
			}

			// We need to get the associated menu for that provided menu item that use the same language
			$menuItem = self::getAssociatedMenu($menuItem, $currentLanguage);
			$langCode = substr($menuItem->language, 0, 2);
		}

		$link = false;

		if (!$menuItem) {
			return $link;
		}

		$joomlaVersion = ES::getInstance('version')->getLongVersion();
		$appendDomain = true;

		// For menus linked within EasySocial, use our own router
		if ($menuItem->component == 'com_easysocial') {
			$view = isset($menuItem->query['view']) ? $menuItem->query['view'] : '';

			if ($view) {

				// it seems like in Joomla 3.6.3 and above, if the url doesn't have itemid, the language filter plugin will failed
				// to redirect user to the associated menu item. Due to this, we cannot use the sef link for return url.
				if (version_compare($joomlaVersion, '3.6.2', '>')) {

					$appendDomain = false;

					if (strpos($menuItem->link, '?') > 0) {
						$link = $menuItem->link . '&Itemid=' . $menuItem->id;
					} else {
						$link = $menuItem->link . '?Itemid=' . $menuItem->id;
					}

					if ($langCode) {
						$link .= '&lang=' . $langCode;
					}


				} else {

					$queries = $menuItem->query;

					unset($queries['option']);
					unset($queries['view']);

					$options = array($queries, false);

					$link = call_user_func_array(array('FRoute', $view), $options);
				}
			}
		}

		// For menus not linked to EasySocial
		if ($menuItem->component != 'com_easysocial') {

			if ($menuItem->type == 'url') {

				// If the url already has http://, we don't append the domain.
				if (strpos($menuItem->link, 'http://') !== false || strpos($menuItem->link, 'https://') !== false) {
					$appendDomain = false;
				}
			}

			// it seems like in Joomla 3.6.3 and above, if the url doesn't have itemid, the language filter plugin will failed
			// to redirect user to the associated menu item. Due to this, we cannot use the sef link for return url.
			if (version_compare($joomlaVersion, '3.6.2', '>')) {
				if (strpos($menuItem->link, '?') > 0) {
					$link = $menuItem->link . '&Itemid=' . $menuItem->id;
				} else {
					$link = $menuItem->link . '?Itemid=' . $menuItem->id;
				}

			} else {
				if (strpos($menuItem->link, '?') > 0) {
					$link = JRoute::_($menuItem->link . '&Itemid=' . $menuItem->id, false);
				} else {
					$link = JRoute::_($menuItem->link . '?Itemid=' . $menuItem->id, false);
				}
			}

			if ($langCode) {
				$link .= '&lang=' . $langCode;
			}

			// If this menu is a home menu item, the link should just be the site url.
			if (!$link || (isset($menuItem->home) && $menuItem->home)) {
				$link = JURI::root();
			}

			$link = JRoute::_($link);
		}

		// It seems like Joomla do not accept those redirection URL without complete URL,
		// it will always fall back to site homepage. We need to prepend the site's url on SEF mode.
		$router = $app->getRouter();

		// Cleanup the url if the configuration is set to non-sef. #294
		if (ESRouter::getMode() != SOCIAL_ROUTER_MODE_SEF) {
			$link = html_entity_decode($link);
		}

		// Here we need to ensure that subfolders are removed from the url to prevent issues with
		// getting the correct link if the site is running on a subfolder.
		// Also need to make sure if the redirection is point back to default homepage, no need to process this.
		if ($appendDomain && ESRouter::getMode() == SOCIAL_ROUTER_MODE_SEF && ($menuItem->home != true)) {

			$subfolder = JURI::root(true);
			$base = str_ireplace($subfolder, '', rtrim(JURI::root(), '/'));

			// For those subfolder name same with the subdomain, it will caused the replace issue.
			// This part is make sure only replace with the subfolder name only
			if (!empty($subfolder)) {
				$subfolder = $subfolder . '/';
				$base = str_ireplace($subfolder, '', rtrim(JURI::root()));
			}

			$link = $base . $link;
		}

		return $link;

	}

	/**
	 * Retrieve associated menu
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public static function getAssociatedMenu($menuItem, $currentLanguage)
	{
		$associatedMenus = ESMenuHelper::getAssociations($menuItem->id);
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		if (isset($associatedMenus[$currentLanguage]) && $menu->getItem($associatedMenus[$currentLanguage])) {
			$associationItemid = $associatedMenus[$currentLanguage];

			$menuItem = $menu->getItem($associationItemid);
		}

		return $menuItem;
	}

	/**
	 * Retrieves the item id based on the view and the layout.
	 *
	 * @since   1.2
	 * @access	public
	 */
	public static function getMenus($view , $layout = null, $id = null, $useUID = false, $useExact = true, $lang = '')
	{
		static $menus = null;
		static $selection = array();
		static $_loaded = array();

		// Always ensure that layout is lowercased
		$layout = strtolower($layout);

		// We want to cache the selection user made.
		$key = $view . $layout . $id;
		$language = false;
		$languageTag = false;

		// If language filter is enabled, we need to get the language tag
		if (!ES::isFromAdmin() && !ES::isCli()) {

			if (! $lang) {
				$language = JFactory::getApplication()->getLanguageFilter();
				$languageTag = JFactory::getLanguage()->getTag();
			} else {
				$language = true;
				$languageTag = $lang;
			}
		}

		// we only get the key for the current active language
		if (isset($languageTag) && $languageTag) {
			$key .= $languageTag;
		}

		// Preload the list of menus based on the current active language
		if (!isset($_loaded[$languageTag])) {

			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__menu');
			$sql->where('published' , SOCIAL_STATE_PUBLISHED);
			$sql->where('link' , 'index.php?option=com_easysocial%' , 'LIKE');

			if ($language) {
				$sql->where('(', '', '', 'AND');
				$sql->where('language', $languageTag, '=', 'OR');
				$sql->where('language', '*', '=', 'OR');
				$sql->where(')');
			}

			// we only want frontend menu item.
			$sql->where('client_id', '0');

			// echo $sql;
			// echo '<br><br>';

			$db->setQuery($sql);

			$result = $db->loadObjectList();
			$menus = array();

			// We need to format them accordingly.
			if (!$result) {
				return array();
			}

			foreach ($result as $row) {

				// Remove the index.php?option=com_easysocial from the link
				$tmp = str_ireplace('index.php?option=com_easysocial', '', $row->link);

				// Parse the URL
				parse_str($tmp, $segments);

				// Convert the segments to std class
				$segments = (object) $segments;

				// if there is no view, most likely this menu item is a external link type. lets skip this item.
				if (!isset($segments->view)) {
					continue;
				}

				$obj = new stdClass();
				$obj->segments = $segments;
				$obj->link = $row->link;
				$obj->view = $segments->view;
				$obj->layout = isset($segments->layout) ? $segments->layout : 0;
				$obj->id = $row->id;

				$layoutKey = strtolower($obj->layout);

				$menus[$obj->view][$layoutKey]['*'][] = $obj;
				$menus[$obj->view][$layoutKey][$row->language][] = $obj;
			}

			if (isset($languageTag)) {
				$_loaded[$languageTag] = $languageTag;
			}
		}

		//target ID
		$tid = $useUID ? $useUID : 'id';


		// Get the current selection of menus from the cache
		if (!isset($selection[$key])) {

			if (isset($menus[$view]) && !is_null($layout) && isset($menus[$view][$layout]) && !is_null($id) && !empty($id)) {

				$tmpMenus = isset($menus[$view][$layout][$languageTag]) ? $menus[$view][$layout][$languageTag] : $menus[$view][$layout]['*'];


				foreach ($tmpMenus as $tMenus) {
					if (isset($tMenus->segments->{$tid}) && (int)$tMenus->segments->{$tid} == (int)$id) {
						$selection[$key] = array($tMenus);
						break;
					}
				}

				// if nothing found and useExact is true, just return false
				if (!isset($selection[$key]) && $useExact) {
					return false;
				}

				// there is no menu item created for this view/item/id
				// let just use the view.
				if (!isset($selection[$key]) && isset($menus[$view]) && isset($menus[$view][0])) {

					$selection[$key] = isset($menus[$view][0][$languageTag]) ? $menus[$view][0][$languageTag] : $menus[$view][0]['*'];
				}
			}

			if (isset($menus[$view]) && $menus[$view] && !is_null($layout) && isset($menus[$view][$layout]) && (is_null($id) || empty($id))) {

				$selection[$key] = isset($menus[$view][$layout][$languageTag]) ? $menus[$view][$layout][$languageTag] : $menus[$view][$layout]['*'];

				// if nothing found and useExact is true, just return false
				if (!isset($selection[$key]) && $useExact) {
					return false;
				}
			}

			// If the user is searching for $views without layout but has an id / uid.
			if (isset($menus[$view]) && $menus[$view] && (is_null($layout) || empty($layout)) && isset($menus[$view][0]) && $id) {

				// $tmpMenus = $menus[$view][0];
				$tmpMenus = isset($menus[$view][0][$languageTag]) ? $menus[$view][0][$languageTag] : $menus[$view][0]['*'];

				foreach ($tmpMenus as $tMenus) {
					if (isset($tMenus->segments->{$tid}) && (int)$tMenus->segments->{$tid} == (int)$id) {
						$selection[$key] = array($tMenus);
						break;
					}
				}

				// if nothing found and useExact is true, just return false
				if (!isset($selection[$key]) && $useExact) {
					return false;
				}
			}

			// If the user is searching for $view only.
			if (isset($menus[$view]) && $menus[$view] && (is_null($layout) || empty($layout)) && (is_null($id) || empty($id))) {

				$viewMenu = false;
				if (isset($menus[$view][0])) {
					$viewMenu = isset($menus[$view][0][$languageTag]) ? $menus[$view][0][$languageTag] : $menus[$view][0]['*'];
				}

				if ($useExact) {

					if ($viewMenu) {

						$found = false;

						foreach ($viewMenu as $vm) {
							// we need to make sure this view has no other segments
							$tSegments = get_object_vars($vm->segments);

							if (isset($tSegments['view'])) {
								unset($tSegments['view']);
							}
							if (isset($tSegments['layout'])) {
								unset($tSegments['layout']);
							}
							if (isset($tSegments['limit'])) {
								unset($tSegments['limit']);
							}
							if (isset($tSegments['filter'])) {
								unset($tSegments['filter']);
							}

							// lets check for registration view the profile_id
							if (isset($tSegments['profile_id']) && ($tSegments['profile_id'] == 'browse' || !$tSegments['profile_id'])) {
								unset($tSegments['profile_id']);
							}

							if ($view == 'activities' && isset($tSegments['type']) && $tSegments['type'] == 'all') {
								unset($tSegments['type']);
							}

							if (count($tSegments) == 0 && $found === false) {
								// this mean this menu item has nothing attached. is a plain view. lets just return this menu item.
								$found = array($vm);
							}
						}

						if ($found === false) {
							return false;
						} else {
							$selection[$key] = $found;
						}

					}

				} else {
					$selection[$key] = $viewMenu;
				}
			}

			// If we still can't find any menu, lets check if the view exits or not. if yes, used it.
			if (!isset($selection[$key]) && isset($menus[$view]) && isset($menus[$view][0]) && !$useExact) {
				$selection[$key] = isset($menus[$view][0][$languageTag]) ? $menus[$view][0][$languageTag] : $menus[$view][0]['*'];
			}

			// if we are trying to get the dashboard view menu item and the result was not found,
			// this mean the site do not have any menu item created for dashbaord. If that is the case,
			// we need to take whatever menu item created for EasySocial or else, the sef link will become
			// site.com/component/easysocial/?Itemid=
			if (!isset($selection[$key]) && $menus && $key == 'dashboard') {

				// get menu keys
				$menuKey = array_keys($menus);
				$tmpMenu = $menus[$menuKey[0]];

				//get layout keys
				$layoutKeys = array_keys($tmpMenu);
				$theOneMenu = false;
				if ($layoutKeys && isset($tmpMenu[$layoutKeys[0]])) {
					$theOneMenu = isset($tmpMenu[$layoutKeys[0]][$languageTag]) ? $tmpMenu[$layoutKeys[0]][$languageTag] : $tmpMenu[$layoutKeys[0]]['*'];
				}
				else if (isset($menus[$menuKey[0]][0])) {
					$theOneMenu = isset($menus[$menuKey[0]][0][$languageTag]) ? $menus[$menuKey[0]][0][$languageTag] : $menus[$menuKey[0]][0]['*'];
				}

				if($theOneMenu) {
					$selection[$key] = $theOneMenu;
				}
			}

			// If we still can't find any menu, skip this altogether.
			if (!isset($selection[$key])) {
				$selection[$key]  = false;
			}

		}

		return $selection[$key];
	}

	/**
	 * Retrieves the item id of the current view.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function getItemId($view, $layout = '', $id = '', $type = false, $useDefault = true, $useExact = false, $lang = '')
	{
		static $views = array();

		// Cache the result
		$key = $view . $layout . $id . (int) $useDefault . $lang;

		if (!isset($views[$key])) {

			// Retrieve the list of default menu
			// for default menu, we need to use 'loose' method since dashboard might have filter type in the url.
			$defaultMenu = ESR::getMenus('dashboard','', '', false, false, $lang);

			// Initial menu should be false
			$menuId = false;

			if (!empty($layout)) {

				// Try to locate menu with just the view if we still can't find a menu id.
				$menus = ESR::getMenus($view, $layout, $id, false, true, $lang);

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}


			if ($view == 'search' && $layout == 'advanced') {
				$menus = ESR::getMenus($view, $layout, $id, false, true, $lang);

				// Check if this menu item has parameter fid.
				if ($menus) {
					$menuId = false;

					foreach ($menus as $menu) {
						$segments = $menu->segments;

						if (!isset($segments->fid)) {
							$menuId = $menu->id;
						}
					}
				}
			}

			// Fix for the create page for app in Group/Event (example: discussion app in group)
			$appClusters = array('group', 'event', 'page');
			if ($view == 'apps' && $layout == 'canvas' && in_array($type, $appClusters)) {

				$view = 'events';

				if ($type == 'group') {
					$view = 'groups';
				}

				if ($id) {
					$menus = ESR::getMenus($view, 'item', $id, false, true, $lang);
				} else {
					$menus = ESR::getMenus($view);
				}

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}

			if ($view == 'albums' && !$layout && $type == 'user' && $id) {
				$menus = ESR::getMenus($view, '', $id, 'userid', $useExact, $lang);

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}

				// lets try with no user id search.
				$my = ES::user();
				if (!$menuId && $my->id && $my->id == $id) {
					$menus = ESR::getMenus($view, '', '', 'userid', $useExact, $lang);

					if (!$menuId && $menus) {
						$menuId = $menus[0]->id;
					}
				}

				if (!$menuId) {
					$menus = ESR::getMenus($view, 'all', 0, '', $useExact, $lang);
				}

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}

			if ($view == 'albums' && $layout) {

				if ($id) {
					$menus = ESR::getMenus($view, $layout, $id, '', $useExact, $lang);
				} else {
					// this could be create album page. lets use the all album menu item if exists. 
					// #4467
					$menus = ESR::getMenus($view, 'all', '', '', $useExact, $lang);
				}

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}

				if (!$menuId && !$useExact) {
					$menus = ESR::getMenus($view, 'all', '', '', true, $lang);
				}

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}


			if ($view == 'videos' && $type == 'categoryId') {
				$menus = ESR::getMenus($view, '', $id, $type, true, $lang);

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}

			if ($view == 'audios' && $type == 'genreId') {
				$menus = ESR::getMenus($view, '', $id, $type, true, $lang);

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}

			if ($view == 'registration' && $type == 'profile_id') {
				$menus = ESR::getMenus($view, '', $id, $type, true, $lang);

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}

			// check for single menu item for a cluster when the layout is edit #3719
			if (in_array($view, array('groups', 'events', 'pages')) && $layout  && $layout == 'edit') {
				$menus = ESR::getMenus($view, 'item', $id, false, true, $lang);

				if (!$menuId && $menus) {
					$menuId = $menus[0]->id;
				}
			}

			// Try to locate menu with just the view if we still can't find a menu id.
			$menus = ESR::getMenus($view, '', '', false, true, $lang);

			// If menu id for view + layout doesn't exists, use the one from the view
			if (!$menuId && $menus && $useDefault) {
				$menuId = $menus[0]->id;
			}

			// If we still don't have a menu id, we use the default dashboard view.
			if (!$menuId && $defaultMenu && $useDefault) {
				$menuId = $defaultMenu[0]->id;
			}

			$views[$key] = $menuId;
		}

		return $views[$key];
	}

	/**
	 * Builds the controller url
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function controller($name , $params = array() , $xhtml = true , $ssl = null)
	{
		// For controller urls, we shouldn't pass it to the router.
		$url = 'index.php?option=com_easysocial&controller=' . $name;

		// Determines if this url is an external url.
		$external = isset($params['external']) ? $params['external'] : false;
		$tokenize = isset($params['tokenize']) ? $params['tokenize'] : false;

		unset($params['external']);
		unset($params['tokenize']);

		if ($params) {
			foreach ($params as $key => $value) {
				$url .= '&' . $key . '=' . $value;
			}
		}

		$url = FRoute::_($url , $xhtml , '' , $ssl , $tokenize , $external);

		return $url;
	}

	/**
	 * Calls the adapter file
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function __callStatic($view, $args)
	{
		$router = ES::router($view);
		return call_user_func_array(array($router, 'route'), $args);
	}

	/**
	 * Parses url
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function parse(&$segments)
	{
		$vars = array();

		// If there is only 1 segment and the segment is index.php, it's just submitting
		if (count($segments) == 1 && $segments[0] == 'index.php') {
			return array();
		}

		// Replace all ':' with '-'
		self::encode($segments, false);

		$segments = self::format($segments);

		// the the fist element always the view.
		$view = $segments[0];

		// Load up the router object so that we can translate the view
		$router = ES::router($view);

		// Parse the segments
		$vars = $router->parse($segments);

		return $vars;
	}

	/**
	 * Format the segments
	 *
	 * @since   3.1.5
	 * @access  public
	 */
	public static function format($segments)
	{
		$app = JFactory::getApplication();

		// Get the menu object.
		$menu = $app->getMenu();

		// Get the active menu object.
		$active = $menu->getActive();

		// Check if the view exists in the segments
		$view = '';
		$viewExists = false;

		foreach (self::$views as $systemView) {
			if (SocialRouterAdapter::translate($systemView) == $segments[0] || $systemView == $segments[0]) {

				// Fix gdpr profile [#2300] & conversation file download [#2512] conflict with download view.
				// Eg: site.com/[profile-menu-item]/download
				if ($systemView == 'download' && ($active->query['view'] == 'profile' || $active->query['view'] == 'conversations')) {
					continue;
				}

				// since we knwo the 1st element is an view, we need reset the 1st element in the segments
				// due to language translation. #3425
				$segments[0] = $systemView;

				$view = $systemView;
				$viewExists = true;
				break;
			}
		}

		// if no view in the segment. let check for the current active menu item.
		if (!$viewExists && $active) {

			$cQuery = $active->query;

			$view = $cQuery['view'];
			$layout = isset($cQuery['layout']) ? $cQuery['layout'] : '';
			$id = isset($cQuery['id']) ? $cQuery['id'] : 0;

			// check if this is the single cluster menu item or not. #3719
			if (in_array($view, array('groups', 'events', 'pages')) && ($layout && $layout == 'item') && $id) {
				$viewExists = true;
			}

			// Check if the alias of the albums menu is changed to 'photos' #2474
			if ($view == 'albums' && $active->alias == 'photos') {
				$albumId = $segments[0];

				// Check if albums exist
				$album = ES::table('Album');
				$album->load($albumId);

				// Probably it trying to load a photos
				if (!$album->id) {
					$view = 'photos';
					$viewExists = true;
				}
			}

			if ($viewExists) {
				// Add the view to the top of the element
				array_unshift($segments, $view);
			}

		}


		if (!$viewExists && $active) {

			// If there is no view in the segments, we treat it that the user
			// has created a menu item on the site.
			$view = $active->query['view'];


			// assume the 1st segment is user permalink, but we will verify at later.
			$userPermalink = $segments[0];
			$shortenerWithID = false;
			$hasIdSegment = false;
			$urlShortenerEnabled = false;
			$userId = null;

			// to get the plain text permalink from 38-xxxxx or 38:xxxxxx for later use
			$isEasySocialUrlPluginInstalled = JPluginHelper::getPlugin('system', 'easysocialurl');
			$isEasySocialUrlPluginEnabled = JPluginHelper::isEnabled('system', 'easysocialurl');

			if ($isEasySocialUrlPluginInstalled && $isEasySocialUrlPluginEnabled) {
				$urlShortenerEnabled = true;
			}

			$pattern = "/[\d]+[\-\:](.+)/i";
			preg_match($pattern, $userPermalink, $matches);

			if ($matches && isset($matches[1])) {
				$hasIdSegment = true;
				$userPermalink = $matches[1];
			}

			if ($view != 'dashboard') {

				$config = ES::config();

				// lets try to get the user id from the permalink
				$userModel = ES::model('Users');
				$userId = $userModel->getUserIdFromAlias($userPermalink);

				if ($userId) {
					$isUser = true;

					if ($hasIdSegment) {
						$xId = (int) $segments[0];

						if ($userId != $xId) {
							$isUser = false;
						}
					}

					if ($isUser) {
						$view = 'profile';
						$segments[0] = $userId;
					}
				}

			} else {

				// lets check if this 1st segments has integer or not.
				$segmentId = (int) $segments[0];

				$userId = null;

				if (is_integer($segmentId) && $segmentId) {

					// lets make sure this is a user id.
					$esUser = ES::user($segmentId);

					if ($esUser->id) {
						// we assume this is a profile sef link.
						$view = 'profile';
						$userId = $esUser->id;
					}
				}

				// If user id still not exists, we try to get the permalink from alias directly
				if (!$userId) {

					// lets try to get the user id from the permalink
					$userModel = ES::model('Users');
					$userId = $userModel->getUserIdFromAlias($userPermalink);

					if ($userId) {
						$view = 'profile';
						$segments[0] = $userId;
					}
				}
			}

			// Add the view to the top of the element
			array_unshift($segments, $view);
		}

		// when reach here, the 1st index should be holding the view.

		return $segments;
	}

	/**
	 * Replaces all ':' with '-'
	 *
	 * @since   1.2
	 * @access  public
	 */
	public static function encode(&$segments, $checkUnicode = true)
	{
		$jconfig = ES::jconfig();
		$unicodeAlias = $jconfig->get('unicodeslugs', false);

		if (is_array($segments)) {
			foreach ($segments as &$segment) {
				$segment = str_ireplace(':' , '-' , $segment);
				if ($checkUnicode) {
					$segment = $unicodeAlias ? JFilterOutput::stringURLUnicodeSlug($segment) : JFilterOutput::stringURLSafe($segment);
				}
			}
		} else {
			$segments = str_ireplace(':' , '-' , $segments);
			if ($checkUnicode) {
				$segments = $unicodeAlias ? JFilterOutput::stringURLUnicodeSlug($segments) : JFilterOutput::stringURLSafe($segments);
			}
		}


	}

	/**
	 * Build urls
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function build(&$query)
	{
		$app = JFactory::getApplication();
		$segments = array();

		$menu = $app->getMenu();

		// this is to support sef generation from backend. #3881
		if (ES::isFromAdmin()) {
			$menu = $app->getMenu('site');
		}

		//remove ts from the query
		if (isset($query['_ts'])) {
			unset($query['_ts']);
		}

		// If we don't have the item id, use the default one.
		$active = $menu->getActive();

		// If there is item id already assigned to the query, we need to query for the active menu
		// Get the menu item based on the item id.
		if (isset($query['Itemid'])) {
			$active = $menu->getItem($query['Itemid']);
		}

		// If there's no view, we wouldn't want to set anything
		if (!isset($query['view'])) {
			return $segments;
		}

		// Get the view.
		$view = isset($query['view']) ? $query['view'] : $active->query['view'];
		$router = ES::router($view);

		$segments = $router->build($active, $query);

		return $segments;
	}

	/**
	 * Build urls
	 *
	 * @since   1.0
	 * @access  public
	 */
	public static function url($options = array())
	{
		// Set option as com_easysocial by default
		if (!isset($options['option'])) {
			$options['option'] = SOCIAL_COMPONENT_NAME;
		}

		// Remove external
		$external = false;
		if (isset($options['external'])) {
			$external = $options['external'];
			unset($options['external']);
		}

		// Remove sef
		$sef = false;
		if (isset($options['sef'])) {
			$sef = $options['sef'];
			unset($options['sef']);
		}

		// Remove adminSef
		$adminSef = false;
		if (isset($options['adminSef'])) {
			$adminSef = $options['adminSef'];
			unset($options['adminSef']);
		}

		// Remove tokenize
		$tokenize = false;
		if (isset($options['tokenize'])) {
			$tokenize = $options['tokenize'];
			unset($options['tokenize']);
		}

		// Remove ssl
		$ssl = false;
		if (isset($options['ssl'])) {
			$ssl = $options['ssl'];
			unset($options['ssl']);
		}

		// Remove xhtml
		$xhtml = false;
		if (isset($options['xhtml'])) {
			$xhtml = $options['xhtml'];
			unset($options['xhtml']);
		}

		$base = 'index.php?' . JURI::buildQuery($options);

		return FRoute::_($base, $xhtml, array(), $ssl, $tokenize, $external, '', '', $sef, $adminSef);
	}

	/**
	 * Return oauth non sef URL request
	 *
	 * @since   2.1.8
	 * @access  public
	 */
	public static function oauthRedirectUri()
	{
		// the reason why we hardcoded this non-sef callback pass to Facebook is because if pass to SEF URL, it might be a lot of possibilities doesn't match the Facebook Oauth redirect URI
		$redirect_uri = JURI::root() . 'index.php?option=com_easysocial&view=registration&layout=oauthDialog&client=facebook';

		return $redirect_uri;
	}

	/**
	 * return system views
	 *
	 * @since   2.0
	 * @access  public
	 */
	public static function getSystemViews()
	{
		return self::$views;
	}


	/**
	 * Determine if permalink need to include Object-ID or not.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function isPermalinkWithID()
	{
		// let check against the settings.
		$config = ES::config();
		return ($config->get('seo.useid', true)) ? true : false;
	}

	/**
	 * Determine if permalink need to include Object-ID or not.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function normalizePermalink($permalink)
	{
		if (ESR::isPermalinkWithID()) {
			return $permalink;
		}

		// lets search for : delimeter
		$pattern = '/\d+:(.+)/is';
		preg_match($pattern, $permalink, $matches);
		if ($matches && isset($matches[1]) && $matches[1]) {
			return $matches[1];
		}

		// if not found, let search for - delimeter
		$pattern = '/\d+-(.+)/is';
		preg_match($pattern, $permalink, $matches);
		if ($matches && isset($matches[1]) && $matches[1]) {
			return $matches[1];
		}

		// $segments = explode(':', $permalink);

		// if (isset($segments[1])) {
		// 	return $segments[1];
		// }

		return $permalink;
	}

	/**
	 * check if this queries already saved into db or not
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function getDbSegments($query = array(), $debug = false)
	{
		$config = ES::config();
		$queryString = '/';

		$excludeItems = array();
		if (!$config->get('seo.cachefile.enabled')) {
			// here we need to exclude itemid.
			$excludeItems['Itemid'] = '';
		}

		$query = self::normalizeQuery($query, $excludeItems);
		if ($query) {
			$queryString = self::getQueryLink($query);
		}

		if ($config->get('seo.cachefile.enabled')) {
			$data = false;

			if (!$debug) {
				// lets check if cache has the url or not.
				$cache = ES::fileCache();
				$data = $cache->getSefUrl($queryString);
			}

			if ($data !== false) {

				$urls = explode('||', $data);

				$segments = explode('/', $urls[1]);

				// we know the 1st segments always the menu item alias.
				// lets remove it.
				array_shift($segments);

				$obj = new stdClass();
				$obj->segments = $segments;
				$obj->rawurl = $urls[0];

				return $obj;
			}
		}

		// since the raw url that stored into db do not include Itemid segment,
		// when querying from db, we need to remove the Itemid as well. #5206
		if (isset($query['Itemid'])) {
			unset($query['Itemid']);
		}
		$queryString = self::getQueryLink($query);

		$urlTable = self::_loadRawUrl($queryString);
		if ($urlTable->id) {

			$segments = explode('/', $urlTable->sefurl);

			// we know the 1st segments always the menu item alias.
			// lets remove it.
			array_shift($segments);

			$obj = new stdClass();
			$obj->segments = $segments;
			$obj->rawurl = $urlTable->rawurl;

			return $obj;
		}

		return false;

	}

	/**
	 * add sef segments into db
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function setDbSegments($oriQuery, &$segments, &$untranslateQuery, $debug = false)
	{
		$config = ES::config();

		$query = self::normalizeQuery($oriQuery, $untranslateQuery);
		$queryString = '/';

		$tmpSegments = $segments;

		if ($query) {
			$queryString = self::getQueryLink($query);
		}

		// here we need to add menu item alias.
		if (isset($oriQuery['Itemid']) && $oriQuery['Itemid']) {
			$menu = JFactory::getApplication()->getMenu('site');
			$active = $menu->getItem($oriQuery['Itemid']);

			// lets check if the oriQuery has lang supplied or not.
			if (isset($oriQuery['lang']) && $oriQuery['lang']) {
				// lets check if this menu item are mean for this lang or not.
				if ($active->language != $oriQuery['lang']) {

					// look like the lang in menu item and the lang from query is not match.
					// lets try to get the menu item belong to the lang from query.
					$view = isset($oriQuery['view']) ? $oriQuery['view'] : '';
					$layout = isset($oriQuery['layout']) ? $oriQuery['layout'] : '';
					$id = isset($oriQuery['id']) ? $oriQuery['id'] : '';
					$uid = isset($oriQuery['uid']) ? $oriQuery['uid'] : '';
					$type = isset($oriQuery['type']) ? $oriQuery['type'] : false;

					// since photos view we will try to get the albusm view.
					// we need to handle the photos url diferently.
					// #4467.
					// please see also at administrator/components/com_easysocial/includes/router/router.php at line 287
					if ($view == 'photos' && $type == 'user') {
						// reset the view.
						$view = 'albums';
					}

					$itemId = self::getItemId($view, $layout, $id, $type, false, false, $oriQuery['lang']);

					if (!$itemId && $layout) {
						// lets try just view
						$itemId = self::getItemId($view, '', '', '', false, false, $oriQuery['lang']);
					}

					if (!$itemId) {
						// lets try without passing in the lang code.
						$itemId = self::getItemId($view, $layout, $id, $type, false, false);
					}

					if ($itemId) {

						$tmpActive = $menu->getItem($itemId);
						if ($tmpActive->id) {
							$active = $tmpActive;
							$oriQuery['Itemid'] = $itemId;
							$untranslateQuery['Itemid'] = $itemId;

							// we need to rebuild the segments if the itemid changed.
							$tmpOriQuery = $oriQuery;
							$segments = FRoute::build($tmpOriQuery);
							$tmpSegments = $segments;
						}
					}

				}
			}

			array_unshift($tmpSegments, $active->alias);
		}

		self::encode($tmpSegments);
		$sefUrl = implode('/', $tmpSegments);

		// check if this sef is already exists.
		$state = true;

		// add only if its not exists
		if ($sefUrl) {

			$urlTable = self::_loadSef($sefUrl);

			if (!$urlTable->id) {
				$urlTable->rawurl = $queryString;
				$urlTable->sefurl = $sefUrl;

				$state = $urlTable->store();
			}
		}

		if ($config->get('seo.cachefile.enabled')) {

			// when writing into file cache, we will ignore the duplicates on non-sef (the translated one)
			// this is becuase when we store, we actually storing the full raw url as the key.
			$data = array($queryString, $sefUrl);

			$tmp = self::normalizeQuery($oriQuery);

			$tmpQuery = self::getQueryLink($tmp);

			if ($sefUrl && ($queryString && $queryString != '/') && !$debug) {
				// save into cache lib only when debug mode is off
				// file cache lib
				$cache = ES::fileCache();
				$cache->addNewUrls($tmpQuery, $data);
			}
		}

		return $state;
	}


	/**
	 * retrieve the vars based on the sef link
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function getDbVars($segments, $debug = false)
	{
		$config = ES::config();

		// we need to insert the current active menu item alias as
		// the 1st segments in the cached url always the menu item alias.

		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		if ($active) {
			array_unshift($segments, $active->alias);
		}

		self::encode($segments);
		$sefLink = implode('/', $segments);

		$urlItem = false;

		if (!$debug && $config->get('seo.cachefile.enabled')) {
			// run this only its not in debug mode
			// lets try to get from cache first.
			$cache = ES::fileCache();
			$urlItem = $cache->getNonSefUrl($sefLink);
		}

		if ($urlItem === false) {
			// try load from db
			$urlItem = self::_loadSef($sefLink);
		}

		// we found the url
		if ($urlItem && $urlItem->rawurl) {

			// lets add the required vars
			parse_str($urlItem->rawurl, $rawQuery);
			foreach ($rawQuery as $key => $val) {
				$vars[$key] = $val;
			}

			return $vars;
		}

		return false;
	}

	/**
	 * manually load the sef link from database.
	 *
	 * @since   3.1.7
	 * @access  private
	 */
	private static function _loadSef($sefLink)
	{
		static $_cache = array();

		if (!$sefLink) {
			return false;
		}

		$key = md5($sefLink);

		if (!isset($_cache[$key])) {

			// load from db
			$urlItem = ES::table('urls');
			$urlItem->load(array('sefurl' => $sefLink));

			$_cache[$key] = $urlItem;

		}

		return $_cache[$key];
	}

	/**
	 * manually load the raw url from database.
	 *
	 * @since   3.1.7
	 * @access  private
	 */
	private static function _loadRawUrl($queryString)
	{
		static $_cache = array();

		if (!$queryString) {
			return false;
		}

		$key = md5($queryString);

		if (!isset($_cache[$key])) {
			// load from db
			$urlTable = ES::table('urls');
			$urlTable->load(array('rawurl' => $queryString));

			$_cache[$key] = $urlTable;
		}

		return $_cache[$key];
	}

	/**
	 * check if the view require sef caching or not.
	 *
	 * @since	3.1.5
	 * @access	public
	 */
	public static function isViewSefCacheAllow($view)
	{

		$disallowedViews = array('search', 'registration', 'stream', 'leaderboard', 'conversations', 'activities', 'notifications', 'account', 'login');
		$disallowedViewsTranslated = array();

		foreach ($disallowedViews as $item) {
			$disallowedViewsTranslated[] = SocialRouterAdapter::translate($item);
		}

		if (in_array($view, $disallowedViews) || in_array($view, $disallowedViewsTranslated)) {
			return false;
		}

		return true;
	}

	/**
	 * Method use to update the cache by deleting the old sef when object's alias get updated.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function updateSEFCache(SocialTable $table, $oldAlias, $newAlias)
	{
		$config = ES::config();
		$alias = self::normalizePermalink($oldAlias);

		// photo alwyas have the id
		if ($table instanceof SocialTablePhoto) {
			$alias = $oldAlias;
		}

		$pattern = array(':');
		$replace = array('-');

		$alias = str_replace($pattern, $replace, $alias);
		$aliasWithId = $alias;

		$hasId = (int) $aliasWithId;
		if (!$hasId) {
			if ($table instanceof SocialTableUsers) {
				$aliasWithId = $table->user_id . ':' . $aliasWithId;
			} else {
				$aliasWithId = $table->id . ':' . $aliasWithId;
			}
		}

		$model = ES::model('Urls');
		$urls = $model->getObjectUrls($alias, $aliasWithId);

		if ($urls) {
			$ids = array();

			foreach ($urls as $url) {
				$ids[] = $url->id;
			}

			// delete from db.
			$model->delete($ids);

			// delete from cache.
			if ($config->get('seo.cachefile.enabled')) {
				$cache = ES::fileCache();
				$cache->removeCacheItems($urls);
			}
		}

	}

	/**
	 * Method delete sef cache when object are being removed.
	 *
	 * @since   3.1
	 * @access  public
	 */
	public static function deleteSEFCache(SocialTable $table, $alias)
	{
		self::updateSEFCache($table, $alias, $alias);

		return true;
	}


	/**
	 * format the query array into string
	 *
	 * @since   3.1
	 * @access  private
	 */
	private static function getQueryLink($query)
	{
		$queryString = '';

		foreach ($query as $key => $val) {

			$str = '';
			if (is_array($val)) {

				$tmp = array();
				foreach ($val as $v) {
					$tmp[] = $key . '[]='.$v;
				}

				$str = implode('&', $tmp);
			} else {
				$str = $key . '=' . $val;
			}

			$queryString .= ($queryString) ? '&' . $str : $str;
		}

		return $queryString;
	}

	/**
	 * normalize the queries data
	 *
	 * @since   3.1
	 * @access  private
	 */
	private static function normalizeQuery($query = array(), $untranslateQuery = array())
	{
		if (! $query) {
			return array();
		}

		// remove option.
		unset($query['option']);

		// remove shortcutmanifest
		unset($query['shortcutmanifest']);

		// remove token string
		$session = JFactory::getSession();
		$token = $session->getFormToken();
		unset($query[$token]);

		if ($untranslateQuery) {
			foreach ($untranslateQuery as $key => $values) {

				// keep language code
				if ($key == 'lang') {
					continue;
				}

				// remove any untranslated key value
				unset($query[$key]);
			}
		}

		// always sort the keys
		ksort($query, SORT_STRING);

		return $query;
	}


}


class FRoute extends ESR {}
