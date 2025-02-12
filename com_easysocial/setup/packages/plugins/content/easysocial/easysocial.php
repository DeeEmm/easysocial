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

jimport('joomla.filesystem.file');

$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/plugins.php';

if (!JFile::exists($file)) {
	return;
}

require_once($file);

class PlgContentEasySocial extends EasySocialPlugins
{
	public $group = 'content';
	public $element = 'easysocial';

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Determines if the context is allowed
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isContextAllowed($context)
	{
		$allowed = [
			'com_content.article',
			'com_virtuemart.productdetails'
		];

		if (!in_array($context, $allowed)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if this plugin should be excluded
	 *
	 * @since	3.2.9
	 * @access	public
	 */
	public function isExcluded($categoryId)
	{
		static $cache = array();

		if (!isset($cache[$categoryId])) {
			$cache[$categoryId] = false;
			$exclusions = $this->params->get('category_exclusion');

			// If this category is excluded, skip this
			if ($exclusions && in_array($categoryId, $exclusions)) {
				$cache[$categoryId] = true;
			}
		}

		return $cache[$categoryId];
	}

	/**
	 * Renders EasySocial dependencies
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function renderDependencies()
	{
		static $loaded = null;

		if (is_null($loaded)) {
			// We need stuffs from EasySocial library
			ES::initialize();

			// Load front end's language file
			ES::language()->loadSite();

			$loaded = true;
		}
		return $loaded;
	}

	/**
	 * Check the user session for the award points.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function sessionExists()
	{
		// Get the IP address from the current user
		$ip	= $_SERVER['REMOTE_ADDR'];

		// Check the article item view
		$this->app = JFactory::getApplication();
		$view = $this->app->input->get('view');

		// Get the current article item id
		$itemId = $this->app->input->get('id', 0, 'int');

		if (!empty($ip) && !empty($itemId) && $view == 'article') {

			$token = md5($ip . $itemId);
			$session = JFactory::getSession();
			$exists	= $session->get($token , false);

			// If the session existed return true
			if ($exists) {
				return true;
			}

			// Set the token so that the next time the same visitor visits the page, it wouldn't get executed again.
			$session->set($token , 1);
		}

		return false;
	}

	/**
	 * Triggered when preparing an article for display
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onContentPrepare($context, &$article, &$params)
	{
		if (!$this->isContextAllowed($context)) {
			return;
		}

		// Return if we don't have a valid article id
		if (!isset($article->id) || !(int) $article->id) {
			return true;
		}

		if ($this->params->get('share_placement', 'default') == 'default') {
			$article->text .= $this->renderShareButton($context, $article, $params);
		}


		$placement = $this->params->get('placement', 1);
		$contents = '';

		// Attach the info box
		if ($placement == 1) {
			$contents .= $this->renderAuthorBox($context, $article, $params);
			$article->text .= $contents;
		}

		// Only assign points to viewer when they are not a guest and not the owner of the article
		if (!$this->my->id) {
			return;
		}

		// Get the current view
		$view = $this->input->get('view', '', 'cmd');

		if ($this->my->id != $article->created_by && $view == 'article' && !$this->sessionExists()) {

			// Assign points to viewer
			$this->assignPoints('read.article', $this->my->id);

			// Assign badge to the viewer
			$this->assignBadge('read.article', JText::_('PLG_CONTENT_EASYSOCIAL_UPDATED_EXISTING_ARTICLE'));

			// Assign points to author when their article is being read
			$this->assignPoints('author.read.article', $article->created_by);

			// Create a new stream item when an article is being read
			$appParams = $this->getAppParams('article', 'user');

			if ($appParams && $appParams->get('stream_read', false)) {
				$this->createStream($article, 'read', false, $this->my->id);
			}
		}
	}

	/**
	 * Places the attached data on the event afterDisplayTitle
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onContentAfterTitle($context, &$article, &$params)
	{
		if (!$this->isContextAllowed($context)) {
			return;
		}

		$contents = '';

		if ($this->params->get('share_placement', 'default') == 'after_title') {
			$contents .= $this->renderShareButton($context, $article, $params);
		}

		if ($this->params->get('placement', 1) != 2) {
			return $contents;
		}

		$contents .= $this->renderAuthorBox($context, $article, $params);

		return $contents;
	}

	/**
	 * Places the attached data on the event beforeDisplayContent
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onContentBeforeDisplay($context, &$article, &$params)
	{
		if (!$this->isContextAllowed($context)) {
			return;
		}

		if ($this->params->get('placement', 1) != 3) {
			return;
		}

		$contents = $this->renderAuthorBox($context, $article, $params);

		return $contents;
	}

	/**
	 * Renders the author's box at the end of the article
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function onContentAfterDisplay($context, &$article, &$params)
	{
		if (!$this->isContextAllowed($context)) {
			return;
		}

		// Get application params
		$appParams = $this->getAppParams('article', 'user');

		if ($this->params->get('modify_contact_link', true)) {

			// If dont have set creator alias
			if (isset($article->created_by_alias) && empty($article->created_by_alias)) {

				$author = ES::user($article->created_by);

				// Update the author link
				$article->contact_link = $author->getPermalink();
			}

			// Determine whether this author got created any contact or not
			$contact = $this->contactExist($article->created_by);

			if ($contact && (isset($article->created_by_alias) && !empty($article->created_by_alias))) {
				$article->contact_link = '';
			}
		}

		$contents = '';

		if ($this->params->get('share_placement', 'default') == 'after_content') {
			$contents .= $this->renderShareButton($context, $article, $params);
		}

		// If author box is configured to appear at the end of the article
		if ($this->params->get('placement', 1) == 4) {
			$contents .= $this->renderAuthorBox($context, $article, $params);
		}

		if ($this->params->get('load_reactions', false)) {
			$contents .= $this->renderReactions($context, $article);
		}

		// Render the comments here
		if ($this->params->get('load_comments', false)) {
			$contents .= $this->renderComments($context, $article);
		}

		return $contents;
	}

	/**
	 * Determine whether this user did created contact
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function contactExist($userId)
	{
		$db = ES::db();

		$contactEnabled = JPluginHelper::isEnabled('content', 'contact');

		if (!$contactEnabled) {
			return false;
		}

		$query = 'SELECT `id`,`catid`,`alias`, MAX(contact.`id`) AS `contactid`, contact.`alias`, contact.`catid`';
		$query .= ' FROM ' . $db->nameQuote('#__contact_details') . ' AS `contact`';
		$query .= ' WHERE contact.' . $db->nameQuote('published') . ' = ' . $db->Quote('1');
		$query .= ' AND contact.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result->id) {
			return false;
		}

		return $result;
	}

	/**
	 * Renders the author box in the content
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderAuthorBox($context, &$article, &$params)
	{
		if (!$this->params->get('display_info', false)) {
			return;
		}

		// Only display when article category isn't excluded
		if ($context == 'com_content.article' && $this->isExcluded($article->catid)) {
			return;
		}

		$this->renderDependencies();

		// Get the author of the article
		if (!isset($article->created_by)) {
			return;
		}

		// For whatever reason that we cannot get the author id, we shouldn't display "guest"
		if (!$article->created_by) {
			return;
		}

		$author = ES::user($article->created_by);
		$this->assign('author', $author);
		$contents = $this->output('article');

		return $contents;
	}

	/**
	 * Renders the share button in the content
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function renderShareButton($context, &$article, &$params)
	{
		if (!$this->params->get('display_share_button', false)) {
			return;
		}

		// Only display when article category isn't excluded
		if ($context == 'com_content.article' && $this->isExcluded($article->catid)) {
			return;
		}

		$contents = $this->output('share');

		return $contents;
	}

	/**
	 * Renders reactions on articles
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function renderReactions($context, $article)
	{
		$this->renderDependencies();

		if ($context != 'com_content.article') {
			return;
		}

		// Only display when article category isn't excluded
		if ($this->isExcluded($article->catid)) {
			return;
		}

		$contextType = $this->getContextType($context);


		// Ensure this article already generated the stream item, so can able to associated the stream item and the last action update
		$streams = $this->getStreamId($article->id, $contextType, 'create');
		$streamId = '';

		// if exist this stream item for this article create verb
		if (count($streams) > 0) {

			foreach ($streams as $streamItem) {
				$streamId = $streamItem->uid;
			}
		}

		$likes = ES::likes($article->id, $contextType, 'create', SOCIAL_APPS_GROUP_USER, $streamId);

		$this->assign('likes', $likes);
		$output = $this->output('reactions');

		return $output;
	}

	/**
	 * Renders comments section on articles
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderComments($context, $article)
	{
		$this->renderDependencies();

		// We only want to allow rendering of comments in articles
		if ($context != 'com_content.article') {
			return;
		}

		// Only display when article category isn't excluded
		if ($this->isExcluded($article->catid)) {
			return;
		}

		$canViewComments = ($this->my->id || !$this->my->id && $this->params->get('guest_viewcomments', true));
		$comments = '';

		// If configured to display comemnts
		if ($canViewComments) {
			$url = ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid);

			// Ensure this article already generated the stream item, so can able to associated the stream item and the last action update
			$streams = $this->getStreamId($article->id, 'article', 'create');
			$streamId = '';

			// if exist this stream item for this article create verb
			if (count($streams) > 0) {

				foreach ($streams as $streamItem) {
					$streamId = $streamItem->uid;
				}
			}

			$comments = ES::comments($article->id, 'article', 'create', SOCIAL_APPS_GROUP_USER, array('url' => $url), $streamId);
			$comments = $comments->getHtml();

			$this->assign('comments', $comments);
			$comments = $this->output('comments');
		}

		return $comments;
	}

	/**
	 * Triggered when an article is stored.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		if ($context != 'com_content.article' && $context != 'com_content.form') {
			return;
		}

		// Set the verb according to the state of the article
		$verb = $isNew ? 'create' : 'update';

		// Get application params
		$appParams = $this->getAppParams('article', 'user');

		// If app does not exist, skip this altogether.
		if (!$appParams) {
			return;
		}

		// There is a case when the user updated their existing published article on the site
		// We need to update the stream params data
		if (!$isNew) {
			$streams = $this->getStreamId($article->id, 'article');

			if (count($streams) > 0) {

				$streamIds = array();

				foreach ($streams as $streamItem) {
					$streamIds[] = $streamItem->uid;
				}

				// update existing stream item params data
				$this->updateExistingStreamItem($article, $streamIds);
			}
		}

		// If plugin is disabled to create new stream, skip this
		if ($isNew && !$appParams->get('stream_create', true)) {
			return;
		}

		// If plugin is disabled to create update stream, skip this.
		if (!$isNew && !$appParams->get('stream_update', true)) {
			return;
		}

		// Skip this if the article state is not published
		if ($article->state != 1) {
			return;
		}

		// Create stream record.
		$this->createStream($article, $verb, $isNew);

		// Command to assign points and badge
		$command = $verb . '.article';

		// Assign points
		$this->assignPoints($command, $article->created_by);

		$badgeMessage = JText::_('PLG_CONTENT_EASYSOCIAL_UPDATED_EXISTING_ARTICLE');

		if ($isNew) {
			$badgeMessage = JText::_('PLG_CONTENT_EASYSOCIAL_CREATED_NEW_ARTICLE');
		}

		// Assign badge for the user
		$this->assignBadge($command, $badgeMessage);
	}

	/**
	 * Assign points
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function assignPoints($command, $userId = null)
	{
		$userId = ES::user($userId)->id;

		return ES::points()->assign($command, 'com_content', $userId);
	}

	/**
	 * Assign badges
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function assignBadge($rule , $message , $creatorId = null)
	{
		$creator 	= ES::user( $creatorId );

		$badge 	= ES::badges();
		$state 	= $badge->log( 'com_content' , $rule , $creator->id , $message );

		return $state;
	}

	/**
	 * Perform cleanup when an article is being deleted
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function onContentBeforeDelete($context, $data)
	{
		if ($context != 'com_content.article' && $context != 'com_content.form') {
			return;
		}

		// Delete the items from the stream.
		$stream = ES::stream();
		$stream->delete($data->id, 'article');
	}

	/**
	 * This is to fix k2 issue where it isn't triggering k2 group of plugins
	 *
	 * @since	3.2.16
	 * @access	public
	 */
	public function onContentAfterDelete($context, $data)
	{
		if ($context != 'com_k2.item') {
			return;
		}

		$stream = ES::stream();
		$stream->delete($data->id, 'k2');
	}

	/**
	 * Generate new stream activity.
	 *
	 * @since	1.0
	 * @access	public
	 */
	private function createStream($article, $verb, $isNew = false, $actorId = null)
	{
		$tmpl = ES::stream()->getTemplate();

		if (is_null($actorId)) {

			$actorId = $article->created_by;

			// Only check this if there is update article action
			if (!$isNew) {
				$isFollowCreatedByBehavior = $this->isFollowCreatedByBehavior();
				$actorId = $isFollowCreatedByBehavior ? $article->created_by : $article->modified_by;
			}

			// For some of the case this article created_by user id no longer exist on the site
			// So we need to ensure this created_by user id is valid or not
			$authorData = ES::user($actorId);

			// If this user id is not valid, we need to update this created_by id to who editing this article now
			if (!$authorData->id) {
				$actorId = $article->modified_by;
			}
		}

		// Set the creator of this article.
		$tmpl->setActor($actorId, SOCIAL_TYPE_USER);
		$tmpl->setContext($article->id, 'article');
		$tmpl->setVerb($verb);

		// Load up the category dataset
		$category = JTable::getInstance('Category');
		$category->load( $article->catid );

		// Get the permalink
		$permalink = ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid . ':' . $category->alias);

		// Get the category permalink
		$categoryPermalink = ESContentHelperRoute::getCategoryRoute($category->id . ':' . $category->alias);

		// Store the article in the params
		$registry = ES::registry();
		$registry->set('article', $article);
		$registry->set('category', $category);
		$registry->set('permalink', $permalink);
		$registry->set('categoryPermalink', $categoryPermalink);

		// We need to tell the stream that this uses the core.view privacy.
		$tmpl->setAccess('core.view');

		// Set the template params
		$tmpl->setParams($registry);

		ES::stream()->add($tmpl);
	}

	/**
	 * Retrieve the context type from EasySocial based on the context of the extension
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getContextType($context)
	{
		$type = 'article';

		if ($context == 'com_virtuemart.productdetails') {
			$type = 'virtuemart';
		}

		return $type;
	}

	/**
	 * Retrieves the stream id given the appropriate item contexts
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function getStreamId($contextId, $contextType, $verb = '')
	{
		$db = ES::db();

		$query = 'SELECT `uid` FROM `#__social_stream_item`';
		$query .= ' WHERE `context_id` = ' . $db->Quote($contextId);
		$query .= ' AND `context_type` = ' . $db->Quote($contextType);

		if ($verb) {
			$query .= ' AND `verb` = ' . $db->Quote($verb);
		}

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves the stream id given the appropriate item contexts
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function updateStreamParams($streamIds = [], $params = null, $article = null)
	{
		$db = ES::db();

		$streamIds = implode(',', $streamIds);

		$query = 'UPDATE ' . $db->nameQuote('#__social_stream');
		$query .= ' SET ' . $db->nameQuote('params') . ' = ' . $db->Quote($params);
		$query .= ' , ' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($article->created_by);
		$query .= ' , ' . $db->nameQuote('state') . ' = ' . $db->Quote($article->state);
		$query .= ' WHERE ' . $db->nameQuote('id') . ' IN (' . $streamIds . ')';

		$db->setQuery($query);
		$db->query();

		$query = 'UPDATE ' . $db->nameQuote('#__social_stream_item');
		$query .= ' SET ' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($article->created_by);
		$query .= ' , ' . $db->nameQuote('state') . ' = ' . $db->Quote($article->state);
		$query .= ' WHERE ' . $db->nameQuote('context_id') . ' = ' . $db->Quote($article->id);
		$query .= ' AND ' . $db->nameQuote('context_type') . ' = ' . $db->Quote('article');

		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Publish/Unpublish the stream item when the article state changed
	 *
	 * @since   4.0.12
	 * @access  public
	 */
	public function updateStreamItemsState($articleIds, $state = SOCIAL_STREAM_STATE_TRASHED)
	{
		$db = ES::db();

		if (empty($articleIds)) {
			return;
		}

		$streamState = $state == SOCIAL_STREAM_STATE_PUBLISHED ? SOCIAL_STREAM_STATE_PUBLISHED : SOCIAL_STREAM_STATE_TRASHED;
		$streamIds = [];

		foreach ($articleIds as $articleId) {
			$streams = $this->getStreamId($articleId, 'article');

			if (count($streams) > 0) {

				foreach ($streams as $streamItem) {
					$streamIds[] = $streamItem->uid;
				}
			}
		}

		if ($streamIds) {
			$ids = implode(',', $streamIds);

			$query = "update `#__social_stream` as a inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
			$query .= " set a.`state` = " . $db->Quote($streamState) . ",";
			$query .= "		b.`state` = " . $db->Quote($streamState);
			$query .= " where a.`id` IN ($ids)";

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}

	/**
	 * This is an event that is called after article content has its state change (e.g. Published to Unpublished).
	 *
	 * @since   4.0.12
	 * @access  public
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if (!$this->isContextAllowed($context)) {
			return;
		}

		$this->updateStreamItemsState($pks, $value);

		return true;
	}

	/**
	 * Update the existing stream item params data when someone update the existing article content
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function updateExistingStreamItem($article, $streamIds = [])
	{
		// Load up the category dataset
		$category = JTable::getInstance('Category');
		$category->load($article->catid);

		// Get the permalink
		$permalink = ESContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid . ':' . $category->alias);

		// Get the category permalink
		$categoryPermalink 	= ESContentHelperRoute::getCategoryRoute($category->id . ':' . $category->alias);

		// Determine that update stream author behavior
		$isFollowCreatedByBehavior = $this->isFollowCreatedByBehavior();

		// Only update certain necessary article params if the update stream behavior is set to modify by
		if (!$isFollowCreatedByBehavior) {

			// Load the stream table data first
			$streamTable = ES::table('Stream');
			$streamItemTable = ES::table('StreamItem');

			foreach ($streamIds as $streamId) {
				$streamState = $streamTable->load($streamId);

				if ($streamState && $streamTable->params) {

					// update the necessary article params especially title, content, and permalink
					// prevent the existing stream item that article permalink show 404 error
					// and can't override the article author name since this behavior only update the latest stream item article author data after proceed this
					$paramsObj = ES::json()->decode($streamTable->params);
					$paramsObj->article->title = $article->title;
					$paramsObj->article->alias = $article->alias;
					$paramsObj->article->introtext = $article->introtext;
					$paramsObj->article->fulltext = $article->fulltext;
					$paramsObj->article->catid = $article->catid;
					$paramsObj->article->state = $article->state;

					$paramsObj->category = $category;
					$paramsObj->permalink = $permalink;
					$paramsObj->categoryPermalink = $categoryPermalink;

					$data = ES::json()->encode($paramsObj);

					$streamTable->state = $article->state;
					$streamTable->params = $data;
					$streamTable->store();
				}

				// update the state under stream item table as well
				$streamItemState = $streamItemTable->load([
					'uid' => $streamId
				]);

				if ($streamItemState) {
					$streamItemTable->state = $article->state;
					$streamItemTable->store();
				}
			}

			return true;
		}

		// For some of the case this article created_by user id no longer exist on the site
		// So we need to ensure this created_by user id is valid or not
		$authorData = ES::user($article->created_by);

		// If this user id is not valid, we need to update this author id who editing this article now
		if (!$authorData->id) {
			$article->created_by = $article->modified_by;
		}

		// Store the article in the params
		$registry = ES::registry();
		$registry->set('article', $article);
		$registry->set('category', $category);
		$registry->set('permalink', $permalink);
		$registry->set('categoryPermalink', $categoryPermalink);

		// Set the params data
		if (!is_string($registry)) {
			if ($registry instanceof SocialRegistry) {
				$params = $registry->toString();
			} else {
				$params = ES::json()->encode($registry);
			}
		} else {
			$params = $registry;
		}

		// update stream params now
		$this->updateStreamParams($streamIds, $params, $article);
	}

	/**
	 * Determine that update stream author behavior
	 *
	 * @since   4.0.11
	 * @access  public
	 */
	public function isFollowCreatedByBehavior()
	{
		// update all the existing stream item base on the created_by (original Actor)
		// update all the existing stream item base on the last modified_by (Modifier Actor)
		$appParams = $this->getAppParams('article', 'user');

		$streamUpdateBehavior = $appParams->get('stream_update_behavior', 'createdby');

		$isFollowCreatedByBehavior = $streamUpdateBehavior === 'createdby' ? true : false;

		return $isFollowCreatedByBehavior;
	}
}
