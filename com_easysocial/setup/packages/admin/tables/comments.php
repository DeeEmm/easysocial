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

ES::import('admin:/tables/table');

class SocialTableComments extends SocialTable
{
	public $id = null;
	public $element = null;
	public $uid = null;
	public $comment = null;
	public $created_by = null;
	public $post_as = null;
	public $created = null;
	public $depth = null;
	public $parent = null;
	public $child = null;
	public $lft = null;
	public $rgt = null;
	public $params = null;
	public $stream_id = null;

	// flag to tell if store need to trigger onBeforeCommentSave and onAfterCommentSave
	public $_trigger = true;

	// custom author for this comment
	public $alias = null;

	// mentions users
	public $_mentions = null;

	// Add a flag to determine whether the store comment process did process the add child comment count yet
	public $_hasProcessedAddChildCount = false;

	public function __construct($db)
	{
		parent::__construct('#__social_comments', 'id', $db);
	}

	/**
	 * Retrieves the element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getElement()
	{
		$parts = explode('.', $this->element);

		return $parts[0];
	}

	/**
	 * Retrieves the verb from the element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getVerb()
	{
		$parts = explode('.', $this->element);

		return $parts[2];
	}

	/**
	 * Retrieves the group from the element
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getGroup()
	{
		$parts = explode('.', $this->element);

		return $parts[1];
	}


	/**
	 * Retrieves the comment author object
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getAuthor()
	{
		$user = ES::user($this->created_by);

		return $user;
	}

	/**
	 * Allow caller to set a custom author alias
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setAuthorAlias($object)
	{
		$this->alias = $object;
	}

	/**
	 * Retrieve author of the comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAuthorAlias()
	{
		if (!$this->alias) {
			return $this->getAuthor();
		}

		return $this->alias;
	}

	/**
	 * Determine whether this comment has giphy or not
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function hasGiphy()
	{
		$hasGiphy = $this->getParams()->get('giphy', '');

		if (!$hasGiphy) {
			return false;
		}

		return $hasGiphy;
	}

	/**
	 * Get the overlay for a comment message (mentions & hashtags)
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getOverlay()
	{
		// Get the tags for the comment
		$model = ES::model('Tags');
		$tags = $model->getTags($this->id, 'comments');

		$overlay = $this->comment;

		if (empty($this->comment) && $this->hasGiphy()) {
			$overlay = ' ';
		}

		$counter = 0;
		$tmp = array();

		foreach ($tags as $tag) {

			if ($tag->type === 'entity' && $tag->item_type === SOCIAL_TYPE_USER) {
				$user = ES::user($tag->item_id);
				$replace = '<span data-value="user:' . $tag->item_id . '" data-type="entity">' . $user->getName() . '</span>';
			}

			if ($tag->type === 'hashtag') {
				$replace = '<span data-value="' . $tag->title . '" data-type="hashtag">' . "#" . $tag->title . '</span>';
			}

			if ($tag->type === 'emoticon') {
				$replace = '<span data-value="' . $tag->title . '" data-type="emoticon">' . ":" . $tag->title . '</span>';
			}

			$tmp[$counter] = $replace;

			$replace = '[si:mentions]' . $counter . '[/si:mentions]';
			$overlay = ESJString::substr_replace($overlay, $replace, $tag->offset, $tag->length);

			$counter++;
		}

		$overlay = ES::string()->escape($overlay);

		foreach ($tmp as $i => $v) {
			$overlay = str_ireplace('[si:mentions]' . $i . '[/si:mentions]', $v, $overlay);
		}

		return $overlay;
	}

	public function store($updateNulls = false)
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = ES::registry($this->params);
		}

		$this->params = $this->params->toString();

		$isNew = false;
		$mentions = '';

		if (empty($this->id)) {
			$isNew = true;
		}

		// Assign the mentions data into a temporary variable to prevent reset this 'mentions' property original value when call getParent function
		if ($this->_mentions) {
			$mentions = $this->_mentions;
		}

		// Get the group
		$group = $this->getGroup();

		ES::apps()->load($group);

		// Get the dispatcher object
		$dispatcher = ES::dispatcher();
		$args = array(&$this);

		if ($isNew && $this->_trigger) {
			// If there is parent id, means this is a replied comment
			if ($this->parent) {
				$parent = $this->getParent();

				if ($parent) {
					// Max 1 level of the depth
					$this->depth = 1;

					$parent->addChildCount();

					// Once procceed this above addChildCount function then need to set this property to false
					$this->_hasProcessedAddChildCount = false;
				}
			}

			$this->setBoundary();

			// @trigger: onValidateSpam
			// This triggers specially created for cleantalk. All comments should be checked for spam.
			$error = $dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onUserValidateCommentSpam', $args);

			if (in_array(true, $error)) {
				$this->setError(JText::_('COM_ES_COMMENT_SPAM'));

				return false;
			}

			// @trigger: onBeforeCommentSave
			$dispatcher->trigger($group, 'onBeforeCommentSave', $args);
		}

		$state = parent::store();

		if (!$state) {
			return false;
		}

		// process the save tags from the comment
		if (!$this->_hasProcessedAddChildCount) {
			if ($isNew) {
				$this->saveTags($mentions);
			} else {
				$this->updateTags($mentions);
			}
		}

		if ($this->_trigger) {
			$trigger = $isNew ? 'onAfterCommentSave' : 'onAfterCommentEdit';

			// @trigger: onAfterCommentSave
			$dispatcher->trigger($group, $trigger, $args);
		}

		return $state;
	}

	/**
	 * Save the tags
	 *
	 * @since   4.0.10
	 * @access  public
	 */
	public function saveTags($mentions = [])
	{
		if (!$mentions) {
			return;
		}

		$user = ES::user($this->created_by);

		$commentId = $this->id;
		$entityTags = [];

		foreach ($mentions as $row) {

			$mention = json_decode($row);

			$tag = ES::table('Tag');
			$tag->offset = $mention->start;
			$tag->length = $mention->length;
			$tag->type = $mention->type;

			if ($tag->type == 'hashtag') {
				$tag->title = $mention->value;
			}

			if ($tag->type == 'emoticon') {
				$title = str_replace(array('(', ')'), '', trim($mention->value));

				// Check if the title exists in database
				$model = ES::model('Emoticons');

				$emoticons = $model->getItems(array('title' => $title));

				if (!$emoticons) {
					continue;
				}

				$tag->title = $mention->value;
			}

			// Name tagging
			if ($tag->type == 'entity') {

				$parts = explode(':', $mention->value);

				if (count($parts) != 2) {
					continue;
				}

				$entityType = $parts[0];
				$entityId = $parts[1];

				// Do not allow tagging to happen if they are not friends
				$tag->item_id = $entityId;
				$tag->item_type = $entityType;
			}

			$tag->creator_id = $user->id;
			$tag->creator_type = SOCIAL_TYPE_USER;

			$tag->target_id = $commentId;
			$tag->target_type = 'comments';

			// Fixed for 'Fields don't have default value' error in Joomla 4
			if (!$tag->item_id) {
				$tag->item_id = 0;
			}

			if (!$tag->item_type) {
				$tag->item_type = '';
			}

			if (!$tag->title) {
				$tag->title = '';
			}

			$state = $tag->store();

			if ($state && $tag->type == 'entity') {

				$entityTags[] = $tag;
			}
		}

		if ($entityTags) {

			$exclusion = [];
			$commentOptions = [
				'commentId' => $commentId,
				'hasTag' => true
			];

			foreach ($entityTags as $entityTag) {

				$commentId = $entityTag->target_id;

				$commentTbl = ES::table('comments');
				$commentTbl->load($commentId);

				// mention user id
				$mentionedUserId = $entityTag->item_id;

				// Get the permalink to the comments
				$permalink = $commentTbl->getPermalink();
				$user = ES::user($commentTbl->created_by);

				// retrieve the first mentioned user from the comment
				$exclusion = $this->getFirstMentionedUserFromComment();

				$commentOptions['exclusion'] = $exclusion;

				$stringLib = ES::string();

				// Apply bbcode on the comment
				$parseBBCodeOptions = [
					'escape' => false,
					'links' => true,
					'code' => true
				];

				// Do not notify this if the first mentioned user same with the parent comment creator
				// Since the onAfterCommentSave method already handle to notify to the user who reply him
				if ($commentTbl->isChild() && ($commentTbl->getParent()->created_by == $mentionedUserId)) {
					continue;
				}

		        $commentContent = $stringLib->normalizeContent($commentTbl->comment, $parseBBCodeOptions, false, '', $commentOptions);

				// Notify recipients that they are mentioned in a comment
				$emailOptions   = [
					'title' => 'COM_EASYSOCIAL_EMAILS_USER_MENTIONED_YOU_IN_A_COMMENT_SUBJECT',
					'template' => 'site/comments/mentions',
					'permalink' => $permalink,
					'actor' => $user->getName(),
					'actorAvatar' => $user->getAvatar(SOCIAL_AVATAR_SQUARE),
					'actorLink' => $user->getPermalink(false, true),
					'message' => $commentContent
				];

				$systemOptions  = [
					'uid' => $commentTbl->stream_id,
					'context_type' => 'comments.user.tagged',
					'context_ids' => $commentTbl->id,
					'type' => 'comments',
					'url' => $permalink,
					'actor_id' => $user->id,
					'target_id' => $entityTag->item_id,
					'aggregate' => false,
					'content' => $commentContent
				];

				// Send notification to the target
				$state = ES::notify('comments.tagged', [$entityTag->item_id], $emailOptions, $systemOptions);
			}
		}
	}

