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

class EasySocialViewDashboard extends EasySocialSiteView
{
	/**
	 * Responsible to output the dashboard layout for the current logged in user.
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// If the user is not logged in, display the dashboard's unity layout.
		if ($this->my->guest) {
			return $this->guests();
		}

		// Check for user profile completeness
		ES::checkCompleteProfile();

		// Define page properties
		$title = $this->my->getName() . ' - ' . JText::_('COM_EASYSOCIAL_PAGE_TITLE_DASHBOARD');

		$this->page->title($title);
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_DASHBOARD');

		// Set Meta data
		ES::setMeta();

		// Get users filter preferences
		$userDefaultFilter = $this->my->getDashboardFilterPreferences();

		// Check if there is any stream filtering or not.
		$filter	= $this->input->get('filter_type', $userDefaultFilter->filter, 'word');

		if ($filter == 'scheduled' && !ES::user()->canPostScheduledStory()) {
			$filter = $userDefaultFilter->filter;
		}

		// The filter 'all' is taken from the menu item the setting. all == user & friend, which mean in this case, is the 'me' filter.
		$filter = $filter == 'all' ? 'me' : $filter;

		// Used in conjunction with type=appFilter
		$filterId = '';

		if ($filter == 'filter') {
			$filterId = $this->input->get('filterid', 0, 'int');
		}

		// Respect what is set by the user if needed to
		if ($userDefaultFilter->filter == 'custom') {
			$filterId = $userDefaultFilter->id;
		}

		// Determine if the current request is for "tags"
		$hashtag = $this->input->get('tag', '', 'default');

		if ($hashtag) {
			$filter = 'hashtag';
		}

		$feedOptions = array('filter' => $filter);
		$id = $this->input->get('id', 0, 'int');

		if ($id) {
			$feedOptions['id'] = $id;
		}

		// Add the rss links
		$this->addRss(ESR::dashboard($feedOptions, false));

		// Get available custom filters on the site
		$customFilters = array();

		$model = ES::model('Stream');
		$customFilters = $model->getFilters($this->my->id);

		// Application filters
		$appFilters = $model->getAppFilters(SOCIAL_TYPE_USER);

		$streamFilter = ES::streamFilter(SOCIAL_TYPE_USER, $this->config->get('users.dashboard.customfilters'));
		$streamFilter->setAppFilters($appFilters);
		$streamFilter->setActiveFilter($filter, $filterId);

		if ($this->config->get('users.dashboard.customfilters')) {
			$streamFilter->setCustomFilters($customFilters);
		}

		$streamFilter->setActiveHashtag($hashtag, $hashtag);

		$this->set('title', $title);
		$this->set('streamFilter', $streamFilter);
		$this->set('hashtag', $hashtag);
		$this->set('filter', $filter);

		return parent::display('site/dashboard/default/default');
	}

	/**
	 * Displays the guest view for the dashboard
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function guests()
	{
		// Add the rss links
		$this->addRss(ESR::dashboard(array(), false));

		// Default stream filter
		$filter = 'everyone';

		// Set Meta data
		ES::setMeta();

		// Render the hashtag filter header
		$streamFilter = ES::streamFilter(SOCIAL_TYPE_USER, false);

		// Determine if the current request is for "tags"
		$hashtag = $this->input->get('tag', '', 'default');

		if (!empty($hashtag)) {

			$filter = 'hashtag';

			// Render the hashtag filter
			$streamFilter->setActiveHashtag($hashtag, $hashtag);
		}

		// Define page properties
		$title = JText::_('COM_EASYSOCIAL_PAGE_TITLE_DASHBOARD');

		$this->page->title($title);
		$this->page->breadcrumb('COM_EASYSOCIAL_PAGE_TITLE_DASHBOARD');

		// Get default return url
		$return = ESR::getMenuLink($this->config->get('general.site.login'));
		$return = ES::getCallback($return);

		// If return value is empty, always redirect back to the dashboard
		if (!$return) {
			$return = ESR::dashboard(array(), false);
		}

		// In guests view, there shouldn't be an app id
		$appId = $this->input->get('appId', '', 'default');

		if ($appId) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PAGE_IS_NOT_AVAILABLE'));
		}

		// Ensure that the return url is always encoded correctly.
		$return = base64_encode($return);

		$this->set('streamFilter', $streamFilter);
		$this->set('filter', $filter);
		$this->set('hashtag', $hashtag);
		$this->set('return', $return);

		echo parent::display('site/dashboard/guests/default');
	}

	/**
	 * private method to check if there is any modules to display to guest or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function hasSideBarModules()
	{
		$sidebarPositions = array('es-dashboard-sidebar-top', 'es-dashboard-sidebar-before-newsfeeds', 'es-dashboard-sidebar-after-newsfeeds', 'es-dashboard-sidebar-bottom');

		foreach ($sidebarPositions as $position) {
			$modules = JModuleHelper::getModules($position);

			$checkedModules = array();
			// check for mod_easysocial_profile_statistic modules. if exits, remove this module as this module is not meant for guest.
			if ($modules) {
				foreach ($modules as $module) {
					if ($module->module != 'mod_easysocial_profile_statistic') {
						$checkedModules[] = $module;
					}
				}
			}

			if ($checkedModules) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves the stream contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getStream($stream , $type = '', $hashtags = array(), $streamFilter = '')
	{
		// Generate RSS link for this view
		$options = array('filter' => $type);
		$id = $this->input->get('id', 0, 'default');

		if ($id) {

			if ($type == 'custom') {
				$sfilter = ES::table('StreamFilter');
				$sfilter->load($id);

				$options['filter'] = 'filter';
				$options['filterid'] = $sfilter->id . ':' . $sfilter->alias;
			} else if ($type == 'list') {
				$options['listId'] = $id;
			} else {
				$options['id'] = $id;
			}
		}

		$this->addRss(ESR::dashboard($options, false));

		// Get the stream count
		$count = $stream->getCount();

		// Retrieve the story lib
		$story = ES::story(SOCIAL_TYPE_USER);

		// Set storyPanelFilter
		$story->setFilter($type);
		
		$story->showPostTo();

		// Get the tags
		if ($hashtags) {
			$hashtags = ES::makeArray($hashtags);
			$story->setHashtags($hashtags);
		}

		$allowedClusters = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE);
		$cluster = false;

		// If the stream is a group type, we need to set the story
		if (in_array($type, $allowedClusters)) {
			$story = ES::get('Story', $type);

			$clusterId = $this->input->getInt('id', 0);

			$story->setCluster($clusterId, $type);
			$story->showPrivacy(false);

			$cluster = ES::cluster($type, $clusterId);
		}

		// Set the story to the stream
		$stream->story = $story;

		$theme = ES::themes();
		$theme->set('cluster', $cluster);
		$theme->set('hashtag', false);
		$theme->set('stream', $stream);
		$theme->set('story', $story);
		$theme->set('streamcount', $count);
		$theme->set('customFilter', $streamFilter);

		$contents = $theme->output('site/dashboard/default/feeds');

		$data = new stdClass();
		$data->contents = $contents;
		$data->count = $count;

		echo json_encode($data);exit;
	}
}
