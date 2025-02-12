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

class SocialUserAppArticle extends SocialAppItem
{
	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'article') {
			return;
		}

		$my = ES::user();
		$groups = $my->getAuthorisedViewLevels();

		$article = JTable::getInstance('Content');
		$article->load($uid);

		if (!$article->id) {
			// article not found.
			return false;
		}

		if (!in_array($article->access, $groups)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the element is supported in this app
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function isSupportedElement($element)
	{
		static $supported = array();

		if (!isset($supported[$element])) {
			$supported[$element] = false;
			$allowed = array('article.user.create', 'article.user.update', 'article.user.read');

			if (in_array($element, $allowed)) {
				$supported[$element] = true;
			}
		}

		return $supported[$element];
	}


	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation(&$item, $includePrivacy = true)
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'article') {
			return false;
		}

		$item->cnt = 1;

		if ($includePrivacy) {
			$uid = $item->id;
			$my = ES::user();
			$privacy = ES::privacy($my->id);

			$model = ES::model('Stream');
			$activityItem = $model->getActivityItem($item->id, 'uid');

			if ($activityItem) {
				$uid = $activityItem[0]->id;

				if (!$privacy->validate('core.view', $uid, SOCIAL_TYPE_ACTIVITY, $item->actor_id)) {
					$item->cnt = 0;
				}
			}
		}

		return true;
	}

	/**
	 * Prepares the activity log item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'article') {
			return;
		}

		// Get the context id.
		$actor = $item->actor;
		$article = $this->getArticle($item);
		$category = $this->getCategory($item, $article);
		$permalink = $this->getPermalink($item, $article, $category);

		$this->set('permalink', $permalink);
		$this->set('article', $article);
		$this->set('actor', $actor);

		// Load up the contents now.
		$item->title = parent::display('streams/' . $item->verb . '.title');
		$item->content = '';
	}

	/**
	 * Responsible to return the excluded verb from this app context
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamVerbExclude(&$exclude)
	{
		$params = $this->getParams();

		$excludeVerb = [];

		if (!$params->get('stream_create', true)) {
			$excludeVerb[] = 'create';
		}

		if (!$params->get('stream_update', true)) {
			$excludeVerb[] = 'update';
		}

		if (!$params->get('stream_read', true)) {
			$excludeVerb[] = 'read';
		}

		if ($excludeVerb !== false) {
			$exclude['article'] = $excludeVerb;
		}
	}

	/**
	 * Prepares the stream item
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'article') {
			return;
		}

		// Decorate the stream item
		$item->display = SOCIAL_STREAM_DISPLAY_FULL;

		// Get application params
		$params = $this->getParams();

		// Ensure the user can really read the article
		$article = $this->getArticle($item);

		// Skip this if the viewer do not have permission to view this article
		// Skipt this if this article state is not published
		if (!$this->canViewArticle($article) || $article->state != 1) {
			return;
		}

		if ($item->verb == 'create' && $params->get('stream_create', true)) {
			$this->prepareCreateArticleStream($item, $article);
		}

		if ($item->verb == 'update' && $params->get('stream_update', true)) {
			$this->prepareUpdateArticleStream($item, $article);
		}

		if ($item->verb == 'read' && $params->get('stream_read', true)) {
			$this->prepareReadArticleStream($item, $article);
		}

		$commentUrl = ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid, $article->language);
		$comments = ES::comments($item->contextId, $item->context, $item->verb, 'user',  array('url' => $commentUrl));
		$item->comments = $comments;

		// Append the opengraph tags
		$item->addOgDescription();
	}

	/**
	 * Determines if an article is viewable by the user
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function canViewArticle($article)
	{
		$groups = $this->my->getAuthorisedViewLevels();

		if (in_array($article->access, $groups)) {
			return true;
		}

		return false;
	}

	/**
	 * Formats the article
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function format(SocialStreamItem $item, &$article)
	{
		// Get the content
		$article->content = $article->introtext;

		if (empty($article->content)) {
			$article->content = $article->fulltext;
		}

		$article->content = $this->normalizeContent($article->content);

		$image = $this->processContentImage($article->content);

		if (!$image && !empty($article->images)) {
			$image = $this->getIntroImage($article->images);
		}

		$article->image = $image;
		$article->content = $this->processContentLength($article->content);
		$article->category = $this->getCategory($item, $article);
		$article->permalink = $this->getPermalink($item, $article, $article->category);
		$article->categoryPermalink = $this->getCategoryPermalink($item, $article->category);
		$article->date = ES::date($article->created);
	}

	/**
	 * Retrieve article's intro image
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getIntroImage($images)
	{
		$joomlaImages = json_decode($images);
		$image = '';

		if (!empty($joomlaImages->image_intro)) {
			$image = htmlspecialchars($joomlaImages->image_intro);
		}

		if (!$image && !empty($joomlaImages->image_fulltext)) {
			$image = htmlspecialchars($joomlaImages->image_fulltext);
		}

		// Added initial slashes if the image is hosted locally. #3443
		if (ESJString::stristr($image, 'https://') === false && ESJString::stristr($image, 'http://') === false && !empty($image)) {
			$image = rtrim(JURI::root(), '/') . '/' . ltrim($image, '/');
		}

		return $image;
	}

	/**
	 * Prepares the stream item for new article creation
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function prepareCreateArticleStream(SocialStreamItem &$item, $article)
	{
		// Check for scheduled article
		$publish = ES::date($article->publish_up)->toUnix();

		// Get current date
		$date = ES::date()->toUnix();

		// Do not display stream if the article is set to be sceduled
		if ($publish && $publish > $date) {
			return;
		}

		$item->likes = ES::likes($article->id, 'article', 'create');

		// Format the article
		$this->format($item, $article);

		// determine if user has the access to edit article or not.
		if ($item->actor->id == $this->my->id && $this->my->authorise('core.edit', 'com_content')) {
			$item->edit_link = $this->getPermalink($item, $article, $article->category, true);
		}

		$this->set('article', $article);
		$this->set('actor', $item->actor);

		$item->title = parent::display('themes:/site/streams/articles/create.title');
		$item->preview = parent::display('themes:/site/streams/articles/preview');

		// Append the opengraph tags
		$item->addOgDescription($article->content);
	}

	/**
	 * Prepares the stream item for new article creation
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function prepareUpdateArticleStream(SocialStreamItem &$item, $article)
	{
		// Format the article
		$this->format($item, $article);

		$this->set('article', $article);
		$this->set('actor', $item->actor);

		$item->title = parent::display('themes:/site/streams/articles/update.title');
		$item->preview = parent::display('themes:/site/streams/articles/preview');

		// Append the opengraph tags
		$item->addOgDescription($article->content);
	}

	/**
	 * Prepares the stream item when an article is being read
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function prepareReadArticleStream(SocialStreamItem &$item, $article)
	{
		// Format the article
		$this->format($item, $article);

		$this->set('article', $article);
		$this->set('actor', $item->actor);

		$item->title = parent::display('themes:/site/streams/articles/read.title');
		$item->preview = parent::display('themes:/site/streams/articles/preview');

		// Append the opengraph tags
		$item->addOgDescription($article->content);
	}

	/**
	 * Retrieves the article object
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getArticle($item)
	{
		// Load up the article dataset
		$article = JTable::getInstance('Content');
		$params = $item->getParams();

		$data = $params->get('article');

		$loadFromRegistry = false;

		if ($data) {
			$article->bind((array) $data);
			$loadFromRegistry = true;
		} else {
			$article->load($item->contextId);
		}

		// we need to check if the copy that
		// stored in registry is published or not.
		// if unpublished, this could be outdated copy.
		// lets load from jtable.
		if ($loadFromRegistry && !$article->state) {
			$article->load($item->contextId);
		}

		return $article;
	}

	/**
	 * Method to normalize the content of the article
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function normalizeContent($content)
	{
		// Expression to search for anything inside {}
		$regex = '/{(.*)}/i';

		// Find all instances of plugin and put in $matches for {}
		preg_match_all($regex, $content, $matchesreg, PREG_SET_ORDER);

		// {} matched
		if ($matchesreg) {
			foreach ($matchesreg as $match) {
				$content = str_replace($match[0], '', $content);
			}
		}

		return $content;
	}

	/**
	 * Extract the first image from the content and remove it from the content
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function processContentImage(&$content)
	{
		$image = '';

		// Use domdocument whenever available
		if (class_exists('DOMDocument') && $content) {
			$dom = new DOMDocument();

			// We'll need to suppress html markup errors when dealing with imperfect html.
			// This will prevent errors from bubbling up to your default error handler.

			@$dom->loadHTML($content);

			$images = $dom->getElementsByTagName('img');

			if ($images->length > 0) {
				if (isset($images[0])) {
					$image = $images[0]->getAttribute('src');
				}
			}
		}


		if (!$image) {

			// @rule: Match images from content
			$pattern = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';
			preg_match($pattern, $content, $matches);

			if ($matches) {
				$image = isset($matches[1]) ? $matches[1] : '';
			}
		}

		if (!$image) {
			return $image;
		}

		if (ESJString::stristr($image, 'https://') === false && ESJString::stristr($image, 'http://') === false && !empty($image)) {
			$image = rtrim(JURI::root(), '/') . '/' . ltrim($image, '/');
		}

		// Remove the first image from content to avoid duplicate image
		$pattern = '#<img[^>]*>#i';
		preg_match($pattern, $content, $matches);

		if ($matches) {
			$content = str_ireplace($matches[0], '', $content);
		}

		return $image;
	}

	/**
	 * Process maximum content length allowed from the apps settings
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function processContentLength($content)
	{
		// Limit the content length
		$params = $this->getParams();
		$contentLength = $params->get('stream_content_length');

		if ($contentLength) {
			$content = ESJString::substr(strip_tags($content), 0, $contentLength) . '... ';
		} else {
			$base = JURI::base(true) . '/';

			// To check for all unknown protocols (a protocol must contain at least one alpahnumeric fillowed by :
			$protocols = '[a-zA-Z0-9]+:';

			$regex = '#(src|href|poster)="(?!/|'.$protocols.'|\#|\')([^"]*)"#m';
			$content = preg_replace($regex, "$1=\"$base\$2\"", $content);
		}

		return $content;
	}

	/**
	 * Retrieves the article category
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function getCategory($item, $article)
	{
		// Load up the category dataset
		$category = JTable::getInstance('Category');

		if ($item->params) {
			$registry = ES::registry($item->params);

			if ($registry->get('category')) {
				$category->bind((array) $registry->get('category'));
			}
		} else {
			$category->load($article->catid);
		}

		return $category;
	}

	/**
	 * Retrieve article permalink
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function getPermalink($item, $article, $category, $isEdit = false)
	{
		if ($isEdit) {
			$permalink = ESContentHelperRoute::getFormRoute($article->id);
			return JRoute::_($permalink);
		}

		$permalink = ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid . ':' . $category->alias, $article->language);
		return JRoute::_($permalink);
	}

	/**
	 * Retrieve category permalink for the article
	 *
	 * @since	2.1
	 * @access	private
	 */
	private function getCategoryPermalink($item, $category)
	{
		// Get the category permalink
		$categoryPermalink = ESContentHelperRoute::getCategoryRoute($category->id . ':' . $category->alias, $category->language);
		return JRoute::_($categoryPermalink);
	}

	/**
	 * Method to process notifications after likes is saved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onAfterLikeSave(&$like)
	{
		if (!$this->isSupportedElement($like->type)) {
			return;
		}

		$segments = $like->type;

		list($element, $group, $verb) = explode('.', $segments);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $like->uid));

		if (!$state) {
			return;
		}

		$owner = $streamItem->actor_id;

		$systemOptions = array(
			'title' => '',
			'context_type' => $like->type,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $like->created_by,
			'uid' => $like->uid,
			'aggregate' => true
		);

		if ($like->created_by != $owner) {
			ES::notify('likes.item', array($owner), array(), $systemOptions);
		}

		$recipients = $this->getStreamNotificationTargets($like->uid, $element, $group, $verb, array(), array($owner, $like->created_by));

		ES::notify('likes.involved', $recipients, array(), $systemOptions);
	}

	/**
	 * Before a comment is deleted, delete notifications tied to the comment
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function onBeforeDeleteComment(SocialTableComments $comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		// Here we know that comments associated with article is always
		// comment.uid = notification.uid
		$model = ES::model('Notifications');
		$model->deleteNotificationsWithUid($comment->uid, $comment->element);
	}

	/**
	 * onBeforeCommentSave trigger to re-sync comment in stream
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onBeforeCommentSave(&$comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		$segments = $comment->element;

		list($element, $group, $verb) = explode('.', $segments);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $comment->uid));

		if (!$state) {
			return;
		}

		if (empty($comment->stream_id)) {
			$comment->stream_id = $streamItem->uid;
		}

		return true;
	}

	/**
	 * onAfterCommentSave trigger to process notifications after the comment is saved
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onAfterCommentSave(&$comment)
	{
		if (!$this->isSupportedElement($comment->element)) {
			return;
		}

		$segments = $comment->element;

		list($element, $group, $verb) = explode('.', $segments);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $comment->uid));

		if (!$state) {
			return;
		}

		$owner = $streamItem->actor_id;

		$permalink = $comment->getPermalink();

		// retrieve the first mentioned user from the comment
		$exclusion = $comment->getFirstMentionedUserFromComment();

		// Convert the mention tag to permalink only for comment.
		$commentOptions = [
			'commentId' => $comment->id,
			'hasTag' => true,
			'exclusion' => $exclusion
		];

		$parseBBCodeOptions = [
			'escape' => false,
			'links' => true,
			'code' => true
		];

		$stringLib = ES::string();
		$commentContent = $stringLib->normalizeContent($comment->comment, $parseBBCodeOptions, false, '', $commentOptions);

		$emailOptions = array(
			'title' => 'APP_USER_ARTICLE_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/user/apps/comment.item',
			'permalink' => ESR::external($permalink),
			'comment' => $commentContent
		);

		$systemOptions = array(
			'title' => '',
			'context_type' => $comment->element,
			'url' => $permalink,
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true,
			'content' => $commentContent
		);

		if ($comment->created_by != $owner && !$comment->isChild()) {
			ES::notify('comments.item', array($owner), $emailOptions, $systemOptions);
		}

		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner, $comment->created_by));

		$emailOptions['title'] = 'APP_USER_ARTICLE_EMAILS_COMMENT_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/user/apps/comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/apps/comment.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 * Format notification item
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		if (!$this->isSupportedElement($item->context_type)) {
			return;
		}

		$hook = $this->getHook('notification', $item->type);
		$hook->execute($item);

		return;
	}
}