	/**
	 * Update comment if contain a tag
	 *
	 * @since   4.0.10
	 * @access  public
	 */
	public function updateTags($mentions = [])
	{
		$commentId = $this->id;

		// Get existing tags and cross check
		$existingTags = ES::model('tags')->getTags($commentId, 'comments');

		// Store the currently used tags id in order to cross reference and delete from $existingTags later
		$usedTags = [];

		if ($mentions) {

			$user = ES::user($this->created_by);
			$entityTags = [];

			foreach ($mentions as $row) {

				$mention = (object) $row;
				$tag = ES::table('Tag');
				$state = false;

				// Try to load existing tag first first
				if ($mention->type === 'entity') {
					list($entityType, $entityId) = explode(':', $mention->value);

					$state = $tag->load(array(
						'offset' => $mention->start,
						'length' => $mention->length,
						'type' => $mention->type,
						'target_id' => $commentId,
						'target_type' => 'comments',
						'item_type' => $entityType,
						'item_id' => $entityId
					));

					if (!$state) {
						$tag->item_id = $entityId;
						$tag->item_type = $entityType;
					}
				}

				if ($mention->type === 'hashtag' || $mention->type === 'emoticon') {

					$title = $mention->value;

					if ($mention->type == 'emoticon') {

						if (is_array($mention->value)) {
							$title = $mention->value['title'];
						}

						$title = str_replace(array('(', ')', ':'), '', trim($title));
						$title = '(' . $title .')';
					}

					$state = $tag->load(array(
						'offset' => $mention->start,
						'length' => $mention->length,
						'type' => $mention->type,
						'target_id' => $commentId,
						'target_type' => 'comments',
						'title' => $title
					));

					if (!$state) {
						$tag->title = $title;
					}
				}

				// If state is false, means this is a new tag
				$isNew = !$state;

				// Only assign this properties if it is a new tag
				if ($isNew) {
					$tag->offset = $mention->start;
					$tag->length = $mention->length;
					$tag->type = $mention->type;
					$tag->target_id = $commentId;
					$tag->target_type = 'comments';
				}

				// If this is not a new tag, then we store the id into $usedTags
				if (!$isNew) {
					$usedTags[] = $tag->id;
				}

				// Regardless of new or old, we reassign the creator because it might be the admin editing the comment
				$tag->creator_id = $user->id;
				$tag->creator_type = SOCIAL_TYPE_USER;

				$state = $tag->store();

				if ($state && $isNew && $tag->type == 'entity') {
					$entityTags[] = $tag;
				}
			}

			if ($entityTags) {

				$exclusion = [];
				$commentOptions = [
					'commentId' => $commentId,
					'hasTag' => true
				];

				foreach ($entityTags as $entityTag) {

					$commentId = $entityTag->target_id;

					$commentTbl = ES::table('comments');
					$commentTbl->load($commentId);

					// mention user id
					$mentionedUserId = $entityTag->item_id;

					// Get the permalink to the comments
					$permalink = $commentTbl->getPermalink();
					$user = ES::user($commentTbl->created_by);

					// retrieve the first mentioned user from the comment
					$exclusion = $this->getFirstMentionedUserFromComment();

					$commentOptions['exclusion'] = $exclusion;

					$stringLib = ES::string();

					// Apply bbcode on the comment
					$parseBBCodeOptions = [
						'escape' => false,
						'links' => true,
						'code' => true
					];

					// Do not notify this if the first mentioned user same with the parent comment creator
					// Since the onAfterCommentSave method already handle to notify to the user who reply him
					if ($commentTbl->isChild() && ($commentTbl->getParent()->created_by == $mentionedUserId)) {
						continue;
					}

			        $commentContent = $stringLib->normalizeContent($commentTbl->comment, $parseBBCodeOptions, false, '', $commentOptions);

					// Notify recipients that they are mentioned in a comment
					$emailOptions = [
						'title' => 'COM_EASYSOCIAL_EMAILS_USER_MENTIONED_YOU_IN_A_COMMENT_SUBJECT',
						'template' => 'site/comments/mentions',
						'permalink' => $permalink,
						'actor' => $user->getName(),
						'actorAvatar' => $user->getAvatar(SOCIAL_AVATAR_SQUARE),
						'actorLink' => $user->getPermalink(false, true),
						'message' => $commentContent
					];

					$systemOptions = [
						'uid' => $this->stream_id,
						'context_type' => 'comments.user.tagged',
						'context_ids' => $commentId,
						'type' => 'comments',
						'url' => $permalink,
						'actor_id' => $user->id,
						'target_id' => $tag->item_id,
						'aggregate' => false,
						'content' => $commentContent
					];

					// Send notification to the target
					ES::notify('comments.tagged', [$entityTag->item_id], $emailOptions, $systemOptions);
				}
			}
		}

		// Now we do a tag clean up to ensure tags that are not in used are deleted properly
		foreach ($existingTags as $existingTag) {
			if (!in_array($existingTag->id, $usedTags)) {
				$existingTag->delete();
			}
		}
	}

