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

class SocialSidebarAbstract extends EasySocial
{
	public function __construct($moduleLibrary)
	{
		$this->lib = $moduleLibrary;

		parent::__construct();
	}

	public function inArrayCaseInsensitive($needle, $haystack)
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}

	public function getTemplatePath($type)
	{
		return JModuleHelper::getLayoutPath('mod_easysocial_sidebar', $type);
	}

	public function app($app, $cluster, $view)
	{
		$appsLib  = ES::apps();

		$options = array('moduleLib' => $this->lib);

		if ($cluster instanceof SocialUser) {
			$options['user'] = $cluster;
		} else {
			$options['cluster'] = $cluster;
		}

		$contents = $appsLib->renderModuleSidebar(SOCIAL_APPS_VIEW_TYPE_EMBED, $view, $app, $options);

		if (!$contents) {
			return;
		}

		$path = $this->getTemplatePath('app_sidebar');
		require($path);
	}

	/**
	 * Determine whether that is timeline page e.g. user and clusters page
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isTimelinePage($view = SOCIAL_TYPE_USERS)
	{
		// Retrieve current user profile page e.g. timeine and about
		$layout = $this->input->get('layout', '', 'cmd');

		// Retrieve current group/page type e.g. timeline or info
		$type = $this->input->get('type', '', 'cmd');

		// Retrieve current event type e.g. timeline or info
		$page = $this->input->get('page', '', 'cmd');

		// user profile view is under 'users'
		$allowedViews = array(SOCIAL_TYPE_USERS, SOCIAL_TYPE_GROUPS, SOCIAL_TYPE_EVENTS, SOCIAL_TYPE_PAGES);

		// Only available for these views
		if (!in_array($view, $allowedViews)) {
			return false;
		}

		// User profile view
		if ($view == SOCIAL_TYPE_USERS) {

			// There are cases where admin configures the default start item to about page
			$defaultDisplay = $this->config->get('users.profile.display');

			// Determine whether that is current view is timeline
			if ($defaultDisplay == 'timeline' && !$layout) {
				$layout = 'timeline';
			}

			// Determine whether that is current view is about page
			if ($defaultDisplay == 'about' && $layout != 'timeline') {
				$layout = 'about';
			}

			if ($layout != 'timeline') {
				return false;
			}

			return true;
		}

		// Group view
		if ($view == SOCIAL_TYPE_GROUPS) {

			// There are cases where admin configures the default start item to about page
			$defaultDisplay = $this->config->get('groups.item.display');

			// only group/page has type from the URL query string
			// standardise all using layout variable
			$layout = $type;

			// Determine whether that is current view is timeline
			if ($defaultDisplay == 'timeline' && !$layout) {
				$layout = 'timeline';
			}

			// Determine whether that is current view is about page
			if ($defaultDisplay == 'info' && $layout != 'timeline') {
				$layout = 'info';
			}

			if ($layout != 'timeline') {
				return false;
			}

			return true;
		}

		// Event view
		if ($view == SOCIAL_TYPE_EVENTS) {

			// There are cases where admin configures the default start item to about page
			$defaultDisplay = $this->config->get('events.item.display');

			// only events has page from the URL query string
			// standardise all using layout variable
			$layout = $page;

			// Determine whether that is current view is timeline
			if ($defaultDisplay == 'timeline' && !$layout) {
				$layout = 'timeline';
			}

			// Determine whether that is current view is about page
			if ($defaultDisplay == 'info' && $layout != 'timeline') {
				$layout = 'info';
			}

			if ($layout != 'timeline') {
				return false;
			}

			return true;
		}

		// Page view
		if ($view == SOCIAL_TYPE_PAGES) {

			// There are cases where admin configures the default start item to about page
			$defaultDisplay = $this->config->get('pages.item.display');

			// only group/page has type from the URL query string
			// standardise all using layout variable
			$layout = $type;

			// Determine whether that is current view is timeline
			if ($defaultDisplay == 'timeline' && !$layout) {
				$layout = 'timeline';
			}

			// Determine whether that is current view is about page
			if ($defaultDisplay == 'info' && $layout != 'timeline') {
				$layout = 'info';
			}

			if ($layout != 'timeline') {
				return false;
			}

			return true;
		}
	}
}
