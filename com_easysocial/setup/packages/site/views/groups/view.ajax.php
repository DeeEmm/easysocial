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

class EasySocialViewGroups extends EasySocialSiteView
{
	/**
	 * Post process after filtering groups
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function filter($filter, $ordering, $groups = array(), $pagination = null, $featuredGroups = array(), $activeCategory = null, $isSortingRequest = false, $showAllFeatured = null, $activeUserId = null)
	{
		$theme = ES::themes();

		// Determines if the sorting should be included
		$showSorting = true;

		// Load the viewed profile
		$user = ES::user($activeUserId);

		// Distance
		$showDistanceSorting = false;
		$showDistance = false;
		$distance = $this->config->get('groups.nearby.radius');
		$delayed = false;
		$heading = JText::_('COM_EASYSOCIAL_GROUPS');

		$browseView = $this->input->get('browseView', false, 'bool');

		// Get the sorting urls
		$sortItems = new stdClass();
		$sortItems->latest = new stdClass();
		$sortItems->name = new stdClass();
		$sortItems->popular = new stdClass();

		$sortAttributes = array('data-sorting', 'data-filter="' . $filter . '"');
		$routeOptions = array();

		$routeOptions['filter'] = 'all';

		if ($filter != 'category') {
			$routeOptions['filter'] = $filter;
		}

		if ($activeCategory) {
			$sortAttributes[] = 'data-id="' . $activeCategory->id . '"';
			$routeOptions['categoryid'] = $activeCategory->getAlias();
		}

		$activeUserId = $this->input->get('userId', null, 'int');

		if (!$browseView && $activeUserId) {
			$routeOptions['userid'] = $user->getAlias();
		}

		// Filter by near by events
		if ($filter === 'nearby') {
			$showSorting = false;
			$showDistance = true;
			$showDistanceSorting = true;
			$defaultDistanceRadius = $distance;
			$distance = $this->input->get('distance', $defaultDistanceRadius, 'string');

			if (!empty($distance) && $distance != $defaultDistanceRadius) {
				$routeOptions['distance'] = $distance;
			}

			$heading = JText::sprintf('COM_ES_GROUPS_IN_RADIUS', $distance, $this->config->get('general.location.proximity.unit'));
		}

		// For nearby filter, we want to get the distance URL since we need to update the url
		$distanceUrl = '';

		if ($filter == 'nearby') {
			$distanceUrl = ESR::groups($routeOptions);
		}

		$helper = ES::viewHelper('Groups', 'List');

		// Render the sorting title attribute
		$latestSort = $helper->renderTitleAttribute($filter, $activeCategory, 'latest');
		$alphabeticalSort = $helper->renderTitleAttribute($filter, $activeCategory, 'alphabetical');
		$popularSort = $helper->renderTitleAttribute($filter, $activeCategory, 'popular');

		$sortItems->latest->attributes = array_merge($sortAttributes, ['data-type="latest"', $latestSort]);
		$sortItems->latest->url = ESR::groups(array_merge($routeOptions, ['ordering' => 'latest']));

		$sortItems->name->attributes = array_merge($sortAttributes, ['data-type="name"', $alphabeticalSort]);
		$sortItems->name->url = ESR::groups(array_merge($routeOptions, ['ordering' => 'name']));

		$sortItems->popular->attributes = array_merge($sortAttributes, ['data-type="popular"', $popularSort]);
		$sortItems->popular->url = ESR::groups(array_merge($routeOptions, ['ordering' => 'popular']));

		$emptyText = 'COM_ES_GROUPS_EMPTY_' . strtoupper($filter);

		if (!$browseView) {
			$emptyText = 'COM_ES_GROUPS_EMPTY_' . strtoupper($filter);

			if (!$user->isViewer()) {
				$emptyText = 'COM_ES_GROUPS_USER_EMPTY_' . strtoupper($filter);
			}
		}

		// Distance options
		$theme->set('heading', $heading);
		$theme->set('distance', $distance);
		$theme->set('distanceUnit', $this->config->get('general.location.proximity.unit'));
		$theme->set('showDistance', $showDistance);
		$theme->set('showDistanceSorting', $showDistanceSorting);
		$theme->set('delayed', $delayed);

		$theme->set('sortItems', $sortItems);
		$theme->set('ordering', $ordering);
		$theme->set('activeCategory', $activeCategory);
		$theme->set('filter', $filter);
		$theme->set('featuredGroups', $featuredGroups);
		$theme->set('groups', $groups);
		$theme->set('emptyText', $emptyText);
		$theme->set('showAllFeatured', $showAllFeatured);

		// We define this browse view same like $showsidebar. so it won't break when other custome that still using $showsidebar
		$theme->set('browseView', $browseView);

		// Since ajax calls to filter groups is only applicable when viewing all groups, this should be enabled by default
		$theme->set('showSidebar', true);
		$theme->set('pagination', $pagination);

		// Default namespace
		$namespace = 'wrapper';
		$sort = $this->input->get('sort', false, 'bool');

		if ($sort) {
			$namespace = 'items';
		}

		// Get the contents of normal groups
		$contents = $theme->output('site/groups/default/' . $namespace);

		return $this->ajax->resolve($contents, $distanceUrl);
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
		$theme->set('clusterType', SOCIAL_TYPE_GROUPS);
		$theme->set('profileId', $profileId);

		$html = '';

		$categoryRouteBaseOptions = array('controller' => 'groups' , 'task' => 'selectCategory');

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
	 * Outputs the app contents for a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getAppContents(SocialGroup $group, $app)
	{
		// Load the library.
		$lib = ES::getInstance('Apps');
		$contents = $lib->renderView(SOCIAL_APPS_VIEW_TYPE_EMBED, 'groups', $app, array('groupId' => $group->id));

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the invite friend form
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function invite()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Get a list of friends that are already in this group
		// and also that are already get invited
		$model = ES::model('Groups');
		$friends = $model->getFriendsInGroup($group->id, array('userId' => $this->my->id, 'published' => true, 'invited' => true));
		$exclusion = array();

		// Exclude users that already a member
		if ($friends) {
			foreach ($friends as $friend) {
				$exclusion[] = $friend->id;
			}
		}

		$theme = ES::themes();
		$theme->set('exclusion', $exclusion);
		$theme->set('group', $group);

		$contents = $theme->output('site/groups/dialogs/invite');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation dialog to set a group as featured
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmFeature()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('returnUrl', $returnUrl);

		$contents = $theme->output('site/groups/dialogs/feature');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation dialog to set a group as featured
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmUnfeature()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('returnUrl', $returnUrl);

		$contents = $theme->output('site/groups/dialogs/unfeature');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post process after a user response to the invitation.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function respondInvitation($group, $action)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the respond to invitation dialog
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmRespondInvitation()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group id from request
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Load the member
		$member = ES::table('GroupMember');
		$member->load(array('cluster_id' => $group->id, 'uid' => $this->my->id));

		// Get the inviter
		$inviter = ES::user($member->invited_by);

		$theme = ES::themes();
		$theme->set('cluster', $group);
		$theme->set('inviter', $inviter);

		$contents = $theme->output('site/clusters/dialogs/respond');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to delete a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmDelete()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		$theme = ES::themes();
		$theme->set('group', $group);
		$contents = $theme->output('site/groups/dialogs/delete');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to delete a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmUnpublishGroup()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		$theme = ES::themes();
		$theme->set('group', $group);

		$contents = $theme->output('site/groups/dialogs/unpublish');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to publish a group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmPublishGroup()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		$theme = ES::themes();
		$theme->set('group', $group);

		$contents = $theme->output('site/groups/dialogs/publish');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after withdrawing from a group
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function withdraw(SocialGroup $group)
	{
		$theme = ES::themes();
		$button = $theme->html('group.action', $group);

		return $this->ajax->resolve($button);
	}

	/**
	 * Displays the confirmation to approve user application
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmApprove()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Load the group
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Get the user id
		$ids = $this->input->get('userId', array(), 'array');

		$message = JText::sprintf('COM_ES_GROUPS_DIALOG_APPROVE_USER_REQUEST_CONFIRMATION', $group->getName());

		if ($ids && count($ids) > 1) {
			$message = JText::sprintf('COM_ES_GROUPS_DIALOG_APPROVE_USERS_REQUEST_CONFIRMATION', $group->getName());
		}

		// Get the return url
		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('return', $return);
		$theme->set('group', $group);
		$theme->set('ids', $ids);
		$theme->set('message', $message);

		$contents = $theme->output('site/groups/dialogs/approve');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to remove user from group
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmRemoveMember()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Get the user id
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		// Get the return redirection
		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('user', $user);
		$theme->set('return', $return);

		$contents = $theme->output('site/groups/dialogs/remove.member');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the confirmation to reject user application
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function confirmReject()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group object
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		if (!$group->canModerateJoinRequests()) {
			return $this->ajax->reject();
		}

		// Get the user id
		$ids = $this->input->get('userId', array(), 'array');
		// $user = ES::user($userId);

		$message = JText::sprintf('COM_ES_GROUPS_DIALOG_REJECT_USER_REQUEST_CONFIRMATION', $group->getName());

		if ($ids && count($ids) > 1) {
			$message = JText::sprintf('COM_ES_GROUPS_DIALOG_REJECT_USERS_REQUEST_CONFIRMATION', $group->getName());
		}

		// Get the return url
		$returnUrl = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('group', $group);
		$theme->set('message', $message);
		$theme->set('returnUrl', $returnUrl);
		$theme->set('ids', $ids);

		$contents = $theme->output('site/groups/dialogs/reject');

		return $this->ajax->resolve($contents);
	}


	/**
	 * Post process after group admin cancel the user invitation
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function cancelInvite(SocialGroup $group)
	{
		return $this->ajax->resolve();
	}

	/**
	 * Displays the join group exceeded notice
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function exceededJoin()
	{
		$allowed = $this->my->getAccess()->get( 'groups.join' );

		$theme = ES::themes();
		$theme->set('allowed', $allowed);
		$contents = $theme->output('site/groups/dialogs/join.exceeded');

		return $this->ajax->reject($contents);
	}

	/**
	 * Displays the disallowed join group notice
	 *
	 * @since	3.2.10
	 * @access	public
	 */
	public function disallowedJoin()
	{
		$theme = ES::themes();
		$contents = $theme->output('site/groups/dialogs/join.disallowed');

		return $this->ajax->reject($contents);
	}

