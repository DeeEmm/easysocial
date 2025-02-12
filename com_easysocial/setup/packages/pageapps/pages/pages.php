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

class SocialPageAppPages extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		// Only for album
		if ($context != SOCIAL_TYPE_ALBUM) {
			return;
		}

		$album = ES::table('Album');
		$album->load($uid);

		$lib = ES::albums($album->uid, SOCIAL_TYPE_PAGE, $album);

		if (!$lib->viewable()) {
			return false;
		}

		return true;
	}


	public function onBeforeStorySave($template, $stream)
	{
		if (!$template->cluster_id || !$template->cluster_type) {
			return;
		}

		if ($template->cluster_type != SOCIAL_TYPE_PAGE) {
			return;
		}

		$page = ES::page($template->cluster_id);
		$params = $page->getParams();
		$moderate = (bool) $params->get('stream_moderation', false);

		// If not configured to moderate, skip this altogether
		if (!$moderate) {
			return;
		}

		// If the current user is a site admin or page admin or page owner, we shouldn't moderate anything
		if ($page->isAdmin() || $page->isOwner()) {
			return;
		}

		// When the script reaches here, we're assuming that the page wants to moderate stream items.
		$template->setState(SOCIAL_STREAM_STATE_MODERATE);
	}

	/**
	 * Processes notification for users notification within the page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		if ($item->cmd == 'pages.promoted') {
			$hook = $this->getHook('notification', 'page');

			return $hook->execute($item);
		}

		if ($item->cmd == 'pages.user.rejected') {
			$hook = $this->getHook('notification', 'page');

			return $hook->execute($item);
		}

		if ($item->cmd == 'pages.user.removed') {
			$hook = $this->getHook('notification', 'page');

			return $hook->execute($item);
		}
	}

	/**
	 * Determine if the cmd is allowed for the notification
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function isAllowedCmd($cmd)
	{
		$allowed = array('pages.user.rejected', 'pages.promoted', 'pages.user.removed');

		return in_array($cmd, $allowed);
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'pages') {
			return false;
		}

		// if this is a cluster stream, let check if user can view this stream or not.
		$params = ES::registry($item->params);
		$page = ES::page($params->get('page'));

		if (!$page) {
			return;
		}

		$item->cnt = 1;

		if (!$page->isPublic() && !$page->isMember()) {
			$item->cnt = 0;
		}

		return true;
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		// Get app params
		$params	= $this->getParams();

		$excludeVerb = [];

		if (!$params->get('stream_like', true)) {
			$excludeVerb[] = 'like';
		}

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'created';
		}

		if (!$params->get('stream_update', true)) {
			$excludeVerb[] = 'update';
		}

		if (!$params->get('stream_promoted', true)) {
			$excludeVerb[] = 'makeAdmin';
		}

		if ($excludeVerb !== false) {
			$exclude['pages'] = $excludeVerb;
		}
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		// We only want to process related items
		if ($item->context != 'pages') {
			return;
		}

		$page = $item->getCluster();

		if (!$page || !$page->id) {
			return;
		}

		// We do not want stream item to contain the repost link
		if (!$page->isMember($viewer->id)) {
			$item->repost = false;
			$item->commentLink = false;
			$item->commentForm = false;
		}

		// Only show Social Sharing in public page
		if (!$page->isOpen()) {
			$item->sharing = false;
		}

		// Check if the viewer can view item
		if (!$page->canViewItem($viewer->id)) {
			return;
		}

		// We dont want to display page that are invite only.
		if ($page->type == SOCIAL_PAGES_INVITE_TYPE) {
			return;
		}

		// Since we know user who created this page will always be the admin,
		// set the actor alias for this item
		$item->setActorAlias($page);
		$item->contentObj = $page->toExportData($viewer);

		$access = $page->getAccess();
		if ($viewer->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $viewer->id)) {
			$item->edit_link = $page->getEditPermalink();;
		}

		// Generates options for comment
		$options = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);

		$item->likes = ES::likes($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));
		$item->comments = ES::comments($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $options, $item->uid);
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		$appParams = $this->getParams();

		if ($item->verb == 'like' && $appParams->get('stream_like', true)) {
			$item->show = true;
		}

		if ($item->verb == 'create' && $appParams->get('stream_create', true)) {
			$item->show = true;
			$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		}

		// Only process these item in groups view
		if ($item->getPerspective() == 'PAGES') {

			if ($item->verb == 'makeAdmin' && $appParams->get('stream_promoted', true)) {
				$item->show = true;
			}

			if ($item->verb == 'update' && $appParams->get('stream_update', true)) {
				$item->show = true;
			}
		}
	}

	/**
	 * Trigger for onPrepareStream
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		// We only want to process related items
		if ($item->context != 'pages') {
			return;
		}

		$page = $item->getCluster();

		if (!$page || !$page->id) {
			return;
		}

		// We do not want stream item to contain the repost link
		if (!$page->isMember()) {
			$item->repost = false;
			$item->commentLink = false;
			$item->commentForm = false;
		}

		// Only show Social Sharing in public page
		if (!$page->isOpen()) {
			$item->sharing = false;
		}

		// Check if the viewer can view item
		if (!$page->canViewItem()) {
			return;
		}

		$params = $item->getParams();
		$appParams = $this->getParams();

		// We dont want to display page that are invite only.
		if ($page->type == SOCIAL_PAGES_INVITE_TYPE) {
			return;
		}

		// all the streams would require these
		$this->set('item', $item);
		$this->set('page', $page);
		$this->set('actor', $item->actor);

		if ($item->verb == 'like' && $appParams->get('stream_like', true)) {
			$this->prepareLikeStream($item, $page, $params);
		}

		if ($item->verb == 'makeAdmin' && $appParams->get('stream_promoted', true) && $item->getPerspective() == 'PAGES') {
			$this->prepareMakeAdminStream($item, $page, $params);
		}

		if ($item->verb == 'update' && $appParams->get('stream_update', true) && $item->getPerspective() == 'PAGES') {
			$this->prepareUpdateStream($item, $page, $params);
		}

		if ($item->verb == 'create' && $appParams->get('stream_create', true)) {
			$this->prepareCreateStream($item, $page, $params);
		}

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Prepares the stream when someone like the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareLikeStream(SocialStreamItem &$item, SocialPage $page, $params)
	{
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('themes:/site/streams/pages/like.title');

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_PAGE_PAGES_STREAM_HAS_LIKE_PAGE', $item->actor->getName()));
	}

	/**
	 * Prepares stream when someone has been promoted page admin
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareMakeAdminStream(SocialStreamItem &$item, SocialPage $page, $params)
	{
		$item->display = SOCIAL_STREAM_DISPLAY_MINI;
		$item->title = parent::display('themes:/site/streams/pages/admin.title');

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_PAGE_PAGES_STREAM_PROMOTED_TO_BE_ADMIN', $item->actor->getName()));
	}

	/**
	 * Prepares the stream when someone edit the page
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareUpdateStream(SocialStreamItem &$item, SocialPage $page, $params)
	{
		$item->title = parent::display('themes:/site/streams/pages/update.title');

		// Since we know only page admin will be able to update page
		// set the actor alias for this item
		$item->setActorAlias($page);

		$item->display = SOCIAL_STREAM_DISPLAY_MINI;

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_PAGE_PAGES_STREAM_UPDATED_PAGE', $item->actor->getName()));
	}

	/**
	 * Prepare ths stream when someone created a page
	 *
	 * @since   2.0
	 * @access  public
	 */
	private function prepareCreateStream(SocialStreamItem &$item, SocialPage $page, $params)
	{
		$access = $page->getAccess();
		if ($this->my->isSiteAdmin() || $page->isAdmin() || ($access->get('stream.edit', 'admins') == 'members' && $item->actor->id == $this->my->id)) {
			$item->edit_link = $page->getEditPermalink();;
		}

		// If we are in a pages perspective, it should just be a mini stream
		$item->title = parent::display('themes:/site/streams/pages/create.title');

		// Since we know user who created this page will always be the admin,
		// set the actor alias for this item
		$item->setActorAlias($page);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->preview = parent::display('themes:/site/streams/pages/preview');

		// Generates options for comment
		$options = array('url' => ESR::stream(array('layout' => 'item', 'id' => $item->uid, 'sef' => false)), 'clusterId' => $page->id);

		$item->likes = ES::likes($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $item->uid, array('clusterId' => $page->id));
		$item->comments = ES::comments($item->uid , $item->context, $item->verb, SOCIAL_APPS_GROUP_PAGE, $options, $item->uid);

		// Append the opengraph tags
		$item->addOgDescription(JText::sprintf('APP_PAGE_PAGES_STREAM_CREATED_PAGE', $item->actor->getName(), $page->getName()));
	}

	/**
	 * Trigger onNotificationExport
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onNotificationExport(&$item, &$content, $viewer)
	{
		if (!$this->isAllowedCmd($item->cmd)) {
			return;
		}

		$page = ES::page($item->uid);

		$content->type = 'page';
		$content->object = $page->toExportData($viewer);
	}
}
