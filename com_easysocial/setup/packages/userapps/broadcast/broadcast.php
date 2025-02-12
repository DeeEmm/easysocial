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

class SocialUserAppBroadcast extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'broadcast') {
			return;
		}

		// there is no coments / react on broadcast stream.
		// just return false.
		return false;
	}

	/**
	 * Processes as soon as the story is saved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onAfterStorySave(SocialStream &$stream, SocialTableStreamItem &$streamItem, SocialStreamTemplate &$streamTemplate)
	{
		// Determines if this request is for broadcasting
		$broadcast = $this->input->get('broadcast_broadcast', false, 'bool');

		if (!$broadcast || $streamItem->context_type != 'broadcast') {
			return;
		}

		// Get the broadcast link
		$link = $this->input->get('broadcast_link', '', 'default');

		// Get the broadcast title
		$title = $this->input->get('broadcast_title', '', 'string');

		// Get the broadcast content
		$content = $this->input->get('broadcast_content', '', 'string');

		if (empty($title) || empty($content)) {
			return;
		}

		// Determine context profile/group
		$context = $this->input->get('broadcast_context', 'profile', 'string');

		// Determine which target profile id
		$profileIds = $this->input->get('broadcast_profileId', array(), 'array');

		// Determine which type this broadcast is
		$type = $this->input->get('broadcast_type', 'notification', 'string');

		// Get the expiry date if any
		$expiryDate = $this->input->get('broadcast_expirydate', '', 'string');

		// Set this broadcast item as scheduled.
		$scheduled = $stream->scheduled;

		// For broadcasted items, we want to insert a new notification for everyone on the site
		$model = ES::model('Broadcast');

		// To check if user select via notification, save to notification table instead.
		if ($type == 'popup') {
			$id = $model->broadcast($profileIds, nl2br($content), $this->my->id, $title, $link, $expiryDate, $context, $streamItem->uid, $scheduled);
		} else {
			$id = $model->notifyBroadcast($profileIds, $title, $content, $link, $this->my->id, $streamItem, $context, $scheduled);
		}

		$streamItem->context_id = $id;

		// Save the stream object
		$streamItem->store();

		// Lets create the scheduled item.
		$scheduled = ES::Scheduler();
		$scheduled->create($streamItem->uid, $streamTemplate);
	}

	/**
	 * When a broadcast is made, it should also appear on the stream
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$stream)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($stream->context != 'broadcast') {
			return;
		}

		// Load up the broadcast object
		$contextId = isset($stream->contextIds[0]) ? $stream->contextIds[0] : false;

		if (!$contextId) {
			return;
		}

		// Load the broadcast item
		$broadcast = ES::table('Broadcast');
		$broadcast->load((int) $contextId);


		// var_dump($contextId);

		// check if the broadcast item has expired, don't show the stream item.
		if ($broadcast->hasExpired()) {
			return;
		}

		// Get the stream actor
		$actor = $stream->actor;

		$stream->display = SOCIAL_STREAM_DISPLAY_FULL;
		$stream->label = JText::_('COM_EASYSOCIAL_STREAM_APP_FILTER_BROADCAST');

		// There will not be any likes for this
		$stream->likes = false;
		$stream->comments = false;
		$stream->repost = false;
		$stream->sharing = false;
		$stream->privacy = false;

		$broadcast->content = ES::string()->replaceHyperlinks($broadcast->content);

		$this->set('broadcast', $broadcast);
		$this->set('actor', $actor);

		$stream->title = parent::display('themes:/site/streams/broadcasts/title');
		$stream->preview = parent::display('themes:/site/streams/broadcasts/preview');

		// for broadcast, we need to clear the content attribute or else we will have two same message.
		$stream->content = '';

		return true;
	}

	/**
	 * Prepares what should appear in the story form.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onPrepareStoryPanel($story)
	{
		// If the anywhereId exists, means this came from Anywhere module
		// We need to exclude broadcast form from it.
		if (!is_null($story->anywhereId)) {
			return;
		}

		// Broadcast disabled
		if (!$this->config->get('notifications.broadcast.popup')) {
			return;
		}

		// Broadcast tool only works for site admin
		if (!$this->my->isSiteAdmin()) {
			return;
		}

		// Get app properties
		$params = $this->getParams();

		// Create plugin object
		$plugin	= $story->createPlugin('broadcast', 'panel');

		// Get a list of profiles on the site
		$model = ES::model('Profiles');
		$groupModel = ES::model('Groups');

		$options = array('state' => SOCIAL_STATE_PUBLISHED);

		$profiles = $model->getProfiles($options);

		// Determine if the group is enabled on the site
		$groupEnabled = true;

		if (!$this->config->get('groups.enabled') || $groupModel->getTotalGroups(array('types' => 'all')) < 1) {
			$groupEnabled = false;
		}

		// We need to attach the button to the story panel
		$theme = ES::themes();
		$theme->set('profiles', $profiles);
		$theme->set('params', $params);
		$theme->set('groupEnabled', $groupEnabled);
		$theme->set('title', $plugin->title);

		$plugin->button->html = $theme->output('site/story/broadcast/button');
		$plugin->content->html = $theme->output('site/story/broadcast/form');

		// Attachment script
		$script	= ES::script();
		$plugin->script	= $script->output('site/story/broadcast/plugin');

		return $plugin;
	}

	/**
	 * Prepares what should appear in the story form.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onNotifierCheck(&$data)
	{
		if (!$this->config->get('notifications.broadcast.popup')) {
			return true;
		}

		$model = ES::model('Broadcast');
		$broadcasts = $model->getBroadcasts($this->my->id);

		$data->broadcasts = $broadcasts;

		return true;
	}

	/**
	 * Publishing the scheduled stream.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function onPublishScheduledAppStory(SocialTableStream &$stream, SocialTableStreamItem &$streamItem, SocialTableStreamScheduled &$scheduled)
	{
		if ($streamItem->context_type != 'broadcast') {
			return false;
		}
		
		$model = ES::model('Broadcast');
		$model->updateBroadcasts(array('streamId' => $streamItem->uid, 'state' => SOCIAL_STATE_PUBLISHED));

		// Publishing the scheduled table.
		$scheduled->publishScheduled($stream, $streamItem);
	}
}
