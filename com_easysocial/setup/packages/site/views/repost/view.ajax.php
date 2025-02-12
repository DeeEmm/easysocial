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

class EasySocialViewRepost extends EasySocialSiteView
{
	/**
	 * Returns an ajax chain.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function share($uid = null, $element = null, $group = SOCIAL_APPS_GROUP_USER, $streamId = 0, $currentViewName = '', $clusterId = '', $clusterType = '', $isModeration = false)
	{
		$share = ES::get('Repost', $uid, $element, $group);
		$count = $share->getCount();

		$countPluralize = ES::language()->pluralize($count, true)->getString();
		$text = JText::sprintf('COM_EASYSOCIAL_REPOST' . $countPluralize, $count);

		$hidden = ($count > 0) ? false : true;
		$html = '';

		// only append the stream item if the current view is not single stream layout
		if ($streamId && $currentViewName != 'stream') {
			$stream = ES::stream();

			if ($isModeration) {
				$stream->getItem($streamId, $clusterId, $clusterType, $isModeration);
			} else {
				$stream->getItem($streamId);
			}

			$html = $stream->html();
		}

		return $this->ajax->resolve($text, $hidden, $count, $html, $currentViewName);
	}

	/**
	 * Post process after retrieving a list of reposters
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getRepostAuthors($users)
	{
		$theme = ES::themes();
		$theme->set('users', $users);

		$contents = $theme->output('site/repost/popbox/authors');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the repost form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form()
	{
		ES::requireLogin();

		$element = $this->input->get('element', '', 'cmd');
		$group = $this->input->get('group', '', 'cmd');
		$uid = $this->input->get('id', 0, 'int');
		$streamId = $this->input->get('streamId', 0, 'int');
		$clusterId = $this->input->get('clusterId', 0, 'int');
		$clusterType = $this->input->get('clusterType', '', 'word');
		$currentViewName = $this->input->get('viewName', '', 'cmd');

		$cluster = false;
		$canRepostIntoCluster = false;
		$shareAs = 'user';

		$repost = ES::repost($uid, $element, $group);

		$theme = ES::themes();

		if ($clusterId && $clusterType) {

			$repost->setCluster($clusterId, $clusterType);
			$cluster = $repost->getCluster();

			// default to true if this is a cluster stream.
			$canRepostIntoCluster = $cluster->isMember() ? true : false;

			// Only a member can repost as content in private group.
			if (!$cluster->isMember() && $cluster->type == 2) {
				$theme->set('title', 'COM_ES_UNABLE_REPOST_FORM_DIALOG');
				$theme->set('desc', JText::sprintf('COM_ES_UNABLE_REPOST_FORM_DIALOG_DESC', $clusterType));
				$html = $theme->output('site/repost/dialogs/message');

				return $this->ajax->resolve($html);
			}

			// Only Page admin can repost as Page
			if ($cluster->getType() == SOCIAL_TYPE_PAGE && $cluster->isAdmin()) {
				$shareAs = $this->input->get('shareAs', 'page', 'word');
			}

			if ($cluster->isMember()) {

				$params = $cluster->getParams();
				$permissions = $params->get('stream_permissions', null);

				// If permissions has been configured before.
				if (!is_null($permissions)) {

					// If the user is not an admin, ensure that permissions has follower
					if (!$cluster->isAdmin() && !in_array('member', $permissions)) {
						$canRepostIntoCluster = false;
					}

					// If the user is an admin, ensure that permissions has admin
					if ($cluster->isAdmin() && !in_array('admin', $permissions) && !$cluster->isOwner()) {
						$canRepostIntoCluster = false;
					}
				}
			}

		}

		if ($streamId) {
			$repost->setStreamId($streamId);
		}

		// Check if the current user already shared this item or not. if yes, display a message and abort the sharing process.
		if ($repost->isShared($this->my->id, $shareAs)) {
			$theme->set('title', 'COM_EASYSOCIAL_REPOST_FORM_DIALOG_TITLE');
			$theme->set('desc', 'COM_EASYSOCIAL_REPOST_MESSAGE_ALREADY_REPOST');
			$html = $theme->output('site/repost/dialogs/message');

			return $this->ajax->resolve($html);
		}

		// Get dialog
		$preview = $repost->preview();
		$theme->set('preview', $preview);
		$theme->set('shareAs', $shareAs);
		$theme->set('currentViewName', $currentViewName);
		$theme->set('cluster', $cluster);
		$theme->set('canRepostIntoCluster', $canRepostIntoCluster);

		$html = $theme->output('site/repost/dialogs/form');

		return $this->ajax->resolve($html);
	}
}