	/*
	 * tell store function not to trigger onBeforeCommentSave and onAfterCommentSave
	 */
	public function offTrigger()
	{
		$this->_trigger = false;
	}

	// No chainability
	public function update(array $newData)
	{
		// IMPORTANT:
		// No escape is required here as we store the data as is

		// General loop to update the rest of the new data
		foreach ($newData as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}

		$state = $this->store();

		if (!$state) {
			return false;
		}

		return true;
	}

	/**
	 * Overwrite of the original delete function to include more hooks
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function delete($pk = null)
	{
		// Ensure that the child comments are also deleted
		if ($this->isParent()) {
			$model = ES::model('Comments');
			$childs = $model->getChilds($this->id);

			foreach ($childs as $child) {
				$child->delete();
			}
		}

		$arguments = [&$this];

		// Trigger beforeDelete event
		$dispatcher = ES::dispatcher();
		$dispatcher->trigger(SOCIAL_APPS_GROUP_USER, 'onBeforeDeleteComment', $arguments);

		$state = parent::delete($pk);

		// Get the necessary group
		$group = $this->getGroup();

		ES::apps()->load($group);

		if ($state) {
			// Clear out all the likes for this comment
			$likesModel = ES::model('likes');
			$likesModel->delete($this->uid, 'comments');

			// Delete reactions made on the comment
			$likesModel->delete($this->id, 'comments.user.like');

			// #3147
			// Delete notifications associated to this comment.
			// We can only delete standard notifications where it matches the following:
			//
			// comment.id is related to notification.uid
			// comment.element is related to notification.context_type
			$notificationsModel = ES::model('Notifications');
			$notificationsModel->deleteNotificationsWithUid($this->id, $this->element);

			// Need to delete the reactions made on the comment notifications as well #3677
			$notificationsModel->deleteNotificationsWithUid($this->id, 'comments.user.like');

			// #3420
			// look like some element need to be removed using uid.
			// e.g. videos.user.create | videos.user.featured
			$requiredManualRemoval = array(
				'videos.user.create', 'videos.group.create', 'videos.event.create', 'videos.page.create',
				'videos.user.featured', 'videos.group.featured', 'videos.event.featured', 'videos.page.featured',
				'audios.user.create', 'audios.group.create', 'audios.event.create', 'audios.page.create',
				'audios.user.featured', 'audios.group.featured', 'audios.event.featured', 'audios.page.featured',
				'stream.user.upload', 'stream.group.upload', 'stream.page.upload', 'stream.event.upload',
				'photos.user.add', 'photos.group.add', 'photos.page.add', 'photos.event.add',
				'albums.user.create', 'albums.group.create', 'albums.page.create', 'albums.event.create'
			);

			if (in_array($this->element, $requiredManualRemoval)) {
				$notificationsModel->deleteNotificationsWithUid($this->uid, $this->element);
			}

			// Delete files related to this comment
			$filesModel = ES::model('Files');
			$filesModel->deleteFiles($this->id, 'comments');

			// Trigger afterDelete event
			$dispatcher->trigger($group, 'onAfterDeleteComment', $arguments);

			// We also need to clear the last action of the stream
			if ($this->stream_id) {
				$model = ES::model('Stream');
				$model->revertLastAction($this->stream_id, $this->created_by, 'comment');
			}
		}

		return $state;
	}

	/**
	 * Renders the output of comments
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function html($options = array())
	{
		$clusterId = $this->getParams()->get('clusterId', 0);
		$clusterType = $this->getParams()->get('clusterType', '');
		$giphy = $this->getParams()->get('giphy', '');

		// Modify the commentator
		if ($this->post_as == SOCIAL_TYPE_PAGE) {
			$page = ES::page($clusterId);
			$this->setAuthorAlias($page);
		}

		$likeOptions = array();

		if ($clusterType == SOCIAL_TYPE_PAGE && $clusterId) {
			$likeOptions['clusterId'] = $clusterId;
		}

		$author = $this->getAuthorAlias();
		$isAuthor = $this->isAuthor();
		$likes = ES::likes($this->id, 'comments', 'like', SOCIAL_APPS_GROUP_USER, null, $likeOptions);

		$theme = ES::themes();

		// Determines if the viewer can delete the comment
		$deleteable = isset($options['deleteable']) ? $options['deleteable'] : $isAuthor;
		$totalRepliesLimit = isset($options['totalRepliesLimit']) ? $options['totalRepliesLimit'] : 0;

		// Get attachments associated with this comment
		$model = ES::model('Files');
		$attachments = $model->getFiles($this->id, SOCIAL_TYPE_COMMENTS);

		$language = JFactory::getLanguage();
		$rtl = $language->isRTL();

		$showChildCommentLink = $this->showChildCommentLink($this, $totalRepliesLimit);
		$commentParentId = $this->normalizeCommentParentId($this, $totalRepliesLimit);

		$theme->set('rtl', $rtl);
		$theme->set('attachments', $attachments);
		$theme->set('deleteable', $deleteable);
		$theme->set('comment', $this);
		$theme->set('author', $author);
		$theme->set('isAuthor', $isAuthor);
		$theme->set('likes', $likes);
		$theme->set('identifier', uniqid());
		$theme->set('giphy', $giphy);
		$theme->set('totalRepliesLimit', $totalRepliesLimit);
		$theme->set('showChildCommentLink', $showChildCommentLink);
		$theme->set('commentParentId', $commentParentId);


		$html = $theme->output('site/comments/item');

		return $html;
	}

	/**
	 * Determine that whether need to show view child comment link
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function showChildCommentLink($comment, $totalRepliesLimit)
	{
		// The site user doesn't want to show child comment on the first load
		// So we need to handle this as well if the total of replies limit set to 0
		if (($comment->isChild() && isset($comment->last) && $comment->last && $comment->hasMore) || (!$totalRepliesLimit && $comment->isParent() && isset($comment->totalChildCount) && $comment->totalChildCount)) {
			return true;
		}

		return false;
	}

	/**
	 * Normalize the comment parent id to show on the comment listing if the Total Replies Visible Initially set to 0
	 *
	 * @since	4.0.7
	 * @access	public
	 */
	public function normalizeCommentParentId($comment, $totalRepliesLimit)
	{
		$commentParentId = $comment->parent;

		if ($totalRepliesLimit == 0 && $comment->isParent()) {
			$commentParentId = $comment->id;
		}

		if ($totalRepliesLimit == 0 && $comment->isChild()) {
			$commentParentId = $comment->parent;
		}

		return $commentParentId;
	}

