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

class SocialUserAppK2 extends SocialAppItem
{

	/**
	 * Determines if the viewer can access the object for comments / reaction
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isItemViewable($action, $context, $verb, $uid)
	{
		if ($context != 'k2') {
			return;
		}

		// the only place that user can submit coments / react on apps is via stream.
		// if the stream id is missing, mean something is fishy.
		// just return false.
		return false;
	}


	public function exists()
	{
		$k2File = JPATH_ROOT . '/components/com_k2/helpers/route.php';

		if (!JFile::exists($k2File)) {
			return false;
		}

		// Ensure the component is enabled
		if (!JComponentHelper::isInstalled('com_k2')) {
			return false;
		}

		require_once($k2File);

		return true;
	}

	/**
	 * Determines if the app should be available
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function appListing()
	{
		if (!$this->exists()) {
			return false;
		}

		return true;
	}

	/**
	 * Triggered to validate the stream item whether should put the item as valid count or not.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onStreamCountValidation( &$item, $includePrivacy = true )
	{
		// If this is not it's context, we don't want to do anything here.
		if ($item->context_type != 'k2') {
			return false;
		}

		$item->cnt = 1;

		if (!$includePrivacy) {
			return true;
		}

		$uid = $item->id;
		$privacy = $this->my->getPrivacy();

		$model = ES::model('Stream');
		$item = $model->getActivityItem($item->id, 'uid');

		if (!$item) {
			return true;
		}

		$uid = $item[0]->id;

		if (!$privacy->validate('core.view', $uid, SOCIAL_TYPE_ACTIVITY, $item->actor_id)) {
			$item->cnt = 0;
		}

		return true;
	}

	/**
	 * Prepares the activity log item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onPrepareActivityLog(SocialStreamItem &$item, $includePrivacy = true )
	{
		if ($item->context != 'k2') {
			return;
		}

		// Get the context id.
		$actor 		= $item->actor;
		$article 	= $this->getArticle( $item );
		$category	= $this->getCategory( $item , $article );
		$permalink	= $this->getPermalink( $item , $article , $category );

		$this->set( 'permalink'	, $permalink );
		$this->set( 'article'	, $article );
		$this->set( 'actor'		, $actor );

		// Load up the contents now.
		$item->title 	= parent::display( 'streams/' . $item->verb . '.title' );
		$item->content 	= '';

	}

	/**
	 * Generates the stream item for K2
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onPrepareStream(SocialStreamItem &$item, $includePrivacy = true)
	{
		if ($item->context != 'k2') {
			return;
		}

		// Get application params
		$params = $this->getParams();
		$article = $this->getArticle($item);

		// Item may have been deleted from the site
		if (!$article || $article->trash) {
			return;
		}

		$this->format($item, $article);

		// Ensure that the user can really view this article
		if (!$this->canView($article)) {
			return;
		}

		if (!$article->published) {
			return;
		}

		if ($item->verb == 'create' && !$params->get('stream_create', true)) {
			return;
		}

		if ($item->verb == 'update' && !$params->get('stream_update', true)) {
			return;
		}

		if ($item->verb == 'read' && !$params->get('stream_read', true)) {
			return;
		}


		if ($item->verb == 'create' && $this->my->id == $item->actor->id) {
			$file = JPATH_ROOT . '/components/com_k2/helpers/permissions.php';
			if (JFile::exists($file)) {
				require_once($file);

				K2HelperPermissions::setPermissions();
				if (K2HelperPermissions::canEditItem($article->created_by, $article->catid)) {
					$item->edit_link = JRoute::_('index.php?option=com_k2&view=item&task=edit&cid='.$article->id.'&tmpl=component');
				}
			}
		}

		// Get the actor
		$actor = $item->actor;

		$this->set('article', $article);
		$this->set('actor', $actor);

		$item->display = SOCIAL_STREAM_DISPLAY_FULL;
		$item->title = parent::display('themes:/site/streams/articles/' . $item->verb . '.title');
		$item->preview = parent::display('themes:/site/streams/articles/preview');

		// Append the opengraph tags
		$item->addOgDescription($article->content);
	}

	/**
	 * Determines if the viewer has access to view this article
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function canView(&$article)
	{
		$viewLevels = $this->my->getAuthorisedViewLevels();

		if (!in_array($article->access, $viewLevels) || !in_array($article->category->access, $viewLevels)) {
			return false;
		}

		return true;
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

		// Add image to the object
		$this->getImage($article);

		// If there is no image, we try to get from the content
		if (!$article->image) {
			$article->image = $this->processContentImage($article->content);
		}

		// Remove any plugin tags
		$article->content = preg_replace('/\{.*\}/i', '', $article->content);

		// Limit the content length
		$params = $this->getParams();
		$contentLength = $params->get('stream_content_length');

		if ($contentLength) {
			$article->content = ESJString::substr(strip_tags($article->content), 0, $contentLength) . JText::_('COM_EASYSOCIAL_ELLIPSES');
		}

		$article->category = $this->getCategory($item, $article);
		$article->permalink = $this->getPermalink($item, $article, $article->category);
		$article->categoryPermalink = $this->getCategoryPermalink($item, $article->category);
		$article->date = ES::date($article->created);
	}

	/**
	 * Add an image to the article if there is any image assigned
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getImage(&$article)
	{
		$params = JFactory::getApplication()->getParams('com_k2');

		$path = JPATH_SITE . '/media/k2/items/cache/' . md5("Image".$article->id) . '_XL.jpg';
		$exists = JFile::exists($path);

		$article->image = false;

		if ($exists) {

			$article->image = JURI::base(true).'/media/k2/items/cache/' . md5("Image".$article->id) . '_XL.jpg';

			if ($params->get('imageTimestamp')) {
				$article->image .= $timestamp;
			}
		}
	}

	/**
	 * Retrieves the row data from K2
	 *
	 * @since	1.2
	 * @access	public
	 */
	private function getArticle($item)
	{
		static $items = array();

		if (!isset($items[$item->contextId])) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__k2_items');
			$sql->where('id', $item->contextId);
			$sql->limit(1);

			$db->setQuery($sql);
			$article = $db->loadObject();

			if (!$article || !$article->id) {
				return false;
			}

			// Include K2's table
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_k2/tables');
			$category = JTable::getInstance('K2Category', 'Table');
			$category->load($article->catid);

			$article->category = $category;

			$items[$item->contextId] = $article;
		}

