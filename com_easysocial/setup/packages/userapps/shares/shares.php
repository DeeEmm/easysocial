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

ES::import('admin:/includes/apps/apps');

class SocialUserAppShares extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'shares') {
			return;
		}

		// the only place that user can submit coments / react on this app is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}

	/**
	 * Responsible to generate the activity contents.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'shares') {
			return;
		}

		// Get the context id.
		$id = $item->contextId;

		// Get the actor
		$actor = $item->actor;

		// Set the actor for the themes.
		$this->set('actor' , $actor);


		// Load the profiles table.
		$share = ES::table('Share');
		$state  = $share->load($id);

		if (!$state) {
			return false;
		}

		$source = explode('.', $share->element);
		$element = $source[0];
		$group = $source[1];

		$config = ES::config();
		$file = dirname(__FILE__) . '/helpers/'.$element.'.php';

		if (JFile::exists($file)) {
			require_once($file);

			// Get class name.
			$className = 'SocialSharesHelper' . ucfirst($element);

			// Instantiate the helper object.
			$helper = new $className($item, $share);

			$item->content = $helper->getContent();
			$item->title = $helper->getTitle();
		}

		$item->display 	= SOCIAL_STREAM_DISPLAY_MINI;
	}

	/**
	 * Notify the owner of the stream when someone reposted their items
	 *
	 * @since   1.2
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function onAfterStreamSave(SocialStreamTemplate &$streamTemplate)
	{
		// We only want to process shares
		if ($streamTemplate->context_type != SOCIAL_TYPE_SHARE && !$streamTemplate->cluster_type) {
			return;
		}

		$allowed = array('add.stream', 'add.photos');

		if (!in_array($streamTemplate->verb, $allowed)) {
			return;
		}

		// Get the share object
		$share = ES::table('Share');
		$share->load($streamTemplate->context_id);

		// Because the verb is segmented with a ., we need to split this up
		$namespace = explode('.', $streamTemplate->verb);
		$verb = $namespace[0];
		$type = $namespace[1];

		// Add a notification to the owner of the stream
		$streamId = $streamTemplate->target_id;
		if ($streamTemplate->verb == 'add.photos') {
			// need to get the correct stream id
			$params = $share->getParams();
			$streamId = $params->get('streamId', $streamTemplate->target_id);
		}

		$stream = ES::table('Stream');
		$stream->load($streamId);

		// If the person that is reposting this is the same as the actor of the stream, skip this altogether.
		if ($streamTemplate->actor_id == $stream->actor_id) {
			return;
		}

		// Get the actor
		$actor = ES::user($streamTemplate->actor_id);


		// Prepare the email params
		$mailParams = array();
		$mailParams['actor'] = $actor->getName();
		$mailParams['actorLink'] = $actor->getPermalink(true, true);
		$mailParams['actorAvatar'] = $actor->getAvatar(SOCIAL_AVATAR_SQUARE);
		$mailParams['permalink'] = $stream->getPermalink(true, true);
		$mailParams['title'] = 'APP_USER_SHARES_EMAILS_USER_REPOSTED_YOUR_POST_SUBJECT';
		$mailParams['template'] = 'apps/user/shares/stream.repost';

		// Prepare the system notification params
		$systemParams = array();
		$systemParams['context_type'] = $streamTemplate->verb;
		$systemParams['url'] = $stream->getPermalink(false, false, false);
		$systemParams['actor_id'] = $actor->id;
		$systemParams['context_ids'] = $share->id;

		ES::notify('repost.item', array($stream->actor_id), $mailParams, $systemParams);
	}

	/**
	 * Process notifications
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		// Processes notifications when someone repost another person's item
		$allowed = array('add.stream', 'add.photos');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		// We should only process items from group here.
		$share = ES::table('Share');
		$share->load($item->context_ids);

		// Item no longer exists. Maybe user delete it or something. #4611
		if (!$share->id) {
			$item->exclude = true;
			return;
		}

		if (!in_array($share->element, ['stream.user', 'photos.user'])) {
			return;
		}

		if ($item->type == 'repost') {

			$hook = $this->getHook('notification', 'repost');
			$hook->execute($item);

			return;
		}
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	jos_social_stream, boolean
	 * @return  0 or 1
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'shares') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$uid = $item->id;
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$sModel = ES::model('Stream');
			$aItem = $sModel->getActivityItem($item->id, 'uid');

			if ($aItem) {
				$uid = $aItem[0]->id;

				if (!$privacy->validate('core.view', $uid , SOCIAL_TYPE_ACTIVITY , $item->actor_id)) {
					$item->cnt = 0;
				}
			}
		}

		return true;
	}

	private function getHelper(SocialStreamItem $item , SocialTableShare $share)
	{
		$source = explode('.', $share->element);
		$element = $source[0];

		$file = dirname(__FILE__) . '/helpers/' . $element .'.php';
		require_once($file);

		// Get class name.
		$className = 'SocialSharesHelper' . ucfirst($element);

		// Instantiate the helper object.
		$helper = new $className($item, $share);

		return $helper;
	}

	/**
	 * Generates the stream for reposting
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		// Only process this if the stream type is shares
		if ($item->context != 'shares') {
			return;
		}

		// Check if this is a cluster item
		$isCluster = $item->isCluster();

		if ($isCluster) {
			$cluster = $item->getCluster();

			if (!$cluster->canViewItem()) {
				return;
			}
		}

		// Get the single context id
		$id = $item->contextId;
		$share = ES::table('Share');
		$share->load($id);

		if (!$share->id) {
			return;
		}

		// Supported elements
		$allowed = array('albums', 'photos', 'stream', 'videos', 'audios', 'marketplaces');

		if (!in_array($share->getElement(), $allowed)) {
			return;
		}

		// Get the repost helper
		$helper = $this->getHelper($item, $share);

		// Apply actions on stream
		$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->comments = ES::comments($item->contextId , $item->context , $item->verb, SOCIAL_APPS_GROUP_USER , array('url' => $item->getPermalink(false, false, false)), $item->uid);
		$item->repost = false;

		// Get the content of the repost
		$title = $helper->getStreamTitle();
		$preview = $helper->getContent();

		// If the content is a false, there could be privacy restrictions.
		if ($preview === false) {
			return;
		}

		$item->display = ($item->display) ? $item->display : SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = $title;
		$item->preview = $preview;

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Generates the stream item for REST API
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function onPrepareRestStream(SocialStreamItem &$item, $includePrivacy = true, $viewer = null)
	{
		// Only process this if the stream type is shares
		if ($item->context != 'shares') {
			return;
		}

		// Check if this is a cluster item
		$isCluster = $item->isCluster();
		$cluster = false;

		if ($isCluster) {
			$cluster = $item->getCluster();

			if (!$cluster->canViewItem()) {
				return;
			}
		}

		// Get the single context id
		$id = $item->contextId;

		// We only need the single actor.
		// Load the profiles table.
		$share = ES::table('Share');
		$share->load($id);

		// If shared item no longer exist, exit here.
		if (!$share->id) {
			return;
		}

		// Get the current logged in user
		$my = ES::user();

		// Break down the shared element
		$segments = explode('.', $share->element);
		$element = $segments[0];

		// We only want to process items from albums, photos and stream
		$allowed = array('albums', 'photos', 'stream');

		if (!in_array($element, $allowed)) {
			return;
		}

		// Get the repost helper
		$helper = $this->getHelper($item, $share);
		$preview = $helper->getContent();

		// If the content is a false, there could be privacy restrictions.
		if ($preview === false) {
			return;
		}

		// We want the likes and comments to be associated with the "stream" rather than the shared item
		$item->likes = ES::likes($item->contextId , $item->context, $item->verb, SOCIAL_APPS_GROUP_USER, $item->uid);
		$item->comments = ES::comments($item->contextId , $item->context , $item->verb, SOCIAL_APPS_GROUP_USER , array('url' => $item->getPermalink(false, false, false)), $item->uid);
		// Share app does not allow reposting itself.
		$item->repost = false;

		$contents = $share->toExportData($viewer);
		// If the content is a false, there could be privacy restrictions.
		if ($contents === false) {
			return;
		}

		// Set stream display mode.
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		$item->content_raw = $share->content;
		$item->contentObj = $contents;
		$item->targets = false;
		$item->show = true;
	}

	/**
	 * Method to load notification for the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onPrepareRestNotification(&$item, SocialUser $viewer)
	{
		// Processes notifications when someone repost another person's item
		$allowed = array('add.stream', 'add.photos');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		if ($item->type == 'repost') {

			// We should only process items from user here.
			$share = ES::table('Share');
			$share->load($item->context_ids);

			// Item no longer exists. Maybe user delete it or something. #4611
			if (!$share->id) {
				$item->exclude = true;
				return;
			}

			if (!in_array($share->element, ['stream.user', 'photos.user'])) {
				return;
			}

			// Run standard notification processing
			$this->onNotificationLoad($item);
			$target = $item->target;

			$target->id = $share->uid;

			if ($item->context_type == 'add.photos') {
				// need to get the correct stream id
				$params = $share->getParams();
				$target->id = $params->get('streamId', $share->uid);
			}

			$target->type = 'stream';
			$target->endpoint = 'stream.item';
			$target->query_string = $target->endpoint . '&id=' . $target->id;

			$item->target = $target;
		}
	}
}