	/**
	 * Displays the join group dialog
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function join(SocialGroup $group)
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group id from request
		$id = $this->input->get('id', 0, 'int');

		if (!$id || !$group) {
			return $this->ajax->reject();
		}

		// Try to load the member object
		$member = ES::table('GroupMember');
		$member->load(array('uid' => $this->my->id , 'type' => SOCIAL_TYPE_USER , 'cluster_id' => $group->id));

		$contents = false;
		$theme = ES::themes();
		$theme->set('group', $group);

		// Check if the group is open or closed
		if ($group->isClosed()) {
			$namespace = 'site/groups/dialogs/join.closed';

			if ($member->state == SOCIAL_GROUPS_MEMBER_PUBLISHED) {
				$namespace = 'site/groups/dialogs/join.invited';
			}

			$contents = $theme->output($namespace);
		}

		// Get the new button state
		$newButton = $theme->html('group.action', $group);
		$newJoinCount = $group->getTotalMembers();

		return $this->ajax->resolve($contents, $newButton, $newJoinCount);
	}

	/**
	 * Displays the make admin confirmation dialog
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmPromote()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group id from request
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Load up the user
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('group', $group);
		$theme->set('return', $return);

		$contents = $theme->output('site/groups/dialogs/promote');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Display the revoke admin confirmation dialog
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function confirmDemote()
	{
		// Only logged in users are allowed here
		ES::requireLogin();

		// Get the group id
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		// Load the user
		$userId = $this->input->get('userId', 0, 'int');
		$user = ES::user($userId);

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('user', $user);
		$theme->set('group', $group);
		$theme->set('return', $return);

		$contents = $theme->output('site/groups/dialogs/demote');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the join group dialog
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmLeaveGroup()
	{
		// Only logged in users are allowed here.
		ES::requireLogin();

		// Get the group id from request
		$id = $this->input->get('id', 0, 'int');
		$group = ES::group($id);

		$theme = ES::themes();
		$theme->set('group', $group);

		$contents = $theme->output('site/groups/dialogs/leave');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Responsible to return the default output when a user really leaves a group
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function leaveGroup()
	{
		return $this->ajax->resolve();
	}

	/**
	 * post processing after group filter get deleted.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function deleteFilter($groupId)
	{
		$this->info->set($this->getMessage());

		$group = ES::group($groupId);
		$url = ESR::groups(array('layout' => 'item' , 'id' => $group->getAlias()), false);

		return $this->ajax->redirect($url);
	}

	/**
	 * Retrieves the "about" section for a group
	 *
	 * @since	2.0
	 * @access	public
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

					$contents = $theme->output('site/groups/item/about');
				}
			}
		}

		return $this->ajax->resolve($contents);
	}

	/**
	 * Displays the suggest result
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function suggest($results = array())
	{
		if (!$results) {
			return $this->ajax->resolve(array());
		}

		$items = array();

		// Load through the result list.
		foreach ($results as $group) {

			$theme = ES::themes();
			$theme->set('group', $group);

			$items[] = $theme->output('site/groups/suggest/item');
		}

		return $this->ajax->resolve($items);
	}

	/**
	 * Post processing after a user is demoted
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function demote()
	{
		return $this->ajax->resolve();
	}

	/**
	 * Renders the popbox for a group
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

		$group = ES::group($id);

		$theme = ES::themes();
		$theme->set('group', $group);

		if (($group->isInviteOnly() || $group->isClosed()) && !$group->isMember() && !$this->my->isSiteAdmin()) {

			$contents = $theme->output('site/groups/popbox/restricted');
			return $this->ajax->resolve($contents);
		}

		$contents = $theme->output('site/groups/popbox/group');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after a user is promoted
	 *
	 * @since	2.0
	 * @access	public
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

		// Get the group id from request
		$id = $this->input->get('id', 0, 'int');

		$theme = ES::themes();
		$theme->set('clusterType', 'groups');
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

		$group = ES::group($uid);

		$theme->set('uid', $group->id);

		$output = $theme->output('site/avatar/dialogs/capture.picture');

		return $this->ajax->resolve($output);
	}
}
