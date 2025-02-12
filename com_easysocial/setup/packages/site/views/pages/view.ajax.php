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

class EasySocialViewPages extends EasySocialSiteView
{
	/**
	 * Post process after filtering page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function filter($filter, $ordering, $pages = array(), $pagination = null, $featuredPages = array(), $activeCategory = null, $isSortingRequest = false, $activeUserId = null)
	{
		$theme = ES::themes();

		$user = ES::user($activeUserId);

		// See if sorting should be included
		$showSorting = true;

		// Get the sorting URL
		$sortItems = new stdClass();
		$sortingTypes = array('latest', 'name', 'popular');

		$helper = ES::viewHelper('Pages', 'List');

		foreach ($sortingTypes as $sortingType) {

			$sortItems->{$sortingType} = new stdClass();

			// display the proper sorting name for the page title.
			$displaySortingName = JText::_($helper->getPageTitle(true));

			$sortType = JText::_("COM_ES_SORT_BY_SHORT_" . strtoupper($sortingType));
			$displaySortingName = $displaySortingName . ' - ' . $sortType;

			// attributes
			$sortAttributes = array('data-sorting', 'data-filter="' . $filter . '"', 'data-type="' . $sortingType . '"', 'title="' . $displaySortingName . '"');
			if ($activeCategory) {
				$sortAttributes[] = 'data-id="' . $activeCategory->id . '"';
			}

			//url
			$urlOptions = array();
			// $urlOptions['filter'] = $filter;

			$urlFilter = $filter;
			if ($urlFilter == 'category') {
				$urlFilter = 'all';
			}

			$urlOptions['filter'] = $urlFilter;
			$urlOptions['ordering'] = $sortingType;
			if ($activeCategory) {
				$urlOptions['categoryid'] = $activeCategory->getAlias();
			}
			$sortUrl = ESR::pages($urlOptions);

			$sortItems->{$sortingType}->attributes = $sortAttributes;

			$sortItems->{$sortingType}->url = $sortUrl;
		}

		$browseView = !$activeUserId;

		$emptyText = 'COM_EASYSOCIAL_PAGES_EMPTY_' . strtoupper($filter);

		if (!$browseView) {
			$emptyText = 'COM_ES_PAGES_EMPTY_' . strtoupper($filter);

			if (!$user->isViewer()) {
				$emptyText = 'COM_ES_PAGES_USER_EMPTY_' . strtoupper($filter);
			}
		}

		$theme->set('sortItems', $sortItems);
		$theme->set('ordering', $ordering);
		$theme->set('activeCategory', $activeCategory);
		$theme->set('filter', $filter);
		$theme->set('featuredPages', $featuredPages);
		$theme->set('pages', $pages);
		$theme->set('emptyText', $emptyText);
		$theme->set('browseView', $browseView);

		// Since this is an ajax call, we know this is for items with sidebar
		$theme->set('showSidebar', true);

		if ($isSortingRequest) {

			// Default contents
			$contents = '';

			// Here we need to also retrieve the featured pages as well
			if ($featuredPages) {
				$theme->set('heading', 'COM_EASYSOCIAL_PAGES_FEATURED_PAGES');
				$theme->set('pages', $featuredPages);
				$theme->set('pagination', false);

				$contents .= $theme->output('site/pages/default/items.list');
			}

			// Now retrieve the contents of the normal pages
			$theme->set('heading', 'COM_EASYSOCIAL_PAGES');
			$theme->set('pages', $pages);
			$theme->set('pagination', $pagination);

			$contents .= $theme->output('site/pages/default/items.list');

			return $this->ajax->resolve($contents);
		} else {
			$theme->set('pagination', $pagination);
		}

		// Get the contents of normal pages
		$contents = $theme->output('site/pages/default/items');

		return $this->ajax->resolve($contents);

	}

	/**
	 * Retrieves pages
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPages($pages = array(), $pagination = null, $featuredPages = array(), $sorting = null)
	{
		// Determines if we should add the category header
		$categoryId = $this->input->get('categoryId', '', 'int');
		$category = false;

		$theme = ES::themes();

		// Get the page category obj
		if ($categoryId) {
			$category = ES::table('PageCategory');
			$category->load($categoryId);
		}

		// Filter
		$filter = $this->input->get('filter', 'all', 'string');
		$sort = $this->input->get('ordering', '', 'default');

		if ($sort) {
			$theme->set('showSorting', false);
			$theme->set('showCategoryHeader', false);
		}

		$theme->set('activeCategory', $category);
		$theme->set('filter', $filter);
		$theme->set('ordering', $sort);
		$theme->set('pagination', $pagination);
		$theme->set('featuredPages', $featuredPages);
		$theme->set('pages', $pages);

		// Retrieve items from the template
		$content = $theme->output('site/pages/default/items');

		return $this->ajax->resolve($content);
	}

	/**
	 * Responsible to output the application contents.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAppContents(SocialPage $page, $app)
	{
		// Load the library
		$lib = ES::getInstance('Apps');
		$contents = $lib->renderView(SOCIAL_APPS_VIEW_TYPE_EMBED, 'pages', $app, array('pageId' => $page->id));

		// Return the content
		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the invite friend form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function invite()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		// Get a list of friends that are already in this page
		// and also that are already get invited
		$model = ES::model('Pages');
		$friends = $model->getFriendsInPage($page->id, array('userId' => $this->my->id, 'published' => true, 'invited' => true));
		$exclusion = array();

		if ($friends) {
			foreach ($friends as $friend) {
				$exclusion[] = $friend->id;
			}
		}

		$theme = ES::themes();
		$theme->set('exclusion', $exclusion);
		$theme->set('page', $page);

		$contents = $theme->output('site/pages/dialogs/invite');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation dialog to set a page as featured
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmFeature()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('returnUrl', $returnUrl);
		$theme->set('page', $page);

		$contents = $theme->output('site/pages/dialogs/feature');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation dialog to set a page as featured
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmUnfeature()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('returnUrl', $returnUrl);

		$contents = $theme->output('site/pages/dialogs/unfeature');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after a user response to the invitation.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function respondInvitation($page, $action)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the respond to invitation dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRespondInvitation()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		// Load the follower
		$follower = ES::table('PageMember');
		$follower->load(array('cluster_id' => $page->id, 'uid' => $this->my->id));

		// Get the inviter
		$inviter = ES::user($follower->invited_by);

		$theme = ES::themes();
		$theme->set('cluster', $page);
		$theme->set('inviter', $inviter);

		$contents = $theme->output('site/clusters/dialogs/respond');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to delete a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);
		$theme = ES::themes();
		$theme->set('page', $page);

		$contents = $theme->output('site/pages/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to delete a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmUnpublishPage()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		$theme = ES::themes();
		$theme->set('page', $page);

		$contents = $theme->output('site/pages/dialogs/unpublish');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to publish a page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmPublishPage()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		$theme = ES::themes();
		$theme->set('page', $page);

		$contents = $theme->output('site/pages/dialogs/publish');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to withdraw application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function withdraw(SocialPage $page)
	{
		$theme = ES::themes();
		$button = $theme->html('page.action', $page);

		return $this->ajax->resolve($button);
	}

	/**
	 * Displays the confirmation to approve user application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmApprove()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Load the page
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		// Get the user id
		$ids = $this->input->get('userId', array(), 'array');

		$message = JText::sprintf('COM_ES_PAGES_DIALOG_APPROVE_USER_REQUEST_CONFIRMATION', $page->getName());

		if ($ids && count($ids) > 1) {
			$message = JText::sprintf('COM_ES_PAGES_DIALOG_APPROVE_USERS_REQUEST_CONFIRMATION', $page->getName());
		}

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('return', $return);
		$theme->set('page', $page);
		$theme->set('message', $message);
		$theme->set('ids', $ids);

		$contents = $theme->output('site/pages/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to remove user from page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRemoveFollower()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		// Get the user id
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('return', $return);
		$theme->set('page', $page);
		$theme->set('user', $user);

		$contents = $theme->output('site/pages/dialogs/remove.follower');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to reject user application
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmReject()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		// Load up the page
		$page = ES::page($id);

		if (!$page->canModerateLikeRequests()) {
			return $this->ajax->reject();
		}

		// Get the user id
		$ids = $this->input->get('userId', array(), 'array');

		$message = JText::sprintf('COM_ES_PAGES_DIALOG_REJECT_USER_REQUEST_CONFIRMATION', $page->getName());

		if ($ids && count($ids) > 1) {
			$message = JText::sprintf('COM_ES_PAGES_DIALOG_REJECT_USERS_REQUEST_CONFIRMATION', $page->getName());
		}

		// Get the return URL
		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('returnUrl', $returnUrl);
		$theme->set('page', $page);
		$theme->set('ids', $ids);
		$theme->set('message', $message);

		$contents = $theme->output('site/pages/dialogs/reject');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to reject invitation for user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function cancelInvite(SocialPage $page)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the like page exceeded notice
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function exceededLike()
	{
		$ajax = ES::ajax();

		$allowed = $this->my->getAccess()->get('pages.like');

		$theme = ES::themes();
		$theme->set('allowed', $allowed);
		$contents = $theme->output('site/pages/dialogs/like.exceeded');

		return $ajax->resolve($contents);
	}

	/**
	 * Displays the like page dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function like(SocialPage $page)
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		if (!$id || !$page) {
			return $this->ajax->reject();
		}

		// Try to load the follower object
		$follower = ES::table('PageMember');
		$follower->load(array('uid' => $this->my->id, 'type' => SOCIAL_TYPE_USER, 'cluster_id' => $page->id));

		$contents = false;
		$theme = ES::themes();
		$theme->set('page', $page);

		// Check if the page is open or closed
		if ($page->isClosed() || $page->isInviteOnly()) {

			$namespace = 'site/pages/dialogs/like.closed';

			if ($follower->state == SOCIAL_PAGES_MEMBER_PUBLISHED) {
				$namespace = 'site/pages/dialogs/like.invited';
			}

			$contents = $theme->output($namespace);
		}

		// Get the button state
		$newButton = $theme->html('page.action', $page);
		$newLikeCount = $page->getTotalMembers();

		return $this->ajax->resolve($contents, $newButton, $newLikeCount);
	}

	/**
	 * Displays the make admin confirmation dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmPromote()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('page', $page);
		$theme->set('return', $return);

		$contents = $theme->output('site/pages/dialogs/promote');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display the revooke admin confirmation dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmDemote()
	{
		// Only logged in users are allowed here
		ES::requireLogin();

		// Load the page library
		$id = $this->input->get('id', 0, 'int');
		$page = ES::page($id);

		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('page', $page);
		$theme->set('return', $return);

		$contents = $theme->output('site/pages/dialogs/demote');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the like page dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmUnlike()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');
		$returnUrl = $this->input->get('return', '', 'default');

		// Load up the page
		$page = ES::page($id);

		$theme = ES::themes();
		$theme->set('page', $page);
		$theme->set('returnUrl', $returnUrl);

		$contents = $theme->output('site/pages/dialogs/unlike');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Responsible to return the default output when a user really unlike page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function unlike(SocialPage $page)
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		if (!$page) {
			return $this->ajax->reject();
		}

		// Determines which namespace we should be using
		$namespace = 'site/pages/dialogs/unlike.success';

		$theme = ES::themes();
		$theme->set('page', $page);

		$contents = $theme->output($namespace);

		return $this->ajax->resolve($contents);
	}

	/**
	 * post processing after page filter get deleted.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFilter($pageId)
	{
		$this->info->set($this->getMessage());

		$page = ES::page($pageId);
		$url = ESR::pages(array('layout' => 'item', 'id' => $page->getAlias()), false);

		return $this->ajax->redirect($url);
	}

	/**
	 * Retrieves the 'about' section for the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getInfo($steps)
	{
		$contents = '';

		// Format the about section now
		if ($steps) {
			foreach ($steps as $step) {
				if ($step->active) {
					$theme = ES::themes();
					$theme->set('fields', $step->fields);

					$contents = $theme->output('site/pages/item/about');
				}
			}
		}

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display the suggest result
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function suggest($results = array())
	{
		if (!$results) {
			return $this->ajax->resolve(array());
		}

		$items = array();

		foreach ($results as $page) {
			$theme = ES::themes();
			$theme->set('page', $page);

			$items[] = $theme->output('site/pages/suggest/item');
		}

		return $this->ajax->resolve($items);
	}

	/**
	 * Post processing after the user is demoted
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function demote()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Renders the popbox for a page
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function popbox()
	{
		$id = $this->input->get('id', 0, 'int');

		if (!$id) {
			return $this->ajax->reject();
		}

		$page = ES::page($id);

		// Display private page contents;
		if (($page->isInviteOnly() || $page->isClosed()) && !$page->isMember() && !$this->my->isSiteAdmin()) {
			$theme = ES::themes();
			$contents = $theme->output('site/pages/popbox/restricted');
			return $this->ajax->resolve($contents);
		}

		$theme = ES::themes();
		$theme->set('page', $page);

		$contents = $theme->output('site/pages/popbox/page');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after the user is promoted
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function promote()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Confirmation to remove an avatar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRemoveAvatar()
	{
		// Only registered users can do this
		ES::requireLogin();

		// Get the page id from request
		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('clusterType', 'pages');
		$theme->set('id', $id);
		$contents = $theme->output('site/clusters/dialogs/remove.avatar');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Allows caller to take a picture
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveCamPicture()
	{
		// Ensure that the user is a valid user
		ES::requireLogin();

		$image = $this->input->get('image', '', 'default');
		$image = imagecreatefrompng($image);

		ob_start();
		imagepng($image, null, 9);
		$contents = ob_get_contents();
		ob_end_clean();

		// Store this in a temporary location
		$file = md5(ES::date()->toSql()) . '.png';
		$tmp = JPATH_ROOT . '/tmp/' . $file;
		$uri = JURI::root() . 'tmp/' . $file;

		JFile::write($tmp, $contents);

		$result = new stdClass();
		$result->file = $file;
		$result->url = $uri;

		return $this->ajax->resolve($result);
	}

	/**
	 * Allows caller to take a picture
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function takePicture()
	{
		// Ensure that the user is logged in
		ES::requireLogin();

		$theme = ES::themes();

		$uid = $this->input->get('uid', 0, 'int');

		$page = ES::page($uid);

		$theme->set('uid', $page->id);

		$output = $theme->output('site/avatar/dialogs/capture.picture');

		return $this->ajax->resolve($output);
	}

	/**
	 * Output for getting subcategories
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getSubcategories($subcategories, $backId)
	{
		// Retrieve current logged in user profile type id
		$profileId = $this->my->getProfile()->id;

		$theme = ES::themes();
		$theme->set('backId', $backId);
		$theme->set('clusterType', SOCIAL_TYPE_PAGES);
		$theme->set('profileId', $profileId);

		$html = '';

		$categoryRouteBaseOptions = array('controller' => 'pages' , 'task' => 'selectCategory');

		foreach ($subcategories as $category) {
			$table = ES::table('ClusterCategory');
			$table->load($category->id);

			$theme->set('category', $table);
			$theme->set('categoryRouteBaseOptions', $categoryRouteBaseOptions);
			$html .= $theme->output('site/clusters/create/category.item');
		}

		return $this->ajax->resolve($html);
	}

	/**
	 * Toggle post as actor
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function togglePostAs()
	{
		$type = $this->input->get('type', 'page', 'string');
		$id = $this->input->get('id', 0, 'int');

		$page = ES::page($id);

		$url = $page->getPermalink(false);
		$url = $this->getReturnUrl($url);

		$uri = new JURI($url);
		$uri->setVar('viewas', $type);
		$returnUrl = $uri->toString();

		$session = JFactory::getSession();
		$session->set('easysocial.viewas', $type, SOCIAL_SESSION_NAMESPACE);

		return $this->ajax->redirect($returnUrl);
	}

}