		return $items[$item->contextId];
	}

	/**
	 * Retrieves the category object from K2
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getCategory($item, $article)
	{
		// Load up the category dataset
		JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_k2/tables' );
		$category = JTable::getInstance('K2Category', 'Table');

		$category->load($article->catid);

		// Normalize the properties so that it behaves the same way as articles
		$category->title = $category->name;

		return $category;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getPermalink( $item , $article , $category )
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');

		// $permalink = ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid . ':' . $category->alias, $article->language);


		$permalink = K2HelperRoute::getItemRoute($article->id . ':' . $article->alias, $article->catid . ':' . $category->alias);

		$language = $article->language;

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
			$permalink .= '&lang=' . $language;
		}

		return JRoute::_($permalink);
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function processContentImage($content)
	{
		// @rule: Match images from content
		$pattern = '/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'\s>]*)/i';
		preg_match($pattern, $content, $matches);

		$image = '';

		if ($matches) {
			$image = isset($matches[1]) ? $matches[1] : '';

			if (ESJString::stristr($matches[1], 'https://') === false && ESJString::stristr($matches[1], 'http://') === false && !empty($image)) {
				$image = rtrim(JURI::root(), '/') . '/' . ltrim($image, '/');
			}
		}

		return $image;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getCategoryPermalink( $item , $category )
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');

		$categoryPermalink = K2HelperRoute::getCategoryRoute($category->id . ':' . $category->alias);

		$language = $category->language;

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled()) {
			$categoryPermalink .= '&lang=' . $language;
		}

		return JRoute::_($categoryPermalink);
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterCommentSave($comment)
	{
		$allowed = array('k2.user.create', 'k2.user.update', 'k2.user.read');

		if (!in_array($comment->element, $allowed)) {
			return;
		}

		list($element, $group, $verb) = explode('.', $comment->element);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $comment->uid));

		if (!$state) {
			return;
		}

		$owner = $streamItem->actor_id;

		$emailOptions = array(
			'title' => 'APP_USER_K2_EMAILS_COMMENT_ITEM_TITLE',
			'template' => 'apps/user/k2/comment.item',
			'permalink' => $streamItem->getPermalink(true, true)
		);

		$systemOptions = array(
			'title' => '',
			'content' => $comment->comment,
			'context_type' => $comment->element,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $comment->created_by,
			'uid' => $comment->uid,
			'aggregate' => true
		);

		if ($comment->created_by != $owner && !$comment->isChild()) {
			ES::notify('comments.item', array($owner), $emailOptions, $systemOptions);
		}

		$recipients = $this->getStreamNotificationTargets($comment->uid, $element, $group, $verb, array(), array($owner, $comment->created_by));

		$emailOptions['title'] = 'APP_USER_K2_EMAILS_COMMENT_INVOLVED_TITLE';
		$emailOptions['template'] = 'apps/user/k2/comment.involved';

		if (!$comment->isChild()) {
			// Notify other participating users
			ES::notify('comments.involved', $recipients, $emailOptions, $systemOptions);
		}

		$emailOptions['title'] = 'COM_ES_EMAILS_REPLIED_TITLE_SUBJECT';
		$emailOptions['template'] = 'apps/user/k2/comment.replied';

		// Notify the owner of the parent comment
		if ($comment->isChild() && $comment->created_by != $comment->getParent()->created_by) {
			ES::notify('comments.replied', [$comment->getParent()->created_by], $emailOptions, $systemOptions);
		}
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onAfterLikeSave($like)
	{
		$allowed = array('k2.user.create', 'k2.user.update', 'k2.user.read');

		if (!in_array($like->type, $allowed)) {
			return;
		}

		$segments = $like->type;

		list($element, $group, $verb) = explode('.', $segments);

		$streamItem = ES::table('streamitem');
		$state = $streamItem->load(array('context_type' => $element, 'actor_type' => $group, 'verb' => $verb, 'context_id' => $likes->uid));

		if (!$state) {
			return;
		}

		$owner = $streamItem->actor_id;

		$systemOptions = array(
			'title' => '',
			'context_type' => $likes->type,
			'url' => $streamItem->getPermalink(false, false, false),
			'actor_id' => $likes->created_by,
			'uid' => $likes->uid,
			'aggregate' => true
		);

		if ($likes->created_by != $owner) {
			ES::notify('likes.item', array($owner), array(), $systemOptions);
		}

		$recipients = $this->getStreamNotificationTargets($likes->uid, $element, $group, $verb, array(), array($owner, $likes->created_by));

		ES::notify('likes.involved', $recipients, array(), $systemOptions);
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onNotificationLoad(SocialTableNotification &$item)
	{
		// k2.user.create
		// k2.user.update
		// k2.user.read

		$allowed = array('k2.user.create', 'k2.user.update', 'k2.user.read');

		if (!in_array($item->context_type, $allowed)) {
			return;
		}

		$hook = $this->getHook('notification', $item->type);
		$hook->execute($item);

		return;
	}
}
