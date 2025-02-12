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

class EasySocialViewFollowersListHelper extends EasySocial
{
	private function createFilterLink($title, $pageTitle, $link)
	{
		$obj = new stdClass();
		$obj->label = JText::_($title);
		$obj->page_title = JText::_($pageTitle, true);
		$obj->link = $link;

		return $obj;
	}

	/**
	 * Determines the current active filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveFilter()
	{
		static $filter = null;

		if (is_null($filter)) {
			$filter = $this->input->get('filter', 'followers', 'cmd');
		}

		return $filter;
	}

	/**
	 * Retrieves the active user
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getActiveUser()
	{
		static $user = null;

		if (is_null($user)) {

			// Check if there's an id.
			// Okay, we need to use getInt to prevent someone inject invalid data from the url. just do another checking later.
			$id = $this->input->get('userid', null, 'int');

			// This is to ensure that the id != 0. if 0, we set to null to get the current user.
			if (empty($id)) {
				$id = null;
			}

			// Get the user.
			$user = ES::user($id);
		}

		return $user;
	}

	/**
	 * Retrieves the counters of the filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getCounters()
	{
		static $counter = null;

		if (is_null($counter)) {
			$counter = new stdClass();

			$user = $this->getActiveUser();
			$model = ES::model('Followers');

			$counter->followers = $model->getTotalFollowers($user->id);
			$counter->following = $model->getTotalFollowing($user->id);
			$counter->suggestion = $model->getTotalSuggestions($user->id);
		}

		return $counter;
	}

	/**
	 * Retrieves the filters available on the page
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilters()
	{
		static $filters = null;

		if (is_null($filters)) {
			$user = $this->getActiveUser();
			$filters = new stdClass();

			$filters->followers = $this->createFilterLink('COM_EASYSOCIAL_FOLLOWERS_FOLLOWERS', 'COM_EASYSOCIAL_PAGE_TITLE_FOLLOWERS', ESR::followers(array(), false));
			$filters->following = $this->createFilterLink('COM_EASYSOCIAL_FOLLOWERS_FOLLOWING', 'COM_EASYSOCIAL_PAGE_TITLE_FOLLOWING', ESR::followers(array('filter' => 'following'), false));
			$filters->suggestion = $this->createFilterLink('COM_EASYSOCIAL_FOLLOWERS_SUGGEST', 'COM_EASYSOCIAL_PAGE_TITLE_PEOPLE_TO_FOLLOW', ESR::followers(array('filter' => 'suggest'), false));

			if (!$user->isViewer()) {
				$filters->followers->link = ESR::followers(array('userid' => $user->getAlias()), false);
				$filters->following->link = ESR::followers(array('userid' => $user->getAlias(), 'filter' => 'following'), false);
			}
		}

		return $filters;
	}

	/**
	 * Generates the correct page title
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getPageTitle()
	{
		static $title = null;

		if (is_null($title)) {
			$title = 'COM_EASYSOCIAL_PAGE_TITLE_FOLLOWERS';
			$filter = $this->getActiveFilter();

			if ($filter == 'following') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_FOLLOWING';
			}

			if ($filter == 'suggest') {
				$title = 'COM_EASYSOCIAL_PAGE_TITLE_PEOPLE_TO_FOLLOW';
			}

			$user = $this->getActiveUser();

			if (!$user->isViewer()) {
				$title = $user->getName() . ' - ' . JText::_($title);
			}
		}

		return $title;
	}
}
