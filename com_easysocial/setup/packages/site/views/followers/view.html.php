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

class EasySocialViewFollowers extends EasySocialSiteView
{
	/**
	 * Determines if this feature is enabled
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function isEnabled()
	{
		if ($this->config->get('followers.enabled')) {
			return true;
		}

		return false;
	}

	/**
	 * Default method to display a list of friends a user has.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Ensure that the feature is enabled
		if (!$this->isEnabled()) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		$helper = $this->getHelper('List');
		$user = $helper->getActiveUser();

		// If the user is blocked, they should not be accessible #5069
		if ($user->isBlock() && !$this->my->isSiteAdmin()) {
			ES::raiseError(404, JText::_('COM_EASYSOCIAL_PROFILE_INVALID_USER'));
		}

		// Lets check if this user is a ESAD user or not
		if (!$this->my->canView($user, 'followers.view') || !$user->hasCommunityAccess()) {

			$facebook = ES::oauth('facebook');
			$return = base64_encode(ES::getURI(true));

			$this->set('return', $return);
			$this->set('facebook', $facebook);
			$this->set('user', $user);

			parent::display('site/profile/restricted');
			return;
		}

		// If user is not found, we need to redirect back to the dashboard page
		if (!$user->id) {
			return $this->redirect(ESR::dashboard(array(), false));
		}

		ES::checkCompleteProfile();
		ES::setMeta();

		$filter = $helper->getActiveFilter();
		$filters = $helper->getFilters();
		$title = $helper->getPageTitle();

		// Default limit
		$options = array('limit' => ES::getLimit('followersLimit'));

		$model = ES::model('Followers');

		if ($filter == 'followers') {
			$users = $model->getFollowers($user->id, $options);
		}

		if ($filter == 'following') {
			$users = $model->getFollowing($user->id, $options);
		}

		if ($filter == 'suggest') {
			$users = $model->getSuggestions($user->id, $options);
		}

		// Get the pagination
		$pagination = $model->getPagination();

		$this->page->title($title);
		$this->page->breadcrumb($title);

		// canonical links
		$options = array('external' => true);

		if (!$user->isViewer()) {
			$options['userid'] = $user->getAlias();
		}

		if ($filter && in_array($filter, array('following', 'suggest'))) {
			$options['filter'] = $filter;
		}

		$this->page->canonical(ESR::followers($options));

		$this->set('filters', $filters);
		$this->set('pagination', $pagination);
		$this->set('user', $user);
		$this->set('filter', $filter);
		$this->set('users', $users);

		return parent::display('site/followers/default/default');
	}
}
