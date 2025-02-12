<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class FollowersControllerPages extends SocialAppsController
{
	/**
	 * Allows caller to filter followers
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getFollowers()
	{
		// Check for request forgeriess
		ES::checkToken();

		// Get the keyword
		$keyword = $this->input->get('keyword', '', 'default');

		// Get the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		$appParam = $this->getApp()->getParams();
		// Get the current filter
		$type = $this->input->get('type', '', 'word');

		// Check whether the viewer can really view the contents
		if (!$page->canViewItem()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION'));
		}

		// Check whether the viewer can really view the contents of pending
		if ($type == 'pending' && !$page->isAdmin() && !$page->isOwner() && !ES::isSiteAdmin()) {
			return $this->ajax->reject(JText::_('COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION'));
		}

		$options = array();
		$emptyText = 'APP_PAGE_FOLLOWERS_EMPTY';

		// Get the pagination settings
		$limit = (int) $appParam->get('follower.limit');

		// Followers to display per page.
		$options['limit'] = $limit;
		$options['state'] = SOCIAL_PAGES_MEMBER_PUBLISHED;

		// List only page admins
		if ($type === 'admin') {
			$options['admin'] = true;
		}

		// List only pending users
		if ($type === 'pending') {
			$options['state'] = SOCIAL_PAGES_MEMBER_PENDING;
		}

		if ($type === 'invited') {
			$options['state'] = SOCIAL_PAGES_MEMBER_INVITED;
		}

		// Display all followers including admins of the page
		if ((!$this->config->get('pages.followers.includeadmin') && !$page->isAdmin()) || $type == 'followers') {
			$options['followers'] = true;
		}

		if (!empty($keyword)) {
			$options['search'] = $keyword;
			$emptyText = 'APP_PAGE_FOLLOWERS_EMPTY_SEARCH';
		}

		$model = ES::model('Pages');
		$users = $model->getMembers($page->id, $options);
		$pending = $model->getTotalPendingFollowers($page->id);
		$pagination	= $model->getPagination();

		$pagination->setVar('view', 'pages');
		$pagination->setVar('layout', 'item');
		$pagination->setVar('id', $page->getAlias());
		$pagination->setVar('appId', $this->getApp()->getAlias() );
		$pagination->setVar('Itemid', ESR::getItemId('pages', 'item', $page->id));

		if ($pagination && $type && $type != 'all') {
			$pagination->setVar('filter', $type);
		}

		// Redirection url when an action is performed on a page follower
		$redirectionOptions = array('layout' => 'item', 'id' => $page->getAlias(), 'appId' => $this->getApp()->getAlias());

		if ($type) {
			$redirectionOptions['filter'] = $type;
		}

		$returnUrl = ESR::pages($redirectionOptions, false);
		$returnUrl = base64_encode($returnUrl);

		// Load the contents
		$theme = ES::themes();
		$theme->set('returnUrl', $returnUrl);
		$theme->set('pagination', $pagination);
		$theme->set('page', $page);
		$theme->set('users', $users);
		$theme->set('emptyText', $emptyText);
		$theme->set('pending', $pending);
		$theme->set('filter', $type);

		$contents = $theme->output('apps/page/followers/pages/wrapper');

		return $this->ajax->resolve($contents);
	}

}