	/**
	 * Deprecated. Use @html instead
	 *
	 * @deprecated	2.1.0
	 */
	public function renderHTML($options = array())
	{
		return $this->html($options);
	}

	/**
	 * Generates the permalink to the comment
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getPermalink($sef = true)
	{
		$base = $this->getParams()->get('url');

		if (empty($base)) {
			return false;
		}

		$base = ESR::_($base, false, [], null, false, false, '', '', $sef, false);
		$base .= '#commentid-' . $this->id;

		return $base;
	}

	/**
	 * Retrieve the comment and format it accordingly
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getComment($limit = 150)
	{
		// Set the comment data on a variable
		$comment = $this->comment;

		// Load up the string library
		$stringLib = ES::get('string');

		// 1.2.17 Update
		// We truncate to get a short preview content but in actual, we prepare 2 copies of data here.
		// Instead of separating the comments into Shorten and Balance, we do Shorten and Full instead.
		// Shorten contains first 150 character in raw.
		// Full contains the full comment, untruncated and processed.
		// The display part switches the shorten into the full content with JS.
		// Preview doesn't need to be processed.

		// Generate a unique id.
		$uid = uniqid();

		$model = ES::model('Tags');
		$tags = $model->getTags($this->id, 'comments');

		if ($tags) {
			$comment = $stringLib->processTags($tags, $comment, true, true);
		}

		$comment = $stringLib->escape($comment);

		// Convert the tags
		if ($tags) {
			$comment = $stringLib->afterProcessTags($tags, $comment, true, false);
			$comment = $stringLib->processSimpleTags($comment);
		}

		// Replace e-mail with proper hyperlinks
		$comment = $stringLib->replaceEmails($comment);

		// Apply bbcode on the comment
		$config = ES::config();
		$comment = $stringLib->parseBBCode($comment, array('escape' => false, 'emoticons' => $config->get('comments.smileys'), 'links' => true));

		// If there's a read more, then we prepare a short preview content
		$preview = '';

		// Determine if read more is needed.
		if ($readmore = ESJString::strlen(preg_replace('/<.*?>/', '', $comment)) >= $limit) {
			$preview = $stringLib->truncateWithHtml($comment, $limit);
		}

		$html = $comment;

		// #2192
		// When there is truncation, we need to find and replace links to avoid problems with hyperlinks
		if ($readmore) {

			require_once(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/crawler/helpers/simplehtml.php');
			$originalParser = SocialSimpleHTML::str_get_html($comment);
			$previewParser = SocialSimpleHTML::str_get_html($preview);

			$links = $previewParser->find('a');

			if ($links) {
				$originalLinks = $originalParser->find('a');

				for ($i = 0; $i < count($links); $i++) {
					$link =& $links[$i];
					$originalLink = $originalLinks[$i];

					$newLink = (string) $originalLink->getAttribute('href');

					$link->setAttribute('href', $newLink);
					$link = (string) $link;

					$preview = (string) $previewParser;
				}
			}

			$html = $preview;

			$html .= '<span data-es-comment-full style="display: none;">' . $comment . '</span>';
			$html .= '<span data-es-comment-readmore-' . $uid . ' data-es-comment-readmore>&nbsp;';
			$html .= '<a href="javascript:void(0);" data-es-comment-readmore>&nbsp;' . JText::_('COM_EASYSOCIAL_MORE_LINK') . '</a>';
			$html .= '</span>';
		}

		return $html;
	}

	/**
	 * Retrieves the date the comment was posted
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function getDate($format = '')
	{
		$config = ES::config();

		$date = ES::date($this->created);

		$elapsed = $config->get('comments_elapsed_time', true);

		// If format is passed in as true or false, this means disregard the elapsed time settings and obey the decision of format
		if ($format === true || $format === false) {
			$elapsed = $format;

			$format = '';
		}

		if ($elapsed && empty($format)) {
			return $date->toLapsed();
		}

		if (empty($format)) {
			return $date->toSql(true);
		}

		return $date->format($format);
	}

	public function getApp()
	{
		static $apps = array();

		if (empty($apps[$this->element])) {
			$app = ES::table('apps');

			$app->loadByElement($this->element, SOCIAL_APPS_GROUP_USER, SOCIAL_APPS_TYPE_APPS);

			$apps[$this->element] = $app;
		}

		return $apps[$this->element];
	}

	/**
	 * Get reports for this comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getReports()
	{
		$model = ES::model('Reports');

		$reports = $model->getReporters('com_easysocial', $this->id, 'comments');

		return $reports;
	}

	/**
	 * Determines if the provided user is the author of the comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function isAuthor($userid = null)
	{
		if (is_null($userid)) {
			$userid = ES::user()->id;
		}

		return $this->created_by == $userid;
	}

	public function getParams()
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = ES::registry($this->params);
		}

		return $this->params;
	}

	public function setParam($key, $value)
	{
		if (!$this->params instanceof SocialRegistry) {
			$this->params = ES::registry($this->params);
		}

		$this->params->set($key, $value);

		return true;
	}

	/**
	 * Determines if the user can delete this comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canDelete($userId = null)
	{
		$user = ES::user($userId);
		$access = $user->getAccess();

		if ($user->isSiteAdmin() || $access->allowed('comments.delete')) {
			return true;
		}

		if ($this->isAuthor($user->id) && $access->allowed('comments.deleteown')) {
			return true;
		}

		if ($this->isStreamAuthor($user->id) && $access->allowed('comments.deleteownstream')){
			return true;
		}

		$cluster = $this->getCluster();

		// For cluster admins, we want to allow them to delete
		if ($cluster && $cluster->getType() != SOCIAL_TYPE_USER && $cluster->isAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if this is a parent comment
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function isParent()
	{
		return $this->parent == 0;
	}

	/**
	 * Determine if this is a child comment a.k.a replied comment
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function isChild()
	{
		return $this->depth > 0 && $this->parent !== 0;
	}

	/**
	 * Determines if the current user is the author of the stream
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function isStreamAuthor($userId = null)
	{
		if (is_null($userId)) {
			$userId = ES::user()->id;
		}

		$model = ES::model('Stream');
		$isStreamAuthor = $model->isStreamAuthor($userId, $this->stream_id);

		if ($isStreamAuthor) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can edit the comment
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function canEdit($userId = null)
	{
		$user = ES::user($userId);
		$access = $user->getAccess();

		if ($user->isSiteAdmin()) {
			return true;
		}

		if ($access->allowed('comments.edit')) {
			return true;
		}

		if ($this->isAuthor($user->id) && $access->allowed('comments.editown')) {
			return true;
		}

		return false;
	}

	/**
	 * Retreives the cluster if the comment is posted in a cluster view
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getCluster()
	{
		static $cache = [];

		$params = $this->getParams();
		$id = $params->get('clusterId', 0);
		$type = $params->get('clusterType', '');

		if (!$id || !$type) {
			return false;
		}

		$key =  $id . '.' . $type;

		if (!isset($cache[$key])) {
			$cache[$key] = ES::cluster($type, $id);
		}

		return $cache[$key];
	}

	public function getParticipants($options = array())
	{
		$model = ES::model('Comments');

		$recipients = $model->getParticipants($this->uid, $this->element);

		if (!empty($options['excludeSelf'])) {
			$total = count($recipients);
			for($i = 0; $i < $total; $i++)
			{
				if ($recipients[$i] == $this->created_by) {
					unset($recipients[$i]);
					break;
				}
			}
		}

		$recipients = array_values($recipients);

		return $recipients;
	}

	public function addChildCount()
	{
		$this->child = $this->child + 1;

		// Set this porperty is because need to prevent this go through the save tag process
		$this->_hasProcessedAddChildCount = true;

		// Do not trigger the app event e.g. afterCommentSave
		$this->offTrigger();

		return $this->store();
	}

	public function getParent()
	{
		if (!$this->parent) {
			return false;
		}

		$parent = ES::table('Comments');
		$state = $parent->load($this->parent);

		if (!$state) {
			return false;
		}

		return $parent;
	}

	public function setBoundary()
	{
		$model = ES::model('Comments');
		$lastSibling = $model->getLastSibling($this->parent);

		$node = 0;

		if (empty($lastSibling)) {
			$parent = $this->getParent();

			if ($parent) {
				$node = $parent->lft;
			}
		}
		else {
			$node = $lastSibling->rgt;
		}

		if ($node > 0) {
			$model->updateBoundary($node);
		}

		$this->lft = $node + 1;
		$this->rgt = $node + 2;

		return true;
	}

	public function hasChild()
	{
		return $this->child > 0;
	}

	/**
	 * Exports necessary data from this table
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		$data = new stdClass();
		$data->id = $this->id;
		$data->element = $this->element;
		$data->comment_content = $this->getFormattedComment($viewer);
		$data->date = $this->created;
		$data->timestamp = $this->getDate();
		$data->attachments = false;
		$data->hasGiphy = $this->hasGiphy();
		$data->permalink = $this->getPermalink();
		$data->canDelete = $this->canDelete();
		$data->parent = $this->parent;
		$data->isParent = $this->isParent();
		$data->isChild = $this->isChild();
		$data->isLast = isset($this->last) ? $this->last : false;

		if ($this->post_as == SOCIAL_TYPE_PAGE) {
			$clusterId = $this->getParams()->get('clusterId', 0);
			$page = ES::page($clusterId);

			if ($page->id) {
				$this->setAuthorAlias($page);
			}
		}

		$data->author = $this->getAuthorAlias()->toExportData($viewer);
		$data->isAuthor = $this->isAuthor($viewer->id);

		$model = ES::model('Files');
		$attachments = $model->getFiles($this->id, SOCIAL_TYPE_COMMENTS);

		if ($attachments) {
			$media = array();
			foreach ($attachments as $attachment) {
				$media[] = $attachment->toExportData($viewer);
			}

			$data->attachments = $media;
		}

		return $data;
	}

	/**
	 * Retrieve the comment and format it according to REST API format
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getFormattedComment($viewer)
	{
		// Set the comment data on a variable
		$rawComment = $this->comment;

		$commentContent = new stdClass();
		$commentContent->raw = $rawComment;
		$commentContent->edit = $rawComment;
		$commentContent->formatted = '';
		$commentContent->object = '';

		if (!$rawComment) {
			return $commentContent;
		}

		$formatType = ['edit', 'formatted'];

		foreach ($formatType as $format) {
			$formattedComment = $rawComment;

			$model = ES::model('Tags');
			$tags = $model->getTags($this->id, 'comments');
			$stringLib = ES::string();

			// Format the tags accordingly
			if ($tags) {
				$replaceType = 'rest';

				if ($format === 'edit') {
					$replaceType = 'restEdit';
				}

				$formattedComment = $stringLib->processTags($tags, $formattedComment, true, $replaceType);
			}

			if ($format === 'formatted') {
				// bbcode comment
				$config = ES::config();
				$bbCodeOptions = array('escape' => false, 'emoticons' => $config->get('comments.smileys'), 'links' => true, 'restFormat' => true);
				$formattedComment = $stringLib->parseBBCode($formattedComment, $bbCodeOptions, $tags);

				// Remove <br>
				$formattedComment = str_ireplace(array('<br>', '</br>'), '', $formattedComment);
			}

			$commentContent->$format = $formattedComment;

			// echo '<pre>'; var_dump($formattedComment); echo '</pre>';exit;
		}

		// Finalize the format
		$commentObject = $this->formatCommentObjects($viewer, $commentContent->formatted, $tags);

		$commentContent->object = $commentObject;

		return $commentContent;
	}

	/**
	 * Method to process stream tags to satisfy the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function formatCommentObjects($viewer, $formattedContent, $tags)
	{
		$objects = array();

		// process all the objects that are tagged within the stream content. (emoticon, hashtag, etc)
		foreach ($tags as $tag) {
			if (!isset($tag->identifier)) {
				continue;
			}

			$object = new stdClass();
			$object->identifier = $tag->identifier;
			$object->type = $tag->type;

			// Process user mention
			if (($tag->type == 'user' || $tag->type == 'entity') && isset($tag->user) && $tag->user instanceof SocialUser) {
				$user = $tag->user;
				$object->user = $user->toExportData($viewer);
			}

			if ($tag->type == 'emoticon') {

				if (!isset($tag->source)) {
					$table = ES::table('emoticon');
					$table->load(array('title' => $tag->title));

					if (!$table->id) {
						continue;
					}

					$tag->source = $table->icon;
				}

				if (stristr($tag->source , 'http://') === false && stristr($tag->source , 'https://') === false) {
					$subFolder = JURI::root(true);
					$tag->source = ltrim($tag->source, '/');

					if ($subFolder) {
						$subFolder = ltrim($subFolder, '/');
						$parts = explode('/', $tag->source);

						// Determine if the source already included the sub folder
						if ($parts[0] !== $subFolder) {
							$tag->source = rtrim(JURI::root(), '/') . '/' . $tag->source;
						} else {
							$uri = JURI::getInstance();
							$root = $uri->toString(array('scheme', 'host'));
							$root = rtrim($root, '/');

							if (isset($uri->port) && $uri->port) {
								$root = $root . '/' . ltrim($uri->port, '/');
							}

							$tag->source = $root . '/' . $tag->source;
						}

					} else {
						$tag->source = rtrim(JURI::root(), '/') . '/' . $tag->source;
					}
				}

				$object->source = $tag->source;
			}

			if ($tag->type == 'hashtag') {
				$object->title = $tag->title;
			}

			if ($tag->type == 'external_url') {
				$object->url = $tag->url;
			}

			if ($tag->type == 'email') {
				$object->email = $tag->value;
			}

			$objects[$object->identifier] = $object;
		}

		// Split the content so that the app can re-assemble the content part by part.
		$contentObject = explode('[[object]]', $formattedContent);
		$newContentObject = array();

		foreach ($contentObject as $string) {
			$obj = new stdClass();

			if (!isset($objects[$string])) {

				// Nothing to process
				if (strlen($string) === 0) {
					continue;
				}

				$obj->type = 'string';
				$obj->value = $string;

				$newContentObject[] = $obj;
				continue;
			}

			$obj->type = 'object';
			$obj->value = $objects[$string];
			$newContentObject[] = $obj;
		}

		return $newContentObject;
	}

	/**
	 * Method to retrieve the first mentioned user id from the comment e.g. reply comment
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function getFirstMentionedUserFromComment()
	{
		if (!$this->isChild() || !$this->params) {
			return [];
		}

		$exclusion = [];

		// check the comment params `start` value
		$commentParams = ES::makeArray($this->params);

		if (isset($commentParams['mentions'])) {

			$mentionsData = $commentParams['mentions'];

			foreach ($mentionsData as $mentions) {
				$mentionsObj = ES::json()->decode($mentions);

				if (isset($mentionsObj->start) && $mentionsObj->start == 0) {
					$mentionUser = explode('user:', $mentionsObj->value);

					$exclusion = [$mentionUser[1]];
					break;
				}
			}
		}

		return $exclusion;
	}
}
