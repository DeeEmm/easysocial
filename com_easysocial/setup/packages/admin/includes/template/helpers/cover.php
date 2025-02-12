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

class ThemesHelperCover extends ThemesHelperAbstract
{
	/**
	 * Determines if the current active item is an app type
	 *
	 * @since	2.1.0
	 * @access	private
	 */
	private function isAppActive($currentActive)
	{
		$isAppActive = stristr($currentActive, 'apps.') !== false;

		return $isAppActive;
	}

	/**
	 * Renders the heading for an event
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function event(SocialEvent $event, $active = 'timeline')
	{
		static $items = array();

		if (!$event->id) {
			return;
		}

		if (isset($items[$event->id])) {
			return $items[$event->id];
		}

		// Maps the active to the appropriate navigation.
		$maps = array('scheduled', 'custom', 'moderation');

		if (in_array($active, $maps)) {
			$active = 'timeline';
		}

		$totalPendingGuests = 0;

		if ($event->isAdmin()) {
			$totalPendingGuests = $event->getTotalPendingGuests();
		}

		$cover = $event->getCoverData();


		$model = ES::model('Apps');
		$apps = $model->getEventApps($event->id);

		// We need to exclude certain apps since they are already rendered under the apps dropdown
		$exclusion = array('followers');

		// These are core apps that is not included in model
		$coreApps = array('guests', 'albums', 'videos', 'audios');

		foreach ($coreApps as $core) {
			if (in_array($core, array('albums', 'videos', 'audios'))) {

				if ($core == 'videos' || $core == 'audios') {
					$method = 'allow' . ucfirst($core);
					$enabled = $event->$method();
				}

				if ($core == 'albums') {
					$enabled = $event->allowPhotos();
				}

				$permalink = ESR::$core(array('uid' => $event->getAlias(), 'type' => SOCIAL_TYPE_EVENT));
			} else {
				$enabled = true;
				$permalink = $event->getAppPermalink($core);
			}

			if ($enabled) {
				$app = new stdClass;
				$app->title = JText::_('COM_ES_' . strtoupper($core));
				$app->pageTitle = JText::_('COM_ES_' . strtoupper($core));
				$app->permalink = $permalink;
				$app->active = $core;
				$app->element = $core;
				$app->isMore = false;
				$app->hasNotice = false;

				if ($core == 'guests') {
					$app->hasNotice = $totalPendingGuests > 0 ? true : false;
				}

				$appsInstalled[$core] = $app;
			}
		}

		foreach ($apps as $app) {
			$permalink = $event->getAppsPermalink($app->getAlias());

			$app->title = $app->getAppTitle();
			$app->pageTitle = $app->getPageTitle();
			$app->permalink = $permalink;
			$app->active = 'apps.' . $app->element;
			$app->isMore = false;
			$app->hasNotice = false;

			if (!in_array($app->element, $exclusion)) {
				$appsInstalled[$app->element] = $app;
			}
		}

		$category = $event->getCategory();

		// Get the event header params
		$headerApps = $category->getParams()->get('header_apps');
		$headerApps = json_decode($headerApps);

		// Process the apps menu
		$appsMenu = $this->processAppsMenu($appsInstalled, $headerApps, $active);

		$appsHeader = $appsMenu->appsHeader;
		$appsDropdown = $appsMenu->appsDropdown;
		$isMoreActive = $appsMenu->isMoreActive;
		$isMoreHasNotice = $appsMenu->isMoreHasNotice;

		$showDropdown = false;

		if ($appsDropdown) {
			$showDropdown = true;
		}

		$returnUrl = base64_encode(ES::getURI(true));

		// Get the timeline link
		$defaultDisplay = $this->config->get('events.item.display', 'timeline');
		$timelinePermalink = $event->getPermalink();
		$aboutPermalink = ESR::events(array('id' => $event->getAlias(), 'page' => 'info', 'layout' => 'item'));

		if ($defaultDisplay == 'info') {
			$aboutPermalink = $timelinePermalink;
			$timelinePermalink = ESR::events(array('id' => $event->getAlias(), 'page' => 'timeline', 'layout' => 'item'));
		}

		$isAppActive = $this->isAppActive($active);

		// Since some of the links are hidden on the apps, we need to check if apps should be active
		if (!$isAppActive) {
			// On mobile devices, we group up the audio, video and albums under the more dropdown
			if ($this->isMobile() && ($active == 'videos' || $active == 'audios' || $active == 'albums')) {
				$isAppActive = true;
			}
		}

		$cluster = null;

		if ($event->isClusterEvent()) {
			$cluster = $event->getCluster();
		}

		$showPhotoPopup = $event->canViewItem();

		$theme = ES::themes();
		$theme->set('isAppActive', $isAppActive);
		$theme->set('timelinePermalink', $timelinePermalink);
		$theme->set('aboutPermalink', $aboutPermalink);
		$theme->set('totalPendingGuests', $totalPendingGuests);
		$theme->set('active', $active);
		$theme->set('event', $event);
		$theme->set('cover', $cover);
		$theme->set('apps', $apps);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('cluster', $cluster);
		$theme->set('appsHeader', $appsHeader);
		$theme->set('appsDropdown', $appsDropdown);
		$theme->set('isMoreActive', $isMoreActive);
		$theme->set('isMoreHasNotice', $isMoreHasNotice);
		$theme->set('showDropdown', $showDropdown);
		$theme->set('showBrowseApps', false);
		$theme->set('showPhotoPopup', $showPhotoPopup);
		$theme->set('user', $this->my);
		$theme->set('uniqid', uniqId());

		$items[$event->id] = $theme->output('site/helpers/cover/event');

		return $items[$event->id];
	}

	/**
	 * Renders the heading for a page
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function page(SocialPage $page, $active = 'timeline')
	{
		static $items = [];

		if (!$page->id) {
			return;
		}

		if (isset($items[$page->id])) {
			return $items[$page->id];
		}

		// Maps the active to the appropriate navigation.
		$maps = ['scheduled', 'custom', 'moderation'];

		if (in_array($active, $maps)) {
			$active = 'timeline';
		}

		$pendingFollowers = 0;

		if ($page->isAdmin()) {
			$pendingFollowers = $page->getTotalPendingFollowers();
		}

		$cover = $page->getCoverData();

		$model = ES::model('Apps');
		$apps = $model->getPageApps($page->id);

		$events = null;

		// These are the known apps that would be rendered below the cover
		$knownApps = ['events'];

		// These are core apps that is not included in model
		$coreApps = ['followers', 'albums', 'videos', 'audios', 'events', 'marketplaces'];

		foreach ($coreApps as $core) {
			if (in_array($core, array('albums', 'videos', 'audios', 'events', 'marketplaces'))) {

				if ($core == 'videos' || $core == 'audios') {
					$method = 'allow' . ucfirst($core);
					$enabled = $page->$method();
				}

				if ($core == 'albums') {
					$enabled = $page->allowPhotos();
				}

				if ($core == 'events') {
					$enabled = $page->canViewEvent();
				}

				if ($core == 'marketplaces') {
					$enabled = $page->allowMarketplaces();
				}

				$permalink = ESR::$core(array('uid' => $page->getAlias(), 'type' => SOCIAL_TYPE_PAGE));
			} else {
				$enabled = true;
				$permalink = $page->getAppPermalink($core);
			}

			if ($enabled) {
				$app = new stdClass;
				$app->title = JText::_('COM_ES_' . strtoupper($core));
				$app->pageTitle = JText::_('COM_ES_' . strtoupper($core));
				$app->permalink = $permalink;
				$app->active = $core == 'followers' ? 'members' : $core;
				$app->element = $core;
				$app->isMore = false;
				$app->hasNotice = false;

				if ($core == 'followers') {
					$app->hasNotice = $pendingFollowers > 0 ? true : false;
				}

				$appsInstalled[$core] = $app;
			}
		}

		foreach ($apps as $app) {
			$permalink = $page->getAppsPermalink($app->getAlias());

			$app->title = $app->getAppTitle();
			$app->pageTitle = $app->getPageTitle();
			$app->permalink = $permalink;
			$app->active = 'apps.' . $app->element;
			$app->isMore = false;
			$app->hasNotice = false;

			if (!in_array($app->element, $knownApps)) {
				$appsInstalled[$app->element] = $app;
			}

			if (in_array($app->element, $knownApps)) {
				${$app->element} = $app;
			}
		}

		$category = $page->getCategory();

		// Get the page header params
		$headerApps = $category->getParams()->get('header_apps');
		$headerApps = json_decode($headerApps);

		// Process the apps menu
		$appsMenu = $this->processAppsMenu($appsInstalled, $headerApps, $active);

		$appsHeader = $appsMenu->appsHeader;
		$appsDropdown = $appsMenu->appsDropdown;
		$isMoreActive = $appsMenu->isMoreActive;
		$isMoreHasNotice = $appsMenu->isMoreHasNotice;

		// Get the timeline link
		$defaultDisplay = $this->config->get('pages.item.display', 'timeline');
		$timelinePermalink = $page->getPermalink();
		$aboutPermalink = ESR::pages(array('id' => $page->getAlias(), 'type' => 'info', 'layout' => 'item'));

		if ($defaultDisplay == 'info') {
			$aboutPermalink = $timelinePermalink;
			$timelinePermalink = ESR::pages(array('id' => $page->getAlias(), 'type' => 'timeline', 'layout' => 'item'));
		}

		$showDropdown = false;

		if ($appsDropdown) {
			$showDropdown = true;
		}

		$isAppActive = $this->isAppActive($active);

		if (!$isAppActive) {

			// On mobile devices, we group up the audio, video and albums under the more dropdown
			if ($this->isMobile() && ($active == 'videos' || $active == 'audios' || $active == 'albums' || $active == 'events')) {
				$isAppActive = true;
			}

			// On mobile
			if ($this->isMobile()) {

			}
		}

		// Determine if the cover and avatar should be clickable.
		$showPhotoPopup = true;
		if (!$page->isOpen() && !$page->isMember() && !$this->my->isSiteAdmin()) {
			$showPhotoPopup = false;
		}

		$creator = $page->getCreator();

		$theme = ES::themes();
		$theme->set('creator', $creator);
		$theme->set('isAppActive', $isAppActive);
		$theme->set('timelinePermalink', $timelinePermalink);
		$theme->set('aboutPermalink', $aboutPermalink);
		$theme->set('pendingFollowers', $pendingFollowers);
		$theme->set('active', $active);
		$theme->set('page', $page);
		$theme->set('cover', $cover);
		$theme->set('apps', $apps);
		$theme->set('appsHeader', $appsHeader);
		$theme->set('appsDropdown', $appsDropdown);
		$theme->set('isMoreActive', $isMoreActive);
		$theme->set('isMoreHasNotice', $isMoreHasNotice);
		$theme->set('showDropdown', $showDropdown);
		$theme->set('showBrowseApps', false);
		$theme->set('showPhotoPopup', $showPhotoPopup);
		$theme->set('user', $this->my);
		$theme->set('uniqid', uniqId());

		foreach ($knownApps as $knownApp) {
			$theme->set($knownApp, ${$knownApp});
		}

		$items[$page->id] = $theme->output('site/helpers/cover/page');

		return $items[$page->id];
	}

	/**
	 * Renders the heading for a group
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function group(SocialGroup $group, $active = 'timeline')
	{
		static $items = array();

		if (!$group->id) {
			return;
		}

		if (isset($items[$group->id])) {
			return $items[$group->id];
		}

		// Maps the active to the appropriate navigation.
		$maps = array('scheduled', 'custom', 'moderation');

		if (in_array($active, $maps)) {
			$active = 'timeline';
		}

		$pendingMembers = 0;
		if ($group->isAdmin()) {
			$pendingMembers = $group->getTotalPendingMembers();
		}

		$cover = $group->getCoverData();

		$model = ES::model('Apps');
		$apps = $model->getGroupApps($group->id);

		// These are the known apps that would be rendered below the cover
		$knownApps = array('events');

		// These are core apps that is not included in model->getUserApps
		$coreApps = array('members', 'albums', 'videos', 'audios', 'events', 'marketplaces');

		foreach ($coreApps as $core) {

			if (in_array($core, array('albums', 'videos', 'audios', 'events', 'marketplaces'))) {

				if ($core == 'videos' || $core == 'audios') {
					$method = 'allow' . ucfirst($core);
					$enabled = $group->$method();
				}

				if ($core == 'albums') {
					$enabled = $group->allowPhotos();
				}

				if ($core == 'events') {

					$enabled = $group->canViewEvent();
				}

				if ($core == 'marketplaces') {
					$enabled = $group->allowMarketplaces();
				}

				$permalink = ESR::$core(array('uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP));
			} else {
				$enabled = true;
				$permalink = $group->getAppPermalink($core);
			}

			if ($enabled) {
				$app = new stdClass;
				$app->title = JText::_('COM_ES_' . strtoupper($core));
				$app->pageTitle = JText::_('COM_ES_' . strtoupper($core));
				$app->permalink = $permalink;
				$app->active = $core;
				$app->element = $core;
				$app->isMore = false;
				$app->hasNotice = false;

				if ($core == 'members') {
					$app->hasNotice = $pendingMembers > 0 ? true : false;
				}

				$appsInstalled[$core] = $app;
			}
		}

		foreach ($apps as $app) {
			$permalink = $group->getAppsPermalink($app->getAlias());

			$app->title = $app->getAppTitle();
			$app->pageTitle = $app->getPageTitle();
			$app->permalink = $permalink;
			$app->active = 'apps.' . $app->element;
			$app->isMore = false;
			$app->hasNotice = false;

			if (!in_array($app->element, $knownApps)) {
				$appsInstalled[$app->element] = $app;
			}

			if (in_array($app->element, $knownApps)) {
				${$app->element} = $app;
			}
		}

		$category = $group->getCategory();

		// Get the group header params
		$headerApps = $category->getParams()->get('header_apps');
		$headerApps = json_decode($headerApps);

		// Process the apps menu
		$appsMenu = $this->processAppsMenu($appsInstalled, $headerApps, $active);

		$appsHeader = $appsMenu->appsHeader;
		$appsDropdown = $appsMenu->appsDropdown;
		$isMoreActive = $appsMenu->isMoreActive;
		$isMoreHasNotice = $appsMenu->isMoreHasNotice;

		// Get the timeline link
		$defaultDisplay = $this->config->get('groups.item.display', 'timeline');
		$timelinePermalink = $group->getPermalink();
		$aboutPermalink = ESR::groups(array('id' => $group->getAlias(), 'type' => 'info', 'layout' => 'item'));

		if ($defaultDisplay == 'info') {
			$aboutPermalink = $timelinePermalink;
			$timelinePermalink = ESR::groups(array('id' => $group->getAlias(), 'type' => 'timeline', 'layout' => 'item'));
		}

		$showDropdown = false;

		if ($appsDropdown) {
			$showDropdown = true;
		}

		// Determine if the cover and avatar should be clickable.
		$showPhotoPopup = true;
		if (!$group->isOpen() && !$group->isMember() && !$this->my->isSiteAdmin()) {
			$showPhotoPopup = false;
		}

		$theme = ES::themes();
		$theme->set('isAppActive', false);
		$theme->set('timelinePermalink', $timelinePermalink);
		$theme->set('aboutPermalink', $aboutPermalink);
		$theme->set('pendingMembers', $pendingMembers);
		$theme->set('active', $active);
		$theme->set('group', $group);
		$theme->set('cover', $cover);
		$theme->set('apps', $apps);
		$theme->set('appsHeader', $appsHeader);
		$theme->set('appsDropdown', $appsDropdown);
		$theme->set('isMoreActive', $isMoreActive);
		$theme->set('isMoreHasNotice', $isMoreHasNotice);
		$theme->set('showDropdown', $showDropdown);
		$theme->set('showBrowseApps', false);
		$theme->set('showPhotoPopup', $showPhotoPopup);
		$theme->set('user', $this->my);
		$theme->set('uniqid', uniqId());

		foreach ($knownApps as $knownApp) {

			if (isset(${$knownApp})) {
				$theme->set($knownApp, ${$knownApp});
			}
		}

		$items[$group->id] = $theme->output('site/helpers/cover/group');

		return $items[$group->id];
	}

	/**
	 * Renders the heading for a user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function user(SocialUser $user, $active = 'timeline')
	{
		static $items = [];

		if (!$user->id) {
			return;
		}

		if (isset($items[$user->id])) {
			return $items[$user->id];
		}

		// Get user's cover object
		$cover = $user->getCoverData();
		$newCover = false;

		// If we're setting a cover
		$coverId = $this->input->get('cover_id', 0, 'int');

		// Load cover photo
		if ($coverId) {
			$coverTable = ES::table('Photo');
			$coverTable->load($coverId);

			// If the cover photo belongs to the user
			if ($coverTable->isMine()) {
				$newCover = $coverTable;
			}
		}

		// Determines if the avatar should be visible
		$photoTable = $user->getAvatarPhoto();
		$showAvatar = $photoTable && $this->my->getPrivacy()->validate('photos.view', $photoTable->id, SOCIAL_TYPE_PHOTO, $user->id);

		// Determines if the user can view the album of the user
		$showPhotoPopup = true;

		if ($photoTable) {
			$photoLib = ES::photo($user->id, SOCIAL_TYPE_USER, $photoTable);
			$showPhotoPopup = $photoLib->viewable();
		}

		// Get lists of badges of the user.
		$badges = $user->getBadges();

		// Determine if user has pending friends
		$pendingFriends = 0;

		if ($user->id == $this->my->id) {
			$model = ES::model('Friends');
			$pendingFriends = $model->getTotalPendingFriends($user->id);
		}

		// Get the timeline link
		$defaultDisplay = $this->config->get('users.profile.display', 'timeline');
		$timelinePermalink = $user->getPermalink();
		$aboutPermalink = ESR::profile(array('id' => $user->getAlias(), 'layout' => 'about'));

		// For now no longer generate this URL /profile/about because it will caused unable to cache the SEF URL for the user about page due with multilingue site. #3649
		// if ($user->id == $user->isViewer()) {
		// 	$abouttPermalink = ESR::profile(array('layout' => 'about'));
		// }

		if ($defaultDisplay == 'about') {
			$aboutPermalink = $timelinePermalink;
			$timelinePermalink = ESR::profile(array('id' => $user->getAlias(), 'layout' => 'timeline'));
		}

		// Retrieve list of apps for this user
		$profile = $user->getProfile();

		$model = ES::model('Apps');
		$apps = $model->getUserApps($user->id, true, array('includeDefault' => true));

		$appsInstalled = array();

		// These are core apps that is not included in model->getUserApps
		$coreApps = array('friends', 'albums', 'videos', 'audios', 'followers', 'pages', 'events', 'groups', 'polls', 'marketplaces');

		foreach ($coreApps as $core) {

			if (in_array($core, array('albums', 'videos', 'audios', 'polls', 'marketplaces'))) {
				$method = 'canCreate' . ucfirst($core);
				$enabled = $user->$method();

				$options = array('uid' => $user->getAlias(), 'type' => SOCIAL_TYPE_USER);

				// Some setting is exist in user profile ACL
				if (in_array($core, array('polls', 'pages', 'groups', 'events'))) {
					$options = array('userid' => $user->getAlias());
					$enabled = $this->config->get($core . '.enabled') && $user->$method();
				}

				$permalink = ESR::$core($options);
			} else {
				$enabled = $this->config->get($core . '.enabled');
				$permalink = ESR::$core(array('userid' => $user->getAlias()));
			}

			if ($enabled) {
				$app = new stdClass;
				$app->title = JText::_('COM_ES_' . strtoupper($core));
				$app->pageTitle = JText::_('COM_ES_' . strtoupper($core));
				$app->permalink = $permalink;
				$app->active = $core;
				$app->element = $core;
				$app->isMore = false;
				$app->hasNotice = false;

				if ($core == 'friends') {
					$app->hasNotice = $pendingFriends > 0 ? true : false;
				}

				$appsInstalled[$core] = $app;
			}
		}

		if (is_array($apps)) {
			foreach ($apps as $app) {
				$app->title = $app->getAppTitle();
				$app->pageTitle = $app->getPageTitle();
				$app->permalink = ESR::profile(array('id' => $user->getAlias(), 'appId' => $app->getAlias()));
				$app->active = 'apps.' . $app->element;
				$app->isMore = false;
				$app->hasNotice = false;

				$appsInstalled[$app->element] = $app;
			}
		}

		// Get the profile header params
		$headerApps = $profile->getParams()->get('header_apps');
		$headerApps = json_decode($headerApps);

		// Process the apps menu
		$appsMenu = $this->processAppsMenu($appsInstalled, $headerApps, $active);

		$appsHeader = $appsMenu->appsHeader;
		$appsDropdown = $appsMenu->appsDropdown;
		$isMoreActive = $appsMenu->isMoreActive;
		$isMoreHasNotice = $appsMenu->isMoreHasNotice;

		$showDropdown = false;

		if ($appsDropdown && $this->config->get('users.layout.sidebarapps')) {
			$showDropdown = true;
		}

		$isSiteAdmin = $this->my->isSiteAdmin() ? true : false;

		$showOnlineState = $this->config->get('users.online.state', true);
		$showBrowseApps = $user->isViewer() && $this->config->get('users.layout.apps') && $this->config->get('apps.browser');

		$theme = ES::themes();
		$theme->set('showPhotoPopup', $showPhotoPopup);
		$theme->set('showOnlineState', $showOnlineState);
		$theme->set('timelinePermalink', $timelinePermalink);
		$theme->set('aboutPermalink', $aboutPermalink);
		$theme->set('defaultDisplay', $defaultDisplay);
		$theme->set('pendingFriends', $pendingFriends);
		$theme->set('active', $active);
		$theme->set('apps', $apps);
		$theme->set('user', $user);
		$theme->set('isSiteAdmin', $isSiteAdmin);
		$theme->set('cover', $cover);
		$theme->set('newCover', $newCover);
		$theme->set('showAvatar', $showAvatar);
		$theme->set('showBrowseApps', $showBrowseApps);
		$theme->set('badges', $badges);
		$theme->set('appsHeader', $appsHeader);
		$theme->set('appsDropdown', $appsDropdown);
		$theme->set('isMoreActive', $isMoreActive);
		$theme->set('isMoreHasNotice', $isMoreHasNotice);
		$theme->set('showDropdown', $showDropdown);
		$theme->set('uniqid', uniqId());

		$items[$user->id] = $theme->output('site/helpers/cover/user');

		return $items[$user->id];
	}

	/**
	 * Process apps menu for the headers
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function processAppsMenu($appsInstalled, $headerApps, $active, $defaultLimit = 4)
	{
		$appsHeader = array();
		$appsDropdown = array();
		$isMoreActive = false;
		$isMoreHasNotice = false;

		// Repositon them
		if ($headerApps) {

			$result = array();
			$resultDropdown = array();
			$isMore = false;

			$lastElement = end($headerApps);

			foreach ($headerApps as $element) {

				if (isset($appsInstalled[$element])) {
					$app = $appsInstalled[$element];

					if (!$isMore || $this->isMobile()) {
						$result[] = $app;
					} else {
						$resultDropdown[] = $app;

						// Check for active state
						if ($active == $app->active) {
							$isMoreActive = true;
						}

						// Check for notice state
						if ($app->hasNotice) {
							$isMoreHasNotice = true;
						}
					}

					unset($appsInstalled[$element]);
				}

				// Set the flag for the next iteration will always use dropdown
				if ($element == 'es-more-section' && !$this->isMobile() && $element != $lastElement) {
					$obj = new stdClass();
					$obj->isMore = true;
					$result[] = $obj;

					$isMore = true;
				}
			}

			// Check if there is some left over apps
			if ($appsInstalled) {
				foreach ($appsInstalled as $app) {
					$resultDropdown[] = $app;
				}
			}

			$appsHeader = $result;
			$appsDropdown = $resultDropdown;
		} else {
			// By default the first 5 will always show
			if (!$this->isMobile()) {
				$i = 0;
				$lastElement = end($appsInstalled);

				foreach ($appsInstalled as $app) {
					if ($i < 4) {
						$appsHeader[] = $app;
					} else if ($i == 4) { // Check fifth element if this is the last element
						$appsHeader[] = $app;

						// Append More dropdown if this is not the last element
						if ($app != $lastElement) {
							$obj = new stdClass();
							$obj->isMore = true;

							$appsHeader[] = $obj;
						}
					} else {
						$appsDropdown[] = $app;

						if ($active == $app->active) {
							$isMoreActive = true;
						}
					}

					$i++;
				}
			} else {
				$appsHeader = $appsInstalled;
			}
		}

		$result = new stdClass();
		$result->appsHeader = $appsHeader;
		$result->appsDropdown = $appsDropdown;
		$result->isMoreActive = $isMoreActive;
		$result->isMoreHasNotice = $isMoreHasNotice;

		return $result;
	}
}
