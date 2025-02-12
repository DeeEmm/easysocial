<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialPageAppStoryHookNotificationUpdates
{
	/**
	 * Processes comment notifications
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function execute(SocialTableNotification &$item)
	{
		// Get the page item
		$page = ES::page($item->context_ids);

		// Get the actor
		$actor = ES::user($item->actor_id);

		$adminPost = false;

		// This is only applies to page admin notifications
		// For Page, this will be a different actor
		if ($page->isAdmin($actor->id)) {
			$item->setActorAlias($page);
			$adminPost = true;
			//$item->title = JText::sprintf('APP_PAGE_VIDEOS_PAGE_UPLOADED_NEW_VIDEO', $page->getName());
		}

		// Format the title
		if ($item->context_type == 'story.page.create') {

			// get the stream item.
			$stream = ES::table('Stream');
			$stream->load($item->uid);

			// check the post_as in for this item.
			if ($stream->post_as != SOCIAL_TYPE_PAGE) {
				$item->setActorAlias('');
				$adminPost = false;
			}

			$item->title = JText::sprintf('APP_PAGE_STORY_USER_POSTED_IN_PAGE', $actor->getName(), $page->getName());

			if ($adminPost) {
				$item->title = JText::sprintf('APP_PAGE_STORY_ADMIN_POSTED_IN_PAGE', $page->getName());
			}

			$item->image = $page->getAvatar();

			$content = '';

			if (!empty($item->content)) {
				$content = ES::string()->processEmoWithTruncate($item->content);
			}

			$item->content = $content;

			return $item;
		}

		if ($item->context_type == 'links.page.create') {

			// get the stream item.
			$stream = ES::table('Stream');
			$stream->load($item->uid);

			// check the post_as in for this item.
			if ($stream->post_as != SOCIAL_TYPE_PAGE) {
				$item->setActorAlias('');
				$adminPost = false;
			}



			$model = ES::model('Stream');
			$links = $model->getAssets($item->uid, SOCIAL_TYPE_LINKS);

			if (!$links) {
				return;
			}

			$link = ES::makeObject($links[0]->data);

			$item->title = JText::sprintf('APP_PAGE_STORY_USER_SHARED_LINK_IN_PAGE', $actor->getName(), $page->getName(), $link->title);

			if ($adminPost) {
				$item->title = JText::sprintf('APP_PAGE_STORY_ADMIN_SHARED_LINK_IN_PAGE', $page->getName(), $link->title);
			}

			// Retrieve the image cache path
			$stream = ES::stream();
			$streamItem = $stream->getItem($item->uid);

			if (!$streamItem) {
				$item->exclude = true;
				return;
			}

			$streamItem = $streamItem[0];

			if (!$streamItem) {
				return;
			}

			$assets = $streamItem->getAssets();

			if ($assets) {
				$assets = $assets[0];
			}

			$app = ES::table('App');
			$app->load(array('element' => 'links', 'group' => SOCIAL_TYPE_PAGE));

			$params = $app->getParams();

			$image = ES::links()->getImageLink($assets, $params);

			$item->image = $image;
			$item->content = '';
		}

		// Someone shared a file in a page
		if ($item->context_type == 'file.page.uploaded') {

			$item->title = JText::sprintf('APP_PAGE_STORY_ADMIN_SHARED_FILE_IN_PAGE', $page->getName());

			// Get the file object
			$file = ES::table('File');
			$file->load($item->context_ids);

			$page = ES::page($item->uid);

			$item->content = $file->name;

			if ($file->hasPreview()) {
				$item->image = $file->getPreviewURI();
			}

			return;
		}


		// Someone shared a photo in a page
		if ($item->context_type == 'photos.page.share') {

			// Based on the stream id, we need to get the stream item id.
			$stream = ES::table('Stream');
			$stream->load($item->uid);

			// check the post_as in for this item.
			if ($stream->post_as != SOCIAL_TYPE_PAGE) {
				$item->setActorAlias('');
				$adminPost = false;
			}

			// Get child items
			$streamItems = $stream->getItems();

			// Since we got all the child of stream, we can get the correct count
			$count = count($streamItems);

			$item->title = JText::sprintf(ES::string()->computeNoun('APP_PAGE_STORY_USER_SHARED_PHOTO_IN_PAGE', $count), $actor->getName(), $page->getName(), $count);

			if ($adminPost) {
				$item->title = JText::sprintf(ES::string()->computeNoun('APP_PAGE_STORY_ADMIN_SHARED_PHOTO_IN_PAGE', $count), $page->getName(), $count);
			}

			if ($count && $count == 1) {

				$photo = ES::table('Photo');
				$photo->load($streamItems[0]->id);

				$item->image = $photo->getSource();
				$item->content = '';

				return;
			}

			$item->content = '';

			return;
		}

		return $item;
	}
}
